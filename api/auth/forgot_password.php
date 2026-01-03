<?php
/**
 * POST /api/forgot-password
 * Request password reset
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/email.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

try {
    $input = getJsonInput();
    $email = $input['email'] ?? null;
    
    if (!$email || !trim($email)) {
        sendError('Email é obrigatório.', 400);
    }
    
    $email = trim($email);
    
    // Basic email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError('Email inválido.', 400);
    }
    
    // Check if user exists
    $users = Database::query(
        'SELECT utilizador_id, nome_utilizador, email FROM utilizador WHERE email = $1',
        [$email]
    );
    
    // Always return success message (security best practice)
    $successMessage = 'Se o email existir na nossa base de dados, receberá instruções para redefinir a password.';
    
    if (count($users) === 0) {
        sendJson(['message' => $successMessage]);
    }
    
    $user = $users[0];
    
    // Generate secure token
    $resetToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Ensure password_reset_tokens table exists
    try {
        Database::query('SELECT 1 FROM password_reset_tokens LIMIT 1');
    } catch (Exception $e) {
        // Table doesn't exist, create it
        Database::execute('
            CREATE TABLE IF NOT EXISTS password_reset_tokens (
                token_id SERIAL PRIMARY KEY,
                utilizador_id INTEGER NOT NULL REFERENCES utilizador(utilizador_id) ON DELETE CASCADE,
                token VARCHAR(255) NOT NULL UNIQUE,
                expires_at TIMESTAMP NOT NULL,
                used BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT NOW()
            )
        ');
    }
    
    // Invalidate any existing tokens for this user
    Database::execute(
        'UPDATE password_reset_tokens SET used = TRUE WHERE utilizador_id = $1 AND used = FALSE',
        [$user['utilizador_id']]
    );
    
    // Store token in database
    Database::insert(
        'INSERT INTO password_reset_tokens (utilizador_id, token, expires_at) VALUES ($1, $2, $3)',
        [$user['utilizador_id'], $resetToken, $expiresAt]
    );
    
    // Create reset URL
    $frontendUrl = getenv('FRONTEND_URL') ?: 'https://lucped.antrob.eu';
    $resetUrl = $frontendUrl . '/public/repor_password.php?token=' . $resetToken;
    
    // Send email (may fail silently on Railway, but that's OK)
    try {
        sendPasswordResetEmail($user['email'], $user['nome_utilizador'], $resetUrl);
    } catch (Exception $e) {
        error_log('Error sending email: ' . $e->getMessage());
        // Continue - don't fail the request if email fails
    }
    
    sendJson(['message' => $successMessage]);
} catch (Exception $e) {
    error_log('Erro ao processar pedido de redefinição de password: ' . $e->getMessage());
    sendError('Erro ao processar pedido. Tente novamente mais tarde.', 500);
}
?>
