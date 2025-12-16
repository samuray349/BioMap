<?php
/**
 * Set PHP session from JavaScript
 * Called after successful login to set session on server side
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'session_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['user']) || !is_array($input['user'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid user data']);
        exit();
    }
    
    // Set the session
    setUserSession($input['user']);
    
    echo json_encode(['success' => true, 'message' => 'Session set successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to set session: ' . $e->getMessage()]);
}
