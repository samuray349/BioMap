<?php
/**
 * PHP Update User Password Endpoint
 * Updates user's password
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
    
    if (!isset($input['current_password']) || !isset($input['new_password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Password atual e nova password são obrigatórios.']);
        exit();
    }
    
    $currentPassword = $input['current_password'];
    $newPassword = $input['new_password'];
    
    // Check if user exists and get current password hash
    $user = executeQuerySingle(
        'SELECT utilizador_id, password_hash FROM utilizador WHERE utilizador_id = :id',
        [':id' => $userId]
    );
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Utilizador não encontrado.']);
        exit();
    }
    
    // Verify current password
    $currentPasswordHash = hash('sha256', $currentPassword);
    if ($currentPasswordHash !== $user['password_hash']) {
        http_response_code(401);
        echo json_encode(['error' => 'Password atual incorreta.']);
        exit();
    }
    
    // Check if new password is different from current password
    if ($currentPassword === $newPassword) {
        http_response_code(400);
        echo json_encode(['error' => 'A nova password deve ser diferente da password atual.']);
        exit();
    }
    
    // Hash new password
    $newPasswordHash = hash('sha256', $newPassword);
    
    // Update password
    $sql = 'UPDATE utilizador SET password_hash = :password_hash WHERE utilizador_id = :id';
    executeUpdate($sql, [
        ':password_hash' => $newPasswordHash,
        ':id' => $userId
    ]);
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'message' => 'Password atualizada com sucesso.',
        'utilizador_id' => (int)$userId
    ]);
    
} catch (PDOException $e) {
    error_log('Update password database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar password.']);
} catch (Exception $e) {
    error_log('Update password error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao atualizar password.']);
}
