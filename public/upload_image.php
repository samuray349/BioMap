<?php
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

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['imagem']) || !isset($input['imagem']['data'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados da imagem não fornecidos']);
        exit();
    }
    
    $base64Image = $input['imagem']['data'];
    $originalName = $input['imagem']['originalName'] ?? 'image.jpg';
    
    // Get folder type (default to 'animal' for backward compatibility)
    $folderType = $input['folder'] ?? $input['type'] ?? 'animal';
    
    // Validate folder type (only allow 'animal' or 'instituicao')
    $allowedFolders = ['animal', 'instituicao'];
    if (!in_array($folderType, $allowedFolders)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de pasta inválido. Use "animal" ou "instituicao"']);
        exit();
    }
    
    // Extract the base64 data (remove data:image/...;base64, prefix)
    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
        $imageType = $matches[1];
        $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
    } else {
        // Assume it's already base64 without prefix
        $imageType = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';
    }
    
    // Validate image type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $imageType = strtolower($imageType);
    if (!in_array($imageType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de imagem não suportado. Use JPG, PNG, GIF ou WEBP']);
        exit();
    }
    
    // Decode base64 image
    $imageData = base64_decode($base64Image);
    if ($imageData === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Erro ao descodificar a imagem']);
        exit();
    }
    
    // Validate file size (max 5MB)
    if (strlen($imageData) > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'Imagem muito grande. Tamanho máximo: 5MB']);
        exit();
    }
    
    // Generate unique filename based on folder type
    $prefix = $folderType === 'instituicao' ? 'instituicao_' : 'animal_';
    $filename = uniqid($prefix, true) . '.' . $imageType;
    $uploadDir = __DIR__ . '/' . $folderType . '/';
    
    // Ensure upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Save file
    $filepath = $uploadDir . $filename;
    if (file_put_contents($filepath, $imageData) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao guardar a imagem']);
        exit();
    }
    
    // Validate that it's actually an image
    $imageInfo = getimagesize($filepath);
    if ($imageInfo === false) {
        unlink($filepath);
        http_response_code(400);
        echo json_encode(['error' => 'Ficheiro não é uma imagem válida']);
        exit();
    }
    
    // Generate URL (adjust this to match your domain structure)
    // Get the base URL dynamically
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the script directory (public/)
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    // Ensure we use /public/{folder}/ or /{folder}/ depending on your setup
    // Since this file is in public/, the path should be /public/{folder}/filename
    $imageUrl = $protocol . '://' . $host . $scriptDir . '/' . $folderType . '/' . $filename;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'url' => $imageUrl,
        'filename' => $filename
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno: ' . $e->getMessage()]);
}
?>

