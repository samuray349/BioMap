<?php
require_once 'access_control.php';
checkAccess(ACCESS_USER);

// Get current user data
$user = getCurrentUser();
$userName = $user['name'] ?? '';
$userEmail = $user['email'] ?? '';
$userId = $user['id'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Editar Perfil</title>
    
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
</head>
<body>
    <!-- Header Placeholder -->
    <div id="header-placeholder"></div>
    
    <!-- Profile Banner Section -->
    <section class="profile-banner">
        <div class="profile-banner-content">
            <div class="profile-info">
                <h1 class="profile-name"><?php echo htmlspecialchars($userName); ?></h1>
            </div>
            <div class="profile-picture-container">
                <div class="profile-picture">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Edit Profile Form Section -->
    <section class="edit-profile-section">
        <div class="container">
            <div class="edit-profile-card">
                <h2 class="edit-profile-title">Editar Perfil</h2>
                
                <form class="edit-profile-form" id="editProfileForm">
                    <div class="form-group">
                        <label for="username" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Nome Utilizador</label>
                        <div class="input-wrapper">
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userName); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" style="color: var(--accent-color); display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                        </div>
                    </div>
                    
                    <div id="errorMessage" class="error-message" style="display: none;"></div>
                    <div id="successMessage" class="success-message" style="display: none;"></div>
                    
                    <button type="submit" class="confirm-button">Confirmar Alterações</button>
                    
                    <a href="#" id="back-link" class="back-link">&lt; Voltar</a>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Scripts -->
    <script src="js/config.js?v=<?php echo time(); ?>"></script>
    <script src="js/session.js?v=<?php echo time(); ?>"></script>
    <script src="js/api-toggle.js?v=<?php echo time(); ?>"></script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script>
        // Load header when page loads
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof loadHeader === 'function') {
                loadHeader();
            }
            
            // Handle form submission
            const form = document.getElementById('editProfileForm');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            const submitButton = form.querySelector('.confirm-button');
            
            function showError(message) {
                // Show notification for errors
                if (typeof showNotification === 'function') {
                    showNotification(message, 'error');
                }
                // Also show in error message div for backwards compatibility
                if (errorMessage) {
                    errorMessage.textContent = message;
                    errorMessage.style.display = 'block';
                }
            }
            
            function showSuccess(message) {
                // Show notification for success
                if (typeof showNotification === 'function') {
                    showNotification(message, 'success');
                }
                // Also show in success message div for backwards compatibility
                if (successMessage) {
                    successMessage.textContent = message;
                    successMessage.style.display = 'block';
                }
            }
            
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Hide previous messages
                if (errorMessage) errorMessage.style.display = 'none';
                if (successMessage) successMessage.style.display = 'none';
                
                // Get current user from session
                const user = SessionHelper?.getCurrentUser() || JSON.parse(getCookie('biomap_user') || '{}');
                if (!user || !user.id) {
                    showError('Erro: Sessão inválida. Por favor, inicie sessão novamente.');
                    return;
                }
                
                const username = document.getElementById('username').value.trim();
                const email = document.getElementById('email').value.trim();
                
                // Basic validation
                if (!username) {
                    showError('Por favor, preencha o campo do nome.');
                    return;
                }
                
                if (!email) {
                    showError('Por favor, preencha o campo do email.');
                    return;
                }
                
                // Email format validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showError('Por favor, insira um email válido.');
                    return;
                }
                
                // Disable submit button
                submitButton.disabled = true;
                submitButton.textContent = 'A atualizar...';
                
                try {
                    // Get API URL
                    const apiUrl = window.API_CONFIG?.getUrl(`users/${user.id}`) || `/users/${user.id}`;
                    
                    const response = await fetch(apiUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            nome_utilizador: username,
                            email: email
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        const errorMsg = data?.error || 'Erro ao atualizar perfil.';
                        showError(errorMsg);
                        throw new Error(errorMsg);
                    }
                    
                    // Update session data with API response data
                    // Map API response fields to session format
                    // API returns: utilizador_id, nome_utilizador, email (and possibly funcao_id, estado_id)
                    // Session expects: id, name, email, funcao_id, etc.
                    if (user) {
                        // Update with API response data if available
                        if (data.nome_utilizador !== undefined) {
                            user.name = data.nome_utilizador;
                        } else {
                            user.name = username; // Fallback to form value
                        }
                        
                        if (data.email !== undefined) {
                            user.email = data.email;
                        } else {
                            user.email = email; // Fallback to form value
                        }
                        
                        // Preserve other session fields that weren't updated
                        // (funcao_id, estado_id, etc. should remain unchanged)
                        
                        // Update cookie (used by PHP and JavaScript)
                        if (typeof SessionHelper !== 'undefined' && SessionHelper.setUser) {
                            SessionHelper.setUser(user);
                        } else {
                            setCookie('biomap_user', JSON.stringify(user), 7);
                        }
                        
                        // Update localStorage (needed for header update and JavaScript)
                        localStorage.setItem('biomapUser', JSON.stringify(user));
                        
                        // Update PHP session on server side and wait for it to complete
                        await fetch('set_session.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ user: user })
                        }).then(response => {
                            return response;
                        }).catch(err => {
                            // Failed to update PHP session
                        });
                    }
                    
                    // Show success notification
                    showSuccess('Perfil atualizado com sucesso!');
                    
                    // Update banner with updated name from session
                    const profileName = document.querySelector('.profile-name');
                    if (profileName && user && user.name) {
                        profileName.textContent = user.name;
                    }
                    
                    // Update header with new name (triggers header refresh)
                    if (typeof applyHeaderAuthState === 'function') {
                        applyHeaderAuthState();
                    }
                    
                    // Also trigger a header reload if available
                    if (typeof loadHeader === 'function') {
                        loadHeader();
                    }
                    
                    // Redirect to canonical profile page after a short delay
                    setTimeout(() => {
                        window.location.href = 'perfil.php';
                    }, 1000);
                    
                } catch (error) {
                    // Error notification is already shown in the try block, but show it here as fallback
                    if (!error.message || error.message === 'Erro ao atualizar perfil.') {
                        showError('Erro ao atualizar perfil. Por favor, tente novamente.');
                    }
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Confirmar Alterações';
                }
            });
            
            // Handle "Voltar" link click - redirect based on funcao_id
            const backLink = document.getElementById('back-link');
            if (backLink) {
                backLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get current user from session
                    const user = SessionHelper?.getCurrentUser() || JSON.parse(getCookie('biomap_user') || '{}');
                    
                    // Back to canonical profile page
                    const redirectUrl = 'perfil.php';
                    
                    // Redirect
                    window.location.href = redirectUrl;
                });
            }
        });
        
        // Helper function to get cookie (if SessionHelper not loaded)
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) {
                    return decodeURIComponent(c.substring(nameEQ.length, c.length));
                }
            }
            return null;
        }
        
        // Helper function to set cookie (if SessionHelper not loaded)
        function setCookie(name, value, days = 7) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
        }
    </script>
    
    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>
</body>
</html>


