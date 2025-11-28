<?php
include 'db_connect.php';
include 'functions.php'; // your sendJsonResponse etc.

$CustomerID = $_POST['CustomerID'] ?? '';
$PaymentMode = $_POST['PaymentMode'] ?? '';
$PaymentDate = $_POST['PaymentDate'] ?? date('Y-m-d');
$Amount = floatval($_POST['Amount'] ?? 0);
$Description = trim($_POST['Description'] ?? '');

if (!$CustomerID || !$Amount) {
    sendJsonResponse(false, "Customer and Amount are required");
}

$conn->begin_transaction();

// get customer account id
$cust = $conn->query("SELECT account_id FROM tblcustomers WHERE CustomerID='$CustomerID'")->fetch_assoc();
$customerAccount = $cust['account_id'];
$cashAccount = 1; // cash in hand

// insert into journal_entries
$stmt = $conn->prepare("INSERT INTO journal_entries (entry_date, description) VALUES (?, ?)");
$stmt->bind_param('ss', $PaymentDate, $Description);
$stmt->execute();
$JournalID = $stmt->insert_id;

// debit cash
$stmt2 = $conn->prepare("INSERT INTO journal_details (journal_id, account_id, debit, credit) VALUES (?, ?, ?, 0)");
$stmt2->bind_param('iid', $JournalID, $cashAccount, $Amount);
$stmt2->execute();

// credit customer
$stmt3 = $conn->prepare("INSERT INTO journal_details (journal_id, account_id, debit, credit) VALUES (?, ?, 0, ?)");
$stmt3->bind_param('iid', $JournalID, $customerAccount, $Amount);
$stmt3->execute();

$conn->commit();

sendJsonResponse(true, "Payment recorded successfully", ['JournalID' => $JournalID]);
