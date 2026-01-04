<?php
/**
 * POST /api/signup
 * Register a new user
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
    validateRequired($input, ['name', 'email', 'password']);
    
    $name = trim($input['name']);
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Check if name already exists
    $existingName = Database::query(
        'SELECT utilizador_id FROM utilizador WHERE nome_utilizador = ?',
        [$name]
    );
    
    // Check if email already exists
    $existingEmail = Database::query(
        'SELECT utilizador_id FROM utilizador WHERE email = ?',
        [$email]
    );
    
    $nameExists = count($existingName) > 0;
    $emailExists = count($existingEmail) > 0;
    
    if ($nameExists && $emailExists) {
        sendError('Nome e email já existentes', 409);
    }
    
    if ($nameExists) {
        sendJson([
            'error' => 'Este nome já existe',
            'nameExists' => true,
            'emailExists' => false
        ], 409);
    }
    
    if ($emailExists) {
        sendJson([
            'error' => 'Este email já existe',
            'nameExists' => false,
            'emailExists' => true
        ], 409);
    }
    
    $passwordHash = hashPassword($password);
    
    $result = Database::insert(
        'INSERT INTO utilizador (nome_utilizador, email, password_hash, funcao_id, estado_id, data_criacao) 
         VALUES (?, ?, ?, 2, 1, NOW())',
        [$name, $email, $passwordHash]
    );
    
    sendJson([
        'message' => 'Utilizador criado com sucesso.',
        'utilizador_id' => $result['utilizador_id']
    ], 201);
} catch (Exception $e) {
    error_log('Erro ao criar utilizador: ' . $e->getMessage());
    sendError('Erro ao criar utilizador.', 500);
}
?>
