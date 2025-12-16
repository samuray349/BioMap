<?php
/**
 * Access Control System
 * Include this at the top of pages that require authentication/authorization
 * 
 * Usage:
 *   require_once 'access_control.php';
 *   checkAccess('user');   // Requires logged in user
 *   checkAccess('admin');  // Requires admin
 */

require_once 'session_helper.php';

// Define page access levels
define('ACCESS_PUBLIC', 'public');      // Anyone can access
define('ACCESS_USER', 'user');          // Requires login (any role)
define('ACCESS_ADMIN', 'admin');        // Requires admin (funcao_id = 1)

// Public pages (accessible without login)
$PUBLIC_PAGES = [
    'index.php',
    'animais.php',
    'sobre_nos.php',
    'doar.php',
    'animal_desc.php',
    'login.php',
    'sign_up.php',
    'esqueceu_password.php',
    'reset_password.php',
    'header.php',
    'get_session.php',
    'set_session.php',
    'logout_server.php',
    'upload_image.php',
    'session_helper.php',
    'access_control.php'
];

// User pages (requires login as any user)
$USER_PAGES = [
    'perfil.php',
    'editar_perfil.php',
    'atualizar_password.php',
    'logout.php',
    'apagar_perfil.php'
];

// Admin pages (requires admin role)
$ADMIN_PAGES = [
    'admin_util.php',
    'admin_animal.php',
    'adicionar_animal.php',
    'adicionar_fundacao.php',
    'perfil_admin.php'
];

/**
 * Check if current user has access to a page
 * @param string $requiredLevel - 'public', 'user', or 'admin'
 * @param string $redirectUrl - URL to redirect to if access denied
 */
function checkAccess($requiredLevel = ACCESS_USER, $redirectUrl = 'login.php') {
    $user = getCurrentUser();
    $isLoggedIn = $user !== null;
    $isAdmin = $isLoggedIn && isset($user['funcao_id']) && $user['funcao_id'] == 1;
    
    switch ($requiredLevel) {
        case ACCESS_PUBLIC:
            // Anyone can access
            return true;
            
        case ACCESS_USER:
            // Must be logged in
            if (!$isLoggedIn) {
                header('Location: ' . $redirectUrl);
                exit();
            }
            return true;
            
        case ACCESS_ADMIN:
            // Must be admin
            if (!$isLoggedIn) {
                header('Location: login.php');
                exit();
            }
            if (!$isAdmin) {
                header('Location: index.php');
                exit();
            }
            return true;
            
        default:
            // Unknown level - deny access
            header('Location: ' . $redirectUrl);
            exit();
    }
}

/**
 * Auto-check access based on current page filename
 * Call this to automatically determine and enforce access level
 */
function autoCheckAccess() {
    global $PUBLIC_PAGES, $USER_PAGES, $ADMIN_PAGES;
    
    // Get current filename
    $currentPage = basename($_SERVER['PHP_SELF']);
    
    // Check if page is public
    if (in_array($currentPage, $PUBLIC_PAGES)) {
        return true; // Allow access
    }
    
    // Check if page requires user login
    if (in_array($currentPage, $USER_PAGES)) {
        return checkAccess(ACCESS_USER);
    }
    
    // Check if page requires admin
    if (in_array($currentPage, $ADMIN_PAGES)) {
        return checkAccess(ACCESS_ADMIN);
    }
    
    // Default: require admin for any unlisted page (safer)
    return checkAccess(ACCESS_ADMIN);
}

/**
 * Get current user's access level
 * @return string 'admin', 'user', or 'public'
 */
function getUserAccessLevel() {
    $user = getCurrentUser();
    
    if (!$user) {
        return ACCESS_PUBLIC;
    }
    
    if (isset($user['funcao_id']) && $user['funcao_id'] == 1) {
        return ACCESS_ADMIN;
    }
    
    return ACCESS_USER;
}

/**
 * Check if user can access a specific page
 * @param string $pageName - The page filename
 * @return bool
 */
function canAccessPage($pageName) {
    global $PUBLIC_PAGES, $USER_PAGES, $ADMIN_PAGES;
    
    $accessLevel = getUserAccessLevel();
    
    // Public pages - anyone can access
    if (in_array($pageName, $PUBLIC_PAGES)) {
        return true;
    }
    
    // User pages - requires login
    if (in_array($pageName, $USER_PAGES)) {
        return $accessLevel === ACCESS_USER || $accessLevel === ACCESS_ADMIN;
    }
    
    // Admin pages - requires admin
    if (in_array($pageName, $ADMIN_PAGES)) {
        return $accessLevel === ACCESS_ADMIN;
    }
    
    // Unknown page - only admin can access
    return $accessLevel === ACCESS_ADMIN;
}
