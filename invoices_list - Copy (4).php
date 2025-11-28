<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// invoices_list.php
include 'header.php';
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Invoices List</h1>
                <p class="text-gray-600">View all invoices</p>
            </div>
            <div>
                <a href="add_invoice.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Invoice
                </a>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search by invoice number or customer..." 
                               class="w-full pl-10 pr-4 py-1 border border-gray-400 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="invoicesTable">
                   <thead class="bg-gray-50">
    <tr>
        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Invoice #</th>
        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">PO #</th>
        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Date</th>
        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Customer</th>
        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 uppercase tracking-wider">Amount</th>
        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Status</th>
        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
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

    <!-- View Invoice Modal -->
    <div id="viewInvoiceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Invoice Details</h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="invoiceDetailsContent" class="mt-4">
                    <!-- Invoice details will be loaded here -->
                </div>
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button onclick="closeViewModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Close
                    </button>
                    <button onclick="printInvoice()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        var currentPage = 1;
        var recordsPerPage = 10;
        var allInvoices = [];

        $(document).ready(function() {
            loadInvoices();
            
            $('#searchInput').on('input', function() {
                filterAndDisplayInvoices();
            });
            
            $('#dateFilter, #statusFilter').change(function() {
                filterAndDisplayInvoices();
            });

             // ✅ Initialize Select2 on dropdowns
   $('#dateFilter, #statusFilter').select2({
        placeholder: 'Select an option', // placeholder text
        allowClear: true,
        width: '100%'
    });
        });

        function loadInvoices() {
            $.ajax({
                url: 'invoice_functions.php',
                type: 'POST',
                data: { action: 'get_invoices' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        allInvoices = data.invoices;
                        filterAndDisplayInvoices();
                    } else {
                        $('#invoicesTable tbody').html('<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Error loading invoices</td></tr>');
                    }
                },
                error: function() {
                    $('#invoicesTable tbody').html('<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Network error</td></tr>');
                }
            });
        }

        function filterAndDisplayInvoices() {
            var searchTerm = $('#searchInput').val().toLowerCase();
            var dateFilter = $('#dateFilter').val();
            var statusFilter = $('#statusFilter').val();
            
            var filteredInvoices = allInvoices.filter(function(invoice) {
                var matchesSearch = !searchTerm || 
                    invoice.CustomerName.toLowerCase().includes(searchTerm) || 
                    invoice.InvoiceNo.toLowerCase().includes(searchTerm);
                
                var matchesDate = true;
                if (dateFilter !== 'all') {
                    var invoiceDate = new Date(invoice.InvoiceDate);
                    var today = new Date();
                    
                    if (dateFilter === 'today') {
                        matchesDate = invoiceDate.toDateString() === today.toDateString();
                    } else if (dateFilter === 'week') {
                        var weekAgo = new Date(today);
                        weekAgo.setDate(today.getDate() - 7);
                        matchesDate = invoiceDate >= weekAgo;
                    } else if (dateFilter === 'month') {
                        var monthAgo = new Date(today);
                        monthAgo.setMonth(today.getMonth() - 1);
                        matchesDate = invoiceDate >= monthAgo;
                    }
                }
                
                var matchesStatus = statusFilter === 'all' || invoice.Status === statusFilter;
                
                return matchesSearch && matchesDate && matchesStatus;
            });
            
            displayInvoices(filteredInvoices);
        }

    function displayInvoices(invoices) {
    var tbody = $('#invoicesTable tbody');
    tbody.empty();
    
    if (invoices.length === 0) {
        tbody.html('<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No invoices found</td></tr>');
        return;
    }
    
    var html = '';
    for (var i = 0; i < invoices.length; i++) {
        var invoice = invoices[i];

        // FORMAT DATE → dd-mm-yyyy
        var dateObj = new Date(invoice.InvoiceDate);
        var day = String(dateObj.getDate()).padStart(2, '0');
        var month = String(dateObj.getMonth() + 1).padStart(2, '0');
        var year = dateObj.getFullYear();
        var invoiceDate = `${day}-${month}-${year}`;

        // MASK PHONE
        var maskedCell = invoice.Phone;
        if (maskedCell && maskedCell.length > 4) {
            maskedCell = maskedCell.substring(0, 4) + "****";
        }

        // BADGE FOR INVOICE #
        var invoiceBadge = `
            <span class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full">
                ${invoice.InvoiceNo}
            </span>
        `;

        // STATUS BADGE
        var statusBadge = getStatusBadge(invoice.Status);

       html += `
<tr class="hover:bg-gray-50">

    <!-- Invoice No Badge -->
    <td class="px-2 py-1 cursor-pointer" onclick="viewInvoice(${invoice.InvoiceID})">
        <span class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full">
            ${invoice.InvoiceNo}
        </span>
    </td>

    <!-- PO Number -->
    <td class="px-2 py-1">
        <div class="text-sm text-gray-900">${invoice.PONo || '-'}</div>
    </td>

    <!-- Date (dd-mm-yyyy) -->
    <td class="px-2 py-1">
        <div class="text-sm text-gray-900">${invoiceDate}</div>
    </td>

    <!-- Customer + Masked Phone -->
    <td class="px-2 py-1">
        <div class="text-sm text-gray-900">
            ${invoice.CustomerName} 
            <span class="text-gray-500">(${maskedCell || '-'})</span>
        </div>
    </td>

    <!-- Amount -->
    <td class="px-2 py-1 text-right">
        Rs. ${parseFloat(invoice.GrandTotal).toLocaleString('en-PK', { minimumFractionDigits: 2 })}
    </td>

    <!-- Status Badge -->
    <td class="px-2 py-1">
        ${statusBadge}
    </td>

    <!-- Actions -->
    <td class="px-2 py-1 text-center">

        <button onclick="viewInvoice(${invoice.InvoiceID})"
            class="text-blue-600 hover:text-blue-900 mr-3" title="View">
            <i class="fas fa-eye"></i>
        </button>

        <button onclick="printInvoiceDirect(${invoice.InvoiceID})"
            class="text-green-600 hover:text-green-900 mr-3" title="Print">
            <i class="fas fa-print"></i>
        </button>

        <button onclick="sendInvoiceWhatsApp('${invoice.Phone}', ${invoice.InvoiceID})"
            class="text-green-500 hover:text-green-700" title="Send via WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </button>

    </td>

</tr>
`;

    }

    tbody.html(html);
}




  function getStatusBadge(status) {
    var badges = {
        'draft': '<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">Draft</span>',
        'sent': '<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-200 text-blue-700">Sent</span>',
        'paid': '<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-200 text-green-700">Paid</span>',
        'cancelled': '<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-200 text-red-700">Cancelled</span>'
    };
    return badges[status] || badges['draft'];
}


        function viewInvoice(invoiceId) {
            $.ajax({
                url: 'invoice_functions.php',
                type: 'POST',
                data: { 
                    action: 'get_invoice_details',
                    invoiceId: invoiceId 
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        displayInvoiceDetails(data.invoice, data.items);
                        document.getElementById('viewInvoiceModal').classList.remove('hidden');
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                },
                error: function() {
                    alert('Network error occurred. Please try again.');
                }
            });
        }

        function displayInvoiceDetails(invoice, items) {
            var invoiceDate = new Date(invoice.InvoiceDate).toLocaleDateString();
            
            var html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">' +
                '<div>' +
                    '<h4 class="text-lg font-medium text-gray-900 mb-2">Invoice Information</h4>' +
                    '<div class="bg-gray-50 p-4 rounded-lg">' +
                        '<div class="mb-2"><span class="font-medium">Invoice No:</span> ' + invoice.InvoiceNo + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Date:</span> ' + invoiceDate + '</div>' +
                        '<div class="mb-2"><span class="font-medium">PO No:</span> ' + (invoice.PONo || '-') + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Subject:</span> ' + (invoice.InvoiceSubject || '-') + '</div>' +
                    '</div>' +
                '</div>' +
                '<div>' +
                    '<h4 class="text-lg font-medium text-gray-900 mb-2">Customer Information</h4>' +
                    '<div class="bg-gray-50 p-4 rounded-lg">' +
                        '<div class="mb-2"><span class="font-medium">Name:</span> ' + invoice.CustomerName + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Phone:</span> ' + (invoice.Phone || '-') + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Email:</span> ' + (invoice.Email || '-') + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Address:</span> ' + (invoice.Address || '-') + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div>' +
                '<h4 class="text-lg font-medium text-gray-900 mb-2">Invoice Items</h4>' +
                '<div class="overflow-x-auto">' +
                    '<table class="min-w-full divide-y divide-gray-200 border border-gray-300">' +
                        '<thead class="bg-gray-50">' +
                            '<tr>' +
                                '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Job #</th>' +
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
                    '<td class="px-4 py-2 text-sm">' + (item.JobNo || '-') + '</td>' +
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
                        '<div class="mb-2"><span class="font-medium">Sub Total:</span> ' + parseFloat(invoice.SubTotal).toFixed(2) + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Advance:</span> ' + parseFloat(invoice.Advance).toFixed(2) + '</div>' +
                    '</div>' +
                    '<div>' +
                        '<div class="mb-2"><span class="font-medium">Total GST:</span> ' + parseFloat(invoice.TotalGST).toFixed(2) + '</div>' +
                        '<div class="mb-2"><span class="font-medium">Total NTN:</span> ' + parseFloat(invoice.TotalNTR).toFixed(2) + '</div>' +
                        '<div class="mb-2"><span class="font-bold text-lg">Grand Total:</span> ' + parseFloat(invoice.GrandTotal).toFixed(2) + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
            
            $('#invoiceDetailsContent').html(html);
        }


function sendInvoiceWhatsApp(phone, invoiceId) {
    if (!phone) {
        alert("No phone number available for this customer.");
        return;
    }

    // Full absolute URL to your invoice (keep your existing path)
    let invoiceLink = window.location.origin + "/PHP/sadiqprinting/view_invoice.php?id=" + invoiceId;

    // Ensure URL starts with http or https (WhatsApp only makes these clickable)
    if (!/^https?:\/\//i.test(invoiceLink)) {
        invoiceLink = "https://" + invoiceLink;
    }

    // WhatsApp message
    const message = `Assalam o Alaikum! 👋
Here is your invoice from Sadiq Printing.
You can view or download it here: ${invoiceLink}`;

    // Encode message to handle spaces, line breaks, and special characters
    const encodedMessage = encodeURIComponent(message);

    // WhatsApp URL (opens WhatsApp chat with pre-filled message)
    const whatsappUrl = `https://wa.me/${phone}?text=${encodedMessage}`;

    // Open WhatsApp in a new tab/window
    window.open(whatsappUrl, "_blank");
}



        function closeViewModal() {
            document.getElementById('viewInvoiceModal').classList.add('hidden');
        }

       function printInvoice() {
    // Get the invoice content inside the modal
    const invoiceContent = document.getElementById('invoiceDetailsContent').innerHTML;

    // Open a new window for print view
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice Print</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #0c0c0cff;
                }
                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 2px solid #333;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                .header .logo img {
                    height: 70px;
                }
                .header .info {
                    text-align: right;
                }
                h1 {
                    font-size: 22px;
                    margin: 0;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                }
                table, th, td {
                    border: 1px solid #ccc;
                }
                th, td {
                    padding: 8px;
                    text-align: left;
                    font-size: 14px;
                }
                th {
                    background: #f5f5f5;
                }
                .footer {
                    text-align: center;
                    font-size: 12px;
                    color: #555;
                    margin-top: 30px;
                    border-top: 1px solid #ccc;
                    padding-top: 10px;
                }
                @media print {
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
                    <img src="alsadiqlogo.jpg" alt="Company Logo">
                </div>
                <div class="info">
                    <h1>Sadiq Printing</h1>
                    <p>Invoice Document</p>
                </div>
            </div>

            ${invoiceContent}

            <div class="footer">
                <p>Thank you for your business!</p>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}


        function printInvoiceDirect(invoiceId) {
            // This would open a new window with print-friendly format
            window.open('print_invoice.php?id=' + invoiceId, '_blank');
        }
    </script>
</body>
</html>