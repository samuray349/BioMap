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
    // Ensure query string is parsed (PHP built-in server with router might not auto-populate $_GET)
    // Always parse from REQUEST_URI to ensure we get all query parameters
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $queryString = parse_url($requestUri, PHP_URL_QUERY);
    if (!empty($queryString)) {
        parse_str($queryString, $queryParams);
        $_GET = array_merge($_GET, $queryParams);
    }
    
    $search = getQueryParam('search');
    $families = getQueryParam('families');
    $states = getQueryParam('states');
    
    // Optimized query - use INNER JOIN where possible, only LEFT JOIN when necessary
    $sqlQuery = '
        SELECT 
            av.avistamento_id,
            av.data_avistamento,
            av.utilizador_id,
            CAST(ST_Y(av."localização"::geometry) AS NUMERIC(10, 8)) as latitude,
            CAST(ST_X(av."localização"::geometry) AS NUMERIC(11, 8)) as longitude,
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
    
    if ($search) {
        $sqlQuery .= " AND (a.nome_comum ILIKE ? OR a.nome_cientifico ILIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    
    if ($families) {
        $familyArray = explode(',', $families);
        $placeholders = [];
        foreach ($familyArray as $family) {
            $placeholders[] = '?';
            $params[] = trim($family);
        }
        $sqlQuery .= " AND f.nome_familia IN (" . implode(', ', $placeholders) . ")";
    }
    
    if ($states) {
        $stateArray = explode(',', $states);
        $placeholders = [];
        foreach ($stateArray as $state) {
            $placeholders[] = '?';
            // URL decode to handle multi-word statuses like "Em Perigo" (spaces encoded as + or %20)
            $decodedState = urldecode(trim($state));
            $params[] = $decodedState;
        }
        $sqlQuery .= " AND e.nome_estado IN (" . implode(', ', $placeholders) . ")";
    }
    
    $sqlQuery .= ' ORDER BY av.data_avistamento DESC';
    
    $results = Database::query($sqlQuery, $params);
    
    // Ensure coordinates are numeric (convert from string to float if needed)
    foreach ($results as &$row) {
        if (isset($row['latitude'])) {
            $row['latitude'] = (float)$row['latitude'];
        }
        if (isset($row['longitude'])) {
            $row['longitude'] = (float)$row['longitude'];
        }
    }
    unset($row); // Break reference
    
    sendJson($results);
} catch (Exception $e) {
    error_log('Erro ao buscar avistamentos: ' . $e->getMessage());
    sendError('Erro ao buscar avistamentos.', 500);
}
?>
