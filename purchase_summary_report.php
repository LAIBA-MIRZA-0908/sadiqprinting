<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

// Default Date Range (current month)
$today = date("Y-m-d");
$start_default = date("Y-m-01");

$start_date  = $_GET['start_date'] ?? $start_default;
$end_date    = $_GET['end_date'] ?? $today;
$supplier_id = $_GET['supplier_id'] ?? "";

$start_date  = $conn->real_escape_string($start_date);
$end_date    = $conn->real_escape_string($end_date);
$supplier_id = $conn->real_escape_string($supplier_id);

function fmt($x){ return number_format((float)$x,2); }

?>
<!DOCTYPE html>
<html>
<head>
    <title>Purchase Summary Report</title>

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
    th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>

<body class="bg-gray-100">

<div class="max-w-7xl mx-auto bg-white p-8 mt-6 shadow-md rounded mb-16">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">📦 Purchase Summary Report</h1>

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
                <label class="text-sm text-gray-700">Supplier</label>
                <select name="supplier_id" class="border p-2 rounded w-full">
                    <option value="">All Suppliers</option>
                    <?php
                    $sup = $conn->query("SELECT SupplierID, SupplierName FROM tblsuppliers ORDER BY SupplierName ASC");
                    while($s = $sup->fetch_assoc()):
                        $sel = ($supplier_id == $s['SupplierID']) ? "selected" : "";
                        echo "<option value='{$s['SupplierID']}' $sel>{$s['SupplierName']}</option>";
                    endwhile;
                    ?>
                </select>
            </div>

            <div class="flex items-end">
                <button class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">Apply</button>
            </div>

        </div>
    </form>

    <hr class="my-6">

<?php
// -----------------------------------------------------
// FETCH PURCHASES SUMMARY (HEADER LEVEL)
// -----------------------------------------------------
$sql = "
    SELECT 
        p.PurchaseID,
        p.PurchaseNo,
        p.PurchaseDate,
        p.SupplierName,
        p.SupplierID,
        p.SubTotal,
        p.TotalGST,
        p.GrandTotal
    FROM tblpurchase_orders p
    WHERE p.PurchaseDate BETWEEN '$start_date' AND '$end_date'
";

if($supplier_id != ""){
    $sql .= " AND p.SupplierID = '$supplier_id' ";
}

$sql .= " ORDER BY p.PurchaseDate DESC, p.PurchaseID DESC";

$purchases = $conn->query($sql);

if($purchases->num_rows == 0){
    echo "<p class='text-center text-gray-500 py-10'>No purchases found.</p>";
    exit;
}

// Totals
$total_sub = $total_gst = $total_grand = 0;

?>

    <!-- PURCHASE SUMMARY TABLE -->
    <h2 class="text-xl font-bold mb-2 text-blue-700">📄 Purchase Invoice Summary</h2>

    <table class="text-sm mb-10">
        <thead class="bg-gray-200">
            <tr>
                <th>Date</th>
                <th>PO No</th>
                <th>Supplier</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">GST</th>
                <th class="text-right">Grand Total</th>
            </tr>
        </thead>

        <tbody>
            <?php while($row = $purchases->fetch_assoc()): ?>

                <?php
                $total_sub   += $row['SubTotal'];
                $total_gst   += $row['TotalGST'];
                $total_grand += $row['GrandTotal'];
                ?>

                <tr>
                    <td><?= $row['PurchaseDate'] ?></td>
                    <td><?= $row['PurchaseNo'] ?></td>
                    <td><?= $row['SupplierName'] ?></td>
                    <td class="text-right"><?= fmt($row['SubTotal']) ?></td>
                    <td class="text-right"><?= fmt($row['TotalGST']) ?></td>
                    <td class="text-right font-bold"><?= fmt($row['GrandTotal']) ?></td>
                </tr>

            <?php endwhile; ?>
        </tbody>

        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td colspan="3">TOTAL</td>
                <td class="text-right"><?= fmt($total_sub) ?></td>
                <td class="text-right"><?= fmt($total_gst) ?></td>
                <td class="text-right text-xl"><?= fmt($total_grand) ?></td>
            </tr>
        </tfoot>
    </table>

<?php
// -----------------------------------------------------
// MATERIAL SUMMARY (GROUP BY MATERIAL)
// -----------------------------------------------------
$sql2 = "
    SELECT 
        d.Material,
        m.name AS MaterialName,
        SUM(d.Qty) AS TotalQty,
        SUM(d.Sqft) AS TotalSqft,
        SUM(d.Total) AS TotalAmount
    FROM tblpurchase_order_details d
    LEFT JOIN tblmaterials m ON m.name = d.Material
    INNER JOIN tblpurchase_orders p ON p.PurchaseID = d.PurchaseID
    WHERE p.PurchaseDate BETWEEN '$start_date' AND '$end_date'
";

if($supplier_id != ""){
    $sql2 .= " AND p.SupplierID = '$supplier_id' ";
}

$sql2 .= " GROUP BY d.Material ORDER BY TotalAmount DESC";

$materials = $conn->query($sql2);

?>

    <!-- MATERIAL SUMMARY -->
    <h2 class="text-xl font-bold mb-2 text-purple-700">🧾 Purchase Summary by Material</h2>

    <table class="text-sm">
        <thead class="bg-gray-200">
            <tr>
                <th>Material</th>
                <th class="text-right">Total Qty</th>
                <th class="text-right">Total Sqft</th>
                <th class="text-right">Total Purchase Amount</th>
            </tr>
        </thead>

        <tbody>
            <?php 
            $sum_qty = $sum_sqft = $sum_amount = 0;

            while($m = $materials->fetch_assoc()):
                $sum_qty    += $m['TotalQty'];
                $sum_sqft   += $m['TotalSqft'];
                $sum_amount += $m['TotalAmount'];
            ?>
                <tr>
                    <td><?= $m['MaterialName'] ?: $m['Material'] ?></td>
                    <td class="text-right"><?= fmt($m['TotalQty']) ?></td>
                    <td class="text-right"><?= fmt($m['TotalSqft']) ?></td>
                    <td class="text-right font-semibold"><?= fmt($m['TotalAmount']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>

        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td>TOTAL</td>
                <td class="text-right"><?= fmt($sum_qty) ?></td>
                <td class="text-right"><?= fmt($sum_sqft) ?></td>
                <td class="text-right text-xl"><?= fmt($sum_amount) ?></td>
            </tr>
        </tfoot>
    </table>

</div>

</body>
</html>
