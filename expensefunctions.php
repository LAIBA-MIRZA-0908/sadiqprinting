<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db_connection.php';
header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';

    // 🧾 1. Fetch all expense categories
    if ($action === 'getCategories') {
        $res = $conn->query("SELECT category_id, category_name FROM expense_categories ORDER BY category_name ASC");
        echo json_encode($res ? $res->fetch_all(MYSQLI_ASSOC) : []);
    }

    // ➕ 2. Add new category
    elseif ($action === 'addCategory') {
        $category = trim($_POST['category_name'] ?? '');
        if ($category === '') {
            echo json_encode(['status' => 'error', 'message' => 'Category name required']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO expense_categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Category added successfully']);
    }

    // 💰 3. Fetch all expenses
    elseif ($action === 'getExpenses') {
        $query = "
            SELECT e.expense_id, c.category_name, e.amount, e.expense_date, e.description
            FROM expenses e
            JOIN expense_categories c ON e.category_id = c.category_id
            ORDER BY e.expense_date DESC
        ";
        $res = $conn->query($query);
        echo json_encode($res ? $res->fetch_all(MYSQLI_ASSOC) : []);
    }

    // 🧍 4. Fetch single expense (for editing)
    elseif ($action === 'getExpense') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("
            SELECT expense_id, category_id, amount, expense_date, description
            FROM expenses WHERE expense_id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows) {
            echo json_encode(['status' => 'success', 'expense' => $res->fetch_assoc()]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Expense not found']);
        }
    }

    // 💾 5. Save expense (insert/update)
    elseif ($action === 'saveExpense') {
        $id = intval($_POST['expense_id'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $date = trim($_POST['expense_date'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if ($category_id === 0 || $amount <= 0 || $date === '') {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
            exit;
        }

        if ($id === 0) {
            $stmt = $conn->prepare("INSERT INTO expenses (category_id, amount, expense_date, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $category_id, $amount, $date, $desc);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Expense added successfully']);
        } else {
            $stmt = $conn->prepare("UPDATE expenses SET category_id=?, amount=?, expense_date=?, description=? WHERE expense_id=?");
            $stmt->bind_param("idssi", $category_id, $amount, $date, $desc, $id);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Expense updated successfully']);
        }
    }

    // ❌ 6. Delete expense
    elseif ($action === 'deleteExpense') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM expenses WHERE expense_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Expense deleted successfully']);
    }

    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
