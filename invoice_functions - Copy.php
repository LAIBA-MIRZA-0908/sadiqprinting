<?php
// invoice_functions.php
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
    case 'get_job_orders':
        getJobOrders();
        break;
    case 'get_job_order_details':
        getJobOrderDetails();
        break;
    case 'get_materials':
        getMaterials();
        break;
    case 'create_invoice':
        createInvoice();
        break;
    case 'get_invoices':
        getInvoices();
        break;
    case 'get_invoice_details':
        getInvoiceDetails();
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

function getJobOrders() {
    global $conn;
    
    // Fixed: Using correct table structure without CustomerID
    $query = "SELECT JobOrderNo, CustomerName, OrderDate, DeliveryDate 
              FROM job_orders 
              ORDER BY JobOrderNo DESC";
    $result = $conn->query($query);
    
    if (!$result) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $jobOrders = [];
    while ($row = $result->fetch_assoc()) {
        $jobOrders[] = $row;
    }
    
    sendJsonResponse(true, 'Job orders loaded successfully', [
        'jobOrders' => $jobOrders
    ]);
}

function getJobOrderDetails() {
    global $conn;
    
    $jobOrderNo = isset($_POST['jobOrderNo']) ? (int)$_POST['jobOrderNo'] : 0;
    
    if ($jobOrderNo <= 0) {
        sendJsonResponse(false, 'Invalid job order number');
    }
    
    // Get job order
    $orderQuery = "SELECT JobOrderNo, CustomerName, OrderDate, DeliveryDate, DesignBy, JobFor, CellNo, Designer, AdvancePayment 
                  FROM job_orders 
                  WHERE JobOrderNo = ?";
    
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $jobOrderNo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendJsonResponse(false, 'Job order not found');
    }
    
    $order = $result->fetch_assoc();
    
    // Get job order details
    $detailsQuery = "SELECT SrNo, Detail, Media, Width, Height, Qty, Sqft, Ring, Pocket 
                    FROM job_order_details 
                    WHERE JobOrderNo = ? 
                    ORDER BY SrNo";
    
    $stmtDetails = $conn->prepare($detailsQuery);
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

function getMaterials() {
    global $conn;
    
    // Fixed: Using correct table name and fields
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

function createInvoice() {
    global $conn;

    $InvoiceNo = trim($_POST['InvoiceNo'] ?? '');
    $InvoiceDate = $_POST['InvoiceDate'] ?? '';
    $PONo = $_POST['PONo'] ?? null;
    $CustomerID = (int)($_POST['CustomerID'] ?? 0);
    $CustomerName = trim($_POST['CustomerName'] ?? '');
    $InvoiceSubject = trim($_POST['InvoiceSubject'] ?? null);
    $SubTotal = floatval($_POST['SubTotal'] ?? 0);
    $Advance = floatval($_POST['Advance'] ?? 0);
    $GSTRate = floatval($_POST['GSTRate'] ?? 0);
    $TotalGST = floatval($_POST['TotalGST'] ?? 0);
    $NTRRate = floatval($_POST['NTRRate'] ?? 0);
    $TotalNTR = floatval($_POST['TotalNTR'] ?? 0);
    $GrandTotal = floatval($_POST['GrandTotal'] ?? 0);
    $items = $_POST['items'] ?? [];

    // Validate required fields
    if (empty($InvoiceNo) || empty($InvoiceDate) || $CustomerID <= 0) {
        sendJsonResponse(false, 'Please fill in all required fields');
    }

    if (empty($items)) {
        sendJsonResponse(false, 'Please add at least one item to the invoice');
    }

    $conn->begin_transaction();

    try {
        // 1️⃣ Insert invoice
        $insertQuery = "INSERT INTO tblinvoices 
            (InvoiceNo, InvoiceDate, PONo, CustomerID, CustomerName, InvoiceSubject, 
            SubTotal, Advance, GSTRate, TotalGST, NTRRate, TotalNTR, GrandTotal) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertQuery);
        if (!$stmt) throw new Exception('Prepare failed for invoice: ' . $conn->error);
        
        $stmt->bind_param("sssisssdddddd", $InvoiceNo, $InvoiceDate, $PONo, $CustomerID, $CustomerName,
                          $InvoiceSubject, $SubTotal, $Advance, $GSTRate, $TotalGST, $NTRRate, $TotalNTR, $GrandTotal);
        if (!$stmt->execute()) throw new Exception('Failed to insert invoice: ' . $stmt->error);

        $invoiceId = $conn->insert_id;

        // 2️⃣ Insert invoice items
        foreach ($items as $item) {
            $insertItemQuery = "INSERT INTO tblinvoice_details 
                (InvoiceID, JobNo, Detail, Media, Width, Height, Qty, Sqft, Rate, Total) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtItem = $conn->prepare($insertItemQuery);
            if (!$stmtItem) throw new Exception('Prepare failed for invoice item: ' . $conn->error);

            $stmtItem->bind_param("isssdddddd", $invoiceId, $item['JobNo'], $item['Detail'], $item['Media'],
                                  $item['Width'], $item['Height'], $item['Qty'], $item['Sqft'], $item['Rate'], $item['Total']);
            if (!$stmtItem->execute()) throw new Exception('Failed to insert invoice item: ' . $stmtItem->error);
        }

        // ✅ Commit invoice before journal
        $conn->commit();
$invoiceData = [
    'InvoiceID' => $invoiceId,
    'InvoiceNo' => $InvoiceNo,
    'CustomerName' => $CustomerName,
    'Customerid' =>  $CustomerID,
    'GrandTotal' => $GrandTotal,
    'GSTAmount' => 0,
    'NTNAmount' => 0,
    'InvoiceDate' => $InvoiceDate
];
 // 🧹 Capture any echoes to avoid breaking JSON
        ob_start();
        $journalResult = createInvoiceJournalEntry($invoiceData);
        $debugLog = ob_get_clean();
        error_log("Journal debug:\n" . $debugLog);

        if (!$journalResult['success']) {
            error_log("⚠️ Journal entry failed for Invoice #{$InvoiceNo}: " . $journalResult['message']);
        }


        // 4️⃣ Respond after everything completes
      sendJsonResponse(true, 'Invoice created successfully', [
           'InvoiceID' => $invoiceId,
           'InvoiceNo' => $InvoiceNo,
          'JournalStatus' => $journalResult['success'] ? 'Created' : 'Failed'
       ]);

    } catch (Exception $e) {
        $conn->rollback();
        sendJsonResponse(false, 'Error: ' . $e->getMessage());
    }
}


function getInvoices() {
    global $conn;
    
    $query = "SELECT i.InvoiceID, i.InvoiceNo, i.InvoiceDate, i.CustomerName, i.GrandTotal, i.Status, i.PONo,
                     c.Phone, c.Email 
              FROM tblinvoices i
              LEFT JOIN tblcustomers c ON i.CustomerID = c.CustomerID
              ORDER BY i.InvoiceID DESC";
    $result = $conn->query($query);
    
    if (!$result) {
        sendJsonResponse(false, 'Database error: ' . $conn->error);
    }
    
    $invoices = [];
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
    
    sendJsonResponse(true, 'Invoices loaded successfully', [
        'invoices' => $invoices
    ]);
}

function getInvoiceDetails() {
    global $conn;
    
    $invoiceId = isset($_POST['invoiceId']) ? (int)$_POST['invoiceId'] : 0;
    
    if ($invoiceId <= 0) {
        sendJsonResponse(false, 'Invalid invoice ID');
    }
    
    // Get invoice details
    $invoiceQuery = "SELECT i.*, c.Phone, c.Email, c.Address 
                     FROM tblinvoices i
                     LEFT JOIN tblcustomers c ON i.CustomerID = c.CustomerID
                     WHERE i.InvoiceID = ?";
    
    $stmt = $conn->prepare($invoiceQuery);
    $stmt->bind_param("i", $invoiceId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendJsonResponse(false, 'Invoice not found');
    }
    
    $invoice = $result->fetch_assoc();
    
    // Get invoice items
    $itemsQuery = "SELECT * FROM tblinvoice_details WHERE InvoiceID = ? ORDER BY DetailID";
    $stmtItems = $conn->prepare($itemsQuery);
    $stmtItems->bind_param("i", $invoiceId);
    $stmtItems->execute();
    $itemsResult = $stmtItems->get_result();
    
    $items = [];
    while ($row = $itemsResult->fetch_assoc()) {
        $items[] = $row;
    }
    
    sendJsonResponse(true, 'Invoice details loaded successfully', [
        'invoice' => $invoice,
        'items' => $items
    ]);
}

// ==========================
// Journal Entry Function
// ==========================
function createInvoiceJournalEntry($invoiceData)
{
    global $conn;

    echo "<pre>🟢 Inside createInvoiceJournalEntry()</pre>";

    // --- Static Accounts ---
    $CASH_IN_HAND_ID    = 1;   // Cash / Bank
    $SALES_REVENUE_ID   = 17;  // Sales Revenue
    $GST_PAYABLE_ID     = 25;  // GST Payable
    $NTN_PAYABLE_ID     = 26;  // NTN Payable

    // --- Invoice Data ---
    $InvoiceNo     = $invoiceData['InvoiceNo'];
    $InvoiceDate   = $invoiceData['InvoiceDate'];
    $CustomerID    = $invoiceData['Customerid'];
    $CustomerName  = $invoiceData['CustomerName'];
    $GrandTotal    = floatval($invoiceData['GrandTotal']);
    $PaidAmount    = floatval($invoiceData['PaidAmount'] ?? 0);
    $GSTAmount     = floatval($invoiceData['GSTAmount'] ?? 0);
    $NTNAmount     = floatval($invoiceData['NTNAmount'] ?? 0);

    $description = "Sales journal entry for Invoice #{$InvoiceNo} ({$CustomerName})";

    echo "<pre>🧾 Invoice: {$InvoiceNo}, CustomerID: {$CustomerID}, Total: {$GrandTotal}, Paid: {$PaidAmount}</pre>";

    // --- Fetch Customer Account ID (Accounts Receivable) ---
    $customerAccountID = null;
    $custQuery = "SELECT account_id FROM tblcustomers WHERE CustomerID = ?";
    $stmtCust = $conn->prepare($custQuery);
    if ($stmtCust) {
        $stmtCust->bind_param("i", $CustomerID);
        $stmtCust->execute();
        $stmtCust->bind_result($customerAccountID);
        $stmtCust->fetch();
        $stmtCust->close();
    }

    if (!$customerAccountID) {
        echo "<pre>⚠️ No account_id found for CustomerID {$CustomerID}</pre>";
        $customerAccountID = 0; // fallback to 0 or handle as needed
    } else {
        echo "<pre>✅ Customer Account ID: {$customerAccountID}</pre>";
    }

    // --- Build Journal Entries ---
    $journal_details = [];

    // CASE 1️⃣ Full Cash Sale
    if ($PaidAmount >= $GrandTotal) {
        $journal_details[] = ['account_id' => $CASH_IN_HAND_ID, 'debit' => $GrandTotal, 'credit' => 0];
        $journal_details[] = ['account_id' => $SALES_REVENUE_ID, 'debit' => 0, 'credit' => $GrandTotal];
    }

    // CASE 2️⃣ Full Credit Sale
    elseif ($PaidAmount <= 0) {
        $journal_details[] = ['account_id' => $customerAccountID, 'debit' => $GrandTotal, 'credit' => 0];
        $journal_details[] = ['account_id' => $SALES_REVENUE_ID, 'debit' => 0, 'credit' => $GrandTotal];
    }

    // CASE 3️⃣ Partial Payment
    else {
        $balance = $GrandTotal - $PaidAmount;
        $journal_details[] = ['account_id' => $CASH_IN_HAND_ID, 'debit' => $PaidAmount, 'credit' => 0];
        $journal_details[] = ['account_id' => $customerAccountID, 'debit' => $balance, 'credit' => 0];
        $journal_details[] = ['account_id' => $SALES_REVENUE_ID, 'debit' => 0, 'credit' => $GrandTotal];
    }

    // Add GST and NTN if applicable
    if ($GSTAmount > 0) {
        $journal_details[] = ['account_id' => $GST_PAYABLE_ID, 'debit' => 0, 'credit' => $GSTAmount];
    }
    if ($NTNAmount > 0) {
        $journal_details[] = ['account_id' => $NTN_PAYABLE_ID, 'debit' => 0, 'credit' => $NTNAmount];
    }

    echo "<pre>📊 Journal Details:\n" . print_r($journal_details, true) . "</pre>";

    // --- Insert into journal tables ---
    try {
        $query = "INSERT INTO journal_entries (entry_date, description, reference_no)
                  VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("sss", $InvoiceDate, $description, $InvoiceNo);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

        $journal_id = $conn->insert_id;
        echo "<pre>✅ Journal Entry Created (ID: {$journal_id})</pre>";

        $detailQuery = "INSERT INTO journal_details (journal_id, account_id, debit, credit)
                        VALUES (?, ?, ?, ?)";
        $stmt_detail = $conn->prepare($detailQuery);
        if (!$stmt_detail) throw new Exception("Prepare failed (details): " . $conn->error);

        foreach ($journal_details as $i => $d) {
            echo "<pre>➡️ Detail #{$i}: " . print_r($d, true) . "</pre>";
            $stmt_detail->bind_param("iidd", $journal_id, $d['account_id'], $d['debit'], $d['credit']);
            if (!$stmt_detail->execute()) throw new Exception("Detail insert failed: " . $stmt_detail->error);
        }

        echo "<pre>✅ All journal details inserted successfully!</pre>";
        return ['success' => true, 'message' => 'Journal created successfully'];

    } catch (Exception $e) {
        echo "<pre>❌ Journal entry failed: {$e->getMessage()}</pre>";
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


?>