<?php
/**
 * Session Helper - Manages user sessions in PHP
 * 
 * Usage:
 *   require_once 'session_helper.php';
 *   session_start();
 *   
 *   // Check if user is logged in
 *   if (isUserLoggedIn()) {
 *       $user = getCurrentUser();
 *       echo $user['name'];
 *   }
 */

/**
 * Start session if not already started
 */
function startSessionIfNotStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isUserLoggedIn() {
    startSessionIfNotStarted();
    return isset($_SESSION['biomap_user']) && !empty($_SESSION['biomap_user']);
}

/**
 * Get current logged in user data
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    startSessionIfNotStarted();
    return $_SESSION['biomap_user'] ?? null;
}

/**
 * Set user session data
 * @param array $userData User data to store in session
 */
function setUserSession($userData) {
    startSessionIfNotStarted();
    $_SESSION['biomap_user'] = $userData;
}

/**
 * Clear user session (logout)
 */
function clearUserSession() {
    startSessionIfNotStarted();
    unset($_SESSION['biomap_user']);
    session_destroy();
}

/**
 * Get user ID from session
 * @return int|null User ID or null if not logged in
 */
function getUserId() {
    $user = getCurrentUser();
    return $user['id'] ?? null;
}

/**
 * Get user name from session
 * @return string|null User name or null if not logged in
 */
function getUserName() {
    $user = getCurrentUser();
    return $user['name'] ?? null;
}

/**
 * Check if user is admin
 * @return bool True if user is admin (funcao_id === 1)
 */
function isAdmin() {
    $user = getCurrentUser();
    return isset($user['funcao_id']) && $user['funcao_id'] == 1;
}
