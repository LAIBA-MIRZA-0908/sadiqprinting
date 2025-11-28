<?php
// test_material_api.php
// Set JSON header
header('Content-Type: application/json');

// Test response
echo json_encode([
    'success' => true,
    'message' => 'API test successful',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>