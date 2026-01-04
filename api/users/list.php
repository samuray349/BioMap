<?php
/**
 * GET /users
 * Get all users with optional filters
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
    if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
        if (empty($_GET)) {
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }
    }
    
    $search = getQueryParam('search');
    $estados = getQueryParam('estados');
    $estatutos = getQueryParam('estatutos');
    
    // Optimized query - only select needed columns, use efficient joins
    $sqlQuery = '
        SELECT 
            u.utilizador_id, 
            u.nome_utilizador, 
            u.email,
            u.estado_id,
            e.nome_estado,
            e.hex_cor as estado_cor,
            f.nome_funcao as estatuto,
            u.funcao_id
        FROM utilizador u
        INNER JOIN estado e ON u.estado_id = e.estado_id
        INNER JOIN funcao f ON u.funcao_id = f.funcao_id
        WHERE 1=1
    ';
    
    $params = [];
    
    if ($search) {
        $sqlQuery .= " AND (u.nome_utilizador ILIKE ? OR u.email ILIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    
    if ($estados) {
        $estadoArray = explode(',', $estados);
        // Use IN clause instead of ANY for compatibility
        $placeholders = [];
        foreach ($estadoArray as $estado) {
            $placeholders[] = '?';
            $params[] = trim($estado);
        }
        $sqlQuery .= " AND e.nome_estado IN (" . implode(', ', $placeholders) . ")";
    }
    
    if ($estatutos) {
        $estatutoArray = explode(',', $estatutos);
        // Use IN clause instead of ANY for compatibility
        $placeholders = [];
        foreach ($estatutoArray as $estatuto) {
            $placeholders[] = '?';
            $params[] = trim($estatuto);
        }
        $sqlQuery .= " AND f.nome_funcao IN (" . implode(', ', $placeholders) . ")";
    }
    
    $sqlQuery .= ' ORDER BY u.utilizador_id';
    
    $results = Database::query($sqlQuery, $params);
    sendJson($results);
} catch (Exception $e) {
    error_log('Erro ao executar a query: ' . $e->getMessage());
    sendError('Erro ao executar a query', 500);
}
?>
