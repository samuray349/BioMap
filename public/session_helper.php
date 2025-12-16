<?php
/**
 * Session Helper - Hybrid Session Management (PHP Sessions + Cookies)
 * 
 * Uses both PHP sessions and cookies so data is accessible from both PHP and JavaScript
 * 
 * Usage in PHP:
 *   require_once 'session_helper.php';
 *   if (isUserLoggedIn()) {
 *       $user = getCurrentUser();
 *   }
 * 
 * Usage in JavaScript:
 *   // Read from cookie
 *   const userData = getCookie('biomap_user');
 *   const user = userData ? JSON.parse(decodeURIComponent(userData)) : null;
 */

// Session name constant
define('SESSION_COOKIE_NAME', 'biomap_user');
define('SESSION_NAME', 'biomap_session');

/**
 * Start session if not already started
 */
function startSessionIfNotStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

/**
 * Set user session data (both PHP session and cookie)
 * @param array $userData User data to store
 */
function setUserSession($userData) {
    startSessionIfNotStarted();
    
    // Store in PHP session
    $_SESSION['biomap_user'] = $userData;
    
    // Store in cookie (readable by JavaScript)
    // Use httpOnly: false so JavaScript can read it
    // Secure: true in production (requires HTTPS)
    $cookieData = json_encode($userData);
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    setcookie(
        SESSION_COOKIE_NAME,
        $cookieData,
        [
            'expires' => time() + (86400 * 7), // 7 days
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => false, // Allow JavaScript to read
            'samesite' => 'Lax'
        ]
    );
}

/**
 * Get current logged in user data
 * Tries PHP session first, then cookie
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    startSessionIfNotStarted();
    
    // Try PHP session first
    if (isset($_SESSION['biomap_user']) && !empty($_SESSION['biomap_user'])) {
        return $_SESSION['biomap_user'];
    }
    
    // Fallback to cookie
    if (isset($_COOKIE[SESSION_COOKIE_NAME])) {
        $userData = json_decode($_COOKIE[SESSION_COOKIE_NAME], true);
        if ($userData) {
            // Sync back to session
            $_SESSION['biomap_user'] = $userData;
            return $userData;
        }
    }
    
    return null;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isUserLoggedIn() {
    $user = getCurrentUser();
    return $user !== null && !empty($user);
}

/**
 * Clear user session (logout) - removes both session and cookie
 */
function clearUserSession() {
    startSessionIfNotStarted();
    
    // Clear PHP session
    unset($_SESSION['biomap_user']);
    session_destroy();
    
    // Clear cookie
    setcookie(SESSION_COOKIE_NAME, '', time() - 3600, '/');
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
