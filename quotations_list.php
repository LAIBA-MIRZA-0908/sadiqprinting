<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// quotations_list.php
include 'header.php';
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotations List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Quotations List</h1>
                <p class="text-gray-600">View all quotations</p>
            </div>
            <div>
                <a href="add_quotation.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Quotation
                </a>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search by quotation number or customer..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <select id="dateFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                        <option value="all">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="approved">Approved</option>
                        <option value="declined">Declined</option>
                        <option value="invoice_made">Invoice Made</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Quotations Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="quotationsTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quotation #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Until</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
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
        </div>
    </div>

    <!-- View Quotation Modal -->
    <div id="viewQuotationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Quotation Details</h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="quotationDetailsContent" class="mt-4">
                    <!-- Quotation details will be loaded here -->
                </div>
                <div class="flex justify-between items-center pb-3 border-t">
                    <div class="flex space-x-2">
                        <button onclick="closeViewModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Close
                        </button>
                        <button onclick="printQuotation()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                            <i class="fas fa-print mr-2"></i> Print
                        </button>
                        <button id="approveBtn" onclick="updateStatus('approved')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                            <i class="fas fa-check mr-2"></i> Approve
                        </button>
                        <button id="declineBtn" onclick="updateStatus('declined')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center">
                            <i class="fas fa-times mr-2"></i> Decline
                        </button>
                        <button id="invoiceBtn" onclick="updateStatus('invoice_made')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                            <i class="fas fa-file-invoice mr-2"></i> Invoice Made
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var currentPage = 1;
        var recordsPerPage = 10;
        var allQuotations = [];
        var currentQuotationId = null;

        $(document).ready(function() {
            loadQuotations();
            
            $('#searchInput').on('input', function() {
                filterAndDisplayQuotations();
            });
            
            $('#dateFilter, #statusFilter').change(function() {
                filterAndDisplayQuotations();
            });
        });

        function loadQuotations() {
            $.ajax({
                url: 'quotation_functions.php',
                type: 'POST',
                data: { action: 'get_quotations' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        allQuotations = data.quotations;
                        filterAndDisplayQuotations();
                    } else {
                        $('#quotationsTable tbody').html('<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Error loading quotations</td></tr>');
                    }
                },
                error: function() {
                    $('#quotationsTable tbody').html('<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Network error</td></tr>');
                }
            });
        }

        function filterAndDisplayQuotations() {
            var searchTerm = $('#searchInput').val().toLowerCase();
            var dateFilter = $('#dateFilter').val();
            var statusFilter = $('#statusFilter').val();
            
            var filteredQuotations = allQuotations.filter(function(quotation) {
                var matchesSearch = !searchTerm || 
                    quotation.CustomerName.toLowerCase().includes(searchTerm) || 
                    quotation.QuotationNo.toLowerCase().includes(searchTerm);
                
                var matchesDate = true;
                if (dateFilter !== 'all') {
                    var quotationDate = new Date(quotation.QuotationDate);
                    var today = new Date();
                    
                    if (dateFilter === 'today') {
                        matchesDate = quotationDate.toDateString() === today.toDateString();
                    } else if (dateFilter === 'week') {
                        var weekAgo = new Date(today);
                        weekAgo.setDate(today.getDate() - 7);
                        matchesDate = quotationDate >= weekAgo;
                    } else if (dateFilter === 'month') {
                        var monthAgo = new Date(today);
                        monthAgo.setMonth(today.getMonth() - 1);
                        matchesDate = quotationDate >= monthAgo;
                    }
                }
                
                var matchesStatus = statusFilter === 'all' || quotation.Status === statusFilter;
                
                return matchesSearch && matchesDate && matchesStatus;
            });
            
            displayQuotations(filteredQuotations);
        }

        function displayQuotations(quotations) {
            var tbody = $('#quotationsTable tbody');
            tbody.empty();
            
            if (quotations.length === 0) {
                tbody.html('<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No quotations found</td></tr>');
                return;
            }
            
            var html = '';
            for (var i = 0; i < quotations.length; i++) {
                var quotation = quotations[i];
                var quotationDate = new Date(quotation.QuotationDate).toLocaleDateString();
                var validUntil = quotation.ValidUntil ? new Date(quotation.ValidUntil).toLocaleDateString() : 'N/A';
                var statusBadge = getStatusBadge(quotation.Status);
                
                html += '<tr class="hover:bg-gray-50">' +
                    '<td class="px-4 py-3 whitespace-nowrap">' +
                        '<div class="text-sm font-medium text-gray-900">' + quotation.QuotationNo + '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3">' +
                        '<div class="text-sm text-gray-900">' + quotation.CustomerName + '</div>' +
                        '<div class="text-xs text-gray-500">' + (quotation.Phone || '-') + '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap">' +
                        '<div class="text-sm text-gray-900">' + quotationDate + '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap">' +
                        '<div class="text-sm text-gray-900">' + validUntil + '</div>' +
                    '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900">' +
                        parseFloat(quotation.GrandTotal).toFixed(2) +
                    '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap">' +
                        statusBadge +
                    '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">' +
                        '<button onclick="viewQuotation(' + quotation.QuotationID + ')" class="text-purple-600 hover:text-purple-900 mr-3" title="View">' +
                            '<i class="fas fa-eye"></i>' +
                        '</button>' +
                        '<button onclick="printQuotationDirect(' + quotation.QuotationID + ')" class="text-green-600 hover:text-green-900 mr-3" title="Print">' +
                            '<i class="fas fa-print"></i>' +
                        '</button>' +
                    '</td>' +
                    '</tr>';
            }
            
            tbody.html(html);
        }

        function getStatusBadge(status) {
            var badges = {
                'draft': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Draft</span>',
                'sent': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Sent</span>',
                'approved': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>',
                'declined': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Declined</span>',
                'invoice_made': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Invoice Made</span>'
            };
            return badges[status] || badges['draft'];
        }

        function viewQuotation(quotationId) {
            currentQuotationId = quotationId;
            $.ajax({
                url: 'quotation_functions.php',
                type: 'POST',
                data: { 
                    action: 'get_quotation_details',
                    quotationId: quotationId 
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        displayQuotationDetails(data.quotation, data.items);
                        document.getElementById('viewQuotationModal').classList.remove('hidden');
                        
                        // Update status buttons based on current status
                        updateStatusButtons(data.quotation.Status);
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                },
                error: function() {
                    alert('Network error occurred. Please try again.');
                }
            });
        }

        function displayQuotationDetails(quotation, items) {
            var quotationDate = new Date(quotation.QuotationDate).toLocaleDateString();
            var validUntil = quotation.ValidUntil ? new Date(quotation.ValidUntil).toLocaleDateString() : 'N/A';
            
            var html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">' +
                '<div>' +
                    '<h4 class="text-lg font-medium text-gray-900 mb-2">Quotation Information</h4>' +
                    '<div class="bg-gray-50 p-4 rounded-lg">' +
                        '<div class="mb-2"><span class="font-medium">Quotation No:</span> ' + quotation.QuotationNo + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Date:</span> ' + quotationDate + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Valid Until:</span> ' + validUntil + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Subject:</span> ' + (quotation.QuotationSubject || '-') + '</div>' +
                    '</div>' +
                '</div>' +
                '<div>' +
                    '<h4 class="text-lg font-medium text-gray-900 mb-2">Customer Information</h4>' +
                    '<div class="bg-gray-50 p-4 rounded-lg">' +
                        '<div class="mb-2"><span class="font-medium">Name:</span> ' + quotation.CustomerName + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Phone:</span> ' + (quotation.Phone || '-') + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Email:</span> ' + (quotation.Email || '-') + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Address:</span> ' + (quotation.Address || '-') + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div>' +
                '<h4 class="text-lg font-medium text-gray-900 mb-2">Quotation Items</h4>' +
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
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>' +
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody class="bg-white divide-y divide-gray-200">';
            
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                html += '<tr>' +
                    '<td class="px-4 py-2 text-sm">' + (i + 1) + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + item.Detail + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + item.Media + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + item.Width + ' × ' + item.Height + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + item.Qty + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + item.Sqft + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + item.Rate + '</td>' +
                    '<td class="px-4 py-2 text-sm">' + item.Total + '</td>' +
                '</tr>';
            }
            
            html += '</tbody></table></div></div>' +
            '<div class="mt-4 bg-gray-50 p-4 rounded-lg">' +
                '<div class="grid grid-cols-2 gap-4">' +
                    '<div>' +
                        '<div class="mb-2"><span class="font-medium">Sub Total:</span> ' + parseFloat(quotation.SubTotal).toFixed(2) + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Advance:</span> ' + parseFloat(quotation.Advance).toFixed(2) + '</div>' +
                    '</div>' +
                    '<div>' +
                        '<div class="mb-2"><span class="font-medium">Total GST:</span> ' + parseFloat(quotation.TotalGST).toFixed(2) + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Total NTN:</span> ' + parseFloat(quotation.TotalNTR).toFixed(2) + '</div>' +
                        '<div class="mb-2"><span class="font-bold text-lg">Grand Total:</span> ' + parseFloat(quotation.GrandTotal).toFixed(2) + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
            
            $('#quotationDetailsContent').html(html);
        }

        function closeViewModal() {
            document.getElementById('viewQuotationModal').classList.add('hidden');
        }

        function updateStatusButtons(status) {
            // Hide all status buttons
            $('#approveBtn, #declineBtn, #invoiceBtn').hide();
            
            // Show appropriate buttons based on current status
            if (status === 'draft') {
                $('#approveBtn, #declineBtn').show();
            } else if (status === 'sent') {
                $('#approveBtn, #declineBtn').show();
            } else if (status === 'approved') {
                $('#invoiceBtn').show();
            }
        }

        function printQuotation() {
            window.print();
        }

        function printQuotationDirect(quotationId) {
            window.open('print_quotation.php?id=' + quotationId, '_blank');
        }

        function updateStatus(status) {
            if (!currentQuotationId) return;
            
            $.ajax({
                url: 'quotation_functions.php',
                type: 'POST',
                data: { 
                    action: 'update_quotation_status',
                    quotationId: currentQuotationId,
                    status: status
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        alert('Quotation status updated to ' + status + '!');
                        loadQuotations(); // Reload the list
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                },
                error: function() {
                    alert('Network error occurred. Please try again.');
                }
            });
        }
    </script>
</body>
</html>