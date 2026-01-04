<?php
/**
 * DELETE /users/:id
 * Delete a user (admin or own account)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendError('Method not allowed', 405);
}

try {
    $input = getJsonInput() ?? [];
    
    // Get ID from query string or path
    $id = getQueryParam('id');
    if (!$id) {
        preg_match('#/users/(\d+)#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $utilizador_id = $input['utilizador_id'] ?? null;
    $funcao_id = $input['funcao_id'] ?? null;
    
    if (!$utilizador_id || !$funcao_id) {
        sendError('Autenticação necessária. utilizador_id e funcao_id são obrigatórios.', 401);
    }
    
    // Check target user exists
    $userCheck = Database::query(
        'SELECT utilizador_id FROM utilizador WHERE utilizador_id = ?',
        [$id]
    );
    
    if (count($userCheck) === 0) {
        sendError('Utilizador não encontrado.', 404);
    }
    
    $isAdmin = (int)$funcao_id === 1;
    $isOwner = (int)$utilizador_id === (int)$id;
    
    if (!$isAdmin && !$isOwner) {
        sendError('Não tem permissão para eliminar este utilizador.', 403);
    }
    
    // Use transaction to ensure atomicity
    Database::beginTransaction();
    try {
        // Delete all avistamentos (sightings) associated with this user
        // This prevents foreign key constraint violations
        $avistamentosDeleted = Database::execute(
            'DELETE FROM avistamento WHERE utilizador_id = ?',
            [$id]
        );
        
        // Delete user
        $deleteResult = Database::execute(
            'DELETE FROM utilizador WHERE utilizador_id = ?',
            [$id]
        );
        
        if ($deleteResult === 0) {
            Database::rollback();
            sendError('Erro ao eliminar utilizador da base de dados.', 500);
        }
        
        // Commit transaction
        Database::commit();
        
        sendJson([
            'message' => 'Utilizador eliminado com sucesso.',
            'avistamentos_eliminados' => $avistamentosDeleted
        ]);
    } catch (Exception $e) {
        Database::rollback();
        throw $e; // Re-throw to be caught by outer catch block
    }
} catch (Exception $e) {
    error_log('Erro ao eliminar utilizador: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    sendError('Erro ao eliminar utilizador: ' . $e->getMessage(), 500);
}
?>
