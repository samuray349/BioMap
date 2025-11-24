let map;
let familyTags = [];
let stateTags = [];
let rightClickPosition = null; // Store this globally to pass to the alert menu

// Species Panel Manager
const SpeciesPanel = {
  isInitialized: false,
  isOpen: false,
  
  elements: {
    container: null,
    image: null,
    name: null,
    scientificName: null,
    family: null,
    diet: null,
    description: null,
    alertDate: null,
    location: null,
    closeButton: null,
    mainContainer: null
  },

  init() {
    if (this.isInitialized) {
      console.log('Panel already initialized');
      return;
    }
    
    console.log('Initializing SpeciesPanel...');
    // Get all panel elements
    this.elements.container = document.getElementById('species-panel');
    if (!this.elements.container) {
      // If we are not on the map page, this is expected
      return;
    }
    console.log('Species panel element found:', this.elements.container);

    this.elements.image = document.getElementById('species-panel-image');
    this.elements.name = document.getElementById('species-panel-name');
    this.elements.scientificName = document.getElementById('species-panel-scientific');
    this.elements.family = document.getElementById('species-panel-family');
    this.elements.diet = document.getElementById('species-panel-diet');
    this.elements.description = document.getElementById('species-panel-description');
    this.elements.alertDate = document.getElementById('species-panel-alert-date');
    this.elements.location = document.getElementById('species-panel-location');
    this.elements.closeButton = document.getElementById('species-panel-close');
    this.elements.mainContainer = document.querySelector('.main-container');

    // Setup event listeners
    this.setupEventListeners();
    
    this.isInitialized = true;
  },

  setupEventListeners() {
    // Close button
    if (this.elements.closeButton) {
      this.elements.closeButton.addEventListener('click', () => this.close());
    }

    // ESC key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.isOpen) {
        this.close();
      }
    });

    // Click outside to close
    document.addEventListener('click', (e) => {
      if (!this.isOpen) return;
      if (this.elements.container.contains(e.target)) return;
      if (e.target.closest('.marker-label')) return;
      if (e.target.closest('.sidebar')) return;
      this.close();
    });
  },

  open(details) {
    console.log('SpeciesPanel.open called with:', details);
    
    if (!this.isInitialized) {
      console.log('Panel not initialized, initializing...');
      this.init();
    }
    
    if (!this.elements.container) {
      console.error('Cannot open panel: container not found');
      return;
    }
    
    // Populate panel with data
    this.populateData(details);

    // Show panel
    this.elements.container.classList.add('show');
    this.elements.container.setAttribute('aria-hidden', 'false');
    
    // Force styles directly via JavaScript since CSS isn't applying
    this.elements.container.style.transform = 'translateX(0)';
    this.elements.container.style.opacity = '1';
    this.elements.container.style.visibility = 'visible';
    this.elements.container.style.pointerEvents = 'auto';
    this.elements.container.style.zIndex = '10000';
    this.elements.container.style.display = 'block';
    
    if (this.elements.mainContainer) {
      this.elements.mainContainer.classList.add('detail-panel-open');
    }

    // UPDATED CODE: Add a slight delay to prevent the document click listener 
    // from closing the panel immediately in the same event loop.
    setTimeout(() => {
      this.isOpen = true;
      console.log('Panel visible. isOpen set to true.');
    }, 100);
  },

  populateData(details) {
    if (!details) return;

    // Image
    if (this.elements.image && details.image) {
      this.elements.image.src = details.image;
      this.elements.image.alt = details.name || 'Animal';
    }

    // Text content
    if (this.elements.name) {
      this.elements.name.textContent = details.name || 'Animal sem nome';
    }
    if (this.elements.scientificName) {
      this.elements.scientificName.textContent = details.scientificName || '—';
    }
    if (this.elements.family) {
      this.elements.family.textContent = details.family || '—';
    }
    if (this.elements.diet) {
      this.elements.diet.textContent = details.diet || '—';
    }
    if (this.elements.description) {
      this.elements.description.textContent = details.description || '';
    }
    if (this.elements.alertDate) {
      this.elements.alertDate.textContent = details.alertDate || '—';
    }

    // Location
    if (this.elements.location) {
      const coords = details.coordinates || {};
      if (typeof coords.lat === 'number' && typeof coords.lng === 'number') {
        this.elements.location.textContent = `${coords.lat.toFixed(3)}, ${coords.lng.toFixed(3)}`;
      } else {
        this.elements.location.textContent = '—';
      }
    }
  },

  close() {
    if (!this.isOpen || !this.elements.container) return;

    this.elements.container.classList.remove('show');
    this.elements.container.setAttribute('aria-hidden', 'true');
    
    // Reset styles
    this.elements.container.style.transform = '';
    this.elements.container.style.opacity = '';
    this.elements.container.style.visibility = '';
    this.elements.container.style.pointerEvents = '';
    
    if (this.elements.mainContainer) {
      this.elements.mainContainer.classList.remove('detail-panel-open');
    }

    this.isOpen = false;
  }
};


function initMap() {
  const mapElement = document.getElementById("map");
  if (!mapElement) return;

  const center = { lat: 39.09903420850493, lng: -9.283192320989297 };

  const pawIconSVG = `
<svg width="60" height="80" viewBox="0 0 60 80" xmlns="http://www.w3.org/2000/svg">
  <path d="M 30,0 C 15,0 0,15 0,30 C 0,45 15,60 30,80 C 45,60 60,45 60,30 C 60,15 45,0 30,0 Z"
        fill="#1A8F4A" stroke="white" stroke-width="3"/>
  <g fill="none" stroke="white" stroke-width="3" transform="translate(0, -2)">
    <ellipse cx="14" cy="28" rx="4.5" ry="5.5" transform="rotate(-40 14 28)" />
    <ellipse cx="24" cy="20" rx="4.5" ry="5.5" transform="rotate(-15 24 20)" />
    <ellipse cx="36" cy="20" rx="4.5" ry="5.5" transform="rotate(15 36 20)" />
    <ellipse cx="46" cy="28" rx="4.5" ry="5.5" transform="rotate(40 46 28)" />
    <path d="M 30 33 C 38 33, 44 39, 44 45 C 44 51, 38 55, 30 55 C 22 55, 16 51, 16 45 C 16 39, 22 33, 30 33 Z" />
  </g>
</svg>
`;

const pawMarkerIcon = {
  url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(pawIconSVG),
  scaledSize: new google.maps.Size(45, 60),
  anchor: new google.maps.Point(22.5, 60)
};


  map = new google.maps.Map(mapElement, {
    zoom: 12,
    center,
    disableDefaultUI: true,
    zoomControl: true,
    mapId: "DEMO_MAP_ID",
  });

  const locations = [
    { 
      position: { lat: 39.098569723610105,  lng: -9.21834924308909 }, 
      title: "Fundação dos Animais",
      type:"intituicao",
      details: {
        name: "Fundação dos Animais",
        institutionType: "Centro de Reabilitação",
        description: "Base de operações da equipa BioMap responsável por monitorizar espécies ameaçadas na região e acolher animais em recuperação.",
        alertDate: "15-10-2025 10:05",
        coordinates: { lat: 39.098569723610105, lng: -9.21834924308909 },
        image: "https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=700&q=80"
      }
    },
    { 
      position: { lat: 39.13471130131973, lng: -9.299138410129158 }, 
      title: "Lince Ibérico",
      type:"animal",
      details: {
        name: "Lince ibérico",
        scientificName: "Lynx pardinus",
        family: "Felídeos",
        diet: "Carnívoro",
        description: "O lince-ibérico é um felino de tamanho médio, oriundo da Península Ibérica, facilmente identificável pela sua pelagem castanho-amarelada com manchas escuras, tufos de pêlo nas orelhas em forma de pincel e cauda curta com ponta negra. É um predador solitário e territorial que vive em matagais mediterrânicos.",
        alertDate: "17-10-2025 19:23",
        coordinates: { lat: 39.13471130131973, lng: -9.299138410129158 },
        image: "img/lince-login.jpeg"
      }
    },
    { 
      position: { lat: 39.16084345764295, lng: -9.237634072626696 }, 
      title: "Javali",
      type:"animal",
      details: {
        name: "Javali europeu",
        scientificName: "Sus scrofa",
        family: "Suídeos",
        diet: "Omnívoro",
        description: "Registo de javali adulto observado próximo de zonas agrícolas. Espécie oportunista com atividade crepuscular, pode causar danos em culturas se não for monitorizada.",
        alertDate: "12-10-2025 06:41",
        coordinates: { lat: 39.16084345764295, lng: -9.237634072626696 },
        image: "https://images.unsplash.com/photo-1503264116251-35a269479413?auto=format&fit=crop&w=700&q=80"
      }
    }
  ];

  locations.forEach(loc => {
    const marker = new google.maps.Marker({
      position: loc.position,
      map,
      icon: pawMarkerIcon,
      label: {
        text: loc.title,
        className: "marker-label"
      },
      title: loc.title
    });
    
    marker.addListener("click", () => {
      try {
        SpeciesPanel.open(loc.details || createFallbackDetails(loc));
      } catch (error) {
        console.error('Error opening panel:', error);
      }
    });
  });

  const familyOptions = [
    "Felidae", "Canidae", "Ursidae", "Mustelidae", 
    "Cervidae", "Suidae", "Leporidae", "Sciuridae",
    "Rodentia", "Chiroptera", "Carnivora", "Artiodactyla"
  ];
  
  const stateOptions = [
    "Em Perigo", "Vulnerável", "Quase Ameaçado",
    "Pouco Preocupante", "Dados Insuficientes",
    "Extinto na Natureza", "Extinto"
  ];

  initTagInputWithDropdown("family-input", "family-tags", "family-dropdown", familyTags, familyOptions);
  initTagInputWithDropdown("state-input", "state-tags", "state-dropdown", stateTags, stateOptions);

  initContextMenu();
  initAlertAnimalMenu();
  SpeciesPanel.init();
}

function createFallbackDetails(location) {
  return {
    name: location.title,
    scientificName: "—",
    family: "—",
    diet: "—",
    description: "Informação detalhada indisponível para este ponto.",
    alertDate: new Date().toLocaleString('pt-PT'),
    coordinates: location.position,
    image: "https://images.unsplash.com/photo-1465101162946-4377e57745c3?auto=format&fit=crop&w=700&q=80"
  };
}

// Tag input functionality with dropdown
function initTagInputWithDropdown(inputId, containerId, dropdownId, tagsArray, options) {
  const input = document.getElementById(inputId);
  const container = document.getElementById(containerId);
  const dropdown = document.getElementById(dropdownId);
  if (!input || !container || !dropdown) return; // Guard clause
  
  const wrapper = input.closest('.tag-input-wrapper');

  function addTag(text) {
    if (!text || !text.trim() || tagsArray.includes(text.trim())) return;
    
    const tag = text.trim();
    tagsArray.push(tag);
    renderTags();
    renderDropdown();
    
    input.value = '';
    input.focus();
  }

  function removeTag(index) {
    tagsArray.splice(index, 1);
    renderTags();
    renderDropdown();
    input.focus();
  }

  function renderTags() {
    container.innerHTML = '';
    tagsArray.forEach((tag, index) => {
      const tagElement = document.createElement('span');
      tagElement.className = 'tag';
      tagElement.innerHTML = `
        ${tag}
        <i class="fas fa-times tag-remove" data-index="${index}"></i>
      `;
      container.appendChild(tagElement);
    });

    container.querySelectorAll('.tag-remove').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const index = parseInt(e.target.getAttribute('data-index'));
        removeTag(index);
      });
    });
  }

  function renderDropdown() {
    const searchTerm = input.value.toLowerCase().trim();
    const filteredOptions = options.filter(opt => 
      opt.toLowerCase().includes(searchTerm) && !tagsArray.includes(opt)
    );

    dropdown.innerHTML = '';

    if (filteredOptions.length === 0 && searchTerm) {
      const noResults = document.createElement('div');
      noResults.className = 'dropdown-item';
      noResults.textContent = 'Nenhum resultado encontrado';
      noResults.style.cursor = 'default';
      noResults.style.color = '#999';
      dropdown.appendChild(noResults);
      dropdown.classList.add('show');
    } else if (filteredOptions.length > 0) {
      filteredOptions.forEach(option => {
        const item = document.createElement('div');
        item.className = 'dropdown-item';
        item.textContent = option;
        item.addEventListener('click', (e) => {
          e.stopPropagation();
          addTag(option);
          dropdown.classList.remove('show');
        });
        dropdown.appendChild(item);
      });
      dropdown.classList.add('show');
    } else if (searchTerm === '') {
      const availableOptions = options.filter(opt => !tagsArray.includes(opt));
      if (availableOptions.length > 0) {
        availableOptions.forEach(option => {
          const item = document.createElement('div');
          item.className = 'dropdown-item';
          item.textContent = option;
          item.addEventListener('click', (e) => {
            e.stopPropagation();
            addTag(option);
            dropdown.classList.remove('show');
          });
          dropdown.appendChild(item);
        });
        dropdown.classList.add('show');
      } else {
        dropdown.classList.remove('show');
      }
    } else {
      dropdown.classList.remove('show');
    }
  }

  input.addEventListener('input', () => { renderDropdown(); });
  input.addEventListener('focus', () => { renderDropdown(); });

  if (wrapper) {
    wrapper.addEventListener('click', (e) => {
      if (e.target !== input && !e.target.closest('.tag') && !e.target.closest('.tag-container')) {
        input.focus();
        renderDropdown();
      }
    });
  }

  document.addEventListener('click', (e) => {
    if (wrapper && !wrapper.contains(e.target)) {
      dropdown.classList.remove('show');
    }
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowDown' && dropdown.classList.contains('show')) {
      e.preventDefault();
      const firstItem = dropdown.querySelector('.dropdown-item:not([style*="cursor: default"])');
      if (firstItem) firstItem.focus();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const firstItem = dropdown.querySelector('.dropdown-item:not([style*="cursor: default"])');
      if (firstItem) firstItem.click();
    } else if (e.key === 'Escape') {
      dropdown.classList.remove('show');
      input.blur();
    }
  });

  renderTags();
}

// Context Menu functionality
function initContextMenu() {
  const contextMenu = document.getElementById('context-menu');
  const menuAlert = document.getElementById('menu-alert');
  const menuLocation = document.getElementById('menu-location');
  const mapContainer = document.getElementById('map-container');
  let mouseX = 0;
  let mouseY = 0;

  if (!contextMenu || !mapContainer) return;

  mapContainer.addEventListener('mousemove', (e) => {
    const rect = mapContainer.getBoundingClientRect();
    mouseX = e.clientX - rect.left;
    mouseY = e.clientY - rect.top;
  });

  map.addListener('rightclick', (e) => {
    rightClickPosition = e.latLng; 
    contextMenu.style.left = (mouseX + 10) + 'px';
    contextMenu.style.top = (mouseY + 10) + 'px';
    contextMenu.classList.add('show');
  });

  mapContainer.addEventListener('contextmenu', (e) => {
    e.preventDefault();
  });

  map.addListener('click', () => {
    contextMenu.classList.remove('show');
  });

  map.addListener('dragstart', () => {
    contextMenu.classList.remove('show');
  });

  if (menuAlert) {
    menuAlert.addEventListener('click', () => {
      const alertMenu = document.getElementById('alert-animal-menu');
      const locationInput = document.getElementById('alert-animal-location');
      
      if (rightClickPosition && locationInput) {
        const lat = rightClickPosition.lat().toFixed(6);
        const lng = rightClickPosition.lng().toFixed(6);
        locationInput.value = `${lat}, ${lng}`;
      }
      
      if (alertMenu) alertMenu.classList.add('show');
      contextMenu.classList.remove('show');
    });
  }

  if (menuLocation) {
    menuLocation.addEventListener('click', () => {
      if (rightClickPosition) {
        const lat = rightClickPosition.lat().toFixed(6);
        const lng = rightClickPosition.lng().toFixed(6);
        
        navigator.clipboard.writeText(`${lat}, ${lng}`).then(() => {
          alert(`Localização copiada:\nLat: ${lat}\nLng: ${lng}`);
        }).catch(() => {
          prompt('Localização:', `${lat}, ${lng}`);
        });
      }
      contextMenu.classList.remove('show');
    });
  }

  document.addEventListener('click', (e) => {
    if (contextMenu && !contextMenu.contains(e.target)) {
      contextMenu.classList.remove('show');
    }
  });
}

function initAlertAnimalMenu() {
  const alertMenu = document.getElementById('alert-animal-menu');
  if (!alertMenu) return;

  const submitButton = document.getElementById('submit-alert-animal');
  const closeButtons = document.querySelectorAll('.close-alert-menu');
  const locationInput = document.getElementById('alert-animal-location');
  const displayLocation = document.getElementById('display-location-text');
  
  // Elements for filtering
  const cards = alertMenu.querySelectorAll('.card'); // Note: We use .card class now
  const searchInput = document.getElementById('popup-search-input');
  
  // Unique Tag Arrays for the popup to avoid conflict with sidebar
  let popupFamilyTags = [];
  let popupStateTags = [];

  // Options (Same as animais.html)
  const familyOptions = [
    "Felídeos", "Canidae", "Ursidae", "Mustelidae", 
    "Cervidae", "Suidae", "Leporidae", "Sciuridae",
    "Rodentia", "Chiroptera", "Carnivora", "Artiodactyla",
    "Psittacidae", "Accipitridae", "Bovidae", "Lacertidae"
  ];
  
  const stateOptions = [
    "Em Perigo", "Vulnerável", "Quase Ameaçado",
    "Pouco Preocupante", "Dados Insuficientes",
    "Extinto na Natureza", "Extinto", "Perigo crítico"
  ];

  // Initialize Tags using existing helper function
  initTagInputWithDropdown("popup-family-input", "popup-family-tags", "popup-family-dropdown", popupFamilyTags, familyOptions);
  initTagInputWithDropdown("popup-state-input", "popup-state-tags", "popup-state-dropdown", popupStateTags, stateOptions);

  let selectedAnimal = null;

  // --- Filtering Logic ---
  function filterCards() {
    const searchText = searchInput ? searchInput.value.toLowerCase() : '';

    cards.forEach(card => {
      const name = card.getAttribute('data-name').toLowerCase();
      const status = card.getAttribute('data-status'); 
      const family = card.getAttribute('data-family');
      
      const matchesSearch = name.includes(searchText);
      
      // OR Logic for Tags (if array is empty, allow all)
      const matchesStatus = popupStateTags.length === 0 || popupStateTags.includes(status);
      const matchesFamily = popupFamilyTags.length === 0 || popupFamilyTags.includes(family);

      if (matchesSearch && matchesStatus && matchesFamily) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  // --- Event Listeners for Filtering ---
  if (searchInput) searchInput.addEventListener('input', filterCards);

  // Observer for tags (since the helper modifies DOM)
  const observerConfig = { childList: true };
  const tagObserver = new MutationObserver(filterCards);
  
  const famContainer = document.getElementById('popup-family-tags');
  const stateContainer = document.getElementById('popup-state-tags');
  
  if (famContainer) tagObserver.observe(famContainer, observerConfig);
  if (stateContainer) tagObserver.observe(stateContainer, observerConfig);


  // --- Selection Logic ---
  cards.forEach(card => {
    card.addEventListener('click', () => {
      // 1. UI Updates
      cards.forEach(c => {
          c.classList.remove('selected');
          c.querySelector('.btn-text').textContent = 'Selecionar';
      });
      
      card.classList.add('selected');
      card.querySelector('.btn-text').textContent = 'Selecionado';

      // 2. Data Storage
      selectedAnimal = {
        name: card.getAttribute('data-name'),
        family: card.getAttribute('data-family'),
        status: card.getAttribute('data-status')
      };

      // 3. Enable Button
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.style.opacity = '1';
        submitButton.style.cursor = 'pointer';
      }
    });
  });

  // --- Menu Visibility & Location ---
  const originalMenuAlertClick = document.getElementById('menu-alert');
  if (originalMenuAlertClick) {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.target.classList.contains('show')) {
               if (locationInput && locationInput.value && displayLocation) {
                   displayLocation.textContent = locationInput.value;
               }
            }
        });
    });
    observer.observe(alertMenu, { attributes: true, attributeFilter: ['class'] });
  }

  function closeMenu() {
    alertMenu.classList.remove('show');
    // Reset Inputs
    if(searchInput) searchInput.value = '';
    
    // Clear Tags
    popupFamilyTags.length = 0;
    popupStateTags.length = 0;
    if(famContainer) famContainer.innerHTML = '';
    if(stateContainer) stateContainer.innerHTML = '';

    filterCards(); 

    // Reset Selection
    cards.forEach(c => {
        c.classList.remove('selected');
        c.querySelector('.btn-text').textContent = 'Selecionar';
    });
    
    selectedAnimal = null;
    if(submitButton) {
        submitButton.disabled = true;
        submitButton.style.opacity = '0.6';
        submitButton.style.cursor = 'not-allowed';
    }
  }

  closeButtons.forEach(button => {
    button.addEventListener('click', closeMenu);
  });

  if (submitButton) {
    submitButton.addEventListener('click', () => {
      const location = locationInput.value;
      if (!selectedAnimal || !location) {
        alert('Por favor, selecione um animal da lista.');
        return;
      }
      alert(`SUCESSO!\n\nAlerta registado com sucesso!\n\nAnimal: ${selectedAnimal.name}\nFamília: ${selectedAnimal.family}\nLocalização: ${location}`);
      closeMenu();
    });
  }
}

// Legacy function names for backward compatibility
function initSpeciesPanel() { SpeciesPanel.init(); }
function openSpeciesPanel(details) { SpeciesPanel.open(details); }
function closeSpeciesPanel() { SpeciesPanel.close(); }

// --- FIXED: Account Menu functionality ---
function initAccountMenu() {
  const userIcon = document.getElementById('user-icon');
  const accountMenu = document.getElementById('account-menu');
  if (!userIcon || !accountMenu) return;

  userIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    accountMenu.classList.toggle('show');
  });

  document.addEventListener('click', (e) => {
    if (accountMenu && !accountMenu.contains(e.target) && e.target !== userIcon) {
      accountMenu.classList.remove('show');
    }
  });
}

// --- FIXED: Dynamic header loader ---
const headerTemplate = `
<header class="header">
  <div class="header-content">
    <div class="logo-section">
      <img class="logo-icon" src="img/biomap-icon.png">
      <a href="index.html" class="logo-text">BioMap</a>
    </div>
    <nav class="nav-links">
      <a href="index.html" class="nav-link">Mapa</a>
      <a href="animais.html" class="nav-link">Animais</a>
      <a href="sobre_nos.html" class="nav-link">Sobre nós</a>
      <a href="doar.html" class="nav-link">Doar</a>
    </nav>
    <div class="user-section">
      <i class="fas fa-user user-icon" id="user-icon"></i>
      <div id="account-menu" class="account-menu">
        <div class="account-menu-item" id="menu-login"><a style="text-decoration: none;color: #333;"href="login.html">Iniciar Sessão</a></div>
        <div class="account-menu-separator"></div>
        <div class="account-menu-item" id="menu-create-account"><a style="text-decoration: none;color: #333;font-weight:600;"href="sign_up.html">Criar Conta</a></div>
      </div>
    </div>
  </div>
</header>
`;

async function loadHeader(path = 'header.html') {
    const placeholder = document.getElementById('header-placeholder');
    if (!placeholder) return;
  
    try {
      placeholder.innerHTML = headerTemplate;
    } catch (err) {
      console.warn('loadHeader: failed to set inline template.', err);
    }
  
    if (typeof initAccountMenu === 'function') initAccountMenu();
    if (typeof highlightCurrentPage === 'function') highlightCurrentPage();
}
  
function highlightCurrentPage() {
  const current = (window.location.pathname.split('/').pop() || 'index.html').split('?')[0];
  document.querySelectorAll('.nav-link').forEach(link => {
    const linkHref = (link.getAttribute('href') || '').split('?')[0];
    link.classList.toggle('current', linkHref === current);
  });
}

// ==========================================
// MAP PICKER OVERLAY FUNCTIONALITY
// ==========================================

let mapPicker;
let selectedMarker = null;
let selectedLatLng = null;

// Initialize the map functionality for the picker
function initMapPicker() {
    // Only initialize if the map picker container exists on this page
    const pickerContainer = document.getElementById("map-picker-container");
    if (!pickerContainer) return;

    // Elements
    const locationInput = document.getElementById('location-search');
    const mapOverlay = document.getElementById('map-overlay');
    const closeMapBtn = document.getElementById('close-map-btn');
    const confirmMapBtn = document.getElementById('confirm-map-btn');
    const useMyCoordBtn = document.getElementById('use-my-coord-btn');
    const selectedCoordsDisplay = document.getElementById('selected-coords-display');

    // Default map center (e.g., center of Portugal)
    const defaultCenter = { lat: 39.557191, lng: -7.8536599 };

    // Initialize Google Map
    mapPicker = new google.maps.Map(pickerContainer, {
        center: defaultCenter,
        disableDefaultUI: true,
        zoomControl: true,
        zoom: 7,
        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.RIGHT_BOTTOM,
        }
    });

    // Event listener for map clicks
    mapPicker.addListener("click", (event) => {
        placeMarkerAndSelectedCoords(event.latLng);
    });

    // Event listener to open the map overlay
    if (locationInput) {
        locationInput.addEventListener('click', () => {
            if (mapOverlay) {
                mapOverlay.classList.add('show');
                // Trigger a resize event to ensure map renders correctly after being unhidden
                google.maps.event.trigger(mapPicker, 'resize');
                if (selectedLatLng) {
                    mapPicker.setCenter(selectedLatLng);
                } else {
                    mapPicker.setCenter(defaultCenter);
                }
            }
        });
    }

    // Event listener to close the map overlay
    if (closeMapBtn && mapOverlay) {
        closeMapBtn.addEventListener('click', () => {
            mapOverlay.classList.remove('show');
        });
    }

    // Close overlay when clicking outside the modal content
    if (mapOverlay) {
        mapOverlay.addEventListener('click', (e) => {
            if (e.target === mapOverlay) {
                mapOverlay.classList.remove('show');
            }
        });
    }

    // Event listener for "Use a minha coordenada" button
    if (useMyCoordBtn) {
        useMyCoordBtn.addEventListener('click', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        placeMarkerAndSelectedCoords(pos);
                        mapPicker.setCenter(pos);
                        mapPicker.setZoom(15); // Zoom in on user location
                    },
                    () => {
                        alert("Erro: O serviço de geolocalização falhou.");
                    }
                );
            } else {
                alert("Erro: O seu navegador não suporta geolocalização.");
            }
        });
    }

    // Event listener for "Confirmar" button
    if (confirmMapBtn) {
        confirmMapBtn.addEventListener('click', () => {
            if (selectedLatLng && locationInput) {
                const lat = typeof selectedLatLng.lat === 'function' ? selectedLatLng.lat() : selectedLatLng.lat;
                const lng = typeof selectedLatLng.lng === 'function' ? selectedLatLng.lng() : selectedLatLng.lng;
                
                // Format the coordinates for the input field
                locationInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                
                if (mapOverlay) mapOverlay.classList.remove('show');
            }
        });
    }

    // Helper function to place a marker and update sidebar
    function placeMarkerAndSelectedCoords(latLng) {
        // Remove existing marker if it exists
        if (selectedMarker) {
            selectedMarker.setMap(null);
        }

        // Create a new marker at the clicked location
        selectedMarker = new google.maps.Marker({
            position: latLng,
            map: mapPicker,
            animation: google.maps.Animation.DROP
        });

        // Store the selected coordinates globally
        selectedLatLng = latLng;

        // Update the display in the sidebar
        const lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
        const lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
        
        if (selectedCoordsDisplay) {
            selectedCoordsDisplay.textContent = `lat: ${lat.toFixed(6)} lng: ${lng.toFixed(6)}`;
        }

        // Enable the confirm button
        if (confirmMapBtn) confirmMapBtn.disabled = false;
    }
}

// Expose initMapPicker to the global scope so the Google Maps API callback can find it
window.initMapPicker = initMapPicker;
// === Time picker (improved) ===
(function(){
  let currentTargetBtn = null;

  const displays = document.querySelectorAll('.time-display');
  const overlay = document.getElementById('timePickerOverlay');
  const hourWheel = document.getElementById('hourWheel');
  const minuteWheel = document.getElementById('minuteWheel');
  const hourValue = document.getElementById('hourValue');
  const minuteValue = document.getElementById('minuteValue');
  const confirmBtn = document.getElementById('confirmTimePicker');
  const cancelBtn = document.getElementById('cancelTimePicker');
  const closeBtn = document.querySelector('.close-picker');

  if (!overlay || !hourWheel || !minuteWheel || !confirmBtn || !cancelBtn) return;

  function buildWheel(container, start, end, step = 1) {
    const ul = document.createElement('ul');
    ul.className = 'wheel-list';
    for (let v = start; v <= end; v += step) {
      const li = document.createElement('li');
      li.tabIndex = 0;
      li.textContent = String(v).padStart(2, '0');
      li.addEventListener('click', () => {
        if (container === hourWheel) hourValue.textContent = li.textContent;
        else minuteValue.textContent = li.textContent;

        // mark active
        container.querySelectorAll('li').forEach(n => n.classList.remove('active'));
        li.classList.add('active');
      });
      ul.appendChild(li);
    }
    container.innerHTML = '';
    container.appendChild(ul);
  }

  // Build wheels (hours 0..23, minutes 0..55 step 5)
  buildWheel(hourWheel, 0, 23, 1);
  buildWheel(minuteWheel, 0, 55, 5);

  function openPickerFor(btn) {
    currentTargetBtn = btn;
    const input = document.getElementById(btn.dataset.target);
    const defaultParts = (input && input.value) ? input.value.split(':') : btn.textContent.split(':');
    const h = (defaultParts[0] || '09').padStart(2, '0');
    const m = (defaultParts[1] || '00').padStart(2, '0');
    hourValue.textContent = h;
    minuteValue.textContent = m;

    // mark active items and scroll them into view
    const hourLi = hourWheel.querySelector(`li:nth-child(${parseInt(h,10) + 1})`);
    const minuteLi = [...minuteWheel.querySelectorAll('li')].find(li => li.textContent === m);
    hourWheel.querySelectorAll('li').forEach(n => n.classList.remove('active'));
    minuteWheel.querySelectorAll('li').forEach(n => n.classList.remove('active'));
    if (hourLi) { hourLi.classList.add('active'); hourLi.scrollIntoView({block: 'center'}); }
    if (minuteLi) { minuteLi.classList.add('active'); minuteLi.scrollIntoView({block: 'center'}); }

    // show overlay using CSS class (important — your CSS uses .show). 
    overlay.classList.add('show');
    overlay.setAttribute('aria-hidden', 'false');
  }

  displays.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openPickerFor(btn);
      console.log('Time picker opened for', btn.dataset.target);
    });
  });

  confirmBtn.addEventListener('click', () => {
    if (!currentTargetBtn) return;
    const input = document.getElementById(currentTargetBtn.dataset.target);
    const value = `${hourValue.textContent}:${minuteValue.textContent}`;
    if (input) input.value = value;
    currentTargetBtn.textContent = value;
    overlay.classList.remove('show');
    overlay.setAttribute('aria-hidden', 'true');
    currentTargetBtn = null;
  });

  function closePicker() {
    overlay.classList.remove('show');
    overlay.setAttribute('aria-hidden', 'true');
    currentTargetBtn = null;
  }
  cancelBtn.addEventListener('click', closePicker);
  if (closeBtn) closeBtn.addEventListener('click', closePicker);
  // click outside modal content closes picker
  overlay.addEventListener('click', (e) => { if (e.target === overlay) closePicker(); });
})();
