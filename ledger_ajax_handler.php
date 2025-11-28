<?php
require_once 'ledger_functions.php';

header('Content-Type: application/json');

$account_id = isset($_GET['account_id']) ? intval($_GET['account_id']) : 0;

if (!$account_id) {
    echo json_encode(['success' => false, 'message' => 'No account ID provided']);
    exit;
}

// Use the same function for consistency
$result = getLedgerData($account_id);
echo json_encode($result);