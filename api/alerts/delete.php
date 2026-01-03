<?php
/**
 * DELETE /api/alerts/:id
 * Delete an alert (avistamento)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendError('Method not allowed', 405);
}

try {
    $input = getJsonInput();
    
    // Get ID from query string or path
    $id = getQueryParam('id');
    if (!$id) {
        preg_match('#/api/alerts/(\d+)#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $utilizador_id = $input['utilizador_id'] ?? null;
    $funcao_id = $input['funcao_id'] ?? null;
    
    if (!$utilizador_id || !$funcao_id) {
        sendError('Autenticação necessária.', 401);
    }
    
    // Check if avistamento exists and get creator ID
    $avistamento = Database::queryOne(
        'SELECT utilizador_id FROM avistamento WHERE avistamento_id = $1',
        [$id]
    );
    
    if (!$avistamento) {
        sendError('Avistamento não encontrado.', 404);
    }
    
    $creatorId = $avistamento['utilizador_id'];
    $isAdmin = (int)$funcao_id === 1;
    $isCreator = (int)$utilizador_id === (int)$creatorId;
    
    if (!$isAdmin && !$isCreator) {
        sendError('Não tem permissão para eliminar este avistamento.', 403);
    }
    
    // Delete avistamento
    Database::execute(
        'DELETE FROM avistamento WHERE avistamento_id = $1',
        [$id]
    );
    
    sendJson(['message' => 'Avistamento eliminado com sucesso.']);
} catch (Exception $e) {
    error_log('Erro ao eliminar avistamento: ' . $e->getMessage());
    sendError('Erro ao eliminar avistamento.', 500);
}
