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
        require __DIR__ . '/' . $routes[$endpoint];
        exit;
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
    $_GET['id'] = $id;
    if ($requestMethod === 'GET') {
        require __DIR__ . '/users/get.php';
        exit;
    }
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update.php';
        exit;
    }
    if ($requestMethod === 'DELETE') {
        require __DIR__ . '/users/delete.php';
        exit;
    }
}

if (preg_match('#^/users/(\d+)/password$#', $path, $matches)) {
    $id = $matches[1];
    $_GET['id'] = $id;
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update_password.php';
        exit;
    }
}

if (preg_match('#^/users/(\d+)/funcao$#', $path, $matches)) {
    $id = $matches[1];
    $_GET['id'] = $id;
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update_funcao.php';
        exit;
    }
}

if (preg_match('#^/users/(\d+)/estado$#', $path, $matches)) {
    $id = $matches[1];
    $_GET['id'] = $id;
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update_estado.php';
        exit;
    }
}

// Animal routes
if ($requestMethod === 'GET' && $path === '/animais') {
    require __DIR__ . '/animais/list.php';
    exit;
}

if ($requestMethod === 'POST' && $path === '/animais') {
    require __DIR__ . '/animais/create.php';
    exit;
}

if ($requestMethod === 'GET' && $path === '/animais/familias') {
    require __DIR__ . '/animais/familias.php';
    exit;
}

if ($requestMethod === 'GET' && $path === '/animais/estados') {
    require __DIR__ . '/animais/estados.php';
    exit;
}

if (preg_match('#^/animaisDesc/(\d+)$#', $path, $matches)) {
    $id = $matches[1];
    $_GET['id'] = $id;
    if ($requestMethod === 'GET') {
        require __DIR__ . '/animais/get.php';
        exit;
    }
}

if (preg_match('#^/animais/(\d+)$#', $path, $matches)) {
    $id = $matches[1];
    $_GET['id'] = $id;
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/animais/update.php';
        exit;
    }
    if ($requestMethod === 'DELETE') {
        require __DIR__ . '/animais/delete.php';
        exit;
    }
}

// Institution routes
if ($requestMethod === 'GET' && $path === '/instituicoes') {
    require __DIR__ . '/instituicoes/list.php';
    exit;
}

if ($requestMethod === 'POST' && $path === '/instituicoes') {
    require __DIR__ . '/instituicoes/create.php';
    exit;
}

if (preg_match('#^/instituicoesDesc/(\d+)$#', $path, $matches)) {
    $id = $matches[1];
    $_GET['id'] = $id;
    if ($requestMethod === 'GET') {
        require __DIR__ . '/instituicoes/get.php';
        exit;
    }
}

if (preg_match('#^/instituicoes/(\d+)$#', $path, $matches)) {
    $id = $matches[1];
    $_GET['id'] = $id;
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/instituicoes/update.php';
        exit;
    }
    if ($requestMethod === 'DELETE') {
        require __DIR__ . '/instituicoes/delete.php';
        exit;
    }
}

// Alert routes
if ($requestMethod === 'GET' && $path === '/api/alerts') {
    require __DIR__ . '/alerts/list.php';
    exit;
}

if ($requestMethod === 'POST' && $path === '/api/alerts') {
    require __DIR__ . '/alerts/create.php';
    exit;
}

if (preg_match('#^/api/alerts/(\d+)$#', $path, $matches)) {
    $id = $matches[1];
    $_GET['id'] = $id;
    if ($requestMethod === 'DELETE') {
        require __DIR__ . '/alerts/delete.php';
        exit;
    }
}

// 404 Not Found
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found', 'path' => $path, 'method' => $requestMethod]);
?>