<?php
/**
 * PHP Update User Profile Endpoint
 * Updates user's name and email
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST/PUT requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
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
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['nome_utilizador']) || !isset($input['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome utilizador e email são obrigatórios.']);
        exit();
    }
    
    $nomeUtilizador = trim($input['nome_utilizador']);
    $email = trim($input['email']);
    
    // Validation
    if (empty($nomeUtilizador)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome utilizador é obrigatório.']);
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email inválido.']);
        exit();
    }
    
    // Check if user exists
    $userCheck = executeQuerySingle(
        'SELECT utilizador_id FROM utilizador WHERE utilizador_id = :id',
        [':id' => $userId]
    );
    
    if (!$userCheck) {
        http_response_code(404);
        echo json_encode(['error' => 'Utilizador não encontrado.']);
        exit();
    }
    
    // Check if email is already taken by another user
    $emailCheck = executeQuerySingle(
        'SELECT utilizador_id FROM utilizador WHERE email = :email AND utilizador_id != :id',
        [':email' => $email, ':id' => $userId]
    );
    
    if ($emailCheck) {
        http_response_code(409);
        echo json_encode(['error' => 'Email já está em uso por outro utilizador.']);
        exit();
    }
    
    // Update user profile
    $sql = 'UPDATE utilizador 
            SET nome_utilizador = :nome, email = :email 
            WHERE utilizador_id = :id';
    
    executeUpdate($sql, [
        ':nome' => $nomeUtilizador,
        ':email' => $email,
        ':id' => $userId
    ]);
    
    // Get updated user data
    $updatedUser = executeQuerySingle(
        'SELECT utilizador_id, nome_utilizador, email, funcao_id, estado_id 
         FROM utilizador 
         WHERE utilizador_id = :id',
        [':id' => $userId]
    );
    
    // Update session with new data
    if ($updatedUser) {
        $userData = [
            'id' => (int)$updatedUser['utilizador_id'],
            'name' => $updatedUser['nome_utilizador'],
            'email' => $updatedUser['email'],
            'funcao_id' => (int)$updatedUser['funcao_id']
        ];
        setUserSession($userData);
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'message' => 'Perfil atualizado com sucesso.',
        'utilizador_id' => (int)$updatedUser['utilizador_id'],
        'nome_utilizador' => $updatedUser['nome_utilizador'],
        'email' => $updatedUser['email']
    ]);
    
} catch (PDOException $e) {
    error_log('Update profile database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar perfil.']);
} catch (Exception $e) {
    error_log('Update profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar perfil.']);
}
