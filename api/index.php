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

// User routes - support both /users and direct file paths
if ($requestMethod === 'GET' && (preg_match('#^/users(/list\.php)?/?$#', $path) || $path === '/users' || $path === '/users/list.php' || $path === '/users/')) {
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

if (preg_match('#^/users/(\d+)$#', $path, $matches) || preg_match('#^/users/get\.php$#', $path)) {
    // For direct file access, ID should be in query string
    if (preg_match('#^/users/(\d+)$#', $path, $matches)) {
        $id = $matches[1];
        $_GET['id'] = $id;
    }
    if ($requestMethod === 'GET') {
        require __DIR__ . '/users/get.php';
        exit;
    }
    if ($requestMethod === 'PUT' || preg_match('#^/users/update\.php$#', $path)) {
        require __DIR__ . '/users/update.php';
        exit;
    }
    if ($requestMethod === 'DELETE' || preg_match('#^/users/delete\.php$#', $path)) {
        require __DIR__ . '/users/delete.php';
        exit;
    }
}

if (preg_match('#^/users/(\d+)/password$#', $path, $matches) || preg_match('#^/users/update_password\.php$#', $path)) {
    // For direct file access, ID should be in query string
    if (preg_match('#^/users/(\d+)/password$#', $path, $matches)) {
        $id = $matches[1];
        $_GET['id'] = $id;
    }
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update_password.php';
        exit;
    }
}

if (preg_match('#^/users/(\d+)/funcao$#', $path, $matches) || preg_match('#^/users/update_funcao\.php$#', $path)) {
    // For direct file access, ID should be in query string
    if (preg_match('#^/users/(\d+)/funcao$#', $path, $matches)) {
        $id = $matches[1];
        $_GET['id'] = $id;
    }
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update_funcao.php';
        exit;
    }
}

if (preg_match('#^/users/(\d+)/estado$#', $path, $matches) || preg_match('#^/users/update_estado\.php$#', $path)) {
    // For direct file access, ID should be in query string
    if (preg_match('#^/users/(\d+)/estado$#', $path, $matches)) {
        $id = $matches[1];
        $_GET['id'] = $id;
    }
    if ($requestMethod === 'PUT') {
        require __DIR__ . '/users/update_estado.php';
        exit;
    }
}

// Animal routes - support both /animais and direct file paths
if ($requestMethod === 'GET' && (preg_match('#^/animais(/list\.php)?/?$#', $path) || $path === '/animais' || $path === '/animais/list.php' || $path === '/animais/')) {
    require __DIR__ . '/animais/list.php';
    exit;
}

if ($requestMethod === 'POST' && (preg_match('#^/animais(/create\.php)?/?$#', $path) || $path === '/animais' || $path === '/animais/create.php' || $path === '/animais/')) {
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

if (preg_match('#^/animaisDesc/(\d+)$#', $path, $matches) || preg_match('#^/animais/get\.php$#', $path)) {
    // For direct file access, ID should be in query string
    if (preg_match('#^/animaisDesc/(\d+)$#', $path, $matches)) {
        $id = $matches[1];
        $_GET['id'] = $id;
    }
    if ($requestMethod === 'GET') {
        require __DIR__ . '/animais/get.php';
        exit;
    }
}

if (preg_match('#^/animais/(\d+)$#', $path, $matches) || preg_match('#^/animais/(update|delete)\.php$#', $path, $fileMatches)) {
    // For direct file access, ID should be in query string
    if (preg_match('#^/animais/(\d+)$#', $path, $matches)) {
        $id = $matches[1];
        $_GET['id'] = $id;
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
if ($requestMethod === 'GET' && (preg_match('#^/instituicoes(/list\.php)?/?$#', $path) || $path === '/instituicoes' || $path === '/instituicoes/list.php' || $path === '/instituicoes/')) {
    require __DIR__ . '/instituicoes/list.php';
    exit;
}

if ($requestMethod === 'POST' && (preg_match('#^/instituicoes(/create\.php)?/?$#', $path) || $path === '/instituicoes' || $path === '/instituicoes/create.php' || $path === '/instituicoes/')) {
    require __DIR__ . '/instituicoes/create.php';
    exit;
}

if (preg_match('#^/instituicoesDesc/(\d+)$#', $path, $matches) || preg_match('#^/instituicoes/get\.php$#', $path)) {
    // For direct file access, ID should be in query string
    if (preg_match('#^/instituicoesDesc/(\d+)$#', $path, $matches)) {
        $id = $matches[1];
        $_GET['id'] = $id;
    }
    if ($requestMethod === 'GET') {
        require __DIR__ . '/instituicoes/get.php';
        exit;
    }
}

if (preg_match('#^/instituicoes/(\d+)$#', $path, $matches) || preg_match('#^/instituicoes/(update|delete)\.php$#', $path, $fileMatches)) {
    // For direct file access, ID should be in query string
    if (preg_match('#^/instituicoes/(\d+)$#', $path, $matches)) {
        $id = $matches[1];
        $_GET['id'] = $id;
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
// Handle direct file path access
if ($requestMethod === 'GET') {
    if ($path === '/api/alerts' || $path === '/api/alerts/' || $path === '/alerts/list.php' || preg_match('#^/alerts(/list\.php)?/?$#', $path)) {
        require __DIR__ . '/alerts/list.php';
        exit;
    }
}

if ($requestMethod === 'POST' && ($path === '/api/alerts' || $path === '/alerts/create.php')) {
    require __DIR__ . '/alerts/create.php';
    exit;
}

if (preg_match('#^/api/alerts/(\d+)$#', $path, $matches) || preg_match('#^/alerts/delete\.php$#', $path)) {
    // For direct file access, ID should be in query string
    if (preg_match('#^/api/alerts/(\d+)$#', $path, $matches)) {
        $id = $matches[1];
        $_GET['id'] = $id;
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