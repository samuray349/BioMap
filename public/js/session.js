/**
 * Session Helper for JavaScript
 * Works with PHP session_helper.php using cookies
 */

/**
 * Get a cookie value by name
 * @param {string} name Cookie name
 * @returns {string|null} Cookie value or null if not found
 */
function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) {
            return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
    }
    return null;
}

/**
 * Set a cookie
 * @param {string} name Cookie name
 * @param {string} value Cookie value
 * @param {number} days Days until expiration
 */
function setCookie(name, value, days = 7) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
}

/**
 * Delete a cookie
 * @param {string} name Cookie name
 */
function deleteCookie(name) {
    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}

/**
 * Get current user from cookie (synced with PHP session)
 * @returns {Object|null} User object or null if not logged in
 */
function getCurrentUserFromCookie() {
    const userData = getCookie('biomap_user');
    if (!userData) {
        return null;
    }
    
    try {
        return JSON.parse(userData);
    } catch (e) {
        console.error('Error parsing user cookie:', e);
        return null;
    }
}

/**
 * Check if user is logged in (from cookie)
 * @returns {boolean}
 */
function isUserLoggedInFromCookie() {
    const user = getCurrentUserFromCookie();
    return user !== null && user !== undefined;
}

/**
 * Check if current user is admin (from cookie)
 * @returns {boolean}
 */
function isAdminFromCookie() {
    const user = getCurrentUserFromCookie();
    return user && user.funcao_id === 1;
}

/**
 * Set user session cookie (called after login)
 * @param {Object} userData User data object
 */
function setUserCookie(userData) {
    setCookie('biomap_user', JSON.stringify(userData), 7);
}

/**
 * Clear user session cookie (called on logout)
 */
function clearUserCookie() {
    deleteCookie('biomap_user');
    // Also clear localStorage if it exists
    localStorage.removeItem('biomapUser');
}

// Export functions for use in other scripts
if (typeof window !== 'undefined') {
    window.SessionHelper = {
        getCookie,
        setCookie,
        deleteCookie,
        getCurrentUser: getCurrentUserFromCookie,
        isLoggedIn: isUserLoggedInFromCookie,
        isAdmin: isAdminFromCookie,
        setUser: setUserCookie,
        clearUser: clearUserCookie
    };
}
