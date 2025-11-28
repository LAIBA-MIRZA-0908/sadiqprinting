<?php
// quotation_functions.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');

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

try {
    include 'db_connection.php';
    
    if (!$conn) {
        sendJsonResponse(false, 'Database connection failed');
    }
} catch (Exception $e) {
    sendJsonResponse(false, 'Database connection error: ' . $e->getMessage());
}

 $action = isset($_POST['action']) ? $_POST['action'] : '';

if (empty($action)) {
    sendJsonResponse(false, 'No action specified');
}

switch ($action) {
    case 'get_customers':
        getCustomers();
        break;
    case 'get_materials':
        getMaterials();
        break;
    case 'create_quotation':
        createQuotation();
        break;
    case 'get_quotations':
        getQuotations();
        break;
    case 'get_quotation_details':
        getQuotationDetails();
        break;
    case 'update_quotation_status':
        updateQuotationStatus();
        break;
    default:
        sendJsonResponse(false, 'Invalid action: ' . $action);
        break;
}

 $conn->close();

function getCustomers() {
    global $conn;
    
    $query = "SELECT CustomerID, CustomerName, ContactPerson, Phone, Email, Address 
              FROM tblcustomers 
              ORDER BY CustomerName ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    
    sendJsonResponse(true, 'Customers loaded successfully', [
        'customers' => $customers
    ]);
}

function getMaterials() {
    global $conn;
    
    $query = "SELECT id, name FROM tblmaterials ORDER BY name ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $materials = [];
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    
    sendJsonResponse(true, 'Materials loaded successfully', [
        'materials' => $materials
    ]);
}

function createQuotation() {
    global $conn;
    
    $QuotationNo = isset($_POST['QuotationNo']) ? trim($_POST['QuotationNo']) : '';
    $QuotationDate = isset($_POST['QuotationDate']) ? $_POST['QuotationDate'] : '';
    $ValidUntil = isset($_POST['ValidUntil']) ? $_POST['ValidUntil'] : null;
    $CustomerID = isset($_POST['CustomerID']) ? (int)$_POST['CustomerID'] : 0;
    $CustomerName = isset($_POST['CustomerName']) ? trim($_POST['CustomerName']) : '';
    $QuotationSubject = isset($_POST['QuotationSubject']) ? trim($_POST['QuotationSubject']) : null;
    $SubTotal = isset($_POST['SubTotal']) ? floatval($_POST['SubTotal']) : 0.00;
    $Advance = isset($_POST['Advance']) ? floatval($_POST['Advance']) : 0.00;
    $GSTRate = isset($_POST['GSTRate']) ? floatval($_POST['GSTRate']) : 0.00;
    $TotalGST = isset($_POST['TotalGST']) ? floatval($_POST['TotalGST']) : 0.00;
    $NTRRate = isset($_POST['NTRRate']) ? floatval($_POST['NTRRate']) : 0.00;
    $TotalNTR = isset($_POST['TotalNTR']) ? floatval($_POST['TotalNTR']) : 0.00;
    $GrandTotal = isset($_POST['GrandTotal']) ? floatval($_POST['GrandTotal']) : 0.00;
    $items = isset($_POST['items']) ? $_POST['items'] : [];
    
    // Validate required fields
    if (empty($QuotationNo) || empty($QuotationDate) || $CustomerID <= 0) {
        sendJsonResponse(false, 'Please fill in all required fields');
    }
    
    if (empty($items)) {
        sendJsonResponse(false, 'Please add at least one item to the quotation');
    }
    
    $conn->begin_transaction();
    
    try {
        // Insert quotation
        $insertQuery = "INSERT INTO tblquotations (QuotationNo, QuotationDate, ValidUntil, CustomerID, CustomerName, QuotationSubject, 
                        SubTotal, Advance, GSTRate, TotalGST, NTRRate, TotalNTR, GrandTotal) 
                        VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertQuery);
        if (!$stmt) {
            throw new Exception('Prepare failed for quotation: ' . $conn->error);
        }
        
        $stmt->bind_param("sssisssdddddd", $QuotationNo, $QuotationDate, $ValidUntil, $CustomerID, $CustomerName, 
                          $QuotationSubject, $SubTotal, $Advance, $GSTRate, $TotalGST, $NTRRate, $TotalNTR, $GrandTotal);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert quotation: ' . $stmt->error);
        }
        
        $quotationId = $conn->insert_id;
        
        // Insert quotation items
        foreach ($items as $index => $item) {
            $insertItemQuery = "INSERT INTO tblquotation_details (QuotationID, Detail, Media, Width, Height, Qty, Sqft, Rate, Total) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmtItem = $conn->prepare($insertItemQuery);
            if (!$stmtItem) {
                throw new Exception('Prepare failed for quotation item: ' . $conn->error);
            }
            
            $stmtItem->bind_param("isssddddd", $quotationId, $item['Detail'], $item['Media'], 
                                  $item['Width'], $item['Height'], $item['Qty'], $item['Sqft'], $item['Rate'], $item['Total']);
            
            if (!$stmtItem->execute()) {
                throw new Exception('Failed to insert quotation item: ' . $stmtItem->error);
            }
        }
        
        $conn->commit();
        
        sendJsonResponse(true, 'Quotation created successfully', [
            'QuotationID' => $quotationId,
            'QuotationNo' => $QuotationNo
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        sendJsonResponse(false, 'Error: ' . $e->getMessage());
    }
}

function getQuotations() {
    global $conn;
    
    $query = "SELECT q.QuotationID, q.QuotationNo, q.QuotationDate, q.CustomerName, q.GrandTotal, q.Status, q.ValidUntil,
                     c.Phone, c.Email 
              FROM tblquotations q
              LEFT JOIN tblcustomers c ON q.CustomerID = c.CustomerID
              ORDER BY q.QuotationID DESC";
    $result = $conn->query($query);
    
    if (!$result) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $quotations = [];
    while ($row = $result->fetch_assoc()) {
        $quotations[] = $row;
    }
    
    sendJsonResponse(true, 'Quotations loaded successfully', [
        'quotations' => $quotations
    ]);
}

function getQuotationDetails() {
    global $conn;
    
    $quotationId = isset($_POST['quotationId']) ? (int)$_POST['quotationId'] : 0;
    
    if ($quotationId <= 0) {
        sendJsonResponse(false, 'Invalid quotation ID');
    }
    
    // Get quotation details
    $quotationQuery = "SELECT q.*, c.Phone, c.Email, c.Address 
                     FROM tblquotations q
                     LEFT JOIN tblcustomers c ON q.CustomerID = c.CustomerID
                     WHERE q.QuotationID = ?";
    
    $stmt = $conn->prepare($quotationQuery);
    $stmt->bind_param("i", $quotationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendJsonResponse(false, 'Quotation not found');
    }
    
    $quotation = $result->fetch_assoc();
    
    // Get quotation items
    $itemsQuery = "SELECT * FROM tblquotation_details WHERE QuotationID = ? ORDER BY DetailID";
    $stmtItems = $conn->prepare($itemsQuery);
    $stmtItems->bind_param("i", $quotationId);
    $stmtItems->execute();
    $itemsResult = $stmtItems->get_result();
    
    $items = [];
    while ($row = $itemsResult->fetch_assoc()) {
        $items[] = $row;
    }
    
    sendJsonResponse(true, 'Quotation details loaded successfully', [
        'quotation' => $quotation,
        'items' => $items
    ]);
}

function updateQuotationStatus() {
    global $conn;
    
    $quotationId = isset($_POST['quotationId']) ? (int)$_POST['quotationId'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    
    if ($quotationId <= 0) {
        sendJsonResponse(false, 'Invalid quotation ID');
    }
    
    if (empty($status)) {
        sendJsonResponse(false, 'Status is required');
    }
    
    // Validate status
    $validStatuses = ['draft', 'sent', 'approved', 'declined', 'invoice_made'];
    if (!in_array($status, $validStatuses)) {
        sendJsonResponse(false, 'Invalid status');
    }
    
    $updateQuery = "UPDATE tblquotations SET Status = ? WHERE QuotationID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $status, $quotationId);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, 'Quotation status updated successfully');
    } else {
        sendJsonResponse(false, 'Failed to update quotation status');
    }
}
?>