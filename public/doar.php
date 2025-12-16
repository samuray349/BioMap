<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Doar</title>
    <link rel="icon" type="image/x-icon" href="/static/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body>
    <div id="header-placeholder"></div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="cor-nossa">Proteger a vida juntos</h1>
            <p>Descubra e apoie organizações dedicadas à conservação da preciosa
                vida selvagem do nosso planeta. Cada doação faz a diferença.</p>
            <a href="#organizations" class="btn btn-primary">
                Explore Organizações
                <i data-feather="arrow-right"></i>
            </a>
        </div>
    </section>
    <!-- Organizations Grid -->
    <section id="organizations" class="container" >
        <div class="section-title" >
            <h2>Organizações que recomendamos</h2>
        </div>
        <div class="org-grid" style="padding-bottom: 60px;">
            <!-- WWF -->
            <div class="org-card">
                <div class="org-image">
                    <img src="img/wwf_logo.png" alt="WWF">
                </div>
                <div class="org-content">
                    <h3 class="org-titulo">WWF</h3>
                    <p>Protege espécies e ecossistemas
                        em todo o mundo</p>
                    <a href="https://www.worldwildlife.org/" target="_blank" class="org-link">
                        Visite o Website
                        <i data-feather="external-link"></i>
                    </a>
                </div>
            </div>
            <!-- Wildlife Conservation Society -->
            <div class="org-card">
                <div class="org-image">
                    <img src="img/wcs_logo.png" alt="WCS">
                </div>
                <div class="org-content">
                    <h3 class="org-titulo">Wildlife Conservation Society</h3>
                    <p>Conserva a vida selvagem e habitats
                        através da ciência</p>
                    <a href="https://www.wcs.org/" target="_blank" class="org-link">
                        Visite o Website
                        <i data-feather="external-link"></i>
                    </a>
                </div>
            </div>
            <!-- The Nature Conservancy -->
            <div class="org-card">
                <div class="org-image">
                    <img src="img/ci_logo.png" alt="Conservation International">
                </div>
                <div class="org-content">
                    <h3 class="org-titulo">Conservation International</h3>
                    <p>Trabalha para preservar a natureza
                        e o clima global</p>
                    <a href="https://www.conservation.org" target="_blank" class="org-link">
                        Visite o Website
                        <i data-feather="external-link"></i>
                    </a>
                </div>
            </div>
            <!-- Jane Goodall Institute -->
            <div class="org-card">
                <div class="org-image">
                    <img src="img/panthera_logo.png" alt="Panthera">
                </div>
                <div class="org-content">
                    <h3 class="org-titulo">Panthera</h3>
                    <p>Focada na conservação de grandes
                        felinos em todo o mundo</p>
                    <a href="https://panthera.org" target="_blank" class="org-link">
                        Visite o Website
                        <i data-feather="external-link"></i>
                    </a>
                </div>
            </div>
            <!-- Sea Shepherd -->
            <div class="org-card">
                <div class="org-image">
                    <img src="img/rt_logo.png" alt="Rainforest Trust">
                </div>
                <div class="org-content">
                    <h3 class="org-titulo">Rainforest Trust</h3>
                    <p>Protege florestas tropicais e
                        espécies ameaçadas</p>
                    <a href="https://www.rainforesttrust.org" target="_blank" class="org-link">
                        Visite o Website
                        <i data-feather="external-link"></i>
                    </a>
                </div>
            </div>
            <!-- Rainforest Alliance -->
            <div class="org-card">
                <div class="org-image">
                    <img src="img/se_logo.png" alt="Sea Shepherd">
                </div>
                <div class="org-content">
                    <h3 class="org-titulo">Sea Shepherd</h3>
                    <p>Defende a vida marinha e combate
                        a pesca ilegal</p>
                    <a href="https://seashepherd.org" target="_blank" class="org-link">
                        Visite o Website
                        <i data-feather="external-link"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <script src="js/script.js"></script>
<script>
  loadHeader();
  highlightCurrentPage();
</script>
<script>
        feather.replace();
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>

