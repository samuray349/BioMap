<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Sobre nós</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    
    <script src="js/config.js"></script>

    <style>
        .family i{
            color: var(--accent-color);
        }
        /* Botão "Ver detalhes" */
.btn-details {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--accent-color);
    color: white;
    padding: 5px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: background 0.2s ease;
    margin-top: auto;
}

.btn-details:hover {
    background: var(--accent-hover);
}
    </style>
</head>
<body>
    <div id="header-placeholder"></div>
    
    <main class="sobre-nos-content">
        <div class="sobre-nos-logo-section">
            <div class="koala-logo">
                <img src="img/biomap-icon.png" alt="biomap" height="200" width="200">
            </div>
            <h1 class="sobre-nos-logo-text">BioMap</h1>
        </div>
        
        <div class="quem-somos-card">
            <h2 class="quem-somos-title">Quem Somos?</h2>
            <p class="quem-somos-text">
                O BioMap é um website dedicado a mostrar os animais em perigo de extinção em Portugal, 
                permitindo aos utilizadores visualizar, aprender e reportar espécies que estão em risco 
                de desaparecer.
            </p>
        </div>
        
        <h2 class="species-section-title">Conheça Algumas Espécies que Visamos Proteger</h2>
        
        <div class="cards-grid">
            <!-- Animal cards will be loaded dynamically from API -->
        </div>
        
        <div class="sobre-nos-footer">
            <p class="footer-message">
                A mão humana causou um grande impacto negativo no reino animal, o que levou à extinção de muitas 
                lindas espécies que residiam no nosso mundo. Eles precisam da nossa ajuda mais do que nunca. 
                <strong class="footer-highlight">Juntos podemos fazer a diferença.</strong>
            </p>
        </div>
    </main>
    
    <script src="js/script.js"></script>
    <script src="js/animals.js"></script>
    <script>
        // Initialize after scripts are loaded
        async function initSobreNosPage() {
            // Load header
            if (typeof loadHeader === 'function') {
                loadHeader();
            }
            if (typeof highlightCurrentPage === 'function') {
                highlightCurrentPage();
            }
            
            // Load and display animal cards
            const cardsGrid = document.querySelector('.cards-grid');
            
            if (cardsGrid && typeof fetchAnimals === 'function' && typeof renderAnimalCards === 'function') {
                try {
                    // Load first 4 animals (or all if less than 4)
                    const animals = await fetchAnimals({});
                    const displayAnimals = animals.slice(0, 4); // Show first 4 animals
                    renderAnimalCards(displayAnimals, cardsGrid, {
                        emptyMessage: 'Nenhum animal encontrado.'
                    });
                } catch (error) {
                    console.error("Erro ao carregar animais:", error);
                    if (cardsGrid) cardsGrid.innerHTML = '<p>Erro ao carregar dados.</p>';
                }
            }
        }
        
        // Wait for window to fully load (ensures all scripts are executed)
        window.addEventListener('load', initSobreNosPage);
    </script>
</body>
</html>
