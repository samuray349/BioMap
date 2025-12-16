<?php
/**
 * API Endpoint: Get current session data as JSON
 * Can be called from JavaScript via fetch()
 * 
 * Usage in JavaScript:
 *   fetch('get_session.php')
 *     .then(r => r.json())
 *     .then(data => {
 *       if (data.loggedIn) {
 *         console.log(data.user);
 *       }
 *     });
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'session_helper.php';

$user = getCurrentUser();

if ($user) {
    echo json_encode([
        'loggedIn' => true,
        'user' => $user
    ]);
} else {
    echo json_encode([
        'loggedIn' => false,
        'user' => null
    ]);
}
