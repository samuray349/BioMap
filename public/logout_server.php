<?php
/**
 * Server-side logout endpoint
 * Clears PHP session
 */

require_once 'session_helper.php';

// Clear session
clearUserSession();

// Return success
header('Content-Type: application/json');
echo json_encode(['success' => true]);
