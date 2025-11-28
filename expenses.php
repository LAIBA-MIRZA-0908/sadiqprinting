<?php
error_reporting(0);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Expenses Management</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    .table-row-hover:hover { background: #f8fafc; }
  </style>
</head>

<body class="bg-gray-100">

<div class="container mx-auto px-4 py-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Expenses Management</h1>
      <p class="text-sm text-gray-600">Add / edit expenses and categories</p>
    </div>
    <div>
      <button id="btnNewExpense" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>New Expense
      </button>
    </div>
  </div>

  <!-- layout -->
  <div class="grid grid-cols-3 gap-6">
    <!-- Left: Expense Form -->
    <div class="col-span-1 bg-white rounded-lg shadow p-5">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Add / Edit Expense</h2>

      <form id="expenseForm" class="space-y-3">
        <input type="hidden" id="expense_id" name="expense_id" value="">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select id="category_id" name="category_id" class="w-full border rounded p-2" required></select>
          <small class="text-gray-500 text-xs">Add new categories below if needed</small>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Amount (Rs)</label>
          <input id="amount" name="amount" type="number" step="0.01" class="w-full border rounded p-2" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
          <input id="expense_date" name="expense_date" type="date" class="w-full border rounded p-2" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea id="description" name="description" class="w-full border rounded p-2" rows="2"></textarea>
        </div>

        <div class="flex items-center justify-between pt-3">
          <div class="flex space-x-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              <i class="fas fa-save mr-2"></i>Save
            </button>
            <button type="button" id="btnReset" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
              Clear
            </button>
          </div>
          <div>
            <span id="formStatus" class="text-sm text-gray-500"></span>
          </div>
        </div>
      </form>

      <hr class="my-4">

      <!-- Category Add -->
      <h3 class="text-md font-semibold text-gray-700 mb-2">Add Category</h3>
      <form id="categoryForm" class="flex space-x-2">
        <input type="text" id="newCategory" placeholder="Category name" class="border rounded p-2 flex-grow">
        <button type="submit" class="bg-green-600 text-white px-3 rounded hover:bg-green-700">
          Add
        </button>
      </form>
    </div>

    <!-- Right: Expenses List -->
    <div class="col-span-2 bg-white rounded-lg shadow p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Expenses List</h2>
        <input id="expenseSearch" type="text" placeholder="Search..." class="border rounded p-2 w-64" />
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-gray-200">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="p-2 border text-left">ID</th>
              <th class="p-2 border text-left">Category</th>
              <th class="p-2 border text-right">Amount (Rs)</th>
              <th class="p-2 border text-left">Date</th>
              <th class="p-2 border text-left">Description</th>
              <th class="p-2 border text-center w-36">Actions</th>
            </tr>
          </thead>
          <tbody id="expensesTableBody" class="bg-white">
            <tr><td colspan="6" class="p-4 text-center text-gray-500">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  loadExpenses();
  loadCategories();

  // New expense button
  $('#btnNewExpense').click(function() {
    resetForm();
    $('html,body').animate({ scrollTop: $('#expenseForm').offset().top - 20 }, 300);
  });

  // Reset form
  $('#btnReset').click(function() {
    resetForm();
  });

  // Save expense
  $('#expenseForm').on('submit', function(e) {
    e.preventDefault();
    $('#formStatus').text('Saving...');

    const formData = {
      action: 'saveExpense',
      expense_id: $('#expense_id').val(),
      category_id: $('#category_id').val(),
      amount: $('#amount').val(),
      expense_date: $('#expense_date').val(),
      description: $('#description').val()
    };

    $.post('expensefunctions.php', formData, function(res) {
      if (res.status === 'success') {
        alert(res.message);
        resetForm();
        loadExpenses();
      } else {
        alert(res.message || 'Error saving expense');
      }
    }, 'json').always(function() {
      $('#formStatus').text('');
    });
  });

  // Add new category
  $('#categoryForm').submit(function(e) {
    e.preventDefault();
    const name = $('#newCategory').val().trim();
    if (!name) return alert('Enter category name');
    $.post('expensefunctions.php', { action: 'addCategory', category_name: name }, function(res) {
      if (res.status === 'success') {
        alert('Category added');
        $('#newCategory').val('');
        loadCategories();
      } else alert(res.message);
    }, 'json');
  });

  // Search
  $('#expenseSearch').on('input', function() {
    const q = $(this).val().toLowerCase();
    $('#expensesTableBody tr').each(function() {
      const txt = $(this).text().toLowerCase();
      $(this).toggle(txt.indexOf(q) > -1);
    });
  });

  // Edit expense
  $(document).on('click', '.editExpenseBtn', function() {
    const id = $(this).data('id');
    $.post('expensefunctions.php', { action: 'getExpense', id }, function(res) {
      if (res.status === 'success') {
        const e = res.expense;
        $('#expense_id').val(e.expense_id);
        $('#category_id').val(e.category_id);
        $('#amount').val(e.amount);
        $('#expense_date').val(e.expense_date);
        $('#description').val(e.description);
        $('#formStatus').text('Editing Expense ID: ' + e.expense_id);
      } else alert(res.message);
    }, 'json');
  });

  // Delete expense
  $(document).on('click', '.deleteExpenseBtn', function() {
    const id = $(this).data('id');
    if (!confirm('Delete expense ID ' + id + '?')) return;
    $.post('expensefunctions.php', { action: 'deleteExpense', id }, function(res) {
      if (res.status === 'success') {
        alert(res.message);
        loadExpenses();
      } else alert(res.message);
    }, 'json');
  });

  // Helpers
  function resetForm() {
    $('#expense_id').val('');
    $('#category_id').val('');
    $('#amount').val('');
    $('#expense_date').val('');
    $('#description').val('');
    $('#formStatus').text('');
  }

  function loadExpenses() {
    $('#expensesTableBody').html('<tr><td colspan="6" class="p-4 text-center text-gray-500">Loading...</td></tr>');
    $.post('expensefunctions.php', { action: 'getExpenses' }, function(res) {
      if (!Array.isArray(res)) {
        $('#expensesTableBody').html('<tr><td colspan="6" class="p-4 text-center text-red-500">Failed to load</td></tr>');
        return;
      }

      if (res.length === 0) {
        $('#expensesTableBody').html('<tr><td colspan="6" class="p-4 text-center text-gray-500">No expenses found</td></tr>');
        return;
      }

      let rows = '';
      res.forEach(function(e) {
        rows += `
          <tr class="table-row-hover">
            <td class="p-2 border">${e.expense_id}</td>
            <td class="p-2 border">${e.category_name}</td>
            <td class="p-2 border text-right">Rs. ${parseFloat(e.amount).toFixed(2)}</td>
            <td class="p-2 border">${e.expense_date}</td>
            <td class="p-2 border">${e.description || '-'}</td>
            <td class="p-2 border text-center">
              <button data-id="${e.expense_id}" class="editExpenseBtn text-indigo-600 hover:text-indigo-800 mr-3" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button data-id="${e.expense_id}" class="deleteExpenseBtn text-red-600 hover:text-red-800" title="Delete">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>`;
      });
      $('#expensesTableBody').html(rows);
    }, 'json');
  }

  function loadCategories() {
    $.post('expensefunctions.php', { action: 'getCategories' }, function(res) {
      if (!Array.isArray(res)) return;
      let options = '<option value="">Select Category</option>';
      res.forEach(c => options += `<option value="${c.category_id}">${c.category_name}</option>`);
      $('#category_id').html(options);
    }, 'json');
  }
});
</script>

</body>
</html>
