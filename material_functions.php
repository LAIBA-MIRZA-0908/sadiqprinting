<?php
// material_functions.php
// Disable all error output to prevent HTML in JSON response
error_reporting(0);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set JSON header immediately
header('Content-Type: application/json');

// Function to send JSON response and exit
function sendJsonResponse($success, $message = '', $data = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    exit;
}

// Database connection
try {
    include 'db_connection.php';
    
    if (!$conn) {
        sendJsonResponse(false, 'Database connection failed');
    }
} catch (Exception $e) {
    sendJsonResponse(false, 'Database connection error: ' . $e->getMessage());
}

// Get the action from the request
 $action = isset($_POST['action']) ? $_POST['action'] : '';

if (empty($action)) {
    sendJsonResponse(false, 'No action specified');
}

// Process based on action
switch ($action) {
    case 'get_materials':
        getMaterials();
        break;
    case 'create_material':
        createMaterial();
        break;
    case 'update_material':
        updateMaterial();
        break;
    case 'delete_material':
        deleteMaterial();
        break;
    default:
        sendJsonResponse(false, 'Invalid action');
        break;
}

// Close connection
 $conn->close();

// Function to get materials
function getMaterials() {
    global $conn;
    
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    
    $offset = ($page - 1) * $limit;
    
    // Check if created_at column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM tblmaterials LIKE 'created_at'");
    $hasCreatedAt = ($checkColumn && $checkColumn->num_rows > 0);
    
    // Build the query
    if ($hasCreatedAt) {
        $query = "SELECT id, name, created_at FROM tblmaterials";
    } else {
        $query = "SELECT id, name FROM tblmaterials";
    }
    
    $countQuery = "SELECT COUNT(*) as total FROM tblmaterials";
    
    if (!empty($search)) {
        $searchTerm = "%" . $conn->real_escape_string($search) . "%";
        $query .= " WHERE name LIKE '$searchTerm'";
        $countQuery .= " WHERE name LIKE '$searchTerm'";
    }
    
    $query .= " ORDER BY name ASC LIMIT $limit OFFSET $offset";
    
    // Get total count
    $countResult = $conn->query($countQuery);
    if (!$countResult) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $total = 0;
    if ($row = $countResult->fetch_assoc()) {
        $total = $row['total'];
    }
    
    // Get materials
    $result = $conn->query($query);
    if (!$result) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $materials = [];
    while ($row = $result->fetch_assoc()) {
        // Add created_at with current date if it doesn't exist
        if (!$hasCreatedAt) {
            $row['created_at'] = date('Y-m-d H:i:s');
        }
        $materials[] = $row;
    }
    
    sendJsonResponse(true, 'Materials loaded successfully', [
        'materials' => $materials,
        'total' => $total
    ]);
}

// Function to create a new material
function createMaterial() {
    global $conn;
    
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    
    if (empty($name)) {
        sendJsonResponse(false, 'Material name is required');
    }
    
    // Check if material already exists
    $checkQuery = "SELECT id FROM tblmaterials WHERE name = ?";
    $stmt = $conn->prepare($checkQuery);
    
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendJsonResponse(false, 'Material with this name already exists');
    }
    
    // Insert new material
    $insertQuery = "INSERT INTO tblmaterials (name) VALUES (?)";
    $stmt = $conn->prepare($insertQuery);
    
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, 'Material created successfully', [
            'material_id' => $conn->insert_id
        ]);
    } else {
        sendJsonResponse(false, 'Failed to create material: ' . $stmt->error);
    }
}

// Function to update a material
function updateMaterial() {
    global $conn;
    
    $materialId = isset($_POST['material_id']) ? (int)$_POST['material_id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    
    if ($materialId <= 0) {
        sendJsonResponse(false, 'Invalid material ID');
    }
    
    if (empty($name)) {
        sendJsonResponse(false, 'Material name is required');
    }
    
    // Check if material exists
    $checkQuery = "SELECT id FROM tblmaterials WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $materialId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendJsonResponse(false, 'Material not found');
    }
    
    // Check if another material with the same name exists
    $checkNameQuery = "SELECT id FROM tblmaterials WHERE name = ? AND id != ?";
    $stmt = $conn->prepare($checkNameQuery);
    
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("si", $name, $materialId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendJsonResponse(false, 'Another material with this name already exists');
    }
    
    // Update material
    $updateQuery = "UPDATE tblmaterials SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("si", $name, $materialId);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, 'Material updated successfully');
    } else {
        sendJsonResponse(false, 'Failed to update material: ' . $stmt->error);
    }
}

// Function to delete a material
function deleteMaterial() {
    global $conn;
    
    $materialId = isset($_POST['material_id']) ? (int)$_POST['material_id'] : 0;
    
    if ($materialId <= 0) {
        sendJsonResponse(false, 'Invalid material ID');
    }
    
    // Check if material exists
    $checkQuery = "SELECT id FROM tblmaterials WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $materialId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendJsonResponse(false, 'Material not found');
    }
    
    // Delete material
    $deleteQuery = "DELETE FROM tblmaterials WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    
    if (!$stmt) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $materialId);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, 'Material deleted successfully');
    } else {
        sendJsonResponse(false, 'Failed to delete material: ' . $stmt->error);
    }
}
?>