<?php
// test_db_connection.php
// Set JSON header
header('Content-Type: application/json');

try {
    include 'db_connection.php';
    
    if (!$conn) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
    } else {
        // Test a simple query
        $result = $conn->query("SELECT 1");
        if ($result) {
            // Check if tblmaterials exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'tblmaterials'");
            $tableExists = ($tableCheck && $tableCheck->num_rows > 0);
            
            echo json_encode([
                'success' => true,
                'message' => 'Database connection successful',
                'table_exists' => $tableExists
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Query test failed: ' . $conn->error
            ]);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
}
?>