<?php
include 'db_connection.php';

// Get invoice ID safely from URL
$invoiceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if valid ID provided
if ($invoiceId <= 0) {
    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px; color:red;'>❌ Invalid or missing invoice ID.</div>");
}

// Fetch invoice details along with customer info
$sql = "SELECT i.*, c.CustomerName, c.Phone AS CustomerPhone, c.Address AS CustomerAddress
        FROM tblinvoices i
        LEFT JOIN tblcustomers c ON i.CustomerID = c.CustomerID
        WHERE i.InvoiceID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoiceId);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();

// If invoice not found
if (!$invoice) {
    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px; color:red;'>⚠️ Invoice not found!</div>");
}

// Fetch invoice items
$sqlItems = "SELECT * FROM tblinvoice_details WHERE InvoiceID = ?";
$stmt2 = $conn->prepare($sqlItems);
$stmt2->bind_param("i", $invoiceId);
$stmt2->execute();
$items = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo htmlspecialchars($invoice['InvoiceNo']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-lg p-8">
        <!-- Header -->
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <img src="alsadiqlogo.jpg" alt="Logo" class="h-16">
            <div class="text-right">
                <h1 class="text-3xl font-bold text-gray-800">Sadiq Printing</h1>
                <p class="text-gray-600">Professional Printing Services</p>
                <p class="text-gray-500 text-sm">www.sadiqprinting.com</p>
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="flex justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Invoice #: <?php echo htmlspecialchars($invoice['InvoiceNo']); ?></h2>
                <p class="text-gray-600">Date: <?php echo date('d M Y', strtotime($invoice['InvoiceDate'])); ?></p>
            </div>
            <div class="text-right">
                <h3 class="font-semibold text-gray-700">Status:</h3>
                <p class="text-<?php echo ($invoice['Status'] == 'Paid' ? 'green' : 'red'); ?>-600 font-medium">
                    <?php echo htmlspecialchars($invoice['Status']); ?>
                </p>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Customer Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($invoice['CustomerName'] ?? 'N/A'); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($invoice['CustomerPhone'] ?? 'N/A'); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($invoice['CustomerAddress'] ?? 'N/A'); ?></p>
        </div>

        <!-- Invoice Items Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border text-left text-gray-600 text-sm">Job #</th>
                        <th class="px-4 py-2 border text-left text-gray-600 text-sm">Description</th>
                        <th class="px-4 py-2 border text-right text-gray-600 text-sm">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $items->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['JobNo']); ?></td>
                            <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['Detail']); ?></td>
                            <td class="px-4 py-2 border text-right"><?php echo number_format($row['Total'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="mt-8 text-right space-y-1">
            <p><strong>Sub Total:</strong> Rs. <?php echo number_format($invoice['SubTotal'], 2); ?></p>
            <p><strong>GST:</strong> Rs. <?php echo number_format($invoice['TotalGST'], 2); ?></p>
            <p><strong>Grand Total:</strong> <span class="font-bold text-xl text-gray-800">Rs. <?php echo number_format($invoice['GrandTotal'], 2); ?></span></p>
        </div>

        <!-- Footer -->
        <div class="text-center mt-10 pt-4 border-t text-gray-600 text-sm">
            Thank you for choosing <b>Sadiq Printing</b>.  
            <br>For queries, contact us at <span class="text-blue-600">info@sadiqprinting.com</span>
        </div>
    </div>
</body>
</html>
