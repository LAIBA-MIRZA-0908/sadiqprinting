<?php
include 'db_connection.php';

if (!isset($_GET['id'])) {
    die("Invalid Request!");
}

$jobOrderNo = intval($_GET['id']);

// Fetch Job Order Data
$sql = "SELECT * FROM job_orders WHERE JobOrderNo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $jobOrderNo);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Job Order not found!");
}

// Fetch Job Order Details (Items)
$sql_details = "SELECT * FROM job_order_details WHERE JobOrderNo = ? ORDER BY SrNo";
$stmt_details = $conn->prepare($sql_details);
$stmt_details->bind_param("i", $jobOrderNo);
$stmt_details->execute();
$result_details = $stmt_details->get_result();
$details = $result_details->fetch_all(MYSQLI_ASSOC);

// Check if Designer column exists, if not add it
$check_column = $conn->query("SHOW COLUMNS FROM job_orders LIKE 'Designer'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE job_orders ADD COLUMN Designer VARCHAR(100) DEFAULT ''");
}

$stmt->close();
$stmt_details->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Order #<?php echo htmlspecialchars($order['JobOrderNo']); ?></title>
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
        .job-order-container {
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
            margin-bottom: 20px;
        }

        .logo-section {
            flex: 0 0 25%;
        }

        .logo-section img {
            height: 150px;
            width: auto;
            display: block;
        }

        .job-title-section {
            flex: 0 0 70%;
            text-align: right;
        }

        .header-top {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }

        .job-title-section h1 {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 6px;
            margin-bottom: 15px;
            line-height: 0.9;
        }

        .job-meta {
            text-align: left;
            font-size: 12px;
            margin-top: 10px;
            display: inline-block;
            border: 1px solid #000;
        }

        .job-meta-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #000;
        }

        .job-meta-item:last-child {
            border-bottom: none;
        }

        .job-meta-item strong {
            font-weight: bold;
            padding: 6px 10px;
            min-width: 80px;
            background-color: #f5f5f5;
            border-right: 1px solid #000;
        }

        .job-meta-value {
            padding: 6px 10px;
            background-color: #fff;
            min-width: 180px;
        }

        /* Customer Section */
        .customer-section {
            margin-bottom: 5px;
            padding: 8px 0 5px 0;
            border-bottom: 1px solid #000;
        }

        .customer-section p {
            font-size: 13px;
            margin: 3px 0;
        }

        .customer-section strong {
            font-weight: bold;
        }

        /* Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-top: 5px;
        }

        .details-table th {
            background-color: #d0d0d0;
            border: 1px solid #000;
            padding: 10px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        .details-table td {
            border: 1px solid #000;
            padding: 8px 6px;
            text-align: center;
            font-size: 11px;
            height: 30px;
        }

        .details-table td:nth-child(2) {
            text-align: left;
        }

        /* Empty rows */
        .empty-row {
            height: 30px;
        }

        .empty-row td {
            border: 1px solid #000;
        }

        /* Red Signature Bar */
        .signature-bar {
            background: #ff4444;
            height: 25px;
            width: 100%;
            margin: 20px 0 15px 0;
            border-radius: 8px;
        }

        /* Signatures Section */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        .signature-item {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 8px;
            font-size: 12px;
        }

        /* Spacer to push footer to bottom */
        .spacer {
            flex-grow: 1;
        }

        /* Thank You Section */
        .thank-you {
            text-align: center;
            margin: 5px 0 3px 0;
            font-weight: bold;
            font-size: 13px;
        }

        /* Contact Info */
        .contact-info {
            text-align: center;
            font-size: 9px;
            color: #000;
            margin-top: 2px;
            padding-top: 3px;
            line-height: 1.3;
        }

        .contact-info p {
            margin: 3px 0;
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

            .job-order-container {
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
    <button class="print-btn" onclick="window.print()">🖨️ Print Job Order</button>
</div>

<!-- Job Order Container -->
<div class="job-order-container">
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <img src="alsadiqlogo.jpg" alt="Al-Sadiq Logo">
        </div>
        
        <div class="job-title-section">
            <div class="header-top">November 5, 2025<br>12:13AM</div>
            <h1>JOB<br>ORDER</h1>
            <div class="job-meta">
                <div class="job-meta-item">
                    <strong>Sr No:</strong>
                    <div class="job-meta-value"><?php echo $order['JobOrderNo']; ?></div>
                </div>
                <div class="job-meta-item">
                    <strong>Dated:</strong>
                    <div class="job-meta-value"><?php echo date('F j, Y', strtotime($order['OrderDate'])); ?></div>
                </div>
                <div class="job-meta-item">
                    <strong>Designer:</strong>
                    <div class="job-meta-value"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['CustomerName']); ?></p>
    </div>

    <!-- Order Details Table -->
    <table class="details-table">
        <thead>
            <tr>
                <th>Sr#</th>
                <th>Description</th>
                <th>Media</th>
                <th>Width</th>
                <th>Height</th>
                <th>QTY</th>
                <th>Sqft</th>
                <th>Ring</th>
                <th>Pkt</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $rowCount = 0;
            foreach ($details as $detail): 
                $rowCount++;
            ?>
            <tr>
                <td><?php echo $detail['SrNo']; ?></td>
                <td><?php echo htmlspecialchars($detail['Detail']); ?></td>
                <td><?php echo htmlspecialchars($detail['Media']); ?></td>
                <td><?php echo rtrim(rtrim($detail['Width'], '0'), '.'); ?></td>
                <td><?php echo rtrim(rtrim($detail['Height'], '0'), '.'); ?></td>
                <td><?php echo $detail['Qty']; ?></td>
                <td><?php echo rtrim(rtrim($detail['Sqft'], '0'), '.'); ?></td>
                <td><?php echo $detail['Ring'] ? $detail['Ring'] : ''; ?></td>
                <td><?php echo $detail['Pocket'] ? $detail['Pocket'] : ''; ?></td>
            </tr>
            <?php endforeach; ?>
            
            <?php 
            // Add empty rows to fill the table (image shows approximately 15 rows)
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

    <!-- Signatures Section -->
    <div class="signatures">
        <div class="signature-item">
            <div class="signature-line">
                Client Sign
            </div>
        </div>
    </div>

    <!-- Spacer to push footer to bottom -->
    <div class="spacer"></div>

    <!-- Thank You Section -->
    <div class="thank-you">
        THANK YOU FOR YOUR BUSINESS!
    </div>

    <!-- Contact Information -->
    <div class="contact-info">
        <p>43-G Shaama Plaza 71-km Main Ferozepur Road Lahore Ph:042-35979297</p>
        <p>Mob: 0321-4527417, 0322-4876106 E-Mail: alsadiqgraphics@gmail.com</p>
    </div>
</div>

</body>
</html>