<?php
/**
 * GET /users/estatutos
 * Get all user roles (funcoes)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

try {
    $estatutos = Database::query('SELECT nome_funcao FROM funcao ORDER BY funcao_id');
    $estatutoNames = array_column($estatutos, 'nome_funcao');
    sendJson($estatutoNames);
} catch (Exception $e) {
    error_log('Erro ao buscar estatutos: ' . $e->getMessage());
    sendError('Erro ao buscar estatutos', 500);
}
?>
