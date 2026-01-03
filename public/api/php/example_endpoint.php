<?php
/**
 * Example PHP API Endpoint
 * 
 * This demonstrates the structure of PHP API endpoints that mirror Node.js endpoints.
 * 
 * NOTE: This won't work on Hostinger because PostgreSQL extension is not available.
 * However, you can show this code in your school project to demonstrate PHP/PDO knowledge.
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration (would need PostgreSQL extension)
// require_once __DIR__ . '/../../config/database.php';

try {
    // Example: GET endpoint
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // This is just an example - actual implementation would use PDO
        /*
        $sql = 'SELECT * FROM table_name WHERE condition = :param';
        $results = executeQuery($sql, [':param' => $value]);
        */
        
        echo json_encode([
            'message' => 'This is an example PHP endpoint',
            'note' => 'Requires PostgreSQL PDO extension to work',
            'method' => 'GET'
        ]);
        exit;
    }
    
    // Example: POST endpoint
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!isset($input['required_field'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required field']);
            exit;
        }
        
        // Process and save (would use PDO here)
        /*
        $sql = 'INSERT INTO table_name (field) VALUES (:value)';
        $id = executeInsert($sql, [':value' => $input['required_field']]);
        */
        
        http_response_code(201);
        echo json_encode([
            'message' => 'Created successfully',
            'id' => 123 // Example ID
        ]);
        exit;
    }
    
    // Method not allowed
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
