
/**
 * Obtém utilizadores da API com filtros opcionais
 * @param {Object} filters - Opções de filtro
 * @param {string} filters.search - Consulta de pesquisa de texto
 * @param {Array<string>} filters.estados - Array de nomes de estados para filtrar
 * @param {Array<string>} filters.estatutos - Array de nomes de estatutos (funções) para filtrar
 * @returns {Promise<Array>} Array de objetos de utilizadores
 */
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
        
        const apiUrl = window.API_CONFIG?.getUrl(`users?${params.toString()}`) || `/users?${params.toString()}`;
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
        
        row.innerHTML = `
            <td>${user.nome_utilizador}</td>
            <td>${user.email}</td>
            <td><span class="${badgeClass}" ${badgeStyle}>${user.nome_estado}</span></td>
            <td><h1 class="estatuto-cell" data-user-id="${user.utilizador_id}" data-current-funcao="${currentFuncaoId}" data-new-funcao="${newFuncaoId}" title="Clique para alterar entre Admin e Utilizador">${user.estatuto}</h1></td>
            <td><i class="fas fa-clock suspend-icon"></i></td>
            <td><i class="fas fa-ban ban-icon"></i></td>
        `;
        
        tbodyEl.appendChild(row);
    });
    
    // Add click handlers for estatuto text spans after all rows are added
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
                const apiUrl = window.API_CONFIG?.getUrl(`users/${userId}/funcao`) || `/users/${userId}/funcao`;
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

