<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto mt-6 grid grid-cols-3 gap-6">
    <!-- Left Form -->
    <div class="col-span-1 bg-white shadow-md rounded-lg p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Add / Edit Customer</h2>
        <form id="customerForm" class="space-y-3">
            <input type="hidden" name="CustomerID" id="CustomerID">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                <input type="text" name="CustomerName" id="CustomerName" class="w-full border rounded-md p-2" required>
            </div>

            <div style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                <input type="text" name="ContactPerson" id="ContactPerson" class="w-full border rounded-md p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="Phone" id="Phone" class="w-full border rounded-md p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="text" name="Email" id="Email" class="w-full border rounded-md p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="Address" id="Address" class="w-full border rounded-md p-2"></textarea>
            </div>

            <div class="flex justify-between pt-3">
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700">Save</button>
                <button type="reset" id="resetForm" class="bg-gray-500 text-white px-5 py-2 rounded-md hover:bg-gray-600">Clear</button>
            </div>
        </form>
    </div>

    <!-- Right Table -->
    <div class="col-span-2 bg-white shadow-md rounded-lg p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Customer List</h2>
        <table class="w-full text-sm border border-gray-300">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-2 border">#</th>
                    <th class="p-2 border text-left">Customer Name</th>
                    <th class="p-2 border">Phone</th>
                    <th class="p-2 border">Email</th>
                    <th class="p-2 border">Account ID</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody id="customerTable">
                <tr><td colspan="6" class="text-center text-gray-500 p-3">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Load customer list
function loadCustomers() {
    $.ajax({
        url: 'customerfunctions.php',
        method: 'POST',
        data: { action: 'getCustomers' },
        success: function (res) {
            $('#customerTable').html(res);
        }
    });
}

// Save Customer
$('#customerForm').on('submit', function(e) {
    e.preventDefault();
    let formData = $(this).serializeArray();
    let customerData = {};
    formData.forEach(field => customerData[field.name] = field.value);

    $.ajax({
        url: 'customerfunctions.php',
        method: 'POST',
        data: { action: 'saveCustomer', customerData: customerData },
        dataType: 'json',
        success: function(res) {
            alert(res.message);
            if (res.status === 'success') {
                $('#customerForm')[0].reset();
                $('#CustomerID').val('');
                loadCustomers();
            }
        }
    });
});

// Edit Customer
$(document).on('click', '.editBtn', function() {
    let data = $(this).data();
    $('#CustomerID').val(data.id);
    $('#CustomerName').val(data.name);
    $('#ContactPerson').val(data.contact);
    $('#Phone').val(data.phone);
    $('#Email').val(data.email);
    $('#Address').val(data.address);
});

// Delete Customer
$(document).on('click', '.deleteBtn', function() {
    if (!confirm('Are you sure you want to delete this customer?')) return;

    let id = $(this).data('id');
    $.ajax({
        url: 'customerfunctions.php',
        method: 'POST',
        data: { action: 'deleteCustomer', CustomerID: id },
        dataType: 'json',
        success: function(res) {
            alert(res.message);
            if (res.status === 'success') loadCustomers();
        }
    });
});

// Reset Form
$('#resetForm').click(function() {
    $('#CustomerID').val('');
});

// Load on page ready
$(document).ready(() => loadCustomers());
</script>

</body>
</html>
