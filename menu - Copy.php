<nav class="bg-indigo-800 text-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:justify-between">
            <div class="flex flex-col md:flex-row">
                <a href="dashboard.php" class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                    <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                </a>

                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2 w-full text-left">
                        <i class="fas fa-shopping-bag"></i><span>Job Order</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <!-- Removed mt-1 and added top-full to position right below the button -->
                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 z-10 top-full">
                        <a href="add_job_order.php" class="block px-4 py-2 hover:bg-indigo-700">Add Job Order</a>
                        <a href="job_orders_list.php" class="block px-4 py-2 hover:bg-indigo-700">Job List</a>
                    </div>
                </div>

              

<div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2 w-full text-left">
                        <i class="fas fa-shopping-bag"></i><span>Invoices</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <!-- Removed mt-1 and added top-full to position right below the button -->
                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 z-10 top-full">
                        <a href="add_invoice.php" class="block px-4 py-2 hover:bg-indigo-700">Add Invoice</a>
                          <a href="invoices_list.php" class="block px-4 py-2 hover:bg-indigo-700">Invoice List</a>  
                    </div>
 </div>
<div class="relative group" style="display:none;">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2 w-full text-left">
                        <i class="fas fa-shopping-bag"></i><span>Quotations</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <!-- Removed mt-1 and added top-full to position right below the button -->
                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 z-10 top-full">
                        <a href="add_qutation.php" class="block px-4 py-2 hover:bg-indigo-700">Add Quotation</a>
                          <a href="quotations_list.php" class="block px-4 py-2 hover:bg-indigo-700">Quotation List</a>  
                    </div>
 </div>



<div class="relative group" style="display:none;>
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2 w-full text-left">
                        <i class="fas fa-shopping-bag"></i><span>Inventory</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <!-- Removed mt-1 and added top-full to position right below the button -->
                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 z-10 top-full">
                        <a href="inventory.php" class="block px-4 py-2 hover:bg-indigo-700">View Display Inventory</a>
                            <a href="inventory_value_report.php" class="block px-4 py-2 hover:bg-indigo-700">Article Wise Value Display</a>
                       <a href="stock_adjustment.php" class="block px-4 py-2 hover:bg-indigo-700">Stock Adjustment</a>
                     <a href="generatebarcode.php" class="block px-4 py-2 hover:bg-indigo-700">Barcode</a>
                       <a href="physical_inventory.php" class="block px-4 py-2 hover:bg-indigo-700">Physical Stock</a>
                        <a href="physical_stock_report.php" class="block px-4 py-2 hover:bg-indigo-700">Physical Stock value report</a>
                    </div>
                </div>


               

   <div   class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2 w-full text-left">
                        <i class="fas fa-shopping-bag"></i><span>Purchase</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <!-- Same fix here -->
                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 z-10 top-full">
                        <a href="purchase.php" class="block px-4 py-2 hover:bg-indigo-700">Purchase</a>
                        <a href="grn.php" class="block px-4 py-2 hover:bg-indigo-700">GRN</a>
                          <a href="suppliers.php" class="block px-4 py-2 hover:bg-indigo-700">Suppliers</a>
                    </div>
                </div>






                <a href="payment.php" class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                    <i class="fas fa-shopping-cart"></i><span>Payments</span>
                </a>
                
                <div style="display:none" class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2 w-full text-left">
                        <i class="fas fa-shopping-bag"></i><span>Sales</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <!-- Same fix here -->
                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 z-10 top-full">
                        <a href="sales.php" class="block px-4 py-2 hover:bg-indigo-700">Sales Invoices</a>
                        <a href="new_sale.php" class="block px-4 py-2 hover:bg-indigo-700">New Sale</a>
                    </div>
                </div>
                
                <a style="display:none" href="invoices.php" class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                    <i class="fas fa-file-invoice"></i><span>Invoices</span>
                </a>
                <a style="display:none" href="expenses.php" class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                    <i class="fas fa-money-bill-wave"></i><span>Expenses</span>
                </a>
                <a style="display:none" href="orders.php" class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                    <i class="fas fa-money-bill-wave"></i><span>Orders</span>
                </a>
                <a style="display:none" href="stock_report.php" class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2">
                    <i class="fas fa-money-bill-wave"></i><span>Stock report</span>
                </a>
                
                <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2 w-full text-left">
                        <i class="fas fa-calculator"></i><span>Accounts</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <!-- Same fix here -->
                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 z-10 top-full">
                        <a href="chart_of_accounts.php" class="block px-4 py-2 hover:bg-indigo-700">Chart of Accounts</a>
                        <a href="category_accounts.php" class="block px-4 py-2 hover:bg-indigo-700">Category Accounts</a>
 
                        <a href="trial_balance.php" class="block px-4 py-2 hover:bg-indigo-700">Trial Balance</a>
                         <a href="journal_entry.php" class="block px-4 py-2 hover:bg-indigo-700">Journal Entry</a>
                           <a href="ledger.php" class="block px-4 py-2 hover:bg-indigo-700">Ledger</a>
                    </div>
                </div>

  <div class="relative group">
                    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2 w-full text-left">
                        <i class="fas fa-calculator"></i><span>Setting</span>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <!-- Same fix here -->
                    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-48 z-10 top-full">
                        <a href="materials.php" class="block px-4 py-2 hover:bg-indigo-700">Materials</a>
                          <a href="customers.php" class="block px-4 py-2 hover:bg-indigo-700">Customers</a>
                              <a href="users.php" class="block px-4 py-2 hover:bg-indigo-700">Users</a>
                    </div>
                     
                </div>

<div class="relative group">
    <button class="px-4 py-3 hover:bg-indigo-700 flex items-center space-x-2 w-full text-left">
        <i class="fas fa-chart-line"></i><span>Reports</span>
        <i class="fas fa-chevron-down ml-1 text-xs"></i>
    </button>
    <div class="absolute hidden group-hover:block bg-indigo-900 text-white shadow-lg rounded w-56 z-10 top-full">
        
        <!-- 🔹 Financial Reports -->
        <a href="report_trial_balance.php" class="block px-4 py-2 hover:bg-indigo-700">Trial Balance</a>
        <a href="report_ledger.php" class="block px-4 py-2 hover:bg-indigo-700">General Ledger</a>
        <a href="report_balance_sheet.php" class="block px-4 py-2 hover:bg-indigo-700">Balance Sheet</a>
        <a href="report_income_statement.php" class="block px-4 py-2 hover:bg-indigo-700">Profit & Loss (Income Statement)</a>

        <hr class="border-gray-700 my-1">

        <!-- 🔹 Sales Reports -->
        <a href="report_sales_summary.php" class="block px-4 py-2 hover:bg-indigo-700">Sales Summary</a>
        <a href="report_customer_ledger.php" class="block px-4 py-2 hover:bg-indigo-700">Customer Ledger</a>
        <a href="report_receipts.php" class="block px-4 py-2 hover:bg-indigo-700">Customer Receipts</a>

        <hr class="border-gray-700 my-1">

        <!-- 🔹 Purchase Reports -->
        <a href="report_supplier_ledger.php" class="block px-4 py-2 hover:bg-indigo-700">Supplier Ledger</a>
        <a href="report_payments.php" class="block px-4 py-2 hover:bg-indigo-700">Supplier Payments</a>
        <a href="report_purchase_summary.php" class="block px-4 py-2 hover:bg-indigo-700">Purchase Summary</a>

        <hr class="border-gray-700 my-1">

        <!-- 🔹 Inventory & Job -->
        <a href="report_stock_balance.php" class="block px-4 py-2 hover:bg-indigo-700">Stock Balance</a>
        <a href="report_job_orders.php" class="block px-4 py-2 hover:bg-indigo-700">Job Orders Report</a>

        <hr class="border-gray-700 my-1">

        <!-- 🔹 Audit & System -->
        <a href="report_journal_entries.php" class="block px-4 py-2 hover:bg-indigo-700">Journal Entries</a>
        <a href="report_account_summary.php" class="block px-4 py-2 hover:bg-indigo-700">Account Summary</a>
    </div>
</div>


            </div>
        </div>
    </div>
</nav>