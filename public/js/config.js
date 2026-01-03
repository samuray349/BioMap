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
    // Force log to ensure function is called
    console.log('[PHP API] mapToPhpEndpoint called with:', nodeEndpoint);
    
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
    
    // Remove leading slash if present and trim whitespace
    const cleanEndpoint = (endpoint.startsWith('/') ? endpoint.substring(1) : endpoint).trim();
    
    console.log('[PHP API] Extracted endpoint:', cleanEndpoint, '| Query:', queryString);
    console.log('[PHP API] ENDPOINT_MAP exists?', typeof ENDPOINT_MAP !== 'undefined');
    if (typeof ENDPOINT_MAP !== 'undefined') {
        console.log('[PHP API] ENDPOINT_MAP keys:', Object.keys(ENDPOINT_MAP));
        console.log('[PHP API] Looking for:', JSON.stringify(cleanEndpoint));
        console.log('[PHP API] Has property?', ENDPOINT_MAP.hasOwnProperty(cleanEndpoint));
    }
    
    let phpEndpoint;
    
    // Check direct mapping first (without query string)
    // Use hasOwnProperty to ensure exact match and log if ENDPOINT_MAP is undefined
    if (typeof ENDPOINT_MAP === 'undefined') {
        console.error('[PHP API] ENDPOINT_MAP is undefined!');
        phpEndpoint = cleanEndpoint.replace(/\//g, '_') + '.php';
    } else if (ENDPOINT_MAP.hasOwnProperty(cleanEndpoint)) {
        phpEndpoint = ENDPOINT_MAP[cleanEndpoint];
        console.log('[PHP API] ✓ Found mapping:', cleanEndpoint, '→', phpEndpoint);
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
        const trimmedQuery = queryString.trim();
        if (trimmedQuery.length > 0) {
            // Safety check: ensure query string doesn't accidentally contain .php extension
            // This can happen if the endpoint mapping failed and .php got appended incorrectly
            let cleanQueryString = trimmedQuery;
            
            // Check if .php appears in the query string value (not just at the end of the whole string)
            // Example: "search=l.php" should become "search=l"
            if (cleanQueryString.includes('.php')) {
                console.warn(`[PHP API] Query string contains .php: "${cleanQueryString}" - cleaning it`);
                // Remove .php from query parameter values (e.g., search=l.php -> search=l)
                // Use regex to replace .php in parameter values while preserving the parameter structure
                cleanQueryString = cleanQueryString.replace(/([^=]+)=([^&]*?)\.php(&|$)/g, '$1=$2$3');
                cleanQueryString = cleanQueryString.replace(/\.php(&|$)/g, '$1'); // Remove any remaining .php
                console.warn(`[PHP API] Cleaned query string: "${cleanQueryString}"`);
            }
            
            const separator = phpEndpoint.includes('?') ? '&' : '?';
            phpEndpoint = phpEndpoint + separator + cleanQueryString;
        }
    }
    
    console.log('[PHP API] Final mapped endpoint:', phpEndpoint);
    return phpEndpoint;
}

// ============================================================================
// API URL BUILDER
// ============================================================================
function getApiBaseUrl() {
    return API_PROVIDER === 'php' ? PHP_API_BASE_URL : NODEJS_API_BASE_URL;
}

function getApiUrl(endpoint) {
    // Clean endpoint - remove leading slash if present
    let cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    
    if (API_PROVIDER === 'php') {
        // Map the endpoint to PHP file path
        const phpEndpoint = mapToPhpEndpoint(cleanEndpoint);
        
        // Remove leading slash from phpEndpoint if present
        const cleanPhpEndpoint = phpEndpoint.startsWith('/') ? phpEndpoint.substring(1) : phpEndpoint;
        
        // Ensure base URL doesn't have trailing slash
        const cleanBaseUrl = PHP_API_BASE_URL.endsWith('/') ? PHP_API_BASE_URL.slice(0, -1) : PHP_API_BASE_URL;
        
        // Construct final URL
        const finalUrl = `${cleanBaseUrl}/${cleanPhpEndpoint}`;
        
        // Debug logging for PHP API - always log to help diagnose issues
        if (typeof console !== 'undefined' && API_PROVIDER === 'php') {
            console.log('[PHP API URL Mapping]', {
                originalInput: endpoint,
                cleanEndpoint: cleanEndpoint,
                mappedPhpEndpoint: phpEndpoint,
                cleanPhpEndpoint: cleanPhpEndpoint,
                finalUrl: finalUrl,
                endpointMapExists: typeof ENDPOINT_MAP !== 'undefined',
                endpointInMap: typeof ENDPOINT_MAP !== 'undefined' && ENDPOINT_MAP.hasOwnProperty(cleanEndpoint.split('?')[0])
            });
        }
        
        return finalUrl;
    } else {
        // Node.js API - just prepend base URL
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
