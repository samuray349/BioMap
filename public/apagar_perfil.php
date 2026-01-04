<?php
require_once 'status_check.php';
// Allow both admin and regular users
require_funcao_or_redirect([1,2], 'login.php');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Apagar Conta</title>
    
    
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
    
    <!-- Delete Account Confirmation Section -->
    <?php
    // Determine back page based on API-synced role and expose current user info
    $user = $STATUS_CHECK['user'] ?? null;
    $currentUserId = intval($user['id'] ?? 0);
    $currentUserFuncao = intval($user['funcao_id'] ?? 2);
    // Canonical profile page
    $backPage = 'perfil.php';
    ?>
    <section class="delete-account-section">
        <div class="container">
            <div class="delete-account-card">
                <div class="warning-icon-container">
                    <i class="fas fa-exclamation-triangle warning-icon"></i>
                </div>
                
                <h1 class="delete-account-heading">
                    Tem certeza que deseja <span class="highlight-orange">Apagar a Sua Conta?</span>
                </h1>
                
                <p class="delete-account-warning">
                    Se o decidir fazer, irá <span class="highlight-orange">perder todos os seus dados, sem nenhuma possibilidade de os recuperar.</span>
                </p>
                
                <div class="delete-account-buttons">
                    <button type="button" class="btn-delete-account">Entendo os Riscos, Apagar Conta</button>
                    <a href="<?= $backPage ?>" class="btn-back-profile">Voltar ao Perfil</a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Scripts -->
    <script src="js/config.js?v=<?php echo time(); ?>"></script>
    <script src="js/api-toggle.js?v=<?php echo time(); ?>"></script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script>
    // Expose current user id and role to JS
    const CURRENT_USER_ID = <?= $currentUserId ?>;
    const CURRENT_USER_FUNCAO = <?= $currentUserFuncao ?>; // 1=admin, 2=user

    // Create a simple modal confirm (Sim / Não)
    function showConfirmDelete(onConfirm, onCancel) {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = '10000';

        const box = document.createElement('div');
        box.style.background = '#fff';
        box.style.padding = '20px';
        box.style.borderRadius = '8px';
        box.style.maxWidth = '420px';
        box.style.width = '90%';
        box.style.boxShadow = '0 8px 24px rgba(0,0,0,0.2)';

        box.innerHTML = `
            <h3 style="margin-top:0">Confirmar eliminação</h3>
            <p>Tem a certeza que deseja apagar a sua conta? Esta ação é irreversível.</p>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px">
                <button id="confirmNo" style="padding:8px 14px;border:1px solid #ccc;background:#fff;border-radius:6px;">Não</button>
                <button id="confirmYes" style="padding:8px 14px;background:#e05353;color:#fff;border:none;border-radius:6px;">Sim</button>
            </div>
        `;

        overlay.appendChild(box);
        document.body.appendChild(overlay);

        const yesBtn = box.querySelector('#confirmYes');
        const noBtn = box.querySelector('#confirmNo');

        yesBtn.addEventListener('click', () => {
            onConfirm();
            document.body.removeChild(overlay);
        });

        noBtn.addEventListener('click', () => {
            if (onCancel) onCancel();
            document.body.removeChild(overlay);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof loadHeader === 'function') loadHeader();

        const deleteBtn = document.querySelector('.btn-delete-account');
        const backLink = document.querySelector('.btn-back-profile');

        // Ensure back link goes to correct profile page (already set server-side) --- no extra action needed

        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                showConfirmDelete(async function onConfirm() {
                    // Perform deletion
                    try {
                        deleteBtn.disabled = true;
                        deleteBtn.textContent = 'A eliminar...';

                        const endpointPath = `users/${CURRENT_USER_ID}`;
                        const apiUrl = window.API_CONFIG?.getUrl(endpointPath) || `/${endpointPath}`;

                        const resp = await fetch(apiUrl, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ utilizador_id: CURRENT_USER_ID, funcao_id: CURRENT_USER_FUNCAO })
                        });

                        let data = {};
                        try {
                            const text = await resp.text();
                            if (text) {
                                data = JSON.parse(text);
                            }
                        } catch (e) {
                            // Failed to parse response
                        }

                        if (!resp.ok) {
                            const msg = (resp.status === 401 || resp.status === 403) ? (data?.error || 'Não autorizado.') : (data?.error || 'Erro ao eliminar conta.');
                            alert(msg);
                            deleteBtn.disabled = false;
                            deleteBtn.textContent = 'Entendo os Riscos, Apagar Conta';
                            return;
                        }

                        // Success - clear PHP session and local client session, then redirect to index
                        try {
                            // Call server-side PHP logout to clear PHP session/cookie
                            await fetch('logout_server.php', { method: 'POST' }).catch(() => {});
                        } catch (e) {
                            // ignore
                        }

                        // Clear client-side session helpers if available
                        if (typeof SessionHelper !== 'undefined' && SessionHelper.clearUser) {
                            SessionHelper.clearUser();
                        }
                        try { localStorage.removeItem('biomapUser'); } catch(e) {}

                        window.location.href = 'index.php';
                    } catch (err) {
                        alert('Erro ao eliminar conta. Tente novamente mais tarde.');
                        deleteBtn.disabled = false;
                        deleteBtn.textContent = 'Entendo os Riscos, Apagar Conta';
                    }
                }, function onCancel() {
                    // No-op on cancel
                });
            });
        }
    });
    </script>
</body>
</html>


