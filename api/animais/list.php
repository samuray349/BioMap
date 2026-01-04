<?php
/**
 * GET /animais
 * Get all animals with optional filters
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
    
    // Debug logging
    error_log('[Animais List] REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'not set'));
    error_log('[Animais List] QUERY_STRING: ' . ($_SERVER['QUERY_STRING'] ?? 'not set'));
    error_log('[Animais List] $_GET: ' . print_r($_GET, true));
    error_log('[Animais List] Search: ' . ($search ?? 'null') . ' | Families: ' . ($families ?? 'null') . ' | States: ' . ($states ?? 'null'));
    
    // Optimized query - use INNER JOIN for better performance
    $sqlQuery = '
        SELECT 
            a.animal_id, 
            a.nome_comum, 
            a.nome_cientifico, 
            a.descricao, 
            a.url_imagem, 
            f.nome_familia, 
            e.nome_estado, 
            e.hex_cor as estado_cor
        FROM animal a
        INNER JOIN familia f ON a.familia_id = f.familia_id
        INNER JOIN estado_conservacao e ON a.estado_id = e.estado_id
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
    
    $sqlQuery .= ' ORDER BY a.animal_id';
    
    $results = Database::query($sqlQuery, $params);
    sendJson($results);
} catch (Exception $e) {
    error_log('Erro ao executar a query: ' . $e->getMessage());
    sendError('Erro ao executar a query', 500);
}
?>
