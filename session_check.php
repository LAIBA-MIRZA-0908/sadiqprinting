<?php
// session_check.php
// --------------------------------------
// Purpose: To include in every page to protect routes
// --------------------------------------

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Make session values available globally
$user_id      = $_SESSION['user_id'];
$user_email   = $_SESSION['email'] ?? '';
$user_name    = $_SESSION['full_name'] ?? '';
$user_role_id = $_SESSION['role_id'] ?? '';
$user_role    = $_SESSION['role_name'] ?? '';

// Optional: make them truly global across includes
$GLOBALS['USER_ID']      = $user_id;
$GLOBALS['USER_EMAIL']   = $user_email;
$GLOBALS['USER_NAME']    = $user_name;
$GLOBALS['USER_ROLE_ID'] = $user_role_id;
$GLOBALS['USER_ROLE']    = $user_role;

// Optionally, you can log session activity for debugging
// error_log("Session active for: " . $user_name . " (ID: " . $user_id . ")");
?>
