<?php
session_start();
require_once 'journal_functions.php';

header('Content-Type: application/json');

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit();
}

 $action = $_POST['action'] ?? '';

if ($action === 'create') {
    $entry_date = $_POST['entry_date'] ?? '';
    $description = $_POST['description'] ?? '';
    $reference_no = $_POST['reference_no'] ?? '';
    
    // --- THE FIX IS HERE ---
    // Decode the JSON string from the frontend into a PHP array
    $details_json = $_POST['details'] ?? '[]';
    $details = json_decode($details_json, true); // `true` makes it an associative array

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid data format for details.']);
        exit;
    }

    $entryData = [
        'entry_date' => $entry_date,
        'description' => $description,
        'reference_no' => $reference_no,
        'details' => $details
    ];

    if (createJournalEntry($entryData)) {
        echo json_encode(['success' => true, 'message' => 'Journal entry saved successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save journal entry. Please ensure debits equal credits and all fields are valid.']);
    }

} elseif ($action === 'delete') {
    $journal_id = intval($_POST['journal_id'] ?? 0);
    if ($journal_id > 0 && deleteJournalEntry($journal_id)) {
        echo json_encode(['success' => true, 'message' => 'Journal entry deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete entry.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}