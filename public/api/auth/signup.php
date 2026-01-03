<?php
/**
 * PHP Signup Endpoint
 * Registers a new user
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Check if PostgreSQL PDO extension is available
if (!extension_loaded('pdo_pgsql')) {
    error_log('PDO PostgreSQL extension (pdo_pgsql) is not loaded');
    http_response_code(500);
    echo json_encode(['error' => 'Servidor não configurado corretamente. Contacte o administrador.']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['name']) || !isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome, email e password são obrigatórios.']);
        exit();
    }
    
    $name = trim($input['name']);
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Basic validation
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome é obrigatório.']);
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email inválido.']);
        exit();
    }
    
    // Check if name already exists
    $nameCheck = executeQuerySingle(
        'SELECT utilizador_id FROM utilizador WHERE nome_utilizador = :name',
        [':name' => $name]
    );
    
    // Check if email already exists
    $emailCheck = executeQuerySingle(
        'SELECT utilizador_id FROM utilizador WHERE email = :email',
        [':email' => $email]
    );
    
    $nameExists = $nameCheck !== null;
    $emailExists = $emailCheck !== null;
    
    if ($nameExists && $emailExists) {
        http_response_code(409);
        echo json_encode([
            'error' => 'Nome e email já existentes',
            'nameExists' => true,
            'emailExists' => true
        ]);
        exit();
    }
    
    if ($nameExists) {
        http_response_code(409);
        echo json_encode([
            'error' => 'Este nome já existe',
            'nameExists' => true,
            'emailExists' => false
        ]);
        exit();
    }
    
    if ($emailExists) {
        http_response_code(409);
        echo json_encode([
            'error' => 'Este email já existe',
            'nameExists' => false,
            'emailExists' => true
        ]);
        exit();
    }
    
    // Hash password (using SHA256 like Node.js)
    $passwordHash = hash('sha256', $password);
    
    // Insert user into database
    // funcao_id = 2 means regular user, estado_id = 1 means normal/active
    $sql = 'INSERT INTO utilizador (
                nome_utilizador,
                email,
                password_hash,
                funcao_id,
                estado_id,
                data_criacao
            )
            VALUES (:name, :email, :password_hash, 2, 1, NOW())
            RETURNING utilizador_id';
    
    $userId = executeInsert($sql, [
        ':name' => $name,
        ':email' => $email,
        ':password_hash' => $passwordHash
    ]);
    
    // Return success response
    http_response_code(201);
    echo json_encode([
        'message' => 'Utilizador criado com sucesso.',
        'utilizador_id' => (int)$userId
    ]);
    
} catch (PDOException $e) {
    error_log('Signup database error: ' . $e->getMessage());
    
    // Check for duplicate key error
    if (strpos($e->getMessage(), 'duplicate key') !== false || 
        strpos($e->getMessage(), 'unique constraint') !== false) {
        http_response_code(409);
        echo json_encode(['error' => 'Nome ou email já existe.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao criar utilizador.']);
    }
} catch (Exception $e) {
    error_log('Signup error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao criar utilizador.']);
}
