<?php
include 'db_connection.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'getEntries') {
    $from = $_POST['fromDate'] ?? '';
    $to = $_POST['toDate'] ?? '';

    $q = "SELECT journal_id, journal_no, entry_date, description 
          FROM journal_entries 
          WHERE entry_date BETWEEN '$from' AND '$to' 
          ORDER BY entry_date DESC";

    $entries = [];
    $res = mysqli_query($conn, $q);
    while ($r = mysqli_fetch_assoc($res)) $entries[] = $r;

    echo json_encode(['status' => 'success', 'entries' => $entries]);
    exit;
}

if ($action === 'getDetails') {
    $jid = intval($_POST['journal_id']);
    $q = "SELECT a.account_name, jd.debit, jd.credit
          FROM journal_details jd
          JOIN accounts a ON jd.account_id = a.account_id
          WHERE jd.journal_id = $jid";

    $details = [];
    $res = mysqli_query($conn, $q);
    while ($r = mysqli_fetch_assoc($res)) $details[] = $r;

    echo json_encode(['status' => 'success', 'details' => $details]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
