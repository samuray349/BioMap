<?php
/**
 * PHP Get User Profile Endpoint
 * Returns current user's profile data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if PostgreSQL PDO extension is available
if (!extension_loaded('pdo_pgsql')) {
    error_log('PDO PostgreSQL extension (pdo_pgsql) is not loaded');
    http_response_code(500);
    echo json_encode(['error' => 'Servidor não configurado corretamente. Contacte o administrador.']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../session_helper.php';

try {
    // Get current user from session
    $currentUser = getCurrentUser();
    
    if (!$currentUser) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autenticado.']);
        exit();
    }
    
    $userId = $currentUser['id'];
    
    // Get user data from database
    $sql = 'SELECT 
                utilizador_id,
                nome_utilizador,
                email,
                funcao_id,
                estado_id
            FROM utilizador
            WHERE utilizador_id = :id';
    
    $user = executeQuerySingle($sql, [':id' => $userId]);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Utilizador não encontrado.']);
        exit();
    }
    
    // Return user profile
    http_response_code(200);
    echo json_encode([
        'utilizador_id' => (int)$user['utilizador_id'],
        'nome_utilizador' => $user['nome_utilizador'],
        'email' => $user['email'],
        'funcao_id' => (int)$user['funcao_id'],
        'estado_id' => (int)$user['estado_id']
    ]);
    
} catch (PDOException $e) {
    error_log('Get profile database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar perfil.']);
} catch (Exception $e) {
    error_log('Get profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar perfil.']);
}
