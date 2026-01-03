<?php
/**
 * GET /users/estados
 * Get all user states
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

try {
    $estados = Database::query('SELECT nome_estado FROM estado ORDER BY estado_id');
    $estadoNames = array_column($estados, 'nome_estado');
    sendJson($estadoNames);
} catch (Exception $e) {
    error_log('Erro ao buscar estados: ' . $e->getMessage());
    sendError('Erro ao buscar estados', 500);
}
?>
