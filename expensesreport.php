<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

include 'header.php';
include 'menu.php';
include 'db_connection.php';

// Get unique years from expenses for dropdown
$yearQuery = "SELECT DISTINCT YEAR(expense_date) AS year FROM expenses ORDER BY year DESC";
$yearResult = mysqli_query($conn, $yearQuery);
$years = [];
while ($row = mysqli_fetch_assoc($yearResult)) {
    $years[] = $row['year'];
}

// Default month/year
$selectedMonth = $_POST['month'] ?? date('m');
$selectedYear = $_POST['year'] ?? date('Y');

// Fetch data if form submitted
$reportData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "
        SELECT 
            c.category_name,
            SUM(e.amount) AS total_amount
        FROM expenses e
        JOIN expense_categories c ON e.category_id = c.category_id
        WHERE MONTH(e.expense_date) = ? AND YEAR(e.expense_date) = ?
        GROUP BY c.category_name
        ORDER BY c.category_name ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $selectedMonth, $selectedYear);
    $stmt->execute();
    $reportData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="min-h-screen flex flex-col bg-gray-50">

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 no-print">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Monthly Expense Report</h2>
                <p class="text-gray-600">View expense summary by category</p>
            </div>
        </div>

        <!-- Form Section -->
        <form method="POST" class="bg-white p-6 rounded-lg shadow mb-6 no-print flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <select name="month" class="border rounded-md p-2">
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $monthName = date('F', mktime(0, 0, 0, $m, 10));
                        $selected = ($m == $selectedMonth) ? 'selected' : '';
                        echo "<option value='$m' $selected>$monthName</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select name="year" class="border rounded-md p-2">
                    <?php
                    foreach ($years as $year) {
                        $selected = ($year == $selectedYear) ? 'selected' : '';
                        echo "<option value='$year' $selected>$year</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700">
                    Show Report
                </button>
            </div>
        </form>

        <!-- Printable Header -->
        <div id="printHeader" class="hidden text-center mb-4">
            <img src="alsadiqlogo.jpg" alt="Logo" class="mx-auto mb-3" style="width: 100px;">
            <h2 class="text-2xl font-bold text-gray-800">Al-Sadiq Printing</h2>
            <p class="text-gray-600">
                Expense Report - <?php echo date('F', mktime(0,0,0,$selectedMonth,1)); ?> <?php echo $selectedYear; ?>
            </p>
            <hr class="my-4 border-gray-400">
        </div>

        <!-- Report Table -->
        <div id="reportArea" class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expense Category</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Amount (Rs)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($reportData)): ?>
                        <?php 
                        $grandTotal = 0;
                        foreach ($reportData as $row): 
                            $grandTotal += $row['total_amount'];
                        ?>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td class="px-6 py-4 text-sm text-right font-semibold text-gray-700">
                                    Rs. <?php echo number_format($row['total_amount'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bg-gray-100 font-bold">
                            <td class="px-6 py-3 text-right">Grand Total:</td>
                            <td class="px-6 py-3 text-right">Rs. <?php echo number_format($grandTotal, 2); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="2" class="text-center p-4 text-gray-500">No records found for this month</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Buttons -->
        <div class="mt-6 flex justify-end gap-3 no-print">
            <button type="button" onclick="printReport()" 
                class="bg-gray-700 text-white px-6 py-2 rounded-md hover:bg-gray-800 flex items-center">
                <i class="fas fa-print mr-2"></i> Save & Print
            </button>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow mt-auto py-4 text-center text-gray-500 text-sm no-print">
        © <?php echo date("Y"); ?> Universal Projects Co. | All Rights Reserved
    </footer>
</div>

<!-- Print Function -->
<script>
function printReport() {
    document.getElementById('printHeader').classList.remove('hidden');
    window.print();
    setTimeout(() => {
        document.getElementById('printHeader').classList.add('hidden');
    }, 1000);
}
</script>

<!-- Print Styles -->
<style>
@media print {
    body { background: white !important; -webkit-print-color-adjust: exact; }
    .no-print, header, nav, .sidebar { display: none !important; }
    #printHeader { display: block !important; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc !important; padding: 8px; }
    th { background: #f3f4f6 !important; }
}
</style>

<?php include 'footer.php'; ?>
