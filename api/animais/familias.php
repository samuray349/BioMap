<?php
/**
 * GET /animais/familias
 * Get all animal families
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

try {
    $familias = Database::query(
        'SELECT familia_id, TRIM(nome_familia) as nome_familia FROM familia ORDER BY nome_familia'
    );
    sendJson($familias);
} catch (Exception $e) {
    error_log('Erro ao buscar famílias de animais: ' . $e->getMessage());
    sendError('Erro ao buscar famílias de animais.', 500);
}
?>
