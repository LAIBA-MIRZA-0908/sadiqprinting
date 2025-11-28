<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

// Show errors for debugging (optional)
error_reporting(E_ALL);
ini_set('display_errors',1);

// Default date range (this month)
$today = date("Y-m-d");
$start_default = date("Y-m-01");

$start_date = $_GET['start_date'] ?? $start_default;
$end_date   = $_GET['end_date']   ?? $today;

$start_date = $conn->real_escape_string($start_date);
$end_date   = $conn->real_escape_string($end_date);

function fmt($x){ return number_format((float)$x,2); }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Summary Report</title>

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>

          @page { size: A4; margin: 18mm; }

    /* Print-specific adjustments */
    @media print {
        #filterForm, #printBtn, .no-print, header, nav, .menu, #topbar {
            display: none !important;
        }

        body { background: #fff !important; color: #000 !important; }

        .break-inside-avoid { page-break-inside: avoid; }

        /* Full width content */
        .max-w-7xl { max-width: 100% !important; margin: 0; }
        .shadow, .rounded { box-shadow: none !important; border-radius: 0 !important; }
        .bg-white { background: #fff !important; }
    }

    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>

<body class="bg-gray-100">

<div class="max-w-7xl mx-auto mt-6 mb-12 bg-white p-8 shadow-md rounded">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">📘 Account Summary Report</h1>

        <button id="printBtn" 
                onclick="window.print()" 
                class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">
            🖨 Print A4
        </button>
    </div>

    <!-- FILTER -->
    <form id="filterForm" method="GET" class="mt-6 no-print">
        <div class="grid grid-cols-4 gap-4">

            <div>
                <label class="text-sm text-gray-700">Start Date</label>
                <input type="date" name="start_date" value="<?= $start_date ?>" class="p-2 border rounded w-full">
            </div>

            <div>
                <label class="text-sm text-gray-700">End Date</label>
                <input type="date" name="end_date" value="<?= $end_date ?>" class="p-2 border rounded w-full">
            </div>

            <div class="col-span-1 flex items-end">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Apply</button>
                <a href="account_summary.php" class="ml-3 text-gray-600 hover:underline text-sm">Reset</a>
            </div>
        </div>
    </form>

    <hr class="my-6">

    <!-- ACCOUNT SUMMARY -->
    <table class="text-sm">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-2 text-left">Account Name</th>
                <th class="p-2 text-center">Type</th>
                <th class="p-2 text-right">Opening Balance</th>
                <th class="p-2 text-right">Total Debit</th>
                <th class="p-2 text-right">Total Credit</th>
                <th class="p-2 text-right">Closing Balance</th>
            </tr>
        </thead>

        <tbody>

        <?php
        // Fetch ALL accounts
        $sql = "SELECT id, name, type, opening_balance FROM accounts ORDER BY type, name";
        $res = $conn->query($sql);

        $grand_open = $grand_debit = $grand_credit = $grand_close = 0;

        while($acc = $res->fetch_assoc()):
            
            $id = $acc['id'];
            $opening = $acc['opening_balance'];

            // Fetch totals for date range
            $sql2 = "
                SELECT 
                    COALESCE(SUM(jd.debit),0) AS t_debit,
                    COALESCE(SUM(jd.credit),0) AS t_credit
                FROM journal_details jd
                INNER JOIN journal_entries je ON jd.journal_id = je.id
                WHERE jd.account_id = $id
                AND je.entry_date BETWEEN '$start_date' AND '$end_date'
            ";

            $tr = $conn->query($sql2)->fetch_assoc();

            $debit  = $tr['t_debit'];
            $credit = $tr['t_credit'];

            // Closing balance formula:
            $closing = $opening + ($debit - $credit);

            // Totals
            $grand_open  += $opening;
            $grand_debit += $debit;
            $grand_credit+= $credit;
            $grand_close += $closing;
        ?>

            <tr>
                <td class="p-2"><?= $acc['name'] ?></td>
                <td class="p-2 text-center"><?= $acc['type'] ?></td>
                <td class="p-2 text-right"><?= fmt($opening) ?></td>
                <td class="p-2 text-right text-blue-700 font-semibold"><?= fmt($debit) ?></td>
                <td class="p-2 text-right text-red-700 font-semibold"><?= fmt($credit) ?></td>
                <td class="p-2 text-right font-bold"><?= fmt($closing) ?></td>
            </tr>

        <?php endwhile; ?>

        </tbody>

        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td class="p-2" colspan="2">TOTAL</td>
                <td class="p-2 text-right"><?= fmt($grand_open) ?></td>
                <td class="p-2 text-right"><?= fmt($grand_debit) ?></td>
                <td class="p-2 text-right"><?= fmt($grand_credit) ?></td>
                <td class="p-2 text-right"><?= fmt($grand_close) ?></td>
            </tr>
        </tfoot>
    </table>

</div>

</body>
</html>
