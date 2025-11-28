<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
include 'header.php';
include 'menu.php';


// Fetch all accounts
$query = "SELECT * FROM accounts ORDER BY code";
$result = mysqli_query($conn, $query);
$accounts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $accounts[] = $row;
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Chart of Accounts</h2>
            <p class="text-gray-600">Manage your account structure</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            
            <a href="account_form.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> New Account
            </a>
        </div>
    </div>

    <!-- Account Categories Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php
        // Calculate summary for each type
        $summary = [
            'Asset' => ['count' => 0, 'balance' => 0],
            'Liability' => ['count' => 0, 'balance' => 0],
            'Equity' => ['count' => 0, 'balance' => 0],
            'Income' => ['count' => 0, 'balance' => 0],
            'Expense' => ['count' => 0, 'balance' => 0]
        ];

        foreach ($accounts as $account) {
            $type = $account['type'];
            $summary[$type]['count']++;
            $summary[$type]['balance'] += $account['balance'];
        }
        ?>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Assets</h3>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded"><?php echo $summary['Asset']['count']; ?> Accounts</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Assets</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Asset']['balance'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Liabilities</h3>
                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded"><?php echo $summary['Liability']['count']; ?> Accounts</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Liabilities</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Liability']['balance'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Equity</h3>
                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded"><?php echo $summary['Equity']['count']; ?> Accounts</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Equity</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Equity']['balance'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Income & Expenses</h3>
                <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded"><?php echo $summary['Income']['count'] + $summary['Expense']['count']; ?> Accounts</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Income</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Income']['balance'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Expenses</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Expense']['balance'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Account List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">All Accounts</h3>
           <div class="relative w-full">
    <input type="text" placeholder="Search accounts..."  id="accountSearch"
           class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <div class="absolute left-3 top-2.5 text-gray-400">
        <i class="fas fa-search"></i>
    </div>
</div>

        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($accounts as $account): ?>

    <?php
    // Prepare a query matched by the account id
    $stmt = $conn->prepare("SELECT * FROM accounts WHERE account_id = ?");
    $stmt->bind_param("i", $account['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $extraData = $result->fetch_assoc();
    ?>

    <tr>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            <?php echo htmlspecialchars($account['code']); ?>
        </td>

        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            <?php echo htmlspecialchars($account['name']); ?>
        </td>

        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            <?php echo htmlspecialchars($account['id']); ?>
        </td>

        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <?php echo htmlspecialchars($account['type']); ?>
        </td>

        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <?php echo htmlspecialchars($account['category']); ?>
        </td>

        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            Rs. <?php echo number_format($account['balance'], 2); ?>
        </td>

        <!-- Example of printing the extra data -->
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <?php echo isset($extraData['some_field']) ? htmlspecialchars($extraData['some_field']) : '—'; ?>
        </td>

        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <a href="account_form.php?id=<?php echo $account['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
            <a href="delete_account.php?id=<?php echo $account['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this account?');">Delete</a>
        </td>
    </tr>

<?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.getElementById("accountSearch").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

<?php include 'footer.php'; ?>