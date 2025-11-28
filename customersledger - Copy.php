<?php
require_once 'ledger_functions.php';
include 'header.php';
include 'menu.php';
require_once 'db_connection.php';

// --- Run the query ---
$query = "
    SELECT 
        c.CustomerID,
        c.CustomerName,
        SUM(j.debit) AS total_debit,
        SUM(j.credit) AS total_credit,
        (SUM(j.debit) - SUM(j.credit)) AS balance
    FROM 
        tblcustomers AS c
    JOIN 
        journal_details AS j
        ON c.account_id = j.account_id
    GROUP BY 
        c.CustomerID, c.CustomerName
";
$result = mysqli_query($conn, $query);
?>

<!-- Page Wrapper -->
<div class="min-h-screen flex flex-col bg-gray-50">

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 no-print">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Customer Ledger Balances</h2>
                <p class="text-gray-600">View current balance for each customer</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button onclick="printReport()" 
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
        </div>

        <!-- Printable Header (hidden normally, visible only when printing) -->
        <div id="printHeader" class="hidden text-center mb-6">
            <img src="alsadiqlogo.jpg" alt="Company Logo" class="mx-auto mb-3" style="width: 100px;">
            <h2 class="text-2xl font-bold text-gray-800">Al-Sadiq Printing</h2>
            <p class="text-gray-600">Customer Ledger Balance Report</p>
            <hr class="my-4 border-gray-400">
        </div>

        <!-- Table Section -->
        <div id="printArea" class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer Name
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Balance (Rs)
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                    <?php echo htmlspecialchars($row['CustomerName']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-semibold 
                                    <?php echo $row['balance'] < 0 ? 'text-red-600' : 'text-green-700'; ?>">
                                    Rs. <?php echo number_format($row['balance'], 2); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">
                                No records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <!-- Sticky Footer -->
    <footer class="bg-white shadow mt-auto py-4 text-center text-gray-500 text-sm no-print">
        © <?php echo date("Y"); ?> Al-Sadiq Printing | All Rights Reserved
    </footer>
</div>

<!-- ✅ Print Functionality Script -->
<script>
function printReport() {
    document.getElementById('printHeader').classList.remove('hidden');
    window.print();
    setTimeout(() => {
        document.getElementById('printHeader').classList.add('hidden');
    }, 1000);
}
</script>

<!-- ✅ Print Styles -->
<style>
    @media print {
        body {
            background: white !important;
            -webkit-print-color-adjust: exact;
        }
        .no-print, header, nav, .sidebar {
            display: none !important;
        }
        #printHeader {
            display: block !important;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc !important;
            padding: 8px;
        }
        th {
            background: #f3f4f6 !important;
        }
    }
</style>

<?php include 'footer.php'; ?>
