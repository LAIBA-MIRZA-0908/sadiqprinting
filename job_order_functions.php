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
    case 'get_customers':
    getCustomers();
    break;
    case 'create_customer':
    createCustomer();
    break;
    case 'cancel_job_order':
        cancelorder();
        break;
    case 'search_customers':
    searchCustomers();
    break;
    case 'update_job_status':
    updateJobStatus();
    break;

    case 'mark_as_printed':
    markAsPrinted();
    break;
    case 'update_job_order':
      updateJobOrder();
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
    
   $query = "
    SELECT 
        j.JobOrderNo,
        j.OrderDate,
        j.DeliveryDate,
        j.CustomerName,
        j.CellNo,
        j.Designer,
        j.AdvancePayment,
        j.status ,
        i.InvoiceNo
    FROM job_orders j
    LEFT JOIN tblinvoices i 
        ON j.JobOrderNo = i.PONo
    ORDER BY j.JobOrderNo DESC
";

    $result = $conn->query($query);
    
    if (!$result) {
        sendJsonResponse(false, 'Database error in job orders query: ' . $conn->error);
    }
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
     $orders[] = [
    "JobOrderNo"     => $row["JobOrderNo"],
    "OrderDate"      => $row["OrderDate"],
    "DeliveryDate"   => $row["DeliveryDate"],
    "CustomerName"   => $row["CustomerName"],
    "CellNo"         => $row["CellNo"],
    "Designer"       => $row["Designer"],
    "AdvancePayment" => $row["AdvancePayment"],
    "status"         => $row["status"],
    "PONo"           => $row["JobOrderNo"],         // Important
    "InvoiceNo"      => $row["InvoiceNo"]     // NEW
];
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
    $DesignerID = isset($_POST['DesignerID']) ? trim($_POST['DesignerID']) : null;
    $AdvancePayment = isset($_POST['AdvancePayment']) ? floatval($_POST['AdvancePayment']) : 0.00;
    $details = isset($_POST['details']) ? $_POST['details'] : [];
    $CustomerID = isset($_POST['CustomerID']) ? trim($_POST['CustomerID']) : '';
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
        $insertQuery = "INSERT INTO job_orders (CustomerID,OrderDate, DeliveryDate, DesignBy, JobFor, CustomerName, CellNo, Designer, DesignerID,AdvancePayment) 
                        VALUES (?,?, ?, ?, ?, ?, ?, ?, ?,?)";
        
        error_log("Job Order Query: " . $insertQuery);
        error_log("Parameters: ".$CustomerID . $OrderDate . ", " . $DeliveryDate . ", " . $DesignBy . ", " . $JobFor . ", " . $CustomerName . ", " . $CellNo . ", " . $Designer . ", " . $AdvancePayment);
        
        $stmt = $conn->prepare($insertQuery);
        if (!$stmt) {
            error_log("Prepare failed for job order: " . $conn->error);
            throw new Exception('Prepare failed for job order: ' . $conn->error);
        }
        
        // REMOVED JobOrderNo from bind_param - now only 8 parameters instead of 9
        $stmt->bind_param("sssssssssd",$CustomerID, $OrderDate, $DeliveryDate, $DesignBy, $JobFor, $CustomerName, $CellNo, $Designer,$DesignerID, $AdvancePayment);
        
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
function getCustomers() {
    global $conn;

    $query = "SELECT CustomerID, CustomerName, Phone FROM tblcustomers ORDER BY CustomerName ASC";
    $result = $conn->query($query);

    if (!$result) {
        sendJsonResponse(false, 'Database error fetching customers: ' . $conn->error);
    }

    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    sendJsonResponse(true, 'Customers loaded successfully', ['customers' => $customers]);
}
function createCustomer() {
    global $conn;

    // Expecting POST values: CustomerName, CellNo (as your JS sends)
    $CustomerName = isset($_POST['CustomerName']) ? trim($_POST['CustomerName']) : '';
    $Phone = isset($_POST['CellNo']) ? trim($_POST['CellNo']) : '';

    if ($CustomerName === '' || $Phone === '') {
        sendJsonResponse(false, 'Customer name and phone are required');
    }

    // Optional: check if a customer with same name+phone already exists to avoid duplicates
    $checkSql = "SELECT CustomerID FROM tblcustomers WHERE CustomerName = ? AND Phone = ? LIMIT 1";
    $stmtCheck = $conn->prepare($checkSql);
    if ($stmtCheck) {
        $stmtCheck->bind_param("ss", $CustomerName, $Phone);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            // Return existing customer id instead of creating duplicate
            sendJsonResponse(true, 'Customer already exists', ['CustomerID' => (int)$row['CustomerID']]);
        }
        // else continue to insert
    }

    // Insert new customer into tblcustomers
    $insertSql = "INSERT INTO tblcustomers (CustomerName, ContactPerson, account_id, Phone, Email, Address, CreatedAt) 
                  VALUES (?, '', NULL, ?, '', '', NOW())";
    $stmt = $conn->prepare($insertSql);
    if (!$stmt) {
        sendJsonResponse(false, 'Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("ss", $CustomerName, $Phone);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
$customer_id=$newId;
////save to account 
 // Step 2: Create customer account in chart of accounts
        $account_code = 'CUS-' . str_pad($customer_id, 4, '0', STR_PAD_LEFT);
        $account_name = 'Customer - ' . $CustomerName;
        $parent_id = 3; // Accounts Receivable parent

        $sql_account = "INSERT INTO accounts (code, name, type, category, parent_id, opening_balance, balance)
                        VALUES (?, ?, 'Asset', 'Accounts Receivable', ?, 0.00, 0.00)";
        $stmt_acc = $conn->prepare($sql_account);
        $stmt_acc->bind_param("ssi", $account_code, $account_name, $parent_id);
        $stmt_acc->execute();

        $account_id = $stmt_acc->insert_id;

        // Step 3: Link customer with its account
        $update_customer = $conn->prepare("UPDATE tblcustomers SET account_id = ? WHERE CustomerID = ?");
        $update_customer->bind_param("ii", $account_id, $customer_id);
        $update_customer->execute();

 
////////////////////
 
        sendJsonResponse(true, 'Customer created', ['CustomerID' => (int)$newId]);
    } else {
        sendJsonResponse(false, 'Failed to create customer: ' . $stmt->error);
    }
}

 function cancelorder()
 {

   global $conn;
    $JobOrderNo = intval($_POST['JobOrderNo']);
    $CancelReason = trim($_POST['CancelReason']);

    $stmt = $conn->prepare("UPDATE job_orders SET status = 'cancelled', cancel_reason = ? WHERE JobOrderNo = ? AND status = 'pending'");
    $stmt->bind_param("si", $CancelReason, $JobOrderNo);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Job order cancelled successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Unable to cancel. Only pending orders can be cancelled."]);
    }
    exit;
 


 }

 function searchCustomers() {
    global $conn;

    $term = isset($_POST['term']) ? trim($_POST['term']) : '';

    if ($term === '') {
        echo json_encode(['success' => true, 'customers' => []]);
        exit;
    }

    // Search by name or phone
    $term_like = "%$term%";
    $stmt = $conn->prepare("SELECT CustomerID, CustomerName, Phone 
                            FROM tblcustomers 
                            WHERE CustomerName LIKE ? OR Phone LIKE ? 
                            ORDER BY CustomerName ASC 
                            LIMIT 10");
    $stmt->bind_param("ss", $term_like, $term_like);
    $stmt->execute();
    $result = $stmt->get_result();

    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    echo json_encode(['success' => true, 'customers' => $customers]);
    exit;
}
function updateJobStatus() {
    global $conn;

    $JobOrderNo = isset($_POST['JobOrderNo']) ? intval($_POST['JobOrderNo']) : 0;
    $NewStatus = isset($_POST['NewStatus']) ? trim($_POST['NewStatus']) : '';

    if ($JobOrderNo <= 0 || $NewStatus === '') {
        sendJsonResponse(false, 'Invalid input');
    }

    $stmt = $conn->prepare("UPDATE job_orders SET status = ? WHERE JobOrderNo = ?");
    $stmt->bind_param("si", $NewStatus, $JobOrderNo);

    if ($stmt->execute()) {
        sendJsonResponse(true, 'Job status updated successfully');
    } else {
        sendJsonResponse(false, 'Failed to update status: ' . $stmt->error);
    }
}

function markAsPrinted() {
    global $conn;
    
    $JobOrderNo = isset($_POST['JobOrderNo']) ? intval($_POST['JobOrderNo']) : 0;
    
    if ($JobOrderNo <= 0) {
        sendJsonResponse(false, 'Invalid job order number');
    }
    
    $stmt = $conn->prepare("UPDATE job_orders SET status = 'printed' WHERE JobOrderNo = ?");
    $stmt->bind_param("i", $JobOrderNo);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, 'Job order marked as printed');
    } else {
        sendJsonResponse(false, 'Failed to update status');
    }
}
  function updateJobOrder() {
      global $conn;
      // Expect POST: JobOrderNo, OrderDate, DeliveryDate, JobFor, CustomerName, CustomerID, CellNo, Designer, DesignerID, AdvancePayment, details (array)
      $JobOrderNo = isset($_POST['JobOrderNo']) ? intval($_POST['JobOrderNo']) : 0;
      if ($JobOrderNo <= 0) sendJsonResponse(false, 'Invalid JobOrderNo');

      $OrderDate = isset($_POST['OrderDate']) ? $_POST['OrderDate'] : null;
      $DeliveryDate = isset($_POST['DeliveryDate']) ? $_POST['DeliveryDate'] : null;
      $JobFor = isset($_POST['JobFor']) ? $_POST['JobFor'] : null;
      $CustomerName = isset($_POST['CustomerName']) ? trim($_POST['CustomerName']) : '';
      $CustomerID = isset($_POST['CustomerID']) ? trim($_POST['CustomerID']) : null;
      $CellNo = isset($_POST['CellNo']) ? trim($_POST['CellNo']) : '';
      $Designer = isset($_POST['Designer']) ? trim($_POST['Designer']) : null;
      $DesignerID = isset($_POST['DesignerID']) ? trim($_POST['DesignerID']) : null;
      $AdvancePayment = isset($_POST['AdvancePayment']) ? floatval($_POST['AdvancePayment']) : 0.00;
      $details = isset($_POST['details']) ? $_POST['details'] : [];

      if (empty($details)) sendJsonResponse(false, 'No details provided');

      $conn->begin_transaction();
      try {
          $stmt = $conn->prepare("UPDATE job_orders SET OrderDate = ?, DeliveryDate = ?, JobFor = ?, CustomerName = ?, CellNo = ?, Designer = ?, DesignerID = ?, AdvancePayment = ? WHERE JobOrderNo = ?");
          if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
          $stmt->bind_param('ssssssdii', $OrderDate, $DeliveryDate, $JobFor, $CustomerName, $CellNo, $Designer, $DesignerID, $AdvancePayment, $JobOrderNo);
          // Note: adjust bind types/order to match your DB fields (DesignerID numeric?)
          $stmt->execute();

          // Delete existing details
          $del = $conn->prepare('DELETE FROM job_order_details WHERE JobOrderNo = ?');
          $del->bind_param('i', $JobOrderNo);
          $del->execute();

          // Insert new details
          $ins = $conn->prepare('INSERT INTO job_order_details (JobOrderNo, SrNo, Detail, Media, Width, Height, Qty, Sqft, Ring, Pocket) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
          if (!$ins) throw new Exception('Prepare detail insert failed: ' . $conn->error);

          foreach ($details as $d) {
              $sr = intval($d['SrNo']);
              $detail = $d['Detail'];
              $media = $d['Media'];
              $width = floatval($d['Width']);
              $height = floatval($d['Height']);
              $qty = intval($d['Qty']);
              $sqft = floatval($d['Sqft']);
              $ring = intval($d['Ring']);
              $pocket = intval($d['Pocket']);

              $ins->bind_param('iisssddiii', $JobOrderNo, $sr, $detail, $media, $width, $height, $qty, $sqft, $ring, $pocket);
              if (!$ins->execute()) throw new Exception('Insert detail failed: ' . $ins->error);
          }

          $conn->commit();
          sendJsonResponse(true, 'Job order updated');
      } catch (Exception $e) {
          $conn->rollback();
          sendJsonResponse(false, 'Error updating job order: ' . $e->getMessage());
      }
  }
?>