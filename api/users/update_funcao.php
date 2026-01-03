<?php
/**
 * PUT /users/:id/funcao
 * Update user role (funcao)
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
        preg_match('#/users/(\d+)/funcao#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $funcao_id = $input['funcao_id'] ?? null;
    
    if (!$funcao_id || ($funcao_id != 1 && $funcao_id != 2)) {
        sendError('funcao_id must be 1 (Admin) or 2 (Utilizador).', 400);
    }
    
    // Check if user exists
    $userCheck = Database::query(
        'SELECT utilizador_id FROM utilizador WHERE utilizador_id = $1',
        [$id]
    );
    
    if (count($userCheck) === 0) {
        sendError('Utilizador not found', 404);
    }
    
    // Check if funcao exists
    $funcaoCheck = Database::query(
        'SELECT funcao_id FROM funcao WHERE funcao_id = $1',
        [$funcao_id]
    );
    
    if (count($funcaoCheck) === 0) {
        sendError('Funcao not found', 400);
    }
    
    // Update user funcao_id
    Database::execute(
        'UPDATE utilizador SET funcao_id = $1 WHERE utilizador_id = $2',
        [$funcao_id, $id]
    );
    
    // Get updated user data with funcao name
    $updatedUser = Database::queryOne(
        'SELECT u.utilizador_id, u.funcao_id, f.nome_funcao as estatuto
         FROM utilizador u
         JOIN funcao f ON u.funcao_id = f.funcao_id
         WHERE u.utilizador_id = $1',
        [$id]
    );
    
    sendJson([
        'message' => 'Funcao atualizada com sucesso.',
        'utilizador_id' => (int)$id,
        'funcao_id' => (int)$funcao_id,
        'estatuto' => $updatedUser['estatuto']
    ]);
} catch (Exception $e) {
    error_log('Erro ao atualizar funcao do utilizador: ' . $e->getMessage());
    sendError('Erro ao atualizar funcao do utilizador.', 500);
}
?>
