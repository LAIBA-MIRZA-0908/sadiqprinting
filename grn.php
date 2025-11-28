<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Receive Note (GRN)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        @media print {
            body { font-size: 12px; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            @page { margin: 0.5in; }
            .print-header { text-align: center; margin-bottom: 20px; }
            .print-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .print-table th, .print-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .print-table th { background-color: #f2f2f2; font-weight: bold; }
            .print-signature { margin-top: 50px; border-top: 1px solid #ddd; padding-top: 20px; }
        }
        .editable-cell {
            cursor: pointer;
            position: relative;
            transition: background-color 0.2s;
        }
        .editable-cell:hover {
            background-color: #f0f9ff;
        }
        .editable-cell:focus {
            outline: 2px solid #3b82f6;
            background-color: #fff;
        }
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .print-only { display: none; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Goods Receive Note (GRN)</h1>
                <p class="text-gray-600">Receive goods against purchase orders</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-2">
                <button onclick="viewGRNList()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-list mr-2"></i> View GRNs
                </button>
            </div>
        </div>

        <!-- GRN Form -->
        <div id="grnForm" class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold">Create New GRN</h2>
                <button onclick="generateGRNNumber()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-sync mr-1"></i> Generate GRN Number
                </button>
            </div>

            <form id="grnForm">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">GRN Number *</label>
                        <input type="text" id="grn_number" name="grn_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">GRN Date *</label>
                        <input type="date" id="grn_date" name="grn_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Order *</label>
                        <select id="purchase_order_id" name="purchase_order_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Purchase Order</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="draft">Draft</option>
                            <option value="confirmed">Confirmed</option>
                        </select>
                    </div>
                </div>

                <!-- PO Details (Hidden until PO is selected) -->
                <div id="poDetails" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-medium mb-3">Purchase Order Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">PO Number</p>
                            <p class="font-medium" id="po_number_display">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Supplier</p>
                            <p class="font-medium" id="po_supplier_display">-</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Order Date</p>
                            <p class="font-medium" id="po_date_display">-</p>
                        </div>
                    </div>
                </div>

                <!-- GRN Items -->
                <div id="grnItemsSection" class="hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">GRN Items</h3>
                        <div class="flex space-x-2">
                            <button type="button" onclick="printGRN()" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm">
                                <i class="fas fa-print mr-1"></i> Print GRN
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Product</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Variant</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">Ordered Qty</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">Received Qty</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right">Unit Price</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="grnItemsTableBody">
                                <!-- Items will be loaded here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold">Total Amount:</td>
                                    <td id="totalAmount" class="border border-gray-300 px-4 py-2 text-right font-semibold">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div id="notesSection" class="hidden mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Additional notes for this GRN..."></textarea>
                </div>

                <!-- Submit Buttons -->
                <div id="submitButtons" class="hidden flex justify-end space-x-3">
                    <button type="button" onclick="resetForm()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="button" onclick="saveAndPrintGRN()" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                        <i class="fas fa-save mr-2"></i> Save & Print
                    </button>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                        <i class="fas fa-save mr-2"></i> Save GRN
                    </button>
                </div>
            </form>
        </div>

        <!-- GRN List (Hidden by default) -->
        <div id="grnList" class="bg-white rounded-lg shadow p-6 hidden">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold">Goods Receive Notes</h2>
                <button onclick="showGRNForm()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New GRN
                </button>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="searchGRNs" placeholder="Search GRN number, supplier..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <select id="dateFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </div>

            <!-- GRNs Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GRN Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="grnsTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Loading GRNs...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print View (Hidden by default) -->
    <div id="printView" class="print-only">
        <div class="print-header">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0;">GOODS RECEIVE NOTE</h1>
            <p style="margin: 5px 0;">GRN Number: <span id="print_grn_number"></span></p>
            <p style="margin: 5px 0;">Date: <span id="print_grn_date"></span></p>
        </div>
        
        <div id="print_po_details" style="margin-bottom: 20px;">
            <!-- PO details will be loaded here -->
        </div>
        
        <div id="print_items_table">
            <!-- Items table will be loaded here -->
        </div>
        
        <div class="print-signature">
            <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                <div>
                    <p style="font-weight: bold;">Received By:</p>
                    <p style="border-bottom: 1px solid #000; width: 200px; height: 30px;"></p>
                </div>
                <div>
                    <p style="font-weight: bold;">Authorized By:</p>
                    <p style="border-bottom: 1px solid #000; width: 200px; height: 30px;"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="hidden bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
        <div class="flex items-center">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            <span>Loading...</span>
        </div>
    </div>

    <!-- Success Message -->
    <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 fade-in">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="successText">GRN saved successfully!</span>
        </div>
    </div>

    <!-- Error Message -->
    <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 fade-in">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span id="errorText">An error occurred. Please try again.</span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let currentGRN = null;
        let grnItems = [];

        $(document).ready(function() {
            // Initialize Select2
            $('#purchase_order_id').select2();

            // Load initial data
            generateGRNNumber();
            loadPurchaseOrders();

            // Form submission
            $('#grnForm').submit(function(e) {
                e.preventDefault();
                saveGRN();
            });

            // GRN list filters
            $('#searchGRNs, #statusFilter, #dateFilter').change(function() {
                loadGRNs();
            });
        });

        function generateGRNNumber() {
            const date = new Date();
            const dateStr = date.getFullYear() + 
                           String(date.getMonth() + 1).padStart(2, '0') + 
                           String(date.getDate()).padStart(2, '0');
            const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
            $('#grn_number').val(`GRN-${dateStr}-${random}`);
        }

        function loadPurchaseOrders() {
            $.ajax({
                url: 'grn_functions.php',
                type: 'POST',
                data: { action: 'get_purchase_orders' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        const select = $('#purchase_order_id');
                        select.empty().append('<option value="">Select Purchase Order</option>');
                        
                        data.orders.forEach(order => {
                            select.append(
                                `<option value="${order.id}">${order.po_number} - ${order.supplier_name}</option>`
                            );
                        });
                    } else {
                        showError('Error loading purchase orders: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading purchase orders:', status, error);
                    showError('Network error loading purchase orders');
                }
            });
        }

        function onPOChange() {
            const poId = $('#purchase_order_id').val();
            
            if (!poId) {
                hidePODetails();
                return;
            }

            showLoading(true);
            
            $.ajax({
                url: 'grn_functions.php',
                type: 'POST',
                data: { action: 'get_po_details', po_id: poId },
                dataType: 'json',
                success: function(data) {
                    showLoading(false);
                    if (data.success) {
                        currentGRN = data.po;
                        displayPODetails(data.po);
                        loadPOItems(data.po);
                    } else {
                        showError('Error loading PO details: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    showLoading(false);
                    console.error('AJAX Error loading PO details:', status, error);
                    showError('Network error loading PO details');
                }
            });
        }

        function displayPODetails(po) {
            $('#po_number_display').text(po.po_number);
            $('#po_supplier_display').text(po.supplier_name);
            $('#po_date_display').text(po.order_date);
            $('#poDetails').removeClass('hidden');
            $('#poDetails').addClass('fade-in');
        }

        function hidePODetails() {
            $('#poDetails').addClass('hidden');
            $('#grnItemsSection').addClass('hidden');
            $('#notesSection').addClass('hidden');
            $('#submitButtons').addClass('hidden');
            currentGRN = null;
            grnItems = [];
        }

        function loadPOItems(po) {
            const tbody = $('#grnItemsTableBody');
            tbody.empty();
            
            if (!po.items || po.items.length === 0) {
                tbody.html('<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No items found for this purchase order</td></tr>');
                return;
            }
            
            grnItems = [];
            let totalAmount = 0;
            
            po.items.forEach((item, index) => {
                const receivedQty = item.received_qty || 0;
                const subtotal = receivedQty * item.unit_price;
                totalAmount += subtotal;
                
                grnItems.push({
                    purchase_item_id: item.id,
                    product_id: item.product_id,
                    variant_id: item.variant_id,
                    product_name: item.product_name,
                    variant_name: item.variant_name,
                    ordered_qty: item.quantity,
                    received_qty: receivedQty,
                    unit_price: item.unit_price,
                    subtotal: subtotal
                });
                
                const row = `
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2">
                            <div class="text-sm font-medium text-gray-900">${item.product_name}</div>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <div class="text-sm text-gray-500">${item.variant_name || 'Default'}</div>
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            <div class="text-sm text-gray-900">${item.quantity}</div>
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            <div class="editable-cell px-2 py-1 border rounded cursor-pointer" 
                                 contenteditable="true" 
                                 data-index="${index}"
                                 onblur="updateReceivedQty(${index}, this)">
                                ${receivedQty}
                            </div>
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-right">
                            <div class="text-sm text-gray-900">Rs. ${parseFloat(item.unit_price).toFixed(2)}</div>
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-right">
                            <div class="text-sm font-medium text-gray-900 subtotal-${index}">Rs. ${subtotal.toFixed(2)}</div>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            $('#totalAmount').text('Rs. ' + totalAmount.toFixed(2));
            $('#grnItemsSection').removeClass('hidden');
            $('#grnItemsSection').addClass('fade-in');
            $('#notesSection').removeClass('hidden');
            $('#submitButtons').removeClass('hidden');
        }

        function updateReceivedQty(index, element) {
            const newQty = parseFloat(element.textContent) || 0;
            grnItems[index].received_qty = newQty;
            grnItems[index].subtotal = newQty * grnItems[index].unit_price;
            
            // Update subtotal display
            $(`.subtotal-${index}`).text('Rs. ' + grnItems[index].subtotal.toFixed(2));
            
            // Update total
            calculateTotal();
            
            // Add visual feedback
            $(element).addClass('pulse');
            setTimeout(() => {
                $(element).removeClass('pulse');
            }, 1000);
        }

        function calculateTotal() {
            let total = 0;
            grnItems.forEach(item => {
                total += item.subtotal;
            });
            $('#totalAmount').text('Rs. ' + total.toFixed(2));
        }

        function printGRN() {
    if (!currentGRN || grnItems.length === 0) {
        showError('No GRN data to print');
        return;
    }
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    
    // Generate complete HTML document for printing
    const currentDate = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    const printDocument = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Goods Receive Note - ${$('#grn_number').val()}</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 12px; 
                line-height: 1.4;
                margin: 0;
                padding: 0;
                background: white;
                color: #333;
            }
            
            .print-header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 2px solid #333;
                border-bottom: 3px double #666;
            }
            
            .print-header h1 {
                font-size: 24px;
                font-weight: bold;
                margin: 0;
                color: #333;
                text-transform: uppercase;
                letter-spacing: 2px;
            }
            
            .print-header .subtitle {
                font-size: 14px;
                color: #666;
                margin: 5px 0 0 0;
            }
            
            .print-header .details {
                display: flex;
                justify-content: space-between;
                margin-top: 10px;
                font-size: 12px;
                color: #555;
            }
            
            .print-section {
                margin-bottom: 20px;
            }
            
            .print-section h3 {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 15px;
                color: #333;
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
            }
            
            .print-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                font-size: 11px;
            }
            
            .print-table th {
                background-color: #f8f9fa !important;
                border: 1px solid #dee2e6 !important;
                padding: 10px 8px !important;
                text-align: left;
                font-weight: bold;
                color: #495057;
                font-size: 11px;
            }
            
            .print-table td {
                border: 1px solid #dee2e6 !important;
                padding: 8px !important;
                vertical-align: top;
                font-size: 11px;
            }
            
            .print-table .text-right {
                text-align: right !important;
            }
            
            .print-table .text-center {
                text-align: center !important;
            }
            
            .print-totals {
                margin-top: 20px;
                display: flex;
                justify-content: flex-end;
            }
            
            .print-totals .total-row {
                display: flex;
                justify-content: space-between;
                border-top: 2px solid #333;
                padding-top: 10px;
                font-weight: bold;
            }
            
            .print-totals .total-label {
                font-size: 14px;
                color: #333;
            }
            
            .print-totals .total-amount {
                font-size: 16px;
                font-weight: bold;
                color: #333;
            }
            
            .print-notes {
                margin: 20px 0;
                padding: 15px;
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 5px;
            }
            
            .print-notes h4 {
                font-size: 14px;
                font-weight: bold;
                margin: 0 0 10px 0;
                color: #333;
            }
            
            .print-notes p {
                font-size: 12px;
                color: #555;
                margin: 0;
                line-height: 1.4;
            }
            
            .print-signature {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #dee2e6;
            }
            
            .print-signature .signature-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
            }
            
            .print-signature .signature-box {
                width: 200px;
            }
            
            .print-signature .signature-label {
                font-size: 12px;
                font-weight: bold;
                color: #333;
                margin-bottom: 5px;
            }
            
            .print-signature .signature-line {
                border-bottom: 1px solid #333;
                height: 30px;
            }
            
            .print-footer {
                position: fixed;
                bottom: 10px;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 10px;
                color: #666;
                padding: 5px 0;
            }
            
            @page { 
                margin: 15mm; 
                size: A4;
            }
        </style>
    </head>
    <body>
        <div class="print-container">
            <!-- Header -->
            <div class="print-header">
                <h1>GOODS RECEIVE NOTE</h1>
                <div class="subtitle">Document No: ${$('#grn_number').val()}</div>
                <div class="details">
                    <div>Date: ${$('#grn_date').val()}</div>
                    <div>Status: ${$('#status option:selected').text().toUpperCase()}</div>
                </div>
            </div>
            
            <!-- PO Details -->
            <div class="print-section">
                <h3>Purchase Order Details</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                    <div><strong>PO Number:</strong> ${currentGRN.po_number}</div>
                    <div><strong>Supplier:</strong> ${currentGRN.supplier_name}</div>
                    <div><strong>Order Date:</strong> ${currentGRN.order_date}</div>
                </div>
            </div>
            
            <!-- Items Table -->
            <div class="print-section">
                <h3>Received Items</h3>
                <table class="print-table">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Product Name</th>
                            <th>Variant</th>
                            <th style="text-align: center;">Ordered Qty</th>
                            <th style="text-align: center;">Received Qty</th>
                            <th style="text-align: right;">Unit Price</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${generatePrintItems()}
                    </tbody>
                </table>
                
                <!-- Totals -->
                <div class="print-totals">
                    <div class="total-row">
                        <div class="total-label">Total Amount:</div>
                        <div class="total-amount">Rs. ${calculateTotal()}</div>
                    </div>
                </div>
            </div>
            
            <!-- Notes -->
            ${$('#notes').val() ? `
            <div class="print-section">
                <h4>Notes</h4>
                <div class="print-notes">
                    <p>${$('#notes').val()}</p>
                </div>
            </div>
            ` : ''}
            
            <!-- Signature Section -->
            <div class="print-signature">
                <div class="signature-row">
                    <div class="signature-box">
                        <div class="signature-label">Received By:</div>
                        <div class="signature-line"></div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-label">Authorized By:</div>
                        <div class="signature-line"></div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="print-footer">
                <div>This is a computer-generated document and is valid without signature.</div>
                <div>Generated on ${currentDate} at ${new Date().toLocaleTimeString()}</div>
            </div>
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

function generatePrintItems() {
    let itemsHtml = '';
    grnItems.forEach((item, index) => {
        itemsHtml += `
            <tr>
                <td style="text-align: center;">${index + 1}</td>
                <td>${item.product_name}</td>
                <td>${item.variant_name || 'Default'}</td>
                <td style="text-align: center;">${item.ordered_qty}</td>
                <td style="text-align: center;">${item.received_qty}</td>
                <td style="text-align: right;">Rs. ${parseFloat(item.unit_price).toFixed(2)}</td>
                <td style="text-align: right;">Rs. ${item.subtotal.toFixed(2)}</td>
            </tr>
        `;
    });
    return itemsHtml;
}

function calculateTotal() {
    let total = 0;
    grnItems.forEach(item => {
        total += item.subtotal;
    });
    return total.toFixed(2);
}

        function saveGRN() {
            if (!currentGRN || grnItems.length === 0) {
                showError('No GRN data to save');
                return;
            }
            
            const formData = new FormData(document.getElementById('grnForm'));
            formData.append('action', 'save_grn');
            formData.append('items', JSON.stringify(grnItems));
            
            showLoading(true);
            
            $.ajax({
                url: 'grn_functions.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(data) {
                    showLoading(false);
                    if (data.success) {
                        showSuccess('GRN saved successfully!');
                        resetForm();
                        showGRNList();
                    } else {
                        showError('Error saving GRN: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    showLoading(false);
                    console.error('AJAX Error saving GRN:', status, error);
                    showError('Network error occurred while saving GRN');
                }
            });
        }

        function saveAndPrintGRN() {
            saveGRN();
            setTimeout(() => {
                printGRN();
            }, 1000);
        }

        function resetForm() {
            document.getElementById('grnForm').reset();
            hidePODetails();
            generateGRNNumber();
            $('#grn_date').val('<?php echo date('Y-m-d'); ?>');
        }

        function showGRNForm() {
            $('#grnForm').removeClass('hidden');
            $('#grnList').addClass('hidden');
        }

        function showGRNList() {
            $('#grnForm').addClass('hidden');
            $('#grnList').removeClass('hidden');
            loadGRNs();
        }

        function loadGRNs() {
            const search = $('#searchGRNs').val();
            const status = $('#statusFilter').val();
            const dateFilter = $('#dateFilter').val();

            showLoading(true);
            
            $.ajax({
                url: 'grn_functions.php',
                type: 'POST',
                data: {
                    action: 'get_grns',
                    search: search,
                    status: status,
                    date_filter: dateFilter
                },
                dataType: 'json',
                success: function(data) {
                    showLoading(false);
                    if (data.success) {
                        displayGRNs(data.grns);
                    } else {
                        showError('Error loading GRNs: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    showLoading(false);
                    console.error('AJAX Error loading GRNs:', status, error);
                    showError('Network error loading GRNs');
                }
            });
        }

        function displayGRNs(grns) {
            const tbody = $('#grnsTableBody');
            
            if (grns.length === 0) {
                tbody.html('<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No GRNs found</td></tr>');
                return;
            }

            let html = '';
            grns.forEach(grn => {
                const statusBadge = getStatusBadge(grn.status);
                
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${grn.grn_number}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${grn.po_number}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${grn.supplier_name}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${grn.grn_date}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">Rs. ${parseFloat(grn.total_amount).toFixed(2)}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${statusBadge}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="viewGRN(${grn.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            <button onclick="printGRNById(${grn.id})" class="text-purple-600 hover:text-purple-900 mr-3">
                                <i class="fas fa-print mr-1"></i> Print
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tbody.html(html);
        }

        function getStatusBadge(status) {
            const badges = {
                'draft': 'bg-gray-100 text-gray-800',
                'confirmed': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badges[status]}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
        }

        function viewGRN(grnId) {
            // Implementation for viewing GRN details
            alert('View GRN details - ID: ' + grnId);
        }

       function printGRNById(grnId) {
    showLoading(true);
    
    $.ajax({
        url: 'grn_functions.php',
        type: 'POST',
        data: { action: 'get_grn_details', grn_id: grnId },
        dataType: 'json',
        success: function(data) {
            showLoading(false);
            if (data.success) {
                // Set current GRN data for printing
                currentGRN = data.grn;
                grnItems = data.items;
                
                // Update form fields with GRN data
                $('#grn_number').val(data.grn.grn_number);
                $('#grn_date').val(data.grn.grn_date);
                $('#status').val(data.grn.status);
                $('#notes').val(data.grn.notes || '');
                
                // Print the GRN
                printGRN();
            } else {
                showError('Error loading GRN details: ' + data.message);
            }
        },
        error: function(xhr, status, error) {
            showLoading(false);
            console.error('AJAX Error loading GRN details:', status, error);
            showError('Network error loading GRN details');
        }
    });
}

        // Utility functions
        function showLoading(show) {
            $('#loadingIndicator').toggleClass('hidden', !show);
        }

        function showSuccess(message) {
            $('#successText').text(message);
            $('#successMessage').removeClass('hidden');
            setTimeout(() => {
                $('#successMessage').addClass('hidden');
            }, 5000);
        }

        function showError(message) {
            $('#errorText').text(message);
            $('#errorMessage').removeClass('hidden');
            setTimeout(() => {
                $('#errorMessage').addClass('hidden');
            }, 5000);
        }

        // Add event listener for PO selection
        $('#purchase_order_id').on('change', onPOChange);
    </script>
    <style>
@media print {
    body { 
        font-size: 12px; 
        line-height: 1.4;
        margin: 0;
        padding: 0;
        background: white;
    }
    
    /* Hide all website elements during print */
    body > *:not(.print-container) {
        display: none !important;
    }
    
    header, footer, nav, .header, .footer, .menu, .navbar {
        display: none !important;
    }
    
    /* Show only print container */
    .print-container {
        display: block !important;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        min-height: 100vh;
        background: white;
        padding: 20px;
        box-sizing: border-box;
    }
    
    @page { 
        margin: 15mm; 
        size: A4;
    }
    
    .print-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 2px solid #333;
        border-bottom: 3px double #666;
    }
    
    .print-header h1 {
        font-size: 24px;
        font-weight: bold;
        margin: 0;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    
    .print-header .subtitle {
        font-size: 14px;
        color: #666;
        margin: 5px 0 0 0;
    }
    
    .print-header .details {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        font-size: 12px;
        color: #555;
    }
    
    .print-section {
        margin-bottom: 20px;
    }
    
    .print-section h3 {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }
    
    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-size: 11px;
    }
    
    .print-table th {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        padding: 10px 8px !important;
        text-align: left;
        font-weight: bold;
        color: #495057;
        font-size: 11px;
    }
    
    .print-table td {
        border: 1px solid #dee2e6 !important;
        padding: 8px !important;
        vertical-align: top;
        font-size: 11px;
    }
    
    .print-table .text-right {
        text-align: right !important;
    }
    
    .print-table .text-center {
        text-align: center !important;
    }
    
    .print-totals {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
    }
    
    .print-totals .total-row {
        display: flex;
        justify-content: space-between;
        border-top: 2px solid #333;
        padding-top: 10px;
        font-weight: bold;
    }
    
    .print-totals .total-label {
        font-size: 14px;
        color: #333;
    }
    
    .print-totals .total-amount {
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }
    
    .print-notes {
        margin: 20px 0;
        padding: 15px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }
    
    .print-notes h4 {
        font-size: 14px;
        font-weight: bold;
        margin: 0 0 10px 0;
        color: #333;
    }
    
    .print-notes p {
        font-size: 12px;
        color: #555;
        margin: 0;
        line-height: 1.4;
    }
    
    .print-signature {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #dee2e6;
    }
    
    .print-signature .signature-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
    }
    
    .print-signature .signature-box {
        width: 200px;
    }
    
    .print-signature .signature-label {
        font-size: 12px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    
    .print-signature .signature-line {
        border-bottom: 1px solid #333;
        height: 30px;
    }
    
    .print-footer {
        position: fixed;
        bottom: 10px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: #666;
        padding: 5px 0;
    }
    
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
}
        </style>
</body>
</html>

<?php include 'footer.php'; ?>