<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// job_orders_list.php
include 'header.php';
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Orders List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Job Orders List</h1>
                <p class="text-gray-600">View all job orders</p>
            </div>
            <div>
                <a href="add_job_order.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Order
                </a>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search by customer name or order number..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <select id="dateFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="cancel">Cancel</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Job Orders Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="jobOrdersTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
 
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designer</th>
   <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
 
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button id="prevPageMobile" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </button>
                    <button id="nextPageMobile" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span id="startRecord" class="font-medium">1</span> to <span id="endRecord" class="font-medium">10</span> of
                            <span id="totalRecords" class="font-medium">0</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <button id="prevPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div id="pageNumbers" class="flex">
                                <!-- Page numbers will be added dynamically -->
                            </div>
                            <button id="nextPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Order Modal -->
    <div id="viewOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Job Order Details</h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="orderDetailsContent" class="mt-4">
                    <!-- Order details will be loaded here -->
                </div>
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button onclick="closeViewModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Close
                    </button>
                    <button onclick="printOrder()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
<!-- Cancel Reason Modal -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-96 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Cancel Job Order</h3>
    <p class="text-sm text-gray-600 mb-3">Please provide a reason for cancelling this job order:</p>
    
    <textarea id="cancelReason" rows="3" 
      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 mb-4"
      placeholder="Enter cancellation reason..."></textarea>

    <div class="flex justify-end space-x-3">
      <button onclick="closeCancelModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
        Close
      </button>
      <button id="confirmCancelBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
        Confirm Cancel
      </button>
    </div>
  </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var currentPage = 1;
        var recordsPerPage = 20;
        var totalPages = 1;
        var allOrders = [];

        $(document).ready(function() {
            loadJobOrders();
            
            // Search functionality
            $('#searchInput').on('input', function() {
                currentPage = 1;
                filterAndDisplayOrders();
            });
            
            // Filter functionality
            $('#dateFilter, #statusFilter').change(function() {
                currentPage = 1;
                filterAndDisplayOrders();
            });
            
            // Pagination
            $('#prevPage, #prevPageMobile').click(function() {
                if (currentPage > 1) {
                    currentPage--;
                    displayOrders();
                }
            });
            
            $('#nextPage, #nextPageMobile').click(function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayOrders();
                }
            });
        });

        function loadJobOrders() {
            $.ajax({
                url: 'job_order_functions.php',
                type: 'POST',
                data: { action: 'get_job_orders' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        allOrders = data.orders;
                        filterAndDisplayOrders();
                    } else {
                        $('#jobOrdersTable tbody').html('<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Error loading orders</td></tr>');
                    }
                },
                error: function() {
                    $('#jobOrdersTable tbody').html('<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Network error</td></tr>');
                }
            });
        }

        function filterAndDisplayOrders() {
            var searchTerm = $('#searchInput').val().toLowerCase();
            var dateFilter = $('#dateFilter').val();
            var statusFilter = $('#statusFilter').val();
            
            var filteredOrders = allOrders.filter(function(order) {
                // Search filter
                var matchesSearch = !searchTerm || 
                    order.CustomerName.toLowerCase().includes(searchTerm) || 
                    order.JobOrderNo.toString().includes(searchTerm);
                
                // Date filter
                var matchesDate = true;
                if (dateFilter !== 'all') {
                    var orderDate = new Date(order.OrderDate);
                    var today = new Date();
                    
                    if (dateFilter === 'today') {
                        matchesDate = orderDate.toDateString() === today.toDateString();
                    } else if (dateFilter === 'week') {
                        var weekAgo = new Date(today);
                        weekAgo.setDate(today.getDate() - 7);
                        matchesDate = orderDate >= weekAgo;
                    } else if (dateFilter === 'month') {
                        var monthAgo = new Date(today);
                        monthAgo.setMonth(today.getMonth() - 1);
                        matchesDate = orderDate >= monthAgo;
                    }
                }
                
                // Status filter (would need to be implemented in backend)
                var matchesStatus = statusFilter === 'all'; // Simplified for now
                
                return matchesSearch && matchesDate && matchesStatus;
            });
            
            totalPages = Math.ceil(filteredOrders.length / recordsPerPage);
            if (totalPages === 0) totalPages = 1;
            
            displayOrders(filteredOrders);
        }

        function displayOrders(orders) {
            if (!orders) {
                // Use filtered orders from the global variable
                var searchTerm = $('#searchInput').val().toLowerCase();
                var dateFilter = $('#dateFilter').val();
                var statusFilter = $('#statusFilter').val();
                
                orders = allOrders.filter(function(order) {
                    var matchesSearch = !searchTerm || 
                        order.CustomerName.toLowerCase().includes(searchTerm) || 
                        order.JobOrderNo.toString().includes(searchTerm);
                    
                    var matchesDate = true;
                    if (dateFilter !== 'all') {
                        var orderDate = new Date(order.OrderDate);
                        var today = new Date();
                        
                        if (dateFilter === 'today') {
                            matchesDate = orderDate.toDateString() === today.toDateString();
                        } else if (dateFilter === 'week') {
                            var weekAgo = new Date(today);
                            weekAgo.setDate(today.getDate() - 7);
                            matchesDate = orderDate >= weekAgo;
                        } else if (dateFilter === 'month') {
                            var monthAgo = new Date(today);
                            monthAgo.setMonth(today.getMonth() - 1);
                            matchesDate = orderDate >= monthAgo;
                        }
                    }
                    
                    var matchesStatus = statusFilter === 'all';
                    
                    return matchesSearch && matchesDate && matchesStatus;
                });
            }
            
            var tbody = $('#jobOrdersTable tbody');
            tbody.empty();
            
            if (orders.length === 0) {
                tbody.html('<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No job orders found</td></tr>');
                updatePaginationInfo(0, 0, 0);
                return;
            }
            
            // Calculate pagination
            var startIndex = (currentPage - 1) * recordsPerPage;
            var endIndex = Math.min(startIndex + recordsPerPage, orders.length);
            
            // Display current page orders
            var html = '';
            for (var i = startIndex; i < endIndex; i++) {
                var order = orders[i];
                var orderDate = new Date(order.OrderDate).toLocaleDateString();
                var deliveryDate = new Date(order.DeliveryDate).toLocaleDateString();
                
                html += '<tr class="hover:bg-gray-50">' +
                    '<td class="px-4 py-3 whitespace-nowrap">' +
                        '<div class="text-sm font-medium text-black">#' + order.JobOrderNo + '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3">' +
                        '<div class="text-sm text-black">' + order.CustomerName + '</div>' +
                        '<div class="text-xs text-black">' + order.CellNo + '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap">' +
                        '<div class="text-sm text-black">' + orderDate + '</div>' +
                    '</td>' +
                 
                    '<td class="px-4 py-3 whitespace-nowrap">' +
                        '<div class="text-sm text-black">' + (order.Designer || '-') + '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap">' +
                        '<div class="text-sm text-black">' + (order.status || '-') + '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">' +
                        '<button onclick="viewOrder(' + order.JobOrderNo + ')" class="text-blue-600 hover:text-blue-900 mr-3" title="View">' +
                            '<i class="fas fa-eye"></i>' +
                        '</button>' +
                    
                     
 // Cancel Button Logic
        (order.status === "pending"
            ? '<button onclick="cancelJob(' + order.JobOrderNo + ')" class="text-red-600 hover:text-red-900" title="Cancel">' +
                '<i class="fas fa-ban"></i>' +
              '</button>'
            : '<button class="text-gray-400 cursor-not-allowed" title="Cannot cancel">' +
                '<i class="fas fa-ban"></i>' +
              '</button>'
        ) +

                    '</td>' +
                    '</tr>';
            }
            
            tbody.html(html);
            updatePaginationInfo(startIndex + 1, endIndex, orders.length);
            updatePaginationControls();
        }

        function updatePaginationInfo(start, end, total) {
            $('#startRecord').text(start);
            $('#endRecord').text(end);
            $('#totalRecords').text(total);
        }

        function updatePaginationControls() {
            // Update previous/next buttons
            $('#prevPage, #prevPageMobile').prop('disabled', currentPage === 1);
            $('#nextPage, #nextPageMobile').prop('disabled', currentPage === totalPages);
            
            // Update page numbers
            var pageNumbers = $('#pageNumbers');
            pageNumbers.empty();
            
            // Show max 5 page numbers
            var startPage = Math.max(1, currentPage - 2);
            var endPage = Math.min(totalPages, startPage + 4);
            
            // Adjust if we're at the end
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }
            
            for (var i = startPage; i <= endPage; i++) {
                var pageClass = i === currentPage ? 
                    'relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-500 text-sm font-medium text-white' : 
                    'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50';
                
                pageNumbers.append(
                    '<button type="button" class="' + pageClass + '" onclick="goToPage(' + i + ')">' + i + '</button>'
                );
            }
        }

        function goToPage(page) {
            currentPage = page;
            displayOrders();
        }

        function viewOrder(jobOrderNo) {
            $.ajax({
                url: 'job_order_functions.php',
                type: 'POST',
                data: { 
                    action: 'get_job_order_details',
                    jobOrderNo: jobOrderNo 
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        displayOrderDetails(data.order, data.details);
                        document.getElementById('viewOrderModal').classList.remove('hidden');
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                },
                error: function() {
                    alert('Network error occurred. Please try again.');
                }
            });
        }

        function displayOrderDetails(order, details) {
            var orderDate = new Date(order.OrderDate).toLocaleDateString();
            var deliveryDate = new Date(order.DeliveryDate).toLocaleDateString();
            
            var html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">' +
                '<div>' +
                    '<h4 class="text-lg font-medium text-gray-900 mb-2">Order Information</h4>' +
                    '<div class="bg-gray-50 p-4 rounded-lg">' +
                        '<div class="mb-2"><span class="font-medium">Order No:</span> #' + order.JobOrderNo + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Order Date:</span> ' + orderDate + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Delivery Date:</span> ' + deliveryDate + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Design By:</span> ' + order.DesignBy + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Job For:</span> ' + order.JobFor + '</div>' +
                    '</div>' +
                '</div>' +
                '<div>' +
                    '<h4 class="text-lg font-medium text-gray-900 mb-2">Customer Information</h4>' +
                    '<div class="bg-gray-50 p-4 rounded-lg">' +
                        '<div class="mb-2"><span class="font-medium">Name:</span> ' + order.CustomerName + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Cell No:</span> ' + order.CellNo + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Designer:</span> ' + (order.Designer || '-') + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Advance Payment:</span> ' + parseFloat(order.AdvancePayment).toFixed(2) + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div>' +
                '<h4 class="text-lg font-medium text-gray-900 mb-2">Order Details</h4>' +
                '<div class="overflow-x-auto">' +
                    '<table class="min-w-full divide-y divide-gray-200 border border-gray-300">' +
                        '<thead class="bg-gray-50">' +
                            '<tr>' +
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sr#</th>' +
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Detail</th>' +
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Media</th>' +
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dimensions</th>' +
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>' +
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sqft</th>' +
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Extras</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody class="bg-white divide-y divide-gray-200">';
            
            for (var i = 0; i < details.length; i++) {
                var detail = details[i];
                var extras = [];
                if (detail.Ring) extras.push('Ring');
                if (detail.Pocket) extras.push('Pocket');
                
                html += '<tr>' +
                    '<td class="px-4 py-2 text-sm">' + detail.SrNo + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + detail.Detail + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + detail.Media + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + detail.Width + ' × ' + detail.Height + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + detail.Qty + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + detail.Sqft + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + (extras.length > 0 ? extras.join(', ') : '-') + '</td>' +
                '</tr>';
            }
            
            html += '</tbody></table></div></div>';
            
            $('#orderDetailsContent').html(html);
        }

        function closeViewModal() {
            document.getElementById('viewOrderModal').classList.add('hidden');
        }

       function printOrder() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    
    // Get the order details content
    const orderContent = document.getElementById('orderDetailsContent').innerHTML;
    const orderNumber = document.querySelector('#orderDetailsContent .grid .bg-gray-50 .mb-2:first-child').textContent;
    
    // Create a complete HTML document for printing
    const printDocument = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Job Order - ${orderNumber}</title>
        <style>
            @media print {
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 15px;
                    color: #0c0c0cff;
                }
                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #ddd;
                }
                .header h1 {
                    margin: 0;
                    font-size: 20px;
                    color: #0c0c0cff;
                }
                .header p {
                    margin: 5px 0 0 0;
                    color: #0c0c0cff;
                    font-size: 15px;
                }
                .header .order-info {
                    text-align: right;
                }
                .header .order-info p {
                    margin: 5px 0;
                    font-size: 14px;
                }
                .grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                .grid h4 {
                    margin: 0 0 10px 0;
                    font-size: 16px;
                    color: #0c0c0cff;
                }
                .grid p {
                    margin: 5px 0;
                    font-size: 14px;
                }
                .bg-gray-50 {
                    background-color: #f9f9f9;
                    padding: 10px;
                    border-radius: 4px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table th, table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                table th {
                    background-color: #f2f2f2;
                    font-weight: bolder;
                }
                .text-right {
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
                .footer {
                    text-align: center;
                    font-size: 12px;
                    color: #0c0c0cff;
                    margin-top: 30px;
                }
                @page {
                    size: A4;
                    margin: 1cm;
                }
            }
        </style>
    </head>
    <body>
        <div class="header">
         <div class="logo">
            <img src="alsadiqlogo.jpg" alt="Company Logo" style="height:60px;">
        </div>
            <div>
                <h1>Job Order Details</h1>
                <p>Printing Shop Management System</p>
            </div>
            <div class="order-info">
                <p><strong>Order No:</strong> ${orderNumber}</p>
                <p><strong>Print Date:</strong> ${new Date().toLocaleDateString()}</p>
            </div>
        </div>
        ${orderContent}
        <div class="footer">
            <p>Generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
        </div>
    </body>
    </html>
    `;
    
    // Write the content to the new window
    printWindow.document.write(printDocument);
    printWindow.document.close();
    
    // Wait for the content to load, then print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
}

        function editOrder(jobOrderNo) {
            window.location.href = 'add_job_order.php?edit=' + jobOrderNo;
        }

        function deleteOrder(jobOrderNo) {
            if (confirm('Are you sure you want to delete this job order? This action cannot be undone.')) {
                $.ajax({
                    url: 'job_order_functions.php',
                    type: 'POST',
                    data: { 
                        action: 'delete_job_order',
                        jobOrderNo: jobOrderNo 
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            alert('Job order deleted successfully!');
                            loadJobOrders();
                        } else {
                            alert('Error: ' + (data.message || 'Unknown error occurred'));
                        }
                    },
                    error: function() {
                        alert('Network error occurred. Please try again.');
                    }
                });
            }
        }
    </script>
    <script>
        let cancelJobNo = null; // store which job is being canceled
function cancelJob(JobOrderNo) {
  cancelJobNo = JobOrderNo;
  $('#cancelReason').val('');
  $('#cancelModal').removeClass('hidden');
}

function closeCancelModal() {
  $('#cancelModal').addClass('hidden');
}

$('#confirmCancelBtn').click(function() {
  const reason = $('#cancelReason').val().trim();
  if (!reason) {
    alert("Please enter a cancellation reason.");
    return;
  }

  $.ajax({
    url: 'job_order_functions.php',
    type: 'POST',
    data: {
      action: 'cancel_job_order',
      JobOrderNo: cancelJobNo,
      CancelReason: reason
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        alert(res.message);
        closeCancelModal();
        loadJobOrders(); // refresh table
      } else {
        alert(res.message || "Error cancelling job order.");
      }
    },
    error: function() {
      alert("Server error while cancelling job order.");
    }
  });
});
</script>

</body>
</html>