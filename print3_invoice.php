<?php
include 'db_connection.php';

if (!isset($_GET['id'])) {
    die("Invalid Request!");
}

$invoiceId = intval($_GET['id']);

// Fetch Invoice Data
$sql = "SELECT i.*, c.CustomerName, c.Phone, c.Email, c.Address, c.CustomerID, c.account_id
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

// Fetch Previous Balance from journal_details
$previousBalance = 0;
if (!empty($invoice['account_id'])) {
    $balance_sql = "SELECT 
                        (SUM(debit) - SUM(credit)) AS balance
                    FROM 
                        journal_details
                    WHERE 
                        account_id = ?";
    $balance_stmt = $conn->prepare($balance_sql);
    $balance_stmt->bind_param("i", $invoice['account_id']);
    $balance_stmt->execute();
    $balance_result = $balance_stmt->get_result();
    $balance_row = $balance_result->fetch_assoc();
    $previousBalance = $balance_row['balance'] ?? 0;
    $balance_stmt->close();
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
            padding: 15mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border: 2px solid #000;
            display: flex;
            flex-direction: column;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .logo-section {
            flex: 0 0 35%;
        }

        .logo-section img {
            height: 110px;
            width: auto;
            display: block;
        }

        .invoice-title-section {
            flex: 0 0 60%;
            text-align: right;
        }

        .invoice-title-section h1 {
            font-size: 40px;
            font-weight: bold;
            letter-spacing: 12px;
            margin-bottom: 8px;
        }

        .invoice-meta {
            display: inline-block;
            text-align: left;
            font-size: 12px;
            border: 1px solid #000;
        }

        .invoice-meta-row {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #000;
        }

        .invoice-meta-row:last-child {
            border-bottom: none;
        }

        .invoice-meta-row strong {
            font-weight: bold;
            padding: 6px 10px;
            min-width: 80px;
            background-color: #f5f5f5;
            border-right: 1px solid #000;
        }

        .invoice-meta-value {
            padding: 6px 10px;
            background-color: #fff;
            min-width: 180px;
        }

        /* Customer Section */
        .customer-section {
            margin-bottom: 2px;
            padding: 3px 0;
        }

        .customer-section p {
            font-size: 13px;
            margin: 0;
        }

        .customer-section .label {
            font-weight: normal;
        }

        .customer-section .value {
            font-weight: bold;
        }

        .customer-divider {
            border: 0;
            border-top: 1px solid #000;
            margin: 2px 0 3px 0;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #d0d0d0;
            border: 1px solid #000;
            padding: 10px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 8px 4px;
            text-align: center;
            font-size: 11px;
        }

        .items-table td:nth-child(2) {
            text-align: left;
        }

        /* Column widths */
        .items-table th:nth-child(1),
        .items-table td:nth-child(1) { width: 6%; } /* Job # */
        
        .items-table th:nth-child(2),
        .items-table td:nth-child(2) { width: 28%; } /* Description - wider */
        
        .items-table th:nth-child(3),
        .items-table td:nth-child(3) { width: 10%; } /* Media */
        
        .items-table th:nth-child(4),
        .items-table td:nth-child(4) { width: 7%; } /* Width */
        
        .items-table th:nth-child(5),
        .items-table td:nth-child(5) { width: 7%; } /* Height */
        
        .items-table th:nth-child(6),
        .items-table td:nth-child(6) { width: 6%; } /* QTY */
        
        .items-table th:nth-child(7),
        .items-table td:nth-child(7) { width: 8%; } /* Sqft */
        
        .items-table th:nth-child(8),
        .items-table td:nth-child(8) { width: 10%; } /* Rate */
        
        .items-table th:nth-child(9),
        .items-table td:nth-child(9) { width: 12%; } /* Total */

        /* Empty rows */
        .empty-row {
            height: 28px;
        }

        .empty-row td {
            border: 1px solid #000;
        }

        /* Totals Section */
        .totals-section {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 250px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 10px 15px;
            font-size: 13px;
            text-align: right;
            border: 1px solid #000;
        }

        .totals-table td:first-child {
            text-align: left;
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .totals-table tr:last-child td {
            font-weight: bold;
            font-size: 14px;
            background-color: #e0e0e0;
        }

        /* Previous Balance Row Styling */
        .totals-table tr.prev-balance-row td {
            background-color: #fff3cd;
            color: #856404;
        }

        .totals-table tr.prev-balance-row td:first-child {
            background-color: #fff3cd;
        }

        /* Spacer to push footer to bottom */
        .spacer {
            flex-grow: 1;
        }

        /* Footer Section */
        .footer {
            margin-top: auto;
        }

        .signature-section {
            margin: 30px 0 15px 0;
            padding-left: 30px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 180px;
            padding-top: 5px;
            font-size: 12px;
        }

        .thank-you {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 5px 0 3px 0;
        }

        .company-details {
            text-align: center;
            font-size: 9px;
            color: #000;
            line-height: 1.3;
            margin-top: 2px;
            padding-top: 3px;
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
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <img src="alsadiqlogo.jpg" alt="Al-Sadiq Logo">
        </div>
        
        <div class="invoice-title-section">
            <h1>INVOICE</h1>
            <div class="invoice-meta">
                <div class="invoice-meta-row">
                    <strong>No:</strong>
                    <div class="invoice-meta-value"><?php echo htmlspecialchars($invoice['InvoiceNo']); ?></div>
                </div>
                <div class="invoice-meta-row">
                    <strong>Date:</strong>
                    <div class="invoice-meta-value"><?php echo date('F j, Y', strtotime($invoice['InvoiceDate'])); ?></div>
                </div>
                <div class="invoice-meta-row">
                    <strong>PO No:</strong>
                    <div class="invoice-meta-value"><?php echo htmlspecialchars($invoice['PONumber'] ?? '-'); ?></div>
                </div>
                <div class="invoice-meta-row">
                    <strong>Q No:</strong>
                    <div class="invoice-meta-value"><?php echo htmlspecialchars($invoice['QuotationNo'] ?? '-'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <p>
            <span class="label">Customer Name:</span> 
            <span class="value">
                <?php 
                echo htmlspecialchars($invoice['CustomerName']); 
                
                // Format phone: 0321-1234567 -> 0321-567
                $phone = $invoice['Phone'];
                $phoneDigits = preg_replace('/[^0-9]/', '', $phone); // Remove non-digits
                
                if (strlen($phoneDigits) >= 7) {
                    $firstFour = substr($phoneDigits, 0, 4);
                    $lastThree = substr($phoneDigits, -3);
                    $formattedPhone = $firstFour . '-' . $lastThree;
                } else {
                    $formattedPhone = $phone; // Fallback
                }
                
                echo ' (' . htmlspecialchars($formattedPhone) . ')';
                ?>
            </span>
        </p>
    </div>
    
    <hr class="customer-divider">

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
                <td><?php echo number_format($item['Total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            
            <?php 
            // Add empty rows
            $totalRows = 18;
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

    <!-- Totals Section -->
    <div class="totals-section">
        <table class="totals-table">
            <!-- Previous Balance Row -->
            <tr class="prev-balance-row">
                <td>PREV. BALANCE</td>
                <td><?php echo number_format($previousBalance, 2); ?></td>
            </tr>
            
            <tr>
                <td>TOTAL</td>
                <td><?php echo number_format($invoice['SubTotal'], 2); ?></td>
            </tr>
            <?php if ($invoice['TotalGST'] > 0): ?>
            <tr>
                <td>GST</td>
                <td><?php echo number_format($invoice['TotalGST'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($invoice['TotalNTR'] > 0): ?>
            <tr>
                <td>NTN</td>
                <td><?php echo number_format($invoice['TotalNTR'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>GRAND TOTAL</td>
                <td><?php echo number_format($invoice['GrandTotal'], 2); ?></td>
            </tr>
        </table>
    </div>

    <!-- Spacer to push footer to bottom -->
    <div class="spacer"></div>

    <!-- Footer -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-line">
                Signature
            </div>
        </div>

        <div class="thank-you">
            THANK YOU FOR YOUR BUSINESS!
        </div>

        <div class="company-details">
            43-G Shaama Plaza 71-km Main Ferozepur Road Lahore Ph:042-35979297<br>
            Mob: 0321-4527417, 0322-4876106 E-Mail: alsadiqgraphics@gmail.com
        </div>
    </div>
</div>

</body>
</html>