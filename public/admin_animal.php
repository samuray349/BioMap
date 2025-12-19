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
    <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
    <script src="js/config.js?v=<?php echo time(); ?>"></script>
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
    
    <!-- Update Animal Modal -->
    <div id="update-animal-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 10000;">
        <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto; background: #ffffffe6; border-radius: 16px; padding: 32px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); width: 90%; position: relative; margin: 20px;">
            <div class="modal-header">
                <h2>Atualizar Animal</h2>
                <button class="modal-close" id="close-update-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="update-animal-form" class="update-animal-form">
                <input type="hidden" id="update-animal-id" name="animal_id">
                
                <div class="form-grid two-columns">
                    <div class="input-field">
                        <label for="update-nome-comum" style="color: var(--accent-color);">Nome Animal</label>
                        <input type="text" id="update-nome-comum" name="nome_comum" required>
                    </div>
                    <div class="input-field">
                        <label for="update-nome-cientifico" style="color: var(--accent-color);">Nome Científico</label>
                        <input type="text" id="update-nome-cientifico" name="nome_cientifico" required>
                    </div>
                </div>

                <div class="input-field">
                    <label for="update-descricao" style="color: var(--accent-color);">Descrição</label>
                    <textarea id="update-descricao" name="descricao" rows="4" required></textarea>
                </div>

                <div class="input-field">
                    <label for="update-facto" style="color: var(--accent-color);">Facto Interessante</label>
                    <input type="text" id="update-facto" name="facto_interessante">
                </div>

                <div class="input-field">
                    <label for="update-populacao" style="color: var(--accent-color);">População Estimada</label>
                    <input type="text" id="update-populacao" name="populacao_estimada" placeholder="Ex: 1 200 indivíduos">
                </div>

                <div class="input-field">
                    <label for="update-family-input" style="color: var(--accent-color);">Família</label>
                    <div class="family-select-wrapper" style="position: relative;">
                        <input type="text" id="update-family-input" class="chip-select family-search-input" placeholder="Pesquisar e selecionar família" autocomplete="off">
                        <div class="dropdown-menu" id="update-family-dropdown"></div>
                    </div>
                </div>

                <div class="input-field">
                    <label for="update-dieta" style="color: var(--accent-color);">Dieta</label>
                    <select id="update-dieta" name="dieta_nome" class="chip-select" required>
                        <option value="">Selecione a dieta</option>
                        <option value="Carnívoro">Carnívoro</option>
                        <option value="Herbívoro">Herbívoro</option>
                        <option value="Omnívoro">Omnívoro</option>
                    </select>
                </div>

                <div class="input-field">
                    <label for="update-estado" style="color: var(--accent-color);">Estado de Conservação</label>
                    <select id="update-estado" name="estado_nome" class="chip-select" required>
                        <option value="">Selecione o estado</option>
                        <option value="Pouco Preocupante">Pouco Preocupante</option>
                        <option value="Quase Ameaçada">Quase Ameaçada</option>
                        <option value="Vulnerável">Vulnerável</option>
                        <option value="Em Perigo">Em Perigo</option>
                        <option value="Perigo Crítico">Perigo Crítico</option>
                        <option value="Extinto na Natureza">Extinto na Natureza</option>
                        <option value="Extinto">Extinto</option>
                        <option value="Dados Insuficientes">Dados Insuficientes</option>
                        <option value="Não Avaliada">Não Avaliada</option>
                    </select>
                </div>

                <div class="form-grid two-columns">
                    <div class="input-field">
                        <label for="update-threat-1" style="color: var(--accent-color);">Ameaça 1</label>
                        <input type="text" id="update-threat-1" name="ameaca_1" placeholder="Ex: Desflorestação, caça ilegal...">
                    </div>
                    <div class="input-field">
                        <label for="update-threat-2" style="color: var(--accent-color);">Ameaça 2</label>
                        <input type="text" id="update-threat-2" name="ameaca_2">
                    </div>
                    <div class="input-field">
                        <label for="update-threat-3" style="color: var(--accent-color);">Ameaça 3</label>
                        <input type="text" id="update-threat-3" name="ameaca_3">
                    </div>
                    <div class="input-field">
                        <label for="update-threat-4" style="color: var(--accent-color);">Ameaça 4</label>
                        <input type="text" id="update-threat-4" name="ameaca_4">
                    </div>
                    <div class="input-field">
                        <label for="update-threat-5" style="color: var(--accent-color);">Ameaça 5</label>
                        <input type="text" id="update-threat-5" name="ameaca_5">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="ghost-btn" id="cancel-update-btn">Cancelar</button>
                    <button type="submit" class="btn-primary">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script src="js/animals.js?v=<?php echo time(); ?>"></script>
    <script>
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
                const errorMessage = `Erro ao carregar animais: ${error?.message || 'Não foi possível carregar a lista de animais. Verifique a sua ligação à internet.'}`;
                if (typeof showNotification === 'function') {
                    showNotification(errorMessage, 'error');
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
            // Add update button handlers
            attachUpdateHandlers();
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
                    let errorDetails = '';
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData?.error || errorMessage;
                        errorDetails = errorData?.details ? ` Detalhes: ${errorData.details}` : '';
                    } catch (e) {
                        // If response is not JSON, use status text
                        errorMessage = response.statusText || errorMessage;
                    }
                    throw new Error(`Erro ao eliminar animal (ID: ${animalId}): ${errorMessage}${errorDetails}`);
                }
                
                // Parse successful response
                const result = await response.json();
                
                // Reload animals
                await loadAnimals();
                
                // Show success notification
                if (typeof showNotification === 'function') {
                    showNotification('Animal eliminado com sucesso!', 'success');
                } else {
                    alert('Animal eliminado com sucesso!');
                }
            } catch (error) {
                console.error('Erro ao deletar animal:', error);
                const errorMessage = error?.message || `Erro ao eliminar animal (ID: ${animalId}). Verifique a sua ligação à internet.`;
                if (typeof showNotification === 'function') {
                    showNotification(errorMessage, 'error');
                } else {
                    alert(errorMessage);
                }
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

        function attachUpdateHandlers() {
            const updateIcons = document.querySelectorAll('.update-icon[data-animal-id]');
            updateIcons.forEach(icon => {
                icon.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const animalId = icon.getAttribute('data-animal-id');
                    if (animalId) {
                        openUpdateModal(parseInt(animalId));
                    }
                });
            });
        }

        async function openUpdateModal(animalId) {
            const modal = document.getElementById('update-animal-modal');
            if (!modal) return;

            try {
                // Fetch animal data and conservation statuses
                const [animalResponse, estadosResponse] = await Promise.all([
                    fetch(getApiUrl(`animaisDesc/${animalId}`)),
                    fetch(getApiUrl('animais/estados'))
                ]);
                
                if (!animalResponse.ok) throw new Error('Erro ao carregar dados do animal');
                
                const animal = await animalResponse.json();
                const estados = estadosResponse.ok ? await estadosResponse.json() : [];
                
                // Populate conservation status dropdown from API
                const estadoSelect = document.getElementById('update-estado');
                if (estadoSelect && estados.length > 0) {
                    estadoSelect.innerHTML = '<option value="">Selecione o estado</option>';
                    estados.forEach(estado => {
                        const option = document.createElement('option');
                        option.value = estado.nome_estado.trim();
                        option.textContent = estado.nome_estado.trim();
                        estadoSelect.appendChild(option);
                    });
                }
                
                // Populate form
                document.getElementById('update-animal-id').value = animal.animal_id;
                document.getElementById('update-nome-comum').value = animal.nome_comum || '';
                document.getElementById('update-nome-cientifico').value = animal.nome_cientifico || '';
                document.getElementById('update-descricao').value = animal.descricao || '';
                document.getElementById('update-facto').value = animal.facto_interessante || '';
                document.getElementById('update-populacao').value = animal.populacao_estimada || '';
                document.getElementById('update-dieta').value = animal.nome_dieta || '';
                
                // Set conservation status - trim to handle any trailing spaces
                const estadoValue = (animal.nome_estado || '').trim();
                if (estadoSelect && estadoValue) {
                    // Try to find matching option (case-insensitive, handle spaces)
                    const options = Array.from(estadoSelect.options);
                    const matchingOption = options.find(opt => 
                        opt.value.trim().toLowerCase() === estadoValue.toLowerCase()
                    );
                    if (matchingOption) {
                        estadoSelect.value = matchingOption.value;
                    } else {
                        // If no exact match, try to set it anyway
                        estadoSelect.value = estadoValue;
                    }
                }
                
                // Handle ameacas (threats) - populate 5 separate inputs
                // The array comes from API in correct order (as shown in animal_desc.php)
                // but appears backwards in the form, so we reverse it when populating
                const ameacasArray = animal.ameacas && Array.isArray(animal.ameacas) 
                    ? [...animal.ameacas].reverse()
                    : [];
                
                for (let i = 1; i <= 5; i++) {
                    const threatInput = document.getElementById(`update-threat-${i}`);
                    if (threatInput) {
                        threatInput.value = ameacasArray[i - 1] || '';
                    }
                }
                
                // Set family input value
                const familyInput = document.getElementById('update-family-input');
                if (familyInput && animal.nome_familia) {
                    familyInput.value = animal.nome_familia;
                }
                
                // Initialize family dropdown if not already initialized
                initUpdateFamilyDropdown();
                
                // Reset scroll position to top
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.scrollTop = 0;
                }
                
                // Show modal
                modal.style.display = 'flex';
                // Prevent body scroll when modal is open
                document.body.style.overflow = 'hidden';
            } catch (error) {
                console.error('Erro ao abrir modal de atualização:', error);
                const errorMessage = `Erro ao carregar dados do animal (ID: ${animalId}): ${error?.message || 'Não foi possível carregar os dados. Verifique a sua ligação à internet.'}`;
                if (typeof showNotification === 'function') {
                    showNotification(errorMessage, 'error');
                } else {
                    alert(errorMessage);
                }
            }
        }

        function closeUpdateModal() {
            const modal = document.getElementById('update-animal-modal');
            if (modal) {
                modal.style.display = 'none';
                document.getElementById('update-animal-form').reset();
                // Restore body scroll
                document.body.style.overflow = '';
            }
        }

        // Initialize family dropdown for update modal
        let updateFamilyDropdownInitialized = false;
        async function initUpdateFamilyDropdown() {
            if (updateFamilyDropdownInitialized) return;
            
            const familyInput = document.getElementById('update-family-input');
            const familyDropdown = document.getElementById('update-family-dropdown');
            const wrapper = familyInput ? familyInput.closest('.family-select-wrapper') : null;
            
            if (!familyInput || !familyDropdown) return;
            
            try {
                let familyOptions = [];
                if (typeof fetchFamilyOptions === 'function') {
                    familyOptions = await fetchFamilyOptions();
                }
                
                function renderDropdown() {
                    const searchTerm = familyInput.value.toLowerCase().trim();
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
                            if (familyInput.value.trim() === option) {
                                item.style.fontWeight = '600';
                                item.style.backgroundColor = '#f0f0f0';
                            }
                            item.addEventListener('click', (e) => {
                                e.stopPropagation();
                                familyInput.value = option;
                                familyDropdown.classList.remove('show');
                            });
                            familyDropdown.appendChild(item);
                        });
                        familyDropdown.classList.add('show');
                    } else {
                        familyDropdown.classList.remove('show');
                    }
                }
                
                familyInput.addEventListener('focus', renderDropdown);
                familyInput.addEventListener('input', renderDropdown);
                
                if (wrapper) {
                    wrapper.addEventListener('click', (e) => {
                        if (e.target !== familyInput && !familyDropdown.contains(e.target)) {
                            familyInput.focus();
                            renderDropdown();
                        }
                    });
                }
                
                document.addEventListener('click', (e) => {
                    if (wrapper && !wrapper.contains(e.target)) {
                        familyDropdown.classList.remove('show');
                    }
                });
                
                updateFamilyDropdownInitialized = true;
            } catch (error) {
                console.error('Erro ao inicializar dropdown de família:', error);
                if (typeof showNotification === 'function') {
                    showNotification(`Erro ao inicializar o campo Família no formulário: ${error?.message || 'Erro desconhecido'}`, 'error');
                }
            }
        }

        // Handle update form submission
        async function handleUpdateAnimal(e) {
            e.preventDefault();
            
            const form = e.target;
            const animalId = document.getElementById('update-animal-id').value;
            const formData = new FormData(form);
            
            // Get form values
            const updateData = {
                nome_comum: document.getElementById('update-nome-comum').value.trim(),
                nome_cientifico: document.getElementById('update-nome-cientifico').value.trim(),
                descricao: document.getElementById('update-descricao').value.trim(),
                facto_interessante: document.getElementById('update-facto').value.trim(),
                populacao_estimada: document.getElementById('update-populacao').value.trim(),
                familia_nome: document.getElementById('update-family-input').value.trim(),
                dieta_nome: document.getElementById('update-dieta').value.trim(),
                estado_nome: document.getElementById('update-estado').value.trim(),
                ameacas: (() => {
                    const threats = [];
                    for (let i = 1; i <= 5; i++) {
                        const threatInput = document.getElementById(`update-threat-${i}`);
                        if (threatInput && threatInput.value.trim()) {
                            threats.push(threatInput.value.trim());
                        }
                    }
                    // Reverse the array to match the order expected by the API
                    // (since we reversed when populating, we need to reverse when reading)
                    return threats.reverse();
                })()
            };
            
            // Validate required fields
            const missingFields = [];
            if (!updateData.nome_comum) missingFields.push('Nome Animal');
            if (!updateData.nome_cientifico) missingFields.push('Nome Científico');
            if (!updateData.descricao) missingFields.push('Descrição');
            if (!updateData.familia_nome) missingFields.push('Família');
            if (!updateData.dieta_nome) missingFields.push('Dieta');
            if (!updateData.estado_nome) missingFields.push('Estado de Conservação');
            
            if (missingFields.length > 0) {
                const validationMessage = `Campos obrigatórios em falta no formulário de atualização: ${missingFields.join(', ')}.`;
                if (typeof showNotification === 'function') {
                    showNotification(validationMessage, 'error');
                } else {
                    alert(validationMessage);
                }
                return;
            }
            
            // Validate ameacas (threats) - must be exactly 5 non-empty threats
            const nonEmptyThreats = updateData.ameacas.filter(t => t && t.trim().length > 0);
            const threatsCount = nonEmptyThreats.length;
            if (threatsCount !== 5) {
                const errorMessage = `Erro no formulário de atualização: Deve preencher exatamente 5 ameaças. Atualmente tem ${threatsCount} ameaça${threatsCount !== 1 ? 's' : ''} preenchida${threatsCount !== 1 ? 's' : ''}.`;
                if (typeof showNotification === 'function') {
                    showNotification(errorMessage, 'error');
                } else {
                    alert(errorMessage);
                }
                return;
            }
            
            try {
                const apiUrl = getApiUrl(`animais/${animalId}`);
                
                const response = await fetch(apiUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });
                
                // Try to parse JSON, but handle non-JSON responses
                let result;
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    const text = await response.text();
                    throw new Error(`Erro ao atualizar animal (ID: ${animalId}): O servidor retornou uma resposta inválida. Status: ${response.status}`);
                }
                
                if (!response.ok) {
                    const errorMsg = result.error || 'Erro ao atualizar animal';
                    const details = result.details ? ` Detalhes: ${result.details}` : '';
                    throw new Error(`Erro ao atualizar animal (ID: ${animalId}): ${errorMsg}${details}`);
                }
                
                // Success - close modal immediately and show notification
                closeUpdateModal();
                
                // Show success notification immediately
                if (typeof showNotification === 'function') {
                    showNotification('Animal atualizado com sucesso!', 'success');
                } else {
                    alert('Animal atualizado com sucesso!');
                }
                
                // Reload animals in background (don't wait for it)
                loadAnimals().catch(err => {
                    console.error('Error reloading animals:', err);
                    if (typeof showNotification === 'function') {
                        showNotification('Animal atualizado, mas erro ao atualizar a lista: ' + (err?.message || 'Erro desconhecido'), 'error');
                    }
                });
            } catch (error) {
                console.error('Erro ao atualizar animal:', error);
                const animalId = document.getElementById('update-animal-id')?.value || 'desconhecido';
                const errorMessage = error.message || `Erro ao atualizar animal (ID: ${animalId}). Verifique a sua ligação à internet.`;
                if (typeof showNotification === 'function') {
                    showNotification(errorMessage, 'error');
                } else {
                    alert(errorMessage);
                }
            }
        }

        async function initAdminAnimalFilters() {
            // Load header
            if (typeof loadHeader === 'function') {
                loadHeader();
            }
            
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

        // Modal event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('update-animal-modal');
            const closeBtn = document.getElementById('close-update-modal');
            const cancelBtn = document.getElementById('cancel-update-btn');
            const updateForm = document.getElementById('update-animal-form');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', closeUpdateModal);
            }
            
            if (cancelBtn) {
                cancelBtn.addEventListener('click', closeUpdateModal);
            }
            
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeUpdateModal();
                    }
                });
            }
            
            if (updateForm) {
                updateForm.addEventListener('submit', handleUpdateAnimal);
            }
        });

        // Wait for all external scripts to load before initializing
        function waitForScriptsAndInit() {
            if (typeof fetchFamilyOptions !== 'function' || 
                typeof fetchStateOptions !== 'function' || 
                typeof getAnimalFilters !== 'function' ||
                typeof fetchAnimals !== 'function' ||
                typeof renderAnimalCards !== 'function' ||
                typeof initAnimalFilters !== 'function' ||
                typeof clearAnimalFilters !== 'function' ||
                typeof loadHeader !== 'function') {
                setTimeout(waitForScriptsAndInit, 100);
                return;
            }
            initAdminAnimalFilters();
        }
        
        // Initialize when window fully loads (all scripts included)
        window.addEventListener('load', waitForScriptsAndInit);
    </script>

    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>
</body>
</html>


