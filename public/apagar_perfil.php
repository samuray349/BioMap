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
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
</head>
<body>
    <!-- Header Placeholder -->
    <div id="header-placeholder"></div>
    
    <!-- Delete Account Confirmation Section -->
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
                    <a href="perfil.php" class="btn-back-profile">Voltar ao Perfil</a>
                </div>
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
        });
    </script>
</body>
</html>


