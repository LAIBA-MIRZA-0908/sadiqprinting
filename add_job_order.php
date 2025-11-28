<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// add_job_order.php
include 'session_check.php';
include 'header.php';
include 'menu.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .numeric-input {
            text-align: right;
        }
        .checkbox-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
 
<style>
/* Adjust Select2 styling to match Tailwind inputs */
.select2-container {
    width: 100% !important;
}

/* Select2 Dropdown Styling */
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

/* Search Input in Dropdown */
.select2-search--dropdown .select2-search__field {
    border: 1px solid #d1d5db !important;
    border-radius: 0.375rem !important;
    padding: 4px 8px !important;
    font-size: 0.875rem !important;
}

/* Dropdown Results */
.select2-results__option {
    font-size: 0.875rem !important;
    padding: 6px 12px !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #2563eb !important;
}

/* Make dropdown appear above table if needed */
.select2-dropdown {
    z-index: 9999 !important;
}
</style>

</head>
<body class="bg-gray-100">
   <div class="container mx-auto px-4 py-6">

    <!-- Job Order Form -->
    <form id="jobOrderForm">
        <!-- ✅ Job Order Header - Compact Version -->
         <div class="bg-blue-600 text-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold">Add New Job Order</h1>
                <p class="text-blue-100">Create job order with multiple details</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="job_orders_list.php" class="bg-white hover:bg-gray-100 text-blue-600 px-4 py-2 rounded-lg text-sm flex items-center font-medium transition-colors">
                    <i class="fas fa-list mr-2"></i> View Job Orders
                </a>
            </div>
        </div>
    </div>
            
            <!-- ✅ Compact Grid with smaller padding -->
            <div class="p-3">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Job Order No</label>
                        <input type="text" id="JobOrderNo" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-100" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Order Date *</label>
                        <input type="date" id="OrderDate" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div style="display:none">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Delivery Date *</label>
                        <input type="date" id="DeliveryDate" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div style="display:none">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Design By *</label>
                        <div class="flex space-x-4">
                            <div class="flex items-center">
                                <input type="radio" name="DesignBy" id="DesignByClient" value="Client" class="h-4 w-4 text-blue-600 focus:ring-blue-500" required>
                                <label for="DesignByClient" class="ml-2 block text-xs text-gray-700">Client</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="DesignBy" id="DesignByFresh" value="Fresh" class="h-4 w-4 text-blue-600 focus:ring-blue-500" checked required>
                                <label for="DesignByFresh" class="ml-2 block text-xs text-gray-700">Fresh</label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Job For *</label>
                        <select id="JobFor" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="New Client">New Client</option>
                            <option value="Existing Client">Existing Client</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Customer Name *</label>
                        <input type="text" id="CustomerName" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <input type="hidden" id="CustomerID" name="CustomerID">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cell No *</label>
                        <input type="text" id="CellNo" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Designer</label>
                        <input type="text" id="Designername" name="Designername"  readonly
                               value="<?php echo htmlspecialchars($user_name); ?>" 
                               class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 rounded-md bg-gray-100">
                        <input type="hidden" id="DesignerID" name="DesignerID"
                               value="<?php echo htmlspecialchars($user_id); ?>">
                    </div>
                    <div style="display:none">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Advance Payment</label>
                        <input type="number" id="AdvancePayment" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md numeric-input focus:ring-blue-500 focus:border-blue-500" 
                               step="0.01" value="0.00">
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ Job Order Details Table - Updated with Centered Dark Headers -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="bg-green-600 text-white px-4 py-2 rounded-t-lg flex justify-between items-center">
                <h3 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-list-alt mr-2"></i>Job Order Details
                </h3>
                <button type="button" class="bg-white text-green-600 px-3 py-1 rounded text-sm font-medium hover:bg-gray-100" onclick="addDetailRow()">
                    <i class="fas fa-plus mr-1"></i> Add Row
                </button>
            </div>
            <div class="p-4">
                <div class="table-responsive overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-300" id="detailsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">Sr#</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width:100px;">Detail</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Media</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Width</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Height</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Qty</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Sqft</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">Ring</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">Pocket</th>
                            </tr>
                        </thead>
                        <tbody id="detailsTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Dynamic rows will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-3">
            <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm" onclick="resetForm()">
                <i class="fas fa-times mr-2"></i> Cancel
            </button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-save mr-2"></i> Save Job Order
            </button>
        </div>
    </form>
</div>
    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>Success
                    </h3>
                    <button onclick="closeSuccessModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mt-4 px-7 py-3">
                    <p class="text-sm text-gray-500">Job Order saved successfully!</p>
                    <p class="text-sm text-gray-500 font-medium" id="jobOrderNumber"></p>
                </div>
                <div class="flex justify-end space-x-3 mt-4 pt-4 border-t">
                    <button onclick="resetForm(); closeSuccessModal();" class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md hover:bg-blue-700 focus:outline-none">
                        Add New Order
                    </button>
                    <a href="job_orders_list.php" class="px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md hover:bg-gray-300 focus:outline-none">
                        View Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
<!-- Customer Selection Modal -->
<!-- Customer Selection Modal -->
<div id="customerModal" class="fixed inset-0 bg-gray-700 bg-opacity-50 hidden z-50">
  <div class="relative top-20 mx-auto bg-white rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-1/2 max-h-[80vh] overflow-hidden">
    <div class="flex justify-between items-center bg-blue-600 text-white px-4 py-3 rounded-t-lg">
      <h3 class="text-lg font-semibold"><i class="fas fa-users mr-2"></i>Select Existing Customer</h3>
      <button onclick="closeCustomerModal()" class="text-white hover:text-gray-200">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Search bar -->
    <div class="p-3 bg-gray-100 border-b border-gray-300">
      <input type="text" id="customerSearch" placeholder="Search customer by name or contact..."
        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <!-- Table -->
    <div class="p-4 overflow-y-auto" style="max-height: 60vh;">
      <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
          </tr>
        </thead>
        <tbody id="customerTableBody" class="bg-white divide-y divide-gray-200">
          <!-- Filled dynamically -->
        </tbody>
      </table>
    </div>
  </div>
</div>


   <!-- ✅ Load jQuery first (no defer) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
.select2-container--open .select2-dropdown {
    width: auto !important;
    min-width: 200px !important;
    position: absolute !important;
}
    </style>
<!-- ✅ Then load Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        var detailRowCount = 0;
        var materials = [];

        $(document).ready(function() {
            // Set today's date
          // Set today's date for both Order and Delivery Date
const today = new Date().toISOString().split('T')[0];
$('#OrderDate').val(today);
$('#DeliveryDate').val(today);
            
            // Generate Job Order No
            generateJobOrderNo();
            
            // Load materials
            loadMaterials();
            
            // Add first detail row
            addDetailRow();
            
            // Form submission
          $('#jobOrderForm').submit(function(e) {
    e.preventDefault();

 $('#CustomerName').prop('readonly', false).removeClass('bg-gray-100');
    $('#CellNo').prop('readonly', false).removeClass('bg-gray-100');

    // If no customer selected, create new one first
    const customerID = $('#CustomerID').val().trim();
    if (!customerID) {
        console.log('No CustomerID found — creating new customer first...');
        saveCustomerThenJobOrder();
    } else {
        submitJobOrder();
    }
});
            
            // alert(detailRowCount);
            if (detailRowCount === 1) {
                for (var i = 1; i <= 7; i++) {
                    addDetailRow();
                }
            }
        });

       function generateJobOrderNo() {
    // format date part as ddmmyyyy
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
    var yyyy = today.getFullYear();
    var datePart = dd + mm + yyyy;

    // Ask server for how many job orders exist today
    $.ajax({
        url: 'job_order_functions.php',
        type: 'POST',
        data: { action: 'get_today_count' },
        dataType: 'json',
        success: function(response) {
            if (response && response.success) {
                // server returned count of existing orders today
                var countToday = parseInt(response.count, 10) || 0;
                var nextNumber = countToday + 1; // increment for this new order
                $('#JobOrderNo').val('JB-' + datePart + '-' + nextNumber);
            } else {
                // fallback: if server fails, still set something sensible
                $('#JobOrderNo').val('JB-' + datePart + '-1');
                console.error('Failed to get today count:', response ? response.message : 'no response');
            }
        },
        error: function(xhr, status, err) {
            // fallback if ajax fails
            $('#JobOrderNo').val('JB-' + datePart + '-1');
            console.error('AJAX error fetching today count:', status, err);
        }
    });
}


        function loadMaterials() {
            $.ajax({
                url: 'job_order_functions.php',
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
function updateMaterialDropdowns() {
    $('.media-select').each(function() {
        var currentValue = $(this).val();
        var $select = $(this);
        
        // Clear and rebuild options
        $select.empty().append('<option value="">Select Media</option>');
        
        for (var i = 0; i < materials.length; i++) {
            $select.append('<option value="' + materials[i].name + '">' + materials[i].name + '</option>');
        }

        $select.val(currentValue);

        // Apply Select2 if not already applied
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


      function addDetailRow() {
    detailRowCount++;
    var row = '<tr id="detailRow-' + detailRowCount + '">' +
        '<td class="px-2 py-2 whitespace-nowrap"><input type="text" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-center bg-gray-100" value="' + detailRowCount + '" readonly></td>' +
        '<td class="px-2 py-2"><input type="text" name="Detail[]" class="w-full px-3 py-1 text-sm border border-gray-300 rounded" placeholder="Enter detail" style="min-width: 100px;"></td>' +
        '<td class="px-2 py-2"><select name="Media[]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded media-select" style="min-width: 140px;"><option value="">Select Media</option></select></td>' +
        '<td class="px-2 py-2"><input type="number" name="Width[]" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded numeric-input" step="1" onchange="calculateSqft(' + detailRowCount + ')" placeholder="Width"></td>' +
'<td class="px-2 py-2"><input type="number" name="Height[]" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded numeric-input" step="1" onchange="calculateSqft(' + detailRowCount + ')" placeholder="Height"></td>' +
'<td class="px-2 py-2"><input type="number" name="Qty[]" class="w-16 px-2 py-1 text-sm border border-gray-300 rounded numeric-input text-center" onchange="calculateSqft(' + detailRowCount + ')" placeholder="Qty"></td>' +
'<td class="px-2 py-2"><input type="number" name="Sqft[]" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded numeric-input" step="1" readonly placeholder="Sqft"></td>' +

        '<td class="px-2 py-2"><div class="checkbox-wrapper"><input type="checkbox" name="Ring[]" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500"></div></td>' +
        '<td class="px-2 py-2"><div class="checkbox-wrapper"><input type="checkbox" name="Pocket[]" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500"></div></td>' +
        '</tr>';
    
    $('#detailsTableBody').append(row);
    
    // ✅ Apply Select2 to the newly added Media dropdown for searchable functionality
    $('#detailsTableBody tr:last .media-select').select2({
        placeholder: 'Search media',
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true
    });
    
    // Update material dropdown for new row
    updateMaterialDropdowns();
}

        function removeDetailRow(rowId) {
            $('#detailRow-' + rowId).remove();
            updateSerialNumbers();
        }

        function updateSerialNumbers() {
            $('#detailsTableBody tr').each(function(index) {
                $(this).find('td:first input').val(index + 1);
            });
        }

        function calculateSqft(rowId) {
            var width = parseFloat($('input[name="Width[]"]').eq(rowId - 1).val()) || 0;
            var height = parseFloat($('input[name="Height[]"]').eq(rowId - 1).val()) || 0;
            var qty = parseFloat($('input[name="Qty[]"]').eq(rowId - 1).val()) || 0;
            var sqft = width * height * qty;
            
            $('input[name="Sqft[]"]').eq(rowId - 1).val(sqft.toFixed(2));
        }

        function submitJobOrder() {
            // Validate form
            if (!$('#jobOrderForm')[0].checkValidity()) {
                $('#jobOrderForm')[0].reportValidity();
                return;
            }

            // Check if at least one detail row exists
            if ($('#detailsTableBody tr').length === 0) {
                alert('Please add at least one detail row.');
                return;
            }

            // Collect form data - REMOVED JobOrderNo since it's auto-generated
            var formData = {
                action: 'create_job_order',
                OrderDate: $('#OrderDate').val(),
                DeliveryDate: $('#DeliveryDate').val(),
                DesignBy: $('input[name="DesignBy"]:checked').val(),
                JobFor: $('#JobFor').val(),
                CustomerName: $('#CustomerName').val(),
                  CustomerID: $('#CustomerID').val(),
                CellNo: $('#CellNo').val(),
                Designer: $('#Designername').val(),
                DesignerID: $('#DesignerID').val(),
                AdvancePayment: $('#AdvancePayment').val(),
                details: []
            };

            // Collect ONLY VALID detail rows
            var hasValidRow = false;
            $('#detailsTableBody tr').each(function(index) {
                var row = $(this);
                var Detail = row.find('input[name="Detail[]"]').val().trim();
                var Media = row.find('select[name="Media[]"]').val();
                var Width = parseFloat(row.find('input[name="Width[]"]').val()) || 0;
                var Height = parseFloat(row.find('input[name="Height[]"]').val()) || 0;
                var Qty = parseFloat(row.find('input[name="Qty[]"]').val()) || 0;
                var Sqft = parseFloat(row.find('input[name="Sqft[]"]').val()) || 0;
                
                // Log each row for debugging
                console.log('Checking Row ' + (index + 1) + ':', {
                    Detail: Detail,
                    Media: Media,
                    Width: Width,
                    Height: Height,
                    Qty: Qty,
                    Sqft: Sqft,
                    isValid: (Detail && Media && Width > 0 && Height > 0 && Qty > 0)
                });
                
                // ONLY include rows where ALL required fields are filled AND have valid values
                if (Detail && Media && Width > 0 && Height > 0 && Qty > 0) {
                    hasValidRow = true;
                    formData.details.push({
                        SrNo: index + 1,
                        Detail: Detail,
                        Media: Media,
                        Width: Width,
                        Height: Height,
                        Qty: Qty,
                        Sqft: Sqft,
                        Ring: row.find('input[name="Ring[]"]').is(':checked') ? 1 : 0,
                        Pocket: row.find('input[name="Pocket[]"]').is(':checked') ? 1 : 0
                    });
                    console.log('Added valid row to formData:', formData.details[formData.details.length - 1]);
                }
            });

            console.log('Final formData being sent:', formData);
            console.log('Total valid rows:', formData.details.length);

            if (!hasValidRow) {
                alert('Please add at least one complete detail row with all required fields (Detail, Media, Width, Height, and Qty).');
                return;
            }

            // Submit to server
            $.ajax({
                url: 'job_order_functions.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(data) {
                    console.log('Server response:', data);
                    if (data.success) {
                        $('#jobOrderNumber').text('Job Order No: ' + data.JobOrderNo + 
                                                 (data.detailsSaved ? ' (' + data.detailsSaved + ' details saved)' : ''));
                        showSuccessModal();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.log('Response Text:', xhr.responseText);
                    console.log('Status Code:', xhr.status);
                    
                    // Try to parse the response to see what's actually being returned
                    try {
                        const responseText = xhr.responseText;
                        if (responseText.length === 0) {
                            alert('Server returned empty response. Check PHP error logs.');
                        } else if (responseText.includes('<br />') || responseText.includes('<b>')) {
                            alert('Server error: PHP error detected. Check console for details.');
                            console.error('PHP Error Response:', responseText);
                        } else {
                            alert('Network error occurred. Please try again.');
                        }
                    } catch (e) {
                        alert('Network error occurred. Please try again.');
                    }
                }
            });
        }

      function resetForm() {
    $('#jobOrderForm')[0].reset();
    $('#OrderDate').val(new Date().toISOString().split('T')[0]);
    $('#DesignByFresh').prop('checked', true);
    $('#AdvancePayment').val('0.00');
    $('#detailsTableBody').empty();
    detailRowCount = 0;
    generateJobOrderNo();
    addDetailRow();
    
    // ✅ NEW CODE: Reset fields to editable state
    $('#CustomerName').prop('readonly', false).removeClass('bg-gray-100');
    $('#CellNo').prop('readonly', false).removeClass('bg-gray-100');
    
    // Hide modal if shown
    closeSuccessModal();
}
        
        function showSuccessModal() {
            document.getElementById('successModal').classList.remove('hidden');
        }
        
        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
        }



// When user chooses JobFor = Existing Client, show modal and disable fields
$('#JobFor').change(function() {
    if ($(this).val() === 'Existing Client') {
        loadCustomers();
        showCustomerModal();
        
        // ✅ NEW CODE: Disable Customer Name and Cell No fields
        $('#CustomerName').prop('readonly', true).addClass('bg-gray-100');
        $('#CellNo').prop('readonly', true).addClass('bg-gray-100');
    } else {
        $('#CustomerID').val('');
        $('#CustomerName').val('').prop('readonly', false).removeClass('bg-gray-100');
        $('#CellNo').val('').prop('readonly', false).removeClass('bg-gray-100');
    }
});

function showCustomerModal() {
    document.getElementById('customerModal').classList.remove('hidden');
}

function closeCustomerModal() {
    document.getElementById('customerModal').classList.add('hidden');
}
let allCustomers = [];
// Load customers from server
function loadCustomers() {
    $.ajax({
        url: 'job_order_functions.php',
        type: 'POST',
        data: { action: 'get_customers' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                allCustomers = response.customers; // store full list
                populateCustomerTable(allCustomers);
            } else {
                alert('Failed to load customers: ' + response.message);
            }
        },
        error: function() {
            alert('Error loading customers.');
        }
    });
}

// Function to mask phone number
function maskPhone(phone) {
    if (!phone) return '';
    phone = phone.replace(/\s+/g, ''); // remove any spaces
    if (phone.length <= 4) return phone; // agar chhota number hai
    return phone.slice(0, 5) + '*'.repeat(phone.length - 5); // first 5 show, baqi * 
}


// Function to mask phone number
function maskPhone(phone) {
    if (!phone) return '';
    phone = phone.replace(/\s+/g, ''); // remove any spaces
    if (phone.length <= 4) return phone; // agar chhota number hai
    return phone.slice(0, 5) + '*'.repeat(phone.length - 5); // first 5 show, baqi * 
}

// Populate table with (filtered) customers
function populateCustomerTable(customers) {
    const tbody = $('#customerTableBody');
    tbody.empty();

    if (!customers || customers.length === 0) {
        tbody.append('<tr><td colspan="3" class="text-center py-4 text-gray-500">No customers found</td></tr>');
        return;
    }

    customers.forEach(cust => {
        const maskedPhone = maskPhone(cust.Phone);
        const row = `
            <tr>
                <td class="px-4 py-2 text-sm text-gray-700">${cust.CustomerName}</td>
                <td class="px-4 py-2 text-sm text-gray-700">${maskedPhone}</td>
                <td class="px-4 py-2 text-center">
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded"
                        onclick="selectCustomer('${cust.CustomerID}', '${cust.CustomerName.replace(/'/g, "\\'")}', '${cust.Phone ? cust.Phone.replace(/'/g, "\\'") : ''}')">
                        Select
                    </button>
                </td>
            </tr>`;
        tbody.append(row);
    });
}


// Live search filter
$('#customerSearch').on('input', function() {
    const query = $(this).val().toLowerCase();
    const filtered = allCustomers.filter(c =>
        c.CustomerName.toLowerCase().includes(query) ||
        (c.Phone && c.Phone.toLowerCase().includes(query))
    );
    populateCustomerTable(filtered);
});

// When select is clicked
function selectCustomer(id, name, phone) {
    $('#CustomerID').val(id);
    $('#CustomerName').val(name);
    $('#CellNo').val(maskPhoneNumber(phone));
    
    // ✅ NEW CODE: Ensure fields remain disabled after selection
    $('#CustomerName').prop('readonly', true).addClass('bg-gray-100');
    $('#CellNo').prop('readonly', true).addClass('bg-gray-100');
    
    closeCustomerModal();
}
function saveCustomerThenJobOrder() {
    const customerName = $('#CustomerName').val().trim();
    const cellNo = $('#CellNo').val().trim();

    if (!customerName || !cellNo) {
        alert('Please enter Customer Name and Cell No to create a new customer.');
        return;
    }

    // Send AJAX to save customer first
    $.ajax({
        url: 'job_order_functions.php',
        type: 'POST',
        data: {
            action: 'create_customer',
            CustomerName: customerName,
            CellNo: cellNo
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.CustomerID) {
                console.log('New customer saved:', response);
                // Set CustomerID and proceed to save job order
                $('#CustomerID').val(response.CustomerID);
                submitJobOrder();
            } else {
                alert('Failed to save customer: ' + (response.message || 'Unknown error.'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error creating customer:', status, error);
            alert('Error creating new customer. Please check console.');
        }
    });
}
function maskPhoneNumber(phone) {
    if (!phone) return '';
    phone = phone.toString().replace(/\s+/g, '');

    if (phone.length <= 4) return phone;

    // Show first 4 digits → mask the remaining
    return phone.slice(0, 4) + '*'.repeat(phone.length - 4);
}
    </script>
</body>
</html>