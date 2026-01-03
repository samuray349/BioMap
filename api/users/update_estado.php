<?php
/**
 * PUT /users/:id/estado
 * Update user state (estado) - with transaction support
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
        preg_match('#/users/(\d+)/estado#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $estado_id = $input['estado_id'] ?? null;
    
    if (!$estado_id || !in_array((int)$estado_id, [1, 2, 3])) {
        sendError('estado_id must be 1 (Normal), 2 (Suspenso), or 3 (Banido).', 400);
    }
    
    Database::beginTransaction();
    
    try {
        // Check if user exists
        $userCheck = Database::query(
            'SELECT utilizador_id FROM utilizador WHERE utilizador_id = $1',
            [$id]
        );
        
        if (count($userCheck) === 0) {
            Database::rollback();
            sendError('Utilizador not found', 404);
        }
        
        // Check if estado exists
        $estadoCheck = Database::query(
            'SELECT estado_id FROM estado WHERE estado_id = $1',
            [$estado_id]
        );
        
        if (count($estadoCheck) === 0) {
            Database::rollback();
            sendError('Estado not found', 400);
        }
        
        // If user is being banned (estado_id = 3), delete all their avistamentos
        if ((int)$estado_id === 3) {
            $deletedCount = Database::execute(
                'DELETE FROM avistamento WHERE utilizador_id = $1',
                [$id]
            );
            error_log("Deleted $deletedCount avistamentos for banned user $id");
        }
        
        // Update user estado_id
        Database::execute(
            'UPDATE utilizador SET estado_id = $1 WHERE utilizador_id = $2',
            [$estado_id, $id]
        );
        
        // Get updated user data with estado name
        $updatedUser = Database::queryOne(
            'SELECT u.utilizador_id, u.estado_id, e.nome_estado, e.hex_cor as estado_cor
             FROM utilizador u
             JOIN estado e ON u.estado_id = e.estado_id
             WHERE u.utilizador_id = $1',
            [$id]
        );
        
        Database::commit();
        
        sendJson([
            'message' => 'Estado atualizado com sucesso.',
            'utilizador_id' => (int)$id,
            'estado_id' => (int)$estado_id,
            'nome_estado' => $updatedUser['nome_estado'],
            'estado_cor' => $updatedUser['estado_cor']
        ]);
    } catch (Exception $e) {
        Database::rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Erro ao atualizar estado do utilizador: ' . $e->getMessage());
    sendError('Erro ao atualizar estado do utilizador.', 500);
}
?>
