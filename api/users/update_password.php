<?php
/**
 * PUT /users/:id/password
 * Update user password
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendError('Method not allowed', 405);
}

try {
    $input = getJsonInput();
    
    // Get ID from query string or path
    $id = getQueryParam('id');
    if (!$id) {
        preg_match('#/users/(\d+)/password#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $current_password = $input['current_password'] ?? null;
    $new_password = $input['new_password'] ?? null;
    
    if (!$current_password || !$new_password) {
        sendError('Password atual e nova password são obrigatórios.', 400);
    }
    
    // Check if user exists and get current password hash
    $userCheck = Database::query(
        'SELECT utilizador_id, password_hash FROM utilizador WHERE utilizador_id = ?',
        [$id]
    );
    
    if (count($userCheck) === 0) {
        sendError('Utilizador not found', 404);
    }
    
    $user = $userCheck[0];
    
    // Verify current password
    $currentPasswordHash = hashPassword($current_password);
    if ($currentPasswordHash !== $user['password_hash']) {
        sendError('Password atual incorreta.', 401);
    }
    
    // Check if new password is different from current password
    if ($current_password === $new_password) {
        sendError('A nova password deve ser diferente da password atual.', 400);
    }
    
    // Hash new password
    $newPasswordHash = hashPassword($new_password);
    
    // Update password
    Database::execute(
        'UPDATE utilizador SET password_hash = ? WHERE utilizador_id = ?',
        [$newPasswordHash, $id]
    );
    
    sendJson([
        'message' => 'Password atualizada com sucesso.',
        'utilizador_id' => (int)$id
    ]);
} catch (Exception $e) {
    error_log('Erro ao atualizar password do utilizador: ' . $e->getMessage());
    sendError('Erro ao atualizar password do utilizador.', 500);
}
?>
