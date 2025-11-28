<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
ini_set('log_errors', 1);
include 'header.php';
include 'menu.php';
 // include 'db_connection.php';

// ================== JOB ORDERS STATS ==================

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Total job orders this month
$qJobThisMonth = $conn->query("
    SELECT COUNT(*) AS total
    FROM job_orders
    WHERE MONTH(OrderDate) = '$currentMonth' AND YEAR(OrderDate) = '$currentYear'
");
$jobThisMonth = $qJobThisMonth->fetch_assoc()['total'] ?? 0;

// Pending job orders this month
$qPendingJob = $conn->query("
    SELECT COUNT(*) AS pending
    FROM job_orders
    WHERE MONTH(OrderDate) = '$currentMonth' AND YEAR(OrderDate) = '$currentYear' 
    AND (status = 'Pending' OR status IS NULL)
");
$pendingJob = $qPendingJob->fetch_assoc()['pending'] ?? 0;

// ================== INVOICE STATS ==================

// Invoices this month
$qInvoiceThisMonth = $conn->query("
    SELECT COUNT(*) AS total
    FROM tblinvoices
    WHERE MONTH(InvoiceDate) = '$currentMonth' AND YEAR(InvoiceDate) = '$currentYear'
");
$invoiceThisMonth = $qInvoiceThisMonth->fetch_assoc()['total'] ?? 0;

// Total Sales this month
$qSalesThisMonth = $conn->query("
    SELECT SUM(GrandTotal) AS total
    FROM tblinvoices
    WHERE MONTH(InvoiceDate) = '$currentMonth' AND YEAR(InvoiceDate) = '$currentYear'
");
$salesThisMonth = $qSalesThisMonth->fetch_assoc()['total'] ?? 0;
$salesThisMonth = $salesThisMonth ? number_format($salesThisMonth, 2) : "0.00";

// ================== MONTHLY SALES CHART ==================
$qMonthlySales = $conn->query("
    SELECT DATE_FORMAT(InvoiceDate, '%Y-%m') AS MonthLabel, SUM(GrandTotal) AS TotalSales
    FROM tblinvoices
    GROUP BY DATE_FORMAT(InvoiceDate, '%Y-%m')
    ORDER BY DATE_FORMAT(InvoiceDate, '%Y-%m') ASC
");

$months = [];
$totals = [];
while ($row = $qMonthlySales->fetch_assoc()) {
    $months[] = $row['MonthLabel'];
    $totals[] = (float)$row['TotalSales'];
}
?>

<!-- Include Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
        <p class="text-gray-600">Welcome to your inventory management dashboard</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Job Orders -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Job Orders this month</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $jobThisMonth ?></p>
                    <p class="text-xs text-green-600 mt-1"><i class="fas fa-arrow-up"></i> Active jobs</p>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-box text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Job Orders -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending Job Orders</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $pendingJob ?></p>
                    <p class="text-xs text-red-600 mt-1"><i class="fas fa-clock"></i> Awaiting action</p>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-tasks text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Invoices -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Invoices this month</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $invoiceThisMonth ?></p>
                    <p class="text-xs text-gray-600 mt-1"><i class="fas fa-file-invoice"></i> Generated</p>
                </div>
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-receipt text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Sales -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Sales this month</p>
                    <p class="text-2xl font-bold text-gray-900">Rs. <?= $salesThisMonth ?></p>
                    <p class="text-xs text-green-600 mt-1"><i class="fas fa-arrow-up"></i> Compared to last month</p>
                </div>
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-file-invoice-dollar text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Sales Overview</h3>
            </div>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
const months = <?= json_encode($months) ?>;
const totals = <?= json_encode($totals) ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Total Invoice Amount (Rs)',
            data: totals,
            borderColor: 'rgba(99, 102, 241, 1)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.3,
            borderWidth: 2,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true },
            tooltip: {
                callbacks: {
                    label: ctx => 'Rs. ' + ctx.parsed.y.toLocaleString()
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => 'Rs. ' + v.toLocaleString()
                }
            }
        }
    }
});
</script>

<?php include 'footer.php'; ?>
