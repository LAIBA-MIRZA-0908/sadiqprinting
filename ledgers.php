<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

// Get filter parameters
$account_id = isset($_GET['account_id']) ? (int)$_GET['account_id'] : '';
$from_date = isset($_GET['from_date']) ? mysqli_real_escape_string($conn, $_GET['from_date']) : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? mysqli_real_escape_string($conn, $_GET['to_date']) : date('Y-m-t');

// Build the query
$query = "SELECT l.*, a.code, a.name FROM ledgers l JOIN accounts a ON l.account_id = a.id WHERE 1=1";

if (!empty($account_id)) {
    $query .= " AND l.account_id = $account_id";
}

if (!empty($from_date)) {
    $query .= " AND l.date >= '$from_date'";
}

if (!empty($to_date)) {
    $query .= " AND l.date <= '$to_date'";
}

$query .= " ORDER BY l.date, l.id";

$result = mysqli_query($conn, $query);
$ledgers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ledgers[] = $row;
}

// Fetch all accounts for the filter dropdown
$accounts = [];
$query = "SELECT id, code, name FROM accounts ORDER BY code";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $accounts[] = $row;
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Ledgers</h2>
            <p class="text-gray-600">View and manage account ledgers</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button class="bg-white border border-gray-300 rounded-lg px-4 py-2 flex items-center hover:bg-gray-50">
                <i class="fas fa-filter mr-2"></i> Filter
            </button>
            <a href="ledger_form.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> New Entry
            </a>
        </div>
    </div>

    <!-- Account Selection and Date Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="get" class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
            <div class="flex-1">
                <label for="account_id" class="block text-sm font-medium text-gray-700 mb-1">Select Account</label>
                <select id="account_id" name="account_id" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Accounts</option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?php echo $account['id']; ?>" <?php echo $account_id == $account['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="from_date" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" id="from_date" name="from_date" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo htmlspecialchars($from_date); ?>">
            </div>
            <div>
                <label for="to_date" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" id="to_date" name="to_date" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo htmlspecialchars($to_date); ?>">
            </div>
            <div class="self-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-search mr-2"></i> Apply
                </button>
            </div>
        </form>
    </div>

    <!-- Ledger Entries -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Ledger Entries</h3>
            <div class="text-sm text-gray-500">
                Showing entries for: <span class="font-medium"><?php echo empty($account_id) ? 'All Accounts' : htmlspecialchars($accounts[array_search($account_id, array_column($accounts, 'id'))]['name']); ?></span> | 
                Period: <span class="font-medium"><?php echo date('M j, Y', strtotime($from_date)); ?> - <?php echo date('M j, Y', strtotime($to_date)); ?></span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit (Rs.)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit (Rs.)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance (Rs.)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $running_balance = 0;
                    foreach ($ledgers as $ledger): 
                        // Calculate running balance for the account
                        $running_balance += $ledger['debit'] - $ledger['credit'];
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y', strtotime($ledger['date'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($ledger['code'] . ' - ' . $ledger['name']); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($ledger['description']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $ledger['debit'] > 0 ? 'Rs. ' . number_format($ledger['debit'], 2) : '-'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $ledger['credit'] > 0 ? 'Rs. ' . number_format($ledger['credit'], 2) : '-'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. <?php echo number_format($running_balance, 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="ledger_form.php?id=<?php echo $ledger['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>