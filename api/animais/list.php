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
    $search = getQueryParam('search');
    $families = getQueryParam('families');
    $states = getQueryParam('states');
    
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
        JOIN familia f ON a.familia_id = f.familia_id
        JOIN estado_conservacao e ON a.estado_id = e.estado_id
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
    
    $sqlQuery .= ' ORDER BY a.animal_id';
    
    $results = Database::query($sqlQuery, $params);
    sendJson($results);
} catch (Exception $e) {
    error_log('Erro ao executar a query: ' . $e->getMessage());
    sendError('Erro ao executar a query', 500);
}
?>
