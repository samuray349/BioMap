<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Atualizar Password</title>
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
                <h2>Criar Password</h2>
                <p>Email confirmado! Crie uma nova palavra passe.</p>
            </div>
            
            <form class="login-form" id="loginForm" novalidate>
                <div class="form-group">
                    <div class="input-wrapper password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <label for="password">Nova Password</label>
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                            <span class="eye-icon"></span>
                        </button>
                    </div>
                    <span class="error-message" id="passwordError"></span>
                </div>
                
                <div class="form-group">
                    <div class="input-wrapper password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <label for="password">Confirmar Password</label>
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                            <span class="eye-icon"></span>
                        </button>
                    </div>
                    <span class="error-message" id="passwordError"></span>
                </div>

                <button type="submit" class="login-btn">
                    <span class="btn-text">Confirmar</span>
                    <span class="btn-loader"></span>
                </button>
            </form>

            <div class="success-message" id="successMessage">
                <div class="success-icon">✓</div>
                <h3>Login Successful!</h3>
                <p>Redirecting to your dashboard...</p>
            </div>
        </div>
    </div>

    <script src="../../shared/js/form-utils.js"></script>
    <script src="js/script-login.js?v=<?php echo time(); ?>"></script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script>
        if (typeof loadHeader === 'function') {
            loadHeader();
        }
    </script>
</body>
</html>
