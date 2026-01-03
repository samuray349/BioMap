/**
 * API Toggle Switch Handler
 * Allows users to switch between Node.js and PHP APIs
 */

// Initialize API toggle when DOM is ready
function initApiToggle() {
    const toggle = document.getElementById('api-toggle-switch');
    if (!toggle) {
        // Try again after a short delay if header hasn't loaded yet
        setTimeout(initApiToggle, 100);
        return;
    }
    
    // Set initial state based on localStorage or default
    const saved = localStorage.getItem('apiProvider');
    const currentProvider = (saved === 'nodejs' || saved === 'php') ? saved : 'nodejs';
    toggle.checked = currentProvider === 'php';
    
    // Add change event listener
    toggle.addEventListener('change', function() {
        const newProvider = this.checked ? 'php' : 'nodejs';
        
        // Save preference to localStorage
        localStorage.setItem('apiProvider', newProvider);
        
        // Reload the page to apply the new API provider
        window.location.reload();
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Wait a bit for header to load
        setTimeout(initApiToggle, 200);
    });
} else {
    // DOM is already ready, wait a bit for header
    setTimeout(initApiToggle, 200);
}
