<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Animais</title>
    <link rel="stylesheet" href="css/styles.css">
  
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
  <script src="js/config.js?v=<?php echo time(); ?>"></script>
  <script src="js/api-toggle.js"></script>
<style>
    /* ============================================================= */
    /*  ESTILOS ESPECÍFICOS PARA A PÁGINA DE LISTAGEM DE ANIMAIS     */
    /* ============================================================= */

.main-content {
    padding: 30px;
    max-width:90%;
    margin: 0 auto;
}

/* Cabeçalho da secção de resultados */
.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.results-title {
    font-size: 2rem;
    font-weight: 600;
}

.sort-group {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    position: relative;
}

.sort-group label {
    color: var(--text-medium);
    font-weight: bolder;
}

.select-input {
    padding: 8px 30px 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: white;
    font-size: 1rem;
    appearance: none;
}

.sort-group::after {
    content: '\f078';
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--accent-color);
    pointer-events: none;
}

/* Grid de cartões */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(20%, 1fr));
    gap: 24px;
}

/* Cartão individual */
.card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}

.card:hover {
    transform: translateY(-6px);
}

.card-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.card-content {
    padding: 18px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.card-header h3 {
    font-size: 1.35rem;
    font-weight: 600;
    color: var(--accent-color);
    margin: 0;
    line-height: 1.2;
}

/* Badges de estado de conservação */
.badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
}



/* Família + ícone */
.family {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    font-size: 0.95rem;
    color: var(--text-medium);
}

.family-icon {
    color: var(--accent-color);
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-size: 14px;
}

/* Descrição */
.description {
    color: var(--text-medium);
    font-size: 0.94rem;
    line-height: 1.5;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
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

.btn-details span {
    font-size: 1.1rem;
}

.sidebar-content{
    padding: 0 0 40px 0  ;
    flex-direction: row;
    align-items: flex-start;
}

.filter-section {
    display: flex;
    flex-direction: column;
}

.search-section {
    display: flex;
    flex-direction: column;
}

.search-section .search-wrapper {
    margin-top: calc(1rem * 1.5 + 4px); /* Match label line-height + margin-bottom to align with tag-input-wrapper */
}

.tag-input-wrapper{
    padding: 0px 40px 0px 12px;
    min-height: 50px;
    display: flex;
    align-items: center;
}

.search-section .search-input {
    height: 50px;
}

.order-input{
    position: relative;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    border: 1px solid #198754;
    border-radius: 8px;
    padding: 8px 40px 8px 12px;
    background-color: #ffffff;
    cursor: text;
    padding: 0px 40px 0px 12px;
    height: 40px;
    font-size: 16px;
    color: #333;
    select{
        background: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNSIgaGVpZ2h0PSIxMCIgdmlld0JveD0iMCAwIDUgMTAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBvbHlnb24gcG9pbnRzPSIxLjQgNC42NyAyLjUgMy4xOCAzLjYgNC42NyAxLjQgNC42NyIgZmlsbD0iIzE5ODc1NCIvPjxwb2x5Z29uIHBvaW50cz0iMy42IDUuMzMgMi41IDYuODIgMS40IDUuMzMgMy42IDUuMzMiIGZpbGw9IiMxOTg3NTQiLz48L3N2Zz4=) no-repeat 100% 50%;
    -moz-appearance: none;
    -webkit-appearance: none;
    appearance: none;
}
}

.search-section{
    width: 62%;
}

.sort-group:after{
    content: none;
}

.filter-section {
    max-width: 30%;
}

.filters-row{
    flex-direction: row-reverse;
    gap: 40px;
}

.filter-label {
    font-size: 1rem;
    font-weight: bolder;
    color: var(--text-medium);
    margin-bottom: 4px;
    display: block;
}

/* Responsividade extra para telas pequenas */
@media (max-width: 768px) {
    .cards-grid {
        grid-template-columns: 1fr;
    }
    
    .results-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .main-content {
        padding: 20px;
    }
}
</style>
</head>
<body>
    <div id="page-loader" class="page-loader">
        <div class="spinner-border text-success" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    <div id="header-placeholder"></div>
    <!-- Main Content -->
    <main class="main-content">
        <!-- Search and Filters Section -->
        <div class="sidebar-content">
            <div class="search-section">
              <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-input" class="search-input" placeholder="Pesquisar">
              </div>
            </div>
    
            <div class="filters-row">
                <div class="filter-section" style="flex: 0 0 auto; max-width: 50px;">
                    <label class="filter-label" style="visibility: hidden;">Clear</label>
                    <button type="button" id="clear-filters-btn" class="clear-filters-btn" title="Limpar filtros">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                    </button>
                  </div>
            
              <div class="filter-section">
                <label class="filter-label">Estado de conservação</label>
                <div class="tag-input-wrapper">
                  <input type="text" id="state-input" class="tag-input" placeholder="Selecionar estado de conservação" autocomplete="off">
                  <div class="tag-container" id="state-tags"></div>
                  <i class="fas fa-chevron-down filter-arrow"></i>
                  <div class="dropdown-menu" id="state-dropdown"></div>
                </div>
              </div>

              <div class="filter-section">
                <label class="filter-label">Família</label>
                <div class="tag-input-wrapper">
                  <input type="text" id="family-input" class="tag-input" placeholder="Selecionar família" autocomplete="off">
                  <div class="tag-container" id="family-tags"></div>
                  <i class="fas fa-chevron-down filter-arrow"></i>
                  <div class="dropdown-menu" id="family-dropdown"></div>
                </div>
              </div>
              
              
            </div>
          </div>
        <!-- Results Header -->
        <div class="results-header">
            <h2 class="results-title text-primary">Animais encontrados</h2>
            <div class="sort-group">
                <label>Ordenar por:</label>
                  <select class="order-input">
                      <option>Mais visto</option>
                  </select>
            </div>
        </div>
        <!-- Animal Cards Grid -->
        <div class="cards-grid">
            
        </div>
    </main>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script src="js/animals.js?v=<?php echo time(); ?>"></script>
    <script>
        // Arrays para as tags de família e estado de conservação
        let animaisFamilyTags = [];
        let animaisStateTags = [];
      
        // Will be populated from API (using functions from script.js)
        let familyOptions = [];
        let stateOptions = [];
        
        // Initialize after DOM and scripts are loaded
        async function initAnimaisPage() {
            // Loader
            const loader = document.getElementById('page-loader');
            if (loader) {
                setTimeout(() => {
                    loader.classList.add('hidden');
                    loader.addEventListener('transitionend', () => loader.remove());
                }, 300);
            }
            
            // Load header
            if (typeof loadHeader === 'function') {
                loadHeader();
            }
            if (typeof highlightCurrentPage === 'function') {
                highlightCurrentPage();
            }
            
            const cardsGrid = document.querySelector('.cards-grid');
            const searchInput = document.getElementById('search-input');
            
            // Função principal para buscar e renderizar os animais
            async function loadAnimals() {
                try {
                    const filters = getAnimalFilters({
                        searchInput: searchInput,
                        familyTagsArray: animaisFamilyTags,
                        stateTagsArray: animaisStateTags
                    });
                    
                    const animals = await fetchAnimals(filters);
                    renderAnimalCards(animals, cardsGrid, {
                        emptyMessage: 'Nenhum animal encontrado.'
                    });
                } catch (error) {
                    console.error("Erro ao buscar animais:", error);
                    if (cardsGrid) cardsGrid.innerHTML = '<p>Erro ao carregar dados.</p>';
                }
            }
          
            // Fetch filter options from API before initializing
            const [families, states] = await Promise.all([
                fetchFamilyOptions(),
                fetchStateOptions()
            ]);
            familyOptions = families;
            stateOptions = states;
          
          // Inicializar o listener do input de pesquisa
          if (searchInput) {
              searchInput.addEventListener('input', () => {
                  loadAnimals();
              });
          }
          
      
          // Inicializar os filtros
          initAnimalFilters({
              familyInputId: "family-input",
              familyTagsId: "family-tags",
              familyDropdownId: "family-dropdown",
              stateInputId: "state-input",
              stateTagsId: "state-tags",
              stateDropdownId: "state-dropdown",
              familyOptions: familyOptions,
              stateOptions: stateOptions,
              familyTagsArray: animaisFamilyTags,
              stateTagsArray: animaisStateTags,
              onFilterChange: loadAnimals
          });
      
          // Clear filters button
          const clearFiltersBtn = document.getElementById('clear-filters-btn');
          if (clearFiltersBtn) {
              clearFiltersBtn.addEventListener('click', () => {
                  clearAnimalFilters({
                      searchInput: searchInput,
                      familyTagsArray: animaisFamilyTags,
                      stateTagsArray: animaisStateTags,
                      familyTagsId: 'family-tags',
                      stateTagsId: 'state-tags',
                      familyInputId: 'family-input',
                      stateInputId: 'state-input'
                  });
                  loadAnimals();
              });
          }
      
            // Carregar os animais
            loadAnimals();
        }
        
        // Check if functions are loaded, then initialize
        function waitForScriptsAndInit() {
            if (typeof fetchFamilyOptions !== 'function' || 
                typeof fetchStateOptions !== 'function' || 
                typeof getAnimalFilters !== 'function' ||
                typeof fetchAnimals !== 'function' ||
                typeof renderAnimalCards !== 'function' ||
                typeof initAnimalFilters !== 'function' ||
                typeof clearAnimalFilters !== 'function') {
                // Retry after a short delay
                setTimeout(waitForScriptsAndInit, 100);
                return;
            }
            // All functions loaded, initialize the page
            initAnimaisPage();
        }
        
        // Start checking after DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', waitForScriptsAndInit);
        } else {
            waitForScriptsAndInit();
        }
      </script>
</body>
</html>
