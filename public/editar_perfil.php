<?php
require_once 'access_control.php';
checkAccess(ACCESS_USER);
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
    
    <!-- Edit Profile Form Section -->
    <section class="edit-profile-section">
        <div class="container">
            <div class="edit-profile-card">
                <h2 class="edit-profile-title">Editar Perfil</h2>
                
                <form class="edit-profile-form" id="editProfileForm">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <input type="text" id="username" name="username" required>
                            <label for="username">Nome Utilizador</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" required>
                            <label for="email">Email</label>
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
        });
    </script>
</body>
</html>


