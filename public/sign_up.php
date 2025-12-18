<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Criar Conta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link href="css/styles.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="js/config.js"></script>
</head>
<body>
    <div id="header-placeholder"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Criar Conta</h2>
            </div>
            
            <form class="login-form" id="signupForm" novalidate>
                <div class="form-group">
                    <label for="name" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Nome</label>
                    <div class="input-wrapper">
                        <input type="name" id="name" name="name" required autocomplete="name">
                    </div>
                    <span class="error-message" id="nameError"></span>
                </div>

                <div class="form-group">
                    <label for="email" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" required autocomplete="email">
                    </div>
                    <span class="error-message" id="emailError"></span>
                </div>

                <div class="form-group">
                    <label for="password" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Password</label>
                    <div class="input-wrapper password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="new-password">
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                            <span class="eye-icon"></span>
                        </button>
                    </div>
                    <span class="error-message" id="passwordError"></span>
                </div>

                <button type="submit" class="login-btn">
                    <span class="btn-text">Criar Conta</span>
                    <span class="btn-loader"></span>
                </button>
            </form>

            <div class="signup-link">
                <p>Já tem conta? <a href="login.php">Iniciar Sessão</a></p>
            </div>

            <div class="error-message" id="formError" role="alert"></div>

            <div class="success-message" id="successMessage">
                <div class="success-icon">✓</div>
                <h3>Conta criada!</h3>
                <p>A redirecionar para o login...</p>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>

    <script src="../../shared/js/form-utils.js"></script>
    <script src="js/script.js"></script>
    <script>
        (function() {
            const form = document.getElementById('signupForm');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const nameError = document.getElementById('nameError');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');
            const formError = document.getElementById('formError');
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

            function showError(target, message) {
                if (target) target.textContent = message;
            }

            function clearErrors() {
                [nameError, emailError, passwordError, formError].forEach(el => {
                    if (el) el.textContent = '';
                });
            }

            function setLoading(isLoading) {
                submitButton.disabled = isLoading;
                submitButton.classList.toggle('loading', isLoading);
                if (btnText) btnText.textContent = isLoading ? 'A criar...' : 'Criar Conta';
            }

            form?.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearErrors();

                const name = nameInput.value.trim();
                const email = emailInput.value.trim();
                const password = passwordInput.value;

                let hasError = false;
                if (!name) {
                    showError(nameError, 'Insira o seu nome.');
                    hasError = true;
                }
                if (!email) {
                    showError(emailError, 'Insira um email válido.');
                    hasError = true;
                }
                if (!password) {
                    const emptyPasswordMsg = 'Insira a password.';
                    showError(passwordError, emptyPasswordMsg);
                    showNotification(emptyPasswordMsg, 'error');
                    hasError = true;
                } else {
                    // Basic strong password policy: min 8 chars, at least one lowercase, one uppercase and one digit
                    const pwdPolicy = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                    if (!pwdPolicy.test(password)) {
                        const passwordErrorMsg = 'A password deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula e um número.';
                        showError(passwordError, passwordErrorMsg);
                        showNotification(passwordErrorMsg, 'error');
                        hasError = true;
                    }
                }

                if (hasError) return;

                setLoading(true);

                try {
                    // First check if name or email already exists
                    const checkApiUrl = window.API_CONFIG?.getUrl('api/check-user') || '/api/check-user';
                    const checkResponse = await fetch(checkApiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ name, email })
                    });

                    const checkData = await checkResponse.json();

                    if (!checkResponse.ok) {
                        throw new Error(checkData?.error || 'Erro ao verificar dados.');
                    }

                    // Check for duplicates and show appropriate error notifications
                    if (checkData.nameExists && checkData.emailExists) {
                        // Show single notification for both errors
                        if (typeof showNotification === 'function') {
                            showNotification('Nome e email já existentes', 'error');
                        }
                        if (nameError) showError(nameError, 'Este nome já existe');
                        if (emailError) showError(emailError, 'Este email já existe');
                        setLoading(false);
                        return;
                    }

                    if (checkData.nameExists) {
                        if (typeof showNotification === 'function') {
                            showNotification('Este nome já existe', 'error');
                        }
                        if (nameError) showError(nameError, 'Este nome já existe');
                        setLoading(false);
                        return;
                    }

                    if (checkData.emailExists) {
                        if (typeof showNotification === 'function') {
                            showNotification('Este email já existe', 'error');
                        }
                        if (emailError) showError(emailError, 'Este email já existe');
                        setLoading(false);
                        return;
                    }

                    // If no duplicates, proceed with signup
                    const apiUrl = window.API_CONFIG?.getUrl('api/signup') || '/api/signup';
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ name, email, password })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // Handle specific error cases from signup endpoint
                        if (data.nameExists && data.emailExists) {
                            // Show single notification for both errors
                            if (typeof showNotification === 'function') {
                                showNotification('Nome e email já existentes', 'error');
                            }
                            if (nameError) showError(nameError, 'Este nome já existe');
                            if (emailError) showError(emailError, 'Este email já existe');
                        } else if (data.nameExists) {
                            if (typeof showNotification === 'function') {
                                showNotification('Este nome já existe', 'error');
                            }
                            if (nameError) showError(nameError, 'Este nome já existe');
                        } else if (data.emailExists) {
                            if (typeof showNotification === 'function') {
                                showNotification('Este email já existe', 'error');
                            }
                            if (emailError) showError(emailError, 'Este email já existe');
                        } else {
                            throw new Error(data?.error || 'Não foi possível criar a conta.');
                        }
                        return;
                    }

                    if (successMessage) {
                        successMessage.style.display = 'block';
                    }

                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1200);
                } catch (error) {
                    showNotification(error.message, 'error');
                    showError(formError, error.message);
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
