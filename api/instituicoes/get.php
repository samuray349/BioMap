<?php
/**
 * GET /instituicoesDesc/:id
 * Get institution details by ID
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
    if (!$id) {
        preg_match('#/instituicoesDesc/(\d+)#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $instituicao = Database::queryOne(
        'SELECT 
            i.instituicao_id,
            i.nome,
            i.descricao,
            i.localizacao_texto,
            i.telefone_contacto,
            i.url_imagem,
            i.dias_aberto,
            i.hora_abertura,
            i.hora_fecho,
            CAST(ST_Y(i."localização"::geometry) AS NUMERIC(10, 8)) as latitude,
            CAST(ST_X(i."localização"::geometry) AS NUMERIC(11, 8)) as longitude
         FROM instituicao i
         WHERE i.instituicao_id = ?',
        [$id]
    );
    
    if (!$instituicao) {
        sendError('Instituição not found', 404);
    }
    
    // Ensure coordinates are numeric (convert from string to float if needed)
    if (isset($instituicao['latitude'])) {
        $instituicao['latitude'] = (float)$instituicao['latitude'];
    }
    if (isset($instituicao['longitude'])) {
        $instituicao['longitude'] = (float)$instituicao['longitude'];
    }
    
    sendJson($instituicao);
} catch (Exception $e) {
    error_log('Erro ao executar a query: ' . $e->getMessage());
    sendError('Erro ao executar a query', 500);
}
?>
