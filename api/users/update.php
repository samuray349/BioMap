<?php
/**
 * PUT /users/:id
 * Update user profile (nome and email)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendError('Method not allowed', 405);
}

try {
    $input = getJsonInput();
    
    // Get ID from query string or path (router should pass it)
    $id = getQueryParam('id');
    if (!$id) {
        // Try to extract from path if router didn't set it
        preg_match('#/users/(\d+)#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $nome_utilizador = $input['nome_utilizador'] ?? null;
    $email = $input['email'] ?? null;
    
    if (!$nome_utilizador || !trim($nome_utilizador)) {
        sendError('Nome utilizador é obrigatório.', 400);
    }
    
    if (!$email || !trim($email)) {
        sendError('Email é obrigatório.', 400);
    }
    
    // Basic email validation
    if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        sendError('Email inválido.', 400);
    }
    
    // Check if user exists
    $userCheck = Database::query(
        'SELECT utilizador_id FROM utilizador WHERE utilizador_id = $1',
        [$id]
    );
    
    if (count($userCheck) === 0) {
        sendError('Utilizador not found', 404);
    }
    
    // Check if email is already taken by another user
    $emailCheck = Database::query(
        'SELECT utilizador_id FROM utilizador WHERE email = $1 AND utilizador_id != $2',
        [trim($email), $id]
    );
    
    if (count($emailCheck) > 0) {
        sendError('Email já está em uso por outro utilizador.', 409);
    }
    
    // Update user profile
    Database::execute(
        'UPDATE utilizador SET nome_utilizador = $1, email = $2 WHERE utilizador_id = $3',
        [trim($nome_utilizador), trim($email), $id]
    );
    
    // Get updated user data
    $updatedUser = Database::queryOne(
        'SELECT utilizador_id, nome_utilizador, email, funcao_id, estado_id
         FROM utilizador WHERE utilizador_id = $1',
        [$id]
    );
    
    sendJson([
        'message' => 'Perfil atualizado com sucesso.',
        'utilizador_id' => (int)$id,
        'nome_utilizador' => $updatedUser['nome_utilizador'],
        'email' => $updatedUser['email']
    ]);
} catch (Exception $e) {
    error_log('Erro ao atualizar perfil do utilizador: ' . $e->getMessage());
    sendError('Erro ao atualizar perfil do utilizador.', 500);
}
?>
