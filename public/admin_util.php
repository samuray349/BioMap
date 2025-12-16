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
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="js/config.js"></script>
    <script src="js/session.js"></script>
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
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th>
                            Estatuto
                            <i class="fas fa-sort sort-icon"></i>
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
    <script src="js/script.js"></script>
    <script src="js/users.js?v=2"></script>
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

        // Load and render users (excluding current user)
        async function loadUsers() {
            try {
                const filters = getUserFilters({
                    searchInput: searchInput,
                    estadoTagsArray: adminEstadoTags,
                    estatutoTagsArray: adminEstatutoTags
                });
                
                const users = await fetchUsers(filters);
                
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
                const filteredUsers = users.filter(user => {
                    return currentUserId === null || user.utilizador_id !== currentUserId;
                });
                
                renderUserTable(filteredUsers, tbody);
            } catch (error) {
                console.error("Erro ao carregar utilizadores:", error);
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6">Erro ao carregar dados.</td></tr>';
                }
            }
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
                onFilterChange: loadUsers
            });

            // Search input triggers load
            if (searchInput) {
                searchInput.addEventListener('input', loadUsers);
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
                    loadUsers();
                });
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


