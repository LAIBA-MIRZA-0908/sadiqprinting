<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Suppliers Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto mt-6 grid grid-cols-3 gap-6">
    <!-- Left Form -->
    <div class="col-span-1 bg-white shadow-md rounded-lg p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Add / Edit Supplier</h2>
        <form id="supplierForm" class="space-y-3">
            <input type="hidden" name="id" id="id">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name</label>
                <input type="text" name="name" id="name" class="w-full border rounded-md p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" class="w-full border rounded-md p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="phone" id="phone" class="w-full border rounded-md p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Type</label>
                <input type="text" name="supplier_type" id="supplier_type" class="w-full border rounded-md p-2" placeholder="e.g. Local / International">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Terms</label>
                <input type="text" name="payment_terms" id="payment_terms" class="w-full border rounded-md p-2" placeholder="e.g. 30 days">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                <input type="text" name="bank_name" id="bank_name" class="w-full border rounded-md p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account No</label>
                <input type="text" name="bank_account_no" id="bank_account_no" class="w-full border rounded-md p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">IBAN</label>
                <input type="text" name="bank_iban" id="bank_iban" class="w-full border rounded-md p-2">
            </div>

            <div class="flex justify-between pt-3">
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700">Save</button>
                <button type="button" id="resetForm" class="bg-gray-500 text-white px-5 py-2 rounded-md hover:bg-gray-600">Clear</button>
            </div>
        </form>
    </div>

    <!-- Right Table -->
    <div class="col-span-2 bg-white shadow-md rounded-lg p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-800">Supplier List</h2>
            <input id="searchSuppliers" type="text" placeholder="Search..." class="border rounded p-2 text-sm w-64">
        </div>

        <div class="overflow-x-auto">
        <table class="w-full text-sm border border-gray-300">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-2 border w-12">#</th>
                    <th class="p-2 border text-left">Name</th>
                    <th class="p-2 border w-40">Type</th>
                    <th class="p-2 border w-32">Phone</th>
                    <th class="p-2 border w-48">Email</th>
                    <th class="p-2 border w-32">Account ID</th>
                    <th class="p-2 border w-44">Actions</th>
                </tr>
            </thead>
            <tbody id="supplierTable">
                <tr><td colspan="7" class="text-center text-gray-500 p-3">Loading...</td></tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
function loadSuppliers(query = '') {
    $.ajax({
        url: 'supplierfunctions.php',
        method: 'POST',
        data: { action: 'getSuppliers', q: query },
        success: function (res) {
            $('#supplierTable').html(res);
        }
    });
}

// Save Supplier
$('#supplierForm').on('submit', function(e) {
    e.preventDefault();
    const supplierData = {
        id: $('#id').val(),
        name: $('#name').val(),
        email: $('#email').val(),
        phone: $('#phone').val(),
        supplier_type: $('#supplier_type').val(),
        payment_terms: $('#payment_terms').val(),
        bank_name: $('#bank_name').val(),
        bank_account_no: $('#bank_account_no').val(),
        bank_iban: $('#bank_iban').val()
    };

    $.ajax({
        url: 'supplierfunctions.php',
        method: 'POST',
        data: { action: 'saveSupplier', supplierData: supplierData },
        dataType: 'json',
        success: function(res) {
            alert(res.message);
            if (res.status === 'success') {
                $('#supplierForm')[0].reset();
                $('#id').val('');
                loadSuppliers();
            }
        }
    });
});

// Edit
$(document).on('click', '.editBtn', function() {
    const data = $(this).data();
    $('#id').val(data.id);
    $('#name').val(data.name);
    $('#email').val(data.email);
    $('#phone').val(data.phone);
    $('#supplier_type').val(data.type);
    $('#payment_terms').val(data.terms);
    $('#bank_name').val(data.bank);
    $('#bank_account_no').val(data.account);
    $('#bank_iban').val(data.iban);
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Delete
$(document).on('click', '.deleteBtn', function() {
    if (!confirm('Are you sure you want to delete this supplier?')) return;
    const id = $(this).data('id');
    $.ajax({
        url: 'supplierfunctions.php',
        method: 'POST',
        data: { action: 'deleteSupplier', id: id },
        dataType: 'json',
        success: function(res) {
            alert(res.message);
            if (res.status === 'success') loadSuppliers();
        }
    });
});

// Search
$('#searchSuppliers').on('input', function() {
    loadSuppliers($(this).val());
});

// Reset
$('#resetForm').on('click', function() {
    $('#supplierForm')[0].reset();
    $('#id').val('');
});

// initial load
$(document).ready(function() { loadSuppliers(); });
</script>

</body>
</html>
