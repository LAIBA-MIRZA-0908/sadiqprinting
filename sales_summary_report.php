<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

// Default date filter
$today = date("Y-m-d");
$start_default = date("Y-m-01");

$start_date = $_GET['start_date'] ?? $start_default;
$end_date   = $_GET['end_date']   ?? $today;
$customer_id = $_GET['customer_id'] ?? "";

$start_date  = $conn->real_escape_string($start_date);
$end_date    = $conn->real_escape_string($end_date);
$customer_id = $conn->real_escape_string($customer_id);

function fmt($x){ return number_format((float)$x,2); }

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Summary Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- jQuery (required by Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Small Select2 styling to match Tailwind */
        .select2-container .select2-selection--single {
            height: 40px !important;
            padding: 6px 10px !important;
            border-radius: 0.375rem !important; /* rounded-md */
            border: 1px solid #d1d5db !important;
            background: #fff;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
        }
        .select2-selection__arrow {
            height: 40px !important;
            right: 8px;
        }
        .select2-container--open { z-index: 9999; }

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

<div class="max-w-7xl mx-auto bg-white p-8 mt-6 shadow rounded mb-16">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">📊 Sales Summary Report</h1>

        <button id="printBtn" onclick="window.print()" 
            class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">
            🖨 Print A4
        </button>
    </div>

    <!-- FILTER FORM -->
    <form id="filterForm" class="mt-6 no-print" method="GET">
        <div class="grid grid-cols-4 gap-4">

            <div>
                <label class="text-sm text-gray-600">Start Date</label>
                <input type="date" name="start_date" value="<?= $start_date ?>"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="text-sm text-gray-600">End Date</label>
                <input type="date" name="end_date" value="<?= $end_date ?>"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="text-sm text-gray-600">Customer</label>
                <select name="customer_id" id="customerSelect" class="border p-2 rounded w-full">
                    <option value="">All Customers</option>
                    <?php
                    $cs = $conn->query("SELECT CustomerID, CustomerName FROM tblcustomers ORDER BY CustomerName");
                    while($c = $cs->fetch_assoc()):
                        $sel = ($customer_id == $c['CustomerID']) ? "selected" : "";
                        $cid = htmlspecialchars($c['CustomerID']);
                        $cname = htmlspecialchars($c['CustomerName']);
                        echo "<option value='{$cid}' $sel>{$cname}</option>";
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
//--------------------------------------------------
//  FETCH INVOICES WITH TOTALS
//--------------------------------------------------
$sql = "
    SELECT 
        i.InvoiceID,
        i.InvoiceNo,
        i.InvoiceDate,
        i.CustomerName,
        i.CustomerID,
        i.SubTotal,
        i.TotalGST,
        i.TotalNTR,
        i.GrandTotal
    FROM tblinvoices i
    WHERE i.InvoiceDate BETWEEN '$start_date' AND '$end_date'
";

if($customer_id != ""){
    $sql .= " AND i.CustomerID = '$customer_id' ";
}

$sql .= " ORDER BY i.InvoiceDate DESC, i.InvoiceID DESC";

$invoices = $conn->query($sql);

if($invoices->num_rows == 0){
    echo "<p class='text-center text-gray-500 py-10'>No invoices found for this period.</p>";
    exit;
}

// Totals
$grand_sub = $grand_gst = $grand_ntr = $grand_total = 0;

?>

    <!-- INVOICE SUMMARY TABLE -->
    <h2 class="text-xl font-bold mt-4 mb-2 text-blue-700">📄 Invoice Summary</h2>

    <table class="text-sm mb-10">
        <thead class="bg-gray-200">
            <tr>
                <th>Date</th>
                <th>Invoice No</th>
                <th>Customer</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">GST</th>
                <th class="text-right">NTR</th>
                <th class="text-right">Grand Total</th>
            </tr>
        </thead>
        <tbody>

        <?php while($inv = $invoices->fetch_assoc()): ?>

            <?php
            $grand_sub   += $inv['SubTotal'];
            $grand_gst   += $inv['TotalGST'];
            $grand_ntr   += $inv['TotalNTR'];
            $grand_total += $inv['GrandTotal'];
            ?>

            <tr>
                <td><?= $inv['InvoiceDate'] ?></td>
                <td><?= $inv['InvoiceNo'] ?></td>
                <td><?= $inv['CustomerName'] ?></td>
                <td class="text-right"><?= fmt($inv['SubTotal']) ?></td>
                <td class="text-right"><?= fmt($inv['TotalGST']) ?></td>
                <td class="text-right"><?= fmt($inv['TotalNTR']) ?></td>
                <td class="text-right font-bold"><?= fmt($inv['GrandTotal']) ?></td>
            </tr>

        <?php endwhile; ?>

        </tbody>

        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td colspan="3">TOTAL</td>
                <td class="text-right"><?= fmt($grand_sub) ?></td>
                <td class="text-right"><?= fmt($grand_gst) ?></td>
                <td class="text-right"><?= fmt($grand_ntr) ?></td>
                <td class="text-right text-xl"><?= fmt($grand_total) ?></td>
            </tr>
        </tfoot>
    </table>

<?php
//--------------------------------------------------
//  MEDIA SUMMARY (GROUP BY MATERIAL)
//--------------------------------------------------
$sql2 = "
    SELECT 
        d.Media,
        m.name AS MaterialName,
        SUM(d.Sqft) AS TotalSqft,
        SUM(d.Total) AS TotalAmount
    FROM tblinvoice_details d
    LEFT JOIN tblmaterials m ON m.name = d.Media
    INNER JOIN tblinvoices i ON i.InvoiceID = d.InvoiceID
    WHERE i.InvoiceDate BETWEEN '$start_date' AND '$end_date'
";

if($customer_id != ""){
    $sql2 .= " AND i.CustomerID = '$customer_id' ";
}

$sql2 .= " GROUP BY d.Media, m.name ORDER BY TotalAmount DESC";

$media_rows = $conn->query($sql2);

?>

    <!-- MEDIA SUMMARY -->
    <h2 class="text-xl font-bold mt-8 mb-2 text-purple-700">🎨 Sales Summary by Material</h2>

    <table class="text-sm">
        <thead class="bg-gray-200">
            <tr>
                <th>Material</th>
                <th class="text-right">Total Sqft</th>
                <th class="text-right">Total Amount</th>
            </tr>
        </thead>

        <tbody>
            <?php 
            $total_sqft = 0; 
            $total_amount = 0;

            while($m = $media_rows->fetch_assoc()):
                $total_sqft   += $m['TotalSqft'];
                $total_amount += $m['TotalAmount'];
            ?>
                <tr>
                    <td><?= $m['MaterialName'] ?: $m['Media'] ?></td>
                    <td class="text-right"><?= fmt($m['TotalSqft']) ?></td>
                    <td class="text-right font-semibold"><?= fmt($m['TotalAmount']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>

        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td>TOTAL</td>
                <td class="text-right"><?= fmt($total_sqft) ?></td>
                <td class="text-right text-xl"><?= fmt($total_amount) ?></td>
            </tr>
        </tfoot>
    </table>

</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Initialize Select2 just for customer dropdown
    $(document).ready(function() {
        $('#customerSelect').select2({
            placeholder: "All Customers",
            allowClear: true,
            width: '100%'
        });
    });
</script>

</body>
</html>
