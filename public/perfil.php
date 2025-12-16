<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Perfil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
</head>
<body>
    <div id="header-placeholder"></div>

    <section class="profile-banner">
        <div class="profile-banner-content">
            <div class="profile-info">
                <h1 class="profile-name">[Nome utilizador]</h1>
                <p class="profile-email">[Email]</p>
            </div>
            <div class="profile-picture-container">
                <div class="profile-picture">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </section>

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
