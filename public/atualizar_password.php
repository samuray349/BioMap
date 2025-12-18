<?php
require_once 'access_control.php';
checkAccess(ACCESS_USER);
?>
<?php
// Inject current user info for the page and JS
$user = getCurrentUser();
$userId = getUserId();
$userName = getUserName();
$userEmail = $user['email'] ?? '';
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
</head>
<body>
    <!-- Header Placeholder -->
    <div id="header-placeholder"></div>
    
    <!-- Profile Banner Section -->
    <section class="profile-banner">
        <div class="profile-banner-content">
            <div class="profile-info">
                <h1 class="profile-name"><?php echo htmlspecialchars($userName ?? ''); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($userEmail ?? ''); ?></p>
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
                    <input type="hidden" id="userId" value="<?php echo htmlspecialchars($userId ?? ''); ?>">
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
                    
                    <a href="perfil.php" class="back-link">&lt; Voltar</a>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Scripts -->
    <script src="js/script.js"></script>
    <script>
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

            // Password verification + update flow
            const form = document.getElementById('changePasswordForm');
            const currentPassword = document.getElementById('currentPassword');
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const userIdInput = document.getElementById('userId');

            // helper to compute SHA-256 hex
            async function sha256Hex(message) {
                const encoder = new TextEncoder();
                const data = encoder.encode(message);
                const hashBuffer = await window.crypto.subtle.digest('SHA-256', data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const uid = userIdInput ? userIdInput.value : null;
                if (!uid) {
                    alert('Utilizador não autenticado.');
                    return;
                }

                const cur = currentPassword.value.trim();
                const nw = newPassword.value;
                const conf = confirmPassword.value;

                if (!cur || !nw || !conf) {
                    alert('Preencha todas as password.');
                    return;
                }

                if (nw !== conf) {
                    alert('A nova password e a confirmação não coincidem.');
                    return;
                }

                // Compute SHA-256 on client for verification
                try {
                    const curHash = await sha256Hex(cur);

                    // Verify against server-stored hash
                    const verifyResp = await fetch('/verify-password', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ utilizador_id: uid, password_hash: curHash })
                    });

                    if (!verifyResp.ok) {
                        const err = await verifyResp.json().catch(() => ({}));
                        alert('Erro ao verificar password: ' + (err.error || verifyResp.status));
                        return;
                    }

                    const verifyJson = await verifyResp.json();
                    if (!verifyJson.valid) {
                        alert('Password atual incorreta.');
                        return;
                    }

                    // Current password verified; call existing update endpoint (expects plain passwords)
                    const updateResp = await fetch(`/users/${uid}/password`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ current_password: cur, new_password: nw })
                    });

                    const updateJson = await updateResp.json().catch(() => ({}));
                    if (!updateResp.ok) {
                        alert(updateJson.error || 'Erro ao atualizar password.');
                        return;
                    }

                    alert(updateJson.message || 'Password atualizada com sucesso.');
                    // Redirect back to profile
                    window.location.href = 'perfil.php';
                } catch (err) {
                    console.error('Erro na verificação/atualização da password', err);
                    alert('Erro interno ao processar a request. Veja o console para mais detalhes.');
                }
            });
            
        });
    </script>
</body>
</html>


