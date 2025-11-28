<?php
session_start();
include 'db_connection.php';

$user_id = $_SESSION['user_id'] ?? 0;

// Default: no access
$userMenus = [];

if ($user_id) {
    $q = "
        SELECT m.menu_key 
        FROM user_menu_access a
        JOIN menus m ON a.menu_id = m.id
        WHERE a.user_id = $user_id
    ";
    $res = $conn->query($q);
    if ($res) {
        $userMenus = array_column($res->fetch_all(MYSQLI_ASSOC), 'menu_key');
    }
}

// Helper function
function canAccess($key, $userMenus) {
    return in_array($key, $userMenus);
}
?>
<style>
/* Navbar stays ABOVE the page content */
nav {
    position: relative;
    z-index: 10000 !important;
}

/* Dropdown menu also ABOVE everything */
nav .group:hover > div {
    z-index: 10001 !important;
    position: absolute;
}

    </style>
<nav class="bg-indigo-800 text-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:justify-between">
            
            <div class="flex flex-col md:flex-row">

                <!-- Dashboard -->
                <?php if (canAccess('dashboard', $userMenus)) { ?>
                <a href="dashboard.php" class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                    <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                </a>
                <?php } ?>

                <!-- Job Order -->
                <?php if (canAccess('joborder', $userMenus)) { ?>
                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                        <i class="fas fa-shopping-bag"></i><a href="job_orders_list.php"><span>Job Order</span></a>
                    </button>
                </div>
                <?php } ?>

                <!-- Invoices -->
                <?php if (canAccess('invoices', $userMenus)) { ?>
                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                        <i class="fas fa-shopping-bag"></i><a href="invoices_list.php"><span>Invoices</span></a> 
                    </button>
                </div>
                <?php } ?>

                <!-- Purchase -->
                <?php if (canAccess('purchase', $userMenus)) { ?>
                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                        <i class="fas fa-shopping-bag"></i><span>Purchase</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>

                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 top-full">
                        <a href="purchase.php" class="block px-4 py-2 hover:bg-indigo-700">Purchase</a>
                        <a href="grn.php" class="block px-4 py-2 hover:bg-indigo-700">GRN</a>
                        <a href="suppliers.php" class="block px-4 py-2 hover:bg-indigo-700">Suppliers</a>
                    </div>
                </div>
                <?php } ?>

                <!-- Payments -->
                <?php if (canAccess('payments', $userMenus)) { ?>
                <a href="payment.php" class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                    <i class="fas fa-shopping-cart"></i><span>Payments</span>
                </a>
                <?php } ?>

                <!-- Accounts -->
                <?php if (canAccess('accounts', $userMenus)) { ?>
                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                        <i class="fas fa-calculator"></i><span>Accounts</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>

                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 top-full">
                        <a href="chart_of_accounts.php" class="block px-4 py-2 hover:bg-indigo-700">Chart of Accounts</a>
                        <a href="category_accounts.php" class="block px-4 py-2 hover:bg-indigo-700">Category Accounts</a>
                        <a href="trialbalance.php" class="block px-4 py-2 hover:bg-indigo-700">Trial Balance</a>
                        <a href="journal_entry.php" class="block px-4 py-2 hover:bg-indigo-700">Journal Entry</a>
                        <a href="ledger.php" class="block px-4 py-2 hover:bg-indigo-700">Ledger</a>
                        <a href="customersledger.php" class="block px-4 py-2 hover:bg-indigo-700">Customers Ledger</a>
                    </div>
                </div>
                <?php } ?>

                <!-- Setting -->
                <?php if (canAccess('setting', $userMenus)) { ?>
                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                        <i class="fas fa-cog"></i><span>Setting</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>

                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 top-full">
                        <a href="materials.php" class="block px-4 py-2 hover:bg-indigo-700">Materials</a>
                        <a href="customers.php" class="block px-4 py-2 hover:bg-indigo-700">Customers</a>
                        <a href="users.php" class="block px-4 py-2 hover:bg-indigo-700">Users</a>
                        <a href="menu_access.php" class="block px-4 py-2 hover:bg-indigo-700">User Permission</a>
                    </div>
                </div>
                <?php } ?>

                <!-- Expenses -->
                <?php if (canAccess('expenses', $userMenus)) { ?>
                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                        <i class="fas fa-chart-line"></i><span>Expenses</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>

                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-56 top-full">
                        <a href="expenses.php" class="block px-4 py-2 hover:bg-indigo-700">Expenses</a>
                        <a href="expensescategory.php" class="block px-4 py-2 hover:bg-indigo-700">Expenses Category</a>
                        <a href="expensesreport.php" class="block px-4 py-2 hover:bg-indigo-700">Expenses Report</a>
                    </div>
                </div>
                <?php } ?>

                <!-- HR -->
                <?php if (canAccess('hr', $userMenus)) { ?>
                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                        <i class="fas fa-users"></i><span>HR</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>

                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 top-full">
                        <a href="employees.php" class="block px-4 py-2 hover:bg-indigo-700">Employees</a>
                        <a href="attendance.php" class="block px-4 py-2 hover:bg-indigo-700">Attandance</a>
                        <a href="salarysheet.php" class="block px-4 py-2 hover:bg-indigo-700">Salary Sheet</a>
                    </div>
                </div>
                <?php } ?>

                <!-- Reports -->
                <?php if (canAccess('reports', $userMenus)) { ?>
                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                        <i class="fas fa-chart-line"></i><span>Reports</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>

                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-56 top-full">

                        <a href="trialbalance.php" class="block px-4 py-2 hover:bg-indigo-700">Trial Balance</a>
                        <a href="ledger_customer.php" class="block px-4 py-2 hover:bg-indigo-700">General Ledger</a>
                        <a href="balance_sheet.php" class="block px-4 py-2 hover:bg-indigo-700">Balance Sheet</a>
                        <a href="profit_loss.php" class="block px-4 py-2 hover:bg-indigo-700">Profit & Loss</a>
                        <a href="journal_viewer.php" class="block px-4 py-2 hover:bg-indigo-700">Journal Viewer</a>

                        <hr class="border-gray-700">

                        <a href="sales_summary_report.php" class="block px-4 py-2 hover:bg-indigo-700">Sales Summary</a>
                        <a href="customer_receipts_report.php" class="block px-4 py-2 hover:bg-indigo-700">Customer Receipts</a>

                        <hr class="border-gray-700">

                        <a href="purchase_summary_report.php" class="block px-4 py-2 hover:bg-indigo-700">Purchase Summary</a>

                        <hr class="border-gray-700">

                        <a href="joborder_summary_report.php" class="block px-4 py-2 hover:bg-indigo-700">Job Orders Report</a>

                        <hr class="border-gray-700">

                        <a href="account_summary.php" class="block px-4 py-2 hover:bg-indigo-700">Account Summary</a>
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>
    </div>
</nav>
