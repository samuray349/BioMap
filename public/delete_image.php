<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['image_url']) || empty($input['image_url'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'image_url is required']);
        exit;
    }
    
    $imageUrl = $input['image_url'];
    
    // Extract the file path from the URL
    // URL format: https://biomappt.com/public/animal/img_abc123.jpg
    // We need to get: animal/img_abc123.jpg
    
    // Parse the URL to get the path
    $parsedUrl = parse_url($imageUrl);
    $urlPath = $parsedUrl['path'] ?? '';
    
    // Extract the filename from the path
    // The path might be like /public/animal/filename.jpg or /animal/filename.jpg
    if (preg_match('#/animal/([^/]+)$#', $urlPath, $matches)) {
        $filename = $matches[1];
        $filePath = __DIR__ . '/animal/' . $filename;
        
        // Security check: ensure the file is within the animal directory
        $realPath = realpath($filePath);
        $animalDir = realpath(__DIR__ . '/animal/');
        
        if ($realPath && $animalDir && strpos($realPath, $animalDir) === 0) {
            // File exists and is in the correct directory
            if (file_exists($realPath)) {
                if (unlink($realPath)) {
                    echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Failed to delete image file']);
                }
            } else {
                // File doesn't exist, but that's okay - maybe it was already deleted
                echo json_encode(['success' => true, 'message' => 'Image file does not exist (already deleted)']);
            }
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied: invalid file path']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid image URL format']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

