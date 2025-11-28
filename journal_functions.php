<?php
require_once 'db_connection.php';

/**
 * Creates a new journal entry with its details in a single transaction.
 *
 * @param array $data Contains 'entry_date', 'description', 'reference_no', and 'details' (an array of rows).
 * @return bool True on success, false on failure.
 */
function createJournalEntry(array $data): bool
{
    global $conn;

    // Basic validation
    if (empty($data['entry_date']) || empty($data['details'])) {
        return false;
    }

    mysqli_begin_transaction($conn);

    try {
        // 1. Insert into journal_entries
        $stmt = mysqli_prepare($conn, "INSERT INTO journal_entries (entry_date, description, reference_no, created_by) VALUES (?, ?, ?, ?)");
        // Assuming a user ID of 1 for now; replace with session user ID later
        $created_by = 1; 
        mysqli_stmt_bind_param($stmt, "sssi", $data['entry_date'], $data['description'], $data['reference_no'], $created_by);
        mysqli_stmt_execute($stmt);
        
        $journal_id = mysqli_insert_id($conn);

        // 2. Insert each detail into journal_details
        // 2. Insert each detail into journal_details
        $stmt_detail = mysqli_prepare($conn, "INSERT INTO journal_details (journal_id, account_id, debit, credit) VALUES (?, ?, ?, ?)");
        foreach ($data['details'] as $row) {
            // Ensure one of debit or credit is set and cast to float
            $debit = (float) (!empty($row['debit']) ? $row['debit'] : 0.00);
            $credit = (float) (!empty($row['credit']) ? $row['credit'] : 0.00);
            
            mysqli_stmt_bind_param($stmt_detail, "iddd", $journal_id, $row['account_id'], $debit, $credit);
            mysqli_stmt_execute($stmt_detail);
        }

        // If all inserts were successful, commit the transaction
        mysqli_commit($conn);
        return true;

    } catch (Exception $e) {
        // An error occurred, roll back the transaction
        mysqli_rollback($conn);
        // In a real application, you would log this error
        // error_log($e->getMessage());
        return false;
    }
}

/**
 * Fetches all journal entries with their total debit and credit.
 *
 * @return array The list of journal entries.
 */
function getAllJournalEntries(): array
{
    global $conn;
    $query = "
        SELECT 
            je.id, 
            je.entry_date, 
            je.reference_no, 
            je.description,
            SUM(jd.debit) AS total_debit,
            SUM(jd.credit) AS total_credit
        FROM journal_entries je
        LEFT JOIN journal_details jd ON je.id = jd.journal_id
        GROUP BY je.id
        ORDER BY je.entry_date DESC, je.id DESC
    ";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Deletes a journal entry and all its associated details.
 *
 * @param int $id The ID of the journal entry to delete.
 * @return bool True on success, false on failure.
 */
function deleteJournalEntry(int $id): bool
{
    global $conn;
    mysqli_begin_transaction($conn);

    try {
        // Delete from details first due to foreign key constraint
        $stmt_details = mysqli_prepare($conn, "DELETE FROM journal_details WHERE journal_id = ?");
        mysqli_stmt_bind_param($stmt_details, "i", $id);
        mysqli_stmt_execute($stmt_details);

        // Then delete from the main table
        $stmt_entry = mysqli_prepare($conn, "DELETE FROM journal_entries WHERE id = ?");
        mysqli_stmt_bind_param($stmt_entry, "i", $id);
        mysqli_stmt_execute($stmt_entry);
        
        mysqli_commit($conn);
        return true;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

/**
 * Fetches all accounts for use in dropdowns.
 *
 * @return array The list of accounts.
 */
function getAccountsForDropdown(): array
{
    global $conn;
    $query = "SELECT id, code, name FROM accounts ORDER BY code";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}