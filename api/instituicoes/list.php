<?php
/**
 * GET /instituicoes
 * Get all institutions with optional search
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

try {
    $search = getQueryParam('search');
    
    $sqlQuery = '
        SELECT 
            i.instituicao_id,
            i.nome,
            i.descricao,
            i.localizacao_texto,
            i.telefone_contacto,
            i.url_imagem,
            i.dias_aberto,
            i.hora_abertura,
            i.hora_fecho,
            ST_X(i."localização"::geometry) as latitude,
            ST_Y(i."localização"::geometry) as longitude
        FROM instituicao i
        WHERE 1=1
    ';
    
    $params = [];
    $paramCounter = 1;
    
    if ($search) {
        $sqlQuery .= " AND (i.nome ILIKE $" . $paramCounter . " OR i.localizacao_texto ILIKE $" . ($paramCounter + 1) . ")";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $paramCounter += 2;
    }
    
    $sqlQuery .= ' ORDER BY i.instituicao_id';
    
    $results = Database::query($sqlQuery, $params);
    sendJson($results);
} catch (Exception $e) {
    error_log('Erro ao executar a query: ' . $e->getMessage());
    sendError('Erro ao executar a query', 500);
}
?>
