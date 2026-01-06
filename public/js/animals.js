
/**
 * Obtém animais da API com filtros opcionais
 * @param {Object} filters - Opções de filtro
 * @param {string} filters.search - Consulta de pesquisa de texto
 * @param {Array<string>} filters.families - Array de nomes de famílias para filtrar
 * @param {Array<string>} filters.states - Array de nomes de estados de conservação para filtrar
 * @returns {Promise<Array>} Array de objetos de animais
 */
async function fetchAnimals(filters = {}) {
    try {
        const params = new URLSearchParams();
        
        if (filters.search && filters.search.trim()) {
            params.append('search', filters.search.trim());
        }
        if (filters.families && filters.families.length > 0) {
            params.append('families', filters.families.join(','));
        }
        if (filters.states && filters.states.length > 0) {
            params.append('states', filters.states.join(','));
        }
        
        const queryString = params.toString();
        // Ternary: if queryString exists, append it with "?", otherwise use base endpoint
        const apiUrl = getApiUrl(queryString ? `animais?${queryString}` : 'animais');
        const response = await fetch(apiUrl);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const animals = await response.json();
        return animals;
    } catch (error) {
        throw error;
    }
}

/**
 * Renderiza os animal cards num contentor
 * @param {Array} animals - Array de objetos de animais
 * @param {HTMLElement|string} container - Elemento contentor ou seletor
 * @param {Object} options - Opções de renderização
 * @param {string} options.cardClass - Classe CSS adicional para os cartões
 * @param {string} options.buttonText - Texto para o botão de ação
 * @param {Function} options.onButtonClick - Callback quando o botão é clicado (recebe o objeto do animal)
 * @param {boolean} options.showDetailsLink - Se deve mostrar a ligação "Ver detalhes" (predefinição: true)
 * @param {string} options.emptyMessage - Mensagem a mostrar quando nenhum animal é encontrado
 */
function renderAnimalCards(animals, container, options = {}) {
    const {
        cardClass = '',
        buttonText = 'Ver detalhes',
        onButtonClick = null,
        showDetailsLink = true,
        emptyMessage = 'Nenhum animal encontrado.'
    } = options;
    
    const containerEl = typeof container === 'string' 
        ? document.querySelector(container) 
        : container;
    
    if (!containerEl) {
        return;
    }
    
    containerEl.innerHTML = '';
    
    if (!animals || animals.length === 0) {
        containerEl.innerHTML = `<p>${emptyMessage}</p>`;
        return;
    }
    
    animals.forEach(animal => {
        const card = document.createElement('div');
        card.className = `card green-shadow ${cardClass}`.trim();
        
        // Determina o conteúdo do botão/ligação
        let buttonContent = '';
        if (onButtonClick) {
            buttonContent = `
                <a href="javascript:void(0)" class="btn-details" data-animal-id="${animal.animal_id}">
                    <span>${buttonText === 'Selecionar' ? '✓' : '👁'}</span> 
                    <span class="btn-text">${buttonText}</span>
                </a>
            `;
        } else if (showDetailsLink) {
            buttonContent = `
                <a href="animal_desc.php?id=${animal.animal_id}" class="btn-details">
                    <span>👁</span> ${buttonText}
                </a>
            `;
        }
        
        // Determine badge color - use grey for "Não Avaliada" or if estado_cor is white
        let badgeColor = animal.estado_cor || '#ccc';
        const estadoLower = (animal.nome_estado || '').toLowerCase();
        if (estadoLower === 'não avaliada' || estadoLower === 'nao avaliada' || 
            estadoLower === 'não avaliado' || estadoLower === 'nao avaliado' ||
            badgeColor === '#FFFFFF' || badgeColor === '#ffffff' || badgeColor === 'white') {
            badgeColor = '#9ca3af'; // Grey color
        }
        
        card.innerHTML = `
            <img src="${animal.url_imagem || 'img/placeholder.jpg'}" 
                 alt="${animal.nome_comum}" 
                 class="card-image" 
                 onerror="this.src='img/placeholder.jpg'">
            <div class="card-content">
                <div class="card-header">
                    <h3>${animal.nome_comum}</h3>
                    <span class="badge" style="background-color: ${badgeColor}; color: white;">
                        ${animal.nome_estado}
                    </span>
                </div>
                <div class="family">
                    <span class="family-icon"><i class="fa-solid fa-paw"></i></span>
                    <span>${animal.nome_familia}</span>
                </div>
                <p class="description">${animal.descricao || ''}</p>
                ${buttonContent}
            </div>
        `;
        
        if (onButtonClick) {
            const button = card.querySelector('.btn-details');
            if (button) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    onButtonClick(animal, card);
                });
            }
        }
        
        // Adiciona atributos de dados para filtragem (usado no popup do index.php)
        card.setAttribute('data-name', animal.nome_comum);
        card.setAttribute('data-family', animal.nome_familia);
        card.setAttribute('data-status', animal.nome_estado);
        
        containerEl.appendChild(card);
    });
}

/**
 * Renderiza linhas de tabela de animais para a página de administração
 * @param {Array} animals - Array de objetos de animais
 * @param {HTMLElement|string} tbody - Elemento do corpo da tabela ou seletor
 */
function renderAnimalTable(animals, tbody) {
    const tbodyEl = typeof tbody === 'string' 
        ? document.querySelector(tbody) 
        : tbody;
    
    if (!tbodyEl) {
        return;
    }
    
    tbodyEl.innerHTML = '';
    
    if (!animals || animals.length === 0) {
        tbodyEl.innerHTML = '<tr><td colspan="5">Nenhum animal encontrado.</td></tr>';
        return;
    }
    
    animals.forEach(animal => {
        const row = document.createElement('tr');
        
        // Determina a classe do distintivo com base no estado
        let badgeClass = 'badge';
        const stateLower = (animal.nome_estado || '').toLowerCase();
        if (stateLower.includes('crítico') || stateLower.includes('critico')) {
            badgeClass += ' critical';
        } else if (stateLower.includes('perigo') && !stateLower.includes('quase')) {
            badgeClass += ' endangered';
        } else if (stateLower.includes('vulnerável') || stateLower.includes('vulneravel')) {
            badgeClass += ' vulnerable';
        } else if (stateLower.includes('quase') || stateLower.includes('ameaçada') || stateLower.includes('ameacada')) {
            badgeClass += ' threatened';
        } else if (stateLower === 'não avaliada' || stateLower === 'nao avaliada' || 
                   stateLower === 'não avaliado' || stateLower === 'nao avaliado') {
            badgeClass += ' unknown';
        }
        
        // Determine badge color - use grey for "Não Avaliada" or if estado_cor is white
        let badgeColor = animal.estado_cor || '#ccc';
        if (stateLower === 'não avaliada' || stateLower === 'nao avaliada' || 
            stateLower === 'não avaliado' || stateLower === 'nao avaliado' ||
            badgeColor === '#FFFFFF' || badgeColor === '#ffffff' || badgeColor === 'white') {
            badgeColor = '#9ca3af'; // Grey color
        }
        
        row.innerHTML = `
            <td><a href="animal_desc.php?id=${animal.animal_id}" style="color: var(--accent-color);">${animal.nome_comum}</a></td>
            <td>${animal.nome_familia}</td>
            <td><span class="${badgeClass}" style="background-color: ${badgeColor}; color: white;">${animal.nome_estado}</span></td>
            <td><i class="fas fa-sync-alt update-icon" data-animal-id="${animal.animal_id}" style="cursor: pointer;"></i></td>
            <td><i class="fas fa-ban ban-icon" data-animal-id="${animal.animal_id}" style="cursor: pointer;"></i></td>
        `;
        
        tbodyEl.appendChild(row);
    });
}

/**
 * Inicializa filtros de animais com entradas de etiquetas
 * @param {Object} config - Objeto de configuração
 * @param {string} config.familyInputId - ID do elemento de entrada de família
 * @param {string} config.familyTagsId - ID do contentor de etiquetas de família
 * @param {string} config.familyDropdownId - ID do menu suspenso de família
 * @param {string} config.stateInputId - ID do elemento de entrada de estado
 * @param {string} config.stateTagsId - ID do contentor de etiquetas de estado
 * @param {string} config.stateDropdownId - ID do menu suspenso de estado
 * @param {Array<string>} config.familyOptions - Opções de família disponíveis
 * @param {Array<string>} config.stateOptions - Opções de estado disponíveis
 * @param {Array} config.familyTagsArray - Array para armazenar etiquetas de família selecionadas
 * @param {Array} config.stateTagsArray - Array para armazenar etiquetas de estado selecionadas
 * @param {Function} config.onFilterChange - Callback quando os filtros mudam
 */
function initAnimalFilters(config) {
    const {
        familyInputId,
        familyTagsId,
        familyDropdownId,
        stateInputId,
        stateTagsId,
        stateDropdownId,
        familyOptions,
        stateOptions,
        familyTagsArray,
        stateTagsArray,
        onFilterChange
    } = config;
    
    // Inicializa entradas de etiquetas se a função existir
    if (typeof initTagInputWithDropdown === 'function') {
        if (familyInputId && familyTagsId && familyDropdownId) {
            initTagInputWithDropdown(
                familyInputId, 
                familyTagsId, 
                familyDropdownId, 
                familyTagsArray, 
                familyOptions
            );
        }
        
        if (stateInputId && stateTagsId && stateDropdownId) {
            initTagInputWithDropdown(
                stateInputId, 
                stateTagsId, 
                stateDropdownId, 
                stateTagsArray, 
                stateOptions
            );
        }
        
        // Observa alterações nas etiquetas
        if (onFilterChange) {
            const observerConfig = { childList: true };
            const observer = new MutationObserver(onFilterChange);
            
            const famContainer = document.getElementById(familyTagsId);
            const stateContainer = document.getElementById(stateTagsId);
            
            if (famContainer) observer.observe(famContainer, observerConfig);
            if (stateContainer) observer.observe(stateContainer, observerConfig);
        }
    }
}

/**
 * Obtém valores de filtro das entradas e arrays de etiquetas
 * @param {Object} config - Objeto de configuração
 * @param {HTMLElement|string} config.searchInput - Elemento de entrada de pesquisa ou seletor
 * @param {Array} config.familyTagsArray - Array de etiquetas de família selecionadas
 * @param {Array} config.stateTagsArray - Array de etiquetas de estado selecionadas
 * @returns {Object} Objeto de filtro com pesquisa, famílias e estados
 */
function getAnimalFilters(config) {
    const {
        searchInput,
        familyTagsArray = [],
        stateTagsArray = []
    } = config;
    
    const searchEl = typeof searchInput === 'string' 
        ? document.querySelector(searchInput) 
        : searchInput;
    
    return {
        search: searchEl ? searchEl.value.trim() : '',
        families: familyTagsArray,
        states: stateTagsArray
    };
}

/**
 * Limpa todos os filtros de animais
 * @param {Object} config - Objeto de configuração
 * @param {HTMLElement|string} config.searchInput - Elemento de entrada de pesquisa ou seletor
 * @param {Array} config.familyTagsArray - Array de etiquetas de família selecionadas
 * @param {Array} config.stateTagsArray - Array de etiquetas de estado selecionadas
 * @param {string} config.familyTagsId - ID do contentor de etiquetas de família
 * @param {string} config.stateTagsId - ID do contentor de etiquetas de estado
 * @param {string} config.familyInputId - ID do elemento de entrada de família
 * @param {string} config.stateInputId - ID do elemento de entrada de estado
 */
function clearAnimalFilters(config) {
    const {
        searchInput,
        familyTagsArray = [],
        stateTagsArray = [],
        familyTagsId,
        stateTagsId,
        familyInputId,
        stateInputId
    } = config;
    
    // Clear search input
    const searchEl = typeof searchInput === 'string' 
        ? document.querySelector(searchInput) 
        : searchInput;
    if (searchEl) {
        searchEl.value = '';
    }
    
    // Clear tag arrays
    if (Array.isArray(familyTagsArray)) {
        familyTagsArray.length = 0;
    }
    if (Array.isArray(stateTagsArray)) {
        stateTagsArray.length = 0;
    }
    
    // Clear tag containers
    if (familyTagsId) {
        const familyContainer = document.getElementById(familyTagsId);
        if (familyContainer) {
            familyContainer.innerHTML = '';
        }
    }
    if (stateTagsId) {
        const stateContainer = document.getElementById(stateTagsId);
        if (stateContainer) {
            stateContainer.innerHTML = '';
        }
    }
    
    // Clear input fields
    if (familyInputId) {
        const familyInput = document.getElementById(familyInputId);
        if (familyInput) {
            familyInput.value = '';
        }
    }
    if (stateInputId) {
        const stateInput = document.getElementById(stateInputId);
        if (stateInput) {
            stateInput.value = '';
        }
    }
}
