<?php
include 'db_connection.php';

if (!isset($_GET['id'])) {
    die("Invalid Request!");
}

$invoiceId = intval($_GET['id']);

// Fetch Invoice Data
$sql = "SELECT i.*, c.CustomerName, c.Phone, c.Email, c.Address 
        FROM tblinvoices i
        LEFT JOIN tblcustomers c ON i.CustomerID = c.CustomerID
        WHERE i.InvoiceID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoiceId);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();

if (!$invoice) {
    die("Invoice not found!");
}

// Fetch Invoice Items
$sql_items = "SELECT * FROM tblinvoice_details WHERE InvoiceID = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $invoiceId);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$items = $result_items->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$stmt_items->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo htmlspecialchars($invoice['InvoiceNo']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: #000;
            background: #f5f5f5;
            padding: 20px;
        }

        /* Main Container */
        .invoice-container {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm 15mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        /* Header Section - INVOICE Title */
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .invoice-header h1 {
            font-size: 48px;
            font-weight: bold;
            letter-spacing: 18px;
            margin: 0;
            padding: 0;
        }

        /* Invoice Meta Section */
        .invoice-meta-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .invoice-meta-left {
            text-align: left;
        }

        .invoice-meta-left p {
            margin: 2px 0;
        }

        .invoice-meta-right {
            text-align: right;
        }

        .invoice-meta-right p {
            margin: 2px 0;
        }

        .invoice-meta-left strong,
        .invoice-meta-right strong {
            font-weight: bold;
        }

        /* Customer Section */
        .customer-section {
            margin-bottom: 15px;
            padding: 8px 0;
            border-bottom: 1px solid #000;
        }

        .customer-section p {
            font-size: 12px;
            margin: 0;
        }

        .customer-section .label {
            font-weight: normal;
        }

        .customer-section .value {
            font-weight: normal;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #fff;
            border: 1px solid #000;
            border-bottom: 2px solid #000;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        .items-table td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-size: 11px;
        }

        .items-table tbody tr:first-child td {
            border-top: none;
        }

        .items-table td:nth-child(2) {
            text-align: left;
        }

        /* Column widths - matching picture */
        .items-table th:nth-child(1),
        .items-table td:nth-child(1) { width: 8%; } /* Job # */
        
        .items-table th:nth-child(2),
        .items-table td:nth-child(2) { width: 25%; } /* Description */
        
        .items-table th:nth-child(3),
        .items-table td:nth-child(3) { width: 10%; } /* Media */
        
        .items-table th:nth-child(4),
        .items-table td:nth-child(4) { width: 7%; } /* Width */
        
        .items-table th:nth-child(5),
        .items-table td:nth-child(5) { width: 7%; } /* Height */
        
        .items-table th:nth-child(6),
        .items-table td:nth-child(6) { width: 6%; } /* QTY */
        
        .items-table th:nth-child(7),
        .items-table td:nth-child(7) { width: 7%; } /* Sqft */
        
        .items-table th:nth-child(8),
        .items-table td:nth-child(8) { width: 10%; } /* Rate */
        
        .items-table th:nth-child(9),
        .items-table td:nth-child(9) { width: 12%; } /* Total */

        /* Empty rows */
        .empty-row {
            height: 24px;
        }

        .empty-row td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        /* Totals Section */
        .totals-section {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-area {
            width: 200px;
            padding-top: 40px;
        }

        .signature-line {
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 5px;
            font-size: 12px;
        }

        .totals-table {
            width: 280px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 15px;
            font-size: 12px;
            text-align: right;
            border: 1px solid #000;
        }

        .totals-table td:first-child {
            text-align: right;
            font-weight: normal;
            background-color: #fff;
        }

        .totals-table tr:last-child td {
            font-weight: bold;
        }

        /* Spacer to push footer to bottom */
        .spacer {
            flex-grow: 1;
        }

        /* Footer Section */
        .footer {
            margin-top: auto;
            padding-top: 30px;
        }

        .thank-you {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 20px 0 0 0;
        }

        /* Print Button */
        .print-controls {
            text-align: center;
            margin: 20px 0;
        }

        .print-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .print-btn:hover {
            background: #0056b3;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                margin: 0;
                border: none;
            }

            .no-print {
                display: none;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<!-- Print Controls -->
<div class="print-controls no-print">
    <button class="print-btn" onclick="window.print()">🖨️ Print Invoice</button>
</div>

<!-- Invoice Container -->
<div class="invoice-container">
    <!-- Header - INVOICE Title -->
    <div class="invoice-header">
        <h1>INVOICE</h1>
    </div>

    <!-- Invoice Meta Information -->
    <div class="invoice-meta-section">
        <div class="invoice-meta-left">
            <p><strong>No:</strong> <?php echo htmlspecialchars($invoice['InvoiceNo']); ?></p>
            <p><strong>PO No:</strong> <?php echo htmlspecialchars($invoice['PONumber'] ?? '-'); ?></p>
            <p><strong>Q No:</strong> <?php echo htmlspecialchars($invoice['QuotationNo'] ?? '-'); ?></p>
        </div>
        <div class="invoice-meta-right">
            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($invoice['InvoiceDate'])); ?></p>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <p>
            <span class="label">Customer Name:</span> 
            <span class="value">
                <?php 
                echo htmlspecialchars($invoice['CustomerName']); 
                
                // Format phone: 0321-1234567 -> (0321-4193...)
                $phone = $invoice['Phone'];
                $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
                
                if (strlen($phoneDigits) >= 8) {
                    $firstPart = substr($phoneDigits, 0, 4);
                    $lastPart = substr($phoneDigits, 4, 4);
                    $formattedPhone = '(' . $firstPart . '-' . $lastPart . '...)';
                } else {
                    $formattedPhone = '(' . $phone . ')';
                }
                
                echo ' ' . htmlspecialchars($formattedPhone);
                ?>
            </span>
        </p>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Job #</th>
                <th>Description</th>
                <th>Media</th>
                <th>Width</th>
                <th>Height</th>
                <th>QTY</th>
                <th>Sqft</th>
                <th>Rate</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $rowCount = 0;
            foreach ($items as $item): 
                $rowCount++;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['JobNo']); ?></td>
                <td><?php echo htmlspecialchars($item['Detail']); ?></td>
                <td><?php echo htmlspecialchars($item['Media']); ?></td>
                <td><?php echo rtrim(rtrim($item['Width'], '0'), '.'); ?></td>
                <td><?php echo rtrim(rtrim($item['Height'], '0'), '.'); ?></td>
                <td><?php echo htmlspecialchars($item['Qty']); ?></td>
                <td><?php echo rtrim(rtrim($item['Sqft'], '0'), '.'); ?></td>
                <td><?php echo number_format($item['Rate'], 0); ?></td>
                <td><?php echo number_format($item['Total'], 0); ?></td>
            </tr>
            <?php endforeach; ?>
            
            <?php 
            // Add empty rows to match picture format
            $totalRows = 15;
            $remainingRows = $totalRows - $rowCount;
            for ($i = 0; $i < $remainingRows; $i++): 
            ?>
            <tr class="empty-row">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- Totals and Signature Section -->
    <div class="totals-section">
        <div class="signature-area">
            <div class="signature-line">
                Signature
            </div>
        </div>
        
        <table class="totals-table">
            <tr>
                <td>TOTAL</td>
                <td><?php echo number_format($invoice['SubTotal'], 0); ?></td>
            </tr>
            <tr>
                <td>GRAND TOTAL</td>
                <td><?php echo number_format($invoice['GrandTotal'], 0); ?></td>
            </tr>
        </table>
    </div>

    <!-- Spacer to push footer to bottom -->
    <div class="spacer"></div>

    <!-- Footer -->
    <div class="footer">
        <div class="thank-you">
            THANK YOU FOR YOUR BUSINESS!
        </div>
    </div>
</div>

</body>
</html>