<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// add_invoice.php
include 'header.php';
include 'menu.php';
include 'session_check.php';
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
        
        /* ✅ Select2 Styling */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 32px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            padding: 2px 8px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            font-size: 0.875rem !important;
            color: #374151 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px !important;
            right: 4px !important;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            padding: 4px 8px !important;
            font-size: 0.875rem !important;
        }

        .select2-results__option {
            font-size: 0.875rem !important;
            padding: 6px 12px !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #2563eb !important;
        }

        .select2-dropdown {
            z-index: 9999 !important;
        }
        
#selectPOBtn {
    display: none; /* button hide visualy, functionality remain */
}

th {
    background-color: #d1d5db; /* thodi dark grey */
    color: #111827; /* thoda dark text */
}

@media print {
    .no-print { display: none !important; }

    /* Hide summary rows if value = 0 */
    #Advance[value="0"],
    #GSTRate[value="0"],
    #TotalGST[value="0"],
    #NTRRate[value="0"],
    #TotalNTR[value="0"] {
        display: none !important;
    }

    /* Hide parent label divs of these inputs */
    #Advance[value="0"]::parent,
    #GSTRate[value="0"]::parent,
    #TotalGST[value="0"]::parent,
    #NTRRate[value="0"]::parent,
    #TotalNTR[value="0"]::parent {
        display: none !important;
    }
}



    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">

        <form id="invoiceForm">

        <input type="hidden" id="CreatedBy" name="CreatedBy" value="<?php echo $user_id; ?>">
<input type="hidden" id="CreatedByName" name="CreatedByName" value="<?php echo htmlspecialchars($user_name); ?>">
            <!-- Invoice Header -->
             <div class="bg-blue-600 text-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-2xl font-bold">Add New Invoice</h1>
                    <p class="text-blue-100">Create invoice for customer</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="invoices_list.php" class="bg-white hover:bg-gray-100 text-blue-600 px-4 py-2 rounded-lg text-sm flex items-center font-medium transition-colors">
                        <i class="fas fa-list mr-2"></i> View Invoices
                    </a>
                </div>
            </div>
        </div>
                
              <div class="p-4">
    <div class="flex flex-wrap gap-2 items-end">
        <div class="flex flex-col w-40">
            <label class="text-xs font-medium text-gray-700 mb-1">Invoice #</label>
            <input type="text" id="InvoiceNo" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-100" readonly>
        </div>
        <div class="flex flex-col w-40">
            <label class="text-xs font-medium text-gray-700 mb-1">Dated</label>
            <input type="date" id="InvoiceDate" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        <div class="flex flex-col w-32" style="display:none;">
            <label class="text-xs font-medium text-gray-700 mb-1">JO No</label>
            <select id="PONo" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Job Order</option>
            </select>
        </div>
      <div class="flex flex-col w-80">
    <label class="text-xs font-medium text-gray-700 mb-1">Customer *</label>

    <div class="flex items-center relative">

        <!-- Actual dropdown -->
        <select id="CustomerID" class="customer-select w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md" required>
            <option value="">Select Customer</option>
        </select>

        <!-- ❌ Clear Button -->
        <button type="button" id="clearCustomerBtn"
            class="absolute right-2 text-red-600 hover:text-red-800 hidden"
            style="z-index: 9999;">
            <i class="fas fa-times-circle text-lg"></i>
        </button>

    </div>
</div>

        <div class="flex flex-col w-80">
            <label class="text-xs font-medium text-gray-700 mb-1">Invoice Subject</label>
            <input type="text" id="InvoiceSubject" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Enter invoice subject">
        </div>

 <div class="flex flex-col w-80">
            <label class="text-xs font-medium text-gray-700 mb-1">PO No</label>
            <input type="text" id="ponumber" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Enter invoice subject">
        </div>
 <div class="flex flex-col w-80">
            <label class="text-xs font-medium text-gray-700 mb-1">Quotation No</label>
            <input type="text" id="quotationno" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Enter invoice subject">
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
          <div class="flex items-center space-x-2">
    <!-- Add Row Button -->
    <button type="button" class="bg-white text-green-600 px-3 py-1 rounded text-sm font-medium hover:bg-gray-100" onclick="addInvoiceRow()">
        <i class="fas fa-plus mr-1"></i> Add Row
    </button>

    <!-- Select Job Order Button -->
    <button type="button" id="selectPOBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        <i class="fas fa-file-import mr-1"></i> Select Job Order
    </button>
</div>

            </div>
            <div class="p-4">
                <div class="table-responsive overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-300" id="invoiceTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Job #</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width:250px;">Detail</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width:120px;">Media</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-18">Width</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-18">Height</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-30">Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Sqft</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Rate</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Total</th>
                           
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

<div class="lg:col-span-4">
    <div class="bg-white rounded-lg shadow-md p-4">
        <h3 class="text-base font-semibold mb-4 text-gray-800 flex justify-between items-center">
            <span class="text-gray-700">Summary</span>
            <span class="text-xs text-gray-500">Total: <span id="summaryTotal" class="font-bold text-gray-900">0.00</span></span>
        </h3>

        <div class="grid grid-cols-2 gap-3 text-sm">
            <!-- Sub Total -->
            <label class="flex items-center justify-between">Sub Total
                <div class="flex items-center space-x-1">
                    <span>Rs.</span>
                    <input type="number" id="SubTotal" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-right bg-gray-100" readonly>
                </div>
            </label>

            <!-- Advance / Paid Amount -->
            <label class="flex items-center justify-between">Advance / Paid Amount
                <div class="flex items-center space-x-1">
                    <span>Rs.</span>
                    <input type="number" id="Advance" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-right bg-gray-100" readonly step="any" value="0">
                </div>
            </label>

            <!-- GST Rate -->
            <label class="flex items-center justify-between">GST Rate (%)
                <input type="number" id="GSTRate" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-right" step="any" value="0">
            </label>

            <!-- Total GST -->
            <label class="flex items-center justify-between">Total GST
                <div class="flex items-center space-x-1">
                    <span>Rs.</span>
                    <input type="number" id="TotalGST" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-right bg-gray-100" readonly>
                </div>
            </label>

            <!-- NTN Rate -->
            <label class="flex items-center justify-between">NTN Rate (%)
                <input type="number" id="NTRRate" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-right" step="any" value="0">
            </label>

            <!-- Total NTN -->
            <label class="flex items-center justify-between">Total NTN
                <div class="flex items-center space-x-1">
                    <span>Rs.</span>
                    <input type="number" id="TotalNTR" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-right bg-gray-100" readonly>
                </div>
            </label>

            <!-- Grand Total -->
            <label class="flex items-center justify-between font-bold text-gray-900">Grand Total
                <div class="flex items-center space-x-1">
                    <span>Rs.</span>
                    <input type="number" id="GrandTotal" class="w-32 px-3 py-2 border border-gray-300 rounded numeric-input text-right font-bold bg-gray-100" readonly>
                </div>
            </label>
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
<!-- ✅ Select JO Modal -->
<div id="selectPOModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white w-11/12 md:w-3/4 lg:w-1/2 rounded-lg shadow-lg">
    <div class="flex justify-between items-center border-b px-5 py-3 bg-blue-600 text-white rounded-t-lg">
      <h3 class="text-lg font-semibold">Select Job Orders</h3>
      <button id="closePOModal" class="text-white hover:text-gray-300">&times;</button>
    </div>

    <div class="p-5">
      <input type="text" id="poSearch" placeholder="Search JO..." 
             class="w-full mb-3 p-2 border border-gray-300 rounded">

      <div class="overflow-y-auto max-h-80">
        <table class="min-w-full border border-gray-300" id="poTable">
          <thead class="bg-gray-100">
            <tr>
              <th class="border p-2">#</th>
              <th class="border p-2">JO Number</th>
              <th class="border p-2">Customer</th>
              <th class="border p-2">Date</th>
              <th class="border p-2">Created At</th>
              
            </tr>
          </thead>
          <tbody id="poTableBody">
            <tr><td colspan="5" class="text-center p-3 text-gray-500">Loading...</td></tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end mt-4">
        <button id="confirmSelectedPOs" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Selected</button>
      </div>
    </div>
  </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- ✅ Select2 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
.select2-container--open .select2-dropdown {
    width: auto !important;
    min-width: 200px !important;
    position: absolute !important;
}
    </style>
    <script>
// Auto-focus search when any select2 dropdown is opened
$(document).on('select2:open', function (e) {
    let searchField = document.querySelector('.select2-container--open .select2-search__field');
    if (searchField) {
        setTimeout(() => searchField.focus(), 10);
    }
});
</script>
    <script>
        var invoiceRowCount = 0;
        var materials = [];
        var customers = [];
        var jobOrders = [];

       $(document).ready(function() {

         $('#CustomerID').select2({
        placeholder: 'Select Customer',
        allowClear: true,
        width: '100%'
    });
    // Set today's date
    $('#InvoiceDate').val(new Date().toISOString().split('T')[0]);
    
    // Generate Invoice No
    generateInvoiceNo();
    
    // Load data
    loadCustomers();
    loadMaterials();
    
    // Add initial rows
    if (invoiceRowCount === 0) {
        for (var i = 1; i <= 7; i++) {
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
    
    // ✅ UPDATED: Customer change handler with popup
    $('#CustomerID').change(function() {
        updateCustomerName();
        
        // Open Job Order selection modal when customer is selected
        var customerId = $(this).val();
        if (customerId) {
            $('#selectPOModal').removeClass('hidden');
            loadPOList();


setTimeout(() => $('#poSearch').focus(), 100);

        }
        
    });
    
    // Tax calculation handlers
    $('#GSTRate, #NTRRate, #Advance').on('input', calculateTotals);
setTimeout(function(){
    $('#selectPOModal').removeClass('hidden');
    loadPOList();
    setTimeout(() => $('#poSearch').focus(), 100);
}, 1000);

 


});

       function generateInvoiceNo() {
            $.ajax({
                url: 'invoice_functions.php',
                type: 'POST',
                data: { action: 'get_next_invoice_number' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        $('#InvoiceNo').val(data.invoiceNo);
                    } else {
                        $('#InvoiceNo').val('INV-001');
                    }
                },
                error: function() {
                    $('#InvoiceNo').val('INV-001');
                }
            });
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
            var selectedCustomer = $('#CustomerID').val();
            $.ajax({
                url: 'invoice_functions.php',
                type: 'POST',
               data: { 
        action: 'get_job_orders', 
        customer_id: selectedCustomer 
    },
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
function loadJobOrdersByCustomer(customerId) {
    $.ajax({
        url: 'invoice_functions.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_job_orders',
            customer_id: customerId
        },
        success: function (response) {
            if (response.success) {
                var tableBody = $('#jobOrderTable tbody'); // adjust selector if different
                tableBody.empty();

                $.each(response.jobOrders, function (index, order) {
                    tableBody.append(`
                        <tr>
                            <td><input type="checkbox" class="select-job" value="${order.JobOrderNo}"></td>
                            <td>${order.JobOrderNo}</td>
                            <td>${order.CustomerName}</td>
                            <td>${order.OrderDate}</td>
                            <td>${order.DeliveryDate}</td>
                        </tr>
                    `);
                });
            } else {
                alert('No job orders found for this customer');
            }
        },
        error: function () {
            alert('Error loading job orders.');
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
        
        // ✅ Apply Select2 if not already initialized
        if (!$select.hasClass('select2-hidden-accessible')) {
            $select.select2({
                placeholder: 'Search media',
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: true,
                matcher: function(params, data) {
                    // Custom search - case insensitive
                    if ($.trim(params.term) === '') {
                        return data;
                    }
                    if (typeof data.text === 'undefined') {
                        return null;
                    }
                    if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                        return data;
                    }
                    return null;
                }
            });
        }
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
    '<td class="px-4 py-2 whitespace-nowrap"><input type="text" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-center job-no" placeholder="Job #"></td>' +
    '<td class="px-4 py-2"><input type="text" class="w-full px-4 py-1 text-sm border border-gray-300 rounded detail-input" placeholder="Enter detail" style="min-width:250px;"></td>' +
    '<td class="px-4 py-2"><select class="w-full px-2 py-1 text-sm border border-gray-300 rounded media-select" style="min-width:120px;"><option value="">Select Media</option></select></td>' +
   '<td class="px-4 py-2"><input type="number" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded numeric-input width-input" step="any" placeholder="Width" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
'<td class="px-4 py-2"><input type="number" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded numeric-input height-input" step="any" placeholder="Height" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
'<td class="px-4 py-2"><input type="number" class="w-full px-2 py-1 text-sm border border-gray-300 rounded numeric-input qty-input text-center" placeholder="Qty" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
'<td class="px-4 py-2"><input type="number" class="w-16 px-2 py-1 text-sm border border-gray-300 rounded numeric-input sqft-input" step="any" placeholder="Sqft" readonly></td>' +
'<td class="px-4 py-2"><input type="number" class="w-16 px-2 py-1 text-sm border border-gray-300 rounded numeric-input rate-input" step="any" placeholder="Rate" onchange="calculateRowTotal($(this).closest(\'tr\'))"></td>' +
'<td class="px-4 py-2"><input type="number" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded numeric-input total-input" step="any" placeholder="Total" readonly></td>' ;

    
    $('#invoiceTableBody').append(row);
    
    // ✅ Apply Select2 to newly added media dropdown
    $('#invoiceTableBody tr:last .media-select').select2({
        placeholder: 'Search media',
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true
    });
    
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
    return new Promise(function(resolve, reject) {
        if (!validateForm()) {
            reject('Form validation failed.');
            return;
        }

    var formData = {
    action: 'create_invoice',
    InvoiceNo: $('#InvoiceNo').val(),
    InvoiceDate: $('#InvoiceDate').val(),
    PONumber: $('#ponumber').val(),
    QuotationNo: $('#quotationno').val(),
    CreatedBy: $('#CreatedBy').val(),
    CreatedByName: $('#CreatedByName').val(),
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
            reject('No items to save.');
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
                    resolve(data.invoiceId || $('#InvoiceNo').val()); // Return invoice ID
                    

  setTimeout(function() {
            window.location.reload();  // 🔥 Reload page instead of reset
        }, 300);

                } else {
                    alert('Error: ' + (data.message || 'Unknown error occurred'));
                    reject(data.message);
                }
            },
            error: function() {
                alert('Network error occurred. Please try again.');
                reject('Network error');
            }
        });
    });
}

function saveAndPrint() {
    if (!validateForm()) {
        return;
    }

    // Save the invoice first
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
    PONumber: $('#ponumber').val(),
    QuotationNo: $('#quotationno').val(),
    CreatedBy: $('#CreatedBy').val(),
    CreatedByName: $('#CreatedByName').val(),
    items: []
};

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

    // Show loading message
    var $saveBtn = $('button[onclick="saveAndPrint()"]');
    var originalText = $saveBtn.html();
    $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving...');

    $.ajax({
        url: 'invoice_functions.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(data) {
            $saveBtn.prop('disabled', false).html(originalText);
            
            if (data.success) {
                alert('Invoice saved successfully! Opening print preview...');
                
                // ✅ FIXED: Get InvoiceID from response and open print page
                var invoiceId = data.InvoiceID;
                var printUrl = 'print_invoice.php?id=' + invoiceId;
                
                // Open print page in new window
                var printWindow = window.open(printUrl, '_blank');
                
                // Wait for print window to load, then trigger print dialog
                if (printWindow) {
                    printWindow.onload = function() {
                        printWindow.focus();
                        printWindow.print();
                    };
                }
                
                // Reset form after successful save
                setTimeout(function() {
                    resetForm();
                }, 1000);
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        },
        error: function(xhr, status, error) {
            $saveBtn.prop('disabled', false).html(originalText);
            console.error('AJAX Error:', status, error);
            console.log('Response:', xhr.responseText);
            alert('Network error occurred. Please try again.');
        }
    });
}

        
        function preparePrint() {
    // Hide zero summary fields before print
    if (parseFloat($('#Advance').val()) === 0) $('#Advance').closest('label').hide();
    if (parseFloat($('#GSTRate').val()) === 0) $('#GSTRate').closest('label').hide();
    if (parseFloat($('#TotalGST').val()) === 0) $('#TotalGST').closest('label').hide();
    if (parseFloat($('#NTRRate').val()) === 0) $('#NTRRate').closest('label').hide();
    if (parseFloat($('#TotalNTR').val()) === 0) $('#TotalNTR').closest('label').hide();
}

function restoreAfterPrint() {
    $('#Advance, #GSTRate, #TotalGST, #NTRRate, #TotalNTR').closest('label').show();
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

<script>
// =================== SELECT PO MODAL ===================

// Open modal
$('#selectPOBtn').on('click', function() {
  $('#selectPOModal').removeClass('hidden');
  loadPOList();
});

// Add this: Close modal when clicking outside
$('#selectPOModal').on('click', function(e) {
    if ($(e.target).is('#selectPOModal')) {
        $('#selectPOModal').addClass('hidden');
    }
});

// Close modal
$('#closePOModal').on('click', function() {
    $('#selectPOModal').addClass('hidden');
});

// Search/filter inside modal
$('#poSearch').on('keyup', function() {
  const value = $(this).val().toLowerCase();
  $('#poTableBody tr').filter(function() {
    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
  });
});

// Load PO list from your API response (jobOrders)
function loadPOList() {
     var selectedCustomer = $('#CustomerID').val();
  $.ajax({
    url: 'invoice_functions.php',
    type: 'POST',
    data: {
            action: 'get_job_orders',
            customer_id: selectedCustomer
        },
    dataType: 'json',
    success: function(response) {
      console.log(response); // ✅ check structure in console

      if (response.success && response.jobOrders && response.jobOrders.length > 0) {
        let rows = '';
        response.jobOrders.forEach((po) => {
          rows += `
            <tr>
              <td class="border p-2 text-center">
                <input type="checkbox" class="poCheckbox" value="${po.JobOrderNo}">
              </td>
              <td class="border p-2">${po.JobOrderNo}</td>
              <td class="border p-2">${po.CustomerName}</td>
              <td class="border p-2">${po.OrderDate}</td>
              <td class="border p-2">${po.DeliveryDate}</td>
              <td class="border p-2 text-center">
               
            </tr>`;
        });
        $('#poTableBody').html(rows);
      } else {
        $('#poTableBody').html('<tr><td colspan="6" class="text-center p-3 text-gray-500">No Job Orders Found</td></tr>');
      }
    },
    error: function() {
      $('#poTableBody').html('<tr><td colspan="6" class="text-center p-3 text-red-500">Error loading Job Orders</td></tr>');
    }
  });
}

// Handle single selection
$(document).on('click', '.selectSinglePO', function() {
  const jobNo = $(this).data('pono');
  fetchPOItems([jobNo]);
  $('#selectPOModal').addClass('hidden');
});

// Handle multiple selection
$('#confirmSelectedPOs').on('click', function() {
    const selectedPOs = $('.poCheckbox:checked').map(function() {
        return $(this).val();
    }).get();

    if (selectedPOs.length === 0) {
        alert('Please select at least one PO.');
        return;
    }

    // ✅ Gather customer names for selected POs
    let selectedCustomers = [];

    $('.poCheckbox:checked').each(function() {
        let row = $(this).closest('tr');
        let customerName = row.find('td:nth-child(3)').text().trim();
        selectedCustomers.push(customerName);
    });

    // Remove duplicates
    selectedCustomers = [...new Set(selectedCustomers)];

    // ❌ If more than 1 unique customer found → stop
    if (selectedCustomers.length > 1) {
        alert('Selected Job Orders belong to different customers. Please select POs of the SAME customer.');
        return;
    }

    // ✅ Only one customer → set Customer dropdown
    let finalCustomerName = selectedCustomers[0];

    // Find customer in dropdown by name
    let foundOption = $("#CustomerID option").filter(function() {
        return $(this).text().trim() === finalCustomerName;
    }).val();

    if (foundOption) {
        $("#CustomerID").val(foundOption).trigger('change');
    }

// ADD THIS
$("#CustomerID").prop("disabled", true);
$("#clearCustomerBtn").removeClass("hidden");
    // Continue as normal
    fetchPOItems(selectedPOs);
    $('#selectPOModal').addClass('hidden');
});

// Fetch selected PO job items
// ✅ Professional version: clear old empty rows + fill table neatly
function fetchPOItems(poList) {
  $.ajax({
    url: 'invoice_functions.php',
    type: 'POST',
    data: { 
      action: 'get_job_order_items', 
      job_order_nos: poList 
    },
    dataType: 'json',
    success: function(response) {
      if (response.success && response.items.length > 0) {
        // ✅ 1. Remove all rows that are empty (no JobNo, no Detail, no totals)
        $('#invoiceTableBody tr').each(function() {
          const hasContent = $(this).find('.detail-input').val() ||
                             $(this).find('.job-no').val() ||
                             parseFloat($(this).find('.total-input').val()) > 0;
          if (!hasContent) $(this).remove();
        });

        // ✅ 2. Add new rows cleanly
        response.items.forEach(item => {
          addInvoiceRow();
          const lastRow = $('#invoiceTableBody tr:last');

          // ✅ Make sure Job Number field matches returned key (JobNo)
          lastRow.find('.job-no').val(item.JobNo || item.JobOrderNo || '');
          lastRow.find('.detail-input').val(item.Detail || '');
          lastRow.find('.media-select').val(item.Media || '');
          lastRow.find('.width-input').val(item.Width || 0);
          lastRow.find('.height-input').val(item.Height || 0);
          lastRow.find('.qty-input').val(item.Qty || 1);
          lastRow.find('.rate-input').val(item.Rate || 0);

          calculateRowTotal(lastRow);
        });

        // ✅ 3. Scroll table to bottom smoothly (user feedback)
        const tableContainer = $('.table-responsive');
        tableContainer.animate({ scrollTop: tableContainer.prop('scrollHeight') }, 400);

        // ✅ 4. Update totals after new data
        calculateTotals();
      } else {
        alert('No items found for selected Job Orders.');
      }
    },
    error: function(xhr) {
      console.error('Error fetching Job Order items:', xhr.responseText);
      alert('Error fetching Job Order items.');
    }
  });
}





$(document).on("keydown", ".rate-input", function(e) {

    if (e.key === "Tab") {
        e.preventDefault(); // Stop normal tab

        let currentRow = $(this).closest("tr");
        let nextRow = currentRow.next("tr");

        if (nextRow.length) {
            let nextRate = nextRow.find(".rate-input");
            nextRate.focus();
            setTimeout(() => nextRate.select(), 10); // 🔥 auto-select
        } else {
            // If last row → add new row automatically
            addInvoiceRow();
            setTimeout(() => {
                let newRate = $("#row-" + rowCounter).find(".rate-input");
                newRate.focus();
                newRate.select(); // 🔥 auto-select
            }, 50);
        }
    }
});

$("#clearCustomerBtn").on("click", function () {

    // Enable Dropdown again
    $("#CustomerID").prop("disabled", false);

    // Clear customer
    $("#CustomerID").val("").trigger("change");

    // Hide clear button
    $("#clearCustomerBtn").addClass("hidden");

    // Clear invoice table rows (optional cleanup)
    $("#invoiceTableBody").empty();
    invoiceRowCount = 0;
    for (var i = 1; i <= 7; i++) {
        addInvoiceRow();
    }

    // Optional: clear PO modal selections
    $(".poCheckbox").prop("checked", false);
});
</script>


</body>
</html>