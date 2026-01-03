/**
 * API Configuration with PHP/Node.js Switch
 * Allows switching between Node.js API (Vercel) and PHP API (Hostinger)
 * 
 * To switch: Change API_PROVIDER to 'nodejs' or 'php'
 */

// ============================================================================
// API PROVIDER CONFIGURATION
// ============================================================================
// Set to 'nodejs' to use Node.js API (Vercel) or 'php' to use PHP API (Railway)
// This can be overridden by localStorage preference set via header toggle
function getApiProvider() {
    if (typeof window !== 'undefined' && window.localStorage) {
        const saved = localStorage.getItem('apiProvider');
        if (saved === 'nodejs' || saved === 'php') {
            return saved;
        }
    }
    return 'nodejs'; // Default
}

const API_PROVIDER = getApiProvider();

// ============================================================================
// NODE.JS API CONFIGURATION (Vercel)
// ============================================================================
function detectNodeJsApiBaseUrl() {
    if (typeof window !== 'undefined') {
        const hostname = window.location.hostname;
        const isLocalhost = hostname === 'localhost' || 
                           hostname === '127.0.0.1' || 
                           hostname === '' ||
                           hostname.startsWith('192.168.') ||
                           hostname.startsWith('10.') ||
                           hostname.endsWith('.local');
        
        if (isLocalhost) {
            return 'http://localhost:3000';
        }
    }
    return 'https://bio-map-xi.vercel.app';
}

const NODEJS_API_BASE_URL = detectNodeJsApiBaseUrl();

// ============================================================================
// PHP API CONFIGURATION (Railway or Hostinger)
// ============================================================================
function getPhpApiBaseUrl() {
    // Option 1: Railway (external hosting)
    const RAILWAY_API_URL = 'https://biomap-production.up.railway.app';
    
    // Option 2: Hostinger (same domain) - fallback
    // const HOSTINGER_API_URL = window.location.origin + '/public/api/php';
    
    if (typeof window !== 'undefined') {
        // Use Railway URL (external PHP API)
        return RAILWAY_API_URL;
    }
    
    return RAILWAY_API_URL;
}

const PHP_API_BASE_URL = getPhpApiBaseUrl();

// ============================================================================
// ENDPOINT MAPPING (Node.js to PHP)
// ============================================================================
// Map Node.js endpoints to PHP file paths
// PHP API files are accessed directly (e.g., users/list.php)
const ENDPOINT_MAP = {
    'api/login': 'auth/login.php',
    'api/signup': 'auth/signup.php',
    'api/check-user': 'auth/check_user.php',
    'api/forgot-password': 'auth/forgot_password.php',
    'api/reset-password': 'auth/reset_password.php',
    'users': 'users/list.php',
    'users/estados': 'users/estados.php',
    'users/estatutos': 'users/estatutos.php',
    'users/:id': 'users/get.php',
    'users/:id/password': 'users/update_password.php',
    'users/:id/funcao': 'users/update_funcao.php',
    'users/:id/estado': 'users/update_estado.php',
    'animais': 'animais/list.php',
    'animais/familias': 'animais/familias.php',
    'animais/estados': 'animais/estados.php',
    'animaisDesc/:id': 'animais/get.php',
    'api/alerts': 'alerts/list.php',
    'instituicoes': 'instituicoes/list.php',
    'instituicoesDesc/:id': 'instituicoes/get.php'
};

/**
 * Map Node.js endpoint to PHP file path
 * Handles parameterized routes (e.g., users/123 -> users/get.php?id=123)
 */
function mapToPhpEndpoint(nodeEndpoint) {
    // Split endpoint and query string FIRST - handle empty query strings properly
    let endpoint = nodeEndpoint;
    let queryString = null;
    
    const questionMarkIndex = nodeEndpoint.indexOf('?');
    if (questionMarkIndex !== -1) {
        endpoint = nodeEndpoint.substring(0, questionMarkIndex);
        const afterQuestion = nodeEndpoint.substring(questionMarkIndex + 1);
        // Only set queryString if there's actual content after the ?
        if (afterQuestion && afterQuestion.trim().length > 0) {
            queryString = afterQuestion;
        }
    }
    
    // Remove leading slash if present
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    
    let phpEndpoint;
    
    // Check direct mapping first (without query string)
    // Use hasOwnProperty to ensure exact match
    if (Object.prototype.hasOwnProperty.call(ENDPOINT_MAP, cleanEndpoint)) {
        phpEndpoint = ENDPOINT_MAP[cleanEndpoint];
    } else {
        // Handle parameterized routes (e.g., users/123)
        let matched = false;
        for (const [nodePattern, phpFile] of Object.entries(ENDPOINT_MAP)) {
            if (nodePattern.includes(':')) {
                const regexPattern = '^' + nodePattern.replace(/:\w+/g, '([^/]+)') + '$';
                const regex = new RegExp(regexPattern);
                const match = cleanEndpoint.match(regex);
                
                if (match) {
                    // Extract ID parameter
                    const id = match[1];
                    // For PHP, use query parameters: users/get.php?id=123
                    phpEndpoint = phpFile + '?id=' + id;
                    matched = true;
                    break;
                }
            }
        }
        
        if (!matched) {
            // Default: convert endpoint to PHP file name (last resort)
            // But this shouldn't happen if all endpoints are mapped
            console.error(`[PHP API] No mapping found for endpoint: "${cleanEndpoint}"`);
            console.error(`[PHP API] Input endpoint was: "${nodeEndpoint}"`);
            console.error(`[PHP API] Available mappings:`, Object.keys(ENDPOINT_MAP));
            phpEndpoint = cleanEndpoint.replace(/\//g, '_') + '.php';
        }
    }
    
    // Re-append query string if it exists and is not empty (but handle existing query params in phpEndpoint)
    if (queryString && queryString.length > 0) {
        const separator = phpEndpoint.includes('?') ? '&' : '?';
        phpEndpoint = phpEndpoint + separator + queryString;
    }
    
    return phpEndpoint;
}

// ============================================================================
// API URL BUILDER
// ============================================================================
function getApiBaseUrl() {
    return API_PROVIDER === 'php' ? PHP_API_BASE_URL : NODEJS_API_BASE_URL;
}

function getApiUrl(endpoint) {
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    
    if (API_PROVIDER === 'php') {
        const phpEndpoint = mapToPhpEndpoint(cleanEndpoint);
        // Remove leading slash from phpEndpoint if present, then join with base URL
        const cleanPhpEndpoint = phpEndpoint.startsWith('/') ? phpEndpoint.substring(1) : phpEndpoint;
        // Ensure base URL doesn't have trailing slash
        const cleanBaseUrl = PHP_API_BASE_URL.endsWith('/') ? PHP_API_BASE_URL.slice(0, -1) : PHP_API_BASE_URL;
        const finalUrl = `${cleanBaseUrl}/${cleanPhpEndpoint}`;
        
        // Debug logging for PHP API - always log to help diagnose issues
        if (typeof console !== 'undefined' && API_PROVIDER === 'php') {
            console.log('[PHP API URL Mapping]', {
                originalInput: endpoint,
                cleanEndpoint: cleanEndpoint,
                mappedPhpEndpoint: phpEndpoint,
                cleanPhpEndpoint: cleanPhpEndpoint,
                finalUrl: finalUrl
            });
        }
        
        return finalUrl;
    } else {
        return `${NODEJS_API_BASE_URL}/${cleanEndpoint}`;
    }
}

// ============================================================================
// EXPORT CONFIGURATION
// ============================================================================
if (typeof window !== 'undefined') {
    window.API_CONFIG = {
        PROVIDER: API_PROVIDER,
        BASE_URL: getApiBaseUrl(),
        getUrl: getApiUrl,
        isNodeJs: () => API_PROVIDER === 'nodejs',
        isPhp: () => API_PROVIDER === 'php',
        switchToNodeJs: () => {
            console.warn('API_PROVIDER is read-only. Change it in config.js file.');
        },
        switchToPhp: () => {
            console.warn('API_PROVIDER is read-only. Change it in config.js file.');
        }
    };
    
    // Log API configuration for debugging
    console.log(`API Configuration: Using ${API_PROVIDER.toUpperCase()} API`);
    console.log(`Base URL: ${getApiBaseUrl()}`);
}
