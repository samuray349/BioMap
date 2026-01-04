<?php
/**
 * POST /api/check-user
 * Check if username or email already exists
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
    $name = $input['name'] ?? null;
    $email = $input['email'] ?? null;
    
    if (!$name && !$email) {
        sendError('Nome ou email é obrigatório.', 400);
    }
    
    $nameExists = false;
    $emailExists = false;
    
    if ($name) {
        $result = Database::query(
            'SELECT utilizador_id FROM utilizador WHERE nome_utilizador = ?',
            [trim($name)]
        );
        $nameExists = count($result) > 0;
    }
    
    if ($email) {
        $result = Database::query(
            'SELECT utilizador_id FROM utilizador WHERE email = ?',
            [trim($email)]
        );
        $emailExists = count($result) > 0;
    }
    
    sendJson([
        'nameExists' => $nameExists,
        'emailExists' => $emailExists
    ]);
} catch (Exception $e) {
    error_log('Erro ao verificar utilizador: ' . $e->getMessage());
    sendError('Erro ao verificar utilizador.', 500);
}
?>
