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



?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice #<?php echo htmlspecialchars($invoice['InvoiceNo']); ?></title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: Arial, sans-serif;
        color: #111;
        background: #f5f5f5;
        padding: 20px;
    }
    
    /* Main Container */
    .invoice-container {
        background: white;
        margin: 0 auto 30px;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    /* A4 Size - First Page (Table Format) */
    .invoice-a4 {
        width: 210mm;
        min-height: 297mm;
    }
    
    /* Half Size - Second Page (Detail Format) */
    .invoice-detail {
        width: 210mm;
        min-height: 148mm;
        page-break-after: always;
    }
    
    /* Header Section for First Page */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 3px solid #000;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    
    .logo-section {
        flex: 0 0 40%;
    }
    
    .logo-section img {
        height: 80px;
        width: auto;
    }
    
    .invoice-title-section {
        flex: 0 0 55%;
        text-align: center;
    }
    
    .invoice-title-section h1 {
        font-size: 36px;
        font-weight: bold;
        letter-spacing: 8px;
        margin-bottom: 10px;
    }
    
    .invoice-meta-header {
        display: flex;
        justify-content: space-around;
        font-size: 11px;
        margin-top: 8px;
    }
    
    .invoice-meta-header div {
        text-align: center;
    }
    
    /* Detail Page Styles */
    .detail-header {
        border-bottom: 2px solid #333;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    
    .detail-header h2 {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 15px;
    }
    
    .detail-section {
        margin-bottom: 25px;
    }
    
    .detail-section h3 {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }
    
    .detail-info {
        font-size: 12px;
        line-height: 1.8;
    }
    
    .detail-info div {
        margin-bottom: 5px;
    }
    
    .detail-info strong {
        display: inline-block;
        width: 100px;
        color: #555;
    }
    
    /* Items Table for Detail Page */
    .detail-items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        font-size: 11px;
    }
    
    .detail-items-table th {
        background: #f0f0f0;
        border: 1px solid #ccc;
        padding: 8px 6px;
        text-align: left;
        font-weight: bold;
        font-size: 10px;
    }
    
    .detail-items-table td {
        border: 1px solid #ccc;
        padding: 8px 6px;
        text-align: left;
    }
    
    .detail-items-table td:nth-child(5),
    .detail-items-table td:nth-child(6),
    .detail-items-table td:nth-child(7),
    .detail-items-table td:nth-child(8) {
        text-align: right;
    }
    
    /* Items Table for First Page */
    table.main-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        font-size: 11px;
    }
    
    table.main-table th {
        background: #f0f0f0;
        border: 1px solid #000;
        padding: 6px 4px;
        text-align: center;
        font-weight: bold;
        font-size: 10px;
    }
    
    table.main-table td {
        border: 1px solid #000;
        padding: 6px 4px;
        text-align: center;
    }
    
    table.main-table td:nth-child(2) {
        text-align: left;
    }
    
    /* Empty rows */
    .empty-row {
        height: 25px;
    }
    
    /* Totals Section for Detail Page */
    .detail-totals {
        margin-top: 20px;
        padding: 15px;
        background: #f9f9f9;
        border: 1px solid #ddd;
    }
    
    .detail-totals-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        font-size: 12px;
    }
    
    .detail-totals-item {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px solid #ddd;
    }
    
    .detail-totals-item.grand {
        font-weight: bold;
        font-size: 14px;
        color: #000;
        border-top: 2px solid #333;
        border-bottom: 2px solid #333;
        padding: 10px 0;
        grid-column: 1 / -1;
    }
    
    /* Totals Section for First Page */
    .totals-section {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
    }
    
    .totals-table {
        width: 300px;
        border: none;
    }
    
    .totals-table td {
        border: none;
        border-bottom: 1px solid #ccc;
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .totals-table tr:last-child td {
        border-top: 2px solid #000;
        border-bottom: 2px solid #000;
        font-weight: bold;
        font-size: 14px;
        padding: 8px 10px;
    }
    
    /* Footer */
    .footer {
        margin-top: 30px;
        padding-top: 15px;
        border-top: 1px solid #ccc;
    }
    
    .signature-line {
        margin: 40px 0 10px;
        font-size: 12px;
        text-align: left;
        border-top: 1px solid #000;
        padding-top: 5px;
        width: 200px;
    }
    
    .thank-you {
        text-align: center;
        font-weight: bold;
        font-size: 14px;
        margin: 20px 0;
    }
    
    .company-details {
        text-align: center;
        font-size: 9px;
        color: #666;
        line-height: 1.4;
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
            padding: 15mm;
        }
        
        .invoice-a4 {
            page-break-after: always;
        }
        
        .invoice-detail {
            page-break-after: always;
        }
        
        .no-print {
            display: none;
        }
        
        @page {
            margin: 0;
        }
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
        margin: 0 10px;
    }
    
    .print-btn:hover {
        background: #0056b3;
    }
</style>
</head>
<body>

<!-- Print Controls -->
<div class="print-controls no-print">
    <button class="print-btn" onclick="window.print()">🖨️ Print Both Pages</button>
</div>

<!-- First Page - A4 Table Format -->
<div class="invoice-container invoice-a4">
    <div class="header">
        <div class="logo-section">
            <img src="alsadiqlogo.jpg" alt="Al Sadiq Logo">
            <div style="margin-top: 10px; font-size: 11px;">
                <strong>Customer Name:</strong> <?php echo htmlspecialchars($invoice['CustomerName']); ?><br>
                <strong>(<?php echo htmlspecialchars($invoice['Phone']); ?>)</strong>
            </div>
        </div>
        
        <div class="invoice-title-section">
            <h1>INVOICE</h1>
            <div class="invoice-meta-header">
                <div><strong>No:</strong> <?php echo htmlspecialchars($invoice['InvoiceNo']); ?></div>
                <div><strong>Date:</strong> <?php echo date('F j, Y', strtotime($invoice['InvoiceDate'])); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Items Table -->
    <table class="main-table">
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
                <td><?php echo htmlspecialchars($item['Rate']); ?></td>
                <td><?php echo number_format($item['Total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            
            <?php 
            // Add empty rows
            for ($i = $rowCount; $i < 15; $i++): 
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
    
    <!-- Totals -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>TOTAL</td>
                <td style="text-align: right;">Rs. <?php echo number_format($invoice['SubTotal'], 2); ?></td>
            </tr>
            <tr>
                <td>GRAND TOTAL</td>
                <td style="text-align: right;">Rs. <?php echo number_format($invoice['GrandTotal'], 2); ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="signature-line">
            Signature
        </div>
        
        <div class="thank-you">
            THANK YOU FOR YOUR BUSINESS!
        </div>
        
        <div class="company-details">
            Al-Iq Business Plaza 73 Jan Milan Rampur Road Lahore Ph:042-35579097<br>
            Mob: 0321-4141417 / 0333-481678 • Web: sadiqprinting@gmail.com
        </div>
    </div>
</div>

<!-- Second Page - Detail Format (Like Screenshot) -->
<div class="invoice-container invoice-detail">
    <div class="detail-header">
        <h2>Invoice Details</h2>
    </div>
    
    <!-- Invoice Information Section -->
    <div class="detail-section">
        <h3>Invoice Information</h3>
        <div class="detail-info">
            <div><strong>Invoice No:</strong> <?php echo htmlspecialchars($invoice['InvoiceNo']); ?></div>
            <div><strong>Date:</strong> <?php echo date('n/j/Y', strtotime($invoice['InvoiceDate'])); ?></div>
            <div><strong>PO No:</strong> <?php echo htmlspecialchars($invoice['PONo']); ?></div>
            <div><strong>Subject:</strong> <?php echo htmlspecialchars($invoice['InvoiceSubject']); ?></div>
        </div>
    </div>
    
    <!-- Customer Information Section -->
    <div class="detail-section">
        <h3>Customer Information</h3>
        <div class="detail-info">
            <div><strong>Name:</strong> <?php echo htmlspecialchars($invoice['CustomerName']); ?></div>
            <div><strong>Phone:</strong> <?php echo htmlspecialchars($invoice['Phone']); ?></div>
            <div><strong>Email:</strong> <?php echo htmlspecialchars($invoice['Email']); ?></div>
            <div><strong>Address:</strong> <?php echo htmlspecialchars($invoice['Address']); ?></div>
        </div>
    </div>
    
    <!-- Invoice Items Section -->
    <div class="detail-section">
        <h3>Invoice Items</h3>
        <table class="detail-items-table">
            <thead>
                <tr>
                    <th>JOB #</th>
                    <th>DETAIL</th>
                    <th>MEDIA</th>
                    <th>DIMENSIONS</th>
                    <th>QTY</th>
                    <th>SQFT</th>
                    <th>RATE</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['JobNo']); ?></td>
                    <td><?php echo htmlspecialchars($item['Detail']); ?></td>
                    <td><?php echo htmlspecialchars($item['Media']); ?></td>
                     <td><?php echo rtrim(rtrim($item['Width'], '0'), '.') . ' × ' . rtrim(rtrim($item['Height'], '0'), '.'); ?></td>
                    <td><?php echo htmlspecialchars($item['Qty']); ?></td>
                     <td><?php echo rtrim(rtrim($item['Sqft'], '0'), '.'); ?></td>
                    <td><?php echo number_format($item['Rate'], 2); ?></td>
                    <td><?php echo number_format($item['Total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Totals Section -->
    <div class="detail-totals">
        <div class="detail-totals-grid">
            <div class="detail-totals-item">
                <span><strong>Sub Total:</strong></span>
                <span><?php echo number_format($invoice['SubTotal'], 2); ?></span>
            </div>
            <div class="detail-totals-item">
                <span><strong>Total GST:</strong></span>
                <span><?php echo number_format($invoice['TotalGST'], 2); ?></span>
            </div>
            <div class="detail-totals-item">
                <span><strong>Advance:</strong></span>
                <span><?php echo number_format($invoice['Advance'], 2); ?></span>
            </div>
            <div class="detail-totals-item">
                <span><strong>Total NTN:</strong></span>
                <span><?php echo number_format($invoice['TotalNTR'], 2); ?></span>
            </div>
            <div class="detail-totals-item grand">
                <span>Grand Total:</span>
                <span><?php echo number_format($invoice['GrandTotal'], 2); ?></span>
            </div>
        </div>
    </div>
</div>

</body>
</html>