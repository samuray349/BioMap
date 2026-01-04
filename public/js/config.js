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
    'users': 'users', // Base path - router handles GET/POST based on method
    'users/list.php': 'users/list.php', // Direct file access
    'users/estados': 'users/estados.php',
    'users/estatutos': 'users/estatutos.php',
    'users/:id': 'users/get.php',
    'users/:id/password': 'users/update_password.php',
    'users/:id/funcao': 'users/update_funcao.php',
    'users/:id/estado': 'users/update_estado.php',
    'animais': 'animais', // Base path - router handles GET/POST based on method
    'animais/list.php': 'animais/list.php', // Direct file access
    'animais/familias': 'animais/familias.php',
    'animais/estados': 'animais/estados.php',
    'animaisDesc/:id': 'animais/get.php',
    'animais/:id': 'animais/update.php', // For PUT/DELETE, router will handle method
    'api/alerts': 'api/alerts', // Base path - router handles GET/POST based on method
    'alerts/list.php': 'alerts/list.php', // Direct file access
    'api/alerts/:id': 'alerts/delete.php', // For DELETE, router will handle method
    'instituicoes': 'instituicoes', // Base path - router handles GET/POST based on method
    'instituicoes/list.php': 'instituicoes/list.php', // Direct file access
    'instituicoesDesc/:id': 'instituicoes/get.php',
    'instituicoes/:id': 'instituicoes/update.php' // For PUT/DELETE, router will handle method
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
                    
                    // For animais/:id, users/:id, instituicoes/:id, and api/alerts/:id, keep path structure for PUT/DELETE
                    // Router expects /animais/4, /users/4, /instituicoes/4, or /api/alerts/4 for PUT/DELETE
                    if ((nodePattern === 'animais/:id' || nodePattern === 'users/:id' || 
                         nodePattern === 'instituicoes/:id' || nodePattern === 'api/alerts/:id') && 
                        (cleanEndpoint.startsWith('animais/') || cleanEndpoint.startsWith('users/') ||
                         cleanEndpoint.startsWith('instituicoes/') || cleanEndpoint.startsWith('api/alerts/'))) {
                        // Keep as-is: animais/4, users/4, instituicoes/4, or api/alerts/4 (router will handle method)
                        phpEndpoint = cleanEndpoint;
                    } else {
                        // For GET requests, use query parameters: animais/get.php?id=123
                        phpEndpoint = phpFile + '?id=' + id;
                    }
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
            // Don't add .php if it already ends with .php
            if (cleanEndpoint.endsWith('.php')) {
                phpEndpoint = cleanEndpoint;
            } else {
            // Handle different endpoint formats
            if (cleanEndpoint.startsWith('api_')) {
                // Convert api_alerts to alerts/list.php
                const withoutApi = cleanEndpoint.replace(/^api_/, '');
                phpEndpoint = withoutApi + '/list.php';
            } else if (cleanEndpoint.startsWith('api/')) {
                // Convert api/alerts to alerts/list.php
                phpEndpoint = cleanEndpoint.replace(/^api\//, '') + '/list.php';
            } else if (cleanEndpoint.includes('/')) {
                // Already has path structure, check if it matches a known endpoint
                // For endpoints like "animais" that map to list.php, ensure we use the correct path
                if (ENDPOINT_MAP.hasOwnProperty(cleanEndpoint.split('/')[0])) {
                    // If the first part is in the map, use the mapped path
                    const firstPart = cleanEndpoint.split('/')[0];
                    if (ENDPOINT_MAP[firstPart].endsWith('/list.php')) {
                        phpEndpoint = cleanEndpoint.replace(/^[^/]+\//, ENDPOINT_MAP[firstPart].replace('/list.php', '/'));
                        if (!phpEndpoint.endsWith('.php')) {
                            phpEndpoint += 'list.php';
                        }
                    } else {
                        phpEndpoint = cleanEndpoint + '.php';
                    }
                } else {
                    phpEndpoint = cleanEndpoint + '.php';
                }
            } else {
                // Single word, check if it's in the map first
                if (ENDPOINT_MAP.hasOwnProperty(cleanEndpoint)) {
                    phpEndpoint = ENDPOINT_MAP[cleanEndpoint];
                } else {
                    // Single word, add /list.php
                    phpEndpoint = cleanEndpoint + '/list.php';
                }
            }
            }
        }
    }
    
    // IMPORTANT: Clean query string BEFORE appending to prevent .php contamination
    // Re-append query string if it exists and is not empty (but handle existing query params in phpEndpoint)
    if (queryString && queryString.length > 0) {
        let trimmedQuery = queryString.trim();
        if (trimmedQuery.length > 0) {
            // CRITICAL: Remove .php from query parameter VALUES
            // Split by & to handle multiple parameters
            const params = trimmedQuery.split('&');
            const cleanedParams = params.map(param => {
                if (param.includes('=')) {
                    const [key, ...valueParts] = param.split('=');
                    let value = valueParts.join('='); // Rejoin in case value has = in it
                    // URL decode, clean .php, then re-encode
                    try {
                        value = decodeURIComponent(value);
                        // Remove .php from anywhere in the value (not just at the end)
                        value = value.replace(/\.php/g, '');
                        value = encodeURIComponent(value);
                    } catch (e) {
                        // If decoding fails, just remove .php directly
                        value = value.replace(/\.php/g, '');
                    }
                    return key + '=' + value;
                }
                // If no = sign, it might be just a value - still clean it
                return param.replace(/\.php/g, '');
            });
            
            const cleanQueryString = cleanedParams.join('&');
            
            if (cleanQueryString !== trimmedQuery) {
                console.warn(`[PHP API] Cleaned query string from "${trimmedQuery}" to "${cleanQueryString}"`);
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
