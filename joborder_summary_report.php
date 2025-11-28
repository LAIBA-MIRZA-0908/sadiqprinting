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

        @media print {
            #filterForm, #printBtn, .no-print, header, nav, .menu, #topbar {
                display: none !important;
            }
            body { background: #fff !important; color: #000 !important; }
            .break-inside-avoid { page-break-inside: avoid; }
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

    <form id="filterForm" method="GET" class="mt-6 no-print">
        <div class="grid grid-cols-4 gap-4">
            <div>
                <label class="text-sm text-gray-700">Start Date</label>
                <input type="date" name="start_date" value="<?= $start_date ?>" class="border p-2 rounded w-full">
            </div>
            <div>
                <label class="text-sm text-gray-700">End Date</label>
                <input type="date" name="end_date" value="<?= $end_date ?>" class="border p-2 rounded w-full">
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
// Fetch job orders
$sql = "SELECT JobOrderNo, OrderDate, CustomerName FROM job_orders WHERE OrderDate BETWEEN '$start_date' AND '$end_date'";
if($customer_id != "") $sql .= " AND CustomerID = '$customer_id'";
$sql .= " ORDER BY OrderDate DESC, JobOrderNo DESC";
$orders = $conn->query($sql);

if($orders->num_rows == 0){
    echo "<p class='text-center text-gray-500 py-10'>No job orders found.</p>";
    exit;
}

$total_qty = $total_sqft = $total_amount = 0;
?>

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
        <?php while($r = $orders->fetch_assoc()):
            $jno = $r['JobOrderNo'];
            $det_res = $conn->query("SELECT SUM(Qty) AS TotalQty, SUM(Sqft) AS TotalSqft FROM job_order_details WHERE JobOrderNo='$jno'");
            $det = $det_res->fetch_assoc();
            $order_qty = $det['TotalQty'] ?? 0;
            $order_sqft = $det['TotalSqft'] ?? 0;
            $order_total = $order_qty * $order_sqft; // simple calculation as placeholder
            $total_qty += $order_qty;
            $total_sqft += $order_sqft;
            $total_amount += $order_total;
        ?>
        <tr>
            <td><?= $r['OrderDate'] ?></td>
            <td><?= $r['JobOrderNo'] ?></td>
            <td><?= $r['CustomerName'] ?></td>
            <td class="text-right"><?= fmt($order_qty) ?></td>
            <td class="text-right"><?= fmt($order_sqft) ?></td>
            <td class="text-right font-bold"><?= fmt($order_total) ?></td>
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
</div>
</body>
</html>
