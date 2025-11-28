<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
include 'db_connection.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. Delete all journal details referencing this account
    $deleteDetails = "DELETE FROM journal_details WHERE account_id = $id";
    mysqli_query($conn, $deleteDetails);

    // 2. Delete all journal entries with no details left
    $deleteEmptyEntries = "
        DELETE j
        FROM journal_entries j
        LEFT JOIN journal_details d ON j.id = d.journal_id
        WHERE d.journal_id IS NULL
    ";
    mysqli_query($conn, $deleteEmptyEntries);

    // 3. Delete the account safely
    $deleteAccount = "DELETE FROM accounts WHERE id = $id";
    mysqli_query($conn, $deleteAccount);

    header('Location: chart_of_accounts.php?msg=Account+deleted');
    exit();
}
?>
