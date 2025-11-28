<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

// Default date filter
$today = date("Y-m-d");
$start_default = date("Y-m-01");

$start_date  = $_GET['start_date'] ?? $start_default;
$end_date    = $_GET['end_date'] ?? $today;
$customer_id = $_GET['customer_id'] ?? "";

$start_date  = $conn->real_escape_string($start_date);
$end_date    = $conn->real_escape_string($end_date);
$customer_id = $conn->real_escape_string($customer_id);

function fmt($x){ return number_format((float)$x,2); }

?>
<!DOCTYPE html>
<html>
<head>
    <title>Job Order Summary Report</title>

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
          @page { size: A4; margin: 17mm; }

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

<div class="max-w-7xl mx-auto bg-white p-8 shadow mt-6 rounded mb-16">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">🧾 Job Order Summary Report</h1>

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
                <input type="date" name="start_date" value="<?= $start_date ?>"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="text-sm text-gray-700">End Date</label>
                <input type="date" name="end_date" value="<?= $end_date ?>"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="text-sm text-gray-700">Customer</label>
                <select name="customer_id" class="border p-2 rounded w-full">
                    <option value="">All Customers</option>
                    <?php
                    $cs = $conn->query("SELECT CustomerID, CustomerName FROM tblcustomers ORDER BY CustomerName");
                    while($c = $cs->fetch_assoc()):
                        $sel = ($customer_id == $c['CustomerID']) ? "selected" : "";
                        echo "<option value='{$c['CustomerID']}' $sel>{$c['CustomerName']}</option>";
                    endwhile;
                    ?>
                </select>
            </div>

            <div class="flex items-end">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Apply</button>
            </div>

        </div>
    </form>

    <hr class="my-6">

<?php
//------------------------------------------------------
// FETCH JOB ORDERS HEADER
//------------------------------------------------------
$sql = "
    SELECT 
        j.JobOrderNo,
        j.OrderDate,
        j.CustomerName,
        j.CustomerID,
        j.TotalQty,
        j.TotalSqft,
        j.GrandTotal
    FROM job_orders j
    WHERE j.OrderDate BETWEEN '$start_date' AND '$end_date'
";

if($customer_id != ""){
    $sql .= " AND j.CustomerID = '$customer_id' ";
}

$sql .= " ORDER BY j.OrderDate DESC, j.JobOrderNo DESC";

$orders = $conn->query($sql);

if($orders->num_rows == 0){
    echo "<p class='text-center text-gray-500 py-10'>No job orders found.</p>";
    exit;
}

$total_qty = $total_sqft = $total_amount = 0;
?>

    <!-- JOB ORDER SUMMARY TABLE -->
    <h2 class="text-xl font-bold mb-2 text-blue-700">📄 Job Order Summary</h2>

    <table class="text-sm mb-10">
        <thead class="bg-gray-200">
            <tr>
                <th>Date</th>
                <th>Job Order No</th>
                <th>Customer</th>
                <th class="text-right">Total Qty</th>
                <th class="text-right">Total Sqft</th>
                <th class="text-right">Grand Total</th>
            </tr>
        </thead>

        <tbody>
            <?php while($r = $orders->fetch_assoc()): ?>

                <?php
                $total_qty    += $r['TotalQty'];
                $total_sqft   += $r['TotalSqft'];
                $total_amount += $r['GrandTotal'];
                ?>

                <tr>
                    <td><?= $r['OrderDate'] ?></td>
                    <td><?= $r['JobOrderNo'] ?></td>
                    <td><?= $r['CustomerName'] ?></td>
                    <td class="text-right"><?= fmt($r['TotalQty']) ?></td>
                    <td class="text-right"><?= fmt($r['TotalSqft']) ?></td>
                    <td class="text-right font-bold"><?= fmt($r['GrandTotal']) ?></td>
                </tr>

            <?php endwhile; ?>
        </tbody>

        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td colspan="3">TOTAL</td>
                <td class="text-right"><?= fmt($total_qty) ?></td>
                <td class="text-right"><?= fmt($total_sqft) ?></td>
                <td class="text-right text-xl"><?= fmt($total_amount) ?></td>
            </tr>
        </tfoot>
    </table>

<?php
//------------------------------------------------------
// JOB MATERIAL SUMMARY
//------------------------------------------------------
$sql2 = "
    SELECT 
        d.Media,
        SUM(d.Qty) AS TotalQty,
        SUM(d.Sqft) AS TotalSqft,
        SUM(d.Total) AS TotalAmount
    FROM tbljob_order_details d
    INNER JOIN tbljob_orders j ON j.JobOrderNo = d.JobOrderNo
    WHERE j.OrderDate BETWEEN '$start_date' AND '$end_date'
";

if($customer_id != ""){
    $sql2 .= " AND j.CustomerID = '$customer_id' ";
}

$sql2 .= " GROUP BY d.Media ORDER BY TotalAmount DESC";

$materials = $conn->query($sql2);

?>

    <!-- MATERIAL SUMMARY -->
    <h2 class="text-xl font-bold mb-2 text-purple-700">🎨 Job Summary by Material</h2>

    <table class="text-sm">
        <thead class="bg-gray-200">
            <tr>
                <th>Media</th>
                <th class="text-right">Total Qty</th>
                <th class="text-right">Total Sqft</th>
                <th class="text-right">Total Amount</th>
            </tr>
        </thead>

        <tbody>
            <?php 
            $sum_media_qty = $sum_media_sqft = $sum_media_amount = 0;

            while($m = $materials->fetch_assoc()):
                $sum_media_qty    += $m['TotalQty'];
                $sum_media_sqft   += $m['TotalSqft'];
                $sum_media_amount += $m['TotalAmount'];
            ?>
                <tr>
                    <td><?= $m['Media'] ?></td>
                    <td class="text-right"><?= fmt($m['TotalQty']) ?></td>
                    <td class="text-right"><?= fmt($m['TotalSqft']) ?></td>
                    <td class="text-right font-semibold"><?= fmt($m['TotalAmount']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>

        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td>TOTAL</td>
                <td class="text-right"><?= fmt($sum_media_qty) ?></td>
                <td class="text-right"><?= fmt($sum_media_sqft) ?></td>
                <td class="text-right text-xl"><?= fmt($sum_media_amount) ?></td>
            </tr>
        </tfoot>
    </table>

</div>

</body>
</html>
