<?php
/**
 * Helper functions for PHP API
 */

/**
 * Set CORS headers
 */
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json');
}

/**
 * Handle preflight OPTIONS requests
 */
function handlePreflight() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

/**
 * Get JSON input from request body
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

/**
 * Send JSON response
 */
function sendJson($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

/**
 * Send error response
 */
function sendError($message, $statusCode = 500) {
    sendJson(['error' => $message], $statusCode);
}

/**
 * Hash password using SHA256 (matching Node.js implementation)
 */
function hashPassword($password) {
    return hash('sha256', $password);
}

/**
 * Validate required fields
 */
function validateRequired($data, $fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        sendError('Campos obrigatórios: ' . implode(', ', $missing), 400);
    }
}

/**
 * Get query parameter
 */
function getQueryParam($name, $default = null) {
    return $_GET[$name] ?? $default;
}
?>
