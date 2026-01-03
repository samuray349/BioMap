<?php
/**
 * POST /api/reset-password
 * Reset password with token
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
    $token = $input['token'] ?? null;
    $password = $input['password'] ?? null;
    
    if (!$token || !trim($token)) {
        sendError('Token é obrigatório.', 400);
    }
    
    if (!$password) {
        sendError('Password é obrigatória.', 400);
    }
    
    // Validate password strength
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        sendError('A password deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula e um número.', 400);
    }
    
    // Find valid token
    $tokens = Database::query(
        'SELECT prt.token_id, prt.utilizador_id, prt.expires_at, prt.used, u.email
         FROM password_reset_tokens prt
         JOIN utilizador u ON prt.utilizador_id = u.utilizador_id
         WHERE prt.token = $1',
        [trim($token)]
    );
    
    if (count($tokens) === 0) {
        sendError('Token inválido ou expirado.', 400);
    }
    
    $tokenData = $tokens[0];
    
    // Check if token is already used
    if ($tokenData['used']) {
        sendError('Este token já foi utilizado.', 400);
    }
    
    // Check if token is expired
    if (strtotime($tokenData['expires_at']) < time()) {
        sendError('Token expirado. Solicite um novo link de redefinição.', 400);
    }
    
    // Hash new password
    $passwordHash = hashPassword($password);
    
    // Update password
    Database::execute(
        'UPDATE utilizador SET password_hash = $1 WHERE utilizador_id = $2',
        [$passwordHash, $tokenData['utilizador_id']]
    );
    
    // Mark token as used
    Database::execute(
        'UPDATE password_reset_tokens SET used = TRUE WHERE token_id = $1',
        [$tokenData['token_id']]
    );
    
    sendJson(['message' => 'Password redefinida com sucesso.']);
} catch (Exception $e) {
    error_log('Erro ao redefinir password: ' . $e->getMessage());
    sendError('Erro ao redefinir password. Tente novamente mais tarde.', 500);
}
?>
