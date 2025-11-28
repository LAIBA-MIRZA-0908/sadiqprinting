<?php
include 'db_connect.php';
include 'functions.php';

$CustomerID = $_POST['CustomerID'] ?? 0;
if (!$CustomerID) sendJsonResponse(false, 'Invalid customer');

$cust = $conn->query("SELECT account_id FROM tblcustomers WHERE CustomerID='$CustomerID'")->fetch_assoc();
$account_id = $cust['account_id'];

$query = "SELECT je.entry_date AS EntryDate, je.description AS Description,
          jd.debit AS Debit, jd.credit AS Credit
          FROM journal_details jd
          JOIN journal_entries je ON jd.journal_id = je.id
          WHERE jd.account_id = '$account_id'
          ORDER BY je.entry_date DESC";

$res = $conn->query($query);
$ledger = [];
while ($row = $res->fetch_assoc()) $ledger[] = $row;

sendJsonResponse(true, 'Ledger loaded', ['ledger' => $ledger]);
