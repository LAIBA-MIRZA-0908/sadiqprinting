<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// add_quotation.php
include 'header.php';
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Quotation</title>
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
                <h1 class="text-2xl font-bold text-gray-800">Add New Quotation</h1>
                <p class="text-gray-600">Create quotation for customer</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="quotations_list.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm flex items-center">
                    <i class="fas fa-list mr-2"></i> View Quotations
                </a>
            </div>
        </div>

        <form id="quotationForm">
            <!-- Quotation Header -->
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="bg-purple-600 text-white px-6 py-4 rounded-t-lg">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-file-invoice mr-2"></i>Quotation Details
                    </h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quotation #</label>
                            <input type="text" id="QuotationNo" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dated</label>
                            <input type="date" id="QuotationDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
                            <input type="date" id="ValidUntil" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer *</label>
                            <select id="CustomerID" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" required>
                                <option value="">Select Customer</option>
                            </select>
                        </div>
                        <div class="lg:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quotation Subject</label>
                            <input type="text" id="QuotationSubject" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" placeholder="Enter quotation subject">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Quotation Items -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                            <h3 class="text-lg font-semibold flex items-center">
                                <i class="fas fa-list-alt mr-2"></i>Quotation Particulars
                            </h3>
                            <button type="button" class="bg-white text-green-600 px-3 py-1 rounded text-sm font-medium hover:bg-gray-100" onclick="addQuotationRow()">
                                <i class="fas fa-plus mr-1"></i> Add Row
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="table-responsive overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border border-gray-300" id="quotationTable">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr#</th>
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
                                    <tbody id="quotationTableBody" class="bg-white divide-y divide-gray-200">
                                        <!-- Dynamic rows will be added here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-3">
                        <h3 class="text-base font-semibold mb-3 text-gray-800">Summary</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-medium text-gray-700">Total</label>
                                <input type="number" id="SubTotal" class="w-24 px-2 py-1 border border-gray-300 rounded numeric-input bg-gray-100 text-sm" readonly>
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-medium text-gray-700">Advance</label>
                                <input type="number" id="Advance" class="w-24 px-2 py-1 border border-gray-300 rounded numeric-input text-sm" step="0.01" value="0">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-medium text-gray-700">GST Rate (%)</label>
                                <input type="number" id="GSTRate" class="w-24 px-2 py-1 border border-gray-300 rounded numeric-input text-sm" step="0.01" value="0">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-medium text-gray-700">Total GST</label>
                                <input type="number" id="TotalGST" class="w-24 px-2 py-1 border border-gray-300 rounded numeric-input bg-gray-100 text-sm" readonly>
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-medium text-gray-700">NTN Rate (%)</label>
                                <input type="number" id="NTRRate" class="w-24 px-2 py-1 border border-gray-300 rounded numeric-input text-sm" step="0.01" value="0">
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-medium text-gray-700">Total NTN</label>
                                <input type="number" id="TotalNTR" class="w-24 px-2 py-1 border border-gray-300 rounded numeric-input bg-gray-100 text-sm" readonly>
                            </div>
                            <div class="border-t pt-2 mt-2">
                                <div class="flex justify-between items-center">
                                    <label class="text-xs font-bold text-gray-900">Grand Total</label>
                                    <input type="number" id="GrandTotal" class="w-24 px-2 py-1 border border-gray-300 rounded numeric-input bg-gray-100 font-bold text-base" readonly>
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
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-save mr-2"></i> Save Quotation
                </button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var quotationRowCount = 0;
        var materials = [];
        var customers = [];

        $(document).ready(function() {
            // Set today's date
            $('#QuotationDate').val(new Date().toISOString().split('T')[0]);
            
            // Set valid until date to 30 days from now
            var validUntil = new Date();
            validUntil.setDate(validUntil.getDate() + 30);
            $('#ValidUntil').val(validUntil.toISOString().split('T')[0]);
            
            // Generate Quotation No
            generateQuotationNo();
            
            // Load data
            loadCustomers();
            loadMaterials();
            
            // Add first quotation row
            addQuotationRow();
            
            // Form submission
            $('#quotationForm').submit(function(e) {
                e.preventDefault();
                saveQuotation();
            });
            
            // Customer change handler
            $('#CustomerID').change(function() {
                updateCustomerName();
            });
            
            // Tax calculation handlers
            $('#GSTRate, #NTRRate, #Advance').on('input', calculateTotals);
        });

        function generateQuotationNo() {
            var timestamp = new Date().getTime();
            var random = Math.floor(Math.random() * 1000);
            $('#QuotationNo').val('QUO-' + timestamp + '-' + random);
        }

        function loadCustomers() {
            $.ajax({
                url: 'quotation_functions.php',
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

        function loadMaterials() {
            $.ajax({
                url: 'quotation_functions.php',
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

        function addQuotationRow() {
            quotationRowCount++;
            var row = '<tr id="quotationRow-' + quotationRowCount + '">' +
                '<td class="px-4 py-2 whitespace-nowrap"><input type="text" class="w-full px-2 py-1 border border-gray-300 rounded text-center" value="' + quotationRowCount + '" readonly></td>' +
                '<td class="px-4 py-2"><input type="text" class="w-full px-2 py-1 border border-gray-300 rounded detail-input" placeholder="Enter detail"></td>' +
                '<td class="px-4 py-2"><select class="w-full px-2 py-1 border border-gray-300 rounded media-select"><option value="">Select Media</option></select></td>' +
                '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input width-input" step="1" value="0" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
                '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input height-input" step="1" value="0" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
                '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input qty-input text-center" value="1" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
                '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input sqft-input" step="1" value="0" readonly></td>' +
                '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input rate-input" step="0.01" value="0" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
                '<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 border border-gray-300 rounded numeric-input total-input" step="0.01" value="0" readonly></td>' +
                '<td class="px-4 py-2 text-center"><button type="button" class="text-red-600 hover:text-red-800" onclick="removeQuotationRow(' + quotationRowCount + ')"><i class="fas fa-trash"></i></button></td>' +
                '</tr>';
            
            $('#quotationTableBody').append(row);
            updateMaterialDropdowns();
        }

        function removeQuotationRow(rowId) {
            $('#quotationRow-' + rowId).remove();
            updateSerialNumbers();
            calculateTotals();
        }

        function updateSerialNumbers() {
            $('#quotationTableBody tr').each(function(index) {
                $(this).find('td:first input').val(index + 1);
            });
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
            
            // Calculate GST and NTN amounts based on the subtotal
            var totalGST = subtotal * (gstRate / 100);
            var totalNTR = subtotal * (ntrRate / 100);
            
            // Grand Total = Subtotal + GST + NTN - Advance
            var grandTotal = subtotal + totalGST + totalNTR - advance;
            
            $('#SubTotal').val(subtotal.toFixed(2));
            $('#TotalGST').val(totalGST.toFixed(2));
            $('#TotalNTR').val(totalNTR.toFixed(2));
            $('#GrandTotal').val(grandTotal.toFixed(2));
        }

        function saveQuotation() {
            if (!validateForm()) return;
            
            var formData = {
                action: 'create_quotation',
                QuotationNo: $('#QuotationNo').val(),
                QuotationDate: $('#QuotationDate').val(),
                ValidUntil: $('#ValidUntil').val(),
                CustomerID: $('#CustomerID').val(),
                CustomerName: $('#CustomerID option:selected').text(),
                QuotationSubject: $('#QuotationSubject').val(),
                SubTotal: $('#SubTotal').val(),
                Advance: $('#Advance').val(),
                GSTRate: $('#GSTRate').val(),
                TotalGST: $('#TotalGST').val(),
                NTRRate: $('#NTRRate').val(),
                TotalNTR: $('#TotalNTR').val(),
                GrandTotal: $('#GrandTotal').val(),
                items: []
            };
            
            // Collect quotation items
            $('#quotationTableBody tr').each(function() {
                var row = $(this);
                var width = parseFloat(row.find('.width-input').val()) || 0;
                var height = parseFloat(row.find('.height-input').val()) || 0;
                var qty = parseFloat(row.find('.qty-input').val()) || 0;
                
                if (width > 0 || height > 0 || qty > 0 || row.find('.detail-input').val()) {
                    formData.items.push({
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
                alert('Please add at least one item to the quotation.');
                return;
            }
            
            $.ajax({
                url: 'quotation_functions.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        alert('Quotation saved successfully!');
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
            saveQuotation();
            // Print functionality will be added after save
        }

        function validateForm() {
            if (!$('#QuotationDate').val()) {
                alert('Please select quotation date.');
                return false;
            }
            if (!$('#CustomerID').val()) {
                alert('Please select a customer.');
                return false;
            }
            return true;
        }

        function resetForm() {
            document.getElementById('quotationForm').reset();
            $('#QuotationDate').val(new Date().toISOString().split('T')[0]);
            
            // Set valid until date to 30 days from now
            var validUntil = new Date();
            validUntil.setDate(validUntil.getDate() + 30);
            $('#ValidUntil').val(validUntil.toISOString().split('T')[0]);
            
            $('#quotationTableBody').empty();
            quotationRowCount = 0;
            generateQuotationNo();
            addQuotationRow();
            calculateTotals();
        }
    </script>
</body>
</html>