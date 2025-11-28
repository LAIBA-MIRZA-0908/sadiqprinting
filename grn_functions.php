<?php
// grn_functions.php
require_once 'db_connection.php';

header('Content-Type: application/json');

function sendJsonResponse($success, $data = null, $message = '') {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response = array_merge($response, $data);
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'get_purchase_orders':
                getPurchaseOrders();
                break;
            case 'get_po_details':
                getPODetails();
                break;
            case 'save_grn':
                saveGRN();
                break;
            case 'get_grns':
                getGRNs();
                break;
                  case 'get_grn_details':
                getGRNDetails();
                break;
                
            default:
                sendJsonResponse(false, [], 'Invalid action: ' . $action);
                break;
        }
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Server error: ' . $e->getMessage());
    }
} else {
    sendJsonResponse(false, [], 'Invalid request method');
}

function getPurchaseOrders() {
    global $conn;
    
    try {
        // Get only confirmed POs
        $sql = "SELECT po.id, po.po_number, po.supplier_id, po.order_date, po.total_amount, po.status,
                s.name as supplier_name 
                FROM purchase_orders po 
                JOIN suppliers s ON po.supplier_id = s.id 
               -- WHERE po.status = 'confirmed' 
                ORDER BY po.created_at DESC";
        
        $result = $conn->query($sql);
        
        if (!$result) {
            sendJsonResponse(false, [], 'Database error: ' . $conn->error);
            return;
        }
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        sendJsonResponse(true, ['orders' => $orders]);
        
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Error loading purchase orders: ' . $e->getMessage());
    }
}

function getPODetails() {
    global $conn;
    
    try {
        $poId = intval($_POST['po_id'] ?? 0);
        
        if ($poId <= 0) {
            sendJsonResponse(false, [], 'Invalid PO ID');
            return;
        }
        
        // Get PO details
        $sql = "SELECT po.*, s.name as supplier_name 
                FROM purchase_orders po 
                JOIN suppliers s ON po.supplier_id = s.id 
                WHERE po.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $poId);
        $stmt->execute();
        $result = $stmt->get_result();
        $po = $result->fetch_assoc();
        
        if (!$po) {
            sendJsonResponse(false, [], 'Purchase order not found');
            return;
        }
        
        // Get PO items
        $itemsSql = "SELECT poi.*, p.title as product_name, pv.title as variant_name 
                     FROM purchase_order_items poi 
                     LEFT JOIN products p ON poi.product_id = p.id 
                     LEFT JOIN product_variants pv ON poi.variant_id = pv.id 
                     WHERE poi.purchase_order_id = ?";
        
        $itemsStmt = $conn->prepare($itemsSql);
        $itemsStmt->bind_param('i', $poId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        $items = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }
        
        $po['items'] = $items;
        sendJsonResponse(true, ['po' => $po]);
        
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Error loading PO details: ' . $e->getMessage());
    }
}

function saveGRN() {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        // Get form data
        $grnNumber = $_POST['grn_number'];
        $grnDate = $_POST['grn_date'];
        $purchaseOrderId = intval($_POST['purchase_order_id']);
        $status = $_POST['status'] ?? 'draft';
        $notes = $_POST['notes'] ?? '';
        $items = json_decode($_POST['items'], true);
        
        // Validate
        if (!$grnNumber || !$grnDate || !$purchaseOrderId || empty($items)) {
            sendJsonResponse(false, [], 'Missing required fields');
        }
        
        // Get PO details
        $poSql = "SELECT supplier_id FROM purchase_orders WHERE id = ?";
        $poStmt = $conn->prepare($poSql);
        $poStmt->bind_param('i', $purchaseOrderId);
        $poStmt->execute();
        $poResult = $poStmt->get_result();
        $po = $poResult->fetch_assoc();
        
        if (!$po) {
            sendJsonResponse(false, [], 'Purchase order not found');
        }
        
        // Calculate total amount
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item['subtotal'];
        }
        
        // Insert GRN master
        $grnSql = "INSERT INTO grn_master (grn_number, purchase_order_id, supplier_id, grn_date, status, total_amount, notes, created_by, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        
        $grnStmt = $conn->prepare($grnSql);
        $grnStmt->bind_param('sisisss', $grnNumber, $purchaseOrderId, $po['supplier_id'], $grnDate, $status, $totalAmount, $notes);
        
        if (!$grnStmt->execute()) {
            throw new Exception("Error creating GRN: " . $grnStmt->error);
        }
        
        $grnId = $conn->insert_id;
        
        // Insert GRN items
        foreach ($items as $item) {
            $itemSql = "INSERT INTO grn_items (grn_id, product_id, variant_id, ordered_qty, received_qty, unit_price, subtotal) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $itemStmt = $conn->prepare($itemSql);
            $itemStmt->bind_param('iiidddd', $grnId, $item['product_id'], $item['variant_id'], 
                                        $item['ordered_qty'], $item['received_qty'], $item['unit_price'], $item['subtotal']);
            
            if (!$itemStmt->execute()) {
                throw new Exception("Error adding GRN item: " . $itemStmt->error);
            }
            
            // Update stock
            updateStock($conn, $item['product_id'], $item['variant_id'], $item['received_qty']);
        }
        
        // Update PO status if fully received
        updatePOStatus($conn, $purchaseOrderId);
        
        $conn->commit();
        sendJsonResponse(true, ['grn_id' => $grnId], 'GRN saved successfully');
        
    } catch (Exception $e) {
        $conn->rollback();
        sendJsonResponse(false, [], $e->getMessage());
    }
}

function updateStock($conn, $productId, $variantId, $quantity) {
    if ($variantId) {
        // Update variant stock
        $sql = "UPDATE product_variants SET inventory_quantity = inventory_quantity + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('di', $quantity, $variantId);
        $stmt->execute();
    } else {
        // Update product stock (if no variants)
        $sql = "UPDATE products SET stock = stock + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('di', $quantity, $productId);
        $stmt->execute();
    }
}

function updatePOStatus($conn, $poId) {
    // Check if PO is fully received
    $sql = "SELECT poi.quantity, SUM(gi.received_qty) as received_qty 
            FROM purchase_order_items poi 
            LEFT JOIN grn_items gi ON poi.product_id = gi.product_id AND poi.variant_id = gi.variant_id 
            WHERE poi.purchase_order_id = ? 
            GROUP BY poi.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $poId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $fullyReceived = true;
    while ($row = $result->fetch_assoc()) {
        if ($row['quantity'] > $row['received_qty']) {
            $fullyReceived = false;
            break;
        }
    }
    
    if ($fullyReceived) {
        $updateSql = "UPDATE purchase_orders SET status = 'received' WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('i', $poId);
        $updateStmt->execute();
    }
}

function getGRNs() {
    global $conn;
    
    try {
        $search = trim($_POST['search'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $dateFilter = trim($_POST['date_filter'] ?? '');
        
        // Build WHERE conditions
        $whereConditions = [];
        $params = [];
        $types = '';
        
        if (!empty($search)) {
            $whereConditions[] = "(gm.grn_number LIKE ? OR s.name LIKE ? OR po.po_number LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            $types .= 'sss';
        }
        
        if (!empty($status)) {
            $whereConditions[] = "gm.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        if ($dateFilter !== 'all') {
            $dateCondition = getGRNDateCondition($dateFilter);
            if ($dateCondition) {
                $whereConditions[] = $dateCondition;
            }
        }
        
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $sql = "SELECT gm.*, po.po_number, s.name as supplier_name 
                FROM grn_master gm 
                JOIN purchase_orders po ON gm.purchase_order_id = po.id 
                JOIN suppliers s ON gm.supplier_id = s.id 
                $whereClause 
                ORDER BY gm.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $grns = [];
        while ($row = $result->fetch_assoc()) {
            $grns[] = $row;
        }
        
        sendJsonResponse(true, ['grns' => $grns]);
        
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Error loading GRNs: ' . $e->getMessage());
    }
}

function getGRNDateCondition($dateFilter) {
    $conditions = [
        'today' => "DATE(gm.grn_date) = CURDATE()",
        'week' => "YEARWEEK(gm.grn_date) = YEARWEEK(CURDATE())",
        'month' => "YEAR(gm.grn_date) = YEAR(CURDATE()) AND MONTH(gm.grn_date) = MONTH(CURDATE())"
    ];
    
    return $conditions[$dateFilter] ?? null;
}
function getGRNDetails() {
    global $conn;
    
    try {
        $grnId = intval($_POST['grn_id'] ?? 0);
        
        if ($grnId <= 0) {
            sendJsonResponse(false, [], 'Invalid GRN ID');
            return;
        }
        
        // Get GRN details
        $sql = "SELECT gm.*, po.po_number, s.name as supplier_name 
                FROM grn_master gm 
                JOIN purchase_orders po ON gm.purchase_order_id = po.id 
                JOIN suppliers s ON gm.supplier_id = s.id 
                WHERE gm.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $grnId);
        $stmt->execute();
        $result = $stmt->get_result();
        $grn = $result->fetch_assoc();
        
        if (!$grn) {
            sendJsonResponse(false, [], 'GRN not found');
            return;
        }
        
        // Get GRN items
        $itemsSql = "SELECT gi.*, p.title as product_name, pv.title as variant_name 
                     FROM grn_items gi 
                     LEFT JOIN products p ON gi.product_id = p.id 
                     LEFT JOIN product_variants pv ON gi.variant_id = pv.id 
                     WHERE gi.grn_id = ?";
        
        $itemsStmt = $conn->prepare($itemsSql);
        $itemsStmt->bind_param('i', $grnId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        $items = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }
        
        $grn['items'] = $items;
        sendJsonResponse(true, ['grn' => $grn, 'items' => $items]);
        
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Error loading GRN details: ' . $e->getMessage());
    }
}
?>