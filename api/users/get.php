<?php
/**
 * GET /users/:id
 * Get user by ID
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

try {
    $id = getQueryParam('id');
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $users = Database::query(
        'SELECT utilizador_id, nome_utilizador, email, funcao_id, estado_id
         FROM utilizador WHERE utilizador_id = $1',
        [$id]
    );
    
    if (count($users) === 0) {
        sendError('Utilizador not found', 404);
    }
    
    sendJson($users[0]);
} catch (Exception $e) {
    error_log('Erro ao executar a query: ' . $e->getMessage());
    sendError('Erro ao executar a query', 500);
}
?>
