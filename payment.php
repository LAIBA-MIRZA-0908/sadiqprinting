<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
// add_invoice.php
include 'header.php';
include 'menu.php';
 // your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_payment'])) {
    $CustomerID = intval($_POST['CustomerID']);
    $PaymentMode = trim($_POST['PaymentMode']);
    $PaymentDate = $_POST['PaymentDate'];
    $Amount = floatval($_POST['Amount']);
    $Description = trim($_POST['Description']);

    // Get customer account_id
    $stmt = $conn->prepare("SELECT account_id, CustomerName FROM tblcustomers WHERE CustomerID=?");
    $stmt->bind_param("i", $CustomerID);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    $CustomerAccountID = $result['account_id'];
    $CustomerName = $result['CustomerName'];

    // Determine cash/bank account
    $CashAccountID = 1; // default "Cash in Hand"
    if ($PaymentMode === 'JazzCash') $CashAccountID = 2; // example: JazzCash account
    if ($PaymentMode === 'Bank Transfer') $CashAccountID = 3; // example: Bank account

    // Create journal entry
    $EntryDate = $PaymentDate;
    $Narration = "Payment received from $CustomerName via $PaymentMode";
    $entry_sql = "INSERT INTO journal_entries (entry_date, description, created_at)
                  VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($entry_sql);
    $stmt->bind_param("ss", $EntryDate, $Narration);
    $stmt->execute();
    $JournalID = $stmt->insert_id;

    // Debit: Cash/Bank | Credit: Customer
    $detail_sql = "INSERT INTO journal_details (journal_id, account_id, debit, credit)
                   VALUES (?, ?, ?, ?), (?, ?, ?, ?)";
    $stmt = $conn->prepare($detail_sql);
    $zero = 0.00;
    $stmt->bind_param(
        "iidididi",
        $JournalID, $CashAccountID, $Amount, $zero,
        $JournalID, $CustomerAccountID, $zero, $Amount
    );
    $stmt->execute();

    echo "<script>alert('Payment recorded successfully!'); window.location.href='payment.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-4xl mx-auto mt-6 bg-white shadow-md rounded-lg p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Customer Payment</h2>

    <form method="POST" class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
            <select name="CustomerID" id="CustomerID" class="w-full border rounded-md p-2" required>
                <option value="">Select Customer</option>
                <?php
                $customers = $conn->query("SELECT CustomerID, CustomerName FROM tblcustomers ORDER BY CustomerName ASC");
                while ($row = $customers->fetch_assoc()) {
                    echo "<option value='{$row['CustomerID']}'>{$row['CustomerName']}</option>";
                }
                ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Mode</label>
            <select name="PaymentMode" class="w-full border rounded-md p-2" required>
                <option value="Cash">Cash</option>
                <option value="JazzCash">JazzCash</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
            <input type="date" name="PaymentDate" value="<?php echo date('Y-m-d'); ?>" class="w-full border rounded-md p-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
            <input type="number" name="Amount" step="0.01" class="w-full border rounded-md p-2" required>
        </div>

        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input type="text" name="Description" class="w-full border rounded-md p-2" placeholder="Optional notes...">
        </div>

        <div class="col-span-2 flex space-x-4 mt-4">
    <button type="submit" name="save_payment" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
        Save
    </button>
    <button type="button" id="printBtn" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700">
        Print
    </button>
</div>

    </form>
</div>

<!-- Customer Journal History -->
<div class="max-w-4xl mx-auto mt-6 bg-white shadow-md rounded-lg p-4">
    <h3 class="text-md font-semibold text-gray-800 mb-3">Customer Payment History</h3>
    <!-- Customer Balance Info -->
<div id="customerBalance" class="mb-3 text-gray-800 text-sm font-medium">
    Select a customer to view current balance...
</div>
    <table class="w-full text-sm border">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-2 border">Date</th>
                <th class="p-2 border">Narration</th>
                <th class="p-2 border">Debit</th>
                <th class="p-2 border">Credit</th>
            </tr>
        </thead>
        <tbody id="customerHistory">
            <tr><td colspan="4" class="text-center p-3 text-gray-500">Select a customer to view history</td></tr>
        </tbody>
    </table>
</div>

<script>
$('#CustomerID').change(function() {
    let customerId = $(this).val();
    if (!customerId) return;

  $.ajax({
    url: 'get_customer_history.php',
    method: 'POST',
    dataType: 'json',
    data: { CustomerID: customerId },
    success: function(res) {
        $('#customerHistory').html(res.rows);
        $('#customerBalance').html("Current Balance: <strong>" + res.balance + "</strong>");
    }
});
});
$('#printBtn').click(function() {
    let customerId = $('#CustomerID').val();
    if (!customerId) {
        alert("Please select a customer first.");
        return;
    }
    window.open('print_payment.php?CustomerID=' + customerId, '_blank');
});

</script>

</body>
</html>
