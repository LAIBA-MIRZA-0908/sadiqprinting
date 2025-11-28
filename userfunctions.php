<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db_connection.php';
header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';

    // ------------------------------
    // GET ALL USERS
    // ------------------------------
    if ($action === 'getUsers') {
        $res = $conn->query("SELECT id, email, full_name, last_login, created_at FROM users ORDER BY id DESC");
        echo json_encode($res ? $res->fetch_all(MYSQLI_ASSOC) : []);
    }

    // ------------------------------
    // GET SINGLE USER
    // ------------------------------
    elseif ($action === 'getUser') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("SELECT id, email, full_name, last_login, created_at FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows) {
            echo json_encode(['status' => 'success', 'user' => $res->fetch_assoc()]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    }

    // ------------------------------
    // SAVE USER (ADD/UPDATE)
    // ------------------------------
    elseif ($action === 'saveUser') {

        $id = intval($_POST['UserID'] ?? 0);
        $email = trim($_POST['Email'] ?? '');
        $password = trim($_POST['Password'] ?? '');
        $full_name = trim($_POST['FullName'] ?? '');

        if (empty($email) || empty($full_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Email and Full Name required']);
            exit;
        }

        // ADD USER
        if ($id === 0) {

            if (empty($password)) {
                echo json_encode(['status' => 'error', 'message' => 'Password required for new user']);
                exit;
            }

            // ❌ No hashing — saving simple password
            $stmt = $conn->prepare("INSERT INTO users (email, password, full_name, created_at, role_id) VALUES (?, ?, ?, NOW(), '3')");
            $stmt->bind_param("sss", $email, $password, $full_name);
            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'User added successfully']);
        }

        // UPDATE USER
        else {

            if (!empty($password)) {
                $stmt = $conn->prepare("UPDATE users SET email=?, password=?, full_name=? WHERE id=?");
                $stmt->bind_param("sssi", $email, $password, $full_name, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET email=?, full_name=? WHERE id=?");
                $stmt->bind_param("ssi", $email, $full_name, $id);
            }

            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
        }
    }

    // ------------------------------
    // DELETE USER
    // ------------------------------
    elseif ($action === 'deleteUser') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
    }

    // ------------------------------
    // INVALID ACTION
    // ------------------------------
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: '.$e->getMessage()]);
}
