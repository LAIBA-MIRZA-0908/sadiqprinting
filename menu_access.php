<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>

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
                echo "<option value='{$u['id']}'>{$u['full_name']}</option>";
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let selectedUser = null;

// Load menus when user selected
$("#userSelect").change(function () {
    selectedUser = $(this).val();
    loadMenus();
});

// Load menus + user access
function loadMenus() {
    if (!selectedUser) return;

    $.post("menu_access_functions.php", { action: "getMenus", user_id: selectedUser }, function (res) {
        let html = "";
        res.menus.forEach(m => {
            let checked = m.allowed ? "checked" : "";
            html += `
                <tr>
                    <td class="p-3 border">${m.menu_name}</td>
                    <td class="p-3 border text-center">
                        <input type="checkbox" class="menuCheck" data-id="${m.id}" ${checked}>
                    </td>
                </tr>
            `;
        });

        $("#menuTable").html(html);
    }, "json");
}

// Save menu access
$("#saveAccess").click(function () {
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
        alert(res.message);
    }, "json");
});
</script>
