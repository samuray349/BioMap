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
      console.error('Species panel element not found in DOM');
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
    
    console.log('Panel container found:', this.elements.container);

    // Populate panel with data
    this.populateData(details);

    // Show panel
    console.log('Adding show class and setting styles...');
    this.elements.container.classList.add('show');
    this.elements.container.setAttribute('aria-hidden', 'false');
    
    // Force styles directly via JavaScript since CSS isn't applying
    this.elements.container.style.transform = 'translateX(0)';
    this.elements.container.style.opacity = '1';
    this.elements.container.style.visibility = 'visible';
    this.elements.container.style.pointerEvents = 'auto';
    this.elements.container.style.zIndex = '10000';
    this.elements.container.style.display = 'block';
    this.elements.container.style.position = 'absolute';
    
    // Debug position
    const rect = this.elements.container.getBoundingClientRect();
    console.log('Panel position:', {
      top: rect.top,
      left: rect.left,
      width: rect.width,
      height: rect.height,
      zIndex: window.getComputedStyle(this.elements.container).zIndex
    });
    console.log('Styles set. Transform:', this.elements.container.style.transform);
    console.log('Opacity:', this.elements.container.style.opacity);
    console.log('Visibility:', this.elements.container.style.visibility);
    
    if (this.elements.mainContainer) {
      this.elements.mainContainer.classList.add('detail-panel-open');
    }

    this.isOpen = true;
    console.log('Panel should now be visible. isOpen:', this.isOpen);
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
    
    // Remove inline styles to allow CSS transitions to work on close
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
  const center = { lat: 39.09903420850493, lng: -9.283192320989297 };

  const pawIconSVG = `
<svg width="60" height="80" viewBox="0 0 60 80" xmlns="http://www.w3.org/2000/svg">
  <!-- Fundo do marcador com borda branca -->
  <path d="M 30,0 C 15,0 0,15 0,30 C 0,45 15,60 30,80 C 45,60 60,45 60,30 C 60,15 45,0 30,0 Z"
        fill="#1A8F4A" stroke="white" stroke-width="3"/>

  <!-- Grupo da Pata: Vazado (fill="none") com contorno (stroke="white") -->
  <!-- Aumentei rx/ry para tornar os dedos mais espessos -->
  <g fill="none" stroke="white" stroke-width="3" transform="translate(0, -2)">
    <!-- Dedo 1 (Esquerda Externa) -->
    <ellipse cx="14" cy="28" rx="4.5" ry="5.5" transform="rotate(-40 14 28)" />
    
    <!-- Dedo 2 (Esquerda Interna) -->
    <ellipse cx="24" cy="20" rx="4.5" ry="5.5" transform="rotate(-15 24 20)" />
    
    <!-- Dedo 3 (Direita Interna) -->
    <ellipse cx="36" cy="20" rx="4.5" ry="5.5" transform="rotate(15 36 20)" />
    
    <!-- Dedo 4 (Direita Externa) -->
    <ellipse cx="46" cy="28" rx="4.5" ry="5.5" transform="rotate(40 46 28)" />
    
    <!-- Palma (Formato arredondado clássico) -->
    <path d="M 30 33 
             C 38 33, 44 39, 44 45 
             C 44 51, 38 55, 30 55 
             C 22 55, 16 51, 16 45 
             C 16 39, 22 33, 30 33 Z" />
  </g>
</svg>
`;
const institutionIconSVG = `
<svg xmlns="http://www.w3.org/2000/svg" width="60" height="80" viewBox="0 0 60 80">
  <!-- Fundo do marcador em forma de "gota" com borda branca --><path d="M 30,0 C 15,0 0,15 0,30 C 0,45 15,60 30,80 C 45,60 60,45 60,30 C 60,15 45,0 30,0 Z"
        fill="#8B5B3E" stroke="white" stroke-width="3"/>

  <!-- Ícone de Árvore Vazada (Hollow Tree Icon) --><!-- Usamos um grupo <g> para o posicionamento e o estilo do contorno --><g transform="translate(15, 18)" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
    <!-- Tronco da árvore --><path d="M 20 45 L 20 30" />
    <!-- Base do tronco (chão) --><path d="M 10 45 L 30 45" />
    <!-- Folhagem da árvore (forma orgânica) --><path d="M 20 5 L 10 25 C 5 30, 10 35, 15 35 C 20 35, 25 30, 30 25 C 35 20, 30 15, 20 5 Z" />
  </g>
</svg>
`;

const pawMarkerIcon = {
  url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(pawIconSVG),
  scaledSize: new google.maps.Size(45, 60),
  anchor: new google.maps.Point(22.5, 60) // bottom center
};


  map = new google.maps.Map(document.getElementById("map"), {
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

    const isGreenPaw = loc.title === "Fundação dos Animais";
  
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
      console.log('Marker clicked:', loc.title);
      console.log('Details:', loc.details);
      try {
        SpeciesPanel.open(loc.details || createFallbackDetails(loc));
        console.log('SpeciesPanel.open called successfully');
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
  initTagInputWithDropdown("state-input", "state-tags", "state-dropdown", stateTags, options = stateOptions);

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

    // Add event listeners to remove buttons
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
      // Show all available options when input is empty
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

  // Handle input changes
  input.addEventListener('input', () => {
    renderDropdown();
  });

  // Handle focus
  input.addEventListener('focus', () => {
    renderDropdown();
  });

  // Handle click on wrapper
  wrapper.addEventListener('click', (e) => {
    if (e.target !== input && !e.target.closest('.tag') && !e.target.closest('.tag-container')) {
      input.focus();
      renderDropdown();
    }
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (wrapper && !wrapper.contains(e.target)) {
      dropdown.classList.remove('show');
    }
  });

  // Handle arrow key navigation
  input.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowDown' && dropdown.classList.contains('show')) {
      e.preventDefault();
      const firstItem = dropdown.querySelector('.dropdown-item:not([style*="cursor: default"])');
      if (firstItem) {
        firstItem.focus();
      }
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const firstItem = dropdown.querySelector('.dropdown-item:not([style*="cursor: default"])');
      if (firstItem) {
        firstItem.click();
      }
    } else if (e.key === 'Escape') {
      dropdown.classList.remove('show');
      input.blur();
    }
  });

  // Initial render
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

  // Store mouse position
  mapContainer.addEventListener('mousemove', (e) => {
    const rect = mapContainer.getBoundingClientRect();
    mouseX = e.clientX - rect.left;
    mouseY = e.clientY - rect.top;
  });

  // Handle right-click using Google Maps event
  map.addListener('rightclick', (e) => {
    rightClickPosition = e.latLng; // Save the lat/lng position
    
    // Position menu at stored mouse position
    contextMenu.style.left = (mouseX + 10) + 'px';
    contextMenu.style.top = (mouseY + 10) + 'px';
    contextMenu.classList.add('show');
  });

  // Also handle native contextmenu to prevent default
  mapContainer.addEventListener('contextmenu', (e) => {
    e.preventDefault();
  });

  // Close menu on map click
  map.addListener('click', () => {
    contextMenu.classList.remove('show');
  });

  // Close menu on scroll
  map.addListener('dragstart', () => {
    contextMenu.classList.remove('show');
  });

  // Menu item handlers
  menuAlert.addEventListener('click', () => {
    // Open the new alert menu
    const alertMenu = document.getElementById('alert-animal-menu');
    const locationInput = document.getElementById('alert-animal-location');
    
    if (rightClickPosition) {
      const lat = rightClickPosition.lat().toFixed(6);
      const lng = rightClickPosition.lng().toFixed(6);
      locationInput.value = `${lat}, ${lng}`;
    }
    
    alertMenu.classList.add('show');
    contextMenu.classList.remove('show');
  });

  menuLocation.addEventListener('click', () => {
    // Get location of where right-click happened
    if (rightClickPosition) {
      const lat = rightClickPosition.lat().toFixed(6);
      const lng = rightClickPosition.lng().toFixed(6);
      
      // Copy to clipboard
      navigator.clipboard.writeText(`${lat}, ${lng}`).then(() => {
        alert(`Localização copiada:\nLat: ${lat}\nLng: ${lng}`);
      }).catch(() => {
        // Fallback if clipboard API fails
        prompt('Localização:', `${lat}, ${lng}`);
      });
    }
    contextMenu.classList.remove('show');
  });

  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
    if (contextMenu && !contextMenu.contains(e.target)) {
      contextMenu.classList.remove('show');
    }
  });
}

// --- NEW: Alert Animal Menu functionality ---
function initAlertAnimalMenu() {
  const alertMenu = document.getElementById('alert-animal-menu');
  if (!alertMenu) return;

  const submitButton = document.getElementById('submit-alert-animal');
  const closeButtons = document.querySelectorAll('.close-alert-menu');

  // Function to close the menu
  function closeMenu() {
    alertMenu.classList.remove('show');
    // Clear form fields
    document.getElementById('alert-animal-name').value = '';
    document.getElementById('alert-animal-species').value = '';
    document.getElementById('alert-animal-status').value = '';
    document.getElementById('alert-animal-description').value = '';
    document.getElementById('alert-animal-location').value = '';
  }

  // Add event listeners to all close buttons (X and "Cancelar")
  closeButtons.forEach(button => {
    button.addEventListener('click', closeMenu);
  });

  // Handle submit
  submitButton.addEventListener('click', () => {
    const animalName = document.getElementById('alert-animal-name').value;
    const location = document.getElementById('alert-animal-location').value;

    if (!animalName || !location) {
      alert('Por favor, preencha pelo menos o nome do animal e a localização.');
      return;
    }
    
    alert(`Alerta submetido para:\nAnimal: ${animalName}\nLocalização: ${location}`);
    
    // Close menu after submit
    closeMenu();
  });
}

// Legacy function names for backward compatibility
function initSpeciesPanel() {
  SpeciesPanel.init();
}

function openSpeciesPanel(details) {
  SpeciesPanel.open(details);
}

function closeSpeciesPanel() {
  SpeciesPanel.close();
}


// --- FIXED: Account Menu functionality (Moved to Global Scope) ---
function initAccountMenu() {
  const userIcon = document.getElementById('user-icon');
  const accountMenu = document.getElementById('account-menu');
  
  // Elements might not exist if header hasn't loaded, so check
  if (!userIcon || !accountMenu) {
    // console.warn('Account menu elements not found yet.');
    return;
  }
  
  const menuLogin = document.getElementById('menu-login');
  const menuCreateAccount = document.getElementById('menu-create-account');

  // Toggle menu on user icon click
  userIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    accountMenu.classList.toggle('show');
  });


  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
    // Check if accountMenu exists and if the click is outside
    if (accountMenu && !accountMenu.contains(e.target) && e.target !== userIcon) {
      accountMenu.classList.remove('show');
    }
  });
}

// --- FIXED: Dynamic header loader (Moved to Global Scope) ---
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
    if (!placeholder) {
      console.warn('No #header-placeholder element found.');
      return;
    }
  
    try {
      // Using inline template as a fallback or primary method
      placeholder.innerHTML = headerTemplate;
    } catch (err) {
      console.warn('loadHeader: failed to set inline template. Reason:', err);
    }
  
    // ✅ Reinitialize menu and nav highlight after header is in the DOM
    if (typeof initAccountMenu === 'function') initAccountMenu();
    if (typeof highlightCurrentPage === 'function') highlightCurrentPage();
}
  
// Optional: convenience helper to mark the current nav link
function highlightCurrentPage() {
  const current = (window.location.pathname.split('/').pop() || 'index.html').split('?')[0];
  document.querySelectorAll('.nav-link').forEach(link => {
    const linkHref = (link.getAttribute('href') || '').split('?')[0];
    link.classList.toggle('current', linkHref === current);
  });
}