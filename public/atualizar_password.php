<?php
require_once 'status_check.php';
// Allow both admin and regular users to access this page
require_funcao_or_redirect([1,2], 'login.php');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Alterar Password</title>
    
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="js/config.js"></script>
    <script src="js/session.js"></script>
</head>
<body>
    <!-- Header Placeholder -->
    <div id="header-placeholder"></div>
    
    <!-- Profile Banner Section -->
    <section class="profile-banner">
        <div class="profile-banner-content">
            <div class="profile-info">
                <?php
                // Use API-synced user info from status_check
                $currentUser = $STATUS_CHECK['user'] ?? null;
                $displayName = htmlspecialchars($currentUser['name'] ?? '[Nome utilizador]', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $displayEmail = htmlspecialchars($currentUser['email'] ?? '[Email]', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $currentUserId = intval($currentUser['id'] ?? 0);
                // funcao_id: 1 = admin, 2 = utilizador (fallback to 2)
                $currentUserFuncao = intval($currentUser['funcao_id'] ?? 2);
                ?>
                <h1 class="profile-name"><?= $displayName ?></h1>
                <p class="profile-email"><?= $displayEmail ?></p>
            </div>
            <div class="profile-picture-container">
                <div class="profile-picture">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Change Password Form Section -->
    <section class="edit-profile-section">
        <div class="container">
            <div class="edit-profile-card">
                <h2 class="edit-profile-title">Alterar Password</h2>
                
                <form class="edit-profile-form" id="changePasswordForm">
                    <div class="form-group">
                        <div class="input-wrapper password-wrapper">
                            <input type="password" id="currentPassword" name="currentPassword" required>
                            <label for="currentPassword">Password Atual</label>
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <span class="eye-icon"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-wrapper password-wrapper">
                            <input type="password" id="newPassword" name="newPassword" required>
                            <label for="newPassword">Nova Password</label>
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <span class="eye-icon"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-wrapper password-wrapper">
                            <input type="password" id="confirmPassword" name="confirmPassword" required>
                            <label for="confirmPassword">Confirmar Nova Password</label>
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <span class="eye-icon"></span>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="confirm-button">Confirmar Alterações</button>
                    <div class="error-message" id="formError" role="alert" style="display:none;margin-top:10px"></div>
                    <div class="success-message" id="successMessage" style="display:none;margin-top:10px">
                        <div class="success-icon">✓</div>
                        <h3>Password atualizada com sucesso!</h3>
                        <p>Redirecionando...</p>
                    </div>
                    
                    <a href="perfil.php" class="back-link">&lt; Voltar</a>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Scripts -->
    <script src="js/script.js"></script>
    <script>
    // Provide the current user id and role to JS safely
    const CURRENT_USER_ID = <?= $currentUserId ?>;
    const CURRENT_USER_FUNCAO = <?= $currentUserFuncao ?>; // 1=admin, 2=user

        (function() {
            // Helper elements
            const form = document.getElementById('changePasswordForm');
            const currentPasswordInput = document.getElementById('currentPassword');
            const newPasswordInput = document.getElementById('newPassword');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const formError = document.getElementById('formError');
            const successMessage = document.getElementById('successMessage');
            const submitButton = form.querySelector('.confirm-button');

            function showError(message) {
                if (formError) {
                    formError.textContent = message;
                    formError.style.display = 'block';
                } else {
                    alert(message);
                }
            }

            function clearError() {
                if (formError) {
                    formError.textContent = '';
                    formError.style.display = 'none';
                }
            }

            function setLoading(isLoading) {
                if (submitButton) {
                    submitButton.disabled = isLoading;
                    submitButton.textContent = isLoading ? 'A processar...' : 'Confirmar Alterações';
                }
            }

            form?.addEventListener('submit', async function(e) {
                e.preventDefault();
                clearError();

                const currentPassword = currentPasswordInput.value || '';
                const newPassword = newPasswordInput.value || '';
                const confirmPassword = confirmPasswordInput.value || '';

                if (!currentPassword.trim()) {
                    showError('Insira a password atual.');
                    return;
                }

                if (!newPassword) {
                    showError('Insira a nova password.');
                    return;
                }

                // Confirm passwords match
                if (newPassword !== confirmPassword) {
                    showError('A nova password e a confirmação não coincidem.');
                    // Additionally send a browser alert for immediate attention
                    alert('A nova password e a confirmação não coincidem.');
                    confirmPasswordInput.focus();
                    return;
                }

                // New password should not be the same as current password
                if (currentPassword && currentPassword === newPassword) {
                    showError('A nova password deve ser diferente da password atual.');
                    alert('A nova password deve ser diferente da password atual.');
                    newPasswordInput.focus();
                    return;
                }

                // Basic strong password policy: min 8 chars, at least one lowercase, one uppercase and one digit
                const pwdPolicy = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                if (!pwdPolicy.test(newPassword)) {
                    showError('A nova password deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula e um número.');
                    alert('A nova password deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula e um número.');
                    newPasswordInput.focus();
                    return;
                }

                if (!CURRENT_USER_ID || CURRENT_USER_ID <= 0) {
                    showError('Erro: utilizador não identificado. Por favor inicie sessão novamente.');
                    return;
                }

                setLoading(true);

                try {
                    const endpointPath = `users/${CURRENT_USER_ID}/password`;
                    const apiUrl = window.API_CONFIG?.getUrl(endpointPath) || `/${endpointPath}`;

                    const resp = await fetch(apiUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ current_password: currentPassword, new_password: newPassword })
                    });

                    const data = await resp.json().catch(() => ({}));

                    if (!resp.ok) {
                        // If server indicates unauthorized (wrong current password) show a clear message
                        if (resp.status === 401) {
                            const errMsg = 'Password errada.';
                            showError(errMsg);
                            // Immediate attention for the user
                            alert(errMsg);
                            if (currentPasswordInput) currentPasswordInput.focus();
                            return;
                        }

                        // Show server-sent error message when available
                        const msg = data?.error || data?.message || 'Erro ao atualizar password.';
                        showError(msg);
                        return;
                    }

                    // Success
                    if (successMessage) successMessage.style.display = 'block';

                    // Clear sensitive inputs
                    currentPasswordInput.value = '';
                    newPasswordInput.value = '';
                    confirmPasswordInput.value = '';

                    // Optionally update local cookie/session user data if password change affects session
                    setTimeout(() => {
                        // Redirect to canonical profile page
                        window.location.href = 'perfil.php';
                    }, 900);

                } catch (err) {
                    console.error('Erro ao chamar API de alterar password', err);
                    showError('Erro de rede ao tentar atualizar a password.');
                } finally {
                    setLoading(false);
                }
            });
        })();

        // Load header when page loads
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof loadHeader === 'function') {
                loadHeader();
            }
            
            // Handle form input labels
            const inputs = document.querySelectorAll('.edit-profile-form input');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.trim() !== '') {
                        this.classList.add('has-value');
                    } else {
                        this.classList.remove('has-value');
                    }
                });
            });
            
            // Password toggle functionality
            const passwordToggles = document.querySelectorAll('.password-toggle');
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const icon = this.querySelector('.eye-icon');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.add('show-password');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('show-password');
                    }
                });
            });
        });
    </script>
</body>
</html>


