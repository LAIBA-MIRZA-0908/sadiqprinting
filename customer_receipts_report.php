<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

// Default filter (this month)
$today = date("Y-m-d");
$start_default = date("Y-m-01");

$start_date = $_GET['start_date'] ?? $start_default;
$end_date   = $_GET['end_date']   ?? $today;
$customer_id = $_GET['customer_id'] ?? "";
$search = $_GET['search'] ?? "";

$start_date  = $conn->real_escape_string($start_date);
$end_date    = $conn->real_escape_string($end_date);
$search      = $conn->real_escape_string($search);
$customer_id = $conn->real_escape_string($customer_id);

function fmt($x){ return number_format((float)$x,2); }

?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Receipts Report</title>

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

<div class="max-w-7xl mx-auto mt-6 bg-white p-8 shadow rounded mb-16">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">📘 Customer Receipts Report</h1>

        <button id="printBtn" onclick="window.print()" 
            class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">
            🖨 Print A4
        </button>
    </div>

    <!-- FILTER FORM -->
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

            <div>
                <label class="text-sm text-gray-700">Select Customer</label>
                <select name="customer_id" class="p-2 border rounded w-full">
                    <option value="">All Customers</option>
                    <?php
                    $cust = $conn->query("SELECT CustomerID, CustomerName, account_id FROM tblcustomers ORDER BY CustomerName");
                    while($c=$cust->fetch_assoc()):
                        $sel = ($customer_id == $c['CustomerID']) ? "selected" : "";
                        echo "<option value='{$c['CustomerID']}' $sel>{$c['CustomerName']}</option>";
                    endwhile;
                    ?>
                </select>
            </div>

            <div>
                <label class="text-sm text-gray-700">Search (Desc / Ref)</label>
                <input type="text" name="search" value="<?= $search ?>" placeholder="Search..." class="p-2 border rounded w-full">
            </div>

        </div>

        <div class="mt-4">
            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Apply</button>
            <a href="customer_receipts_report.php" class="ml-3 text-sm text-gray-600 hover:underline">Reset</a>
        </div>
    </form>

    <hr class="my-6">

<?php
// --------------------------------------------------------------------
// FETCH RECEIPTS
// A receipt = credit to customer account, debit to cash/bank
// --------------------------------------------------------------------

$sql = "
    SELECT 
        je.id AS jeid,
        je.entry_date,
        je.description,
        je.reference_no,
        jd.credit,
        acc.name AS account_name,
        c.CustomerName
    FROM journal_details jd
    INNER JOIN journal_entries je ON je.id = jd.journal_id
    INNER JOIN accounts acc ON acc.id = jd.account_id
    INNER JOIN tblcustomers c ON c.account_id = jd.account_id
    WHERE jd.credit > 0
    AND je.entry_date BETWEEN '$start_date' AND '$end_date'
    AND (je.description LIKE '%$search%' OR je.reference_no LIKE '%$search%')
";

// filter by customer
if ($customer_id != "") {
    $sql .= " AND c.CustomerID = '$customer_id' ";
}

$sql .= " ORDER BY je.entry_date DESC, je.id DESC";

$rows = $conn->query($sql);

?>

    <table class="text-sm mt-3">
        <thead class="bg-gray-200">
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Description</th>
                <th>Reference</th>
                <th class="text-right">Received Amount</th>
            </tr>
        </thead>

        <tbody>
            <?php 
            $total_receipts = 0;

            if($rows->num_rows == 0): ?>
                <tr><td colspan="5" class="text-center p-4 text-gray-500">No receipts found for this period.</td></tr>
            <?php else: 
                while($r = $rows->fetch_assoc()):
                    $total_receipts += $r['credit'];
            ?>
                <tr>
                    <td><?= $r['entry_date'] ?></td>
                    <td><?= $r['CustomerName'] ?></td>
                    <td><?= $r['description'] ?></td>
                    <td><?= $r['reference_no'] ?></td>
                    <td class="text-right font-semibold text-green-700"><?= fmt($r['credit']) ?></td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>

        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td colspan="4" class="p-2">TOTAL RECEIPTS</td>
                <td class="p-2 text-right text-xl text-green-700"><?= fmt($total_receipts) ?></td>
            </tr>
        </tfoot>
    </table>

</div>

</body>
</html>
