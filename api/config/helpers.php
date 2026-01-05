<?php
/**
 * Helper functions for PHP API
 */

/**
 * Set CORS headers
 * Optimized to avoid unnecessary processing
 */
function setCorsHeaders() {
    // Only set headers if not already sent
    if (!headers_sent()) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');
        // Add cache control for API responses (can be overridden per endpoint if needed)
        header('Cache-Control: no-cache, must-revalidate');
    }
}

/**
 * Handle preflight OPTIONS requests
 */
function handlePreflight() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

/**
 * Get JSON input from request body
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

/**
 * Send JSON response
 */
function sendJson($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

/**
 * Send error response
 */
function sendError($message, $statusCode = 500) {
    sendJson(['error' => $message], $statusCode);
}

/**
 * Hash password using SHA256 (matching Node.js implementation)
 */
function hashPassword($password) {
    return hash('sha256', $password);
}

/**
 * Validate required fields
 */
function validateRequired($data, $fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        sendError('Campos obrigatórios: ' . implode(', ', $missing), 400);
    }
}

/**
 * Get query parameter
 */
function getQueryParam($name, $default = null) {
    return $_GET[$name] ?? $default;
}

/**
 * Delete image file from Hostinger by calling delete_image.php endpoint
 * @param string $imageUrl - Full URL of the image to delete
 * @param string $type - Type of image: 'animal' or 'instituicao' (determines which domain to use)
 * @return bool - True if deletion succeeded or image doesn't exist, false on error
 */
function deleteImageFile($imageUrl, $type = 'animal') {
    if (empty($imageUrl) || trim($imageUrl) === '' || $imageUrl === 'img/placeholder.jpg') {
        return true; // No image to delete
    }
    
    // Determine which domain to use based on type
    $deleteImageUrl = ($type === 'instituicao') 
        ? 'https://biomappt.com/public/delete_image.php'
        : 'https://lucped.antrob.eu/public/delete_image.php';
    
    try {
        // Use cURL to make the request
        $ch = curl_init($deleteImageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['image_url' => $imageUrl]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("[DELETE IMAGE] cURL error: {$curlError}");
            return false;
        }
        
        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['success']) && $responseData['success']) {
                error_log("[DELETE IMAGE] Image deleted successfully: {$imageUrl}");
                return true;
            } else {
                error_log("[DELETE IMAGE] Failed to delete image: " . ($responseData['error'] ?? 'Unknown error'));
                return false;
            }
        } else {
            error_log("[DELETE IMAGE] HTTP error {$httpCode} when deleting image: {$imageUrl}");
            return false;
        }
    } catch (Exception $e) {
        error_log("[DELETE IMAGE] Exception deleting image: " . $e->getMessage());
        return false;
    }
}
?>
