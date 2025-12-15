/**
 * API Configuration
 * Change API_BASE_URL to switch between localhost and Vercel deployment
 */

// Set to empty string for localhost, or your Vercel URL for production
const API_BASE_URL = 'https://bio-map-xi.vercel.app';

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
}
