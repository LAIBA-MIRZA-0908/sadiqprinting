<?php
// category_accounts.php
include 'header.php';
include 'menu.php';
include 'db_connection.php'; // Include your database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Accounts Assignment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Category Accounts Assignment</h1>
                <p class="text-gray-600">Assign ledger accounts to categories for financial tracking</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-2">
                <a href="utilities.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-tools mr-2"></i> Utilities
                </a>
            </div>
        </div>

        <!-- Category Selection Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="mb-4">
                <label for="categorySelect" class="block text-sm font-medium text-gray-700 mb-2">Select Category</label>
                <select id="categorySelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select a Category --</option>
                    <?php
                    // Fetch categories from database
                    $sql = "SELECT id, name FROM categories ORDER BY name";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Account Assignment Section -->
        <div id="accountAssignmentSection" class="bg-white rounded-lg shadow p-6 mb-6 hidden">
            <h2 class="text-lg font-semibold mb-4">Account Assignment</h2>
            <div id="categoryName" class="text-sm text-gray-600 mb-4"></div>
            
            <form id="accountAssignmentForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Stock Input Account -->
                    <div>
                        <label for="stockInputAccount" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sign-in-alt mr-1 text-green-600"></i> Stock Input Account
                        </label>
                        <select id="stockInputAccount" name="stock_input_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Select Account --</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Account used when stock is added</p>
                    </div>
                    
                    <!-- Stock Output Account -->
                    <div>
                        <label for="stockOutputAccount" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sign-out-alt mr-1 text-red-600"></i> Stock Output Account
                        </label>
                        <select id="stockOutputAccount" name="stock_output_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Select Account --</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Account used when stock is removed</p>
                    </div>
                    
                    <!-- Expense Account -->
                    <div>
                        <label for="expenseAccount" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave mr-1 text-orange-600"></i> Expense Account
                        </label>
                        <select id="expenseAccount" name="expense_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Select Account --</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Account used for expenses related to this category</p>
                    </div>
                    
                    <!-- Income Account -->
                    <div>
                        <label for="incomeAccount" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-coins mr-1 text-blue-600"></i> Income Account
                        </label>
                        <select id="incomeAccount" name="income_account_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Select Account --</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Account used for income from this category</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center">
                        <i class="fas fa-save mr-2"></i> Save Account Assignments
                    </button>
                </div>
            </form>
        </div>

        <!-- Success/Error Message -->
        <div id="messageContainer" class="hidden fixed top-4 right-4 max-w-sm"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('categorySelect');
            const accountAssignmentSection = document.getElementById('accountAssignmentSection');
            const accountAssignmentForm = document.getElementById('accountAssignmentForm');
            
            // Load accounts when page loads
            loadAccounts();
            
            // Handle category selection
            categorySelect.addEventListener('change', function() {
                const categoryId = this.value;
                if (categoryId) {
                    loadCategoryDetails(categoryId);
                    accountAssignmentSection.classList.remove('hidden');
                } else {
                    accountAssignmentSection.classList.add('hidden');
                }
            });
            
            // Handle form submission
            accountAssignmentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveAccountAssignments();
            });
        });

        function loadAccounts() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'category_accounts_function.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            populateAccountDropdowns(response.accounts);
                        } else {
                            showMessage('Error loading accounts: ' + response.message, 'error');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showMessage('Error loading accounts', 'error');
                    }
                }
            };
            
            const params = new URLSearchParams({
                action: 'get_accounts'
            });
            
            xhr.send(params.toString());
        }

        function populateAccountDropdowns(accounts) {
            const stockInputAccount = document.getElementById('stockInputAccount');
            const stockOutputAccount = document.getElementById('stockOutputAccount');
            const expenseAccount = document.getElementById('expenseAccount');
            const incomeAccount = document.getElementById('incomeAccount');
            
            // Clear existing options (except the first one)
            [stockInputAccount, stockOutputAccount, expenseAccount, incomeAccount].forEach(select => {
                while (select.options.length > 1) {
                    select.remove(1);
                }
            });
            
            // Add accounts to dropdowns
            accounts.forEach(account => {
                const option = document.createElement('option');
                option.value = account.id;
                option.textContent = `${account.code} - ${account.name} (${account.type})`;
                
                stockInputAccount.appendChild(option.cloneNode(true));
                stockOutputAccount.appendChild(option.cloneNode(true));
                expenseAccount.appendChild(option.cloneNode(true));
                incomeAccount.appendChild(option.cloneNode(true));
            });
        }

        function loadCategoryDetails(categoryId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'category_accounts_function.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            const category = response.category;
                            
                            // Update category name display
                            document.getElementById('categoryName').textContent = `Assigning accounts for: ${category.name}`;
                            
                            // Set current account selections
                            document.getElementById('stockInputAccount').value = category.stock_input_account_id || '';
                            document.getElementById('stockOutputAccount').value = category.stock_output_account_id || '';
                            document.getElementById('expenseAccount').value = category.expense_account_id || '';
                            document.getElementById('incomeAccount').value = category.income_account_id || '';
                        } else {
                            showMessage('Error loading category details: ' + response.message, 'error');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showMessage('Error loading category details', 'error');
                    }
                }
            };
            
            const params = new URLSearchParams({
                action: 'get_category_details',
                category_id: categoryId
            });
            
            xhr.send(params.toString());
        }

        function saveAccountAssignments() {
            const categoryId = document.getElementById('categorySelect').value;
            const stockInputAccountId = document.getElementById('stockInputAccount').value;
            const stockOutputAccountId = document.getElementById('stockOutputAccount').value;
            const expenseAccountId = document.getElementById('expenseAccount').value;
            const incomeAccountId = document.getElementById('incomeAccount').value;
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'category_accounts_function.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            showMessage('Account assignments saved successfully!', 'success');
                        } else {
                            showMessage('Error saving assignments: ' + response.message, 'error');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showMessage('Error saving assignments', 'error');
                    }
                }
            };
            
            const params = new URLSearchParams({
                action: 'save_account_assignments',
                category_id: categoryId,
                stock_input_account_id: stockInputAccountId,
                stock_output_account_id: stockOutputAccountId,
                expense_account_id: expenseAccountId,
                income_account_id: incomeAccountId
            });
            
            xhr.send(params.toString());
        }

        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            
            // Set message class based on type
            let alertClass = 'p-4 rounded-lg shadow-lg flex items-center';
            if (type === 'success') {
                alertClass += ' bg-green-100 text-green-800';
            } else if (type === 'error') {
                alertClass += ' bg-red-100 text-red-800';
            } else {
                alertClass += ' bg-blue-100 text-blue-800';
            }
            
            // Set icon based on type
            let icon = '';
            if (type === 'success') {
                icon = '<i class="fas fa-check-circle mr-2"></i>';
            } else if (type === 'error') {
                icon = '<i class="fas fa-exclamation-circle mr-2"></i>';
            } else {
                icon = '<i class="fas fa-info-circle mr-2"></i>';
            }
            
            // Create message element
            messageContainer.innerHTML = `
                <div class="${alertClass}">
                    ${icon}
                    <span>${message}</span>
                </div>
            `;
            
            // Show message
            messageContainer.classList.remove('hidden');
            
            // Hide message after 5 seconds
            setTimeout(() => {
                messageContainer.classList.add('hidden');
            }, 5000);
        }
    </script>
</body>
</html>