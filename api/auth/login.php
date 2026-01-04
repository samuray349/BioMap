<?php
/**
 * POST /api/login
 * User login
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
    validateRequired($input, ['email', 'password']);
    
    $email = $input['email'];
    $password = $input['password'];
    
    $users = Database::query(
        'SELECT utilizador_id, nome_utilizador, email, password_hash, estado_id, funcao_id 
         FROM utilizador WHERE email = ? LIMIT 1',
        [$email]
    );
    
    if (count($users) === 0) {
        sendError('Credenciais inválidas.', 401);
    }
    
    $user = $users[0];
    
    if ((int)$user['estado_id'] !== 1) {
        sendError('Conta inativa.', 403);
    }
    
    $passwordHash = hashPassword($password);
    if ($passwordHash !== $user['password_hash']) {
        sendError('Credenciais inválidas.', 401);
    }
    
    sendJson([
        'message' => 'Login bem-sucedido.',
        'user' => [
            'id' => $user['utilizador_id'],
            'name' => $user['nome_utilizador'],
            'email' => $user['email'],
            'funcao_id' => $user['funcao_id']
        ]
    ]);
} catch (Exception $e) {
    error_log('Erro ao iniciar sessão: ' . $e->getMessage());
    sendError('Erro ao iniciar sessão.', 500);
}
?>
