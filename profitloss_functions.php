<?php
include 'db_connection.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'getProfitLoss') {
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    $start = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $end = date("Y-m-t", strtotime($start));

    // Income
    $incomeQ = "
        SELECT a.account_name, 
               SUM(jd.credit - jd.debit) AS amount
        FROM journal_details jd
        JOIN journal_entries je ON jd.journal_id = je.journal_id
        JOIN accounts a ON jd.account_id = a.account_id
        WHERE a.account_type = 'Income'
          AND je.entry_date BETWEEN '$start' AND '$end'
        GROUP BY a.account_id
        ORDER BY a.account_name
    ";
    $income = [];
    $res = mysqli_query($conn, $incomeQ);
    while ($r = mysqli_fetch_assoc($res)) $income[] = ['name' => $r['account_name'], 'amount' => $r['amount']];

    // Expense
    $expenseQ = "
        SELECT a.account_name, 
               SUM(jd.debit - jd.credit) AS amount
        FROM journal_details jd
        JOIN journal_entries je ON jd.journal_id = je.journal_id
        JOIN accounts a ON jd.account_id = a.account_id
        WHERE a.account_type = 'Expense'
          AND je.entry_date BETWEEN '$start' AND '$end'
        GROUP BY a.account_id
        ORDER BY a.account_name
    ";
    $expense = [];
    $res2 = mysqli_query($conn, $expenseQ);
    while ($r = mysqli_fetch_assoc($res2)) $expense[] = ['name' => $r['account_name'], 'amount' => $r['amount']];

    echo json_encode([
        'status' => 'success',
        'month_name' => date('F', strtotime($start)),
        'year' => $year,
        'income' => $income,
        'expense' => $expense
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
