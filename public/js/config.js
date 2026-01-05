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
const NODEJS_API_BASE_URL = 'https://bio-map-xi.vercel.app';

// ============================================================================
// PHP API CONFIGURATION (Railway)
// ============================================================================

const PHP_API_BASE_URL = 'https://biomap-production.up.railway.app'

// ============================================================================
// ENDPOINT MAPPING (Node.js to PHP)
// ============================================================================
// Map Node.js endpoints to PHP file paths
// PHP API files are accessed directly (e.g., users/list.php)
const ENDPOINT_MAP = {
    'api/login': 'api/login', // Keep as-is for router to handle
    'api/signup': 'api/signup', // Keep as-is for router to handle
    'api/check-user': 'api/check-user', // Keep as-is for router to handle
    // Password reset endpoints removed - always use Node.js API
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
    
    let phpEndpoint;
    let directMappingFound = false;
    
    // Check direct mapping first (without query string)
    // Use hasOwnProperty to ensure exact match
    if (typeof ENDPOINT_MAP === 'undefined') {
        phpEndpoint = cleanEndpoint.replace(/\//g, '_') + '.php';
        directMappingFound = false;
    } else {
        const hasMapping = ENDPOINT_MAP.hasOwnProperty(cleanEndpoint);
        if (hasMapping) {
            phpEndpoint = ENDPOINT_MAP[cleanEndpoint];
            directMappingFound = true;
            // For base paths like 'api/alerts', keep them as-is so router can handle GET/POST
        } else {
            directMappingFound = false;
        }
    }
    
    // Only do fallback mapping if direct mapping wasn't found
    if (!directMappingFound) {
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
        
        // Only do fallback if no parameterized route matched
        if (!matched) {
            // Default: convert endpoint to PHP file name (last resort)
            // But this shouldn't happen if all endpoints are mapped
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
                // For api/alerts, keep as api/alerts (router handles GET/POST)
                // This fallback should only happen if direct mapping wasn't found
                if (cleanEndpoint === 'api/alerts') {
                    // This should have been caught by direct mapping, but if we're here, use router path
                    phpEndpoint = 'api/alerts'; // Keep base path for router
                } else {
                    // Convert other api/* endpoints to */list.php
                    phpEndpoint = cleanEndpoint.replace(/^api\//, '') + '/list.php';
                }
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
            
            const separator = phpEndpoint.includes('?') ? '&' : '?';
            phpEndpoint = phpEndpoint + separator + cleanQueryString;
        }
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
    // Clean endpoint - remove leading slash if present
    let cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    
    // Password reset endpoints always use Node.js API, even if PHP is selected
    const passwordResetEndpoints = ['api/forgot-password', 'api/reset-password'];
    const isPasswordReset = passwordResetEndpoints.some(ep => cleanEndpoint.startsWith(ep));
    
    // Force Node.js API for password reset
    if (isPasswordReset) {
        return `${NODEJS_API_BASE_URL}/${cleanEndpoint}`;
    }
    
    if (API_PROVIDER === 'php') {
        // Map the endpoint to PHP file path
        const phpEndpoint = mapToPhpEndpoint(cleanEndpoint);
        
        // Remove leading slash from phpEndpoint if present
        let cleanPhpEndpoint = phpEndpoint.startsWith('/') ? phpEndpoint.substring(1) : phpEndpoint;
        
        // If endpoint maps back to itself (e.g., 'api/login' → 'api/login'), it means router handles it
        // Keep the original endpoint structure for router-based endpoints
        if (phpEndpoint === cleanEndpoint && cleanEndpoint.startsWith('api/')) {
            cleanPhpEndpoint = cleanEndpoint;
        }
        
        // Ensure base URL doesn't have trailing slash
        const cleanBaseUrl = PHP_API_BASE_URL.endsWith('/') ? PHP_API_BASE_URL.slice(0, -1) : PHP_API_BASE_URL;
        
        // Construct final URL - router expects paths like /api/login
        const finalUrl = `${cleanBaseUrl}/${cleanPhpEndpoint}`;
        
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
            // API_PROVIDER is read-only. Change it in config.js file.
        },
        switchToPhp: () => {
            // API_PROVIDER is read-only. Change it in config.js file.
        }
    };
}
