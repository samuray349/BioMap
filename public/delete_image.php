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
    
    $imageUrl = trim($input['image_url']);
    
    // Log the received URL for debugging (remove in production if needed)
    error_log("Delete image request received for URL: " . $imageUrl);
    
    // Extract the file path from the URL
    // URL format could be:
    // - https://biomappt.com/public/animal/filename.jpg
    // - https://biomappt.com/animal/filename.jpg
    // - /public/animal/filename.jpg (relative)
    
    // Parse the URL to get the path
    $parsedUrl = parse_url($imageUrl);
    $urlPath = $parsedUrl['path'] ?? '';
    
    // If URL doesn't have a scheme, it might be a relative path
    if (empty($parsedUrl['scheme']) && !empty($imageUrl)) {
        // Assume it's a path starting with /
        $urlPath = $imageUrl;
    }
    
    error_log("Parsed path: " . $urlPath);
    
    // Extract the filename from the path
    // The path might be like /public/animal/filename.jpg or /animal/filename.jpg
    // Match any path ending with /animal/filename (with or without extension)
    if (preg_match('#/animal/([^/]+)$#', $urlPath, $matches)) {
        $filename = $matches[1];
        
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        
        $animalDir = __DIR__ . '/animal/';
        $filePath = $animalDir . $filename;
        
        // Ensure animal directory exists
        if (!is_dir($animalDir)) {
            // Directory doesn't exist, so image doesn't exist either
            echo json_encode(['success' => true, 'message' => 'Image directory does not exist (already deleted)']);
            exit;
        }
        
        // Security check: ensure the file path is within the animal directory
        // First check if file exists
        if (!file_exists($filePath)) {
            // File doesn't exist, but that's okay - maybe it was already deleted
            error_log("File does not exist (already deleted?): " . $filePath);
            echo json_encode(['success' => true, 'message' => 'Image file does not exist (already deleted)']);
            exit;
        }
        
        // Now verify the file is actually in the animal directory (security check)
        $realPath = realpath($filePath);
        $realAnimalDir = realpath($animalDir);
        
        error_log("Attempting to delete file: " . $filePath);
        error_log("Real path: " . ($realPath ? $realPath : 'NULL'));
        error_log("Animal dir: " . ($realAnimalDir ? $realAnimalDir : 'NULL'));
        
        if ($realAnimalDir && $realPath && strpos($realPath, $realAnimalDir) === 0) {
            // File exists and is in the correct directory - safe to delete
            if (unlink($realPath)) {
                error_log("File deleted successfully: " . $realPath);
                echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
            } else {
                error_log("Failed to unlink file: " . $realPath);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete image file', 'path' => $realPath]);
            }
        } else {
            // Path doesn't resolve correctly (security issue)
            error_log("Security check failed. RealPath: " . ($realPath ?: 'NULL') . ", RealAnimalDir: " . ($realAnimalDir ?: 'NULL'));
            http_response_code(403);
            echo json_encode([
                'success' => false, 
                'error' => 'Access denied: invalid file path',
                'filePath' => $filePath,
                'realPath' => $realPath ?: 'null',
                'animalDir' => $realAnimalDir ?: 'null'
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid image URL format. Expected URL containing /animal/']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

