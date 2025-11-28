<?php
// category_accounts_function.php
include 'db_connection.php'; // Include your database connection

header('Content-Type: application/json');

 $action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_accounts':
        getAccounts();
        break;
    case 'get_category_details':
        getCategoryDetails();
        break;
    case 'save_account_assignments':
        saveAccountAssignments();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getAccounts() {
    global $conn;
    
    $sql = "SELECT id, code, name, type FROM accounts ORDER BY code";
    $result = $conn->query($sql);
    
    $accounts = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'accounts' => $accounts]);
}

function getCategoryDetails() {
    global $conn;
    
    $categoryId = $_POST['category_id'] ?? 0;
    
    if (empty($categoryId)) {
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        return;
    }
    
    $sql = "SELECT id, name, stock_input_account_id, stock_output_account_id, expense_account_id, income_account_id 
            FROM categories WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $category = $result->fetch_assoc();
        echo json_encode(['success' => true, 'category' => $category]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Category not found']);
    }
}

function saveAccountAssignments() {
    global $conn;
    
    $categoryId = $_POST['category_id'] ?? 0;
    $stockInputAccountId = $_POST['stock_input_account_id'] ?? null;
    $stockOutputAccountId = $_POST['stock_output_account_id'] ?? null;
    $expenseAccountId = $_POST['expense_account_id'] ?? null;
    $incomeAccountId = $_POST['income_account_id'] ?? null;
    
    if (empty($categoryId)) {
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        return;
    }
    
    // Convert empty strings to null
    $stockInputAccountId = empty($stockInputAccountId) ? null : $stockInputAccountId;
    $stockOutputAccountId = empty($stockOutputAccountId) ? null : $stockOutputAccountId;
    $expenseAccountId = empty($expenseAccountId) ? null : $expenseAccountId;
    $incomeAccountId = empty($incomeAccountId) ? null : $incomeAccountId;
    
    $sql = "UPDATE categories SET 
            stock_input_account_id = ?, 
            stock_output_account_id = ?, 
            expense_account_id = ?, 
            income_account_id = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $stockInputAccountId, $stockOutputAccountId, $expenseAccountId, $incomeAccountId, $categoryId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Account assignments saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving account assignments: ' . $stmt->error]);
    }
}
?>