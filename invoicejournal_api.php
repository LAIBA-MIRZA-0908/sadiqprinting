<?php
// journal_api.php
// Reusable accounting journal entry module
// Do NOT send headers or exit — just define functions
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
function createInvoiceJournalEntry($conn, $invoiceData) {
    // Extract invoice fields
    $invoiceId     = $invoiceData['InvoiceID'] ?? 0;
    $invoiceNo     = $invoiceData['InvoiceNo'] ?? '';
    $customerName  = $invoiceData['CustomerName'] ?? '';
    $grandTotal    = floatval($invoiceData['GrandTotal'] ?? 0);
    $invoiceDate   = $invoiceData['InvoiceDate'] ?? date('Y-m-d');
    $gstAmount     = floatval($invoiceData['GSTAmount'] ?? 0);
    $ntnAmount     = floatval($invoiceData['NTNAmount'] ?? 0);

    if (empty($invoiceId) || empty($invoiceNo) || $grandTotal <= 0) {
        return ['success' => false, 'message' => 'Missing or invalid invoice data'];
    }

    // 🔢 Chart of Accounts IDs — adjust as per your actual setup
    $salesAccount        = 17; // Sales Revenue (I400)
    $receivableAccount   = 3;  // Accounts Receivable (A102)
    $gstPayableAccount   = 25; // GST Payable (L201)
    $ntnPayableAccount   = 26; // NTN Payable (L202)

    // Begin transaction
    $conn->begin_transaction();

    try {
        $desc = "Invoice #$invoiceNo - $customerName";

        // 1️⃣ Debit Accounts Receivable (total invoice)
        $query = "INSERT INTO journal_entries 
            (entry_date, description, account_id, debit, credit, reference_no, reference_type)
            VALUES (?, ?, ?, ?, 0, ?, 'Invoice')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssids", $invoiceDate, $desc, $receivableAccount, $grandTotal, $invoiceNo);
        if (!$stmt->execute()) throw new Exception($stmt->error);

        // 2️⃣ Credit Sales Revenue (exclusive of taxes)
        $salesBase = $grandTotal - $gstAmount - $ntnAmount;
        if ($salesBase < 0) $salesBase = 0;

        $query = "INSERT INTO journal_entries 
            (entry_date, description, account_id, debit, credit, reference_no, reference_type)
            VALUES (?, ?, ?, 0, ?, ?, 'Invoice')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssids", $invoiceDate, $desc, $salesAccount, $salesBase, $invoiceNo);
        if (!$stmt->execute()) throw new Exception($stmt->error);

        // 3️⃣ Credit GST Payable (if applicable)
        if ($gstAmount > 0) {
            $query = "INSERT INTO journal_entries 
                (entry_date, description, account_id, debit, credit, reference_no, reference_type)
                VALUES (?, ?, ?, 0, ?, ?, 'Invoice')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssids", $invoiceDate, $desc, $gstPayableAccount, $gstAmount, $invoiceNo);
            if (!$stmt->execute()) throw new Exception($stmt->error);
        }

        // 4️⃣ Credit NTN Payable (if applicable)
        if ($ntnAmount > 0) {
            $query = "INSERT INTO journal_entries 
                (entry_date, description, account_id, debit, credit, reference_no, reference_type)
                VALUES (?, ?, ?, 0, ?, ?, 'Invoice')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssids", $invoiceDate, $desc, $ntnPayableAccount, $ntnAmount, $invoiceNo);
            if (!$stmt->execute()) throw new Exception($stmt->error);
        }

        // ✅ Update account balances
        $conn->query("UPDATE accounts SET balance = balance + $grandTotal WHERE id = $receivableAccount");
        if ($salesBase > 0) $conn->query("UPDATE accounts SET balance = balance - $salesBase WHERE id = $salesAccount");
        if ($gstAmount > 0) $conn->query("UPDATE accounts SET balance = balance + $gstAmount WHERE id = $gstPayableAccount");
        if ($ntnAmount > 0) $conn->query("UPDATE accounts SET balance = balance + $ntnAmount WHERE id = $ntnPayableAccount");

        $conn->commit();

        return [
            'success' => true,
            'message' => 'Journal entry created successfully',
            'invoice_no' => $invoiceNo,
            'gst' => $gstAmount,
            'ntn' => $ntnAmount
        ];

    } catch (Exception $e) {
        $conn->rollback();
        return [
            'success' => false,
            'message' => 'Error creating journal entry: ' . $e->getMessage()
        ];
    }
}
?>
