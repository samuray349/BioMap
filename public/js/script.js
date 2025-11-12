let map;
let familyTags = [];
let stateTags = [];

function initMap() {
  const center = { lat: 39.09903420850493, lng: -9.283192320989297 };

  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 12,
    center,
    disableDefaultUI: true,
    zoomControl: true,
    mapId: "DEMO_MAP_ID",
  });

  const locations = [
    { position: { lat: 39.098569723610105,  lng: -9.21834924308909 }, title: "Fundação dos animais" },
    { position: { lat: 39.13471130131973, lng: -9.299138410129158 }, title: "Lince Ibérico" },
    { position: { lat: 39.16084345764295, lng: -9.237634072626696 }, title: "Javali" }
  ];

  // Create labeled markers
  locations.forEach(loc => {
    const marker = new google.maps.Marker({
      position: loc.position,
      map,
      title: loc.title,
      label: {
        text: loc.title,
        className: "marker-label"
      },
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 8,
        fillColor: "#198754",
        fillOpacity: 1,
        strokeColor: "white",
        strokeWeight: 2
      }
    });
  });

  // Initialize tag inputs with predefined options
  const familyOptions = [
    'Felidae', 'Canidae', 'Ursidae', 'Mustelidae', 
    'Cervidae', 'Suidae', 'Leporidae', 'Sciuridae',
    'Rodentia', 'Chiroptera', 'Carnivora', 'Artiodactyla'
  ];
  
  const stateOptions = [
    'Em Perigo', 'Vulnerável', 'Quase Ameaçado',
    'Pouco Preocupante', 'Dados Insuficientes',
    'Extinto na Natureza', 'Extinto'
  ];

  initTagInputWithDropdown('family-input', 'family-tags', 'family-dropdown', familyTags, familyOptions);
  initTagInputWithDropdown('state-input', 'state-tags', 'state-dropdown', stateTags, stateOptions);

  // Initialize context menu
  initContextMenu();

  // Initialize account menu
  initAccountMenu();
}

// Tag input functionality with dropdown
function initTagInputWithDropdown(inputId, containerId, dropdownId, tagsArray, options) {
  const input = document.getElementById(inputId);
  const container = document.getElementById(containerId);
  const dropdown = document.getElementById(dropdownId);
  const wrapper = input.closest('.tag-input-wrapper');

  function addTag(text) {
    if (!text.trim() || tagsArray.includes(text.trim())) return;
    
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
    if (!wrapper.contains(e.target)) {
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
  let rightClickPosition = null;
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
    rightClickPosition = e.latLng;
    
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
    if (rightClickPosition) {
      alert(`Alertar animal em:\nLat: ${rightClickPosition.lat().toFixed(6)}\nLng: ${rightClickPosition.lng().toFixed(6)}`);
    }
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
    if (!contextMenu.contains(e.target)) {
      contextMenu.classList.remove('show');
    }
  });
}

// Account Menu functionality
function initAccountMenu() {
  const userIcon = document.getElementById('user-icon');
  const accountMenu = document.getElementById('account-menu');
  const menuLogin = document.getElementById('menu-login');
  const menuCreateAccount = document.getElementById('menu-create-account');

  // Toggle menu on user icon click
  userIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    accountMenu.classList.toggle('show');
  });

  // Menu item handlers
  menuLogin.addEventListener('click', () => {
    alert('Iniciar Sessão');
    accountMenu.classList.remove('show');
  });

  menuCreateAccount.addEventListener('click', () => {
    alert('Criar Conta');
    accountMenu.classList.remove('show');
  });

  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
    if (!accountMenu.contains(e.target) && e.target !== userIcon) {
      accountMenu.classList.remove('show');
    }
  });
}

