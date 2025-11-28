<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

// Show errors (remove in live)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Default date range = current month
$today = date("Y-m-d");
$start_default = date("Y-m-01");

$start_date = $_GET['start_date'] ?? $start_default;
$end_date   = $_GET['end_date']   ?? $today;
$search     = $_GET['search']     ?? "";

// sanitize
$start_date = $conn->real_escape_string($start_date);
$end_date   = $conn->real_escape_string($end_date);
$search     = $conn->real_escape_string($search);

// FETCH JOURNAL ENTRIES
$sql = "
    SELECT *
    FROM journal_entries
    WHERE entry_date BETWEEN '$start_date' AND '$end_date'
    AND (description LIKE '%$search%' OR reference_no LIKE '%$search%')
    ORDER BY entry_date DESC, id DESC
";

$entries = $conn->query($sql);

function formatAmt($n){ return number_format((float)$n, 2); }

?>

<!DOCTYPE html>
<html>
<head>
    <title>Journal Entry Viewer</title>

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

    table { border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto mt-6 bg-white p-8 shadow rounded">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">📘 Journal Entry Viewer</h1>

        <button id="printBtn" onclick="window.print()" 
            class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">
            🖨 Print (A4)
        </button>
    </div>

    <!-- Filter Form -->
    <form id="filterForm" method="GET" class="mt-6 no-print">
        <div class="grid grid-cols-4 gap-4">

            <div>
                <label class="text-sm text-gray-600">Start Date</label>
                <input type="date" name="start_date" value="<?= $start_date ?>" class="border p-2 w-full rounded">
            </div>

            <div>
                <label class="text-sm text-gray-600">End Date</label>
                <input type="date" name="end_date" value="<?= $end_date ?>" class="border p-2 w-full rounded">
            </div>

            <div class="col-span-2">
                <label class="text-sm text-gray-600">Search (Description / Reference)</label>
                <input type="text" name="search" value="<?= $search ?>" placeholder="Search..."
                    class="border p-2 w-full rounded">
            </div>
        </div>

        <div class="mt-4">
            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Apply</button>
            <a href="journal_viewer.php" class="ml-3 text-sm text-gray-600 hover:underline">Reset</a>
        </div>
    </form>

    <hr class="my-6">

    <!-- Journal Entries -->
    <?php if($entries->num_rows == 0): ?>
        <p class="text-center text-gray-500 py-10">No journal entries found for this range.</p>
    <?php else: ?>

        <?php while($row = $entries->fetch_assoc()): ?>
            <div class="mb-10 break-inside-avoid">

                <h2 class="text-lg font-bold text-blue-700 border-b pb-1">
                    JE# <?= $row['id'] ?> — <?= $row['entry_date'] ?>
                </h2>

                <p class="mt-1 text-sm text-gray-700">
                    <strong>Description:</strong> <?= $row['description'] ?: "—" ?>
                </p>
                <p class="text-sm text-gray-700">
                    <strong>Reference:</strong> <?= $row['reference_no'] ?: "—" ?>
                </p>

                <!-- Fetch Journal Details -->
                <?php
                $jid = $row['id'];
                $sql2 = "
                    SELECT jd.*, a.name AS account_name
                    FROM journal_details jd
                    INNER JOIN accounts a ON a.id = jd.account_id
                    WHERE jd.journal_id = $jid
                ";
                $details = $conn->query($sql2);
                ?>

                <table class="w-full text-sm mt-4">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-left">Account</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totD = 0; 
                        $totC = 0; 

                        while($d = $details->fetch_assoc()): 
                            $totD += $d['debit'];
                            $totC += $d['credit'];
                        ?>
                        <tr>
                            <td><?= $d['account_name'] ?></td>
                            <td class="text-right"><?= formatAmt($d['debit']) ?></td>
                            <td class="text-right"><?= formatAmt($d['credit']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>

                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td>Total</td>
                            <td class="text-right"><?= formatAmt($totD) ?></td>
                            <td class="text-right"><?= formatAmt($totC) ?></td>
                        </tr>
                    </tfoot>
                </table>

                <?php if($totD !== $totC): ?>
                    <p class="text-red-600 font-semibold mt-2">⚠ Unbalanced Entry (Debit ≠ Credit)</p>
                <?php else: ?>
                    <p class="text-green-600 font-semibold mt-2">✔ Balanced Entry</p>
                <?php endif; ?>

            </div>

            <hr class="my-6">

        <?php endwhile; ?>

    <?php endif; ?>

</div>

</body>
</html>
