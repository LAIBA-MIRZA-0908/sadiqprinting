<?php
// purchase_functions.php - Debug Version
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable display errors for debugging

require_once 'db_connection.php';

// Set header first to avoid any output issues
header('Content-Type: application/json');

// Function to send consistent JSON responses
function sendJsonResponse($success, $data = null, $message = '') {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response = array_merge($response, $data);
    echo json_encode($response);
    exit;
}

// Create a global connection variable
 $GLOBALS['conn'] = $conn;

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'get_suppliers':
                getSuppliers();
                break;
            case 'get_payment_accounts':
                getPaymentAccounts();
                break;
            case 'get_products':
                getProducts();
                break;
            case 'get_variants':
                getVariants();
                break;
            case 'create_purchase':
                createPurchaseOrder();
                break;
            case 'get_purchases':
                getPurchases();
                break;
            case 'get_purchase_details':
                getPurchaseDetails();
                break;
            case 'delete_purchase':
                deletePurchase();
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

// Simplified function definitions without $conn parameter
function getSuppliers() {
    $conn = $GLOBALS['conn'];
    
    try {
        // Check if suppliers table exists
      
        
        $sql = "SELECT * FROM suppliers ORDER BY name";
        $result = $conn->query($sql);
        
        if (!$result) {
            sendJsonResponse(false, [], 'Database error: ' . $conn->error);
            return;
        }
        
        $suppliers = [];
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
        
        sendJsonResponse(true, ['suppliers' => $suppliers]);
        
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Error loading suppliers: ' . $e->getMessage());
    }
}

function getPaymentAccounts() {
    $conn = $GLOBALS['conn'];
    
    try {
        // Check if accounts table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'accounts'");
        if ($checkTable->num_rows === 0) {
            // Create accounts table if it doesn't exist
            $createTable = "CREATE TABLE IF NOT EXISTS `accounts` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(150) NOT NULL,
                `type` VARCHAR(50) NOT NULL,
                `balance` DECIMAL(15,2) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if (!$conn->query($createTable)) {
                sendJsonResponse(false, [], 'Error creating accounts table: ' . $conn->error);
                return;
            }
            
            // Insert sample accounts
            $conn->query("INSERT INTO accounts (name, type, balance) VALUES 
                ('Cash Account', 'asset', 10000), 
                ('Bank Account', 'bank', 50000), 
                ('Accounts Payable', 'liability', 0)");
        }
        
        $sql = "SELECT id, name, balance FROM accounts ORDER BY name";
        $result = $conn->query($sql);
        
        if (!$result) {
            sendJsonResponse(false, [], 'Database error: ' . $conn->error);
            return;
        }
        
        $accounts = [];
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }
        
        sendJsonResponse(true, ['accounts' => $accounts]);
        
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Error loading payment accounts: ' . $e->getMessage());
    }
}

function getProducts() {
    $conn = $GLOBALS['conn'];
    
    try {
        // Check if products table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'products'");
        if ($checkTable->num_rows === 0) {
            // Create products table if it doesn't exist
            $createTable = "CREATE TABLE IF NOT EXISTS `products` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `category_id` INT,
                `status` VARCHAR(50) DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if (!$conn->query($createTable)) {
                sendJsonResponse(false, [], 'Error creating products table: ' . $conn->error);
                return;
            }
            
            // Insert sample products
            $conn->query("INSERT INTO products (title, category_id) VALUES 
                ('Laptop', 1), ('Smartphone', 1), ('T-Shirt', 2), ('Jeans', 2), ('Rice', 3)");
        }
        
        $sql = "SELECT id, title, category_id FROM products ORDER BY title";
        $result = $conn->query($sql);
        
        if (!$result) {
            sendJsonResponse(false, [], 'Database error: ' . $conn->error);
            return;
        }
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        sendJsonResponse(true, ['products' => $products]);
        
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Error loading products: ' . $e->getMessage());
    }
}

function getVariants() {
    $conn = $GLOBALS['conn'];
    
    try {
        $product_id = intval($_POST['product_id'] ?? 0);
        
        if ($product_id <= 0) {
            sendJsonResponse(false, [], 'Invalid product ID');
            return;
        }
        
        // Check if product_variants table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'product_variants'");
        if ($checkTable->num_rows === 0) {
            // Create product_variants table if it doesn't exist
            $createTable = "CREATE TABLE IF NOT EXISTS `product_variants` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `product_id` INT(11) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `price` DECIMAL(10,2) DEFAULT 0,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if (!$conn->query($createTable)) {
                sendJsonResponse(false, [], 'Error creating product_variants table: ' . $conn->error);
                return;
            }
            
            // Insert sample variants
            $conn->query("INSERT INTO product_variants (product_id, title, price) VALUES 
                (1, '8GB RAM', 50000), (1, '16GB RAM', 65000), 
                (2, '64GB', 25000), (2, '128GB', 35000),
                (3, 'Small', 500), (3, 'Medium', 600), (3, 'Large', 700),
                (4, '30', 1500), (4, '32', 1600), (4, '34', 1700),
                (5, '1kg', 100), (5, '5kg', 450)");
        }
        
        $sql = "SELECT id, title, price FROM product_variants WHERE product_id = ? ORDER BY title";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            sendJsonResponse(false, [], 'Prepare failed: ' . $conn->error);
            return;
        }
        
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $variants = [];
        while ($row = $result->fetch_assoc()) {
            $variants[] = $row;
        }
        
        sendJsonResponse(true, ['variants' => $variants]);
        
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Error loading variants: ' . $e->getMessage());
    }
}

function createPurchaseOrder() {
    $conn = $GLOBALS['conn'];
    
    try {
        $conn->autocommit(FALSE); // Start transaction
        
        // Check if purchase_orders table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'purchase_orders'");
        if ($checkTable->num_rows === 0) {
            // Create purchase_orders table if it doesn't exist
            $createTable = "CREATE TABLE IF NOT EXISTS `purchase_orders` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `po_number` VARCHAR(50) NOT NULL,
                `supplier_id` INT(11) NOT NULL,
                `order_date` DATE NOT NULL,
                `due_date` DATE,
                `total_amount` DECIMAL(15,2) NOT NULL,
                `payment_type` VARCHAR(20) NOT NULL,
                `payment_account_id` INT(11),
                `supplier_payable_account_id` INT(11),
                `notes` TEXT,
                `status` VARCHAR(20) DEFAULT 'draft',
                `created_by` INT(11),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if (!$conn->query($createTable)) {
                throw new Exception("Error creating purchase_orders table: " . $conn->error);
            }
        }
        
        // Check if purchase_order_items table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'purchase_order_items'");
        if ($checkTable->num_rows === 0) {
            // Create purchase_order_items table if it doesn't exist
            $createTable = "CREATE TABLE IF NOT EXISTS `purchase_order_items` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `purchase_order_id` INT(11) NOT NULL,
                `product_id` INT(11) NOT NULL,
                `variant_id` INT(11),
                `quantity` DECIMAL(10,2) NOT NULL,
                `unit_price` DECIMAL(10,2) NOT NULL,
                `discount` DECIMAL(10,2) DEFAULT 0,
                `tax` DECIMAL(10,2) DEFAULT 0,
                `stock_account_id` INT(11),
                `expense_account_id` INT(11),
                PRIMARY KEY (`id`),
                FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if (!$conn->query($createTable)) {
                throw new Exception("Error creating purchase_order_items table: " . $conn->error);
            }
        }
        
        // Get form data
        $po_number = $conn->real_escape_string($_POST['po_number']);
        $supplier_id = intval($_POST['supplier_id']);
        $order_date = $conn->real_escape_string($_POST['order_date']);
        $due_date = !empty($_POST['due_date']) ? $conn->real_escape_string($_POST['due_date']) : null;
        $payment_type = $conn->real_escape_string($_POST['payment_type']);
        $payment_account_id = !empty($_POST['payment_account_id']) ? intval($_POST['payment_account_id']) : null;
        $notes = $conn->real_escape_string($_POST['notes'] ?? '');
        
        // Parse items
        $items = json_decode($_POST['items'], true);
        $total_amount = 0;
        
        // Calculate total amount
        foreach ($items as $item) {
            $quantity = floatval($item['quantity']);
            $unit_price = floatval($item['unit_price']);
            $discount = floatval($item['discount']);
            $tax = floatval($item['tax']);
            $subtotal = ($quantity * $unit_price) - $discount + $tax;
            $total_amount += $subtotal;
        }
        
        // Insert purchase order
        $sql = "INSERT INTO purchase_orders (po_number, supplier_id, order_date, due_date, total_amount, 
                payment_type, payment_account_id, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $created_by = 1; // You can get this from session
        $stmt->bind_param('sissdsssi', $po_number, $supplier_id, $order_date, $due_date, $total_amount, 
                         $payment_type, $payment_account_id, $notes, $created_by);
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating purchase order: " . $stmt->error);
        }
        
        $purchase_order_id = $conn->insert_id;
        
        // Insert purchase items
        foreach ($items as $item) {
            $product_id = intval($item['product_id']);
            $variant_id = !empty($item['variant_id']) ? intval($item['variant_id']) : null;
            $quantity = floatval($item['quantity']);
            $unit_price = floatval($item['unit_price']);
            $discount = floatval($item['discount']);
            $tax = floatval($item['tax']);
            
            $item_sql = "INSERT INTO purchase_order_items (purchase_order_id, product_id, variant_id, 
                         quantity, unit_price, discount, tax) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $item_stmt = $conn->prepare($item_sql);
            $item_stmt->bind_param('iiidddd', $purchase_order_id, $product_id, $variant_id, 
                                  $quantity, $unit_price, $discount, $tax);
            
            if (!$item_stmt->execute()) {
                throw new Exception("Error adding purchase item: " . $item_stmt->error);
            }
        }
        
        $conn->commit();
        sendJsonResponse(true, ['purchase_id' => $purchase_order_id], 'Purchase order created successfully');
        
    } catch (Exception $e) {
        $conn->rollback();
        sendJsonResponse(false, [], $e->getMessage());
    } finally {
        $conn->autocommit(TRUE);
    }
}

function getPurchases() {
    $conn = $GLOBALS['conn'];
    
    try {
         
        
        $page = intval($_POST['page'] ?? 1);
        $limit = intval($_POST['limit'] ?? 10);
        $search = trim($_POST['search'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $payment_type = trim($_POST['payment_type'] ?? '');
        $date_filter = trim($_POST['date_filter'] ?? 'all');
        
        $offset = ($page - 1) * $limit;
        
        // Build WHERE conditions
        $whereConditions = [];
        $params = [];
        $types = '';
        
        if (!empty($search)) {
            $whereConditions[] = "(po.po_number LIKE ? OR s.name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
            $types .= 'ss';
        }
        
        if (!empty($status)) {
            $whereConditions[] = "po.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        if (!empty($payment_type)) {
            $whereConditions[] = "po.payment_type = ?";
            $params[] = $payment_type;
            $types .= 's';
        }
        
        if ($date_filter !== 'all') {
            $dateCondition = getPurchaseDateCondition($date_filter);
            if ($dateCondition) {
                $whereConditions[] = $dateCondition;
            }
        }
        
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM purchase_orders po 
                     JOIN suppliers s ON po.supplier_id = s.id 
                     $whereClause";
        $countStmt = $conn->prepare($countSql);
        
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        
        $countStmt->execute();
        $totalResult = $countStmt->get_result();
        $totalRow = $totalResult->fetch_assoc();
        $totalPurchases = $totalRow['total'];
        
        // Get purchases
        $sql = "SELECT po.*, s.name 
                FROM purchase_orders po 
                JOIN suppliers s ON po.supplier_id = s.id 
                $whereClause 
                ORDER BY po.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $purchases = [];
        while ($row = $result->fetch_assoc()) {
            $purchases[] = $row;
        }
        
        sendJsonResponse(true, [
            'purchases' => $purchases,
            'total' => $totalPurchases,
            'page' => $page,
            'limit' => $limit
        ]);
        
    } catch (Exception $e) {
        sendJsonResponse(false, [], 'Error loading purchases: ' . $e->getMessage());
    }
}
function getPurchaseDateCondition($date_filter) {
    $conditions = [
        'today' => "DATE(po.order_date) = CURDATE()",
        'week' => "YEARWEEK(po.order_date) = YEARWEEK(CURDATE())",
        'month' => "YEAR(po.order_date) = YEAR(CURDATE()) AND MONTH(po.order_date) = MONTH(CURDATE())"
    ];
    
    return $conditions[$date_filter] ?? null;
}

function getPurchaseDetails() {
    $conn = $GLOBALS['conn'];
    
    $purchase_id = intval($_POST['purchase_id']);
    
    // Get purchase order
    $sql = "SELECT po.*, s.name as supplier_name, '' as contact_person, s.phone, s.email, s.address 
            FROM purchase_orders po 
            JOIN suppliers s ON po.supplier_id = s.id 
            WHERE po.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $purchase_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $purchase = $result->fetch_assoc();
    
    if (!$purchase) {
        sendJsonResponse(false, [], 'Purchase order not found');
        return;
    }
    
    // Get purchase items
    $items_sql = "SELECT poi.*, p.title as product_name, pv.title as variant_name 
                  FROM purchase_order_items poi 
                  LEFT JOIN products p ON poi.product_id = p.id 
                  LEFT JOIN product_variants pv ON poi.variant_id = pv.id 
                  WHERE poi.purchase_order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param('i', $purchase_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    $html = generatePurchaseDetailsHtml($purchase, $items);
    
    sendJsonResponse(true, [
        'purchase' => $purchase,
        'items' => $items,
        'html' => $html
    ]);
}

function generatePurchaseDetailsHtml($purchase, $items) {
    $orderDate = date('F j, Y', strtotime($purchase['order_date']));
    $dueDate = $purchase['due_date'] ? date('F j, Y', strtotime($purchase['due_date'])) : 'Not specified';
    
    ob_start();
    ?>
    <div class="print-container bg-white">
        <div class="header mb-6 border-b pb-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">PURCHASE ORDER</h1>
                    <p class="text-gray-600">Just Original Shop</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold">PO #: <?php echo $purchase['po_number']; ?></p>
                    <p class="text-sm">Date: <?php echo $orderDate; ?></p>
                    <p class="text-sm">Due Date: <?php echo $dueDate; ?></p>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="font-semibold mb-2">SUPPLIER INFORMATION</h3>
                <p class="font-medium"><?php echo $purchase['supplier_name']; ?></p>
                <p><?php echo $purchase['contact_person'] ?: 'N/A'; ?></p>
                <p><?php echo $purchase['phone'] ?: 'N/A'; ?></p>
                <p><?php echo $purchase['email'] ?: 'N/A'; ?></p>
                <p class="text-sm"><?php echo $purchase['address'] ?: 'N/A'; ?></p>
            </div>
            <div>
                <h3 class="font-semibold mb-2">ORDER SUMMARY</h3>
                <div class="flex justify-between mb-1">
                    <span>Status:</span>
                    <span class="font-semibold"><?php echo strtoupper($purchase['status']); ?></span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Payment Type:</span>
                    <span><?php echo strtoupper($purchase['payment_type']); ?></span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Items:</span>
                    <span><?php echo count($items); ?></span>
                </div>
            </div>
        </div>
        
        <table class="w-full border-collapse border border-gray-300 mb-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2 text-left">Product</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Variant</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Qty</th>
                    <th class="border border-gray-300 px-4 py-2 text-right">Unit Price</th>
                    <th class="border border-gray-300 px-4 py-2 text-right">Discount</th>
                    <th class="border border-gray-300 px-4 py-2 text-right">Tax</th>
                    <th class="border border-gray-300 px-4 py-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($item['variant_name'] ?: 'Default'); ?></td>
                    <td class="border border-gray-300 px-4 py-2 text-center"><?php echo $item['quantity']; ?></td>
                    <td class="border border-gray-300 px-4 py-2 text-right">Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                    <td class="border border-gray-300 px-4 py-2 text-right">Rs. <?php echo number_format($item['discount'], 2); ?></td>
                    <td class="border border-gray-300 px-4 py-2 text-right">Rs. <?php echo number_format($item['tax'], 2); ?></td>
                    <td class="border border-gray-300 px-4 py-2 text-right">Rs. <?php echo number_format(($item['quantity'] * $item['unit_price']) - $item['discount'] + $item['tax'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="border border-gray-300 px-4 py-2 text-right font-semibold">Total Amount:</td>
                    <td class="border border-gray-300 px-4 py-2 text-right font-semibold">
                        Rs. <?php echo number_format($purchase['total_amount'], 2); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <?php if ($purchase['notes']): ?>
        <div class="notes-section mt-6 p-4 bg-gray-100 rounded">
            <h3 class="font-semibold mb-2">NOTES</h3>
            <p><?php echo htmlspecialchars($purchase['notes']); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="footer mt-8 text-center text-sm text-gray-600">
            <p>Generated on <?php echo date('F j, Y g:i A'); ?></p>
        </div>
    </div>
    
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-container, .print-container * {
                visibility: visible;
            }
            .print-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}

function deletePurchase() {
    $conn = $GLOBALS['conn'];
    
    $purchase_id = intval($_POST['purchase_id']);
    
    try {
        $conn->autocommit(FALSE);
        
        // First, check if purchase exists and get details
        $sql = "SELECT * FROM purchase_orders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $purchase_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $purchase = $result->fetch_assoc();
        
        if (!$purchase) {
            throw new Exception("Purchase order not found");
        }
        
        // Reverse ledger entries before deleting the purchase
        reverseLedgerEntries($conn, $purchase_id, $purchase['total_amount'], $purchase['payment_type'], $purchase['payment_account_id'], $purchase['supplier_id']);
        
        // Delete purchase items
        $delete_items_sql = "DELETE FROM purchase_order_items WHERE purchase_order_id = ?";
        $delete_items_stmt = $conn->prepare($delete_items_sql);
        $delete_items_stmt->bind_param('i', $purchase_id);
        $delete_items_stmt->execute();
        
        // Delete purchase order
        $delete_sql = "DELETE FROM purchase_orders WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('i', $purchase_id);
        $delete_stmt->execute();
        
        $conn->commit();
        sendJsonResponse(true, [], 'Purchase order deleted successfully');
        
    } catch (Exception $e) {
        $conn->rollback();
        sendJsonResponse(false, [], $e->getMessage());
    } finally {
        $conn->autocommit(TRUE);
    }
}
 

// FIXED: Added $conn parameter
function reverseLedgerEntries($conn, $purchase_order_id, $total_amount, $payment_type, $payment_account_id, $supplier_id) {
    // Get purchase order details for description
    $po_sql = "SELECT po.po_number, s.name as supplier_name FROM purchase_orders po 
               JOIN suppliers s ON po.supplier_id = s.id 
               WHERE po.id = ?";
    $po_stmt = $conn->prepare($po_sql);
    $po_stmt->bind_param('i', $purchase_order_id);
    $po_stmt->execute();
    $po_result = $po_stmt->get_result();
    $purchase_order = $po_result->fetch_assoc();
    
    $description = "REVERSAL - Purchase from {$purchase_order['supplier_name']} - PO: {$purchase_order['po_number']}";
    
    // Get inventory account from first item
    $item_sql = "SELECT stock_account_id FROM purchase_order_items WHERE purchase_order_id = ? LIMIT 1";
    $item_stmt = $conn->prepare($item_sql);
    $item_stmt->bind_param('i', $purchase_order_id);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();
    $item = $item_result->fetch_assoc();
    
    $inventory_account_id = $item['stock_account_id'];
    
    // Credit Inventory Account (reverse the debit)
    $credit_sql = "INSERT INTO ledgers (account_id, debit, credit, description, date) VALUES (?, 0, ?, ?, CURDATE())";
    $credit_stmt = $conn->prepare($credit_sql);
    $credit_stmt->bind_param('ids', $inventory_account_id, $total_amount, $description);
    $credit_stmt->execute();
    
    updateAccountBalance($conn, $inventory_account_id, $total_amount, 'credit');
    
    // Debit based on payment type (reverse the credit)
    if ($payment_type === 'credit') {
        $supplier_account_id = getSupplierPayableAccount($conn, $supplier_id);
        
        $debit_sql = "INSERT INTO ledgers (account_id, debit, credit, description, date) VALUES (?, ?, 0, ?, CURDATE())";
        $debit_stmt = $conn->prepare($debit_sql);
        $debit_stmt->bind_param('ids', $supplier_account_id, $total_amount, $description);
        $debit_stmt->execute();
        
        updateAccountBalance($conn, $supplier_account_id, $total_amount, 'debit');
        
    } else {
        $debit_sql = "INSERT INTO ledgers (account_id, debit, credit, description, date) VALUES (?, ?, 0, ?, CURDATE())";
        $debit_stmt = $conn->prepare($debit_sql);
        $debit_stmt->bind_param('ids', $payment_account_id, $total_amount, $description);
        $debit_stmt->execute();
        
        updateAccountBalance($conn, $payment_account_id, $total_amount, 'debit');
    }
}
 function getSupplierPayableAccount($conn, $supplier_id) {
    // Check if supplier already has a ledger account
    $sql = "SELECT ledger_account_id FROM suppliers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $supplier = $result->fetch_assoc();
    
    if ($supplier && $supplier['ledger_account_id']) {
        return $supplier['ledger_account_id'];
    }
    
    // Get supplier details
    $supplier_sql = "SELECT name as supplier_name FROM suppliers WHERE id = ?";
    $supplier_stmt = $conn->prepare($supplier_sql);
    $supplier_stmt->bind_param('i', $supplier_id);
    $supplier_stmt->execute();
    $supplier_result = $supplier_stmt->get_result();
    $supplier_data = $supplier_result->fetch_assoc();
    
    if (!$supplier_data) {
        throw new Exception("Supplier not found");
    }
    
    $account_name = "Accounts Payable - " . $supplier_data['supplier_name'];
    
    // Check if account already exists with this name
    $checkSql = "SELECT id FROM accounts WHERE name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('s', $account_name);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $existingAccount = $checkResult->fetch_assoc();
        $account_id = $existingAccount['id'];
    } else {
        // Create new account for supplier
        $account_sql = "INSERT INTO accounts (name, type, balance) VALUES (?, 'liability', 0)";
        $account_stmt = $conn->prepare($account_sql);
        $account_stmt->bind_param('s', $account_name);
        
        if (!$account_stmt->execute()) {
            throw new Exception("Error creating supplier account: " . $account_stmt->error);
        }
        
        $account_id = $conn->insert_id;
    }
    
    // Update supplier with new account ID
    $update_sql = "UPDATE suppliers SET ledger_account_id = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ii', $account_id, $supplier_id);
    $update_stmt->execute();
    
    return $account_id;
}

function updateAccountBalance($conn, $account_id, $amount, $type) {
    $operator = ($type === 'debit') ? '+' : '-';
    $sql = "UPDATE accounts SET balance = balance $operator ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('di', $amount, $account_id);
    $stmt->execute();
}
?>