<?php
include 'db_connection.php';

$CustomerID = intval($_GET['CustomerID'] ?? 0);

if (!$CustomerID) {
    die("Invalid customer.");
}

// Get customer info
$stmt = $conn->prepare("SELECT CustomerName, account_id FROM tblcustomers WHERE CustomerID=?");
$stmt->bind_param("i", $CustomerID);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

$CustomerName = $customer['CustomerName'];
$AccountID = $customer['account_id'];

// Fetch last payment (latest journal entry involving this account)
$query = "
    SELECT je.id AS JournalID, je.entry_date, je.description, 
           jd.debit, jd.credit
    FROM journal_details jd
    JOIN journal_entries je ON jd.journal_id = je.id
    WHERE jd.account_id = ?
    ORDER BY je.id DESC
    LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $AccountID);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("<p style='font-family:sans-serif;text-align:center;margin-top:50px;'>No payment found for this customer.</p>");
}

// Determine payment amount (credit side = received)
$amount = $payment['credit'] > 0 ? $payment['credit'] : $payment['debit'];
$amount = number_format($amount, 2);
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment Receipt</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    font-size: 13px;
}
.receipt {
    width: 600px;
    border: 1px solid #000;
    padding: 10px;
    margin: auto;
}
h3 {
    text-align: center;
    margin-bottom: 10px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
td {
    padding: 5px 0;
}
.text-right {
    text-align: right;
}
.footer {
    margin-top: 15px;
    text-align: center;
    font-size: 11px;
    border-top: 1px dashed #000;
    padding-top: 5px;
}
@media print {
    button { display: none; }
}
</style>
</head>
<body>
   
<div class="receipt">
    <div style="text-align:center; margin-bottom:10px;">
    <img src="alsadiqlogo.jpg" alt="Company Logo" style="height:70px;">
</div>

    <h3>Payment Receipt</h3>
    <table>
        <tr><td><strong>Date:</strong></td><td class="text-right"><?php echo $payment['entry_date']; ?></td></tr>
        <tr><td><strong>Customer:</strong></td><td class="text-right"><?php echo htmlspecialchars($CustomerName); ?></td></tr>
        <tr><td><strong>Amount:</strong></td><td class="text-right"><?php echo $amount; ?></td></tr>
        <tr><td><strong>Description:</strong></td><td class="text-right"><?php echo htmlspecialchars($payment['description']); ?></td></tr>
    </table>
    <div class="footer">
        Thank you for your payment.<br>
        Universal Projects Co.
    </div>
</div>
<div style="text-align:center;margin-top:10px;">
    <button onclick="window.print()">Print</button>
</div>
</body>
</html>
