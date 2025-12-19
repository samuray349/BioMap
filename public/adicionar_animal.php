<?php
require_once 'access_control.php';
checkAccess(ACCESS_ADMIN);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Adicionar Animal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="js/config.js?v=<?php echo time(); ?>"></script>
    <style>
        .preview-section {
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }

        .preview-section .preview-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .preview-section .preview-header h2 {
            margin: 8px 0 0;
            font-size: 1.8rem;
            color: var(--accent-color);
            margin-top: 3.5rem;
        }

        .preview-section .preview-block {
            margin-bottom: 48px;
        }

        .preview-section .preview-title {
            font-size: 1.25rem;
            margin-bottom: 16px;
            color: var(--text-dark, #1f2937);
        }

        .preview-section .preview-card {
            max-width: 360px;
        }

        .preview-section .animal-card {
            background-color: #ffffff;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px #1987548c;
            overflow: hidden;
            display: flex;
            flex-direction: row;
            border: 1px solid rgba(0, 0, 0, 0.05);
            align-items: stretch;
        }

        .preview-section .animal-card.main {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto 32px;
        }

        .preview-section .description-preview .animal-card.main {
            transform: scale(0.7);
            transform-origin: center top;
        }

        .preview-section .animal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 13px #1987548c;
        }

        .preview-section .card-image-section {
            width: 65%;
            position: relative;
        }

        .preview-section .card-image-section img {
            width: 100%;
            height: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .preview-section .card-content-section {
            width: 35%;
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .preview-section .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 10px;
        }

        .preview-section .animal-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--accent-color, #198754);
            margin: 0;
            line-height: 1.1;
        }

        .preview-section .scientific-name {
            font-style: italic;
            color: #6b7280;
            font-size: 1rem;
            margin-top: 0.25rem;
        }

        .preview-section .status-badge {
            background-color: #ffc107;
            color: #ffffff;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            box-shadow: 0 2px 5px rgba(255, 193, 7, 0.3);
            white-space: nowrap;
            align-self: flex-start;
        }

        .preview-section .section-title {
            color: var(--accent-color, #198754);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 12px;
            margin-top: 0;
            text-align: left;
        }

        .preview-section .facts-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 1400px) {
            .preview-section .facts-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .preview-section .fact-label {
            color: var(--accent-color, #198754);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 2px;
            display: block;
        }

        .preview-section .fact-value {
            color: #1f2937;
            font-weight: 500;
            font-size: 1rem;
        }

        .preview-section .description-text {
            color: #4b5563;
            line-height: 1.6;
            font-size: 0.95rem;
            margin: 0;
        }

        .preview-section .preview-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        .preview-section .preview-info-card {
            flex-direction: column;
            padding: 24px;
            height: 100%;
        }

        /* Visual cue for blocked taxonomy selects */
        .taxonomy-disabled {
            opacity: 0.6;
            cursor: not-allowed;
            filter: grayscale(0.4);
            background-color: #d8dde4 !important;
            color: #4b5563 !important;
        }

        /* Submit feedback */
        .submit-message {
            margin-top: 12px;
            font-size: 0.95rem;
        }
        .submit-message.error {
            color: #b91c1c;
        }
        .submit-message.success {
            color: #047857;
        }

        /* Success Animation Overlay */
        .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .success-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .success-content {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .success-checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--accent-color, #198754);
            position: relative;
            animation: scaleIn 0.5s ease-out;
        }

        .success-checkmark::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid var(--accent-color, #198754);
            animation: ripple 1s ease-out infinite;
        }

        .success-checkmark svg {
            width: 50px;
            height: 50px;
            stroke: white;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: drawCheckmark 0.6s ease-out 0.3s forwards;
        }

        .success-message-popup {
            font-size: 2rem;
            font-weight: 600;
            color: var(--accent-color, #198754);
            margin: 1rem 0 0 0;
            padding: 0;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.4s ease-out 0.4s forwards;
            position: relative;
            z-index: 1;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        @keyframes drawCheckmark {
            to {
                stroke-dashoffset: 0;
            }
        }

        @keyframes ripple {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .preview-section .preview-info-card h3 {
            color: var(--accent-color, #198754);
            margin: 0 0 16px;
        }

        .preview-section .threat-item {
            position: relative;
            padding-left: 20px;
            color: #4b5563;
            margin-bottom: 12px;
            list-style: none;
        }

        .preview-section .threat-item::before {
            content: "";
            position: absolute;
            left: 0;
            top: 8px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #198754;
        }

        .preview-section .conservation-status {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .preview-section .status-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffcc00;
            color: #1f1f1f;
            font-size: 20px;
        }

        .preview-section .donate-btn {
            margin-top: auto;
            background-color: var(--accent-color, #198754);
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .preview-section .preview-fact-card {
            flex-direction: row;
            align-items: flex-start;
            padding: 24px;
            gap: 16px;
        }

        .preview-section .preview-fact-card .status-icon {
            width: 3rem;
            height: 2rem;
            background: linear-gradient(145deg, #1b7f4a, #23a05b);
            color: #fff;
            border: 3px solid #e6f4ec;
            font-size: 24px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .preview-section .preview-share-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 16px;
        }

        .preview-section .preview-share-row button {
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 999px;
            padding: 6px 18px;
            font-size: 0.9rem;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .preview-section .animal-card {
                flex-direction: column;
            }

            .preview-section .card-image-section,
            .preview-section .card-content-section {
                width: 100%;
            }

            .preview-section .card-content-section {
                padding: 2rem;
            }
        }

        .preview-section .status-badge.threatened { background-color: #99CC33; color: #fff; }   /* Green */
    .preview-section .status-badge.vulnerable { background-color: #FFCC00; color: #fff; }   /* Yellow */
    .preview-section .status-badge.endangered { background-color: #FF6600; color: #fff; }   /* Orange */
    .preview-section .status-badge.critical   { background-color: #FF0000; color: #fff; }   /* Red */
    .preview-section .status-badge.extinct   { background-color: #828282; color: #fff; }   /* Gray */

    /* Colors for the Mini Badge (.badge) - ensuring they match */
    .preview-section .badge.threatened { background-color: #99CC33 !important; color: #fff; }
    .preview-section .badge.vulnerable { background-color: #FFCC00 !important; color: #fff; }
    .preview-section .badge.endangered { background-color: #FF6600 !important; color: #fff; }
    .preview-section .badge.critical   { background-color: #FF0000 !important; color: #fff; }
    .preview-section .badge.extinct   { background-color: #828282 !important; color: #fff; }
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

    /* Family searchable select styling - matches chip-select */
    .family-select-wrapper {
        position: relative;
        margin-top: 0;
    }
    
    .family-search-input {
        cursor: text;
    }
    
    .family-select-wrapper .dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        z-index: 1000;
        background: white;
        border: 1px solid #d9dee7;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        max-height: 300px;
        overflow-y: auto;
        display: none;
        padding: 8px 0;
    }
    
    .family-select-wrapper .dropdown-menu.show {
        display: block;
    }
    
    .family-select-wrapper .dropdown-item {
        padding: 12px 20px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        font-size: 15px;
        font-weight: 500;
        color: #0f172a;
    }
    
    .family-select-wrapper .dropdown-item:hover {
        background-color: #eef2f8;
    }
    
    .family-select-wrapper .dropdown-item:first-child {
        border-radius: 20px 20px 0 0;
    }
    
    .family-select-wrapper .dropdown-item:last-child {
        border-radius: 0 0 20px 20px;
    }
    </style>
</head>
<body>
    <!-- Success Animation Overlay -->
    <div id="success-overlay" class="success-overlay">
        <div class="success-content">
            <div class="success-checkmark">
                <svg viewBox="0 0 24 24">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
            </div>
            <h1 class="success-message-popup">Animal criado com sucesso</h1>
        </div>
    </div>

    <div id="header-placeholder"></div>

    <main class="add-animal-page">
        <section class="add-animal-card">
            <div class="add-animal-header">
                <div>
                    <h1>Adicionar Animal</h1>
                    <p class="subtitle">Preencha todos os campos para publicar um novo animal na plataforma.</p>
                </div>
                <button class="ghost-btn" type="button">
                    <i class="fa-solid fa-rotate"></i>
                    Limpar formulário
                </button>
            </div>

            <form class="add-animal-form">
                <div class="form-grid two-columns">
                    <div class="input-field">
                        <label for="animal-name">Nome Animal</label>
                        <input id="animal-name" type="text" placeholder="Ex: Lince Ibérico">
                    </div>
                    <div class="input-field">
                        <label for="scientific-name">Nome Científico</label>
                        <input id="scientific-name" type="text" placeholder="Ex: Lynx pardinus">
                    </div>
                </div>

                <section class="add-section taxonomy-section">
                    <div class="section-heading">
                        <h2>Família</h2>
                        <p>Selecione a família relacionada com o animal registado.</p>
                    </div>

                    <div class="family-select-wrapper">
                        <input type="text" id="family-input" class="chip-select family-search-input" placeholder="Selecione uma família" autocomplete="off">
                        <div class="dropdown-menu" id="family-dropdown"></div>
                    </div>
                </section>

                <section class="add-section diet-section">
                    <div class="section-heading">
                        <h2>Dietas</h2>
                        <p>Identifique a dieta predominante do animal.</p>
                    </div>
                    <select id="diet-type" name="diet-type" class="chip-select" aria-label="Dieta">
                        <option value="">Selecione a dieta</option>
                        <option value="Carnívoro">Carnívoro</option>
                        <option value="Herbívoro">Herbívoro</option>
                        <option value="Omnívoro">Omnívoro</option>
                    </select>
                </section>

                <section class="add-section threats-section">
                    <div class="section-heading">
                        <h2>Ameaças</h2>
                        <p>Registe até cinco ameaças principais e o estado de conservação atual.</p>
                    </div>
                    <div class="form-grid two-columns">
                        <div class="input-field">
                            <label for="threat-1">Ameaça 1</label>
                            <input id="threat-1" type="text" placeholder="Desflorestação, caça ilegal...">
                        </div>
                        <div class="input-field">
                            <label for="threat-2">Ameaça 2</label>
                            <input id="threat-2" type="text">
                        </div>
                        <div class="input-field">
                            <label for="threat-3">Ameaça 3</label>
                            <input id="threat-3" type="text">
                        </div>
                        <div class="input-field">
                            <label for="threat-4">Ameaça 4</label>
                            <input id="threat-4" type="text">
                        </div>
                        <div class="input-field">
                            <label for="threat-5">Ameaça 5</label>
                            <input id="threat-5" type="text">
                        </div>
                    </div>
                    <select id="conservation-status" name="conservation-status" class="chip-select conservation-row" aria-label="Estado de conservação"
                        onchange="updateConservationBgColor(this)">
                        <option value="">Selecione o estado de conservação</option>
                        <option value="Não Avaliada">Não Avaliada</option>
                        <option value="Dados Insuficientes">Dados Insuficientes</option>
                        <option value="Pouco Preocupante">Pouco Preocupante</option>
                        <option value="Quase Ameaçada">Quase Ameaçada</option>
                        <option value="Vulnerável">Vulnerável</option>
                        <option value="Em Perigo">Em Perigo</option>
                        <option value="Perigo Crítico">Perigo Crítico</option>
                        <option value="Extinto na Natureza">Extinto na Natureza</option>
                        <option value="Extinto">Extinto</option>
                    </select>
                    <script>
                        function updateConservationBgColor(select) {
                            const colorMap = {
                                "": "#d9dee7",
                                "Não Avaliada": "#d9dee7",
                                "Dados Insuficientes": "#d9dee7",
                                "Pouco Preocupante": "#d9dee7",
                                "Quase Ameaçada": "#e0faac",
                                "Vulnerável": "#ffd7a0",
                                "Em Perigo": "#faa973",
                                "Perigo Crítico": "#fa7878",
                                "Extinto na Natureza": "#828282",
                                "Extinto": "#828282"
                            };
                            const accentColorMap = {
                                "": "#6e7a85",
                                "Não Avaliada": "#6e7a85",
                                "Dados Insuficientes": "#6e7a85",
                                "Pouco Preocupante": "#6e7a85",
                                "Quase Ameaçada": "#598d13",
                                "Vulnerável": "#ff9100",
                                "Em Perigo": "#df540a",
                                "Perigo Crítico": "#c60a0a",
                                "Extinto na Natureza": "#222326",
                                "Extinto": "#222326"
                            };
                            // Set background color
                            select.style.backgroundColor = colorMap[select.value] || "#d9dee7";
                            // Set accent color for modern browsers (will affect the dropdown highlight, focus ring, etc.)
                            select.style.setProperty('accent-color', accentColorMap[select.value] || "#6e7a85");
                            // For extra visual hint, also update border color if desired:
                            select.style.borderColor = accentColorMap[select.value] || "#6e7a85";
                            // Optionally set color for font if dark background
                            if(select.value === "Perigo crítico" || select.value === "Extinto na Natureza") {
                                select.style.color = "#fff";
                            } else {
                                select.style.color = "#222326";
                            }
                        }
                        // Initialize background and accent color on DOM load for pre-selected values (editing scenarios)
                        document.addEventListener("DOMContentLoaded", function() {
                            var select = document.getElementById('conservation-status');
                            updateConservationBgColor(select);
                        });
                    </script>
                </section>

                <section class="add-section quick-stats">
                    <div class="form-grid two-columns">
                        <div class="input-field">
                            <label for="population">População Estimada</label>
                            <input id="population" type="text" placeholder="Ex: 1 200 indivíduos">
                        </div>
                        <div class="input-field">
                            <label for="fact">Facto</label>
                            <input id="fact" type="text" placeholder="Curiosidade ou facto relevante">
                        </div>
                    </div>
                </section>

                <section class="add-section description-section">
                    <div class="section-heading">
                        <h2>Descrição detalhada</h2>
                        <p>Conte a história, comportamento e habitat do animal.</p>
                    </div>
                    <div class="description-editor">
                        
                        <label for="description">Descrição</label>
                        <textarea id="description" rows="8" placeholder="Conte a história do animal, habitat, comportamento e outros detalhes importantes..."></textarea>
                    </div>
                </section>

                <section class="add-section image-upload-section">
                    <div class="section-heading">
                        <h2>Adicionar imagem</h2>
                        <p>Utilize fotografias horizontais com boa resolução (1200px x 800px recomendado).</p>
                    </div>
                    <div class="image-upload">
                        <div class="image-upload-body">
                            <label class="upload-box" for="image-input">
                                <div class="upload-instructions">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <p>Arraste e solte a imagem aqui ou <span>clique para fazer upload</span></p>
                                    <small>Formatos aceites: JPG ou PNG até 5MB</small>
                                </div>
                                <input type="file" id="image-input" accept="image/*">
                            </label>
                            <figure class="image-preview">
                                <img src="./img/lince.jpg" alt="Pré-visualização do animal">
                                <figcaption>Imagem de exemplo</figcaption>
                            </figure>
                        </div>
                    </div>
                </section>

                <div class="form-actions add-section">
                    <button type="submit" class="primary-btn">
                        <i class="fa-solid fa-paw"></i>
                        Adicionar Animal
                    </button>
                    <div id="submit-message" class="submit-message" role="status" aria-live="polite"></div>
                </div>
            </form>
        </section>
        
            <section class="preview-section">
                <div class="preview-header">
                    <h2>Veja como o animal aparecerá na plataforma</h2>
                </div>
    
                <div class="preview-block">
                    <h3 class="preview-title">Miniatura</h3>
                    <div class="card green-shadow preview-card">
                        <img src="./img/lince.jpg" alt="Lince ibérico" class="card-image">
                        <div class="card-content">
                            <div class="card-header">
                                <h3>Lince ibérico</h3>
                                <span class="badge vulnerable">Vulnerável</span>
                            </div>
                            <div class="family">
                                <span class="family-icon"><i class="fa-solid fa-people-group"></i></span>
                                <span>Felídeos</span>
                            </div>
                            <p class="description">O lince-ibérico é um felino de tamanho médio, oriundo da Península Ibérica...</p>
                            <a class="btn-details">
                                <span>👁</span> Ver detalhes
                            </a>
                        </div>
                    </div>
                </div>
    
                <div class="preview-block">
                    <h3 class="preview-title">Descrição</h3>
                    <div class="preview-share-row">
                        <button type="button">Partilhar</button>
                    </div>
                    <div class="animal-card main">
                        <div class="card-image-section">
                            <img src="./img/lince.jpg" alt="Lince Ibérico">
                        </div>
    
                        <div class="card-content-section">
                            <div class="card-header-row">
                                <div>
                                    <h1 class="animal-title">Lince Ibérico</h1>
                                    <p class="scientific-name">Lynx pardinus</p>
                                </div>
                                <span class="status-badge">Vulnerável</span>
                            </div>
    
                            <div class="mb-6">
                                <h3 class="section-title">Factos</h3>
                                <div class="facts-grid">
                                    <div>
                                        <span class="fact-label">Família:</span>
                                        <span class="fact-value">Felidae</span>
                                    </div>
                                    <div>
                                        <span class="fact-label">Dieta:</span>
                                        <span class="fact-value">Carnívoro</span>
                                    </div>
                                </div>
                            </div>
    
                            <div>
                                <h3 class="section-title">Descrição</h3>
                                <p class="description-text">O lince-ibérico é um felino de médio porte endêmico da Península Ibérica, considerado o felino mais ameaçado do mundo até recentemente. Reconhecido pelas suas orelhas pontiagudas com tufos pretos, patas longas, cauda curta e o característico “barbicho” facial, o lince-ibérico é um predador ágil e solitário, perfeitamente adaptado aos ecossistemas mediterrânicos.</p>
                            </div>
                        </div>
                    </div>
    
                    <div class="preview-details-grid">
                        <div class="animal-card preview-info-card">
                            <h3>5 Principais Ameaças</h3>
                            <ul style="padding:0; margin:0;">
                                <li class="threat-item">Perda e fragmentação do habitat devido à urbanização</li>
                                <li class="threat-item">Redução das populações das suas presas por doenças</li>
                                <li class="threat-item">Atropelamentos em estradas</li>
                                <li class="threat-item">Caça ilegal e armadilhas destinadas a outros animais</li>
                                <li class="threat-item">Mudanças climáticas que afetam presas e habitat</li>
                            </ul>
                        </div>
                        <div class="animal-card preview-info-card">
                            <h3>Estado de Conservação</h3>
                            <div class="conservation-status">
                                <div class="status-icon">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm" style="margin:0;">Lista Vermelha IUCN</p>
                                    <p class="font-medium" style="margin:0;">Vulnerável</p>
                                    <p class="text-gray-700" style="margin:8px 0 0;">População estimada: 4.000-6.500 indivíduos.</p>
                                </div>
                            </div>
                            <button class="donate-btn" type="button">
                                <i class="fa-solid fa-heart"></i> Doar
                            </button>
                        </div>
                    </div>
    
                    <div class="animal-card preview-fact-card" style="margin-top: 2rem;">
                        <div class="status-icon">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>
                        <div>
                            <h3 style="margin:0 0 8px;">Facto interessante</h3>
                            <p style="margin:0;">Nos anos 2000, restavam menos de 100 indivíduos — à beira da extinção. Graças a programas de reprodução e proteção de habitat, hoje existem mais de 2.000 linces ibéricos livres na Península Ibérica.</p>
                        </div>
                    </div>
                </div>
            </section>
    </main>

    <script src="js/script.js?v=<?php echo time(); ?>"></script>
<script>
    loadHeader();
    highlightCurrentPage();

    // Initialize single-select searchable family dropdown
    (async function initFamilyDropdown() {
        const familyInput = document.getElementById('family-input');
        const familyDropdown = document.getElementById('family-dropdown');
        const wrapper = familyInput ? familyInput.closest('.family-select-wrapper') : null;
        
        if (!familyInput || !familyDropdown) return;
        
        let familyOptions = [];
        
        // Fetch family options from API
        try {
            if (typeof fetchFamilyOptions === 'function') {
                familyOptions = await fetchFamilyOptions();
            } else {
                console.error('fetchFamilyOptions function not available');
                if (typeof showNotification === 'function') {
                    showNotification('Erro: Função de carregamento de famílias não disponível. Recarregue a página.', 'error');
                }
                return;
            }
        } catch (error) {
            console.error('Error fetching family options:', error);
            return;
        }
        
        // Function to render dropdown based on search term
        function renderDropdown() {
            const searchTerm = familyInput.value.toLowerCase().trim();
            const currentValue = familyInput.value.trim();
            
            // Filter options based on search term
            const filteredOptions = familyOptions.filter(opt => 
                opt.toLowerCase().includes(searchTerm)
            );
            
            familyDropdown.innerHTML = '';
            
            if (filteredOptions.length === 0 && searchTerm) {
                const noResults = document.createElement('div');
                noResults.className = 'dropdown-item';
                noResults.textContent = 'Nenhum resultado encontrado';
                noResults.style.cursor = 'default';
                noResults.style.color = '#999';
                familyDropdown.appendChild(noResults);
                familyDropdown.classList.add('show');
            } else if (filteredOptions.length > 0 || searchTerm === '') {
                const optionsToShow = filteredOptions.length > 0 ? filteredOptions : familyOptions;
                optionsToShow.forEach(option => {
                    const item = document.createElement('div');
                    item.className = 'dropdown-item';
                    item.textContent = option;
                    // Highlight if this option is currently selected
                    if (currentValue === option) {
                        item.style.fontWeight = '600';
                        item.style.backgroundColor = '#f0f0f0';
                    }
                    item.addEventListener('click', (e) => {
                        e.stopPropagation();
                        familyInput.value = option;
                        familyDropdown.classList.remove('show');
                        // Trigger updateFamily
                        if (typeof updateFamily === 'function') {
                            updateFamily();
                        }
                        // Trigger input event for form validation
                        familyInput.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    familyDropdown.appendChild(item);
                });
                familyDropdown.classList.add('show');
            } else {
                familyDropdown.classList.remove('show');
            }
        }
        
        // Show dropdown on focus
        familyInput.addEventListener('focus', () => {
            renderDropdown();
        });
        
        // Show dropdown on input
        familyInput.addEventListener('input', () => {
            renderDropdown();
        });
        
        // Show dropdown when clicking wrapper
        if (wrapper) {
            wrapper.addEventListener('click', (e) => {
                if (e.target !== familyInput && !familyDropdown.contains(e.target)) {
                    familyInput.focus();
                    renderDropdown();
                }
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (wrapper && !wrapper.contains(e.target)) {
                familyDropdown.classList.remove('show');
            }
        });
        
        // Keyboard navigation
        familyInput.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' && familyDropdown.classList.contains('show')) {
                e.preventDefault();
                const firstItem = familyDropdown.querySelector('.dropdown-item:not([style*="cursor: default"])');
                if (firstItem) firstItem.focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const firstItem = familyDropdown.querySelector('.dropdown-item:not([style*="cursor: default"])');
                if (firstItem) firstItem.click();
            } else if (e.key === 'Escape') {
                familyDropdown.classList.remove('show');
                familyInput.blur();
            }
        });
    })();

    // Dynamic Preview System
    (function setupDynamicPreviews() {
        // Store default values
        const defaults = {
            animalName: 'Lince ibérico',
            scientificName: 'Lynx pardinus',
            family: 'Felídeos',
            familyScientific: 'Felidae',
            diet: 'Carnívoro',
            conservationStatus: 'Vulnerável',
            population: '4.000-6.500 indivíduos.',
            fact: 'Nos anos 2000, restavam menos de 100 indivíduos — à beira da extinção. Graças a programas de reprodução e proteção de habitat, hoje existem mais de 2.000 linces ibéricos livres na Península Ibérica.',
            description: 'O lince-ibérico é um felino de tamanho médio, oriundo da Península Ibérica...',
            descriptionFull: 'O lince-ibérico é um felino de médio porte endêmico da Península Ibérica, considerado o felino mais ameaçado do mundo até recentemente. Reconhecido pelas suas orelhas pontiagudas com tufos pretos, patas longas, cauda curta e o característico "barbicho" facial, o lince-ibérico é um predador ágil e solitário, perfeitamente adaptado aos ecossistemas mediterrânicos.',
            threats: [
                'Perda e fragmentação do habitat devido à urbanização',
                'Redução das populações das suas presas por doenças',
                'Atropelamentos em estradas',
                'Caça ilegal e armadilhas destinadas a outros animais',
                'Mudanças climáticas que afetam presas e habitat'
            ]
        };

        // Get preview elements
        const previewElements = {
            animalNameMini: document.querySelector('.preview-card h3'),
            animalNameMain: document.querySelector('.animal-title'),
            scientificName: document.querySelector('.scientific-name'),
            // Corrigido o seletor para garantir que a família e a dieta são únicas no facts-grid:
            familyMini: document.querySelector('.family span:last-child'),
            familyMain: document.querySelector('.facts-grid > div:first-child .fact-value'), // Seleciona o 1º div.fact-value
            dietMain: document.querySelector('.facts-grid > div:last-child .fact-value'),   // Seleciona o 2º div.fact-value
            conservationBadgeMini: document.querySelector('.preview-card .badge'),
            conservationBadgeMain: document.querySelector('.status-badge'),
            conservationStatusText: document.querySelector('.conservation-status .font-medium'),
            population: document.querySelector('.conservation-status .text-gray-700'),
            fact: document.querySelector('.preview-fact-card p'),
            descriptionMini: document.querySelector('.preview-card .description'),
            descriptionMain: document.querySelector('.description-text'),
            threats: document.querySelectorAll('.threat-item'),
            imageMini: document.querySelector('.preview-card .card-image'),
            imageMain: document.querySelector('.card-image-section img'),
            imageUploadPreview: document.querySelector('.image-preview img')
        };

        const defaultImages = {
            mini: previewElements.imageMini?.src || '',
            main: previewElements.imageMain?.src || '',
            upload: previewElements.imageUploadPreview?.src || ''
        };

        // Family mapping for display names
        const familyDisplayNames = {
            'Felidae': 'Felídeos',
            'Canidae': 'Canídeos',
            'Ursidae': 'Ursídeos',
            'Bovidae': 'Bovídeos',
            'Cervidae': 'Cervídeos',
            'Mustelidae': 'Mustelídeos',
            'Accipitridae': 'Accipitrídeos',
            'Strigidae': 'Estrigídeos',
            'Falconidae': 'Falconídeos',
            'Boidae': 'Boidídeos',
            'Viperidae': 'Viperídeos',
            'Cyprinidae': 'Ciprinídeos',
            'Cichlidae': 'Ciclídeos'
        };

        // Conservation status badge classes (UPDATED TO USER REQUEST)
        const conservationClasses = {
            'Não Avaliada': 'unknown',
            'Dados Insuficientes': 'unknown',
            'Pouco Preocupante': 'least-concern',
            'Quase Ameaçada': 'threatened',      // Maps to Green (#99CC33)
            'Vulnerável': 'vulnerable',         // Maps to Yellow (#FFCC00)
            'Em Perigo': 'endangered',          // Maps to Orange (#FF6600)
            'Perigo Crítico': 'critical',       // Maps to Red (#FF0000)
            'Extinto na Natureza': 'extinct',   // Maps to Gray
            'Extinto': 'extinct'                // Maps to Gray
        };

        // Helper function to get or default
        function getValueOrDefault(value, defaultValue) {
            return value && value.trim() ? value.trim() : defaultValue;
        }

        // Update animal name
        function updateAnimalName() {
            const input = document.getElementById('animal-name');
            const value = getValueOrDefault(input.value, defaults.animalName);
            if (previewElements.animalNameMini) previewElements.animalNameMini.textContent = value;
            if (previewElements.animalNameMain) previewElements.animalNameMain.textContent = value;
            // Update image alt text
            if (previewElements.imageMini) previewElements.imageMini.alt = value;
            if (previewElements.imageMain) previewElements.imageMain.alt = value;
            if (previewElements.imageUploadPreview) previewElements.imageUploadPreview.alt = value;
        }

        // Update scientific name
        function updateScientificName() {
            const input = document.getElementById('scientific-name');
            const value = getValueOrDefault(input.value, defaults.scientificName);
            if (previewElements.scientificName) {
                previewElements.scientificName.textContent = value;
            }
        }

        // Update family
        function updateFamily() {
            const familyInput = document.getElementById('family-input');
            const selectedFamily = familyInput ? familyInput.value.trim() : '';
            const familyScientific = getValueOrDefault(selectedFamily, defaults.familyScientific);
            const familyDisplay = familyDisplayNames[familyScientific] || familyScientific || defaults.family;

            if (previewElements.familyMini) previewElements.familyMini.textContent = familyDisplay;
            if (previewElements.familyMain) previewElements.familyMain.textContent = familyScientific || defaults.familyScientific;
        }


        // Update diet
        function updateDiet() {
            const select = document.getElementById('diet-type');
            const value = getValueOrDefault(select.value, defaults.diet);
            if (previewElements.dietMain) previewElements.dietMain.textContent = value;
        }

        // Update conservation status (UPDATED)
        function updateConservationStatus() {
            const select = document.getElementById('conservation-status');
            const value = getValueOrDefault(select.value, defaults.conservationStatus);
            const statusClass = conservationClasses[value] || 'vulnerable'; // Determine class once
            
            // 1. Update Miniature Preview Badge
            if (previewElements.conservationBadgeMini) {
                previewElements.conservationBadgeMini.textContent = value;
                // Reset to base class then add the new specific class
                previewElements.conservationBadgeMini.className = 'badge';
                previewElements.conservationBadgeMini.classList.add(statusClass);
            }
            
            // 2. Update Main Description Preview Badge
            if (previewElements.conservationBadgeMain) {
                previewElements.conservationBadgeMain.textContent = value;
                // Apply class to main badge (status-badge)
                // Note: The main badge uses .status-badge, which already has some base styles.
                // We clear existing status classes and add the new one.
                previewElements.conservationBadgeMain.className = 'status-badge'; // Reset to base class
                previewElements.conservationBadgeMain.classList.add(statusClass); // Add the dynamic class
            }
            
            // 3. Update Conservation Status Text
            if (previewElements.conservationStatusText) {
                previewElements.conservationStatusText.textContent = value;
            }
        }

        // Update population
        function updatePopulation() {
            const input = document.getElementById('population');
            const value = getValueOrDefault(input.value, defaults.population);
            if (previewElements.population) {
                previewElements.population.textContent = `População estimada: ${value}`;
            }
        }

        // Update fact
        function updateFact() {
            const input = document.getElementById('fact');
            const value = getValueOrDefault(input.value, defaults.fact);
            if (previewElements.fact) previewElements.fact.textContent = value;
        }

        // Update description
        function updateDescription() {
            const textarea = document.getElementById('description');
            const value = textarea.value.trim();
            
            if (previewElements.descriptionMini) {
                previewElements.descriptionMini.textContent = value || defaults.description;
            }
            
            if (previewElements.descriptionMain) {
                previewElements.descriptionMain.textContent = value || defaults.descriptionFull;
            }
        }

        // Update threats
        function updateThreats() {
            const threatInputs = [
                document.getElementById('threat-1'),
                document.getElementById('threat-2'),
                document.getElementById('threat-3'),
                document.getElementById('threat-4'),
                document.getElementById('threat-5')
            ];

            previewElements.threats.forEach((threatEl, index) => {
                const input = threatInputs[index];
                const value = getValueOrDefault(input.value, defaults.threats[index]);
                threatEl.textContent = value;
            });
        }

        // Update image
        function updateImage() {
            const input = document.getElementById('image-input');
            if (!input) return;

            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageUrl = e.target.result;
                        if (previewElements.imageMini) {
                            previewElements.imageMini.src = imageUrl;
                        }
                        if (previewElements.imageMain) {
                            previewElements.imageMain.src = imageUrl;
                        }
                        if (previewElements.imageUploadPreview) {
                            previewElements.imageUploadPreview.src = imageUrl;
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function setSubmitMessage(message, type = '') {
            const el = document.getElementById('submit-message');
            if (!el) return;
            el.textContent = message || '';
            el.className = `submit-message ${type}`.trim();
        }

        // Attach event listeners
        document.getElementById('animal-name')?.addEventListener('input', updateAnimalName);
        document.getElementById('scientific-name')?.addEventListener('input', updateScientificName);
        document.getElementById('diet-type')?.addEventListener('change', updateDiet);
        document.getElementById('conservation-status')?.addEventListener('change', function() {
            updateConservationStatus();
            updateConservationBgColor(this);
        });
        document.getElementById('population')?.addEventListener('input', updatePopulation);
        document.getElementById('fact')?.addEventListener('input', updateFact);
        document.getElementById('description')?.addEventListener('input', updateDescription);

        // Family input (single-select searchable dropdown)
        const familyInput = document.getElementById('family-input');
        if (familyInput) {
            familyInput.addEventListener('input', updateFamily);
            familyInput.addEventListener('change', updateFamily);
        }

        // Threat inputs
        for (let i = 1; i <= 5; i++) {
            document.getElementById(`threat-${i}`)?.addEventListener('input', updateThreats);
        }

        // Image upload
        updateImage();

        // Clear form button
        document.querySelector('.ghost-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.add-animal-form')?.reset();
            
            // Reset all previews to defaults
            updateAnimalName();
            updateScientificName();
            updateFamily();
            updateDiet();
            updateConservationStatus();
            updatePopulation();
            updateFact();
            updateDescription();
            updateThreats();
            
            // Reset image
            const imageInput = document.getElementById('image-input');
            if (imageInput) imageInput.value = '';
            if (previewElements.imageMini) {
                previewElements.imageMini.src = defaultImages.mini || 'https://t4.ftcdn.net/jpg/10/64/99/85/360_F_1064998553_4RvqeSWINhkKJEyVLzidd8bUKtOV7hIc.jpg';
            }
            if (previewElements.imageMain) {
                previewElements.imageMain.src = defaultImages.main || './img/lince.jpg';
            }
            if (previewElements.imageUploadPreview) {
                previewElements.imageUploadPreview.src = defaultImages.upload || './img/lince.jpg';
            }
            
            // Reset conservation status background
            const conservationSelect = document.getElementById('conservation-status');
            if (conservationSelect) updateConservationBgColor(conservationSelect);
        });

        // Initialize all previews on load
        updateAnimalName();
        updateScientificName();
        updateFamily();
        updateDiet();
        updateConservationStatus();
        updatePopulation();
        updateFact();
        updateDescription();
        updateThreats();
    })();

    // --- Submit handler: send data to API ---
    (function attachAddAnimalSubmitHandler() {
        const form = document.querySelector('.add-animal-form');
        const imageInput = document.getElementById('image-input');
        const messageEl = document.getElementById('submit-message');

        const setMessage = (text, type = '') => {
            if (!messageEl) return;
            messageEl.textContent = text || '';
            messageEl.className = `submit-message ${type}`.trim();
        };

        const showSuccessAnimation = () => {
            const overlay = document.getElementById('success-overlay');
            if (!overlay) return;

            // Show overlay
            overlay.classList.add('show');

            // Hide overlay after animation completes and reset form
            setTimeout(() => {
                overlay.classList.remove('show');
            }, 2500);
        };

        const getSelectedFamily = () => {
            const familyInput = document.getElementById('family-input');
            return familyInput ? familyInput.value.trim() : '';
        };

        const getThreats = () => {
            const threats = [];
            for (let i = 1; i <= 5; i++) {
                const value = document.getElementById(`threat-${i}`)?.value?.trim();
                if (value) threats.push(value);
            }
            return threats;
        };

        const fileToDataURL = (file) => new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = (err) => reject(err);
            reader.readAsDataURL(file);
        });

        if (!form) return;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            try {
                setMessage('A enviar...');

                const family = getSelectedFamily();
                const diet = document.getElementById('diet-type')?.value?.trim();
                const estado = document.getElementById('conservation-status')?.value?.trim();
                const nome = document.getElementById('animal-name')?.value?.trim();
                const cientifico = document.getElementById('scientific-name')?.value?.trim();
                const fact = document.getElementById('fact')?.value?.trim();
                const descricao = document.getElementById('description')?.value?.trim();
                const populationRaw = document.getElementById('population')?.value || '';
                const population = populationRaw.replace(/[^\d]/g, '');
                const threats = getThreats();
                const file = imageInput?.files?.[0];

                // Validate required fields with specific messages
                const missingFields = [];
                if (!nome) missingFields.push('Nome Animal');
                if (!cientifico) missingFields.push('Nome Científico');
                if (!descricao) missingFields.push('Descrição');
                if (!family) missingFields.push('Família');
                if (!diet) missingFields.push('Dieta');
                if (!estado) missingFields.push('Estado de Conservação');
                
                if (missingFields.length > 0) {
                    const errorMessage = `Campos obrigatórios em falta no formulário: ${missingFields.join(', ')}.`;
                    setMessage(''); // Clear loading message
                    if (typeof showNotification === 'function') {
                        showNotification(errorMessage, 'error');
                    }
                    return;
                }
                
                if (!file) {
                    const errorMessage = 'Erro no formulário: Selecione uma imagem para o animal.';
                    setMessage(''); // Clear loading message
                    if (typeof showNotification === 'function') {
                        showNotification(errorMessage, 'error');
                    }
                    return;
                }
                
                // Validate ameaças - must be exactly 5
                const threatsCount = threats.length;
                if (threatsCount !== 5) {
                    const errorMessage = `Erro no formulário: Deve preencher exatamente 5 ameaças. Atualmente tem ${threatsCount} ameaça${threatsCount !== 1 ? 's' : ''}.`;
                    setMessage(''); // Clear loading message
                    if (typeof showNotification === 'function') {
                        showNotification(errorMessage, 'error');
                    }
                    return;
                }

                const base64Image = await fileToDataURL(file);

                // First, upload the image to get a URL
                setMessage('A fazer upload da imagem...');
                const uploadResponse = await fetch('upload_image.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        imagem: {
                            data: base64Image,
                            originalName: file.name
                        }
                    })
                });

                const uploadResult = await uploadResponse.json();
                if (!uploadResponse.ok || !uploadResult.success) {
                    const errorMsg = uploadResult?.error || 'Erro desconhecido';
                    throw new Error(`Erro ao fazer upload da imagem: ${errorMsg}. Verifique se a imagem é válida e tente novamente.`);
                }

                // Now send the animal data with the image URL
                setMessage('A guardar o animal...');
                const payload = {
                    nome_comum: nome,
                    nome_cientifico: cientifico,
                    descricao,
                    facto_interessante: fact,
                    populacao_estimada: population ? Number(population) : null,
                    familia_nome: family,
                    dieta_nome: diet,
                    estado_nome: estado,
                    ameacas: threats,
                    imagem_url: uploadResult.url
                };

                const apiUrl = window.API_CONFIG?.getUrl('animais') || '/animais';
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok) {
                    const errorMsg = result?.error || 'Erro desconhecido';
                    const errorDetails = result?.details ? ` Detalhes: ${result.details}` : '';
                    throw new Error(`${errorDetails}`);
                }

                // Clear the loading message
                setMessage('');
                
                // Show success notification
                if (typeof showNotification === 'function') {
                    showNotification('Animal criado com sucesso!', 'success');
                }
                
                // Show success animation
                showSuccessAnimation();

                // Reutilizar botão de limpar para resetar pré-visualizações
                setTimeout(() => {
                    document.querySelector('.ghost-btn')?.click();
                }, 2500);
            } catch (error) {
                console.error('Erro ao submeter animal', error);
                const errorMessage = error?.message || 'Erro ao submeter o animal. Verifique a sua ligação à internet e tente novamente.';
                setMessage(''); // Clear loading message
                if (typeof showNotification === 'function') {
                    showNotification(errorMessage, 'error');
                }
            }
        });
    })();
    
</script>

    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>
</body>
</html>


