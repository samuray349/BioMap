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
        });
    </script>
</body>
</html>


