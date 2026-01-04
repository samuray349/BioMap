<?php
/**
 * POST /api/alerts
 * Create a new alert (avistamento)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

try {
    $input = getJsonInput();
    
    $animal_id = $input['animal_id'] ?? null;
    $utilizador_id = $input['utilizador_id'] ?? null;
    $latitude = $input['latitude'] ?? null;
    $longitude = $input['longitude'] ?? null;
    $data_avistamento = $input['data_avistamento'] ?? null;
    
    // Validate required fields
    if ($animal_id === null || $utilizador_id === null || $latitude === null || $longitude === null) {
        sendError('Campos obrigatórios em falta: animal_id, utilizador_id, latitude, longitude.', 400);
    }
    
    // Validate user is logged in
    if (!$utilizador_id || $utilizador_id === 'null' || $utilizador_id === 'undefined') {
        sendError('Deve iniciar sessão para criar um alerta.', 401);
    }
    
    // Validate IDs are numeric
    if (!preg_match('/^\d+$/', (string)$animal_id) || !preg_match('/^\d+$/', (string)$utilizador_id)) {
        sendError('IDs devem ser números válidos.', 400);
    }
    
    // Validate coordinates
    // Note: Frontend sends latitude/longitude, but ST_MakePoint expects (longitude, latitude)
    // So we store them as received: $lon (from longitude field) and $lat (from latitude field)
    $lat = (float)$latitude;
    $lon = (float)$longitude;
    if (is_nan($lat) || is_nan($lon) || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
        sendError('Coordenadas inválidas.', 400);
    }
    
    // Check if animal exists
    $animal = Database::queryOne(
        'SELECT animal_id FROM animal WHERE animal_id = ?',
        [$animal_id]
    );
    if (!$animal) {
        sendError('Animal não encontrado.', 404);
    }
    
    // Check if user exists
    $user = Database::queryOne(
        'SELECT utilizador_id FROM utilizador WHERE utilizador_id = ?',
        [$utilizador_id]
    );
    if (!$user) {
        sendError('Utilizador não encontrado.', 404);
    }
    
    // Use current date if not provided
    $avistamentoDate = $data_avistamento ?: date('c');
    
    // Insert avistamento with PostGIS geography
    // ST_MakePoint expects (longitude, latitude), so we pass ($lon, $lat) as received
    // When reading back, ST_X returns longitude and ST_Y returns latitude
    $avistamento = Database::insert(
        'INSERT INTO avistamento (data_avistamento, "localização", animal_id, utilizador_id)
         VALUES (?, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?, ?)',
        [
            $avistamentoDate,
            $lon,  // longitude (X coordinate)
            $lat,  // latitude (Y coordinate)
            $animal_id,
            $utilizador_id
        ]
    );
    
    sendJson([
        'message' => 'Alerta criado com sucesso.',
        'avistamento_id' => $avistamento['avistamento_id']
    ], 201);
} catch (Exception $e) {
    error_log('Erro ao criar alerta: ' . $e->getMessage());
    sendError('Erro ao criar alerta.', 500);
}
?>
