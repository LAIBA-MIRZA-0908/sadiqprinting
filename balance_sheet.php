<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

// Enable error reports
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Balance Sheet</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        @media print {
            #printBtn, .no-print {
                display: none;
            }
            body {
                background: #ffffff !important;
            }
        }

           @media print {
            #printBtn { display: none; }
            .no-print { display: none; }
            body { background: white !important; }

            /* Hide header/menu for printing */
            header, nav, .menu, #topbar { 
                display: none !important;
            }

            /* Make content full width */
            .max-w-7xl { max-width: 100% !important; margin: 0; }
            .shadow, .rounded { box-shadow: none !important; border-radius: 0 !important; }
            .bg-white { background: white !important; }
        }
    </style>
</head>

<body class="bg-gray-100">

<div class="max-w-6xl mx-auto mt-6 bg-white p-8 shadow-lg rounded">

    <h1 class="text-3xl font-bold text-center mb-6">📘 Balance Sheet</h1>

    <button id="printBtn" onclick="window.print()" 
        class="mb-6 bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">
        🖨 Print Balance Sheet
    </button>

<?php
// --------------------------------------
// 1. FETCH ALL ACCOUNTS WITH BALANCES
// --------------------------------------
$sql = "
    SELECT 
        a.id, 
        a.name, 
        a.type,
        a.category,
        a.opening_balance,
        COALESCE(SUM(jd.debit - jd.credit), 0) AS trans_balance
    FROM accounts a
    LEFT JOIN journal_details jd ON jd.account_id = a.id
    GROUP BY a.id
    ORDER BY a.type, a.name
";

$result = $conn->query($sql);

// Prepare sections
$assets = [];
$liabilities = [];
$equity = [];

while ($row = $result->fetch_assoc()) {

    $balance = $row['opening_balance'] + $row['trans_balance'];

    if ($row['type'] === 'Asset') {
        $assets[] = ['name' => $row['name'], 'balance' => $balance];
    } 
    elseif ($row['type'] === 'Liability') {
        $liabilities[] = ['name' => $row['name'], 'balance' => $balance];
    } 
    else if ($row['type'] === 'Equity') {
        $equity[] = ['name' => $row['name'], 'balance' => $balance];
    }
}

// Calculate totals
$total_assets = array_sum(array_column($assets, 'balance'));
$total_liabilities = array_sum(array_column($liabilities, 'balance'));
$total_equity = array_sum(array_column($equity, 'balance'));

?>

<!-- ============ BALANCE SHEET TABLE ============ -->
<div class="grid grid-cols-2 gap-10">

    <!-- ASSETS -->
    <div>
        <h2 class="text-xl font-bold mb-3 border-b pb-1 text-blue-700">Assets</h2>
        <table class="w-full text-sm border">
            <tbody>

            <?php foreach ($assets as $a): ?>
                <tr>
                    <td class="border p-2"><?= $a['name'] ?></td>
                    <td class="border p-2 text-right font-semibold">
                        <?= number_format($a['balance'], 2) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

                <tr class="bg-gray-100 font-bold text-lg">
                    <td class="border p-2">Total Assets</td>
                    <td class="border p-2 text-right"><?= number_format($total_assets, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- LIABILITIES & EQUITY -->
    <div>
        <h2 class="text-xl font-bold mb-3 border-b pb-1 text-red-700">Liabilities</h2>
        <table class="w-full text-sm border mb-6">
            <tbody>

            <?php foreach ($liabilities as $l): ?>
                <tr>
                    <td class="border p-2"><?= $l['name'] ?></td>
                    <td class="border p-2 text-right font-semibold">
                        <?= number_format($l['balance'], 2) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

                <tr class="bg-gray-100 font-bold text-lg">
                    <td class="border p-2">Total Liabilities</td>
                    <td class="border p-2 text-right"><?= number_format($total_liabilities, 2) ?></td>
                </tr>
            </tbody>
        </table>

        <h2 class="text-xl font-bold mb-3 border-b pb-1 text-green-700">Equity</h2>
        <table class="w-full text-sm border">
            <tbody>

            <?php foreach ($equity as $e): ?>
                <tr>
                    <td class="border p-2"><?= $e['name'] ?></td>
                    <td class="border p-2 text-right font-semibold">
                        <?= number_format($e['balance'], 2) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

                <tr class="bg-gray-100 font-bold text-lg">
                    <td class="border p-2">Total Equity</td>
                    <td class="border p-2 text-right"><?= number_format($total_equity, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<hr class="my-6">

<!-- BALANCE CHECK -->
<div class="text-center text-xl font-bold">
    <p>Total Liabilities + Equity = <span class="text-blue-700">
        <?= number_format($total_liabilities + $total_equity, 2) ?>
    </span></p>

    <p>Total Assets = <span class="text-green-700">
        <?= number_format($total_assets, 2) ?>
    </span></p>

    <?php if ($total_assets == ($total_liabilities + $total_equity)): ?>
        <p class="text-green-600 mt-2">✔ Balance Sheet is Balanced</p>
    <?php else: ?>
        <p class="text-red-600 mt-2">❌ Not Balanced! Check entries.</p>
    <?php endif; ?>
</div>

</div>

</body>
</html>
