<?php
require_once 'access_control.php';
checkAccess(ACCESS_ADMIN);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Administração do registo animal</title>
    
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/config.js"></script>
</head>
<body>
    <!-- Header Placeholder -->
    <div id="header-placeholder"></div>
    
    <!-- Divider -->
    <div class="header-divider"></div>
    
    <!-- Main Content -->
    <main class="admin-animals-content">
        <!-- Page Title -->
        <h1 class="admin-animals-title">Administração do registo animal</h1>
        
        <!-- Search and Filters Section -->
        <div class="sidebar-content">
            <div class="search-section">
              <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-input" class="search-input" placeholder="Pesquisar">
              </div>
            </div>
    
            <div class="filters-row">
            
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
              
              <div class="filter-section" style="flex: 0 0 auto; max-width: 50px;">
                <label class="filter-label" style="visibility: hidden;">Clear</label>
                <button type="button" id="clear-filters-btn" class="clear-filters-btn" title="Limpar filtros">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                </button>
              </div>
            </div>
          </div>
        
        <!-- Animals Table -->
        <div class="admin-table-container">
            <table class="admin-animals-table">
                <thead>
                    <tr>
                        <th>
                            Nome Animal
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th>
                            Família
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th>
                            Estado de cons
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th>Atualizar</th>
                        <th>Excluir</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Animal rows will be loaded dynamically from API -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="admin-pagination-container">
            <div class="pagination-info">
                <span>Linhas por paginas</span>
                <select class="pagination-select">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
                <span>de 140 linhas</span>
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn" title="Primeira página">
                    <i class="fas fa-angle-double-left"></i>
                </button>
                <button class="pagination-btn" title="Página anterior">
                    <i class="fas fa-angle-left"></i>
                </button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <span class="pagination-ellipsis">...</span>
                <button class="pagination-btn">10</button>
                <button class="pagination-btn" title="Próxima página">
                    <i class="fas fa-angle-right"></i>
                </button>
                <button class="pagination-btn" title="Última página">
                    <i class="fas fa-angle-double-right"></i>
                </button>
            </div>
        </div>
    </main>
    
    <!-- Scripts -->
    <script src="js/script.js"></script>
    <script src="js/animals.js"></script>
    <script>
        // Load header when page loads
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof loadHeader === 'function') {
                loadHeader();
            }
        });

        // Tag arrays specific to admin_animal.php
        let adminFamilyTags = [];
        let adminStateTags = [];

        // Will be populated from API (using functions from script.js)
        let familyOptions = [];
        let stateOptions = [];

        const tbody = document.querySelector('.admin-animals-table tbody');
        const searchInput = document.getElementById('search-input');
        
        // Pagination state
        let currentPage = 1;
        let itemsPerPage = 10;
        let allAnimals = [];
        let totalPages = 1;

        // Pagination elements
        const paginationSelect = document.querySelector('.pagination-select');
        const paginationInfo = document.querySelector('.pagination-info span:last-child');
        const paginationControls = document.querySelector('.pagination-controls');

        // Load and render animals
        async function loadAnimals() {
            try {
                const filters = getAnimalFilters({
                    searchInput: searchInput,
                    familyTagsArray: adminFamilyTags,
                    stateTagsArray: adminStateTags
                });
                
                allAnimals = await fetchAnimals(filters);
                updatePagination();
                renderCurrentPage();
            } catch (error) {
                console.error("Erro ao carregar animais:", error);
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="5">Erro ao carregar dados.</td></tr>';
                }
            }
        }

        // Render current page of animals
        function renderCurrentPage() {
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageAnimals = allAnimals.slice(startIndex, endIndex);
            
            renderAnimalTable(pageAnimals, tbody);
            
            // Add delete button handlers
            attachDeleteHandlers();
        }

        // Update pagination UI
        function updatePagination() {
            totalPages = Math.max(1, Math.ceil(allAnimals.length / itemsPerPage));
            const totalItems = allAnimals.length;
            
            // Update info text
            if (paginationInfo) {
                paginationInfo.textContent = `de ${totalItems} linhas`;
            }
            
            // Update pagination buttons
            renderPaginationButtons();
        }

        // Render pagination buttons
        function renderPaginationButtons() {
            if (!paginationControls) return;
            
            paginationControls.innerHTML = '';
            
            // First page button
            const firstBtn = createPaginationButton('first', '<i class="fas fa-angle-double-left"></i>', 'Primeira página');
            firstBtn.disabled = currentPage === 1;
            paginationControls.appendChild(firstBtn);
            
            // Previous page button
            const prevBtn = createPaginationButton('prev', '<i class="fas fa-angle-left"></i>', 'Página anterior');
            prevBtn.disabled = currentPage === 1;
            paginationControls.appendChild(prevBtn);
            
            // Page number buttons
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            if (startPage > 1) {
                const firstPageBtn = createPaginationButton(1, '1');
                paginationControls.appendChild(firstPageBtn);
                
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    paginationControls.appendChild(ellipsis);
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = createPaginationButton(i, i.toString());
                if (i === currentPage) {
                    pageBtn.classList.add('active');
                }
                paginationControls.appendChild(pageBtn);
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    paginationControls.appendChild(ellipsis);
                }
                
                const lastPageBtn = createPaginationButton(totalPages, totalPages.toString());
                paginationControls.appendChild(lastPageBtn);
            }
            
            // Next page button
            const nextBtn = createPaginationButton('next', '<i class="fas fa-angle-right"></i>', 'Próxima página');
            nextBtn.disabled = currentPage === totalPages;
            paginationControls.appendChild(nextBtn);
            
            // Last page button
            const lastBtn = createPaginationButton('last', '<i class="fas fa-angle-double-right"></i>', 'Última página');
            lastBtn.disabled = currentPage === totalPages;
            paginationControls.appendChild(lastBtn);
        }

        // Create pagination button
        function createPaginationButton(value, content, title = '') {
            const btn = document.createElement('button');
            btn.className = 'pagination-btn';
            btn.innerHTML = content;
            if (title) btn.title = title;
            
            btn.addEventListener('click', () => {
                if (value === 'first') {
                    currentPage = 1;
                } else if (value === 'prev') {
                    currentPage = Math.max(1, currentPage - 1);
                } else if (value === 'next') {
                    currentPage = Math.min(totalPages, currentPage + 1);
                } else if (value === 'last') {
                    currentPage = totalPages;
                } else {
                    currentPage = value;
                }
                renderCurrentPage();
                renderPaginationButtons();
            });
            
            return btn;
        }

        // Delete animal handler
        async function deleteAnimal(animalId) {
            if (!confirm('Tem certeza que deseja excluir este animal? Esta ação não pode ser desfeita.')) {
                return;
            }
            
            try {
                const apiUrl = window.API_CONFIG?.getUrl(`animais/${animalId}`) || `/animais/${animalId}`;
                const response = await fetch(apiUrl, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                // Check if response is ok before parsing JSON
                if (!response.ok) {
                    // Try to parse error message, but handle non-JSON responses
                    let errorMessage = 'Erro ao deletar animal.';
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData?.error || errorMessage;
                    } catch (e) {
                        // If response is not JSON, use status text
                        errorMessage = response.statusText || errorMessage;
                    }
                    throw new Error(errorMessage);
                }
                
                // Parse successful response
                const result = await response.json();
                
                // Reload animals
                await loadAnimals();
            } catch (error) {
                console.error('Erro ao deletar animal:', error);
                alert(error?.message || 'Erro ao deletar animal.');
            }
        }

        // Attach delete handlers to ban icons
        function attachDeleteHandlers() {
            const deleteIcons = document.querySelectorAll('.ban-icon[data-animal-id]');
            deleteIcons.forEach(icon => {
                icon.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const animalId = icon.getAttribute('data-animal-id');
                    if (animalId) {
                        deleteAnimal(parseInt(animalId));
                    }
                });
            });
        }

        async function initAdminAnimalFilters() {
            // Fetch filter options from API before initializing
            const [families, states] = await Promise.all([
                fetchFamilyOptions(),
                fetchStateOptions()
            ]);
            familyOptions = families;
            stateOptions = states;

            // Initialize filters
            initAnimalFilters({
                familyInputId: "family-input",
                familyTagsId: "family-tags",
                familyDropdownId: "family-dropdown",
                stateInputId: "state-input",
                stateTagsId: "state-tags",
                stateDropdownId: "state-dropdown",
                familyOptions: familyOptions,
                stateOptions: stateOptions,
                familyTagsArray: adminFamilyTags,
                stateTagsArray: adminStateTags,
                onFilterChange: () => {
                    currentPage = 1; // Reset to first page on filter change
                    loadAnimals();
                }
            });

            // Search input triggers load
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    currentPage = 1; // Reset to first page on search
                    loadAnimals();
                });
            }

            // Items per page change
            if (paginationSelect) {
                paginationSelect.addEventListener('change', (e) => {
                    itemsPerPage = parseInt(e.target.value);
                    currentPage = 1;
                    updatePagination();
                    renderCurrentPage();
                });
            }

            // Clear filters button
            const clearFiltersBtn = document.getElementById('clear-filters-btn');
            if (clearFiltersBtn && typeof clearAnimalFilters === 'function') {
                clearFiltersBtn.addEventListener('click', () => {
                    clearAnimalFilters({
                        searchInput: searchInput,
                        familyTagsArray: adminFamilyTags,
                        stateTagsArray: adminStateTags,
                        familyTagsId: 'family-tags',
                        stateTagsId: 'state-tags',
                        familyInputId: 'family-input',
                        stateInputId: 'state-input'
                    });
                    currentPage = 1;
                    loadAnimals();
                });
            }

            // Initial load
            loadAnimals();
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAdminAnimalFilters);
        } else {
            initAdminAnimalFilters();
        }
    </script>
</body>
</html>


