<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// add_invoice.php
include 'header.php';
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .numeric-input {
            text-align: right;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Add New Invoice</h1>
                <p class="text-gray-600">Create invoice for customer</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="invoices_list.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm flex items-center">
                    <i class="fas fa-list mr-2"></i> View Invoices
                </a>
            </div>
        </div>

        <form id="invoiceForm">
            <!-- Invoice Header -->
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Invoice Details
                    </h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Invoice #</label>
                            <input type="text" id="InvoiceNo" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dated</label>
                            <input type="date" id="InvoiceDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">PO No</label>
                            <select id="PONo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select PO</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer *</label>
                            <select id="CustomerID" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select Customer</option>
                            </select>
                        </div>
                        <div class="lg:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Subject</label>
                            <input type="text" id="InvoiceSubject" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Enter invoice subject">
                        </div>
                    </div>
                </div>
            </div>

          <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Invoice Items -->
    <div class="lg:col-span-4">
        <div class="bg-white rounded-lg shadow-md">
            <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-list-alt mr-2"></i>Invoice Particulars
                </h3>
                <button type="button" class="bg-white text-green-600 px-3 py-1 rounded text-sm font-medium hover:bg-gray-100" onclick="addInvoiceRow()">
                    <i class="fas fa-plus mr-1"></i> Add Row
                </button>
            </div>
            <div class="p-4">
                <div class="table-responsive overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-300" id="invoiceTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Job #</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Media</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Width</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Height</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Sqft</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Rate</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Total</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Dynamic rows will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Section -->
   <!-- Summary Section -->
<div class="lg:col-span-1">
    <div class="bg-white rounded-lg shadow-md p-4">
        <h3 class="text-base font-semibold mb-4 text-gray-800 flex items-center justify-between">
            <span class="text-gray-700">Summary</span>
            <div class="text-xs text-gray-500">Total: <span id="summaryTotal" class="font-bold text-gray-900">0.00</span></div>
        </h3>
        
        <div class="bg-gray-50 p-3 rounded-lg">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-medium text-gray-700">Sub Total</span>
                    <input type="number" id="SubTotal" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input bg-gray-100 text-sm text-right" readonly>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-medium text-gray-700">Advance</span>
                    <input type="number" id="Advance" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-sm text-right" step="0.01" value="0">
                </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-medium text-gray-700">GST Rate (%)</span>
                    <input type="number" id="GSTRate" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-sm text-right" step="0.01" value="0">
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-medium text-gray-700">Total GST</span>
                    <input type="number" id="TotalGST" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input bg-gray-100 text-sm text-right" readonly>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-medium text-gray-700">NTN Rate (%)</span>
                    <input type="number" id="NTRRate" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-sm text-right" step="0.01" value="0">
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-medium text-gray-700">Total NTN</span>
                        <input type="number" id="TotalNTR" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input bg-gray-100 text-sm text-right" readonly>
                    </div>
                </div>
                <div class="border-t pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-900">Grand Total</span>
                        <input type="number" id="GrandTotal" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input bg-gray-100 text-sm text-right font-bold text-base" readonly>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="resetForm()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button type="button" onclick="saveAndPrint()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-print mr-2"></i> Save & Print
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-save mr-2"></i> Save Invoice
                </button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var invoiceRowCount = 0;
        var materials = [];
        var customers = [];
        var jobOrders = [];

        $(document).ready(function() {
            // Set today's date
            $('#InvoiceDate').val(new Date().toISOString().split('T')[0]);
            
            // Generate Invoice No
            generateInvoiceNo();
            
            // Load data
            loadCustomers();
            loadJobOrders();
            loadMaterials();
             if (invoiceRowCount === 0) {
                    for (var i = 1; i <= 7; i++) {
            // Add first invoice row
            addInvoiceRow();
                    }
                }
            
            // Form submission
            $('#invoiceForm').submit(function(e) {
                e.preventDefault();
                saveInvoice();
            });
            
            // PO No change handler
            $('#PONo').change(function() {
                loadJobOrderDetails($(this).val());
            });
            
            // Customer change handler
            $('#CustomerID').change(function() {
                updateCustomerName();
            });
            
            // Tax calculation handlers
            $('#GSTRate, #NTRRate, #Advance').on('input', calculateTotals);
            // Test: Log when data is loaded
    setTimeout(function() {
        console.log('Customers loaded:', customers.length);
        console.log('Job Orders loaded:', jobOrders.length);
        console.log('Materials loaded:', materials.length);
    }, 1000);
        });

        function generateInvoiceNo() {
            var timestamp = new Date().getTime();
            var random = Math.floor(Math.random() * 1000);
            $('#InvoiceNo').val('INV-' + timestamp + '-' + random);
        }

        function loadCustomers() {
            $.ajax({
                url: 'invoice_functions.php',
                type: 'POST',
                data: { action: 'get_customers' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        customers = data.customers;
                        updateCustomerDropdown();
                    }
                },
                error: function() {
                    console.error('Failed to load customers');
                }
            });
        }

        function loadJobOrders() {
            $.ajax({
                url: 'invoice_functions.php',
                type: 'POST',
                data: { action: 'get_job_orders' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        jobOrders = data.jobOrders;
                        updatePODropdown();
                    }
                },
                error: function() {
                    console.error('Failed to load job orders');
                }
            });
        }

        function loadMaterials() {
            $.ajax({
                url: 'invoice_functions.php',
                type: 'POST',
                data: { action: 'get_materials' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        materials = data.materials;
                        updateMaterialDropdowns();
                    }
                },
                error: function() {
                    console.error('Failed to load materials');
                }
            });
        }

        function updateCustomerDropdown() {
            $('#CustomerID').empty().append('<option value="">Select Customer</option>');
            customers.forEach(function(customer) {
                $('#CustomerID').append('<option value="' + customer.CustomerID + '">' + customer.CustomerName + '</option>');
            });
        }

        function updatePODropdown() {
            $('#PONo').empty().append('<option value="">Select PO</option>');
            jobOrders.forEach(function(job) {
                $('#PONo').append('<option value="' + job.JobOrderNo + '">' + job.JobOrderNo + ' - ' + job.CustomerName + '</option>');
            });
        }

       function updateMaterialDropdowns() {
    $('.media-select').each(function() {
        var $select = $(this);
        var currentValue = $select.val();
        
        // Clear and repopulate the dropdown
        $select.empty().append('<option value="">Select Media</option>');
        
        if (materials && materials.length > 0) {
            materials.forEach(function(material) {
                $select.append('<option value="' + material.name + '">' + material.name + '</option>');
            });
        }
        
        // Restore the previous value
        $select.val(currentValue);
    });
}

       function updateCustomerName() {
    var customerId = $('#CustomerID').val();
    var customer = customers.find(c => c.CustomerID == customerId);
    if (customer) {
        // Customer is selected, you can use the customer data if needed
        console.log('Selected customer:', customer);
    }
}

      function loadJobOrderDetails(jobOrderNo) {
    if (!jobOrderNo) return;
    
    console.log('Loading job order details for:', jobOrderNo); // Debug log
    
    $.ajax({
        url: 'invoice_functions.php',
        type: 'POST',
        data: { 
            action: 'get_job_order_details',
            jobOrderNo: jobOrderNo 
        },
        dataType: 'json',
        success: function(data) {
            console.log('Job order details response:', data); // Debug log
            
            if (data.success) {
                // Find matching customer by name
                var matchingCustomer = customers.find(c => c.CustomerName === data.order.CustomerName);
                if (matchingCustomer) {
                    $('#CustomerID').val(matchingCustomer.CustomerID);
                    console.log('Set customer to:', matchingCustomer.CustomerName); // Debug log
                } else {
                    console.log('No matching customer found for:', data.order.CustomerName); // Debug log
                }
                
                // Clear existing rows
                $('#invoiceTableBody').empty();
                invoiceRowCount = 0;
                
                // Add job order details
                if (data.details && data.details.length > 0) {
                    data.details.forEach(function(detail, index) {
                        console.log('Adding detail:', detail); // Debug log
                        addInvoiceRow();
                        var lastRow = $('#invoiceTableBody tr:last');
                        
                        // Set JobNo with the actual JobOrderNo
                        lastRow.find('.job-no').val(data.order.JobOrderNo);
                        lastRow.find('.detail-input').val(detail.Detail || '');
                        lastRow.find('.media-select').val(detail.Media || '');
                        lastRow.find('.width-input').val(detail.Width || 0);
                        lastRow.find('.height-input').val(detail.Height || 0);
                        lastRow.find('.qty-input').val(detail.Qty || 1);
                        lastRow.find('.sqft-input').val(detail.Sqft || 0);
                        lastRow.find('.rate-input').val('0'); // Default rate
                        calculateRowTotal(lastRow);
                    });
                } else {
                    console.log('No details found for this job order'); // Debug log
                    // Add one empty row if no details
                    addInvoiceRow();
                }
                
                calculateTotals();
            } else {
                console.error('Error loading job order details:', data.message);
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading job order details:', status, error);
            console.log('Response Text:', xhr.responseText);
            alert('Network error occurred. Please try again.');
        }
    });
}
      function addInvoiceRow() {
    invoiceRowCount++;
    var row = '<tr id="invoiceRow-' + invoiceRowCount + '">' +
        '<td class="px-4 py-2 whitespace-nowrap"><input type="text" class="w-full px-2 py-1 border border-gray-300 rounded text-center job-no" placeholder="Job #"></td>' +
        '<td class="px-4 py-2"><input type="text" class="w-full px-2 py-1 border border-gray-300 rounded detail-input" placeholder="Enter detail"></td>' +
        '<td class="px-4 py-2"><select class="w-full px-2 py-1 border border-gray-300 rounded media-select"><option value="">Select Media</option></select></td>' +
        '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input width-input" step="1" value="0" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
        '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input height-input" step="1" value="0" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
        '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input qty-input text-center" value="1" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
        '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input sqft-input" step="1" value="0" readonly></td>' +
        '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input rate-input" step="0.01" value="0" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
        '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input total-input" step="0.01" value="0" readonly></td>' +
        '<td class="px-4 py-2 text-center"><button type="button" class="text-red-600 hover:text-red-800" onclick="removeInvoiceRow(' + invoiceRowCount + ')"><i class="fas fa-trash"></i></button></td>' +
        '</tr>';
    
    $('#invoiceTableBody').append(row);
    
    // Update material dropdown for the new row
    updateMaterialDropdowns();
}

        function removeInvoiceRow(rowId) {
            $('#invoiceRow-' + rowId).remove();
            calculateTotals();
        }

        function calculateRowTotal(row) {
            var width = parseFloat(row.find('.width-input').val()) || 0;
            var height = parseFloat(row.find('.height-input').val()) || 0;
            var qty = parseFloat(row.find('.qty-input').val()) || 0;
            var sqft = width * height * qty;
            var rate = parseFloat(row.find('.rate-input').val()) || 0;
            var total = sqft * rate;
            
            row.find('.sqft-input').val(sqft.toFixed(2));
            row.find('.total-input').val(total.toFixed(2));
            
            calculateTotals();
        }

        function calculateTotals() {
    var subtotal = 0;
    $('.total-input').each(function() {
        subtotal += parseFloat($(this).val()) || 0;
    });
    
    var advance = parseFloat($('#Advance').val()) || 0;
    var gstRate = parseFloat($('#GSTRate').val()) || 0;
    var ntrRate = parseFloat($('#NTRRate').val()) || 0;
    
    // Calculate GST and NTN amounts based on the subtotal (not subtotal minus advance)
    var totalGST = subtotal * (gstRate / 100);
    var totalNTR = subtotal * (ntrRate / 100);
    
    // Grand Total = Subtotal + GST + NTN - Advance
    var grandTotal = subtotal + totalGST + totalNTR - advance;
    
    $('#SubTotal').val(subtotal.toFixed(2));
    $('#TotalGST').val(totalGST.toFixed(2));
    $('#TotalNTR').val(totalNTR.toFixed(2));
    $('#GrandTotal').val(grandTotal.toFixed(2));
}

        function saveInvoice() {
            if (!validateForm()) return;
            
            var formData = {
                action: 'create_invoice',
                InvoiceNo: $('#InvoiceNo').val(),
                InvoiceDate: $('#InvoiceDate').val(),
                PONo: $('#PONo').val() || null,
                CustomerID: $('#CustomerID').val(),
                CustomerName: $('#CustomerID option:selected').text(),
                InvoiceSubject: $('#InvoiceSubject').val(),
                SubTotal: $('#SubTotal').val(),
                Advance: $('#Advance').val(),
                GSTRate: $('#GSTRate').val(),
                TotalGST: $('#TotalGST').val(),
                NTRRate: $('#NTRRate').val(),
                TotalNTR: $('#TotalNTR').val(),
                GrandTotal: $('#GrandTotal').val(),
                items: []
            };
            
            // Collect invoice items
            $('#invoiceTableBody tr').each(function() {
                var row = $(this);
                var width = parseFloat(row.find('.width-input').val()) || 0;
                var height = parseFloat(row.find('.height-input').val()) || 0;
                var qty = parseFloat(row.find('.qty-input').val()) || 0;
                
                if (width > 0 || height > 0 || qty > 0 || row.find('.detail-input').val()) {
                    formData.items.push({
                        JobNo: row.find('.job-no').val(),
                        Detail: row.find('.detail-input').val(),
                        Media: row.find('.media-select').val(),
                        Width: width,
                        Height: height,
                        Qty: qty,
                        Sqft: row.find('.sqft-input').val(),
                        Rate: row.find('.rate-input').val(),
                        Total: row.find('.total-input').val()
                    });
                }
            });
            
            if (formData.items.length === 0) {
                alert('Please add at least one item to the invoice.');
                return;
            }
            
            $.ajax({
                url: 'invoice_functions.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        alert('Invoice saved successfully!');
                        resetForm();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                },
                error: function() {
                    alert('Network error occurred. Please try again.');
                }
            });
        }

        function saveAndPrint() {
            saveInvoice();
            // Print functionality will be added after save
        }

        function validateForm() {
            if (!$('#InvoiceDate').val()) {
                alert('Please select invoice date.');
                return false;
            }
            if (!$('#CustomerID').val()) {
                alert('Please select a customer.');
                return false;
            }
            return true;
        }

        function resetForm() {
            document.getElementById('invoiceForm').reset();
            $('#InvoiceDate').val(new Date().toISOString().split('T')[0]);
            $('#invoiceTableBody').empty();
            invoiceRowCount = 0;
            generateInvoiceNo();
            addInvoiceRow();
            calculateTotals();
        }
    </script>
</body>
</html>