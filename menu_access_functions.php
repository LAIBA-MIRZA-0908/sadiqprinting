<?php
include 'db_connection.php';
header("Content-Type: application/json");

$action = $_POST['action'] ?? '';

if ($action === 'getMenus') {
    $user_id = intval($_POST['user_id']);

    $menus = $conn->query("SELECT * FROM menus")->fetch_all(MYSQLI_ASSOC);

    $access = $conn->query("SELECT menu_id FROM user_menu_access WHERE user_id=$user_id")
                   ->fetch_all(MYSQLI_ASSOC);

    $allowedMenus = array_column($access, 'menu_id');

    foreach ($menus as &$m) {
        $m['allowed'] = in_array($m['id'], $allowedMenus);
    }

    echo json_encode(['menus' => $menus]);
}

elseif ($action === 'saveAccess') {

    $user_id = intval($_POST['user_id']);
    $access = json_decode($_POST['access'], true);

    // Remove old access
    $conn->query("DELETE FROM user_menu_access WHERE user_id=$user_id");

    // Insert new access
    foreach ($access as $a) {
        if ($a['allowed'] == 1) {
            $menu_id = intval($a['menu_id']);
            $conn->query("INSERT INTO user_menu_access (user_id, menu_id) VALUES ($user_id, $menu_id)");
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Access updated successfully']);
}

else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
