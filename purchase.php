<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// purchase.php
include 'header.php';
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Purchase Management</h1>
                <p class="text-gray-600">Create and manage purchase orders</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-2">
                <button onclick="showPurchaseList()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-list mr-2"></i> View Purchases
                </button>
            </div>
        </div>

        <!-- Purchase Form -->
        <div id="purchaseForm" class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold">Create New Purchase Order</h2>
                <button onclick="generatePONumber()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                    <i class="fas fa-sync mr-1"></i> Generate PO Number
                </button>
            </div>

            <form id="purchaseOrderForm">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">PO Number *</label>
                        <input type="text" id="po_number" name="po_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Order Date *</label>
                        <input type="date" id="order_date" name="order_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                        <input type="date" id="due_date" name="due_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Supplier Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier *</label>
                    <select id="supplier_id" name="supplier_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Supplier</option>
                    </select>
                </div>

                <!-- Payment Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Type *</label>
                        <select id="payment_type" name="payment_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div id="paymentAccountField" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Account *</label>
                        <select id="payment_account_id" name="payment_account_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Account</option>
                        </select>
                    </div>
                </div>

                <!-- Purchase Items -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Purchase Items</h3>
                        <button type="button" onclick="addItemRow()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Product</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Variant</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">Quantity</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right">Unit Price</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right">Discount</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right">Tax</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right">Subtotal</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Items will be added dynamically -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold">Total Amount:</td>
                                    <td id="totalAmount" class="border border-gray-300 px-4 py-2 text-right font-semibold">0.00</td>
                                    <td class="border border-gray-300 px-4 py-2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Additional notes for this purchase order..."></textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="resetForm()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                        <i class="fas fa-save mr-2"></i> Create Purchase Order
                    </button>
                </div>
            </form>
        </div>

        <!-- Purchase List (Hidden by default) -->
        <div id="purchaseList" class="bg-white rounded-lg shadow p-6 hidden">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold">Purchase Orders</h2>
                <button onclick="showPurchaseForm()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> New Purchase
                </button>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="searchPurchases" placeholder="Search PO number, supplier..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="received">Received</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Type</label>
                    <select id="paymentFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="credit">Credit</option>
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

            <!-- Purchase Orders Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="purchasesTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Loading purchases...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex justify-between items-center">
                <div id="purchasePaginationInfo" class="text-sm text-gray-700"></div>
                <div class="space-x-2">
                    <button id="prevPurchasePage" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button id="nextPurchasePage" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

   <!-- View Purchase Modal -->
<div id="viewPurchaseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-semibold">Purchase Order Details - <span id="modalPONumber"></span></h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="purchaseDetailsContent" class="mt-4">
                <!-- Content will be loaded by AJAX -->
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button onclick="closeViewModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Close
                </button>
                <button onclick="printPurchase()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let itemCounter = 0;
        let currentPurchasePage = 1;
        const purchasesPerPage = 10;

        $(document).ready(function() {
            // Initialize Select2
            $('#supplier_id').select2();
            $('#payment_account_id').select2();

            // Load initial data
            loadSuppliers();
            loadPaymentAccounts();
            generatePONumber();
            addItemRow();

            // Payment type change handler
            $('#payment_type').change(function() {
                togglePaymentAccountField();
            });

            // Form submission
            $('#purchaseOrderForm').submit(function(e) {
                e.preventDefault();
                submitPurchaseOrder();
            });

            // Purchase list filters
            $('#searchPurchases, #statusFilter, #paymentFilter, #dateFilter').change(function() {
                currentPurchasePage = 1;
                loadPurchases();
            });

            $('#prevPurchasePage').click(function() {
                if (currentPurchasePage > 1) {
                    currentPurchasePage--;
                    loadPurchases();
                }
            });

            $('#nextPurchasePage').click(function() {
                currentPurchasePage++;
                loadPurchases();
            });
        });

        function togglePaymentAccountField() {
            const paymentType = $('#payment_type').val();
            const paymentAccountField = $('#paymentAccountField');
            
            if (paymentType === 'cash' || paymentType === 'bank') {
                paymentAccountField.show();
                $('#payment_account_id').prop('required', true);
            } else {
                paymentAccountField.hide();
                $('#payment_account_id').prop('required', false);
            }
        }

        function generatePONumber() {
            const timestamp = new Date().getTime();
            const random = Math.floor(Math.random() * 1000);
            $('#po_number').val(`PO-${timestamp}-${random}`);
        }

        function addItemRow() {
            itemCounter++;
            const row = `
                <tr id="itemRow-${itemCounter}" class="item-row">
                    <td class="border border-gray-300 px-4 py-2">
                        <select name="items[${itemCounter}][product_id]" required 
                                class="product-select w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
                                onchange="loadVariants(${itemCounter})">
                            <option value="">Select Product</option>
                        </select>
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <select name="items[${itemCounter}][variant_id]" 
                                class="variant-select-${itemCounter} w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                            <option value="">Default</option>
                        </select>
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" name="items[${itemCounter}][quantity]" required min="1" step="0.01"
                               class="quantity-input w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
                               onchange="calculateSubtotal(${itemCounter})" onkeyup="calculateSubtotal(${itemCounter})">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" name="items[${itemCounter}][unit_price]" required min="0" step="0.01"
                               class="price-input w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 text-right"
                               onchange="calculateSubtotal(${itemCounter})" onkeyup="calculateSubtotal(${itemCounter})">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" name="items[${itemCounter}][discount]" min="0" step="0.01" value="0"
                               class="discount-input w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 text-right"
                               onchange="calculateSubtotal(${itemCounter})" onkeyup="calculateSubtotal(${itemCounter})">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" name="items[${itemCounter}][tax]" min="0" step="0.01" value="0"
                               class="tax-input w-full px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 text-right"
                               onchange="calculateSubtotal(${itemCounter})" onkeyup="calculateSubtotal(${itemCounter})">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <span class="subtotal-display block text-right">0.00</span>
                    </td>
                    <td class="border border-gray-300 px-4 py-2 text-center">
                        <button type="button" onclick="removeItemRow(${itemCounter})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#itemsTableBody').append(row);
            loadProducts(itemCounter);
        }

        function removeItemRow(rowId) {
            $(`#itemRow-${rowId}`).remove();
            calculateTotal();
        }

        function calculateSubtotal(rowId) {
            const quantity = parseFloat($(`input[name="items[${rowId}][quantity]"]`).val()) || 0;
            const price = parseFloat($(`input[name="items[${rowId}][unit_price]"]`).val()) || 0;
            const discount = parseFloat($(`input[name="items[${rowId}][discount]"]`).val()) || 0;
            const tax = parseFloat($(`input[name="items[${rowId}][tax]"]`).val()) || 0;
            
            const subtotal = (quantity * price) - discount + tax;
            $(`#itemRow-${rowId} .subtotal-display`).text(subtotal.toFixed(2));
            
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            $('.subtotal-display').each(function() {
                total += parseFloat($(this).text()) || 0;
            });
            $('#totalAmount').text(total.toFixed(2));
        }

      function loadSuppliers() {
    console.log('Loading suppliers...');
    $.ajax({
        url: 'purchase_functions.php',
        type: 'POST',
        data: { action: 'get_suppliers' },
        dataType: 'json', // Expect JSON response
        success: function(data) {
            console.log('Suppliers response:', data);
            if (data.success) {
                $('#supplier_id').empty().append('<option value="">Select Supplier</option>');
                if (data.suppliers && data.suppliers.length > 0) {
                    data.suppliers.forEach(supplier => {
                        $('#supplier_id').append(
                            `<option value="${supplier.id}">${supplier.name}</option>`
                        );
                    });
                } else {
                    $('#supplier_id').append('<option value="">No suppliers found</option>');
                }
                $('#supplier_id').trigger('change');
            } else {
                console.error('Error from server:', data.message);
                $('#supplier_id').empty().append('<option value="">Error: ' + (data.message || 'Unknown error') + '</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading suppliers:', status, error, xhr.responseText);
            $('#supplier_id').empty().append('<option value="">Network error loading suppliers</option>');
            
            // Try to parse the response even if it failed
            try {
                const errorData = JSON.parse(xhr.responseText);
                if (errorData && errorData.message) {
                    console.error('Server error message:', errorData.message);
                }
            } catch (e) {
                console.error('Raw server response:', xhr.responseText);
            }
        }
    });
}

  function loadPaymentAccounts() {
    console.log('Loading payment accounts...');
    $.ajax({
        url: 'purchase_functions.php',
        type: 'POST',
        data: { action: 'get_payment_accounts' },
        dataType: 'json',
        success: function(data) {
            console.log('Payment accounts response:', data);
            if (data.success) {
                $('#payment_account_id').empty().append('<option value="">Select Account</option>');
                if (data.accounts && data.accounts.length > 0) {
                    data.accounts.forEach(account => {
                        $('#payment_account_id').append(
                            `<option value="${account.id}">${account.name} (Balance: ${account.balance || 0})</option>`
                        );
                    });
                } else {
                    $('#payment_account_id').append('<option value="">No accounts found - using default</option>');
                }
                $('#payment_account_id').trigger('change');
            } else {
                console.error('Error from server:', data.message);
                $('#payment_account_id').empty().append('<option value="">Error: ' + (data.message || 'Unknown error') + '</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading payment accounts:', status, error, xhr.responseText);
            $('#payment_account_id').empty().append('<option value="">Using default accounts</option>');
        }
    });
}

function loadProducts(rowId) {
    console.log('Loading products for row:', rowId);
    $.ajax({
        url: 'purchase_functions.php',
        type: 'POST',
        data: { action: 'get_products' },
        dataType: 'json',
        success: function(data) {
            console.log('Products response:', data);
            if (data.success) {
                const productSelect = $(`#itemRow-${rowId} .product-select`);
                productSelect.empty().append('<option value="">Select Product</option>');
                
                if (data.products && data.products.length > 0) {
                    data.products.forEach(product => {
                        productSelect.append(
                            `<option value="${product.id}" data-category="${product.category_id || ''}">${product.title}</option>`
                        );
                    });
                } else {
                    productSelect.append('<option value="">No products found</option>');
                }
            } else {
                console.error('Error from server:', data.message);
                $(`#itemRow-${rowId} .product-select`).empty().append('<option value="">Error loading products</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading products:', status, error, xhr.responseText);
            $(`#itemRow-${rowId} .product-select`).empty().append('<option value="">Network error</option>');
        }
    });
}

     function loadVariants(rowId) {
    const productId = $(`#itemRow-${rowId} .product-select`).val();
    console.log('Loading variants for product:', productId, 'in row:', rowId);
    
    if (!productId) {
        $(`.variant-select-${rowId}`).empty().append('<option value="">Default</option>');
        return;
    }

    $.ajax({
        url: 'purchase_functions.php',
        type: 'POST',
        data: { 
            action: 'get_variants',
            product_id: productId
        },
        dataType: 'json',
        success: function(data) {
            console.log('Variants response:', data);
            const variantSelect = $(`.variant-select-${rowId}`);
            variantSelect.empty().append('<option value="">Default</option>');
            
            if (data.success && data.variants && data.variants.length > 0) {
                data.variants.forEach(variant => {
                    variantSelect.append(
                        `<option value="${variant.id}">${variant.title} - Rs. ${variant.price || 0}</option>`
                    );
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading variants:', status, error, xhr.responseText);
            $(`.variant-select-${rowId}`).empty().append('<option value="">Error loading variants</option>');
        }
    });
}

      function submitPurchaseOrder() {
    const formData = new FormData(document.getElementById('purchaseOrderForm'));
     const paymentType = $('#payment_type').val();
    const paymentAccountId = $('#payment_account_id').val();
    
    if ((paymentType === 'cash' || paymentType === 'bank') && !paymentAccountId) {
        alert('Please select a payment account for cash/bank payments.');
        return;
    }
    // Validate required fields
    const poNumber = $('#po_number').val();
    const supplierId = $('#supplier_id').val();
    const orderDate = $('#order_date').val();
   
    
    if (!poNumber || !supplierId || !orderDate || !paymentType) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Add items data
    const items = [];
    let hasValidItems = false;
    
    $('.item-row').each(function() {
        const row = $(this);
        const productId = row.find('.product-select').val();
        const quantity = row.find('.quantity-input').val();
        const unitPrice = row.find('.price-input').val();
        
        if (productId && quantity && unitPrice) {
            const item = {
                product_id: productId,
                variant_id: row.find('.variant-select').val() || null,
                quantity: quantity,
                unit_price: unitPrice,
                discount: row.find('.discount-input').val() || 0,
                tax: row.find('.tax-input').val() || 0
            };
            items.push(item);
            hasValidItems = true;
        }
    });

    if (!hasValidItems) {
        alert('Please add at least one valid item to the purchase order.');
        return;
    }

    formData.append('action', 'create_purchase');
    formData.append('items', JSON.stringify(items));

    console.log('Submitting purchase order...');
    
    $.ajax({
        url: 'purchase_functions.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
            console.log('Purchase submission response:', data);
            if (data.success) {
                alert('Purchase order created successfully!');
                resetForm();
                showPurchaseList();
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error submitting purchase:', status, error, xhr.responseText);
            alert('Network error occurred. Please check console for details.');
        }
    });
}

        function resetForm() {
            document.getElementById('purchaseOrderForm').reset();
            $('#itemsTableBody').empty();
            itemCounter = 0;
            addItemRow();
            generatePONumber();
            $('#order_date').val('<?php echo date('Y-m-d'); ?>');
        }

        function showPurchaseList() {
            $('#purchaseForm').addClass('hidden');
            $('#purchaseList').removeClass('hidden');
            loadPurchases();
        }

        function showPurchaseForm() {
            $('#purchaseList').addClass('hidden');
            $('#purchaseForm').removeClass('hidden');
        }

     function loadPurchases() {
    const search = $('#searchPurchases').val();
    const status = $('#statusFilter').val();
    const paymentType = $('#paymentFilter').val();
    const dateFilter = $('#dateFilter').val();

    $.ajax({
        url: 'purchase_functions.php',
        type: 'POST',
        data: {
            action: 'get_purchases',
            page: currentPurchasePage,
            limit: purchasesPerPage,
            search: search,
            status: status,
            payment_type: paymentType,
            date_filter: dateFilter
        },
        dataType: 'json', // jQuery will automatically parse the response
        success: function(data) {
            console.log('Purchases data:', data);
            if (data.success) {
                displayPurchases(data.purchases);
                updatePurchasePagination(data.total);
            } else {
                console.error('Error from server:', data.message);
                $('#purchasesTableBody').html('<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error: ' + (data.message || 'Unknown error') + '</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading purchases:', status, error);
            console.error('Response text:', xhr.responseText);
            
            // Try to parse the response even if it failed
            try {
                const errorData = JSON.parse(xhr.responseText);
                if (errorData && errorData.message) {
                    console.error('Server error message:', errorData.message);
                    $('#purchasesTableBody').html('<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error: ' + errorData.message + '</td></tr>');
                } else {
                    $('#purchasesTableBody').html('<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Network error loading purchases</td></tr>');
                }
            } catch (e) {
                console.error('Raw server response:', xhr.responseText);
                $('#purchasesTableBody').html('<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Network error loading purchases</td></tr>');
            }
        }
    });
}

        function displayPurchases(purchases) {
            const tbody = $('#purchasesTableBody');
            
            if (purchases.length === 0) {
                tbody.html('<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No purchases found</td></tr>');
                return;
            }

            let html = '';
            purchases.forEach(purchase => {
                const orderDate = new Date(purchase.order_date).toLocaleDateString();
                const statusBadge = getStatusBadge(purchase.status);
                const paymentBadge = getPaymentBadge(purchase.payment_type);
                
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${purchase.po_number}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${purchase.supplier_name}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${orderDate}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">Rs. ${parseFloat(purchase.total_amount).toFixed(2)}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${statusBadge}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${paymentBadge}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="viewPurchase(${purchase.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            <button onclick="deletePurchase(${purchase.id})" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash mr-1"></i> Delete
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
                'confirmed': 'bg-yellow-100 text-yellow-800',
                'received': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badges[status]}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
        }

        function getPaymentBadge(paymentType) {
            const badges = {
                'cash': 'bg-green-100 text-green-800',
                'bank': 'bg-blue-100 text-blue-800',
                'credit': 'bg-orange-100 text-orange-800'
            };
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badges[paymentType]}">${paymentType.charAt(0).toUpperCase() + paymentType.slice(1)}</span>`;
        }

        function updatePurchasePagination(total) {
            const totalPages = Math.ceil(total / purchasesPerPage);
            $('#purchasePaginationInfo').text(`Page ${currentPurchasePage} of ${totalPages} (${total} total purchases)`);
            $('#prevPurchasePage').prop('disabled', currentPurchasePage === 1);
            $('#nextPurchasePage').prop('disabled', currentPurchasePage === totalPages);
        }

       function viewPurchase(purchaseId) {
    $.ajax({
        url: 'purchase_functions.php',
        type: 'POST',
        data: { action: 'get_purchase_details', purchase_id: purchaseId },
        dataType: 'json', // Add this to ensure proper JSON parsing
        success: function(data) {
            if (data.success) {
                $('#purchaseDetailsContent').html(data.html);
                $('#modalPONumber').text(data.purchase.po_number);
                $('#viewPurchaseModal').removeClass('hidden');
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading purchase details:', status, error);
            console.error('Response text:', xhr.responseText);
            
            // Try to parse the response even if it failed
            try {
                const errorData = JSON.parse(xhr.responseText);
                if (errorData && errorData.message) {
                    alert('Error: ' + errorData.message);
                } else {
                    alert('Network error occurred while loading purchase details.');
                }
            } catch (e) {
                console.error('Raw server response:', xhr.responseText);
                alert('Network error occurred while loading purchase details.');
            }
        }
    });
}

        function closeViewModal() {
            $('#viewPurchaseModal').addClass('hidden');
        }

        function printPurchase() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    
    // Get the HTML content
    const printContent = $('#purchaseDetailsContent').html();
    const poNumber = $('#modalPONumber').text();
    
    // Create a complete HTML document for printing
    const printDocument = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Purchase Order - ${poNumber}</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                color: #333;
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
                font-size: 24px;
                color: #333;
            }
            .header p {
                margin: 5px 0 0 0;
                color: #666;
            }
            .header .po-info {
                text-align: right;
            }
            .header .po-info p {
                margin: 5px 0;
            }
            .grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 20px;
            }
            .grid h3 {
                margin: 0 0 10px 0;
                font-size: 16px;
                color: #333;
            }
            .grid p {
                margin: 5px 0;
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
                font-weight: bold;
            }
            table td.text-right {
                text-align: right;
            }
            table td.text-center {
                text-align: center;
            }
            .notes-section {
                background-color: #f9f9f9;
                padding: 10px;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            .notes-section h3 {
                margin: 0 0 10px 0;
                font-size: 16px;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                color: #666;
                margin-top: 30px;
            }
            @media print {
                body {
                    padding: 0;
                }
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        ${printContent}
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

       function deletePurchase(purchaseId) {
    if (confirm('Are you sure you want to delete this purchase order? This action cannot be undone.')) {
        $.ajax({
            url: 'purchase_functions.php',
            type: 'POST',
            data: { action: 'delete_purchase', purchase_id: purchaseId },
            dataType: 'json', // Add this to ensure proper JSON parsing
            success: function(data) {
                if (data.success) {
                    alert('Purchase order deleted successfully!');
                    loadPurchases(); // Reload the list
                } else {
                    alert('Error: ' + (data.message || 'Unknown error occurred'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error deleting purchase:', status, error);
                console.error('Response text:', xhr.responseText);
                
                // Try to parse the response even if it failed
                try {
                    const errorData = JSON.parse(xhr.responseText);
                    if (errorData && errorData.message) {
                        alert('Error: ' + errorData.message);
                    } else {
                        alert('Network error occurred while deleting purchase order.');
                    }
                } catch (e) {
                    console.error('Raw server response:', xhr.responseText);
                    alert('Network error occurred while deleting purchase order.');
                }
            }
        });
    }
}
    </script>
</body>
</html>