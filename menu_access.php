<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Optional small style to match Tailwind-ish look -->
<style>
.select2-container .select2-selection--single {
    height: 40px !important;
    padding: 6px 10px !important;
    border-radius: 0.375rem !important; /* rounded-md */
    border: 1px solid #d1d5db !important;
    background: #fff;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px !important;
}
.select2-selection__arrow {
    height: 40px !important;
    right: 8px;
}
.select2-container--open { z-index: 9999; }
</style>

<div class="container mx-auto px-6 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">User Menu Access Control</h1>

    <!-- Select User -->
    <div class="bg-white p-5 rounded shadow mb-6">
        <label class="block text-sm font-medium mb-2">Select User</label>
        <select id="userSelect" class="border p-2 rounded w-64">
            <option value="">-- Select User --</option>

            <?php
            $res = $conn->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
            while ($u = $res->fetch_assoc()) {
                $id = htmlspecialchars($u['id']);
                $name = htmlspecialchars($u['full_name']);
                echo "<option value='{$id}'>{$name}</option>";
            }
            ?>
        </select>
    </div>

    <!-- Menus Table -->
    <div class="bg-white p-5 rounded shadow">
        <h2 class="text-lg font-semibold mb-4">Main Menus</h2>

        <table class="min-w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 border text-left">Menu Name</th>
                    <th class="p-3 border text-center">Allow Access</th>
                </tr>
            </thead>
            <tbody id="menuTable"></tbody>
        </table>

        <button id="saveAccess" class="bg-blue-600 text-white px-5 py-2 rounded mt-4 hover:bg-blue-700">
            Save Access
        </button>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let selectedUser = null;

$(document).ready(function() {
    // Initialize Select2 on userSelect
    $('#userSelect').select2({
        placeholder: "-- Select User --",
        allowClear: true,
        width: 'resolve' // let CSS control width; will respect the element's width (w-64)
    });

    // When selection changes, trigger change event (works with select2)
    $('#userSelect').on('change', function() {
        selectedUser = $(this).val();
        loadMenus();
    });

    // Save access button handler
    $('#saveAccess').on('click', function() {
        if (!selectedUser) {
            alert('Please select a user first.');
            return;
        }

        let accessList = [];

        $(".menuCheck").each(function () {
            accessList.push({
                menu_id: $(this).data("id"),
                allowed: $(this).is(":checked") ? 1 : 0
            });
        });

        $.post("menu_access_functions.php", {
            action: "saveAccess",
            user_id: selectedUser,
            access: JSON.stringify(accessList)
        }, function (res) {
            if (res && res.message) {
                alert(res.message);
            } else {
                alert('Unexpected response from server.');
            }
        }, "json").fail(function() {
            alert('Server error while saving access.');
        });
    });
});

// Load menus + user access
function loadMenus() {
    if (!selectedUser) {
        $("#menuTable").html(''); 
        return;
    }

    $.post("menu_access_functions.php", { action: "getMenus", user_id: selectedUser }, function (res) {
        if (!res || !Array.isArray(res.menus)) {
            $("#menuTable").html('<tr><td colspan="2" class="p-3 border text-center text-red-500">Error loading menus.</td></tr>');
            return;
        }

        let html = "";
        res.menus.forEach(m => {
            let checked = m.allowed ? "checked" : "";
            // sanitize menu_name for output
            let menuName = $('<div/>').text(m.menu_name).html();
            html += `
                <tr>
                    <td class="p-3 border">${menuName}</td>
                    <td class="p-3 border text-center">
                        <input type="checkbox" class="menuCheck" data-id="${m.id}" ${checked}>
                    </td>
                </tr>
            `;
        });

        $("#menuTable").html(html);
    }, "json").fail(function() {
        $("#menuTable").html('<tr><td colspan="2" class="p-3 border text-center text-red-500">Server error while loading menus.</td></tr>');
    });
}
</script>
