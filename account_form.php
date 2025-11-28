<?php
ob_start(); // Start output buffering
include 'header.php';
include 'menu.php';
include 'db_connection.php';

// Check if we are editing an existing account
$account = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM accounts WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $account = mysqli_fetch_assoc($result);
}

// Fetch all accounts for parent selection (excluding the current account if editing)
$parentAccounts = [];
$query = "SELECT id, code, name FROM accounts ORDER BY code";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    if ($account && $row['id'] == $account['id']) {
        continue; // Skip self
    }
    $parentAccounts[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = mysqli_real_escape_string($conn, $_POST['code']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
    $opening_balance = (float)$_POST['opening_balance'];

    if ($account) {
        // Update existing account
        $query = "UPDATE accounts SET code = '$code', name = '$name', type = '$type', category = '$category', parent_id = $parent_id, opening_balance = $opening_balance WHERE id = " . $account['id'];
        mysqli_query($conn, $query);
    } else {
        // Insert new account
        $query = "INSERT INTO accounts (code, name, type, category, parent_id, opening_balance, balance) VALUES ('$code', '$name', '$type', '$category', $parent_id, $opening_balance, $opening_balance)";
        mysqli_query($conn, $query);
    }

    header('Location: chart_of_accounts.php');
    exit();
    ob_end_flush(); // optional, flush output
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $account ? 'Edit Account' : 'New Account'; ?></h2>
            <p class="text-gray-600"><?php echo $account ? 'Update account details' : 'Create a new account'; ?></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Account Code</label>
                    <input type="text" id="code" name="code" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo $account ? htmlspecialchars($account['code']) : ''; ?>">
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                    <input type="text" id="name" name="name" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo $account ? htmlspecialchars($account['name']) : ''; ?>">
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select id="type" name="type" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Type</option>
                        <option value="Asset" <?php echo $account && $account['type'] == 'Asset' ? 'selected' : ''; ?>>Asset</option>
                        <option value="Liability" <?php echo $account && $account['type'] == 'Liability' ? 'selected' : ''; ?>>Liability</option>
                        <option value="Equity" <?php echo $account && $account['type'] == 'Equity' ? 'selected' : ''; ?>>Equity</option>
                        <option value="Income" <?php echo $account && $account['type'] == 'Income' ? 'selected' : ''; ?>>Income</option>
                        <option value="Expense" <?php echo $account && $account['type'] == 'Expense' ? 'selected' : ''; ?>>Expense</option>
                    </select>
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input type="text" id="category" name="category" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo $account ? htmlspecialchars($account['category']) : ''; ?>">
                </div>
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Parent Account (Optional)</label>
                    <select id="parent_id" name="parent_id" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">None</option>
                        <?php foreach ($parentAccounts as $parent): ?>
                            <option value="<?php echo $parent['id']; ?>" <?php echo $account && $account['parent_id'] == $parent['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($parent['code'] . ' - ' . $parent['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-1">Opening Balance</label>
                    <input type="number" id="opening_balance" name="opening_balance" step="0.01" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo $account ? htmlspecialchars($account['opening_balance']) : '0.00'; ?>">
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <a href="chart_of_accounts.php" class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Save Account</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>