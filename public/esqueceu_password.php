<?php
require_once __DIR__ . '/session_helper.php';

// Redirect logged-in users away from password recovery page
if (isUserLoggedIn()) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Esqueceu Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link href="css/styles.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="js/config.js?v=<?php echo time(); ?>"></script>
    <script src="js/api-toggle.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <div id="header-placeholder"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Esqueceu-se da Password?</h2>
                <p>Insira o seu email abaixo para receber instruções para redefinir a sua password.</p>
            </div>
            
            <form class="login-form" id="forgotPasswordForm" novalidate>
                <div class="form-group">
                    <label for="email" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" required autocomplete="email">
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <span class="btn-text">Enviar</span>
                    <span class="btn-loader"></span>
                </button>
            </form>

            <div class="signup-link">
                <p><a href="login.php">Voltar ao Login</a></p>
            </div>

            <div class="success-message" id="successMessage">
                <div class="success-icon">✓</div>
                <h3>Email enviado!</h3>
                <p>Verifique a sua caixa de entrada para redefinir a password.</p>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>

    <script src="../../shared/js/form-utils.js"></script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script>
        (function() {
            const form = document.getElementById('forgotPasswordForm');
            const emailInput = document.getElementById('email');
            const successMessage = document.getElementById('successMessage');
            const submitButton = form.querySelector('.login-btn');
            const btnText = submitButton.querySelector('.btn-text');
            const btnLoader = submitButton.querySelector('.btn-loader');

            function showError(message) {
                // Show notification for errors
                if (typeof showNotification === 'function') {
                    showNotification(message, 'error');
                }
            }

            function clearErrors() {
                // Errors are only shown via notifications
            }

            function setLoading(isLoading) {
                submitButton.disabled = isLoading;
                submitButton.classList.toggle('loading', isLoading);
                if (btnText) btnText.textContent = isLoading ? 'A enviar...' : 'Enviar';
            }

            form?.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearErrors();

                const email = emailInput.value.trim();

                let hasError = false;
                if (!email) {
                    showError('Insira um email válido.');
                    hasError = true;
                } else {
                    // Basic email validation
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        showError('Insira um email válido.');
                        hasError = true;
                    }
                }

                if (hasError) return;

                setLoading(true);

                try {
                    // TODO: Replace with actual API endpoint when available
                    const apiUrl = window.API_CONFIG?.getUrl('api/forgot-password') || '/api/forgot-password';
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ email })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data?.error || 'Não foi possível enviar o email de recuperação.');
                    }

                    if (successMessage) {
                        successMessage.style.display = 'block';
                    }

                    // Optionally redirect after showing success message
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 3000);
                } catch (error) {
                    showError(error.message);
                } finally {
                    setLoading(false);
                }
            });
        })();

        if (typeof loadHeader === 'function') {
            loadHeader();
        }
    </script>
</body>
</html>
