<?php
// edit_job_order.php
// Edit Job Order page (Option A)
// Place this file in your project and open as: edit_job_order.php?id=123

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
include 'header.php';
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Job Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .numeric-input { text-align: right; }
        .checkbox-wrapper { display:flex; justify-content:center; align-items:center; height:100%; }
        .table-responsive { max-height: 420px; overflow-y: auto; }
        .select2-container { width:100% !important; }
    </style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-6">
    <div class="bg-blue-600 text-white rounded-lg shadow-md p-4 mb-6 flex justify-between items-center">
     
 
    
    <div>
            <h1 class="text-2xl font-bold">Edit Job Order (Customer :   <span id="currentCustomerName">Loading...</span>)</h1>
            <p class="text-blue-100">Modify job order and details</p>
        </div>
        <div>
            <a href="job_orders_list.php" class="bg-white text-blue-600 px-3 py-2 rounded">Back to List</a>
        </div>
    </div>

    <form id="editJobForm">
        <input type="hidden" id="JobOrderNo" name="JobOrderNo" />
        <div class="bg-white rounded-lg p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700">Job Order No</label>
                    <input id="JobOrderNoDisplay" class="w-full px-2 py-1 text-sm border rounded bg-gray-100" readonly />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Order Date*</label>
                    <input type="date" id="OrderDate" name="OrderDate" class="w-full px-2 py-1 text-sm border rounded" required />
                </div>
                <div style="display:none">
                    <label class="block text-xs font-medium text-gray-700">Delivery Date</label>
                    <input type="date" id="DeliveryDate" name="DeliveryDate" class="w-full px-2 py-1 text-sm border rounded" />
                </div>
                <div  style="display:none">
                    <label class="block text-xs font-medium text-gray-700">Job For</label>
                    <select id="JobFor" name="JobFor" class="w-full px-2 py-1 text-sm border rounded">
                        <option>New Client</option>
                        <option>Existing Client</option>
                    </select>
                </div>

                <div  style="display:none">
                    <label class="block text-xs font-medium text-gray-700">Customer Name*</label>
                    <input type="text" id="CustomerName" name="CustomerName" class="w-full px-2 py-1 text-sm border rounded" required />
                    <input type="hidden" id="CustomerID" name="CustomerID">
                </div>
                <button type="button"
        id="changeCustomerBtn"
        class="mt-1 bg-blue-600 text-white px-3 py-1 rounded text-xs">
    Change Customer
</button>
                <div  style="display:none">
                    <label class="block text-xs font-medium text-gray-700">Cell No*</label>
                    <input type="text" id="CellNo" name="CellNo" class="w-full px-2 py-1 text-sm border rounded" required />
                </div>
                <div  style="display:none">
                    <label class="block text-xs font-medium text-gray-700">Designer</label>
                    <input type="text" id="Designername" name="Designername" class="w-full px-2 py-1 text-sm border rounded" />
                    <input type="hidden" id="DesignerID" name="DesignerID">
                </div>
                <div style="display:none">
                    <label class="block text-xs font-medium text-gray-700">Advance Payment</label>
                    <input type="number" id="AdvancePayment" name="AdvancePayment" step="0.01" class="w-full px-2 py-1 text-sm border rounded numeric-input" />
                </div>
            </div>
        </div>

        <!-- Details table -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="bg-green-600 text-white px-4 py-2 rounded-t-lg flex justify-between items-center">
                <h3 class="text-lg font-semibold"><i class="fas fa-list-alt mr-2"></i>Job Order Details</h3>
                <div class="flex items-center gap-2">
                    <button type="button" id="addRowBtn" class="bg-white text-green-600 px-3 py-1 rounded text-sm" onclick="addDetailRow()"><i class="fas fa-plus mr-1"></i> Add Row</button>
                    <div class="text-sm text-white">Total Sqft: <span id="totalSqft">0.00</span></div>
                </div>
            </div>
            <div class="p-4">
                <div class="table-responsive overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border" id="detailsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-xs">Sr#</th>
                                <th class="px-2 py-2 text-xs">Detail</th>
                                <th class="px-2 py-2 text-xs">Media</th>
                                <th class="px-2 py-2 text-xs">Width</th>
                                <th class="px-2 py-2 text-xs">Height</th>
                                <th class="px-2 py-2 text-xs">Qty</th>
                                <th class="px-2 py-2 text-xs">Sqft</th>
                                <th class="px-2 py-2 text-xs">Ring</th>
                                <th class="px-2 py-2 text-xs">Pocket</th>
                                <th class="px-2 py-2 text-xs">Remove</th>
                            </tr>
                        </thead>
                        <tbody id="detailsTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="job_orders_list.php" class="px-4 py-2 bg-gray-300 rounded">Cancel</a>
            <button type="submit" id="saveBtn" class="px-4 py-2 bg-blue-600 text-white rounded">Save Changes</button>
        </div>
    </form>
</div>
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

<!-- Status Modal reused from list if needed (not mandatory) -->

<!-- JS libs -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let materials = [];
let detailRowCount = 0;
let orderStatus = '';

$(document).ready(function(){
    // init
    loadMaterials();

    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    if (!id) {
        alert('Missing Job Order id in URL. Use edit_job_order.php?id=123');
        return;
    }
    $('#JobOrderNo').val(id);
    $('#JobOrderNoDisplay').val('JB#' + id);

    // Load order header + details
    loadOrder(id);

    // Form submit
    $('#editJobForm').submit(function(e){
        e.preventDefault();
        saveEditedOrder();
    });

    // Delegated events for dynamic rows
    $('#detailsTableBody').on('input', 'input[name="Width[]"], input[name="Height[]"], input[name="Qty[]"]', function(){
        const rowId = $(this).closest('tr').data('row');
        calculateSqft(rowId);
    });

    $('#detailsTableBody').on('click', '.remove-row', function(){
        const row = $(this).closest('tr');
        row.remove();
        updateSerialNumbers();
        recalcTotalSqft();
    });
});

function loadMaterials(){
    $.post('job_order_functions.php', { action: 'get_materials' }, function(res){
        if (res.success) {
            materials = res.materials;
        }
    }, 'json');
}

function loadOrder(id){
    // get header+details
    $.post('job_order_functions.php', { action: 'get_job_order_details', jobOrderNo: id }, function(res){
        if (!res.success) { alert(res.message || 'Failed to load'); return; }

        const order = res.order;
        const details = res.details || [];

        // Populate header fields
        // Note: get_job_order_details earlier doesn't return status. We'll call get_job_orders to find status.
        $('#OrderDate').val(formatDateForInput(order.OrderDate));
        $('#DeliveryDate').val(formatDateForInput(order.DeliveryDate));
        $('#CustomerName').val(order.CustomerName);
        $('#currentCustomerName').text(order.CustomerName);

        $('#CustomerID').val(order.CustomerID || '');
        $('#CellNo').val(order.CellNo);
        $('#Designername').val(order.Designer || '');
        $('#AdvancePayment').val(parseFloat(order.AdvancePayment || 0).toFixed(2));

        // Clear detail rows and add from data
        $('#detailsTableBody').empty();
        detailRowCount = 0;
        for (let i=0;i<details.length;i++){
            addDetailRow(details[i]);
        }

        // grab status from main list (fallback) - request get_job_orders and find
        $.post('job_order_functions.php', { action: 'get_job_orders' }, function(resp){
            if (resp.success) {
                const found = (resp.orders || []).find(o => String(o.JobOrderNo) === String(id));
                orderStatus = found ? (found.status || '') : '';
                toggleEditableByStatus();
            }
            recalcTotalSqft();
        }, 'json');

    }, 'json');
}

function formatDateForInput(dateStr){
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    const iso = d.toISOString().split('T')[0];
    return iso;
}

function addDetailRow(data){
    detailRowCount++;
    const rowId = detailRowCount;
    const detail = data && data.Detail ? data.Detail : '';
    const media = data && data.Media ? data.Media : '';
    const width = data && data.Width ? data.Width : '';
    const height = data && data.Height ? data.Height : '';
    const qty = data && data.Qty ? data.Qty : '';
    const sqft = data && data.Sqft ? data.Sqft : '';
    const ring = data && data.Ring ? 1 : 0;
    const pocket = data && data.Pocket ? 1 : 0;

    const mediaOptions = materials.map(m => `<option value="${escapeHtml(m.name)}" ${m.name === media ? 'selected' : ''}>${escapeHtml(m.name)}</option>`).join('');

    const tr = $(
        `<tr data-row="${rowId}" id="detailRow-${rowId}">
            <td class="px-2 py-2 text-center"><input type="text" class="w-12 px-2 py-1 text-sm border rounded bg-gray-100 text-center" value="${rowId}" readonly></td>
            <td class="px-2 py-2"><input name="Detail[]" class="w-full px-2 py-1 text-sm border rounded" value="${escapeHtml(detail)}"></td>
            <td class="px-2 py-2">
                <select name="Media[]" class="media-select w-full px-2 py-1 text-sm border rounded"> 
                    <option value="">Select Media</option>
                    ${mediaOptions}
                </select>
            </td>
            <td class="px-2 py-2"><input type="number" name="Width[]" class="w-20 px-2 py-1 text-sm border rounded numeric-input" step="0.01" value="${width}"></td>
            <td class="px-2 py-2"><input type="number" name="Height[]" class="w-20 px-2 py-1 text-sm border rounded numeric-input" step="0.01" value="${height}"></td>
            <td class="px-2 py-2"><input type="number" name="Qty[]" class="w-16 px-2 py-1 text-sm border rounded numeric-input" step="1" value="${qty}"></td>
            <td class="px-2 py-2"><input type="number" name="Sqft[]" class="w-24 px-2 py-1 text-sm border rounded numeric-input" step="0.01" readonly value="${sqft}"></td>
            <td class="px-2 py-2 text-center"><input type="checkbox" name="Ring[]" ${ring ? 'checked' : ''}></td>
            <td class="px-2 py-2 text-center"><input type="checkbox" name="Pocket[]" ${pocket ? 'checked' : ''}></td>
            <td class="px-2 py-2 text-center"><button type="button" class="text-red-600 remove-row">Remove</button></td>
        </tr>`
    );

    $('#detailsTableBody').append(tr);

    // Attach select2
    tr.find('.media-select').select2({ placeholder: 'Search media', allowClear: true, width: '100%' });

    // If this row had width/height/qty provided, calculate sqft
    calculateSqft(rowId);
    recalcTotalSqft();
}

function calculateSqft(rowId){
    const row = $(`#detailRow-${rowId}`);
    if (!row.length) return;
    const width = parseFloat(row.find('input[name="Width[]"]').val()) || 0;
    const height = parseFloat(row.find('input[name="Height[]"]').val()) || 0;
    const qty = parseFloat(row.find('input[name="Qty[]"]').val()) || 0;
    const sqft = width * height * qty;
    row.find('input[name="Sqft[]"]').val(sqft ? sqft.toFixed(2) : '0.00');
    recalcTotalSqft();
}

function recalcTotalSqft(){
    let total = 0;
    $('#detailsTableBody tr').each(function(){
        const v = parseFloat($(this).find('input[name="Sqft[]"]').val()) || 0;
        total += v;
    });
    $('#totalSqft').text(total.toFixed(2));
}

function updateSerialNumbers(){
    $('#detailsTableBody tr').each(function(i){
        $(this).attr('data-row', i+1);
        $(this).find('input[readonly]').first().val(i+1);
        $(this).attr('id', 'detailRow-' + (i+1));
    });
    detailRowCount = $('#detailsTableBody tr').length;
}

function saveEditedOrder(){
    // Validate required
    if (!$('#CustomerName').val().trim() || !$('#CellNo').val().trim()) { alert('Customer name and cell required'); return; }
    if ($('#detailsTableBody tr').length === 0) { alert('Add at least one detail row'); return; }

    // Collect details
    const details = [];
    $('#detailsTableBody tr').each(function(i){
        const r = $(this);
        const detail = r.find('input[name="Detail[]"]').val().trim();
        const media = r.find('select[name="Media[]"]').val();
        const width = parseFloat(r.find('input[name="Width[]"]').val()) || 0;
        const height = parseFloat(r.find('input[name="Height[]"]').val()) || 0;
        const qty = parseFloat(r.find('input[name="Qty[]"]').val()) || 0;
        const sqft = parseFloat(r.find('input[name="Sqft[]"]').val()) || 0;
        const ring = r.find('input[name="Ring[]"]').is(':checked') ? 1 : 0;
        const pocket = r.find('input[name="Pocket[]"]').is(':checked') ? 1 : 0;

        if (detail && media && width > 0 && height > 0 && qty > 0) {
            details.push({ SrNo: i+1, Detail: detail, Media: media, Width: width, Height: height, Qty: qty, Sqft: sqft, Ring: ring, Pocket: pocket });
        }
    });

    if (details.length === 0) { alert('Please complete at least one detail row with all required fields'); return; }

    const payload = {
        action: 'update_job_order',
        JobOrderNo: $('#JobOrderNo').val(),
        OrderDate: $('#OrderDate').val(),
        DeliveryDate: $('#DeliveryDate').val(),
        JobFor: $('#JobFor').val(),
        CustomerName: $('#CustomerName').val(),
        CustomerID: $('#CustomerID').val(),
        CellNo: $('#CellNo').val(),
        Designer: $('#Designername').val(),
        DesignerID: $('#DesignerID').val(),
        AdvancePayment: $('#AdvancePayment').val(),
        details: details
    };

    // Disable button to prevent double submit
    $('#saveBtn').prop('disabled', true).text('Saving...');

    $.ajax({
        url: 'job_order_functions.php',
        type: 'POST',
        data: payload,
        dataType: 'json',
        success: function(res){
            $('#saveBtn').prop('disabled', false).text('Save Changes');
            if (res.success) {
                alert('Job order updated successfully');
                window.location.href = 'job_orders_list.php';
            } else {
                alert('Error: ' + (res.message || 'Unknown error'));
            }
        },
        error: function(xhr){
            $('#saveBtn').prop('disabled', false).text('Save Changes');
            console.error('Save error', xhr.responseText);
            alert('Server error while saving. Check logs.');
        }
    });
}

function toggleEditableByStatus(){
    if (!orderStatus) return; // unknown
    const blocked = (orderStatus.toLowerCase() === 'completed' || orderStatus.toLowerCase() === 'cancelled'  || orderStatus.toLowerCase() === 'printed');
    if (blocked) {
        // disable form controls
        $('#editJobForm input, #editJobForm select, #addRowBtn, .remove-row, #saveBtn').prop('disabled', true);
        $('#addRowBtn').hide();
        $('.remove-row').hide();
        $('#saveBtn').text('Editing disabled (order ' + orderStatus + ')');
    }
}

function escapeHtml(text){
    if (!text) return '';
    return $('<div>').text(text).html();
}

$(document).on("click", "#changeCustomerBtn", function () {
    loadCustomers();  // same function like add page
    $("#customerModal").removeClass("hidden");
});



let allCustomers = [];

function loadCustomers() {
    $.ajax({
        url: 'job_order_functions.php',
        type: 'POST',
        data: { action: 'get_customers' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                allCustomers = response.customers;
                populateCustomerTable(allCustomers);
            }
        }
    });
}
function selectCustomer(id, name, phone) {
    $('#CustomerID').val(id);
    $('#CustomerName').val(name);
    $('#CellNo').val(phone);
 $('#currentCustomerName').text(name); // Update display box
    closeCustomerModal();
}
function populateCustomerTable(customers) {
    const tbody = $('#customerTableBody');
    tbody.empty();

    customers.forEach(cust => {
          const maskedPhone = maskPhone(cust.Phone);
        const row = `
            <tr>
                <td class="px-4 py-2 text-sm">${cust.CustomerName}</td>
                <td class="px-4 py-2 text-sm">${maskedPhone}</td>
                <td class="px-4 py-2 text-center">
                    <button type="button" class="bg-blue-600 text-white px-2 py-1 rounded text-xs"
                        onclick="selectCustomer('${cust.CustomerID}', '${cust.CustomerName.replace(/'/g,"\\'")}', '${cust.Phone.replace(/'/g,"\\'")}')">
                        Select
                    </button>
                </td>
            </tr>`;
        tbody.append(row);
    });
}
function closeCustomerModal() {
    $("#customerModal").addClass("hidden");
}
function maskPhone(phone) {
    if (!phone) return '';
    phone = phone.toString().replace(/\s+/g, '');

    if (phone.length <= 4) return phone;

    // Show first 4 digits → mask the remaining
    return phone.slice(0, 4) + '*'.repeat(phone.length - 4);
}

</script>

</body>
</html>

 
