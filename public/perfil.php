<?php
require_once 'status_check.php';
require_once 'access_control.php';

// Allow both admins (1) and regular users (2) to access the canonical profile page.
// The page will render role-appropriate actions based on funcao_id.
require_funcao_or_redirect([1,2], 'login.php');

// Get user info and determine role
$user = $STATUS_CHECK['user'] ?? null;
$funcao_id = intval($user['funcao_id'] ?? 0);
$isAdmin = $funcao_id === 1;

// For admin users, ensure they have admin access (validation from perfil_admin.php)
if ($isAdmin) {
    checkAccess(ACCESS_ADMIN);
}

$profileName = htmlspecialchars($user['name'] ?? '[Nome utilizador]', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$profileEmail = htmlspecialchars($user['email'] ?? '[Email]', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - <?= $isAdmin ? 'Perfil Admin' : 'Perfil' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="js/config.js"></script>
    <script src="js/session.js"></script>
    <script src="js/api-toggle.js"></script>
</head>
<body<?= $isAdmin ? ' class="admin-dashboard-page"' : '' ?>>
    <div id="header-placeholder"></div>

    <section class="profile-banner">
        <div class="profile-banner-content">
            <div class="profile-info">
                <h1 class="profile-name"><?= $profileName ?></h1>
                <p class="profile-email"><?= $profileEmail ?></p>
            </div>
            <div class="profile-picture-container">
                <div class="profile-picture">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </section>

    <?php if ($isAdmin): ?>
        <!-- Admin Dashboard Layout (from perfil_admin.php) -->
        <section class="admin-dashboard">
            <div class="admin-dashboard-grid">

                <a href="editar_perfil.php" class="admin-dashboard-card">
                    <div class="admin-dashboard-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <h3 class="admin-dashboard-title">Editar Perfil</h3>
                </a>

                <a href="atualizar_password.php" class="admin-dashboard-card">
                    <div class="admin-dashboard-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="admin-dashboard-title">Alterar Password</h3>
                </a>

                <a href="admin_animal.php" class="admin-dashboard-card admin-dashboard-card--yellow">
                    <div class="admin-dashboard-icon">
                        <i class="fas fa-paw"></i>
                    </div>
                    <h3 class="admin-dashboard-title">Administrar registo animal</h3>
                </a>

                <a href="admin_util.php" class="admin-dashboard-card admin-dashboard-card--yellow">
                    <div class="admin-dashboard-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <h3 class="admin-dashboard-title">Administrar Utilizadores</h3>
                </a>
                <a href="adicionar_animal.php" class="admin-dashboard-card admin-dashboard-card--yellow">
                    <div class="admin-dashboard-icon">
                        <i class="fa-solid fa-plus"></i>
                        <i class="fas fa-paw"></i>
                    </div>
                    <h3 class="admin-dashboard-title">Adicionar Animal</h3>
                </a>
                <a href="adicionar_fundacao.php" class="admin-dashboard-card admin-dashboard-card--yellow">
                    <div class="admin-dashboard-icon">
                        <i class="fa-solid fa-plus"></i>
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <h3 class="admin-dashboard-title">Adicionar Instituição</h3>
                </a>
                

                <a href="logout.php" class="action-link-wrapper action-link-wrapper-wide">
                    <div class="action-card log-out">
                        <div class="action-icon red-icon">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <h3 class="action-title red-title">Terminar Sessão</h3>
                    </div>
                </a>

            </div>
        </section>
    <?php else: ?>
        <!-- Regular User Layout (from perfil.php) -->
        <section class="actions-section">
            <div class="container">
                <div class="actions-grid">

                    <a href="editar_perfil.php" class="action-link-wrapper">
                        <div class="action-card edit-profile">
                            <div class="action-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <h3 class="action-title">Editar Perfil</h3>
                        </div>
                    </a>

                    <a href="atualizar_password.php" class="action-link-wrapper">
                        <div class="action-card change-password">
                            <div class="action-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h3 class="action-title">Alterar Password</h3>
                        </div>
                    </a>

                    <a href="logout.php" class="action-link-wrapper action-link-wrapper-wide">
                        <div class="action-card log-out">
                            <div class="action-icon red-icon">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <h3 class="action-title red-title">Terminar Sessão</h3>
                        </div>
                    </a>

                </div>
            </div>
        </section>
    <?php endif; ?>

    <footer class="delete-account-footer">
        <a href="apagar_perfil.php" class="delete-account-link">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Apagar Conta</span>
        </a>
    </footer>

    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof loadHeader === 'function') loadHeader();
        });
    </script>
</body>
</html>
