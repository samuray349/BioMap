<?php
/**
 * PUT /instituicoes/:id
 * Update an institution
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendError('Method not allowed', 405);
}

try {
    $input = getJsonInput();
    
    // Get ID from query string or path
    $id = getQueryParam('id');
    if (!$id) {
        preg_match('#/instituicoes/(\d+)#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
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
    
    // Validate location format (optional for update)
    $lat = null;
    $lon = null;
    if ($localizacao !== null) {
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
    
    Database::beginTransaction();
    
    try {
        // Check if institution exists
        $existing = Database::queryOne(
            'SELECT instituicao_id FROM instituicao WHERE instituicao_id = $1',
            [$id]
        );
        if (!$existing) {
            Database::rollback();
            sendError('Instituição não encontrada.', 404);
        }
        
        // Check for duplicate name (excluding current)
        $duplicate = Database::queryOne(
            'SELECT instituicao_id FROM instituicao WHERE LOWER(TRIM(nome)) = LOWER(TRIM($1)) AND instituicao_id != $2 LIMIT 1',
            [trim($nome), $id]
        );
        if ($duplicate) {
            Database::rollback();
            sendError('Já existe uma instituição com o nome "' . trim($nome) . '". Por favor, escolha um nome diferente.', 400);
        }
        
        // Build update query dynamically
        $updateFields = [
            'nome' => trim($nome),
            'descricao' => trim($descricao),
            'localizacao_texto' => trim($localizacao_texto),
            'telefone_contacto' => trim($telefone_contacto),
            'dias_aberto' => trim($dias_aberto),
            'hora_abertura' => trim($hora_abertura),
            'hora_fecho' => trim($hora_fecho)
        ];
        
        $params = array_values($updateFields);
        $paramCounter = count($params) + 1;
        
        $sql = 'UPDATE instituicao SET nome = $1, descricao = $2, localizacao_texto = $3, telefone_contacto = $4, dias_aberto = $5, hora_abertura = $6, hora_fecho = $7';
        
        // Add url_imagem if provided
        if ($url_imagem !== null) {
            $sql .= ', url_imagem = $' . $paramCounter;
            $params[] = trim($url_imagem) ?: 'img/placeholder.jpg';
            $paramCounter++;
        }
        
        // Add location if provided
        if ($localizacao !== null && $lat !== null && $lon !== null && !is_nan($lat) && !is_nan($lon)) {
            $sql .= ', "localização" = ST_SetSRID(ST_MakePoint($' . $paramCounter . ', $' . ($paramCounter + 1) . '), 4326)::geography';
            $params[] = $lon;
            $params[] = $lat;
            $paramCounter += 2;
        }
        
        $sql .= ' WHERE instituicao_id = $' . $paramCounter;
        $params[] = $id;
        
        Database::execute($sql, $params);
        
        Database::commit();
        
        sendJson(['message' => 'Instituição atualizada com sucesso.']);
    } catch (Exception $e) {
        Database::rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Erro ao atualizar instituição: ' . $e->getMessage());
    sendError('Erro ao atualizar instituição.', 500);
}
?>
