<?php
/**
 * PHP API Entry Point for Railway
 * Router for PHP built-in server
 */

require_once __DIR__ . '/config/helpers.php';

setCorsHeaders();
handlePreflight();

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

// List endpoints
if ($path === '/list-endpoints.php' || $path === '/list-endpoints') {
    require __DIR__ . '/list-endpoints.php';
    exit;
}

// Route API endpoints
$routes = [];

// Authentication routes
if (preg_match('#^/api/(login|signup|check-user|forgot-password|reset-password)$#', $path, $matches)) {
    $endpoint = $matches[1];
    $routes = [
        'login' => 'auth/login.php',
        'signup' => 'auth/signup.php',
        'check-user' => 'auth/check_user.php',
        'forgot-password' => 'auth/forgot_password.php',
        'reset-password' => 'auth/reset_password.php'
    ];
    if (isset($routes[$endpoint])) {
        $file = __DIR__ . '/' . $routes[$endpoint];
        if (file_exists($file)) {
            require $file;
            exit;
        }
    }
}

// User routes
if ($requestMethod === 'GET' && $path === '/users') {
    require __DIR__ . '/users/list.php';
    exit;
}

if ($requestMethod === 'GET' && $path === '/users/estados') {
    require __DIR__ . '/users/estados.php';
    exit;
}

if ($requestMethod === 'GET' && $path === '/users/estatutos') {
    require __DIR__ . '/users/estatutos.php';
    exit;
}

if (preg_match('#^/users/(\d+)$#', $path, $matches)) {
    $id = $matches[1];
    if ($requestMethod === 'GET') {
        $_GET['id'] = $id; // Pass ID as query param for get.php
        require __DIR__ . '/users/get.php';
        exit;
    }
    // PUT and DELETE will be handled by update.php and delete.php when created
}

// For routes with parameters in path (e.g., /users/123/password)
// We'll need to parse them and pass as query params or create specific handlers
// This is a simplified router - for production, consider using a routing library

// 404 Not Found
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found', 'path' => $path, 'method' => $requestMethod]);
?>