<?php
    echo "aaaaa";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link href="css/styles.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="js/config.js"></script>
    <script src="js/session.js"></script>
</head>
<body>
    <div id="header-placeholder"></div>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Iniciar Sessão</h2>
            </div>
            
            <form class="login-form" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" required autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Password</label>
                    <div class="input-wrapper password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                            <span class="eye-icon"></span>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    
                    <a href="esqueceu_password.php" class="forgot-password">Esqueceu-se da password?</a>
                </div>

                <button type="submit" class="login-btn" action="index.php">
                    <span class="btn-text">Iniciar Sessão</span>
                    <span class="btn-loader"></span>
                </button>
            </form>

            <div class="signup-link">
                <p>Não tem conta? <a href="sign_up.php">Criar Conta</a></p>
            </div>

            <div class="success-message" id="successMessage">
                <div class="success-icon">✓</div>
                <h3>Login bem-sucedido!</h3>
                <p>A redirecionar...</p>
            </div>
        </div>
    </div>
    
    <script src="../../shared/js/form-utils.js"></script>
    <script src="js/script.js"></script>
    <script>
        (function() {
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const successMessage = document.getElementById('successMessage');
            const submitButton = form.querySelector('.login-btn');
            const btnText = submitButton.querySelector('.btn-text');
            const btnLoader = submitButton.querySelector('.btn-loader');

            const togglePassword = () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                passwordToggle.classList.toggle('visible', !isPassword);
            };

            if (passwordToggle) {
                passwordToggle.addEventListener('click', togglePassword);
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
                if (btnText) btnText.textContent = isLoading ? 'A validar...' : 'Iniciar Sessão';
            }

            form?.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearErrors();

                const email = emailInput.value.trim();
                const password = passwordInput.value;

                let hasError = false;
                if (!email) {
                    showError('Insira um email válido.');
                    hasError = true;
                }
                if (!password) {
                    showError('Insira a password.');
                    hasError = true;
                }

                if (hasError) return;

                setLoading(true);

                try {
                    // Use PHP endpoint for authentication
                    const apiUrl = 'api/auth/login.php';
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ email, password })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data?.error || 'Não foi possível iniciar sessão.');
                    }

                    if (successMessage) {
                        successMessage.style.display = 'block';
                    }

                    // PHP endpoint sets session and cookie automatically
                    // Just store in localStorage for JavaScript compatibility
                    if (data?.user) {
                        localStorage.setItem('biomapUser', JSON.stringify(data.user));
                        
                        // Also update cookie for JavaScript (PHP already set it, but ensure compatibility)
                        if (typeof SessionHelper !== 'undefined') {
                            SessionHelper.setUser(data.user);
                        }
                    }

                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 800);
                } catch (error) {
                    const errorMsg = error.message || 'Não foi possível iniciar sessão.';
                    showError(errorMsg);
                } finally {
                    setLoading(false);
                }
            });
        })();

        if (typeof loadHeader === 'function') {
            loadHeader();
        }
    </script>

    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>
</body>
</html>
