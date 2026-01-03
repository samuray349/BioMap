<?php
/**
 * PHP Login Endpoint
 * Authenticates user and creates session
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../session_helper.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email e password são obrigatórios.']);
        exit();
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Basic email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email inválido.']);
        exit();
    }
    
    // Get user from database
    $sql = 'SELECT utilizador_id, nome_utilizador, email, password_hash, estado_id, funcao_id 
            FROM utilizador 
            WHERE email = :email 
            LIMIT 1';
    
    $user = executeQuerySingle($sql, [':email' => $email]);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciais inválidas.']);
        exit();
    }
    
    // Check if account is active (estado_id = 1 means normal/active)
    if ((int)$user['estado_id'] !== 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Conta inativa.']);
        exit();
    }
    
    // Verify password (using SHA256 hash like Node.js)
    $passwordHash = hash('sha256', $password);
    if ($passwordHash !== $user['password_hash']) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciais inválidas.']);
        exit();
    }
    
    // Create user session data
    $userData = [
        'id' => (int)$user['utilizador_id'],
        'name' => $user['nome_utilizador'],
        'email' => $user['email'],
        'funcao_id' => (int)$user['funcao_id']
    ];
    
    // Set session (stores in both PHP session and cookie)
    setUserSession($userData);
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'message' => 'Login bem-sucedido.',
        'user' => $userData
    ]);
    
} catch (PDOException $e) {
    error_log('Login database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao iniciar sessão.']);
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao iniciar sessão.']);
}
