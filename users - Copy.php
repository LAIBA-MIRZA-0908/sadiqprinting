<!-- users.php -->
<?php
error_reporting(0);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
// users.php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Users Management</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    /* small visual tweak for table rows */
    .table-row-hover:hover { background: #f8fafc; }
  </style>
</head>
<body class="bg-gray-100">

<div class="container mx-auto px-4 py-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Users Management</h1>
      <p class="text-sm text-gray-600">Add / edit system users</p>
    </div>
    <div>
      <button id="btnNewUser" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
        <i class="fas fa-user-plus mr-2"></i>New User
      </button>
    </div>
  </div>

  <!-- layout: left form (1/3) + right list (2/3) -->
  <div class="grid grid-cols-3 gap-6">
    <!-- Left: Form -->
    <div class="col-span-1 bg-white rounded-lg shadow p-5">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Add / Edit User</h2>

      <form id="userForm" class="space-y-3">
        <input type="hidden" id="UserID" name="UserID" value="">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
          <input id="FullName" name="FullName" type="text" class="w-full border rounded p-2" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
          <input id="Email" name="Email" type="text" class="w-full border rounded p-2" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input id="Password" name="Password" type="password" class="w-full border rounded p-2" placeholder="Leave blank when editing to keep existing">
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

    <!-- Right: Users table -->
    <div class="col-span-2 bg-white rounded-lg shadow p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Users List</h2>
        <input id="userSearch" type="text" placeholder="Search..." class="border rounded p-2 w-64" />
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-gray-200">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="p-2 border text-left w-16">ID</th>
              <th class="p-2 border text-left">Full Name</th>
              <th class="p-2 border text-left">Email</th>
              <th class="p-2 border text-left">Last Login</th>
              <th class="p-2 border text-left">Created At</th>
              <th class="p-2 border text-center w-36">Actions</th>
            </tr>
          </thead>
          <tbody id="usersTableBody" class="bg-white">
            <tr><td colspan="6" class="p-4 text-center text-gray-500">Loading users...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  // Load users on page ready
  loadUsers();

  // New user button resets form and scrolls to form
  $('#btnNewUser').click(function() {
    resetForm();
    $('html,body').animate({ scrollTop: $('#userForm').offset().top - 20 }, 300);
  });

  // Reset button
  $('#btnReset').click(function() {
    resetForm();
  });

  // Submit form (create/update)
  $('#userForm').on('submit', function(e) {
    e.preventDefault();
    $('#formStatus').text('Saving...');

    var formData = {
      action: 'saveUser',
      UserID: $('#UserID').val(),
      FullName: $('#FullName').val().trim(),
      Email: $('#Email').val().trim(),
      Password: $('#Password').val()
    };

    $.ajax({
      url: 'userfunctions.php',
      method: 'POST',
      data: formData,
      dataType: 'json'
    }).done(function(res) {
      if (res.status === 'success') {
        alert(res.message);
        resetForm();
        loadUsers();
      } else {
        alert('Error: ' + (res.message || 'Unknown error'));
      }
    }).fail(function(xhr, status, err) {
      console.error('Save error', status, err);
      alert('Network error while saving user.');
    }).always(function() {
      $('#formStatus').text('');
    });
  });

  // Search filter
  $('#userSearch').on('input', function() {
    const q = $(this).val().toLowerCase();
    $('#usersTableBody tr').each(function() {
      const txt = $(this).text().toLowerCase();
      $(this).toggle(txt.indexOf(q) > -1);
    });
  });

  // Delegate edit/delete buttons
  $(document).on('click', '.editUserBtn', function() {
    const id = $(this).data('id');
    // fetch single user data then fill form
    $.post('userfunctions.php', { action: 'getUser', id: id }, function(res) {
      if (res.status === 'success') {
        const u = res.user;
        $('#UserID').val(u.id);
        $('#FullName').val(u.full_name);
        $('#Email').val(u.email);
        $('#Password').val(''); // keep empty
        $('#formStatus').text('Editing user ID: ' + u.id);
        $('html,body').animate({ scrollTop: $('#userForm').offset().top - 20 }, 300);
      } else {
        alert(res.message || 'Unable to load user');
      }
    }, 'json').fail(function() {
      alert('Network error fetching user');
    });
  });

  $(document).on('click', '.deleteUserBtn', function() {
    const id = $(this).data('id');
    if (!confirm('Delete user ID ' + id + ' ? This action cannot be undone.')) return;

    $.post('userfunctions.php', { action: 'deleteUser', id: id }, function(res) {
      if (res.status === 'success') {
        alert(res.message);
        loadUsers();
      } else {
        alert(res.message || 'Delete failed');
      }
    }, 'json').fail(function() {
      alert('Network error deleting user');
    });
  });

  // helper functions
  function resetForm() {
    $('#UserID').val('');
    $('#FullName').val('');
    $('#Email').val('');
    $('#Password').val('');
    $('#formStatus').text('');
  }

  function loadUsers() {
    $('#usersTableBody').html('<tr><td colspan="6" class="p-4 text-center text-gray-500">Loading users...</td></tr>');
    $.post('userfunctions.php', { action: 'getUsers' }, function(res) {
      if (!Array.isArray(res)) {
        $('#usersTableBody').html('<tr><td colspan="6" class="p-4 text-center text-red-500">Failed to load users</td></tr>');
        return;
      }
      if (res.length === 0) {
        $('#usersTableBody').html('<tr><td colspan="6" class="p-4 text-center text-gray-500">No users found</td></tr>');
        return;
      }

      let rows = '';
      res.forEach(function(u) {
        rows += `
          <tr class="table-row-hover">
            <td class="p-2 border">${u.id}</td>
            <td class="p-2 border">${escapeHtml(u.full_name)}</td>
            <td class="p-2 border">${escapeHtml(u.email)}</td>
            <td class="p-2 border">${u.last_login ?? '-'}</td>
            <td class="p-2 border">${u.created_at}</td>
            <td class="p-2 border text-center">
              <button data-id="${u.id}" class="editUserBtn text-indigo-600 hover:text-indigo-800 mr-3" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button data-id="${u.id}" class="deleteUserBtn text-red-600 hover:text-red-800" title="Delete">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>`;
      });

      $('#usersTableBody').html(rows);
    }, 'json').fail(function() {
      $('#usersTableBody').html('<tr><td colspan="6" class="p-4 text-center text-red-500">Network error loading users</td></tr>');
    });
  }

  // small helper to avoid simple XSS
  function escapeHtml(text) {
    if (!text && text !== 0) return '';
    return $('<div>').text(text).html();
  }
});
</script>

</body>
</html> 