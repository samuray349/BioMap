<?php
/**
 * PHP API Entry Point for Railway
 * Simple router for PHP built-in server
 */

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Health check
if ($path === '/health' || $path === '/health.php') {
    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'service' => 'PHP API',
        'timestamp' => date('c')
    ]);
    exit;
}

// Route to API endpoints
// This is a basic router - you may want to use a proper routing library
// For now, this demonstrates the structure

// Example: /api/login -> auth/login.php
if (preg_match('#^/api/(.*)$#', $path, $matches)) {
    $endpoint = $matches[1];
    
    // Map endpoints to files
    $routes = [
        'login' => 'auth/login.php',
        'signup' => 'auth/signup.php',
        'check-user' => 'auth/check_user.php'
    ];
    
    if (isset($routes[$endpoint])) {
        $file = __DIR__ . '/' . $routes[$endpoint];
        if (file_exists($file)) {
            require $file;
            exit;
        }
    }
}

// 404 Not Found
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
