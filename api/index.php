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

// Parse URL to get path and query string
$path = parse_url($requestUri, PHP_URL_PATH);
$queryString = parse_url($requestUri, PHP_URL_QUERY);

// Ensure $_GET is populated from query string (PHP built-in server with router might not auto-populate)
if (!empty($queryString)) {
    parse_str($queryString, $queryParams);
    $_GET = array_merge($_GET, $queryParams);
}

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

// Authentication routes (password reset removed - always use Node.js API)
if (preg_match('#^/api/(login|signup|check-user)$#', $path, $matches)) {
    $endpoint = $matches[1];
    $routes = [
        'login' => 'auth/login.php',
        'signup' => 'auth/signup.php',
        'check-user' => 'auth/check_user.php'
    ];
    if (isset($routes[$endpoint])) {
        require __DIR__ . '/' . $routes[$endpoint];
        exit;
    }
}

// User routes - support both /users and direct file paths
if ($requestMethod === 'GET' && (preg_match('#^/users(/list\.php)?/?$#', $path) || $path === '/users/list.php')) {
    require __DIR__ . '/users/list.php';
    exit;
}

if ($requestMethod === 'GET' && ($path === '/users/estados' || $path === '/users/estados.php')) {
    require __DIR__ . '/users/estados.php';
    exit;
}

if ($requestMethod === 'GET' && ($path === '/users/estatutos' || $path === '/users/estatutos.php')) {
    require __DIR__ . '/users/estatutos.php';
    exit;
}

if (preg_match('#^/users/(\d+)$#', $path, $matches) || $path === '/users/get.php') {
    // Extract ID from path if present
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1];
    }
    if ($requestMethod === 'GET') {
        require __DIR__ . '/users/get.php';
        exit;
    }
    if ($requestMethod === 'PUT' || $path === '/users/update.php') {
        require __DIR__ . '/users/update.php';
        exit;
    }
    if ($requestMethod === 'DELETE' || $path === '/users/delete.php') {
        require __DIR__ . '/users/delete.php';
        exit;
    }
}

if (preg_match('#^/users/(\d+)/password$#', $path, $matches) || $path === '/users/update_password.php') {
    // Extract ID from path if present
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1];
    }
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update_password.php';
        exit;
    }
}

if (preg_match('#^/users/(\d+)/funcao$#', $path, $matches) || $path === '/users/update_funcao.php') {
    // Extract ID from path if present
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1];
    }
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update_funcao.php';
        exit;
    }
}

if (preg_match('#^/users/(\d+)/estado$#', $path, $matches) || $path === '/users/update_estado.php') {
    // Extract ID from path if present
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1];
    }
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update_estado.php';
        exit;
    }
}

// Animal routes - support both /animais and direct file paths
if ($requestMethod === 'GET' && (preg_match('#^/animais(/list\.php)?/?$#', $path) || $path === '/animais/list.php')) {
    require __DIR__ . '/animais/list.php';
    exit;
}

if ($requestMethod === 'POST' && (preg_match('#^/animais(/create\.php)?/?$#', $path) || $path === '/animais/create.php')) {
    require __DIR__ . '/animais/create.php';
    exit;
}

if ($requestMethod === 'GET' && ($path === '/animais/familias' || $path === '/animais/familias.php')) {
    require __DIR__ . '/animais/familias.php';
    exit;
}

if ($requestMethod === 'GET' && ($path === '/animais/estados' || $path === '/animais/estados.php')) {
    require __DIR__ . '/animais/estados.php';
    exit;
}

if (preg_match('#^/animaisDesc/(\d+)$#', $path, $matches) || $path === '/animais/get.php') {
    // Extract ID from path if present
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1];
    }
    if ($requestMethod === 'GET') {
        require __DIR__ . '/animais/get.php';
        exit;
    }
}

if (preg_match('#^/animais/(\d+)$#', $path, $matches) || preg_match('#^/animais/(update|delete)\.php$#', $path, $fileMatches)) {
    // Extract ID from path if present
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1];
    }
    if ($requestMethod === 'PUT' || (isset($fileMatches) && $fileMatches[1] === 'update')) {
        require __DIR__ . '/animais/update.php';
        exit;
    }
    if ($requestMethod === 'DELETE' || (isset($fileMatches) && $fileMatches[1] === 'delete')) {
        require __DIR__ . '/animais/delete.php';
        exit;
    }
}

// Institution routes - support both /instituicoes and /instituicoes/list.php
if ($requestMethod === 'GET' && (preg_match('#^/instituicoes(/list\.php)?/?$#', $path) || $path === '/instituicoes/list.php')) {
    require __DIR__ . '/instituicoes/list.php';
    exit;
}

if ($requestMethod === 'POST' && (preg_match('#^/instituicoes(/create\.php)?/?$#', $path) || $path === '/instituicoes/create.php')) {
    require __DIR__ . '/instituicoes/create.php';
    exit;
}

if (preg_match('#^/instituicoesDesc/(\d+)$#', $path, $matches) || $path === '/instituicoes/get.php') {
    // Extract ID from path if present
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1];
    }
    if ($requestMethod === 'GET') {
        require __DIR__ . '/instituicoes/get.php';
        exit;
    }
}

if (preg_match('#^/instituicoes/(\d+)$#', $path, $matches) || preg_match('#^/instituicoes/(update|delete)\.php$#', $path, $fileMatches)) {
    // Extract ID from path if present
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1];
    }
    if ($requestMethod === 'PUT' || (isset($fileMatches) && $fileMatches[1] === 'update')) {
        require __DIR__ . '/instituicoes/update.php';
        exit;
    }
    if ($requestMethod === 'DELETE' || (isset($fileMatches) && $fileMatches[1] === 'delete')) {
        require __DIR__ . '/instituicoes/delete.php';
        exit;
    }
}

// Alert routes - support both /api/alerts and /alerts/list.php
if ($requestMethod === 'GET' && ($path === '/api/alerts' || $path === '/api/alerts/' || $path === '/alerts/list.php' || preg_match('#^/alerts(/list\.php)?/?$#', $path))) {
    require __DIR__ . '/alerts/list.php';
    exit;
}

if ($requestMethod === 'POST' && ($path === '/api/alerts' || $path === '/api/alerts/' || preg_match('#^/api/alerts/?$#', $path) || $path === '/alerts/create.php')) {
    require __DIR__ . '/alerts/create.php';
    exit;
}

if (preg_match('#^/api/alerts/(\d+)$#', $path, $matches) || $path === '/alerts/delete.php') {
    // Extract ID from path if present
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1];
    }
    if ($requestMethod === 'DELETE') {
        require __DIR__ . '/alerts/delete.php';
        exit;
    }
}

// 404 Not Found
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found', 'path' => $path, 'method' => $requestMethod]);
?>