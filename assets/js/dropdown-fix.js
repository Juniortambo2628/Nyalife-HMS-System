/**
 * Nyalife HMS - Dropdown Fix
 * Ensures Bootstrap dropdowns work correctly, especially after AJAX page loads
 */

/* global bootstrap */

function initializeDropdowns() {
    // Wait for Bootstrap to be available
    if (typeof window.bootstrap === 'undefined' && typeof bootstrap === 'undefined') {
        // Retry after a short delay if Bootstrap isn't loaded yet
        setTimeout(initializeDropdowns, 100);
        return;
    }
    
    const bs = window.bootstrap || bootstrap;
    
    // Initialize all dropdowns that aren't already initialized
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    const dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
        // Check if dropdown is already initialized
        const existingInstance = bs.Dropdown.getInstance(dropdownToggleEl);
        if (existingInstance) {
            return existingInstance;
        }
        
        // Initialize new dropdown
        try {
            return new bs.Dropdown(dropdownToggleEl, {
                autoClose: true,
                boundary: 'viewport'
            });
        } catch (e) {
            // eslint-disable-next-line no-console
            console.warn('Failed to initialize dropdown:', e);
            return null;
        }
    }).filter(function(item) {
        return item !== null;
    });
    
    if (dropdownList.length > 0) {
        // eslint-disable-next-line no-console
        console.log('Bootstrap dropdowns initialized:', dropdownList.length);
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // eslint-disable-next-line no-console
    console.log('Initializing dropdown fixes...');
    initializeDropdowns();
});

// Re-initialize dropdowns when content is loaded via AJAX
document.addEventListener('page:loaded', function() {
    initializeDropdowns();
});

// Also initialize immediately if DOM is already loaded
if (document.readyState === 'loading') {
    // DOM is still loading, wait for DOMContentLoaded
} else {
    // DOM is already loaded, initialize immediately
    initializeDropdowns();
}

// Re-initialize after a short delay to ensure all scripts are loaded
// This is especially important for pages that load scripts dynamically
setTimeout(function() {
    initializeDropdowns();
}, 500);

// Also re-initialize when window is fully loaded
window.addEventListener('load', function() {
    initializeDropdowns();
});

// Specifically initialize card header menu toggles
function initializeCardHeaderMenus() {
    if (typeof window.bootstrap === 'undefined' && typeof bootstrap === 'undefined') {
        setTimeout(initializeCardHeaderMenus, 100);
        return;
    }
    
    const bs = window.bootstrap || bootstrap;
    const cardHeaderMenus = document.querySelectorAll('.card-header-menu-toggle');
    
    cardHeaderMenus.forEach(function(toggle) {
        // Check if already initialized
        const existingInstance = bs.Dropdown.getInstance(toggle);
        if (existingInstance) {
            return;
        }
        
        // Initialize dropdown
        try {
            new bs.Dropdown(toggle, {
                autoClose: true,
                boundary: 'viewport'
            });
        } catch (e) {
            // eslint-disable-next-line no-console
            console.warn('Failed to initialize card header menu:', e);
        }
    });
}

// Initialize card header menus on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initializeCardHeaderMenus();
});

// Re-initialize after a delay
setTimeout(function() {
    initializeCardHeaderMenus();
}, 500);

// Re-initialize on window load
window.addEventListener('load', function() {
    initializeCardHeaderMenus();
}); 