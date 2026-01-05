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
    // Read from localStorage which should persist across pages
    const saved = localStorage.getItem('apiProvider');
    const currentProvider = (saved === 'nodejs' || saved === 'php') ? saved : 'nodejs';
    toggle.checked = currentProvider === 'php';
    
    // Remove any existing listeners to prevent duplicates
    const newToggle = toggle.cloneNode(true);
    toggle.parentNode.replaceChild(newToggle, toggle);
    
    // Add change event listener
    newToggle.addEventListener('change', function() {
        const newProvider = this.checked ? 'php' : 'nodejs';
        
        // Save preference to localStorage (persists across pages)
        localStorage.setItem('apiProvider', newProvider);
        
        // Reload the page to apply the new API provider
        window.location.reload();
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setTimeout(initApiToggle, 200));
} else {
    setTimeout(initApiToggle, 200);
}
