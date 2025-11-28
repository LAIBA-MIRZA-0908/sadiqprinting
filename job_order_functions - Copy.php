<?php
// job_order_functions.php
// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

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
    case 'create_job_order':
        createJobOrder();
        break;
    case 'get_job_orders':
        getJobOrders();
        break;
    case 'get_job_order_details':
        getJobOrderDetails();
        break;
    case 'delete_job_order':
        deleteJobOrder();
        break;
        case 'get_today_count':
    getTodayCount();
    break;
    default:
        sendJsonResponse(false, 'Invalid action: ' . $action);
        break;
}

// Close connection
 $conn->close();

// Function to get materials
function getMaterials() {
    global $conn;
    
    $query = "SELECT id, name FROM tblmaterials ORDER BY name ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        sendJsonResponse(false, 'Database error in materials query: ' . $conn->error);
    }
    
    $materials = [];
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    
    sendJsonResponse(true, 'Materials loaded successfully', [
        'materials' => $materials
    ]);
}

// Function to get job orders
function getJobOrders() {
    global $conn;
    
    $query = "SELECT JobOrderNo, OrderDate, DeliveryDate, CustomerName, CellNo, Designer, AdvancePayment 
              FROM job_orders 
              ORDER BY JobOrderNo DESC";
    $result = $conn->query($query);
    
    if (!$result) {
        sendJsonResponse(false, 'Database error in job orders query: ' . $conn->error);
    }
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    sendJsonResponse(true, 'Job orders loaded successfully', [
        'orders' => $orders
    ]);
}

// Function to get job order details
function getJobOrderDetails() {
    global $conn;
    
    $jobOrderNo = isset($_POST['jobOrderNo']) ? (int)$_POST['jobOrderNo'] : 0;
    
    if ($jobOrderNo <= 0) {
        sendJsonResponse(false, 'Invalid job order number');
    }
    
    // Get job order details
    $orderQuery = "SELECT JobOrderNo, OrderDate, DeliveryDate, DesignBy, JobFor, CustomerName, CellNo, Designer, AdvancePayment 
                  FROM job_orders 
                  WHERE JobOrderNo = ?";
    
    $stmt = $conn->prepare($orderQuery);
    if (!$stmt) {
        sendJsonResponse(false, 'Prepare failed for job order: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $jobOrderNo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendJsonResponse(false, 'Job order not found');
    }
    
    $order = $result->fetch_assoc();
    
    // Get job order items
    $detailsQuery = "SELECT SrNo, Detail, Media, Width, Height, Qty, Sqft, Ring, Pocket 
                    FROM job_order_details 
                    WHERE JobOrderNo = ? 
                    ORDER BY SrNo";
    
    $stmtDetails = $conn->prepare($detailsQuery);
    if (!$stmtDetails) {
        sendJsonResponse(false, 'Prepare failed for job order details: ' . $conn->error);
    }
    
    $stmtDetails->bind_param("i", $jobOrderNo);
    $stmtDetails->execute();
    $detailsResult = $stmtDetails->get_result();
    
    $details = [];
    while ($row = $detailsResult->fetch_assoc()) {
        $details[] = $row;
    }
    
    sendJsonResponse(true, 'Job order details loaded successfully', [
        'order' => $order,
        'details' => $details
    ]);
}

// Function to delete job order
function deleteJobOrder() {
    global $conn;
    
    $jobOrderNo = isset($_POST['jobOrderNo']) ? (int)$_POST['jobOrderNo'] : 0;
    
    if ($jobOrderNo <= 0) {
        sendJsonResponse(false, 'Invalid job order number');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete job order details first
        $deleteDetailsQuery = "DELETE FROM job_order_details WHERE JobOrderNo = ?";
        $stmtDetails = $conn->prepare($deleteDetailsQuery);
        if (!$stmtDetails) {
            throw new Exception('Prepare failed for deleting details: ' . $conn->error);
        }
        
        $stmtDetails->bind_param("i", $jobOrderNo);
        if (!$stmtDetails->execute()) {
            throw new Exception('Failed to delete job order details: ' . $stmtDetails->error);
        }
        
        // Delete job order
        $deleteOrderQuery = "DELETE FROM job_orders WHERE JobOrderNo = ?";
        $stmtOrder = $conn->prepare($deleteOrderQuery);
        if (!$stmtOrder) {
            throw new Exception('Prepare failed for deleting order: ' . $conn->error);
        }
        
        $stmtOrder->bind_param("i", $jobOrderNo);
        if (!$stmtOrder->execute()) {
            throw new Exception('Failed to delete job order: ' . $stmtOrder->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        sendJsonResponse(true, 'Job order deleted successfully');
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        sendJsonResponse(false, 'Error: ' . $e->getMessage());
    }
}

// Function to create job order
function createJobOrder() {
    global $conn;
    
    // Get job order data
    $OrderDate = isset($_POST['OrderDate']) ? $_POST['OrderDate'] : '';
    $DeliveryDate = isset($_POST['DeliveryDate']) ? $_POST['DeliveryDate'] : '';
    $DesignBy = isset($_POST['DesignBy']) ? $_POST['DesignBy'] : '';
    $JobFor = isset($_POST['JobFor']) ? $_POST['JobFor'] : '';
    $CustomerName = isset($_POST['CustomerName']) ? trim($_POST['CustomerName']) : '';
    $CellNo = isset($_POST['CellNo']) ? trim($_POST['CellNo']) : '';
    $Designer = isset($_POST['Designer']) ? trim($_POST['Designer']) : null;
    $AdvancePayment = isset($_POST['AdvancePayment']) ? floatval($_POST['AdvancePayment']) : 0.00;
    $details = isset($_POST['details']) ? $_POST['details'] : [];
    
    // Log received data for debugging
    error_log("Received data: " . print_r($_POST, true));
    
    // Validate required fields
    if (empty($OrderDate) || empty($DeliveryDate) || 
        empty($DesignBy) || empty($JobFor) || empty($CustomerName) || empty($CellNo)) {
        sendJsonResponse(false, 'Please fill in all required fields');
    }
    
    // No need to filter details anymore since JavaScript only sends valid rows
    error_log("Details received from client: " . count($details));
    
    if (empty($details)) {
        sendJsonResponse(false, 'Please add at least one complete detail row with all required fields');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert into job_orders table - REMOVED JobOrderNo from query since it's auto-increment
        $insertQuery = "INSERT INTO job_orders (OrderDate, DeliveryDate, DesignBy, JobFor, CustomerName, CellNo, Designer, AdvancePayment) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        error_log("Job Order Query: " . $insertQuery);
        error_log("Parameters: " . $OrderDate . ", " . $DeliveryDate . ", " . $DesignBy . ", " . $JobFor . ", " . $CustomerName . ", " . $CellNo . ", " . $Designer . ", " . $AdvancePayment);
        
        $stmt = $conn->prepare($insertQuery);
        if (!$stmt) {
            error_log("Prepare failed for job order: " . $conn->error);
            throw new Exception('Prepare failed for job order: ' . $conn->error);
        }
        
        // REMOVED JobOrderNo from bind_param - now only 8 parameters instead of 9
        $stmt->bind_param("sssssssd", $OrderDate, $DeliveryDate, $DesignBy, $JobFor, $CustomerName, $CellNo, $Designer, $AdvancePayment);
        
        if (!$stmt->execute()) {
            error_log("Execute failed for job order: " . $stmt->error);
            throw new Exception('Failed to insert job order: ' . $stmt->error);
        }
        
        // Get the auto-generated JobOrderNo
        $insertedJobOrderNo = $conn->insert_id;
        error_log("Inserted Job Order No: " . $insertedJobOrderNo);
        
        // Insert details (no need to filter since JavaScript already filtered)
        foreach ($details as $index => $detail) {
            $insertDetailQuery = "INSERT INTO job_order_details (JobOrderNo, SrNo, Detail, Media, Width, Height, Qty, Sqft, Ring, Pocket) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            error_log("Detail Query: " . $insertDetailQuery);
            error_log("Detail Parameters: " . $insertedJobOrderNo . ", " . ($index + 1) . ", " . $detail['Detail'] . ", " . $detail['Media'] . ", " . $detail['Width'] . ", " . $detail['Height'] . ", " . $detail['Qty'] . ", " . $detail['Sqft'] . ", " . $detail['Ring'] . ", " . $detail['Pocket']);
            
            $stmtDetail = $conn->prepare($insertDetailQuery);
            if (!$stmtDetail) {
                error_log("Prepare failed for job order detail: " . $conn->error);
                throw new Exception('Prepare failed for job order detail: ' . $conn->error);
            }
            
            // Convert all values to proper types before binding
            $jobOrderNo = (int)$insertedJobOrderNo;
            $srNo = (int)($index + 1);
            $detailText = $detail['Detail'];
            $mediaText = $detail['Media'];
            $width = (float)$detail['Width'];
            $height = (float)$detail['Height'];
            $qty = (int)$detail['Qty'];
            $sqft = (float)$detail['Sqft'];
            $ring = (int)$detail['Ring'];
            $pocket = (int)$detail['Pocket'];
            
            error_log("Converted Parameters: " . $jobOrderNo . ", " . $srNo . ", " . $detailText . ", " . $mediaText . ", " . $width . ", " . $height . ", " . $qty . ", " . $sqft . ", " . $ring . ", " . $pocket);
            
            $stmtDetail->bind_param("iisssddiii", $jobOrderNo, $srNo, $detailText, $mediaText, 
                                   $width, $height, $qty, $sqft, $ring, $pocket);
            
            if (!$stmtDetail->execute()) {
                error_log("Execute failed for job order detail: " . $stmtDetail->error);
                throw new Exception('Failed to insert job order detail: ' . $stmtDetail->error);
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        sendJsonResponse(true, 'Job order created successfully', [
            'JobOrderNo' => $insertedJobOrderNo,
            'detailsSaved' => count($details)
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        error_log("Transaction failed: " . $e->getMessage());
        sendJsonResponse(false, 'Error: ' . $e->getMessage());
    }
}
function getTodayCount() {
    global $conn;

    // Count orders where OrderDate is today's date
    $query = "SELECT COUNT(*) AS cnt FROM job_orders WHERE DATE(OrderDate) = CURDATE()";
    $result = $conn->query($query);

    if (!$result) {
        sendJsonResponse(false, 'Database error in count query: ' . $conn->error);
    }

    $row = $result->fetch_assoc();
    $count = isset($row['cnt']) ? (int)$row['cnt'] : 0;

    // return the count (number of existing orders today)
    sendJsonResponse(true, 'Count fetched', ['count' => $count]);
}

?>