<?php
/**
 * GET /animais/estados
 * Get all animal conservation states
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

try {
    $estados = Database::query(
        'SELECT estado_id, TRIM(nome_estado) as nome_estado, hex_cor FROM estado_conservacao ORDER BY estado_id'
    );
    sendJson($estados);
} catch (Exception $e) {
    error_log('Erro ao buscar estados de conservação: ' . $e->getMessage());
    sendError('Erro ao buscar estados de conservação.', 500);
}
?>
