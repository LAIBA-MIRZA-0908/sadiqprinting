<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
include 'header.php';
include 'menu.php';
  include 'db_connection.php';
  global $conn;

// Initialize variables
$as_of_date = date('Y-m-d');
$accounts = [];
$total_debits = 0;
$total_credits = 0;
$summary = [
    'Asset' => ['debit' => 0, 'credit' => 0],
    'Liability' => ['debit' => 0, 'credit' => 0],
    'Equity' => ['debit' => 0, 'credit' => 0],
    'Income' => ['debit' => 0, 'credit' => 0],
    'Expense' => ['debit' => 0, 'credit' => 0]
];

// Get as_of_date from form submission
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['as_of_date'])) {
    $as_of_date = mysqli_real_escape_string($conn, $_GET['as_of_date']);
}

// Fetch all accounts
$query = "SELECT * FROM accounts ORDER BY code";
$result = mysqli_query($conn, $query);

while ($account = mysqli_fetch_assoc($result)) {
    // Calculate balance as of the selected date
    $account_id = $account['id'];
    
    // Get total debits up to the as_of_date
    $debit_query = "SELECT COALESCE(SUM(debit), 0) as total_debit FROM ledgers 
                   WHERE account_id = $account_id AND date <= '$as_of_date'";
    $debit_result = mysqli_query($conn, $debit_query);
    $debit_data = mysqli_fetch_assoc($debit_result);
    $total_debit = $debit_data['total_debit'];
    
    // Get total credits up to the as_of_date
    $credit_query = "SELECT COALESCE(SUM(credit), 0) as total_credit FROM ledgers 
                    WHERE account_id = $account_id AND date <= '$as_of_date'";
    $credit_result = mysqli_query($conn, $credit_query);
    $credit_data = mysqli_fetch_assoc($credit_result);
    $total_credit = $credit_data['total_credit'];
    
    // Calculate balance
    $balance = $account['opening_balance'] + $total_debit - $total_credit;
    
    // Determine if this is a debit or credit balance account
    $type = $account['type'];
    $debit_amount = 0;
    $credit_amount = 0;
    
    // Asset and Expense accounts normally have debit balances
    if ($type === 'Asset' || $type === 'Expense') {
        if ($balance > 0) {
            $debit_amount = $balance;
        } else {
            $credit_amount = abs($balance);
        }
    } 
    // Liability, Equity, and Income accounts normally have credit balances
    else {
        if ($balance > 0) {
            $credit_amount = $balance;
        } else {
            $debit_amount = abs($balance);
        }
    }
    
    // Add to summary
    $summary[$type]['debit'] += $debit_amount;
    $summary[$type]['credit'] += $credit_amount;
    
    // Add to totals
    $total_debits += $debit_amount;
    $total_credits += $credit_amount;
    
    // Add account to list
    $accounts[] = [
        'id' => $account['id'],
        'code' => $account['code'],
        'name' => $account['name'],
        'type' => $type,
        'debit_amount' => $debit_amount,
        'credit_amount' => $credit_amount
    ];
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Trial Balance</h2>
            <p class="text-gray-600">Summary of all ledger account balances</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button class="bg-white border border-gray-300 rounded-lg px-4 py-2 flex items-center hover:bg-gray-50">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </button>
            <button class="bg-white border border-gray-300 rounded-lg px-4 py-2 flex items-center hover:bg-gray-50">
                <i class="fas fa-file-excel mr-2"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="get" class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
            <div>
                <label for="as_of_date" class="block text-sm font-medium text-gray-700 mb-1">As of Date</label>
                <input type="date" id="as_of_date" name="as_of_date" class="w-full md:w-auto border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo htmlspecialchars($as_of_date); ?>">
            </div>
            <div class="self-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
            </div>
        </form>
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="text-center">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Trial Balance</h3>
            <p class="text-gray-600">As of <?php echo date('F j, Y', strtotime($as_of_date)); ?></p>
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit (Rs.)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit (Rs.)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($account['code']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($account['name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?php echo $account['debit_amount'] > 0 ? 'Rs. ' . number_format($account['debit_amount'], 2) : '-'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?php echo $account['credit_amount'] > 0 ? 'Rs. ' . number_format($account['credit_amount'], 2) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-sm font-bold text-gray-900 text-right">Total:</td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">Rs. <?php echo number_format($total_debits, 2); ?></td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">Rs. <?php echo number_format($total_credits, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Debit Balance Accounts</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Assets</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Asset']['debit'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Expenses</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Expense']['debit'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-200 pt-3 mt-3">
                    <span class="text-sm font-bold text-gray-800">Total Debits</span>
                    <span class="text-sm font-bold text-gray-800">Rs. <?php echo number_format($total_debits, 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Credit Balance Accounts</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Liabilities</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Liability']['credit'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Equity</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Equity']['credit'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Income</span>
                    <span class="text-sm font-medium">Rs. <?php echo number_format($summary['Income']['credit'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-200 pt-3 mt-3">
                    <span class="text-sm font-bold text-gray-800">Total Credits</span>
                    <span class="text-sm font-bold text-gray-800">Rs. <?php echo number_format($total_credits, 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Difference Notice -->
    <?php 
    $difference = abs($total_debits - $total_credits);
    if ($difference > 0.01): // Allow for small rounding differences
    ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <span class="font-medium">Note:</span> The difference between Debits and Credits is Rs. <?php echo number_format($difference, 2); ?>. 
                    <?php 
                    if ($total_debits > $total_credits) {
                        echo "This indicates a net loss for the period.";
                    } else {
                        echo "This represents the Net Income for the period.";
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    <span class="font-medium">Success:</span> The trial balance is in balance. Total Debits equal Total Credits.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>