<?php
include 'db_connection.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'getBalanceSheet') {
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    $start = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $end = date("Y-m-t", strtotime($start));

    // Assets
    $assetsQ = "
        SELECT a.account_name, 
               SUM(jd.debit - jd.credit) AS balance
        FROM journal_details jd
        JOIN journal_entries je ON jd.journal_id = je.journal_id
        JOIN accounts a ON jd.account_id = a.account_id
        WHERE a.account_type = 'Asset'
          AND je.entry_date <= '$end'
        GROUP BY a.account_id
        ORDER BY a.account_name
    ";
    $assets = [];
    $res = mysqli_query($conn, $assetsQ);
    while ($r = mysqli_fetch_assoc($res)) $assets[] = ['name' => $r['account_name'], 'balance' => $r['balance']];

    // Liabilities & Equity
    $leQ = "
        SELECT a.account_name, 
               SUM(jd.credit - jd.debit) AS balance
        FROM journal_details jd
        JOIN journal_entries je ON jd.journal_id = je.journal_id
        JOIN accounts a ON jd.account_id = a.account_id
        WHERE a.account_type IN ('Liability', 'Equity')
          AND je.entry_date <= '$end'
        GROUP BY a.account_id
        ORDER BY a.account_name
    ";
    $liabilities = [];
    $res2 = mysqli_query($conn, $leQ);
    while ($r = mysqli_fetch_assoc($res2)) $liabilities[] = ['name' => $r['account_name'], 'balance' => $r['balance']];

    echo json_encode([
        'status' => 'success',
        'month_name' => date('F', strtotime($start)),
        'year' => $year,
        'assets' => $assets,
        'liabilities' => $liabilities
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
