/**
 * API Configuration
 * Auto-detects environment and uses appropriate API URL
 */

// Auto-detect environment
function detectApiBaseUrl() {
    // Check if we're on localhost
    if (typeof window !== 'undefined') {
        const hostname = window.location.hostname;
        const isLocalhost = hostname === 'localhost' || 
                           hostname === '127.0.0.1' || 
                           hostname === '' ||
                           hostname.startsWith('192.168.') ||
                           hostname.startsWith('10.') ||
                           hostname.endsWith('.local');
        
        if (isLocalhost) {
            // Use local Node.js server
            return 'http://localhost:3000';
        }
    }
    
    // Production: use Vercel API
    return 'https://bio-map-xi.vercel.app';
}

const API_BASE_URL = detectApiBaseUrl();

// Helper function to build API URLs
function getApiUrl(endpoint) {
    // Remove leading slash if present to avoid double slashes
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    return `${API_BASE_URL}/${cleanEndpoint}`;
}

// Export for use in other scripts
if (typeof window !== 'undefined') {
    window.API_CONFIG = {
        BASE_URL: API_BASE_URL,
        getUrl: getApiUrl
    };
    
    // Log API configuration for debugging (only in development)
    if (API_BASE_URL.includes('localhost')) {
        console.log('API Configuration: Using localhost API at', API_BASE_URL);
    }
}
