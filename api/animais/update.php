<?php
/**
 * PUT /animais/:id
 * Update an animal
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
        preg_match('#/animais/(\d+)#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    $nome_comum = $input['nome_comum'] ?? null;
    $nome_cientifico = $input['nome_cientifico'] ?? null;
    $descricao = $input['descricao'] ?? null;
    $facto_interessante = $input['facto_interessante'] ?? null;
    $populacao_estimada = $input['populacao_estimada'] ?? null;
    $familia_nome = $input['familia_nome'] ?? null;
    $dieta_nome = $input['dieta_nome'] ?? null;
    $estado_nome = $input['estado_nome'] ?? null;
    $ameacas = $input['ameacas'] ?? [];
    $imagem_url = $input['imagem_url'] ?? null;
    
    // Validate required fields
    $errors = [];
    if (!$nome_comum || !trim($nome_comum)) $errors[] = 'Nome comum é obrigatório.';
    if (!$nome_cientifico || !trim($nome_cientifico)) $errors[] = 'Nome científico é obrigatório.';
    if (!$descricao || !trim($descricao)) $errors[] = 'Descrição é obrigatória.';
    if (!$familia_nome || !trim($familia_nome)) $errors[] = 'Família é obrigatória.';
    if (!$dieta_nome || !trim($dieta_nome)) $errors[] = 'Dieta é obrigatória.';
    if (!$estado_nome || !trim($estado_nome)) $errors[] = 'Estado de conservação é obrigatório.';
    
    // Validate ameacas - must be exactly 5 non-empty threats
    if (!is_array($ameacas)) {
        $errors[] = 'Ameaças devem ser fornecidas como um array.';
    } else {
        $nonEmptyThreats = array_filter(array_map(function($t) {
            return trim($t ?? '');
        }, $ameacas), function($t) {
            return strlen($t) > 0;
        });
        if (count($nonEmptyThreats) !== 5) {
            $errors[] = 'Deve fornecer exatamente 5 ameaças. Fornecidas: ' . count($nonEmptyThreats) . '.';
        }
    }
    
    if (!empty($errors)) {
        sendError(implode(' ', $errors), 400);
    }
    
    // Normalize population
    $normalizedPopulation = null;
    if ($populacao_estimada !== null) {
        if (is_numeric($populacao_estimada)) {
            $normalizedPopulation = (int)$populacao_estimada;
        } else {
            $normalizedPopulation = (int)preg_replace('/[^\d]/', '', $populacao_estimada);
            if ($normalizedPopulation === 0) $normalizedPopulation = null;
        }
    }
    
    // Validate population range
    if ($normalizedPopulation !== null && ($normalizedPopulation > 2147483647 || $normalizedPopulation < -2147483648)) {
        sendError('População estimada está fora do intervalo permitido. O valor deve estar entre -2,147,483,648 e 2,147,483,647.', 400);
    }
    
    Database::beginTransaction();
    
    try {
        // Check if animal exists
        $animalCheck = Database::queryOne(
            'SELECT animal_id FROM animal WHERE animal_id = $1',
            [$id]
        );
        if (!$animalCheck) {
            Database::rollback();
            sendError('Animal não encontrado.', 404);
        }
        
        // Get familia_id
        $familia = Database::queryOne(
            'SELECT familia_id FROM familia WHERE TRIM(nome_familia) = TRIM($1) LIMIT 1',
            [trim($familia_nome)]
        );
        if (!$familia) {
            Database::rollback();
            sendError('Família "' . trim($familia_nome) . '" não encontrada na base de dados. Por favor, selecione uma família válida.', 400);
        }
        
        // Get dieta_id
        $dieta = Database::queryOne(
            'SELECT dieta_id FROM dieta WHERE TRIM(nome_dieta) = TRIM($1) LIMIT 1',
            [trim($dieta_nome)]
        );
        if (!$dieta) {
            Database::rollback();
            sendError('Dieta "' . trim($dieta_nome) . '" não encontrada na base de dados. Por favor, selecione uma dieta válida.', 400);
        }
        
        // Get estado_id
        $estado = Database::queryOne(
            'SELECT estado_id FROM estado_conservacao WHERE TRIM(nome_estado) = TRIM($1) LIMIT 1',
            [trim($estado_nome)]
        );
        if (!$estado) {
            Database::rollback();
            sendError('Estado de conservação "' . trim($estado_nome) . '" não encontrado na base de dados. Por favor, selecione um estado válido.', 400);
        }
        
        // Update animal
        if ($imagem_url !== null && $imagem_url !== '') {
            Database::execute(
                'UPDATE animal SET nome_comum = $1, nome_cientifico = $2, descricao = $3, facto_interessante = $4, populacao_estimada = $5, dieta_id = $6, familia_id = $7, estado_id = $8, url_imagem = $9 WHERE animal_id = $10',
                [
                    trim($nome_comum),
                    trim($nome_cientifico),
                    trim($descricao),
                    trim($facto_interessante ?? ''),
                    $normalizedPopulation,
                    $dieta['dieta_id'],
                    $familia['familia_id'],
                    $estado['estado_id'],
                    trim($imagem_url),
                    $id
                ]
            );
        } else {
            Database::execute(
                'UPDATE animal SET nome_comum = $1, nome_cientifico = $2, descricao = $3, facto_interessante = $4, populacao_estimada = $5, dieta_id = $6, familia_id = $7, estado_id = $8 WHERE animal_id = $9',
                [
                    trim($nome_comum),
                    trim($nome_cientifico),
                    trim($descricao),
                    trim($facto_interessante ?? ''),
                    $normalizedPopulation,
                    $dieta['dieta_id'],
                    $familia['familia_id'],
                    $estado['estado_id'],
                    $id
                ]
            );
        }
        
        // Handle ameacas - delete existing and insert new
        Database::execute(
            'DELETE FROM animal_ameaca WHERE animal_id = $1',
            [$id]
        );
        
        if (!empty($ameacas)) {
            $uniqueThreats = array_slice(array_unique(array_filter(array_map(function($t) {
                return trim($t ?? '');
            }, $ameacas), function($t) {
                return strlen($t) > 0;
            })), 0, 5);
            
            foreach ($uniqueThreats as $threat) {
                // Check if threat exists
                $existing = Database::queryOne(
                    'SELECT ameaca_id FROM ameaca WHERE descricao = $1 LIMIT 1',
                    [$threat]
                );
                
                if ($existing) {
                    $threatId = $existing['ameaca_id'];
                } else {
                    // Create new threat
                    $newThreat = Database::insert(
                        'INSERT INTO ameaca (descricao) VALUES ($1)',
                        [$threat]
                    );
                    $threatId = $newThreat['ameaca_id'];
                }
                
                // Link threat to animal
                try {
                    Database::execute(
                        'INSERT INTO animal_ameaca (animal_id, ameaca_id) VALUES ($1, $2)',
                        [$id, $threatId]
                    );
                } catch (Exception $e) {
                    // Ignore duplicate key errors
                    if (strpos($e->getMessage(), 'duplicate') === false && strpos($e->getMessage(), 'unique') === false) {
                        throw $e;
                    }
                }
            }
        }
        
        Database::commit();
        
        sendJson(['message' => 'Animal atualizado com sucesso.']);
    } catch (Exception $e) {
        Database::rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Erro ao atualizar animal: ' . $e->getMessage());
    sendError('Erro ao atualizar animal.', 500);
}
?>
