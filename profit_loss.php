<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

// show errors for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// handle date range (default: start of month to today)
date_default_timezone_set('UTC'); // adjust if needed
$today = date('Y-m-d');
$start_default = date('Y-m-01');
$start_date = isset($_GET['start_date']) && $_GET['start_date'] ? $_GET['start_date'] : $start_default;
$end_date   = isset($_GET['end_date'])   && $_GET['end_date']   ? $_GET['end_date']   : $today;

// sanitize (basic)
$start_date = $conn->real_escape_string($start_date);
$end_date   = $conn->real_escape_string($end_date);

// helper to format currency
function fmt($n){
    return number_format((float)$n, 2);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Profit & Loss Statement</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @page { size: A4; margin: 18mm; }
    @media print {
        /* Hide elements not needed for print */
        #printBtn, #filterForm, .no-print, header, nav, .menu, #topbar {
            display: none !important;
        }
        body { background: #fff !important; color: #000 !important; }

        /* Optional: prevent tables from breaking across pages */
        .table-row { page-break-inside: avoid; }

        /* Optional: full width for print */
        .max-w-6xl { max-width: 100% !important; margin: 0; }
        .shadow, .rounded { box-shadow: none !important; border-radius: 0 !important; }
        .bg-white { background: white !important; }
    }
        /* small adjustments for better printed tables */
        table { border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 0.6rem; }
    </style>
</head>
<body class="bg-gray-100">

<div class="max-w-6xl mx-auto mt-6 mb-12 bg-white p-6 shadow rounded">

    <!-- Header -->
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800">Profit & Loss Statement</h1>
            <p class="text-sm text-gray-600 mt-1">Period: <span class="font-semibold"><?= htmlspecialchars($start_date) ?></span> to <span class="font-semibold"><?= htmlspecialchars($end_date) ?></span></p>
        </div>

        <div class="space-x-2">
            <button id="printBtn" onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                🖨 Print (A4)
            </button>
        </div>
    </div>

    <!-- Filter -->
    <form id="filterForm" class="mt-6 mb-6 no-print" method="get">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="border rounded p-2">
            </div>
            <div>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Apply</button>
                <a href="profit_and_loss.php" class="ml-2 text-sm text-gray-600 hover:underline">Reset</a>
            </div>
        </div>
    </form>

    <?php
    // ------------------------------
    // Fetch Income accounts and amounts
    // For Income: net = SUM(credit - debit) within period + opening_balance (if any)
    // ------------------------------
    $income_sql = "
        SELECT 
            a.id, a.name,
            COALESCE(a.opening_balance,0) AS opening_balance,
            COALESCE(SUM(jd.credit - jd.debit),0) AS trans_net
        FROM accounts a
        LEFT JOIN journal_details jd ON jd.account_id = a.id
        LEFT JOIN journal_entries je ON je.id = jd.journal_id
            AND je.entry_date BETWEEN '{$start_date}' AND '{$end_date}'
        WHERE a.type = 'Income'
        GROUP BY a.id
        ORDER BY a.name
    ";
    $income_res = $conn->query($income_sql);
    if (!$income_res) {
        echo "<div class='p-4 bg-red-50 border-l-4 border-red-400 text-red-700'>SQL Error (Income): " . htmlspecialchars($conn->error) . "</div>";
        $income_res = [];
    }

    // ------------------------------
    // Fetch Expense accounts and amounts
    // For Expense: net = SUM(debit - credit) within period + opening_balance (if any)
    // ------------------------------
    $expense_sql = "
        SELECT 
            a.id, a.name,
            COALESCE(a.opening_balance,0) AS opening_balance,
            COALESCE(SUM(jd.debit - jd.credit),0) AS trans_net
        FROM accounts a
        LEFT JOIN journal_details jd ON jd.account_id = a.id
        LEFT JOIN journal_entries je ON je.id = jd.journal_id
            AND je.entry_date BETWEEN '{$start_date}' AND '{$end_date}'
        WHERE a.type = 'Expense'
        GROUP BY a.id
        ORDER BY a.name
    ";
    $expense_res = $conn->query($expense_sql);
    if (!$expense_res) {
        echo "<div class='p-4 bg-red-50 border-l-4 border-red-400 text-red-700'>SQL Error (Expense): " . htmlspecialchars($conn->error) . "</div>";
        $expense_res = [];
    }

    // Build arrays for display and totals
    $incomes = [];
    $expenses = [];
    $total_income = 0.0;
    $total_expense = 0.0;

    if ($income_res && $income_res->num_rows) {
        while ($r = $income_res->fetch_assoc()) {
            // income amount as positive number
            $amount = floatval($r['opening_balance']) + floatval($r['trans_net']);
            // some income may be negative due to returns; include as-is
            $incomes[] = ['name' => $r['name'], 'amount' => $amount];
            $total_income += $amount;
        }
    }

    if ($expense_res && $expense_res->num_rows) {
        while ($r = $expense_res->fetch_assoc()) {
            $amount = floatval($r['opening_balance']) + floatval($r['trans_net']);
            $expenses[] = ['name' => $r['name'], 'amount' => $amount];
            $total_expense += $amount;
        }
    }

    // Net Profit (Income - Expense)
    $net = $total_income - $total_expense;
    ?>

    <!-- Content -->
    <div class="grid grid-cols-2 gap-8">

        <!-- INCOME -->
        <div class="bg-white rounded shadow-sm p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Income</h3>

            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-2">Account</th>
                        <th class="text-right p-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($incomes) === 0): ?>
                        <tr class="table-row"><td class="p-2" colspan="2">No income accounts or transactions for this period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($incomes as $inc): ?>
                            <tr class="table-row">
                                <td class="p-2"><?= htmlspecialchars($inc['name']) ?></td>
                                <td class="p-2 text-right font-medium"><?= fmt($inc['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-bold">
                        <td class="p-2">Total Income</td>
                        <td class="p-2 text-right"><?= fmt($total_income) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- EXPENSE -->
        <div class="bg-white rounded shadow-sm p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Expenses</h3>

            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-2">Account</th>
                        <th class="text-right p-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($expenses) === 0): ?>
                        <tr class="table-row"><td class="p-2" colspan="2">No expense accounts or transactions for this period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $exp): ?>
                            <tr class="table-row">
                                <td class="p-2"><?= htmlspecialchars($exp['name']) ?></td>
                                <td class="p-2 text-right font-medium"><?= fmt($exp['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-bold">
                        <td class="p-2">Total Expenses</td>
                        <td class="p-2 text-right"><?= fmt($total_expense) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    <!-- Summary -->
    <div class="mt-6 bg-white p-4 rounded shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Calculated for: <strong><?= htmlspecialchars($start_date) ?></strong> to <strong><?= htmlspecialchars($end_date) ?></strong></p>
            </div>
            <div class="text-right">
                <p class="text-lg">Net Profit / (Loss)</p>
                <p class="text-3xl font-extrabold <?= $net >= 0 ? 'text-green-600' : 'text-red-600' ?>"><?= fmt($net) ?></p>
                <p class="text-sm text-gray-500 mt-1">Total Income: <?= fmt($total_income) ?> &nbsp; | &nbsp; Total Expenses: <?= fmt($total_expense) ?></p>
            </div>
        </div>
    </div>

    <p class="mt-4 text-xs text-gray-500">Note: Amounts are calculated as follows — Income accounts use <code>credit - debit</code> within the selected period, Expense accounts use <code>debit - credit</code>. Opening balances (if present on account record) are included. If you want revenue recognition rules or other adjustments, tell me and I'll add them.</p>

</div>

</body>
</html>
