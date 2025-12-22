
/**
 * Obtém utilizadores da API com filtros opcionais
 * @param {Object} filters - Opções de filtro
 * @param {string} filters.search - Consulta de pesquisa de texto
 * @param {Array<string>} filters.estados - Array de nomes de estados para filtrar
 * @param {Array<string>} filters.estatutos - Array de nomes de estatutos (funções) para filtrar
 * @returns {Promise<Array>} Array de objetos de utilizadores
 */

// Create a simple modal confirm (Sim / Não)
function showConfirmBan(onConfirm, onCancel) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = '10000';

    const box = document.createElement('div');
    box.style.background = '#fff';
    box.style.padding = '20px';
    box.style.borderRadius = '8px';
    box.style.maxWidth = '420px';
    box.style.width = '90%';
    box.style.boxShadow = '0 8px 24px rgba(0,0,0,0.2)';

    box.innerHTML = `
        <h3 style="margin-top:0">Confirmar banimento</h3>
        <p>Tem certeza que deseja banir este utilizador? <strong>ATENÇÃO: Todos os avistamentos deste utilizador serão deletados permanentemente!</strong></p>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px">
            <button id="confirmNo" style="padding:8px 14px;border:1px solid #ccc;background:#fff;border-radius:6px;">Não</button>
            <button id="confirmYes" style="padding:8px 14px;background:#e05353;color:#fff;border:none;border-radius:6px;">Sim</button>
        </div>
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    const yesBtn = box.querySelector('#confirmYes');
    const noBtn = box.querySelector('#confirmNo');

    yesBtn.addEventListener('click', async () => {
        try {
            await onConfirm();
        } catch (error) {
            console.error('Error in onConfirm:', error);
        }
        document.body.removeChild(overlay);
    });

    noBtn.addEventListener('click', () => {
        if (onCancel) onCancel();
        document.body.removeChild(overlay);
    });
}

// Create a simple modal confirm for suspend (Sim / Não)
function showConfirmSuspend(onConfirm, onCancel) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = '10000';

    const box = document.createElement('div');
    box.style.background = '#fff';
    box.style.padding = '20px';
    box.style.borderRadius = '8px';
    box.style.maxWidth = '420px';
    box.style.width = '90%';
    box.style.boxShadow = '0 8px 24px rgba(0,0,0,0.2)';

    box.innerHTML = `
        <h3 style="margin-top:0">Confirmar suspensão</h3>
        <p>Tem certeza que deseja suspender este utilizador?</p>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px">
            <button id="confirmNo" style="padding:8px 14px;border:1px solid #ccc;background:#fff;border-radius:6px;">Não</button>
            <button id="confirmYes" style="padding:8px 14px;background:#e05353;color:#fff;border:none;border-radius:6px;">Sim</button>
        </div>
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    const yesBtn = box.querySelector('#confirmYes');
    const noBtn = box.querySelector('#confirmNo');

    yesBtn.addEventListener('click', async () => {
        try {
            await onConfirm();
        } catch (error) {
            console.error('Error in onConfirm:', error);
        }
        document.body.removeChild(overlay);
    });

    noBtn.addEventListener('click', () => {
        if (onCancel) onCancel();
        document.body.removeChild(overlay);
    });
}

// Create a simple modal confirm for unban (Sim / Não)
function showConfirmUnban(onConfirm, onCancel) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = '10000';

    const box = document.createElement('div');
    box.style.background = '#fff';
    box.style.padding = '20px';
    box.style.borderRadius = '8px';
    box.style.maxWidth = '420px';
    box.style.width = '90%';
    box.style.boxShadow = '0 8px 24px rgba(0,0,0,0.2)';

    box.innerHTML = `
        <h3 style="margin-top:0">Confirmar reverter banimento</h3>
        <p>Tem certeza que deseja suspender este utilizador?</p>
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px">
            <button id="confirmNo" style="padding:8px 14px;border:1px solid #ccc;background:#fff;border-radius:6px;">Não</button>
            <button id="confirmYes" style="padding:8px 14px;background:#28a745;color:#fff;border:none;border-radius:6px;">Sim</button>
        </div>
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    const yesBtn = box.querySelector('#confirmYes');
    const noBtn = box.querySelector('#confirmNo');

    yesBtn.addEventListener('click', async () => {
        try {
            await onConfirm();
        } catch (error) {
            console.error('Error in onConfirm:', error);
        }
        document.body.removeChild(overlay);
    });

    noBtn.addEventListener('click', () => {
        if (onCancel) onCancel();
        document.body.removeChild(overlay);
    });
}

async function fetchUsers(filters = {}) {
    try {
        const params = new URLSearchParams();
        
        if (filters.search && filters.search.trim()) {
            params.append('search', filters.search.trim());
        }
        if (filters.estados && filters.estados.length > 0) {
            params.append('estados', filters.estados.join(','));
        }
        if (filters.estatutos && filters.estatutos.length > 0) {
            params.append('estatutos', filters.estatutos.join(','));
        }
        
        const apiUrl = getApiUrl(`users?${params.toString()}`);
        const response = await fetch(apiUrl);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const users = await response.json();
        return users;
    } catch (error) {
        console.error("Erro ao buscar utilizadores:", error);
        throw error;
    }
}

// Pagination state
let currentPage = 1;
let itemsPerPage = 10;
let allUsers = [];
let totalPages = 1;

// Pagination elements
let paginationSelect = null;
let paginationInfo = null;
let paginationControls = null;

/**
 * Initialize pagination elements and state
 */
function initPagination() {
    paginationSelect = document.querySelector('.pagination-select');
    paginationInfo = document.querySelector('.pagination-info span:last-child');
    paginationControls = document.querySelector('.pagination-controls');

    // Set initial items per page from select
    if (paginationSelect) {
        itemsPerPage = parseInt(paginationSelect.value) || 10;

        // Items per page change handler
        paginationSelect.addEventListener('change', (e) => {
            itemsPerPage = parseInt(e.target.value);
            currentPage = 1;
            updatePagination();
            renderCurrentPage();
        });
    }
}

/**
 * Load users with pagination support
 */
async function loadUsersWithPagination(filters = {}) {
    try {
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

        updatePagination();
        renderCurrentPage();
    } catch (error) {
        console.error("Erro ao carregar utilizadores:", error);
        const tbody = document.querySelector('.admin-users-table tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6">Erro ao carregar dados.</td></tr>';
        }
    }
}

/**
 * Render current page of users
 */
function renderCurrentPage() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageUsers = allUsers.slice(startIndex, endIndex);

    const tbody = document.querySelector('.admin-users-table tbody');
    renderUserTable(pageUsers, tbody);
}

/**
 * Update pagination UI
 */
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

/**
 * Render pagination buttons
 */
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

/**
 * Create pagination button
 */
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

/**
 * Renderiza linhas de tabela de utilizadores para a página de administração
 * @param {Array} users - Array de objetos de utilizadores
 * @param {HTMLElement|string} tbody - Elemento do corpo da tabela ou seletor
 */
function renderUserTable(users, tbody) {
    const tbodyEl = typeof tbody === 'string' 
        ? document.querySelector(tbody) 
        : tbody;
    
    if (!tbodyEl) {
        console.error('Table body not found for renderUserTable');
        return;
    }
    
    tbodyEl.innerHTML = '';
    
    if (!users || users.length === 0) {
        tbodyEl.innerHTML = '<tr><td colspan="6">Nenhum utilizador encontrado.</td></tr>';
        return;
    }
    
    users.forEach(user => {
        const row = document.createElement('tr');
        
        // Check if user is banned (estado_id = 3)
        const isBanned = user.estado_id === 3;
        console.log('User:', user.utilizador_id, 'estado_id:', user.estado_id, 'isBanned:', isBanned);
        
        // Determina a classe do distintivo com base no estado
        let badgeClass = 'status-badge';
        const estadoLower = (user.nome_estado || '').toLowerCase();
        if (estadoLower.includes('normal')) {
            badgeClass += ' status-normal';
        } else if (estadoLower.includes('suspenso')) {
            badgeClass += ' status-suspended';
        }
        
        // Use the color from database if available, otherwise use default
        const badgeStyle = user.estado_cor ? `style="background-color: ${user.estado_cor}; color: white;"` : '';
        
        // Make estatuto text clickable - current funcao_id: 1 = Admin, 2 = Utilizador
        // Determine current funcao_id from user data
        let currentFuncaoId = user.funcao_id;
        if (!currentFuncaoId) {
            // Fallback: determine from estatuto name if funcao_id not provided
            currentFuncaoId = (user.estatuto === 'Admin' || user.estatuto === 'admin') ? 1 : 2;
        }
        const newFuncaoId = currentFuncaoId === 1 ? 2 : 1;
        const banIconHtml = isBanned 
            ? `<i class="fa-solid fa-check banned-check-icon" data-user-id="${user.utilizador_id}" style="color: #198754; cursor: pointer;" title="Clique para reverter banimento"></i>`
            : `<i class="fas fa-ban ban-icon" data-user-id="${user.utilizador_id}" style="cursor: pointer;" title="Banir utilizador"></i>`;

        row.innerHTML = `
            <td>${user.nome_utilizador}</td>
            <td>${user.email}</td>
            <td><span class="${badgeClass}" ${badgeStyle}>${user.nome_estado}</span></td>
            <td><span class="estatuto-cell" data-user-id="${user.utilizador_id}" data-current-funcao="${currentFuncaoId}" data-new-funcao="${newFuncaoId}" title="Clique para alterar entre Admin e Utilizador">${user.estatuto}</span></td>
            <td><i class="fas fa-clock suspend-icon" data-user-id="${user.utilizador_id}" style="cursor: pointer;" title="Suspender utilizador"></i></td>
            <td>${banIconHtml}</td>
        `;
        
        tbodyEl.appendChild(row);
    });
    
    // Add click handlers for estatuto span elements after all rows are added
    tbodyEl.querySelectorAll('.estatuto-cell').forEach(span => {
        span.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const userId = this.getAttribute('data-user-id');
            const newFuncaoId = parseInt(this.getAttribute('data-new-funcao'));
            const currentFuncaoId = parseInt(this.getAttribute('data-current-funcao'));
            
            if (!userId || !newFuncaoId) {
                console.error('Missing data attributes for estatuto cell');
                return;
            }
            
            // Show loading state
            const originalText = this.textContent.trim();
            this.textContent = 'A alterar...';
            this.style.pointerEvents = 'none';
            this.style.opacity = '0.6';
            
            try {
                const apiUrl = getApiUrl(`users/${userId}/funcao`);
                const response = await fetch(apiUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ funcao_id: newFuncaoId })
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result?.error || 'Erro ao atualizar estatuto.');
                }
                
                // Update the span with new values
                this.textContent = result.estatuto;
                this.setAttribute('data-current-funcao', newFuncaoId);
                this.setAttribute('data-new-funcao', currentFuncaoId);
                
            } catch (error) {
                console.error('Erro ao atualizar estatuto:', error);
                alert(error.message || 'Erro ao atualizar estatuto. Por favor, tente novamente.');
                this.textContent = originalText;
            } finally {
                this.style.pointerEvents = 'auto';
                this.style.opacity = '1';
            }
        });
    });
    
    // Add click handlers for suspend icons
    tbodyEl.querySelectorAll('.suspend-icon').forEach(icon => {
        icon.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const userId = this.getAttribute('data-user-id');
            
            if (!userId) {
                console.error('Missing user ID for suspend icon');
                return;
            }
            
            showConfirmSuspend(async function onConfirm() {
                try {
                    const apiUrl = getApiUrl(`users/${userId}/estado`);
                    const response = await fetch(apiUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ estado_id: 2 }) // 2 = Suspenso
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(result?.error || 'Erro ao suspender utilizador.');
                    }
                    
                    // Reload the users table to show updated status
                    if (typeof window.loadUsers === 'function') {
                        window.loadUsers();
                    } else {
                        window.location.reload();
                    }
                    
                } catch (error) {
                    console.error('Erro ao suspender utilizador:', error);
                    alert(error.message || 'Erro ao suspender utilizador. Por favor, tente novamente.');
                }
            }, function onCancel() {
                // No-op on cancel
            });
        });
    });
    
    // Add click handlers for ban icons (only for non-banned users)
    tbodyEl.querySelectorAll('.ban-icon').forEach(icon => {
        icon.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const userId = this.getAttribute('data-user-id');
            
            if (!userId) {
                console.error('Missing user ID for ban icon');
                return;
            }
            
            showConfirmBan(async function onConfirm() {
                try {
                    if (typeof getApiUrl !== 'function') {
                        throw new Error('getApiUrl function not available');
                    }
                    const apiUrl = getApiUrl(`users/${userId}/estado`);
                    const response = await fetch(apiUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ estado_id: 3 }) // 3 = Banido
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(result?.error || 'Erro ao banir utilizador.');
                    }
                    
                    // Show success message
                    alert('Utilizador banido com sucesso. Os avistamentos foram deletados.');
                    
                    // Reload the users table to show updated status
                    if (typeof window.loadUsers === 'function') {
                        window.loadUsers();
                    } else {
                        window.location.reload();
                    }
                    
                } catch (error) {
                    console.error('Erro ao banir utilizador:', error);
                    alert(error.message || 'Erro ao banir utilizador. Por favor, tente novamente.');
                }
            }, function onCancel() {
                // No-op on cancel
            });
        });
    });
    
    // Add click handlers for banned check icons (to unban users)
    const bannedCheckIcons = tbodyEl.querySelectorAll('.banned-check-icon');
    console.log('Found', bannedCheckIcons.length, 'banned check icons');
    bannedCheckIcons.forEach(icon => {
        console.log('Attaching click handler to banned check icon for user:', icon.getAttribute('data-user-id'));
        icon.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Banned check icon clicked for user:', this.getAttribute('data-user-id'));
            
            const userId = this.getAttribute('data-user-id');
            
            if (!userId) {
                console.error('Missing user ID for banned check icon');
                return;
            }
            
            showConfirmUnban(async function onConfirm() {
                try {
                    const apiUrl = getApiUrl(`users/${userId}/estado`);
                    const response = await fetch(apiUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ estado_id: 1 }) // 1 = Normal
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(result?.error || 'Erro ao reverter banimento.');
                    }
                    
                    // Show success message
                    alert('Banimento revertido com sucesso. O utilizador foi restaurado para o estado normal.');
                    
                    // Reload the users table to show updated status
                    if (typeof window.loadUsers === 'function') {
                        window.loadUsers();
                    } else {
                        window.location.reload();
                    }
                    
                } catch (error) {
                    console.error('Erro ao reverter banimento:', error);
                    alert(error.message || 'Erro ao reverter banimento. Por favor, tente novamente.');
                }
            }, function onCancel() {
                // No-op on cancel
            });
        });
    });
}

/**
 * Inicializa filtros de utilizadores com entradas de etiquetas
 * @param {Object} config - Objeto de configuração
 * @param {string} config.estadoInputId - ID do elemento de entrada de estado
 * @param {string} config.estadoTagsId - ID do contentor de etiquetas de estado
 * @param {string} config.estadoDropdownId - ID do menu suspenso de estado
 * @param {string} config.estatutoInputId - ID do elemento de entrada de estatuto
 * @param {string} config.estatutoTagsId - ID do contentor de etiquetas de estatuto
 * @param {string} config.estatutoDropdownId - ID do menu suspenso de estatuto
 * @param {Array<string>} config.estadoOptions - Opções de estado disponíveis
 * @param {Array<string>} config.estatutoOptions - Opções de estatuto disponíveis
 * @param {Array} config.estadoTagsArray - Array para armazenar etiquetas de estado selecionadas
 * @param {Array} config.estatutoTagsArray - Array para armazenar etiquetas de estatuto selecionadas
 * @param {Function} config.onFilterChange - Callback quando os filtros mudam
 */
function initUserFilters(config) {
    const {
        estadoInputId,
        estadoTagsId,
        estadoDropdownId,
        estatutoInputId,
        estatutoTagsId,
        estatutoDropdownId,
        estadoOptions,
        estatutoOptions,
        estadoTagsArray,
        estatutoTagsArray,
        onFilterChange
    } = config;
    
    // Initialize pagination
    initPagination();
    
    // Inicializa entradas de etiquetas se a função existir
    if (typeof initTagInputWithDropdown === 'function') {
        if (estadoInputId && estadoTagsId && estadoDropdownId) {
            initTagInputWithDropdown(
                estadoInputId, 
                estadoTagsId, 
                estadoDropdownId, 
                estadoTagsArray, 
                estadoOptions
            );
        }
        
        if (estatutoInputId && estatutoTagsId && estatutoDropdownId) {
            initTagInputWithDropdown(
                estatutoInputId, 
                estatutoTagsId, 
                estatutoDropdownId, 
                estatutoTagsArray, 
                estatutoOptions
            );
        }
        
        // Observa alterações nas etiquetas
        if (onFilterChange) {
            const observerConfig = { childList: true };
            const observer = new MutationObserver(onFilterChange);
            
            const estadoContainer = document.getElementById(estadoTagsId);
            const estatutoContainer = document.getElementById(estatutoTagsId);
            
            if (estadoContainer) observer.observe(estadoContainer, observerConfig);
            if (estatutoContainer) observer.observe(estatutoContainer, observerConfig);
        }
    }
}

/**
 * Obtém valores de filtro das entradas e arrays de etiquetas
 * @param {Object} config - Objeto de configuração
 * @param {HTMLElement|string} config.searchInput - Elemento de entrada de pesquisa ou seletor
 * @param {Array} config.estadoTagsArray - Array de etiquetas de estado selecionadas
 * @param {Array} config.estatutoTagsArray - Array de etiquetas de estatuto selecionadas
 * @returns {Object} Objeto de filtro com pesquisa, estados e estatutos
 */
function getUserFilters(config) {
    const {
        searchInput,
        estadoTagsArray = [],
        estatutoTagsArray = []
    } = config;
    
    const searchEl = typeof searchInput === 'string' 
        ? document.querySelector(searchInput) 
        : searchInput;
    
    return {
        search: searchEl ? searchEl.value.trim() : '',
        estados: estadoTagsArray,
        estatutos: estatutoTagsArray
    };
}

/**
 * Limpa todos os filtros de utilizadores
 * @param {Object} config - Objeto de configuração
 * @param {HTMLElement|string} config.searchInput - Elemento de entrada de pesquisa ou seletor
 * @param {Array} config.estadoTagsArray - Array de etiquetas de estado selecionadas
 * @param {Array} config.estatutoTagsArray - Array de etiquetas de estatuto selecionadas
 * @param {string} config.estadoTagsId - ID do contentor de etiquetas de estado
 * @param {string} config.estatutoTagsId - ID do contentor de etiquetas de estatuto
 * @param {string} config.estadoInputId - ID do elemento de entrada de estado
 * @param {string} config.estatutoInputId - ID do elemento de entrada de estatuto
 */
function clearUserFilters(config) {
    const {
        searchInput,
        estadoTagsArray = [],
        estatutoTagsArray = [],
        estadoTagsId,
        estatutoTagsId,
        estadoInputId,
        estatutoInputId
    } = config;
    
    // Clear search input
    const searchEl = typeof searchInput === 'string' 
        ? document.querySelector(searchInput) 
        : searchInput;
    if (searchEl) {
        searchEl.value = '';
    }
    
    // Clear tag arrays
    if (Array.isArray(estadoTagsArray)) {
        estadoTagsArray.length = 0;
    }
    if (Array.isArray(estatutoTagsArray)) {
        estatutoTagsArray.length = 0;
    }
    
    // Clear tag containers
    if (estadoTagsId) {
        const estadoContainer = document.getElementById(estadoTagsId);
        if (estadoContainer) {
            estadoContainer.innerHTML = '';
        }
    }
    if (estatutoTagsId) {
        const estatutoContainer = document.getElementById(estatutoTagsId);
        if (estatutoContainer) {
            estatutoContainer.innerHTML = '';
        }
    }
    
    // Clear input fields
    if (estadoInputId) {
        const estadoInput = document.getElementById(estadoInputId);
        if (estadoInput) {
            estadoInput.value = '';
        }
    }
    if (estatutoInputId) {
        const estatutoInput = document.getElementById(estatutoInputId);
        if (estatutoInput) {
            estatutoInput.value = '';
        }
    }
}

