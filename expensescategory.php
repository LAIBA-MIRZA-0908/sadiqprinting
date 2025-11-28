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
  <title>Expense Categories</title>
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
      <h1 class="text-2xl font-bold text-gray-800">Expense Categories</h1>
      <p class="text-sm text-gray-600">Add / edit / delete expense categories</p>
    </div>
    <div>
      <button id="btnNewCategory" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>New Category
      </button>
    </div>
  </div>

  <!-- Category Form -->
    <div class="col-span-1 bg-white rounded-lg shadow p-5">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Add / Edit Category</h2>

    <form id="categoryForm" class="space-y-3">
      <input type="hidden" id="category_id" name="category_id" value="">

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
        <input id="category_name" name="category_name" type="text" class="w-full border rounded p-2" required>
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
  </div>

  <!-- Categories List -->
  <div class="col-span-2 bg-white rounded-lg shadow p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Categories List</h2>
      <input id="categorySearch" type="text" placeholder="Search..." class="border rounded p-2 w-64" />
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border border-gray-200">
        <thead class="bg-gray-50 text-gray-700">
          <tr>
            <th class="p-2 border text-left">ID</th>
            <th class="p-2 border text-left">Category Name</th>
            <th class="p-2 border text-center w-36">Actions</th>
          </tr>
        </thead>
        <tbody id="categoriesTableBody" class="bg-white">
          <tr><td colspan="3" class="p-4 text-center text-gray-500">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(function() {
  loadCategories();

  // New category button
  $('#btnNewCategory').click(function() {
    resetForm();
    $('html,body').animate({ scrollTop: $('#categoryForm').offset().top - 20 }, 300);
  });

  // Reset form
  $('#btnReset').click(function() {
    resetForm();
  });

  // Save category
  $('#categoryForm').on('submit', function(e) {
    e.preventDefault();
    $('#formStatus').text('Saving...');

    const formData = {
      action: 'saveCategory',
      category_id: $('#category_id').val(),
      category_name: $('#category_name').val()
    };

    $.post('expensecategoryfunctions.php', formData, function(res) {
      if (res.status === 'success') {
        alert(res.message);
        resetForm();
        loadCategories();
      } else {
        alert(res.message || 'Error saving category');
      }
    }, 'json').always(function() {
      $('#formStatus').text('');
    });
  });

  // Edit category
  $(document).on('click', '.editCategoryBtn', function() {
    const id = $(this).data('id');
    $.post('expensecategoryfunctions.php', { action: 'getCategory', id }, function(res) {
      if (res.status === 'success') {
        $('#category_id').val(res.category.category_id);
        $('#category_name').val(res.category.category_name);
        $('#formStatus').text('Editing Category ID: ' + res.category.category_id);
      } else alert(res.message);
    }, 'json');
  });

  // Delete category
  $(document).on('click', '.deleteCategoryBtn', function() {
    const id = $(this).data('id');
    if (!confirm('Delete category ID ' + id + '?')) return;
    $.post('expensecategoryfunctions.php', { action: 'deleteCategory', id }, function(res) {
      if (res.status === 'success') {
        alert(res.message);
        loadCategories();
      } else alert(res.message);
    }, 'json');
  });

  // Search categories
  $('#categorySearch').on('input', function() {
    const q = $(this).val().toLowerCase();
    $('#categoriesTableBody tr').each(function() {
      const txt = $(this).text().toLowerCase();
      $(this).toggle(txt.indexOf(q) > -1);
    });
  });

  // Helpers
  function resetForm() {
    $('#category_id').val('');
    $('#category_name').val('');
    $('#formStatus').text('');
  }

  function loadCategories() {
    $('#categoriesTableBody').html('<tr><td colspan="3" class="p-4 text-center text-gray-500">Loading...</td></tr>');
    $.post('expensecategoryfunctions.php', { action: 'getCategories' }, function(res) {
      if (!Array.isArray(res)) {
        $('#categoriesTableBody').html('<tr><td colspan="3" class="p-4 text-center text-red-500">Failed to load</td></tr>');
        return;
      }

      if (res.length === 0) {
        $('#categoriesTableBody').html('<tr><td colspan="3" class="p-4 text-center text-gray-500">No categories found</td></tr>');
        return;
      }

      let rows = '';
      res.forEach(function(c) {
        rows += `
          <tr class="table-row-hover">
            <td class="p-2 border">${c.category_id}</td>
            <td class="p-2 border">${c.category_name}</td>
            <td class="p-2 border text-center">
              <button data-id="${c.category_id}" class="editCategoryBtn text-indigo-600 hover:text-indigo-800 mr-3" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button data-id="${c.category_id}" class="deleteCategoryBtn text-red-600 hover:text-red-800" title="Delete">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>`;
      });
      $('#categoriesTableBody').html(rows);
    }, 'json');
  }
});
</script>

</body>
</html>
