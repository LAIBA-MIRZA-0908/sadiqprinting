<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';

// Check if we are editing an existing ledger entry
$ledger = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM ledgers WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $ledger = mysqli_fetch_assoc($result);
}

// Fetch all accounts for the dropdown
$accounts = [];
$query = "SELECT id, code, name FROM accounts ORDER BY code";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $accounts[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = (int)$_POST['account_id'];
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $debit = (float)$_POST['debit'];
    $credit = (float)$_POST['credit'];

    if ($ledger) {
        // Update existing ledger entry
        $query = "UPDATE ledgers SET account_id = $account_id, date = '$date', description = '$description', debit = $debit, credit = $credit WHERE id = " . $ledger['id'];
        mysqli_query($conn, $query);
    } else {
        // Insert new ledger entry
        $query = "INSERT INTO ledgers (account_id, date, description, debit, credit) VALUES ($account_id, '$date', '$description', $debit, $credit)";
        mysqli_query($conn, $query);
    }

    // Update the account balance
    // First, get the opening balance
    $query = "SELECT opening_balance FROM accounts WHERE id = $account_id";
    $result = mysqli_query($conn, $query);
    $account = mysqli_fetch_assoc($result);
    $opening_balance = $account['opening_balance'];

    // Calculate the new balance: opening balance + total debits - total credits
    $query = "SELECT COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit FROM ledgers WHERE account_id = $account_id";
    $result = mysqli_query($conn, $query);
    $totals = mysqli_fetch_assoc($result);
    $new_balance = $opening_balance + $totals['total_debit'] - $totals['total_credit'];

    // Update the account balance
    $query = "UPDATE accounts SET balance = $new_balance WHERE id = $account_id";
    mysqli_query($conn, $query);

    header('Location: ledgers.php');
    exit();
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $ledger ? 'Edit Ledger Entry' : 'New Ledger Entry'; ?></h2>
            <p class="text-gray-600"><?php echo $ledger ? 'Update ledger entry details' : 'Create a new ledger entry'; ?></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="account_id" class="block text-sm font-medium text-gray-700 mb-1">Account</label>
                    <select id="account_id" name="account_id" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Account</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['id']; ?>" <?php echo $ledger && $ledger['account_id'] == $account['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" id="date" name="date" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo $ledger ? $ledger['date'] : date('Y-m-d'); ?>">
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo $ledger ? htmlspecialchars($ledger['description']) : ''; ?></textarea>
                </div>
                <div>
                    <label for="debit" class="block text-sm font-medium text-gray-700 mb-1">Debit Amount</label>
                    <input type="number" id="debit" name="debit" step="0.01" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo $ledger ? htmlspecialchars($ledger['debit']) : '0.00'; ?>">
                </div>
                <div>
                    <label for="credit" class="block text-sm font-medium text-gray-700 mb-1">Credit Amount</label>
                    <input type="number" id="credit" name="credit" step="0.01" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo $ledger ? htmlspecialchars($ledger['credit']) : '0.00'; ?>">
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <a href="ledgers.php" class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Save Entry</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>