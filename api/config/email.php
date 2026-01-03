<?php
/**
 * Email Configuration for PHP API
 * Uses PHPMailer or native mail() function
 * For production, consider using a service like SendGrid, Mailgun, or PHPMailer with SMTP
 */

function sendEmail($to, $subject, $htmlBody, $textBody = null) {
    // For Railway/PHP, you can use:
    // 1. PHPMailer with SMTP (recommended for production)
    // 2. Native mail() function (may not work on Railway)
    // 3. External service API (SendGrid, Mailgun, etc.)
    
    // For now, using native mail() as a fallback
    // In production, implement PHPMailer or service API
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: pedrovan14@gmail.com',
        'Reply-To: pedrovan14@gmail.com'
    ];
    
    // Note: mail() function may not work on Railway
    // Consider using PHPMailer or an email service API
    $result = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    
    return $result;
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $name, $resetUrl) {
    $subject = 'Redefinir Password - BioMap';
    
    $htmlBody = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #1A8F4A;">Redefinir Password</h2>
            <p>Olá ' . htmlspecialchars($name) . ',</p>
            <p>Recebemos um pedido para redefinir a password da sua conta BioMap.</p>
            <p>Clique no link abaixo para criar uma nova password:</p>
            <p style="margin: 20px 0;">
                <a href="' . htmlspecialchars($resetUrl) . '" style="background-color: #1A8F4A; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Redefinir Password
                </a>
            </p>
            <p>Ou copie e cole este link no seu navegador:</p>
            <p style="word-break: break-all; color: #666;">' . htmlspecialchars($resetUrl) . '</p>
            <p style="color: #999; font-size: 12px; margin-top: 30px;">
                Este link expira em 1 hora. Se não solicitou esta alteração, ignore este email.
            </p>
            <p style="color: #999; font-size: 12px;">
                Atenciosamente,<br>Equipa BioMap
            </p>
        </div>
    ';
    
    $textBody = "
        Redefinir Password
        
        Olá $name,
        
        Recebemos um pedido para redefinir a password da sua conta BioMap.
        
        Clique no link abaixo para criar uma nova password:
        $resetUrl
        
        Este link expira em 1 hora. Se não solicitou esta alteração, ignore este email.
        
        Atenciosamente,
        Equipa BioMap
    ";
    
    return sendEmail($email, $subject, $htmlBody, $textBody);
}
?>
