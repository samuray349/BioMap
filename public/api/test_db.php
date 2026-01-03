<?php
/**
 * Database Connection Test Script
 * Use this to diagnose database connection issues
 */

header('Content-Type: application/json');

// Check if PDO PostgreSQL extension is available
$pdoAvailable = extension_loaded('pdo');
$pgsqlAvailable = extension_loaded('pdo_pgsql');

$result = [
    'pdo_available' => $pdoAvailable,
    'pdo_pgsql_available' => $pgsqlAvailable,
    'php_version' => PHP_VERSION,
    'error' => null
];

if (!$pdoAvailable) {
    $result['error'] = 'PDO extension is not loaded';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

if (!$pgsqlAvailable) {
    $result['error'] = 'PDO PostgreSQL extension (pdo_pgsql) is not loaded. This extension is required to connect to PostgreSQL databases.';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Try to connect
try {
    require_once __DIR__ . '/../config/database.php';
    
    $pdo = getDBConnection();
    $result['connection'] = 'success';
    $result['message'] = 'Database connection successful';
    
} catch (Exception $e) {
    $result['connection'] = 'failed';
    $result['error'] = $e->getMessage();
    $result['error_code'] = $e->getCode();
}

echo json_encode($result, JSON_PRETTY_PRINT);
