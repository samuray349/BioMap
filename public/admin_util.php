<?php
require_once 'access_control.php';
checkAccess(ACCESS_ADMIN);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BioMap - Administração de Utilizadores</title>
    
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="js/config.js?v=<?php echo time(); ?>"></script>
    <script src="js/session.js?v=<?php echo time(); ?>"></script>
    <script src="js/api-toggle.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <!-- Header Placeholder -->
    <div id="header-placeholder"></div>
    
    <!-- Divider -->
    <div class="header-divider"></div>
    
    <!-- Main Content -->
    <main class="admin-users-content">
        <!-- Page Title -->
        <h1 class="admin-users-title">Administração de Utilizadores</h1>
        
        <div class="sidebar-content">
            <div class="search-section">
              <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-input" class="search-input" placeholder="Pesquisar">
              </div>
            </div>
    
            <div class="filters-row">
            
              <div class="filter-section">
                <label class="filter-label">Estado</label>
                <div class="tag-input-wrapper">
                  <input type="text" id="estado-input" class="tag-input" placeholder="Selecionar estado" autocomplete="off">
                  <div class="tag-container" id="estado-tags"></div>
                  <i class="fas fa-chevron-down filter-arrow"></i>
                  <div class="dropdown-menu" id="estado-dropdown"></div>
                </div>
              </div>

              <div class="filter-section">
                <label class="filter-label">Estatuto</label>
                <div class="tag-input-wrapper">
                  <input type="text" id="estatuto-input" class="tag-input" placeholder="Selecionar estatuto" autocomplete="off">
                  <div class="tag-container" id="estatuto-tags"></div>
                  <i class="fas fa-chevron-down filter-arrow"></i>
                  <div class="dropdown-menu" id="estatuto-dropdown"></div>
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
        
        <!-- Users Table -->
        <div class="admin-table-container">
            <table class="admin-users-table">
                <thead>
                    <tr>
                        <th>
                            Nome Utilizador
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th>
                            Email
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th>
                            Estado
                            
                        </th>
                        <th>
                            Estatuto
                            
                        </th>
                        <th>Suspender</th>
                        <th>Banir</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- User rows will be loaded dynamically from API -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="admin-pagination-container">
            <div class="pagination-info">
                <span>Linhas por pagina</span>
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
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script src="js/users.js?v=<?php echo time(); ?>"></script>
    <script>
        // Load header when page loads
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof loadHeader === 'function') {
                loadHeader();
            }
        });

        // Tag arrays specific to admin_util.php
        let adminEstadoTags = [];
        let adminEstatutoTags = [];

        // Options loaded from database
        let estadoOptions = [];
        let estatutoOptions = [];

        const tbody = document.querySelector('.admin-users-table tbody');
        const searchInput = document.getElementById('search-input');
        
        // Pagination state
        let currentPage = 1;
        let itemsPerPage = 10;
        let allUsers = [];
        let totalPages = 1;

        // Sorting state
        let sortStates = {
            'nome': 0, // 0: original, 1: asc, 2: desc
            'email': 0  // 0: original, 1: asc, 2: desc
        };
        let originalUsers = []; // Store original order

        // Pagination elements
        const paginationSelect = document.querySelector('.pagination-select');
        const paginationInfo = document.querySelector('.pagination-info span:last-child');
        const paginationControls = document.querySelector('.pagination-controls');

        // Fetch estado options from API
        async function fetchEstadoOptions() {
            try {
                const apiUrl = window.API_CONFIG?.getUrl('users/estados') || '/users/estados';
                const response = await fetch(apiUrl);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                estadoOptions = await response.json();
                return estadoOptions;
            } catch (error) {
                console.error("Erro ao buscar opções de estado:", error);
                return [];
            }
        }

        // Fetch estatuto options from API
        async function fetchEstatutoOptions() {
            try {
                const apiUrl = window.API_CONFIG?.getUrl('users/estatutos') || '/users/estatutos';
                const response = await fetch(apiUrl);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                estatutoOptions = await response.json();
                return estatutoOptions;
            } catch (error) {
                console.error("Erro ao buscar opções de estatuto:", error);
                return [];
            }
        }

        // Load and render users
        async function loadUsers() {
            try {
                const filters = getUserFilters({
                    searchInput: searchInput,
                    estadoTagsArray: adminEstadoTags,
                    estatutoTagsArray: adminEstatutoTags
                });
                
                allUsers = await fetchUsers(filters);

                // Get current user ID and filter it out
                let currentUserId = null;
                if (typeof SessionHelper !== 'undefined' && SessionHelper.getCurrentUser) {
                    const currentUser = SessionHelper.getCurrentUser();
                    currentUserId = currentUser ? currentUser.id : null;
                } else {
                    // Fallback to localStorage if SessionHelper not available
                    try {
                        const storedUser = localStorage.getItem('biomapUser');
                        if (storedUser) {
                            const currentUser = JSON.parse(storedUser);
                            currentUserId = currentUser ? currentUser.id : null;
                        }
                    } catch (e) {
                        console.warn('Could not get current user ID:', e);
                    }
                }

                // Filter out current user from the list
                allUsers = allUsers.filter(user => {
                    return currentUserId === null || user.utilizador_id !== currentUserId;
                });

                // Store original order for reverting sort
                originalUsers = [...allUsers];

                updatePagination();
                renderCurrentPage();
            } catch (error) {
                console.error("Erro ao carregar utilizadores:", error);
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6">Erro ao carregar dados.</td></tr>';
                }
            }
        }

        // Render current page of users
        function renderCurrentPage() {
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageUsers = allUsers.slice(startIndex, endIndex);

            renderUserTable(pageUsers, tbody);
        }

        // Sort users by column
        function sortUsers(column) {
            sortStates[column]++;
            if (sortStates[column] > 2) {
                sortStates[column] = 0;
            }

            if (sortStates[column] === 0) {
                // Revert to original order
                allUsers = [...originalUsers];
            } else {
                // Sort alphabetically
                allUsers.sort((a, b) => {
                    let valueA, valueB;
                    if (column === 'nome') {
                        valueA = a.nome_utilizador.toLowerCase();
                        valueB = b.nome_utilizador.toLowerCase();
                    } else if (column === 'email') {
                        valueA = a.email.toLowerCase();
                        valueB = b.email.toLowerCase();
                    }

                    if (sortStates[column] === 1) {
                        // Ascending (A-Z)
                        return valueA.localeCompare(valueB);
                    } else {
                        // Descending (Z-A)
                        return valueB.localeCompare(valueA);
                    }
                });
            }

            // Update sort icons
            updateSortIcons();

            // Reset to first page and render
            currentPage = 1;
            updatePagination();
            renderCurrentPage();
        }

        // Update sort icons based on current sort state
        function updateSortIcons() {
            const nomeIcon = document.querySelector('.admin-users-table thead th:nth-child(1) .sort-icon');
            const emailIcon = document.querySelector('.admin-users-table thead th:nth-child(2) .sort-icon');

            // Reset all icons to default
            if (nomeIcon) nomeIcon.className = 'fas fa-sort sort-icon';
            if (emailIcon) emailIcon.className = 'fas fa-sort sort-icon';

            // Update nome icon
            if (sortStates.nome === 1) {
                if (nomeIcon) nomeIcon.className = 'fas fa-sort-up sort-icon';
            } else if (sortStates.nome === 2) {
                if (nomeIcon) nomeIcon.className = 'fas fa-sort-down sort-icon';
            }

            // Update email icon
            if (sortStates.email === 1) {
                if (emailIcon) emailIcon.className = 'fas fa-sort-up sort-icon';
            } else if (sortStates.email === 2) {
                if (emailIcon) emailIcon.className = 'fas fa-sort-down sort-icon';
            }
        }

        // Update pagination UI
        function updatePagination() {
            totalPages = Math.max(1, Math.ceil(allUsers.length / itemsPerPage));
            const totalItems = allUsers.length;

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

        async function initAdminUserFilters() {
            // Load filter options from API first
            await Promise.all([
                fetchEstadoOptions(),
                fetchEstatutoOptions()
            ]);

            // Initialize filters with loaded options
            initUserFilters({
                estadoInputId: "estado-input",
                estadoTagsId: "estado-tags",
                estadoDropdownId: "estado-dropdown",
                estatutoInputId: "estatuto-input",
                estatutoTagsId: "estatuto-tags",
                estatutoDropdownId: "estatuto-dropdown",
                estadoOptions: estadoOptions,
                estatutoOptions: estatutoOptions,
                estadoTagsArray: adminEstadoTags,
                estatutoTagsArray: adminEstatutoTags,
                onFilterChange: () => {
                    currentPage = 1; // Reset to first page on filter change
                    // Reset sort states on filter change
                    sortStates = { 'nome': 0, 'email': 0 };
                    updateSortIcons();
                    loadUsers();
                }
            });

            // Search input triggers load
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    currentPage = 1; // Reset to first page on search
                    // Reset sort states on search
                    sortStates = { 'nome': 0, 'email': 0 };
                    updateSortIcons();
                    loadUsers();
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
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', () => {
                    clearUserFilters({
                        searchInput: searchInput,
                        estadoTagsArray: adminEstadoTags,
                        estatutoTagsArray: adminEstatutoTags,
                        estadoTagsId: 'estado-tags',
                        estatutoTagsId: 'estatuto-tags',
                        estadoInputId: 'estado-input',
                        estatutoInputId: 'estatuto-input'
                    });
                    currentPage = 1;
                    // Reset sort states on clear filters
                    sortStates = { 'nome': 0, 'email': 0 };
                    updateSortIcons();
                    loadUsers();
                });
            }

            // Sort functionality
            const nomeTh = document.querySelector('.admin-users-table thead th:nth-child(1)');
            const emailTh = document.querySelector('.admin-users-table thead th:nth-child(2)');

            if (nomeTh) {
                nomeTh.style.cursor = 'pointer';
                nomeTh.addEventListener('click', () => sortUsers('nome'));
            }
            if (emailTh) {
                emailTh.style.cursor = 'pointer';
                emailTh.addEventListener('click', () => sortUsers('email'));
            }

            // Initial load
            loadUsers();
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAdminUserFilters);
        } else {
            initAdminUserFilters();
        }
    </script>
</body>
</html>


