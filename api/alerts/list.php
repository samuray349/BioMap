<?php
/**
 * GET /api/alerts
 * Get all alerts (avistamentos) with optional filters
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
    $families = getQueryParam('families');
    $states = getQueryParam('states');
    
    // Optimized query - use INNER JOIN where possible, only LEFT JOIN when necessary
    $sqlQuery = '
        SELECT 
            av.avistamento_id,
            av.data_avistamento,
            av.utilizador_id,
            ST_Y(av."localização"::geometry) as latitude,
            ST_X(av."localização"::geometry) as longitude,
            a.animal_id,
            a.nome_comum,
            a.nome_cientifico,
            a.descricao,
            a.url_imagem,
            f.nome_familia,
            d.nome_dieta,
            e.nome_estado,
            e.hex_cor as estado_cor
        FROM avistamento av
        INNER JOIN animal a ON av.animal_id = a.animal_id
        INNER JOIN familia f ON a.familia_id = f.familia_id
        INNER JOIN estado_conservacao e ON a.estado_id = e.estado_id
        LEFT JOIN dieta d ON a.dieta_id = d.dieta_id
        WHERE 1=1
    ';
    
    $params = [];
    $paramCounter = 1;
    
    if ($search) {
        $sqlQuery .= " AND (a.nome_comum ILIKE $" . $paramCounter . ")";
        $params[] = '%' . $search . '%';
        $paramCounter++;
    }
    
    if ($families) {
        $familyArray = explode(',', $families);
        $placeholders = [];
        foreach ($familyArray as $family) {
            $placeholders[] = '$' . $paramCounter;
            $params[] = trim($family);
            $paramCounter++;
        }
        $sqlQuery .= " AND f.nome_familia IN (" . implode(', ', $placeholders) . ")";
    }
    
    if ($states) {
        $stateArray = explode(',', $states);
        $placeholders = [];
        foreach ($stateArray as $state) {
            $placeholders[] = '$' . $paramCounter;
            $params[] = trim($state);
            $paramCounter++;
        }
        $sqlQuery .= " AND e.nome_estado IN (" . implode(', ', $placeholders) . ")";
    }
    
    $sqlQuery .= ' ORDER BY av.data_avistamento DESC';
    
    $results = Database::query($sqlQuery, $params);
    sendJson($results);
} catch (Exception $e) {
    error_log('Erro ao buscar avistamentos: ' . $e->getMessage());
    sendError('Erro ao buscar avistamentos.', 500);
}
?>
