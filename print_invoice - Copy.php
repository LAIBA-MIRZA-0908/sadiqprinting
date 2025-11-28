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
    body {
        font-family: Arial, sans-serif;
        margin: 40px;
        color: #111;
        font-size: 16px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #333;
        padding-bottom: 15px;
        margin-bottom: 30px;
    }

    .header .logo img {
        height: 100px;
    }

    .header .company-info {
        text-align: right;
    }

    .header h1 {
        margin: 0;
        font-size: 28px;
        color: #222;
    }

    .invoice-meta {
        margin-bottom: 25px;
    }

    .invoice-meta div {
        margin-bottom: 8px;
        font-size: 16px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 16px;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 12px;
        text-align: left;
    }

    th {
        background: #f2f2f2;
    }

    .totals {
        margin-top: 25px;
        width: 100%;
        border-top: 2px solid #333;
        padding-top: 12px;
        font-size: 16px;
    }

    .totals div {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .grand-total {
        font-size: 22px;
        font-weight: bold;
        color: #000;
    }

    .footer {
        text-align: center;
        font-size: 14px;
        color: #555;
        margin-top: 50px;
        border-top: 1px solid #ccc;
        padding-top: 15px;
    }

    @media print {
        @page {
            size: A3 landscape;
            margin: 2cm;
        }
        body {
            margin: 0;
        }
        .no-print {
            display: none;
        }
        table th, table td {
            font-size: 18px;
            padding: 10px;
        }
    }

    @media screen and (max-width: 1600px) {
        body {
            font-size: 14px;
        }
        table th, table td {
            padding: 8px;
        }
    }
</style>
</head>
<body>

<div class="header">
    <div class="logo">
        <img src="alsadiqlogo.jpg" alt="Company Logo">
    </div>
    <div class="company-info">
        <h1>Sadiq Printing</h1>
        <p><strong>Invoice</strong></p>
    </div>
</div>

<div class="invoice-meta">
    <div><strong>Invoice No:</strong> <?php echo htmlspecialchars($invoice['InvoiceNo']); ?></div>
    <div><strong>Date:</strong> <?php echo date('d M Y', strtotime($invoice['InvoiceDate'])); ?></div>
    <div><strong>PO No:</strong> <?php echo htmlspecialchars($invoice['PONo']); ?></div>
    <div><strong>Subject:</strong> <?php echo htmlspecialchars($invoice['InvoiceSubject']); ?></div>
</div>

<div class="invoice-meta">
    <h3>Customer Information</h3>
    <div><strong>Name:</strong> <?php echo htmlspecialchars($invoice['CustomerName']); ?></div>
    <div><strong>Phone:</strong> <?php echo htmlspecialchars($invoice['Phone']); ?></div>
    <div><strong>Email:</strong> <?php echo htmlspecialchars($invoice['Email']); ?></div>
    <div><strong>Address:</strong> <?php echo htmlspecialchars($invoice['Address']); ?></div>
</div>

<h3>Invoice Items</h3>
<table>
    <thead>
        <tr>
            <th>Job #</th>
            <th>Detail</th>
            <th>Media</th>
            <th>Dimensions</th>
            <th>Qty</th>
            <th>Sqft</th>
            <th>Rate</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['JobNo']); ?></td>
            <td><?php echo htmlspecialchars($item['Detail']); ?></td>
            <td><?php echo htmlspecialchars($item['Media']); ?></td>
            <td><?php echo htmlspecialchars($item['Width']) . ' × ' . htmlspecialchars($item['Height']); ?></td>
            <td><?php echo htmlspecialchars($item['Qty']); ?></td>
            <td><?php echo htmlspecialchars($item['Sqft']); ?></td>
            <td><?php echo htmlspecialchars($item['Rate']); ?></td>
            <td><?php echo htmlspecialchars($item['Total']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="totals">
    <div><span>Sub Total:</span> <span>Rs. <?php echo number_format($invoice['SubTotal'], 2); ?></span></div>
    <div><span>Advance:</span> <span>Rs. <?php echo number_format($invoice['Advance'], 2); ?></span></div>
    <div><span>Total GST:</span> <span>Rs. <?php echo number_format($invoice['TotalGST'], 2); ?></span></div>
    <div><span>Total NTN:</span> <span>Rs. <?php echo number_format($invoice['TotalNTR'], 2); ?></span></div>
    <div class="grand-total"><span>Grand Total:</span> <span>Rs. <?php echo number_format($invoice['GrandTotal'], 2); ?></span></div>
</div>

<div class="footer">
    <p>Thank you for your business!</p>
    <p class="no-print"><button onclick="window.print()">🖨️ Print Invoice</button></p>
</div>

</body>
</html>
