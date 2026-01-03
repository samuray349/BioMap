<?php
/**
 * GET /animaisDesc/:id
 * Get animal details by ID
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
        preg_match('#/animaisDesc/(\d+)#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $animal = Database::queryOne(
        'SELECT 
            a.animal_id, 
            a.nome_comum, 
            a.nome_cientifico, 
            a.descricao, 
            a.url_imagem, 
            a.populacao_estimada,
            a.facto_interessante,
            f.nome_familia, 
            d.nome_dieta,
            e.nome_estado, 
            e.hex_cor as estado_cor
         FROM animal a
         LEFT JOIN familia f ON a.familia_id = f.familia_id
         LEFT JOIN estado_conservacao e ON a.estado_id = e.estado_id
         LEFT JOIN dieta d ON a.dieta_id = d.dieta_id
         WHERE a.animal_id = $1',
        [$id]
    );
    
    if (!$animal) {
        sendError('Animal not found', 404);
    }
    
    // Get threats (ameacas)
    $ameacas = Database::query(
        'SELECT a.descricao
         FROM ameaca a 
         JOIN animal_ameaca aa ON a.ameaca_id = aa.ameaca_id 
         WHERE aa.animal_id = $1',
        [$id]
    );
    
    $animal['ameacas'] = array_column($ameacas, 'descricao');
    
    sendJson($animal);
} catch (Exception $e) {
    error_log('Erro ao executar a query: ' . $e->getMessage());
    sendError('Erro ao executar a query', 500);
}
?>
