<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Redefinir Password</title>
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
                <h2>Redefinir Password</h2>
                <p>Crie uma nova password para a sua conta.</p>
            </div>
            
            <form class="login-form" id="resetPasswordForm" novalidate>
                <div class="form-group">
                    <label for="password" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Nova Password</label>
                    <div class="input-wrapper password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="new-password">
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                            <span class="eye-icon"></span>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Confirmar Password</label>
                    <div class="input-wrapper password-wrapper">
                        <input type="password" id="confirmPassword" name="confirmPassword" required autocomplete="new-password">
                        <button type="button" class="password-toggle" id="confirmPasswordToggle" aria-label="Toggle password visibility">
                            <span class="eye-icon"></span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <span class="btn-text">Redefinir Password</span>
                    <span class="btn-loader"></span>
                </button>
            </form>

            <div class="signup-link">
                <p><a href="login.php">Voltar ao Login</a></p>
            </div>

            <div class="success-message" id="successMessage">
                <div class="success-icon">✓</div>
                <h3>Password redefinida!</h3>
                <p>A redirecionar para o login...</p>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>

    <script src="../../shared/js/form-utils.js"></script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script>
        (function() {
            const form = document.getElementById('resetPasswordForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const passwordToggle = document.getElementById('passwordToggle');
            const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
            const successMessage = document.getElementById('successMessage');
            const submitButton = form.querySelector('.login-btn');
            const btnText = submitButton.querySelector('.btn-text');
            const btnLoader = submitButton.querySelector('.btn-loader');

            const togglePassword = (input, toggle) => {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                toggle.classList.toggle('visible', !isPassword);
            };

            if (passwordToggle) {
                passwordToggle.addEventListener('click', () => togglePassword(passwordInput, passwordToggle));
            }

            if (confirmPasswordToggle) {
                confirmPasswordToggle.addEventListener('click', () => togglePassword(confirmPasswordInput, confirmPasswordToggle));
            }

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
                if (btnText) btnText.textContent = isLoading ? 'A redefinir...' : 'Redefinir Password';
            }

            form?.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearErrors();

                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                let hasError = false;
                if (!password) {
                    showError('Insira uma password.');
                    hasError = true;
                } else {
                    // Basic strong password policy: min 8 chars, at least one lowercase, one uppercase and one digit
                    const pwdPolicy = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                    if (!pwdPolicy.test(password)) {
                        showError('A password deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula e um número.');
                        hasError = true;
                    }
                }

                if (!confirmPassword) {
                    showError('Confirme a password.');
                    hasError = true;
                } else if (password !== confirmPassword) {
                    showError('As passwords não coincidem.');
                    hasError = true;
                }

                if (hasError) return;

                setLoading(true);

                try {
                    // Get token from URL parameter (required for password reset)
                    const urlParams = new URLSearchParams(window.location.search);
                    const token = urlParams.get('token');

                    if (!token) {
                        showError('Token de redefinição inválido. Por favor, use o link enviado por email.');
                        setLoading(false);
                        return;
                    }

                    const apiUrl = window.API_CONFIG?.getUrl('api/reset-password') || '/api/reset-password';
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ 
                            password,
                            token: token
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data?.error || 'Não foi possível redefinir a password.');
                    }

                    if (successMessage) {
                        successMessage.style.display = 'block';
                    }

                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1200);
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

