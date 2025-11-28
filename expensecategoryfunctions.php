<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db_connection.php';
header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';

    // 1. Fetch all categories
    if ($action === 'getCategories') {
        $res = $conn->query("SELECT category_id, category_name FROM expense_categories ORDER BY category_name ASC");
        echo json_encode($res ? $res->fetch_all(MYSQLI_ASSOC) : []);
    }

    // 2. Fetch single category
    elseif ($action === 'getCategory') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("SELECT category_id, category_name FROM expense_categories WHERE category_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows) {
            echo json_encode(['status'=>'success','category'=>$res->fetch_assoc()]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Category not found']);
        }
    }

    // 3. Save category (add or update)
    elseif ($action === 'saveCategory') {
        $id = intval($_POST['category_id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');
        if ($name === '') {
            echo json_encode(['status'=>'error','message'=>'Category name required']);
            exit;
        }

        if ($id === 0) {
            $stmt = $conn->prepare("INSERT INTO expense_categories (category_name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            echo json_encode(['status'=>'success','message'=>'Category added successfully']);
        } else {
            $stmt = $conn->prepare("UPDATE expense_categories SET category_name=? WHERE category_id=?");
            $stmt->bind_param("si", $name, $id);
            $stmt->execute();
            echo json_encode(['status'=>'success','message'=>'Category updated successfully']);
        }
    }

    // 4. Delete category
    elseif ($action === 'deleteCategory') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM expense_categories WHERE category_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['status'=>'success','message'=>'Category deleted successfully']);
    }

    else {
        echo json_encode(['status'=>'error','message'=>'Invalid action']);
    }

} catch (Throwable $e) {
    echo json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()]);
}
