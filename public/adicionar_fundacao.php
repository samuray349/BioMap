<?php
require_once 'access_control.php';
checkAccess(ACCESS_ADMIN);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Instituição</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/x-icon" href="./img/biomap-icon.png">
    <script src="js/config.js?v=<?php echo time(); ?>"></script>
    <style>
        /* Error styling for form fields */
        .input-field input.field-error,
        .input-field textarea.field-error,
        .input-field select.field-error,
        .chip-select.field-error,
        .time-display.field-error,
        .upload-box.field-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
        }
        
        /* Error styling for location input wrapper */
        .location-input-wrapper.field-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
        }
        
        .location-input-wrapper.field-error input {
            border-color: #ef4444 !important;
        }
        
        /* Textarea styling to match inputs */
        .input-field textarea {
            border: 1px solid #d9dee7 !important;
            border-radius: 16px !important;
            padding: 16px 18px !important;
            font-size: 15px !important;
            background-color: #fbfcfe !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
            width: 100%;
            font-family: inherit;
            resize: vertical;
            min-height: 150px;
            line-height: 1.5;
        }
        
        .input-field textarea::placeholder {
            color: #94a3b8 !important;
        }
        
        .input-field textarea:focus {
            border-color: #198754 !important;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.12) !important;
            outline: none !important;
        }

        /* Submit feedback */
        .submit-message {
            margin-top: 12px;
            font-size: 0.95rem;
        }
        .submit-message.error {
            color: #b91c1c;
        }
        .submit-message.success {
            color: #047857;
        }
    </style>
</head>
<body>
    <div id="header-placeholder"></div>

    <main class="add-institution-page">
        <section class="add-institution-card">
            <div class="add-animal-header">
                <div>
                    <h1>Adicionar Instituição</h1>
                    <p class="subtitle">Preencha os detalhes para publicar uma nova instituição parceira na plataforma.</p>
                </div>
                <button class="ghost-btn" type="button">
                    <i class="fa-solid fa-rotate"></i>
                    Limpar formulário
                </button>
            </div>

            <form class="add-animal-form add-institution-form">
                <div class="form-grid">
                    <div class="input-field">
                        <label for="institution-name">Nome da Instituição</label>
                        <input id="institution-name" type="text" placeholder="Ex: Centro de Conservação do Lince Ibérico">
                    </div>
                    <div class="input-field">
                        <label for="institution-contact">Contacto</label>
                        <input id="institution-contact" type="text" placeholder="Email, telefone ou website oficial">
                    </div>
                </div>

                <section class="add-section schedule-section">
                    <div class="section-heading">
                        <h2>Horário</h2>
                        <p>Defina os dias de funcionamento e os respetivos horários.</p>
                    </div>
                    <div class="schedule-grid">
                        <div class="input-field">
                            <label for="schedule-days">Dias</label>
                            <select id="schedule-days" name="schedule-days" class="chip-select multi-select" multiple aria-describedby="schedule-days-hint">
                                <option value="Segunda">Segunda</option>
                                <option value="Terça">Terça</option>
                                <option value="Quarta">Quarta</option>
                                <option value="Quinta">Quinta</option>
                                <option value="Sexta">Sexta</option>
                                <option value="Sábado">Sábado</option>
                                <option value="Domingo">Domingo</option>
                            </select>
                            <small id="schedule-days-hint" class="field-hint">Use Ctrl (Windows) ou ⌘ (Mac) para selecionar vários dias.</small>
                        </div>
                        <div class="schedule-times">
                            <div class="input-field time-field">
                                <label for="opening-time">Hora abertura</label>
                                <button type="button" class="time-display" data-target="opening-time">09:00</button>
                                <input id="opening-time" type="hidden" value="09:00">
                            </div>
                            <div class="input-field time-field">
                                <label for="closing-time">Hora fecho</label>
                                <button type="button" class="time-display" data-target="closing-time">17:00</button>
                                <input id="closing-time" type="hidden" value="17:00">
                            </div>
                        </div>
                    </div>
                </section>

                <section class="add-section description-section">
                    <div class="section-heading">
                        <h2>Descrição detalhada</h2>
                        <p>Conte a história, missão e iniciativas da instituição.</p>
                    </div>
                    <div class="input-field">
                        <textarea id="institution-description" rows="4" placeholder="Conte a história da instituição, missão, projetos desenvolvidos e outros detalhes importantes..."></textarea>
                    </div>
                </section>

                <section class="add-section location-section">
                    <div class="section-heading">
                        <h2>Localização</h2>
                        <p>Indique a localização principal ou zona de atuação.</p>
                    </div>
                    <div class="location-search input-field">
                        <label class="visually-hidden" for="location-search">Pesquisar localização</label>
                        <div class="location-input-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input id="location-search" type="text" placeholder="Pesquisar" readonly style="cursor: pointer;">
                        </div>
                    </div>
                </section>

                <section class="add-section image-upload-section">
                    <div class="section-heading">
                        <h2>Adicionar Imagem</h2>
                        <p>Carregue uma imagem destaque da instituição ou das suas atividades.</p>
                    </div>
                    <div class="image-upload">
                        <div class="image-upload-body">
                            <label class="upload-box" for="institution-image">
                                <div class="upload-instructions">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <p>Upload Imagem...</p>
                                    <small>Formatos aceites: JPG ou PNG até 5MB</small>
                                </div>
                                <input type="file" id="institution-image" accept="image/*">
                            </label>
                            <figure class="image-preview">
                                <img src="./img/generico_instituicao.jpg" alt="Pré-visualização da instituição">
                                <figcaption>Imagem de exemplo</figcaption>
                            </figure>
                        </div>
                    </div>
                </section>

                <div class="form-actions add-section">
                    <button type="submit" class="primary-btn">
                        <i class="fa-solid fa-building"></i>
                        Adicionar Instituição
                    </button>
                    <div id="submit-message" class="submit-message" role="status" aria-live="polite"></div>
                </div>
            </form>
        </section>
        
        <!-- Existing Time Picker Modal -->
        <div class="time-picker-overlay" id="timePickerOverlay" aria-hidden="true" role="dialog" aria-modal="true">
            <div class="time-picker-modal">
                <div class="time-picker-header">
                    <h3 class="text-primary">Escolher tempo</h3>
                    <button type="button" class="close-picker" aria-label="Fechar seletor">&times;</button>
                </div>
                <div class="time-picker-body wheel-mode">
                    <div class="time-wheel-column">
                        <span class="wheel-label">Hora</span>
                        <span id="hourValue" class="wheel-current" aria-live="polite">09</span>
                        <div class="time-wheel" id="hourWheel" tabindex="0" role="listbox" aria-label="Selecionar hora"></div>
                    </div>
                    <div class="time-wheel-column">
                        <span class="wheel-label">Minuto</span>
                        <span id="minuteValue" class="wheel-current" aria-live="polite">00</span>
                        <div class="time-wheel" id="minuteWheel" tabindex="0" role="listbox" aria-label="Selecionar minuto"></div>
                    </div>
                </div>
                <div class="time-picker-actions">
                    <button type="button" class="ghost-btn small" id="cancelTimePicker">Cancelar</button>
                    <button type="button" class="primary-btn small" id="confirmTimePicker">Escolher</button>
                </div>
            </div>
        </div>
    </main>

    <!-- MAP OVERLAY HTML -->
    <div class="map-overlay" id="map-overlay">
        <div class="map-modal">
            <div class="map-modal-header">
                <h2 class="text-primary">Escolher a localização</h2>
                <div class="map-search-box">
                    <input type="text" id="map-overlay-search" placeholder="Portugal" value="Portugal">
                </div>
                <button class="close-map-btn" id="close-map-btn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="map-modal-body">
                <div id="map-picker-container" class="map-picker-container"></div>
                <div class="map-sidebar">
                    <button class="use-my-coord-btn" id="use-my-coord-btn">
                        Use a minha coordenada
                    </button>
                    <div class="selected-coords-container">
                        <p class="coords-label">Coordenadas selecionadas</p>
                        <p id="selected-coords-display" class="coords-value">Nenhuma localização selecionada</p>
                    </div>
                    <p class="map-tip">Dica: Clique em qualquer lugar no mapa para sinalizar o localização.</p>
                    <button class="confirm-map-btn" id="confirm-map-btn" disabled>Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <!-- Google Maps API with callback to the global function in script.js -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBPikkMWW5AerEd4av-nwnTgqksXuaUiws&callback=initMapPicker&libraries=places&v=weekly&loading=async" defer></script>
    
    <script>
        loadHeader();
        highlightCurrentPage();

        // Form submission handler with validations
        (function attachAddInstitutionSubmitHandler() {
            const form = document.querySelector('.add-institution-form');
            const imageInput = document.getElementById('institution-image');
            const messageEl = document.getElementById('submit-message');

            const setMessage = (text, type = '') => {
                if (!messageEl) return;
                messageEl.textContent = text || '';
                messageEl.className = `submit-message ${type}`.trim();
            };

            // Helper function to clear all error classes
            const clearAllErrors = () => {
                const allInputs = form.querySelectorAll('input, textarea, select, .chip-select, .time-display, .upload-box');
                allInputs.forEach(input => {
                    input.classList.remove('field-error');
                });
                // Also clear location wrapper error
                const locationWrapper = document.querySelector('.location-input-wrapper');
                if (locationWrapper) {
                    locationWrapper.classList.remove('field-error');
                }
            };

            // Helper function to add error class to a field
            const addError = (fieldId) => {
                if (fieldId === 'location-search') {
                    // Special handling for location - highlight the wrapper div
                    const locationWrapper = document.querySelector('.location-input-wrapper');
                    if (locationWrapper) {
                        locationWrapper.classList.add('field-error');
                    }
                } else {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.classList.add('field-error');
                    } else {
                        // Try to find by class or other selector
                        const timeDisplay = document.querySelector(`[data-target="${fieldId}"]`);
                        if (timeDisplay) {
                            timeDisplay.classList.add('field-error');
                        }
                    }
                }
            };

            // Add event listeners to remove error class when user types
            const setupFieldValidation = () => {
                const fields = [
                    'institution-name', 'institution-contact', 'institution-description',
                    'schedule-days', 'opening-time', 'closing-time', 'location-search'
                ];
                fields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('input', () => {
                            field.classList.remove('field-error');
                        });
                        field.addEventListener('change', () => {
                            field.classList.remove('field-error');
                        });
                    }
                });
                
                // Handle time displays
                const timeDisplays = document.querySelectorAll('.time-display');
                timeDisplays.forEach(display => {
                    display.addEventListener('click', () => {
                        display.classList.remove('field-error');
                    });
                });
                
                // Handle location input wrapper
                const locationWrapper = document.querySelector('.location-input-wrapper');
                const locationInput = document.getElementById('location-search');
                if (locationWrapper && locationInput) {
                    locationInput.addEventListener('click', () => {
                        locationWrapper.classList.remove('field-error');
                    });
                    locationInput.addEventListener('input', () => {
                        locationWrapper.classList.remove('field-error');
                    });
                }
                
                // Handle image input
                if (imageInput) {
                    const uploadBox = document.querySelector('.upload-box');
                    if (uploadBox) {
                        imageInput.addEventListener('change', () => {
                            uploadBox.classList.remove('field-error');
                        });
                    }
                }
            };

            if (!form) return;

            // Setup field validation listeners
            setupFieldValidation();

            // Image preview functionality (like in adicionar_animal.php)
            (function setupImagePreview() {
                const imageInput = document.getElementById('institution-image');
                const imagePreview = document.querySelector('.image-preview img');
                
                if (!imageInput || !imagePreview) return;
                
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imageUrl = e.target.result;
                            if (imagePreview) {
                                imagePreview.src = imageUrl;
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            })();

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                
                // Clear all previous errors
                clearAllErrors();
                
                try {
                    setMessage('A enviar...');

                    const nome = document.getElementById('institution-name')?.value?.trim();
                    const descricao = document.getElementById('institution-description')?.value?.trim();
                    const localizacao_texto = document.getElementById('location-search')?.value?.trim();
                    const telefone_contacto = document.getElementById('institution-contact')?.value?.trim();
                    const scheduleDays = document.getElementById('schedule-days');
                    const selectedDays = Array.from(scheduleDays?.selectedOptions || []).map(opt => opt.value);
                    const dias_aberto = selectedDays.length > 0 ? selectedDays.join(', ') : '';
                    const hora_abertura = document.getElementById('opening-time')?.value?.trim();
                    const hora_fecho = document.getElementById('closing-time')?.value?.trim();
                    
                    // Get location coordinates from map picker
                    let localizacao = null;
                    const locationInputValue = document.getElementById('location-search')?.value?.trim();
                    if (locationInputValue) {
                        // Parse coordinates from input (format: "lat, lng" or "lat,lng")
                        // The map picker sets it as "lat, lng" format
                        const coordsMatch = locationInputValue.match(/(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)/);
                        if (coordsMatch) {
                            // Input format is "lat, lng", convert to {lat, lon} object
                            // Database uses ST_MakePoint(lon, lat) so we keep lat/lon order in the object
                            localizacao = {
                                lat: parseFloat(coordsMatch[1]),
                                lon: parseFloat(coordsMatch[2])
                            };
                        }
                    }

                    const file = imageInput?.files?.[0];

                    let hasErrors = false;

                    // Validate required fields
                    if (!nome) { addError('institution-name'); hasErrors = true; }
                    if (!descricao) { addError('institution-description'); hasErrors = true; }
                    if (!localizacao_texto) { addError('location-search'); hasErrors = true; }
                    if (!telefone_contacto) { addError('institution-contact'); hasErrors = true; }
                    if (!dias_aberto) { addError('schedule-days'); hasErrors = true; }
                    if (!hora_abertura) { addError('opening-time'); hasErrors = true; }
                    if (!hora_fecho) { addError('closing-time'); hasErrors = true; }
                    if (!localizacao) { addError('location-search'); hasErrors = true; }
                    
                    // Validate character lengths
                    if (nome && nome.length < 3) { addError('institution-name'); hasErrors = true; }
                    if (descricao && descricao.length < 10) { addError('institution-description'); hasErrors = true; }
                    if (telefone_contacto && telefone_contacto.length < 5) { addError('institution-contact'); hasErrors = true; }
                    
                    // Validate location coordinates format
                    if (localizacao) {
                        const lat = localizacao.lat;
                        const lon = localizacao.lon;
                        if (isNaN(lat) || isNaN(lon)) {
                            addError('location-search');
                            hasErrors = true;
                        } else {
                            if (lat < -90 || lat > 90 || lon < -180 || lon > 180) {
                                addError('location-search');
                                hasErrors = true;
                            }
                        }
                    } else if (!locationInputValue) {
                        // If no location input value, check the selected coords display as fallback
                        const selectedCoordsDisplay = document.getElementById('selected-coords-display');
                        if (!selectedCoordsDisplay || selectedCoordsDisplay.textContent === 'Nenhuma localização selecionada') {
                            addError('location-search');
                            hasErrors = true;
                        }
                    }
                    
                    // Validate time format and that opening < closing
                    if (hora_abertura && hora_fecho) {
                        const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
                        if (!timeRegex.test(hora_abertura)) {
                            addError('opening-time');
                            hasErrors = true;
                        }
                        if (!timeRegex.test(hora_fecho)) {
                            addError('closing-time');
                            hasErrors = true;
                        }
                        
                        // Check if opening time is before closing time
                        if (timeRegex.test(hora_abertura) && timeRegex.test(hora_fecho)) {
                            const [openHour, openMin] = hora_abertura.split(':').map(Number);
                            const [closeHour, closeMin] = hora_fecho.split(':').map(Number);
                            const openTime = openHour * 60 + openMin;
                            const closeTime = closeHour * 60 + closeMin;

                            if (openTime >= closeTime) {
                                addError('opening-time');
                                addError('closing-time');
                                hasErrors = true;
                                if (typeof showNotification === 'function') {
                                    showNotification('Hora de abertura deve ser anterior à hora de fecho.', 'error');
                                }
                            }
                        }
                    }
                    
                    if (!file) {
                        const uploadBox = document.querySelector('.upload-box');
                        if (uploadBox) {
                            uploadBox.classList.add('field-error');
                        }
                        hasErrors = true;
                    }
                    
                    if (hasErrors) {
                        setMessage(''); // Clear loading message
                        if (typeof showNotification === 'function') {
                            showNotification('Preencha todos os campos corretamente.', 'error');
                        }
                        return;
                    }

                    // Check for duplicate institution name before uploading image
                    setMessage('A verificar nome da instituição...');
                    const checkDuplicateUrl = window.API_CONFIG?.getUrl('instituicoes') || '/instituicoes';
                    // Note: We'll check duplicates on the server side, but we can try to fetch existing ones if GET endpoint exists
                    
                    // All validations passed, now create the institution in database first (without image)
                    setMessage('A guardar a instituição...');
                    
                    const fileToDataURL = (file) => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = (err) => reject(err);
                        reader.readAsDataURL(file);
                    });
                    
                    const base64Image = await fileToDataURL(file);
                    
                    // First, create institution without image URL (will be updated after upload)
                    const payload = {
                        nome: nome,
                        descricao: descricao,
                        localizacao_texto: localizacao_texto,
                        telefone_contacto: telefone_contacto,
                        localizacao: localizacao,
                        dias_aberto: dias_aberto,
                        hora_abertura: hora_abertura,
                        hora_fecho: hora_fecho
                        // url_imagem is omitted - will be added after successful upload
                    };

                    const apiUrl = window.API_CONFIG?.getUrl('instituicoes') || '/instituicoes';
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        const errorMsg = result?.error || '';
                        
                        // Check if error is about duplicate name
                        const isDuplicateName = errorMsg.toLowerCase().includes('já existe') || 
                                              errorMsg.toLowerCase().includes('duplicate');
                        
                        if (isDuplicateName) {
                            addError('institution-name');
                            setMessage('');
                            if (typeof showNotification === 'function') {
                                showNotification(errorMsg || `Já existe uma instituição com o nome "${nome}". Por favor, escolha um nome diferente.`, 'error');
                            }
                            return;
                        }
                        
                        // Check if error is about location
                        const isLocationError = errorMsg.toLowerCase().includes('localização') || 
                                              errorMsg.toLowerCase().includes('coordenadas');
                        
                        if (isLocationError) {
                            addError('location-search');
                            setMessage('');
                            if (typeof showNotification === 'function') {
                                showNotification('Formato de localização inválido. Por favor, selecione uma localização válida no mapa.', 'error');
                            }
                            return;
                        }
                        
                        // Generic location error
                        if (errorMsg.toLowerCase().includes('localização') || errorMsg.toLowerCase().includes('coordenadas')) {
                            addError('location-search');
                            setMessage('');
                            if (typeof showNotification === 'function') {
                                showNotification('Erro na localização. Por favor, selecione uma localização válida no mapa.', 'error');
                            }
                            return;
                        }
                        
                        // Check if error is about time
                        const isTimeError = errorMsg.toLowerCase().includes('hora') || 
                                          errorMsg.toLowerCase().includes('abertura') ||
                                          errorMsg.toLowerCase().includes('fecho');
                        
                        if (isTimeError) {
                            addError('opening-time');
                            addError('closing-time');
                            setMessage('');
                            if (typeof showNotification === 'function') {
                                showNotification('Hora de abertura deve ser anterior à hora de fecho.', 'error');
                            }
                            return;
                        }
                        
                        // Generic error message
                        setMessage('');
                        if (typeof showNotification === 'function') {
                            showNotification('Erro ao criar instituição. Verifique os campos e tente novamente.', 'error');
                        }
                        return;
                    }

                    // Institution created successfully, now upload the image
                    setMessage('A fazer upload da imagem...');
                    const uploadResponse = await fetch('upload_image.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            imagem: {
                                data: base64Image,
                                originalName: file.name
                            },
                            folder: 'instituicao'
                        })
                    });

                    const uploadResult = await uploadResponse.json();
                    if (!uploadResponse.ok || !uploadResult.success) {
                        // Image upload failed - delete the institution that was just created
                        const instituicaoId = result.instituicao_id;
                        if (instituicaoId) {
                            try {
                                // Note: DELETE endpoint for instituicoes may not exist yet, but we'll try
                                const deleteUrl = window.API_CONFIG?.getUrl(`instituicoes/${instituicaoId}`) || `/instituicoes/${instituicaoId}`;
                                await fetch(deleteUrl, {
                                    method: 'DELETE',
                                    headers: { 'Content-Type': 'application/json' }
                                });
                            } catch (deleteError) {
                                console.error('Error deleting institution after image upload failure:', deleteError);
                            }
                        }
                        
                        setMessage('');
                        if (typeof showNotification === 'function') {
                            showNotification('Erro ao fazer upload da imagem. Verifique se a imagem é válida e tente novamente.', 'error');
                        }
                        return;
                    }

                    // Image uploaded successfully, now update the institution with the correct image URL
                    setMessage('A atualizar imagem da instituição...');
                    const instituicaoId = result.instituicao_id;
                    if (instituicaoId) {
                        // Note: PUT endpoint for instituicoes may not exist yet, but we'll try
                        const updateResponse = await fetch(window.API_CONFIG?.getUrl(`instituicoes/${instituicaoId}`) || `/instituicoes/${instituicaoId}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                nome: nome,
                                descricao: descricao,
                                localizacao_texto: localizacao_texto,
                                telefone_contacto: telefone_contacto,
                                localizacao: localizacao,
                                dias_aberto: dias_aberto,
                                hora_abertura: hora_abertura,
                                hora_fecho: hora_fecho,
                                url_imagem: uploadResult.url
                            })
                        });
                        
                        if (!updateResponse.ok) {
                            // Update failed - try to delete the institution
                            try {
                                const deleteUrl = window.API_CONFIG?.getUrl(`instituicoes/${instituicaoId}`) || `/instituicoes/${instituicaoId}`;
                                await fetch(deleteUrl, {
                                    method: 'DELETE',
                                    headers: { 'Content-Type': 'application/json' }
                                });
                            } catch (deleteError) {
                                console.error('Error deleting institution after image update failure:', deleteError);
                            }
                            
                            setMessage('');
                            if (typeof showNotification === 'function') {
                                showNotification('Erro ao atualizar imagem da instituição. Tente novamente.', 'error');
                            }
                            return;
                        }
                    }

                    // Clear the loading message
                    setMessage('');
                    
                    // Show success notification
                    if (typeof showNotification === 'function') {
                        showNotification('Instituição criada com sucesso!', 'success');
                    }
                    
                    // Reset form after success
                    setTimeout(() => {
                        form.reset();
                        // Reset location display
                        const selectedCoordsDisplay = document.getElementById('selected-coords-display');
                        if (selectedCoordsDisplay) {
                            selectedCoordsDisplay.textContent = 'Nenhuma localização selecionada';
                        }
                        // Reset time displays
                        const openingTimeDisplay = document.querySelector('[data-target="opening-time"]');
                        const closingTimeDisplay = document.querySelector('[data-target="closing-time"]');
                        if (openingTimeDisplay) {
                            openingTimeDisplay.textContent = '09:00';
                            document.getElementById('opening-time').value = '09:00';
                        }
                        if (closingTimeDisplay) {
                            closingTimeDisplay.textContent = '17:00';
                            document.getElementById('closing-time').value = '17:00';
                        }
                    }, 2000);
                } catch (error) {
                    console.error('Erro ao submeter instituição', error);
                    setMessage('');
                    if (typeof showNotification === 'function') {
                        showNotification('Erro ao criar instituição. Verifique os campos e tente novamente.', 'error');
                    }
                }
            });
        })();
    </script>

    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>
    
</body>
</html>
