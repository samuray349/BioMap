<?php
/**
 * POST /instituicoes
 * Create a new institution
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

try {
    $input = getJsonInput();
    
    $nome = $input['nome'] ?? null;
    $descricao = $input['descricao'] ?? null;
    $localizacao_texto = $input['localizacao_texto'] ?? null;
    $telefone_contacto = $input['telefone_contacto'] ?? null;
    $url_imagem = $input['url_imagem'] ?? null;
    $localizacao = $input['localizacao'] ?? null;
    $dias_aberto = $input['dias_aberto'] ?? null;
    $hora_abertura = $input['hora_abertura'] ?? null;
    $hora_fecho = $input['hora_fecho'] ?? null;
    
    // Validate required fields
    $errors = [];
    if (!$nome || !trim($nome)) {
        $errors[] = 'Nome da instituição é obrigatório.';
    } else if (strlen(trim($nome)) < 3) {
        $errors[] = 'Nome da instituição deve ter pelo menos 3 caracteres.';
    }
    
    if (!$descricao || !trim($descricao)) {
        $errors[] = 'Descrição é obrigatória.';
    } else if (strlen(trim($descricao)) < 10) {
        $errors[] = 'Descrição deve ter pelo menos 10 caracteres.';
    }
    
    if (!$localizacao_texto || !trim($localizacao_texto)) {
        $errors[] = 'Localização (texto) é obrigatória.';
    }
    
    if (!$telefone_contacto || !trim($telefone_contacto)) {
        $errors[] = 'Telefone de contacto é obrigatório.';
    }
    
    if (!$dias_aberto || !trim($dias_aberto)) {
        $errors[] = 'Dias abertos são obrigatórios.';
    }
    
    if (!$hora_abertura || !trim($hora_abertura)) {
        $errors[] = 'Hora de abertura é obrigatória.';
    }
    
    if (!$hora_fecho || !trim($hora_fecho)) {
        $errors[] = 'Hora de fecho é obrigatória.';
    }
    
    // Validate location format
    $lat = null;
    $lon = null;
    if (!$localizacao) {
        $errors[] = 'Localização (coordenadas) é obrigatória.';
    } else {
        if (is_string($localizacao)) {
            $parts = explode(',', $localizacao);
            if (count($parts) !== 2) {
                $errors[] = 'Formato de localização inválido. Use "latitude,longitude".';
            } else {
                $lat = (float)trim($parts[0]);
                $lon = (float)trim($parts[1]);
            }
        } else if (is_array($localizacao) || is_object($localizacao)) {
            $loc = (array)$localizacao;
            $lat = isset($loc['lat']) ? (float)$loc['lat'] : null;
            $lon = isset($loc['lon']) ? (float)$loc['lon'] : null;
        } else {
            $errors[] = 'Formato de localização inválido.';
        }
        
        // Validate coordinates
        if ($lat !== null && $lon !== null) {
            if (is_nan($lat) || is_nan($lon)) {
                $errors[] = 'Coordenadas devem ser números válidos.';
            } else {
                if ($lat < -90 || $lat > 90) {
                    $errors[] = 'Latitude deve estar entre -90 e 90.';
                }
                if ($lon < -180 || $lon > 180) {
                    $errors[] = 'Longitude deve estar entre -180 e 180.';
                }
            }
        }
    }
    
    // Validate time format
    if ($hora_abertura && $hora_fecho) {
        $timeRegex = '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/';
        if (!preg_match($timeRegex, trim($hora_abertura))) {
            $errors[] = 'Formato de hora de abertura inválido. Use HH:MM (24 horas).';
        }
        if (!preg_match($timeRegex, trim($hora_fecho))) {
            $errors[] = 'Formato de hora de fecho inválido. Use HH:MM (24 horas).';
        }
        
        // Check opening < closing
        if (preg_match($timeRegex, trim($hora_abertura)) && preg_match($timeRegex, trim($hora_fecho))) {
            list($openHour, $openMin) = array_map('intval', explode(':', trim($hora_abertura)));
            list($closeHour, $closeMin) = array_map('intval', explode(':', trim($hora_fecho)));
            $openTime = $openHour * 60 + $openMin;
            $closeTime = $closeHour * 60 + $closeMin;
            
            if ($openTime >= $closeTime) {
                $errors[] = 'Hora de abertura deve ser anterior à hora de fecho.';
            }
        }
    }
    
    if (!empty($errors)) {
        sendError(implode(' ', $errors), 400);
    }
    
    // Use placeholder if no image URL
    $placeholderImageUrl = 'img/placeholder.jpg';
    $finalImageUrl = ($url_imagem && trim($url_imagem)) ? trim($url_imagem) : $placeholderImageUrl;
    
    Database::beginTransaction();
    
    try {
        // Check for duplicate institution name
        $duplicate = Database::queryOne(
            'SELECT instituicao_id FROM instituicao WHERE LOWER(TRIM(nome)) = LOWER(TRIM(?)) LIMIT 1',
            [trim($nome)]
        );
        if ($duplicate) {
            Database::rollback();
            sendError('Já existe uma instituição com o nome "' . trim($nome) . '". Por favor, escolha um nome diferente.', 400);
        }
        
        // Insert institution with PostGIS geography
        $instituicao = Database::insert(
            'INSERT INTO instituicao (nome, descricao, localizacao_texto, telefone_contacto, url_imagem, "localização", dias_aberto, hora_abertura, hora_fecho)
             VALUES (?, ?, ?, ?, ?, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?, ?, ?)',
            [
                trim($nome),
                trim($descricao),
                trim($localizacao_texto),
                trim($telefone_contacto),
                $finalImageUrl,
                $lon,  // ST_MakePoint expects (longitude, latitude) - first parameter is longitude (X)
                $lat,  // Second parameter is latitude (Y)
                trim($dias_aberto),
                trim($hora_abertura),
                trim($hora_fecho)
            ]
        );
        
        Database::commit();
        
        sendJson([
            'message' => 'Instituição criada com sucesso.',
            'instituicao_id' => $instituicao['instituicao_id']
        ], 201);
    } catch (Exception $e) {
        Database::rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Erro ao criar instituição: ' . $e->getMessage());
    sendError('Erro ao criar instituição.', 500);
}
?>
