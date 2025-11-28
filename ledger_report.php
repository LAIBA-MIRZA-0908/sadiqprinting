<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

// Initialize variables
$account_id = '';
$from_date = date('Y-m-01');
$to_date = date('Y-m-t');
$account_details = [];
$ledger_entries = [];
$opening_balance = 0;
$total_debits = 0;
$total_credits = 0;
$closing_balance = 0;

// Get filter parameters
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['account_id'])) {
    $account_id = (int)$_GET['account_id'];
    $from_date = isset($_GET['from_date']) ? mysqli_real_escape_string($conn, $_GET['from_date']) : date('Y-m-01');
    $to_date = isset($_GET['to_date']) ? mysqli_real_escape_string($conn, $_GET['to_date']) : date('Y-m-t');
    
    // Get account details
    $query = "SELECT * FROM accounts WHERE id = $account_id";
    $result = mysqli_query($conn, $query);
    $account_details = mysqli_fetch_assoc($result);
    
    if ($account_details) {
        // Calculate opening balance (account opening balance + transactions before from_date)
        $query = "SELECT 
                    COALESCE(SUM(debit), 0) as total_debit, 
                    COALESCE(SUM(credit), 0) as total_credit 
                  FROM ledgers 
                  WHERE account_id = $account_id AND date < '$from_date'";
        $result = mysqli_query($conn, $query);
        $previous_totals = mysqli_fetch_assoc($result);
        
        $opening_balance = $account_details['opening_balance'] + $previous_totals['total_debit'] - $previous_totals['total_credit'];
        
        // Get ledger entries for the selected period
        $query = "SELECT * FROM ledgers 
                  WHERE account_id = $account_id AND date BETWEEN '$from_date' AND '$to_date'
                  ORDER BY date";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $ledger_entries[] = $row;
            $total_debits += $row['debit'];
            $total_credits += $row['credit'];
        }
        
        // Calculate closing balance
        $closing_balance = $opening_balance + $total_debits - $total_credits;
    }
}

// Get all accounts for the dropdown
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
            <h2 class="text-2xl font-bold text-gray-800">Ledger Report</h2>
            <p class="text-gray-600">Detailed account transaction report</p>
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
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="get" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="account_id" class="block text-sm font-medium text-gray-700 mb-1">Account</label>
                    <select id="account_id" name="account_id" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Account</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['id']; ?>" <?php echo $account_id == $account['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="from_date" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" id="from_date" name="from_date" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo htmlspecialchars($from_date); ?>">
                </div>
                
                <div>
                    <label for="to_date" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" id="to_date" name="to_date" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo htmlspecialchars($to_date); ?>">
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Generate Report
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($account_details)): ?>
    <!-- Report Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($account_details['name']); ?> Ledger Report</h3>
                <p class="text-gray-600">Account Code: <?php echo htmlspecialchars($account_details['code']); ?></p>
                <p class="text-gray-600">Period: <?php echo date('M j, Y', strtotime($from_date)); ?> - <?php echo date('M j, Y', strtotime($to_date)); ?></p>
            </div>
            <div class="mt-4 md:mt-0 text-right">
                <p class="text-sm text-gray-600">Opening Balance</p>
                <p class="text-xl font-bold text-gray-800">Rs. <?php echo number_format($opening_balance, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particulars</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher No.</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit (Rs.)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit (Rs.)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance (Rs.)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $running_balance = $opening_balance;
                    
                    // Display opening balance row
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y', strtotime($from_date)); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-900">Opening Balance</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">OB</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $opening_balance > 0 ? 'Rs. ' . number_format($opening_balance, 2) : '-'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $opening_balance < 0 ? 'Rs. ' . number_format(abs($opening_balance), 2) : '-'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. <?php echo number_format($running_balance, 2); ?></td>
                    </tr>
                    
                    <?php foreach ($ledger_entries as $entry): 
                        // Update running balance
                        $running_balance += $entry['debit'] - $entry['credit'];
                        
                        // Extract voucher type and number from description
                        $description_parts = explode(' #', $entry['description']);
                        $particulars = $description_parts[0];
                        $voucher_type = '';
                        $voucher_no = '';
                        
                        if (count($description_parts) > 1) {
                            $voucher_info = $description_parts[1];
                            if (strpos($voucher_info, 'INV-') !== false) {
                                $voucher_type = 'Invoice';
                            } elseif (strpos($voucher_info, 'REC-') !== false) {
                                $voucher_type = 'Receipt';
                            } elseif (strpos($voucher_info, 'PAY-') !== false) {
                                $voucher_type = 'Payment';
                            } elseif (strpos($voucher_info, 'PO-') !== false) {
                                $voucher_type = 'Purchase';
                            }
                            $voucher_no = $voucher_info;
                        }
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y', strtotime($entry['date'])); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($particulars); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $voucher_type; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $voucher_no; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 2) : '-'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 2) : '-'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. <?php echo number_format($running_balance, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="border-r border-gray-200 pr-6">
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Total Debits</h4>
                <p class="text-2xl font-bold text-gray-800">Rs. <?php echo number_format($total_debits, 2); ?></p>
            </div>
            
            <div class="border-r border-gray-200 pr-6">
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Total Credits</h4>
                <p class="text-2xl font-bold text-gray-800">Rs. <?php echo number_format($total_credits, 2); ?></p>
            </div>
            
            <div>
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Closing Balance</h4>
                <p class="text-2xl font-bold text-gray-800">Rs. <?php echo number_format($closing_balance, 2); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>