console.log('📜 script.js LOADED - Version with instituições support');
let map;
let familyTags = [];
let stateTags = [];
let rightClickPosition = null; // Store this globally to pass to the alert menu
let mapMarkers = []; // Store all map markers for dynamic updates
let instituicaoMarkers = []; // Store all instituição markers for dynamic updates
let pawMarkerIcon = null; // Will be initialized in initMap() after Google Maps API loads
let houseMarkerIcon = null; // Will be initialized in initMap() after Google Maps API loads
let currentZoomLevel = 12; // Track current zoom level

// ==========================================
// GENERALIZED UTILITY FUNCTIONS
// ==========================================

/**
 * Get API URL with fallback
 * @param {string} endpoint - API endpoint path
 * @returns {string} Full API URL
 */
function getApiUrl(endpoint) {
    return window.API_CONFIG?.getUrl(endpoint) || `/${endpoint}`;
}

/**
 * Get current user from session (tries SessionHelper first, then localStorage)
 * @returns {Object|null} User object or null if not logged in
 */
function getCurrentUser() {
    try {
        // Try SessionHelper first (cookie-based)
        if (typeof SessionHelper !== 'undefined' && SessionHelper.getCurrentUser) {
            const user = SessionHelper.getCurrentUser();
            if (user) return user;
        }
        
        // Fallback to localStorage
        const userData = localStorage.getItem('biomapUser');
        if (userData) {
            return JSON.parse(userData);
        }
    } catch (error) {
        console.error('Error getting current user:', error);
    }
    return null;
}

/**
 * Check if user is logged in
 * @returns {boolean} True if user is logged in
 */
function isLoggedIn() {
    const user = getCurrentUser();
    return user !== null && user.id != null;
}

/**
 * Check if current user is an admin
 * @returns {boolean} True if user is admin (funcao_id === 1)
 */
function isAdmin() {
    const user = getCurrentUser();
    return user !== null && Number(user.funcao_id) === 1;
}

/**
 * Check if current user matches a specific user ID
 * @param {number} userId - User ID to check against
 * @returns {boolean} True if current user matches the given ID
 */
function isCurrentUser(userId) {
    const user = getCurrentUser();
    return user !== null && Number(user.id) === Number(userId);
}

/**
 * Fetch all animal families from the API
 * @returns {Promise<Array<string>>} Array of family names
 */
async function fetchFamilyOptions() {
    try {
        const apiUrl = getApiUrl('animais/familias');
        const response = await fetch(apiUrl);
        const families = await response.json();
        return families.map(f => f.nome_familia);
    } catch (error) {
        console.error('Erro ao buscar famílias:', error);
        return [];
    }
}

/**
 * Fetch all conservation status options from the API
 * @returns {Promise<Array<string>>} Array of conservation status names
 */
async function fetchStateOptions() {
    try {
        const apiUrl = getApiUrl('animais/estados');
        const response = await fetch(apiUrl);
        const states = await response.json();
        return states.map(s => s.nome_estado);
    } catch (error) {
        console.error('Erro ao buscar estados de conservação:', error);
        return [];
    }
}

// ==========================================
// NOTIFICATION SYSTEM
// ==========================================
function showNotification(message, type = 'success') {
  const container = document.getElementById('notification-container');
  if (!container) return;

  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  
  // Add icon based on type
  let icon = '';
  if (type === 'success') {
    icon = '<i class="fas fa-check-circle"></i>';
  } else if (type === 'error') {
    icon = '<i class="fas fa-exclamation-circle"></i>';
  } else if (type === 'info') {
    icon = '<i class="fas fa-info-circle"></i>';
  }
  
  notification.innerHTML = `
    <div class="notification-content">
      ${icon}
      <span class="notification-message">${message}</span>
    </div>
  `;

  // Insert at the beginning so newest notifications appear at top
  if (container.firstChild) {
    container.insertBefore(notification, container.firstChild);
  } else {
    container.appendChild(notification);
  }

  // Trigger animation
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);

  // Remove after 3 seconds
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

// Species Panel Manager
const SpeciesPanel = {
  isInitialized: false,
  isOpen: false,
  
  elements: {
    container: null,
    image: null,
    imageContainer: null,
    name: null,
    scientificName: null,
    family: null,
    diet: null,
    description: null,
    alertDate: null,
    location: null,
    closeButton: null,
    mainContainer: null,
    badge: null,
    menu: null,
    menuToggle: null,
    menuDropdown: null,
    deleteButton: null
  },

  init() {
    if (this.isInitialized) {
      return;
    }
    
    // Get all panel elements
    this.elements.container = document.getElementById('species-panel');
    if (!this.elements.container) {
      // If we are not on the map page, this is expected
      return;
    }
    console.log('Species panel element found:', this.elements.container);

    this.elements.image = document.getElementById('species-panel-image');
    this.elements.imageContainer = this.elements.container?.querySelector('.species-panel__image');
    this.elements.name = document.getElementById('species-panel-name');
    this.elements.scientificName = document.getElementById('species-panel-scientific');
    this.elements.family = document.getElementById('species-panel-family');
    this.elements.diet = document.getElementById('species-panel-diet');
    this.elements.description = document.getElementById('species-panel-description');
    this.elements.alertDate = document.getElementById('species-panel-alert-date');
    this.elements.location = document.getElementById('species-panel-location');
    this.elements.closeButton = document.getElementById('species-panel-close');
    this.elements.mainContainer = document.querySelector('.main-container');
    this.elements.badge = this.elements.container?.querySelector('.badge');
    this.elements.menu = document.getElementById('species-panel-menu');
    this.elements.menuToggle = document.getElementById('species-panel-menu-toggle');
    this.elements.menuDropdown = document.getElementById('species-panel-menu-dropdown');
    this.elements.deleteButton = document.getElementById('species-panel-delete');

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

    // Menu toggle
    if (this.elements.menuToggle) {
      this.elements.menuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const isVisible = this.elements.menuDropdown.style.display === 'block';
        this.elements.menuDropdown.style.display = isVisible ? 'none' : 'block';
      });
    }

    // Delete button
    if (this.elements.deleteButton) {
      this.elements.deleteButton.addEventListener('click', (e) => {
        e.stopPropagation();
        this.deleteAvistamento();
      });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (this.elements.menuDropdown && 
          !this.elements.menu.contains(e.target) && 
          this.elements.menuDropdown.style.display === 'block') {
        this.elements.menuDropdown.style.display = 'none';
      }
    });

    // Click outside to close - close when clicking anywhere outside the panel
    document.addEventListener('click', (e) => {
      if (!this.isOpen) return;
      // Don't close if clicking inside the panel
      if (this.elements.container.contains(e.target)) return;
      // Don't close if clicking on sidebar
      if (e.target.closest('.sidebar')) return;
      // Close for all other clicks (including markers and map)
      this.close();
    });
  },

  async deleteAvistamento() {
    if (!this.currentAvistamentoId) {
      console.error('No avistamento ID available');
      return;
    }

    // Get current user
    const user = getCurrentUser();
    
    if (!isLoggedIn()) {
      alert('Por favor, inicie sessão para eliminar avistamentos.');
      return;
    }

    // Confirm deletion
    if (!confirm('Tem a certeza que deseja eliminar este avistamento?')) {
      return;
    }

    try {
      const apiUrl = getApiUrl(`api/alerts/${this.currentAvistamentoId}`);
      const response = await fetch(apiUrl, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          utilizador_id: user.id,
          funcao_id: user.funcao_id
        })
      });

      let result;
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        result = await response.json();
      } else {
        const text = await response.text();
        throw new Error(text || `Erro ${response.status}: ${response.statusText}`);
      }

      if (!response.ok) {
        throw new Error(result?.error || 'Erro ao eliminar avistamento.');
      }

      // Show success notification
      showNotification('Avistamento eliminado com sucesso!', 'success');

      // Close panel and reload avistamentos
      this.close();
      if (typeof loadAvistamentos === 'function') {
        loadAvistamentos();
      }
    } catch (error) {
      console.error('Erro ao eliminar avistamento:', error);
      alert(error?.message || 'Erro ao eliminar avistamento. Por favor, tente novamente.');
    }
  },

  open(details) {
    if (!this.isInitialized) {
      this.init();
    }
    
    if (!this.elements.container) {
      console.error('Cannot open panel: container not found');
      return;
    }
    
    // If panel is already open, we can still update it with new data
    // Set isOpen to false temporarily to prevent the click listener from interfering
    const wasOpen = this.isOpen;
    this.isOpen = false;
    
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

    // Set isOpen after a short delay to prevent the document click listener 
    // from closing the panel immediately in the same event loop.
    setTimeout(() => {
      this.isOpen = true;
    }, 50);
  },

  populateData(details) {
    if (!details) return;
    

    // Image - make it clickable if animal_id exists
    // First, ensure we have the image container
    if (!this.elements.imageContainer) {
      this.elements.imageContainer = this.elements.container?.querySelector('.species-panel__image');
    }
    
    if (this.elements.imageContainer && details.image) {
      if (details.animal_id) {
        const animalUrl = `animal_desc.php?id=${details.animal_id}`;
        // Wrap the image in a link by replacing the container's content
        this.elements.imageContainer.innerHTML = `
          <a href="${animalUrl}" style="display: block; width: 100%; height: 100%; text-decoration: none; cursor: pointer;">
            <img id="species-panel-image" src="${details.image}" alt="${details.name || 'Animal'}" style="width: 100%; height: 100%; object-fit: cover; display: block;" onerror="this.src='img/placeholder.jpg'">
          </a>
        `;
        // Re-get the image element after innerHTML change
        this.elements.image = document.getElementById('species-panel-image');
      } else {
        // No animal_id, just set the image normally
        this.elements.imageContainer.innerHTML = `
          <img id="species-panel-image" src="${details.image}" alt="${details.name || 'Animal'}" style="width: 100%; height: 100%; object-fit: cover; display: block;" onerror="this.src='img/placeholder.jpg'">
        `;
        this.elements.image = document.getElementById('species-panel-image');
      }
    } else if (this.elements.image && details.image) {
      // Fallback if container doesn't exist
      this.elements.image.src = details.image;
      this.elements.image.alt = details.name || 'Animal';
      if (details.animal_id) {
        const animalUrl = `animal_desc.php?id=${details.animal_id}`;
        // Wrap in link
        const link = document.createElement('a');
        link.href = animalUrl;
        link.style.cssText = 'display: block; text-decoration: none; cursor: pointer;';
        const parent = this.elements.image.parentNode;
        parent.insertBefore(link, this.elements.image);
        link.appendChild(this.elements.image);
      }
    }

    // Text content - make name clickable if animal_id exists
    // Re-fetch the element to ensure we have the latest reference
    const nameElement = document.getElementById('species-panel-name');
    
    if (nameElement) {
      if (details.animal_id) {
        // Create a link wrapper for the name
        const animalUrl = `animal_desc.php?id=${details.animal_id}`;
        const nameText = details.name || 'Animal sem nome';
        // Replace the h2's content with a link - use innerHTML to ensure it's set
        const linkHTML = `<a href="${animalUrl}" style="color: inherit !important; text-decoration: none !important; cursor: pointer !important; display: block !important; width: 100% !important;">${nameText}</a>`;
        nameElement.innerHTML = linkHTML;
        
        // Update the reference
        this.elements.name = nameElement;
      } else {
        nameElement.textContent = details.name || 'Animal sem nome';
      }
    }
    // Handle instituição vs animal data
    if (details.isInstituicao) {
      // For instituições, show instituição-specific fields
      if (this.elements.scientificName) {
        const parentRow = this.elements.scientificName.closest('.species-panel__info-row');
        if (parentRow) {
          const icon = parentRow.querySelector('i');
          const label = parentRow.querySelector('.label');
          if (icon) icon.className = 'fas fa-map-marker-alt';
          if (label) label.textContent = 'Localização:';
          this.elements.scientificName.textContent = details.localizacao_texto || '—';
        }
      }
      if (this.elements.family) {
        const parentRow = this.elements.family.closest('.species-panel__info-row');
        if (parentRow) {
          const icon = parentRow.querySelector('i');
          const label = parentRow.querySelector('.label');
          if (icon) icon.className = 'fas fa-phone';
          if (label) label.textContent = 'Contacto:';
          this.elements.family.textContent = details.telefone_contacto || '—';
        }
      }
      if (this.elements.diet) {
        const parentRow = this.elements.diet.closest('.species-panel__info-row');
        if (parentRow) {
          const icon = parentRow.querySelector('i');
          const label = parentRow.querySelector('.label');
          if (icon) icon.className = 'fas fa-calendar';
          if (label) label.textContent = 'Dias abertos:';
          this.elements.diet.textContent = details.dias_aberto || '—';
        }
      }
      if (this.elements.alertDate) {
        const parentRow = this.elements.alertDate.closest('.species-panel__info-row');
        if (parentRow) {
          const icon = parentRow.querySelector('i');
          const label = parentRow.querySelector('.label');
          if (icon) icon.className = 'fas fa-clock';
          if (label) label.textContent = 'Horário:';
          const horario = details.hora_abertura && details.hora_fecho 
            ? `${details.hora_abertura} - ${details.hora_fecho}`
            : '—';
          this.elements.alertDate.textContent = horario;
        }
      }
      if (this.elements.description) {
        this.elements.description.textContent = details.description || '';
      }
      // Hide badge for instituições
      if (this.elements.badge) {
        this.elements.badge.textContent = '';
        this.elements.badge.style.display = 'none';
      }
    } else {
      // For animals, show animal-specific fields
      if (this.elements.scientificName) {
        const parentRow = this.elements.scientificName.closest('.species-panel__info-row');
        if (parentRow) {
          const icon = parentRow.querySelector('i');
          const label = parentRow.querySelector('.label');
          if (icon) icon.className = 'fas fa-info-circle';
          if (label) label.textContent = 'Nome científico:';
          this.elements.scientificName.textContent = details.scientificName || '—';
        }
      }
      if (this.elements.family) {
        const parentRow = this.elements.family.closest('.species-panel__info-row');
        if (parentRow) {
          const icon = parentRow.querySelector('i');
          const label = parentRow.querySelector('.label');
          if (icon) icon.className = 'fas fa-users';
          if (label) label.textContent = 'Família:';
          this.elements.family.textContent = details.family || '—';
        }
      }
      if (this.elements.diet) {
        const parentRow = this.elements.diet.closest('.species-panel__info-row');
        if (parentRow) {
          const icon = parentRow.querySelector('i');
          const label = parentRow.querySelector('.label');
          if (icon) icon.className = 'fas fa-drumstick-bite';
          if (label) label.textContent = 'Dieta:';
          this.elements.diet.textContent = details.diet || '—';
        }
      }
      if (this.elements.description) {
        this.elements.description.textContent = details.description || '';
      }
      if (this.elements.alertDate) {
        const parentRow = this.elements.alertDate.closest('.species-panel__info-row');
        if (parentRow) {
          const icon = parentRow.querySelector('i');
          const label = parentRow.querySelector('.label');
          if (icon) icon.className = 'far fa-clock';
          if (label) label.textContent = 'Data do avistamento:';
          this.elements.alertDate.textContent = details.alertDate || '—';
        }
      }
      // Show badge for animals
      if (this.elements.badge && details.estado) {
        this.elements.badge.style.display = '';
        this.elements.badge.textContent = details.estado;
        if (details.estadoCor) {
          this.elements.badge.style.backgroundColor = details.estadoCor;
        }
      }
    }

    // Location
    if (this.elements.location) {
      const coords = details.coordinates || {};
      if (typeof coords.lat === 'number' && typeof coords.lng === 'number') {
        this.elements.location.textContent = `${coords.lat.toFixed(3)}, ${coords.lng.toFixed(3)}`;
      } else if (details.location) {
        this.elements.location.textContent = details.location;
      } else {
        this.elements.location.textContent = '—';
      }
    }

    // Conservation status badge
    if (this.elements.badge && details.estado) {
      this.elements.badge.textContent = details.estado;
      // Update badge color if estadoCor is provided
      if (details.estadoCor) {
        this.elements.badge.style.backgroundColor = details.estadoCor;
      }
    }

    // Show/hide menu based on permissions
    this.updateMenuVisibility(details);
    
    // Store avistamento ID for deletion
    this.currentAvistamentoId = details.avistamento_id;
    this.currentAvistamentoCreatorId = details.utilizador_id;
  },

  updateMenuVisibility(details) {
    if (!this.elements.menu) return;

    // For instituições, don't show the menu (no delete functionality for instituições on map)
    if (details.isInstituicao) {
      this.elements.menu.style.display = 'none';
      // Close dropdown if open
      if (this.elements.menuDropdown) {
        this.elements.menuDropdown.style.display = 'none';
      }
      return;
    }

    // Show menu only if:
    // 1. User is logged in
    // 2. User is admin (funcao_id === 1) OR user is the creator of the avistamento
    const canManage = isAdmin() || isCurrentUser(details.utilizador_id);

    if (canManage) {
      this.elements.menu.style.display = 'block';
    } else {
      this.elements.menu.style.display = 'none';
      // Close dropdown if open
      if (this.elements.menuDropdown) {
        this.elements.menuDropdown.style.display = 'none';
      }
    }
  },

  // Helper function to convert hex color to RGB
  hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
      r: parseInt(result[1], 16),
      g: parseInt(result[2], 16),
      b: parseInt(result[3], 16)
    } : null;
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
  console.log('🚀🚀🚀 INITMAP CALLED 🚀🚀🚀');
  console.log('initMap: Function called');
  const mapElement = document.getElementById("map");
  if (!mapElement) {
    console.warn('initMap: map element not found');
    return;
  }
  console.log('initMap: Map element found, initializing...');

  const center = { lat: 39.09903420850493, lng: -9.283192320989297 };

  // Initialize paw marker icon - Font Awesome style paw print
  const pawIconSVG = `<svg width="60" height="80" viewBox="0 0 60 80" xmlns="http://www.w3.org/2000/svg"><path d="M 30,0 C 15,0 0,15 0,30 C 0,45 15,60 30,80 C 45,60 60,45 60,30 C 60,15 45,0 30,0 Z" fill="#1A8F4A" stroke="white" stroke-width="3"/><g fill="white" transform="translate(30, 40)"><circle cx="-10" cy="-8" r="4"/><circle cx="0" cy="-12" r="4"/><circle cx="10" cy="-8" r="4"/><circle cx="-6" cy="0" r="4"/><circle cx="6" cy="0" r="4"/><ellipse cx="0" cy="8" rx="8" ry="6"/></g></svg>`;

  // Function to get scaled icon size based on zoom level
  function getScaledIconSize(zoom) {
    // Base size at zoom level 12
    const baseZoom = 12;
    const baseWidth = 45;
    const baseHeight = 60;
    
    // Scale factor: smaller icons when zoomed out, larger when zoomed in
    // Use 1.15 for more subtle scaling
    const clampedZoom = Math.max(8, Math.min(18, zoom));
    const scale = Math.pow(1.15, clampedZoom - baseZoom);
    
    const width = baseWidth * scale;
    const height = baseHeight * scale;
    
    // Label position: slightly above the marker, scaling with marker size
    const labelYOffset = -15 * scale; // Scale the gap with the marker size
    
  return {
    scale: scale,
    scaledSize: new google.maps.Size(width, height),
    anchor: new google.maps.Point(width / 2, height),
    labelOrigin: new google.maps.Point(width / 2, labelYOffset)
  };
  }

  // Initialize icon with default size
  const iconSize = getScaledIconSize(12);
  pawMarkerIcon = {
    url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(pawIconSVG),
    scaledSize: iconSize.scaledSize,
    anchor: iconSize.anchor,
    labelOrigin: iconSize.labelOrigin
  };

  // Initialize house marker icon using Font Awesome house icon (simple outline style)
  // Font Awesome fa-house icon path - simple outline house, positioned higher, with door
  const houseIconSVG = `<svg width="60" height="80" viewBox="0 0 60 80" xmlns="http://www.w3.org/2000/svg"><path d="M 30,0 C 15,0 0,15 0,30 C 0,45 15,60 30,80 C 45,60 60,45 60,30 C 60,15 45,0 30,0 Z" fill="#8B4513" stroke="white" stroke-width="3"/><g fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" transform="translate(30, 32) scale(0.9)"><path d="M-12 -8L0 -18L12 -8V6C12 8 10 10 8 10H-8C-10 10 -12 8 -12 6V-8Z"/><rect x="-4" y="2" width="8" height="6" fill="none" stroke="white" stroke-width="2.5"/></g></svg>`;
  houseMarkerIcon = {
    url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(houseIconSVG),
    scaledSize: iconSize.scaledSize,
    anchor: iconSize.anchor,
    labelOrigin: iconSize.labelOrigin
  };

  map = new google.maps.Map(mapElement, {
    zoom: 12,
    center,
    disableDefaultUI: true,
    zoomControl: true,
    mapId: "DEMO_MAP_ID",
  });

  // Add zoom change listener to update marker sizes
  map.addListener('zoom_changed', () => {
    const newZoom = map.getZoom();
    if (newZoom !== currentZoomLevel) {
      currentZoomLevel = newZoom;
      updateMarkerSizes(newZoom);
    }
  });

  // Load and display avistamentos dynamically
  loadAvistamentos();
  
  // Load and display instituições dynamically (ensure icon is ready)
  // houseMarkerIcon is initialized just before this, so it should be ready
  console.log('initMap: About to call loadInstituicoes', { 
    houseMarkerIcon: !!houseMarkerIcon,
    houseMarkerIconValue: houseMarkerIcon,
    map: !!map 
  });
  if (houseMarkerIcon) {
    console.log('initMap: houseMarkerIcon is ready, calling loadInstituicoes NOW');
    loadInstituicoes();
  } else {
    console.error('initMap: houseMarkerIcon NOT ready!', houseMarkerIcon);
    console.warn('initMap: Will retry loadInstituicoes in 200ms');
    setTimeout(() => {
      console.log('initMap: Retry check - houseMarkerIcon:', !!houseMarkerIcon);
      if (houseMarkerIcon) {
        console.log('initMap: houseMarkerIcon ready after retry, calling loadInstituicoes');
        loadInstituicoes();
      } else {
        console.error('initMap: houseMarkerIcon still not ready after retry');
      }
    }, 200);
  }

  // Fetch filter options from API and initialize dropdowns
  (async () => {
    try {
      const [familyOptions, stateOptions] = await Promise.all([
        fetchFamilyOptions(),
        fetchStateOptions()
      ]);

      initTagInputWithDropdown("family-input", "family-tags", "family-dropdown", familyTags, familyOptions);
      initTagInputWithDropdown("state-input", "state-tags", "state-dropdown", stateTags, stateOptions);
    } catch (error) {
      console.error('Erro ao carregar opções de filtros:', error);
      // Fallback to empty arrays if API fails
      initTagInputWithDropdown("family-input", "family-tags", "family-dropdown", familyTags, []);
      initTagInputWithDropdown("state-input", "state-tags", "state-dropdown", stateTags, []);
    }
  })();

  // Set up filter change listeners to update map markers
  const searchInput = document.getElementById('search-input');
  if (searchInput) {
    searchInput.addEventListener('input', debounce(() => {
      loadAvistamentos();
      loadInstituicoes();
    }, 300));
  }

  // Observe changes to tag containers
  const familyTagsContainer = document.getElementById('family-tags');
  const stateTagsContainer = document.getElementById('state-tags');
  
  if (familyTagsContainer) {
    const familyObserver = new MutationObserver(debounce(loadAvistamentos, 300));
    familyObserver.observe(familyTagsContainer, { childList: true });
  }
  
  if (stateTagsContainer) {
    const stateObserver = new MutationObserver(debounce(loadAvistamentos, 300));
    stateObserver.observe(stateTagsContainer, { childList: true });
  }

  // Clear filters button
  const sidebarClearFiltersBtn = document.getElementById('sidebar-clear-filters-btn');
  if (sidebarClearFiltersBtn && typeof clearAnimalFilters === 'function') {
    sidebarClearFiltersBtn.addEventListener('click', () => {
      clearAnimalFilters({
        searchInput: searchInput, // Pass the actual element
        familyTagsArray: familyTags,
        stateTagsArray: stateTags,
        familyTagsId: 'family-tags',
        stateTagsId: 'state-tags',
        familyInputId: 'family-input',
        stateInputId: 'state-input'
      });
      // Reload avistamentos and instituições after clearing filters
      loadAvistamentos();
      loadInstituicoes();
    });
  }

  initContextMenu();
  initAlertAnimalMenu();
  SpeciesPanel.init();
}

// Debounce helper function
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Update marker sizes based on zoom level
function updateMarkerSizes(zoom) {
  if ((!mapMarkers || mapMarkers.length === 0) && (!instituicaoMarkers || instituicaoMarkers.length === 0)) return;
  
  // Calculate new icon size with more gradual scaling
  const baseZoom = 12;
  const baseWidth = 45;
  const baseHeight = 60;
  const baseFontSize = 14;
  const clampedZoom = Math.max(8, Math.min(18, zoom));
  // Use 1.15 for more subtle scaling
  const scale = Math.pow(1.15, clampedZoom - baseZoom);
  const width = baseWidth * scale;
  const height = baseHeight * scale;
  const fontSize = Math.round(baseFontSize * scale);
  
  // Label position: slightly above the marker, scaling with marker size
  const labelYOffset = -15 * scale; // Scale the gap with the marker size
  
  // Get the current paw icon SVG
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
  
  // Update the global icon with proper label origin
  pawMarkerIcon = {
    url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(pawIconSVG),
    scaledSize: new google.maps.Size(width, height),
    anchor: new google.maps.Point(width / 2, height),
    labelOrigin: new google.maps.Point(width / 2, labelYOffset)
  };
  
  // Get the current house icon SVG (Font Awesome style - simple outline with door)
  const houseIconSVG = `<svg width="60" height="80" viewBox="0 0 60 80" xmlns="http://www.w3.org/2000/svg"><path d="M 30,0 C 15,0 0,15 0,30 C 0,45 15,60 30,80 C 45,60 60,45 60,30 C 60,15 45,0 30,0 Z" fill="#8B4513" stroke="white" stroke-width="3"/><g fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" transform="translate(30, 32) scale(0.9)"><path d="M-12 -8L0 -18L12 -8V6C12 8 10 10 8 10H-8C-10 10 -12 8 -12 6V-8Z"/><rect x="-4" y="2" width="8" height="6" fill="none" stroke="white" stroke-width="2.5"/></g></svg>`;
  
  // Update the global house icon with proper label origin
  houseMarkerIcon = {
    url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(houseIconSVG),
    scaledSize: new google.maps.Size(width, height),
    anchor: new google.maps.Point(width / 2, height),
    labelOrigin: new google.maps.Point(width / 2, labelYOffset)
  };
  
  // Update all existing animal markers with new icon and label size
  if (mapMarkers && mapMarkers.length > 0) {
    mapMarkers.forEach(marker => {
      marker.setIcon(pawMarkerIcon);
      
      // Update label with new font size
      const currentLabel = marker.getLabel();
      if (currentLabel) {
        marker.setLabel({
          text: currentLabel.text || currentLabel,
          className: "marker-label",
          fontSize: `${fontSize}px`,
          fontWeight: "600"
        });
      }
    });
  }
  
  // Update all existing instituição markers with new icon and label size
  if (instituicaoMarkers && instituicaoMarkers.length > 0) {
    instituicaoMarkers.forEach(marker => {
      marker.setIcon(houseMarkerIcon);
      
      // Update label with new font size
      const currentLabel = marker.getLabel();
      if (currentLabel) {
        marker.setLabel({
          text: currentLabel.text || currentLabel,
          className: "marker-label",
          fontSize: `${fontSize}px`,
          fontWeight: "600"
        });
      }
    });
  }
  }

// Fetch and display avistamentos on the map
async function loadAvistamentos() {
  if (!map || !pawMarkerIcon) return;

  try {
    // Get current filter values
    const filters = getAnimalFilters({
      searchInput: document.getElementById('search-input'),
      familyTagsArray: familyTags,
      stateTagsArray: stateTags
    });

    // Build query parameters
    const params = new URLSearchParams();
    if (filters.search) {
      params.append('search', filters.search);
    }
    if (filters.families && filters.families.length > 0) {
      params.append('families', filters.families.join(','));
    }
    if (filters.states && filters.states.length > 0) {
      params.append('states', filters.states.join(','));
    }

    // Fetch avistamentos from API
    const apiUrl = getApiUrl(`api/alerts?${params.toString()}`);
    const response = await fetch(apiUrl);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const avistamentos = await response.json();

    // Create new markers first (but don't add to map yet to avoid flicker)
    const newMarkers = [];
    const markerDataMap = new Map(); // Store marker data for click handlers

    avistamentos.forEach(avistamento => {
      const position = {
        lat: parseFloat(avistamento.latitude),
        lng: parseFloat(avistamento.longitude)
      };

      if (isNaN(position.lat) || isNaN(position.lng)) {
        console.warn('Invalid coordinates for avistamento:', avistamento);
        return;
      }

      
      // Format date for display
      const alertDate = new Date(avistamento.data_avistamento);
      const formattedDate = alertDate.toLocaleDateString('pt-PT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });

      // Format location coordinates
      const locationText = `${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}`;

      // Create details object for SpeciesPanel
      const details = {
        avistamento_id: avistamento.avistamento_id,
        animal_id: avistamento.animal_id,
        utilizador_id: avistamento.utilizador_id,
        name: avistamento.nome_comum,
        scientificName: avistamento.nome_cientifico || '—',
        family: avistamento.nome_familia || '—',
        diet: avistamento.nome_dieta || '—',
        description: avistamento.descricao || '—',
        alertDate: formattedDate,
        coordinates: position,
        location: locationText,
        image: avistamento.url_imagem || 'img/placeholder.jpg',
        estado: avistamento.nome_estado || '—',
        estadoCor: avistamento.estado_cor || '#666'
      };

      // Calculate label font size based on current zoom
      const baseZoom = 12;
      const baseFontSize = 14;
      const clampedZoom = Math.max(8, Math.min(18, currentZoomLevel));
      const scale = Math.pow(1.15, clampedZoom - baseZoom);
      const fontSize = Math.round(baseFontSize * scale);
      
      const marker = new google.maps.Marker({
        position: position,
        map: null, // Don't add to map yet
        icon: pawMarkerIcon,
        label: {
          text: avistamento.nome_comum,
          className: "marker-label",
          fontSize: `${fontSize}px`,
          fontWeight: "600"
        },
        title: avistamento.nome_comum
      });

      // Store marker and its data
      markerDataMap.set(marker, details);
      newMarkers.push(marker);
    });

    // Now update markers with smooth transitions
    // First, fade out old markers
    mapMarkers.forEach(marker => {
      const labelDiv = marker.getLabel()?.element;
      if (labelDiv) {
        labelDiv.classList.add('marker-fade-out');
      }
      // Remove marker after fade out animation
      setTimeout(() => {
        marker.setMap(null);
      }, 300);
    });
    
    // Then add new markers with fade in animation
    setTimeout(() => {
      newMarkers.forEach((marker, index) => {
        // Stagger the appearance slightly for a smoother effect
        setTimeout(() => {
          marker.setMap(map);
          
          // Attach click handler
          const details = markerDataMap.get(marker);
          if (details) {
            marker.addListener("click", (e) => {
              try {
                // If panel is already open, just update the content without closing
                if (SpeciesPanel.isOpen) {
                  SpeciesPanel.populateData(details);
                } else {
                  SpeciesPanel.open(details);
                }
              } catch (error) {
                console.error('Error opening panel:', error);
              }
            });
          }
        }, index * 20); // 20ms delay between each marker
      });
      
      // Update the markers array after a short delay
      setTimeout(() => {
        mapMarkers = newMarkers;
      }, newMarkers.length * 20 + 100);
    }, 300);
  } catch (error) {
    console.error('Erro ao carregar avistamentos:', error);
  }
}

async function loadInstituicoes() {
  console.log('=== loadInstituicoes: FUNCTION CALLED ===', { 
    map: !!map, 
    houseMarkerIcon: !!houseMarkerIcon,
    mapType: typeof map,
    iconType: typeof houseMarkerIcon
  });
  
  if (!map) {
    console.error('loadInstituicoes: map not initialized - ABORTING');
    return;
  }
  if (!houseMarkerIcon) {
    console.error('loadInstituicoes: houseMarkerIcon not initialized - ABORTING');
    console.error('loadInstituicoes: houseMarkerIcon value:', houseMarkerIcon);
    return;
  }
  
  console.log('loadInstituicoes: Both map and houseMarkerIcon are ready, proceeding to API call...');

  try {
    console.log('loadInstituicoes: Starting to load instituições...');
    // Get current search filter
    const searchInput = document.getElementById('search-input');
    const searchTerm = searchInput ? searchInput.value.trim() : '';
    
    // Build query parameters
    const params = new URLSearchParams();
    if (searchTerm) {
      params.append('search', searchTerm);
    }

    // Fetch instituições from API
    const apiUrl = getApiUrl(`instituicoes?${params.toString()}`);
    console.log('loadInstituicoes: Fetching from', apiUrl);
    const response = await fetch(apiUrl);
    if (!response.ok) {
      console.error('loadInstituicoes: HTTP error! status:', response.status);
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const instituicoes = await response.json();
    console.log('loadInstituicoes: Received', instituicoes.length, 'instituições');
    console.log('loadInstituicoes: Sample instituição data:', instituicoes[0]);

    // Create new markers first (but don't add to map yet to avoid flicker)
    const newMarkers = [];
    const markerDataMap = new Map(); // Store marker data for click handlers

    if (!instituicoes || instituicoes.length === 0) {
      console.log('loadInstituicoes: No instituições found');
      return;
    }

    instituicoes.forEach((instituicao, index) => {
      // Extract coordinates from API (same format as avistamentos)
      const lat = parseFloat(instituicao.latitude);
      const lng = parseFloat(instituicao.longitude);
      
      const position = {
        lat: lat,
        lng: lng
      };

      console.log(`loadInstituicoes: Processing instituição ${index + 1}/${instituicoes.length}:`, {
        nome: instituicao.nome,
        latitude: instituicao.latitude,
        longitude: instituicao.longitude,
        parsed_lat: lat,
        parsed_lng: lng
      });

      if (isNaN(position.lat) || isNaN(position.lng)) {
        console.warn('Invalid coordinates for instituição:', instituicao.nome, 'lat:', lat, 'lng:', lng, 'raw data:', instituicao);
        return;
      }

      // Validate coordinates are within reasonable bounds (Portugal is roughly 36-42°N, 6-10°W)
      if (position.lat < 30 || position.lat > 45 || position.lng < -15 || position.lng > 0) {
        console.warn('Coordinates out of bounds for instituição:', instituicao.nome, 'lat:', position.lat, 'lng:', position.lng);
        // Don't return - still try to show it, but log the warning
      }

      // Format location coordinates
      const locationText = `${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}`;
      
      console.log(`loadInstituicoes: Creating marker for ${instituicao.nome} at (${position.lat}, ${position.lng})`);

      // Create details object for SpeciesPanel
      const details = {
        instituicao_id: instituicao.instituicao_id,
        name: instituicao.nome,
        description: instituicao.descricao || '—',
        localizacao_texto: instituicao.localizacao_texto || '—',
        telefone_contacto: instituicao.telefone_contacto || '—',
        dias_aberto: instituicao.dias_aberto || '—',
        hora_abertura: instituicao.hora_abertura || '—',
        hora_fecho: instituicao.hora_fecho || '—',
        coordinates: position,
        location: locationText,
        image: instituicao.url_imagem || 'img/placeholder.jpg',
        isInstituicao: true // Flag to identify this as an instituição
      };

      // Calculate label font size based on current zoom
      const baseZoom = 12;
      const baseFontSize = 14;
      const clampedZoom = Math.max(8, Math.min(18, currentZoomLevel));
      const scale = Math.pow(1.15, clampedZoom - baseZoom);
      const fontSize = Math.round(baseFontSize * scale);
      
      console.log(`loadInstituicoes: Creating marker with icon:`, houseMarkerIcon);
      const marker = new google.maps.Marker({
        position: position,
        map: null, // Don't add to map yet
        icon: houseMarkerIcon,
        label: {
          text: instituicao.nome,
          className: "marker-label",
          fontSize: `${fontSize}px`,
          fontWeight: "600"
        },
        title: instituicao.nome
      });

      console.log(`loadInstituicoes: Marker created:`, marker, 'Position:', marker.getPosition());

      // Store marker and its data
      markerDataMap.set(marker, details);
      newMarkers.push(marker);
    });

    console.log('loadInstituicoes: Created', newMarkers.length, 'markers');

    // Now update markers with smooth transitions
    // First, fade out old markers
    if (instituicaoMarkers && instituicaoMarkers.length > 0) {
      instituicaoMarkers.forEach(marker => {
        const labelDiv = marker.getLabel()?.element;
        if (labelDiv) {
          labelDiv.classList.add('marker-fade-out');
        }
        // Remove marker after fade out animation
        setTimeout(() => {
          marker.setMap(null);
        }, 300);
      });
    }
    
    // Then add new markers with fade in animation
    console.log('loadInstituicoes: Adding', newMarkers.length, 'markers to map');
    setTimeout(() => {
      newMarkers.forEach((marker, index) => {
        // Stagger the appearance slightly for a smoother effect
        setTimeout(() => {
          try {
            const markerPosition = marker.getPosition();
            console.log(`loadInstituicoes: About to add marker ${index + 1} to map. Position:`, markerPosition, 'Map:', map);
            marker.setMap(map);
            console.log('loadInstituicoes: Added marker', index + 1, 'to map. Marker visible:', marker.getVisible(), 'Map:', marker.getMap());
            
            // Verify marker is actually on the map
            setTimeout(() => {
              const actualMap = marker.getMap();
              const actualPosition = marker.getPosition();
              console.log(`loadInstituicoes: Marker ${index + 1} verification - Map:`, actualMap, 'Position:', actualPosition, 'Visible:', marker.getVisible());
            }, 100);
            
            // Attach click handler
            const details = markerDataMap.get(marker);
            if (details) {
              marker.addListener("click", (e) => {
                try {
                  // If panel is already open, just update the content without closing
                  if (SpeciesPanel.isOpen) {
                    SpeciesPanel.populateData(details);
                  } else {
                    SpeciesPanel.open(details);
                  }
                } catch (error) {
                  console.error('Error opening panel:', error);
                }
              });
            }
          } catch (error) {
            console.error('Error adding marker to map:', error, marker);
          }
        }, index * 20); // 20ms delay between each marker
      });
      
      // Update the markers array after a short delay
      setTimeout(() => {
        instituicaoMarkers = newMarkers;
        console.log('loadInstituicoes: Updated instituicaoMarkers array with', newMarkers.length, 'markers');
      }, newMarkers.length * 20 + 100);
    }, 300);
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
  }
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
    
    // Hide or show the alert option based on login status
    if (menuAlert) {
      const userLoggedIn = isLoggedIn();
      menuAlert.style.display = userLoggedIn ? 'block' : 'none';
      // Also hide the separator if alert is hidden
      const separator = contextMenu.querySelector('.context-menu-separator');
      if (separator) {
        separator.style.display = userLoggedIn ? 'block' : 'none';
      }
    }
    
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
      // Check if user is logged in before allowing alert creation
      if (!isLoggedIn()) {
        showNotification('Por favor, inicie sessão para criar um alerta.', 'error');
        contextMenu.classList.remove('show');
        // Optionally redirect to login after a short delay
        setTimeout(() => {
          if (confirm('Deseja ir para a página de login?')) {
            window.location.href = 'login.php';
          }
        }, 500);
        return;
      }

      
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
          showNotification('Localização copiada para a área de transferência!', 'info');
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
  const cardsGrid = alertMenu.querySelector('.cards-grid');
  const searchInput = document.getElementById('popup-search-input');
  
  // Unique Tag Arrays for the popup to avoid conflict with sidebar
  let popupFamilyTags = [];
  let popupStateTags = [];

  let selectedAnimal = null;

  // --- Load and Render Animals ---
  async function loadAnimals() {
    if (!cardsGrid) return;
    
    try {
      const filters = getAnimalFilters({
        searchInput: searchInput,
        familyTagsArray: popupFamilyTags,
        stateTagsArray: popupStateTags
      });
      
      const animals = await fetchAnimals(filters);
      
      renderAnimalCards(animals, cardsGrid, {
        buttonText: 'Selecionar',
        showDetailsLink: false,
        onButtonClick: (animal, card) => {
          // Deselect all cards
          const allCards = cardsGrid.querySelectorAll('.card');
          allCards.forEach(c => {
            c.classList.remove('selected');
            const btnText = c.querySelector('.btn-text');
            if (btnText) btnText.textContent = 'Selecionar';
          });
          
          // Select this card
          card.classList.add('selected');
          const btnText = card.querySelector('.btn-text');
          if (btnText) btnText.textContent = 'Selecionado';
          
          // Store selected animal
          selectedAnimal = {
            name: animal.nome_comum,
            family: animal.nome_familia,
            status: animal.nome_estado,
            id: animal.animal_id
          };
          
          // Enable submit button
          if (submitButton) {
            submitButton.disabled = false;
            submitButton.style.opacity = '1';
            submitButton.style.cursor = 'pointer';
          }
        },
        emptyMessage: 'Nenhum animal encontrado.'
      });
    } catch (error) {
      console.error("Erro ao carregar animais:", error);
      if (cardsGrid) {
        cardsGrid.innerHTML = '<p>Erro ao carregar dados.</p>';
      }
    }
  }

  // Initialize Tags using existing helper function - fetch options from API first
  (async () => {
    if (typeof initTagInputWithDropdown === 'function') {
      try {
        const [familyOptions, stateOptions] = await Promise.all([
          fetchFamilyOptions(),
          fetchStateOptions()
        ]);

        initTagInputWithDropdown("popup-family-input", "popup-family-tags", "popup-family-dropdown", popupFamilyTags, familyOptions);
        initTagInputWithDropdown("popup-state-input", "popup-state-tags", "popup-state-dropdown", popupStateTags, stateOptions);
      } catch (error) {
        console.error('Erro ao carregar opções de filtros do popup:', error);
        // Fallback to empty arrays if API fails
        initTagInputWithDropdown("popup-family-input", "popup-family-tags", "popup-family-dropdown", popupFamilyTags, []);
        initTagInputWithDropdown("popup-state-input", "popup-state-tags", "popup-state-dropdown", popupStateTags, []);
      }
    }
  })();

  // --- Event Listeners for Filtering ---
  if (searchInput) {
    searchInput.addEventListener('input', loadAnimals);
  }

  // Observer for tags (since the helper modifies DOM)
  const observerConfig = { childList: true };
  const tagObserver = new MutationObserver(loadAnimals);
  
  const famContainer = document.getElementById('popup-family-tags');
  const stateContainer = document.getElementById('popup-state-tags');
  
  if (famContainer) tagObserver.observe(famContainer, observerConfig);
  if (stateContainer) tagObserver.observe(stateContainer, observerConfig);

  // Clear filters button for popup
  const popupClearFiltersBtn = document.getElementById('popup-clear-filters-btn');
  if (popupClearFiltersBtn && typeof clearAnimalFilters === 'function') {
    popupClearFiltersBtn.addEventListener('click', () => {
      clearAnimalFilters({
        searchInput: searchInput,
        familyTagsArray: popupFamilyTags,
        stateTagsArray: popupStateTags,
        familyTagsId: 'popup-family-tags',
        stateTagsId: 'popup-state-tags',
        familyInputId: 'popup-family-input',
        stateInputId: 'popup-state-input'
      });
      loadAnimals();
    });
  }

  // --- Menu Visibility & Location ---
  const originalMenuAlertClick = document.getElementById('menu-alert');
  if (originalMenuAlertClick) {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.target.classList.contains('show')) {
               if (locationInput && locationInput.value && displayLocation) {
                   displayLocation.textContent = locationInput.value;
               }
               // Load animals when menu opens
               loadAnimals();
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

    // Reset Selection
    if (cardsGrid) {
      const cards = cardsGrid.querySelectorAll('.card');
      cards.forEach(c => {
        c.classList.remove('selected');
        const btnText = c.querySelector('.btn-text');
        if (btnText) btnText.textContent = 'Selecionar';
      });
    }
    
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
    submitButton.addEventListener('click', async () => {
      const location = locationInput.value;
      if (!selectedAnimal || !location) {
        alert('Por favor, selecione um animal da lista.');
        return;
      }

      // Get user
      const user = getCurrentUser();
      
      if (!isLoggedIn()) {
        alert('Por favor, inicie sessão para criar um alerta.');
        return;
      }

      // Parse location coordinates (format: "lat, lng" or "lat, lng")
      const locationMatch = location.match(/(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/);
      if (!locationMatch) {
        alert('Formato de localização inválido. Use: latitude, longitude');
        return;
      }

      const latitude = parseFloat(locationMatch[1]);
      const longitude = parseFloat(locationMatch[2]);

      if (isNaN(latitude) || isNaN(longitude)) {
        alert('Coordenadas inválidas.');
        return;
      }

      // Disable button during submission
      submitButton.disabled = true;
      submitButton.style.opacity = '0.6';
      submitButton.style.cursor = 'not-allowed';
      const originalText = submitButton.textContent;
      submitButton.textContent = 'A enviar...';

      try {
        const apiUrl = getApiUrl('api/alerts');
        const response = await fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            animal_id: selectedAnimal.id,
            utilizador_id: user.id,
            latitude: latitude,
            longitude: longitude,
            data_avistamento: new Date().toISOString()
          })
        });

        let result;
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          result = await response.json();
        } else {
          const text = await response.text();
          throw new Error(text || `Erro ${response.status}: ${response.statusText}`);
        }

        if (!response.ok) {
          throw new Error(result?.error || 'Erro ao criar alerta.');
        }

        // Show success notification
        showNotification('Avistamento criado com sucesso!', 'success');

        closeMenu();
        
        // Reload avistamentos to show the new alert on the map
        if (typeof loadAvistamentos === 'function') {
          loadAvistamentos();
        }
      } catch (error) {
        console.error('Erro ao criar alerta:', error);
        alert(error?.message || 'Erro ao criar alerta. Por favor, tente novamente.');
      } finally {
        // Re-enable button
        submitButton.disabled = false;
        submitButton.style.opacity = '1';
        submitButton.style.cursor = 'pointer';
        submitButton.textContent = originalText;
      }
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
      <a href="index.php" class="logo-text">BioMap</a>
    </div>
    <nav class="nav-links">
      <a href="index.php" class="nav-link">Mapa</a>
      <a href="animais.php" class="nav-link">Animais</a>
      <a href="sobre_nos.php" class="nav-link">Sobre nós</a>
      <a href="doar.php" class="nav-link">Doar</a>
    </nav>
    <div class="user-section">
      <a href="perfil.php" id="user-name" class="user-name"></a>
      <i class="fas fa-user user-icon" id="user-icon"></i>
      <div id="account-menu" class="account-menu">
        <div class="account-menu-item" id="menu-login"><a style="text-decoration: none;color: #333;" href="login.php">Iniciar Sessão</a></div>
        <div class="account-menu-separator" id="sep-login"></div>
        <div class="account-menu-item" id="menu-create-account"><a style="text-decoration: none;color: #333;font-weight:600;" href="sign_up.php">Criar Conta</a></div>
        <div class="account-menu-separator" id="sep-create"></div>
        <div class="account-menu-item" id="menu-profile"><a style="text-decoration: none;color: #333;font-weight:600;" href="perfil.php">Perfil</a></div>
        <div class="account-menu-separator" id="sep-profile"></div>
  <div class="account-menu-item" id="menu-profile-admin"><a style="text-decoration: none;color: #333;font-weight:600;" href="perfil.php">Perfil</a></div>
        <div class="account-menu-separator" id="sep-logout"></div>
        <div class="account-menu-item" id="menu-logout"><a style="text-decoration: none;color: #333;font-weight:600;" href="logout.php">Terminar Sessão</a></div>
      </div>
    </div>
  </div>
</header>
`;

async function loadHeader(path = 'header.php') {
    const placeholder = document.getElementById('header-placeholder');
    if (!placeholder) return;
  
    try {
      placeholder.innerHTML = headerTemplate;
    } catch (err) {
      console.warn('loadHeader: failed to set inline template.', err);
    }
  
    if (typeof initAccountMenu === 'function') initAccountMenu();
    if (typeof applyHeaderAuthState === 'function') applyHeaderAuthState();
    if (typeof highlightCurrentPage === 'function') highlightCurrentPage();
}
  
function highlightCurrentPage() {
  const current = (window.location.pathname.split('/').pop() || 'index.php').split('?')[0];
  document.querySelectorAll('.nav-link').forEach(link => {
    const linkHref = (link.getAttribute('href') || '').split('?')[0];
    link.classList.toggle('current', linkHref === current);
  });
}

// --- Dynamic header visibility by user role ---
function applyHeaderAuthState() {
  const user = getCurrentUser();

  const loginItem = document.getElementById('menu-login');
  const createItem = document.getElementById('menu-create-account');
  const profileItem = document.getElementById('menu-profile');
  const adminItem = document.getElementById('menu-profile-admin');
  const sepLogin = document.getElementById('sep-login');
  const sepCreate = document.getElementById('sep-create');
  const sepProfile = document.getElementById('sep-profile');
  const logoutItem = document.getElementById('menu-logout');
  const sepLogout = document.getElementById('sep-logout');
  const userNameElement = document.getElementById('user-name');

  const hide = (el) => { if (el) el.style.display = 'none'; };
  const show = (el) => { if (el) el.style.display = 'block'; };

  if (!user) {
    show(loginItem); show(sepLogin);
    show(createItem); hide(sepCreate);
    hide(profileItem); hide(sepProfile);
    hide(adminItem);
    hide(logoutItem); hide(sepLogout);
    if (userNameElement) {
      userNameElement.textContent = '';
      userNameElement.style.display = 'none';
    }
    return;
  }

  hide(loginItem); hide(sepLogin);
  hide(createItem); hide(sepCreate);
  show(logoutItem); show(sepLogout);

  // Display user name if available
  if (userNameElement && user.name) {
    userNameElement.textContent = user.name;
    userNameElement.style.display = 'inline-block';
  } else if (userNameElement) {
    userNameElement.textContent = '';
    userNameElement.style.display = 'none';
  }

  // Both admin and regular users are directed to the canonical profile page
  if (isAdmin()) {
    hide(profileItem); hide(sepProfile);
    show(adminItem);
    if (userNameElement) userNameElement.href = 'perfil.php';
  } else if (Number(user.funcao_id) === 2) {
    show(profileItem); show(sepProfile);
    hide(adminItem);
    if (userNameElement) userNameElement.href = 'perfil.php';
  } else {
    hide(profileItem); hide(sepProfile);
    hide(adminItem);
  }
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
