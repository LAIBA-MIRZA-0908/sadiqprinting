<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<!-- Include Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
        <p class="text-gray-600">Welcome to your inventory management dashboard</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Products</p>
                    <p class="text-2xl font-bold text-gray-900">248</p>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-arrow-up"></i> 12% from last month
                    </p>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-box text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Purchase Orders</p>
                    <p class="text-2xl font-bold text-gray-900">12</p>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-arrow-up"></i> 3 pending approval
                    </p>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                    <p class="text-2xl font-bold text-gray-900">7</p>
                    <p class="text-xs text-red-600 mt-1">
                        <i class="fas fa-arrow-up"></i> Requires attention
                    </p>
                </div>
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Monthly Sales</p>
                    <p class="text-2xl font-bold text-gray-900">Rs. 8,24,000</p>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-arrow-up"></i> 18% from last month
                    </p>
                </div>
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-file-invoice-dollar text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Sales Overview</h3>
                <div class="flex space-x-2">
                    <button class="text-xs px-2 py-1 bg-indigo-100 text-indigo-800 rounded">Week</button>
                    <button class="text-xs px-2 py-1 text-gray-500 hover:bg-gray-100 rounded">Month</button>
                    <button class="text-xs px-2 py-1 text-gray-500 hover:bg-gray-100 rounded">Year</button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        
        <!-- Stock Level Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Stock Levels by Category</h3>
                <button class="text-xs text-indigo-600 hover:text-indigo-800">View Details</button>
            </div>
            <div class="h-64">
                <canvas id="stockChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Recent Shopify Orders</h3>
                <a href="orders.php" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">John Smith</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jun 18, 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. 12,599</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Fulfilled</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Sarah Johnson</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jun 17, 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. 8,950</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1003</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Michael Brown</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jun 16, 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. 21,075</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Unfulfilled</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#1004</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Emily Davis</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jun 15, 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. 7,525</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Fulfilled</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Top Selling Products</h3>
                <a href="products.php" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sold</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <img class="h-8 w-8 rounded-md object-cover" src="https://via.placeholder.com/32" alt="Product image">
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Summer T-Shirt</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. 499</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">124</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. 61,876</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <img class="h-8 w-8 rounded-md object-cover" src="https://via.placeholder.com/32" alt="Product image">
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Denim Jeans</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. 1,299</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">87</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. 1,13,013</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <img class="h-8 w-8 rounded-md object-cover" src="https://via.placeholder.com/32" alt="Product image">
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Cotton Shirt</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. 899</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">65</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. 58,435</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <img class="h-8 w-8 rounded-md object-cover" src="https://via.placeholder.com/32" alt="Product image">
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Casual Shorts</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. 799</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">52</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">Rs. 41,548</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Expenses -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium">New product added: <span class="text-indigo-600">Summer T-Shirt</span></p>
                            <p class="text-sm text-gray-500">Today, 10:30 AM</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="p-2 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium">Purchase order <span class="text-indigo-600">#PO-0012</span> received</p>
                            <p class="text-sm text-gray-500">Yesterday, 3:45 PM</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="p-2 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-exclamation"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium">Low stock alert: <span class="text-indigo-600">Denim Jeans</span></p>
                            <p class="text-sm text-gray-500">Jun 12, 9:15 AM</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="p-2 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium">Invoice <span class="text-indigo-600">#INV-003</span> generated</p>
                            <p class="text-sm text-gray-500">Jun 10, 2:30 PM</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="p-2 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium">Expense added: <span class="text-indigo-600">Office Rent</span></p>
                            <p class="text-sm text-gray-500">Jun 8, 11:20 AM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Expense Summary -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Expense Summary</h3>
                <a href="expenses.php" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
            </div>
            <div class="p-6">
                <div class="h-48 mb-4">
                    <canvas id="expenseChart"></canvas>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                            <span class="text-sm text-gray-600">Rent</span>
                        </div>
                        <span class="text-sm font-medium">Rs. 20,000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                            <span class="text-sm text-gray-600">Utilities</span>
                        </div>
                        <span class="text-sm font-medium">Rs. 8,700</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                            <span class="text-sm text-gray-600">Salaries</span>
                        </div>
                        <span class="text-sm font-medium">Rs. 12,500</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-sm font-medium text-gray-900">Total</span>
                        <span class="text-sm font-bold">Rs. 41,200</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Sales',
                data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Stock Level Chart
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    const stockChart = new Chart(stockCtx, {
        type: 'doughnut',
        data: {
            labels: ['T-Shirts', 'Jeans', 'Dresses', 'Shirts', 'Accessories'],
            datasets: [{
                data: [120, 85, 45, 65, 30],
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Expense Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    const expenseChart = new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: ['Rent', 'Utilities', 'Salaries', 'Marketing', 'Other'],
            datasets: [{
                data: [20000, 8700, 12500, 6500, 3500],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(107, 114, 128, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>