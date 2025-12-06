/**
 * Dashboard Debug Helper
 * This script helps diagnose and fix issues with the dashboard
 * Updated to use core modules when available
 */

(function() {
    // Check if core modules are available
    const usingCoreModules = typeof NyalifeUtils !== 'undefined';
    
    if (usingCoreModules) {
        NyalifeUtils.log('Dashboard Debug Helper loaded (using core modules)');
    } else {
        console.log('Dashboard Debug Helper loaded (standalone mode)');
    }

    // Function to fix link click issues
    function fixLinkClicks() {
        // Select all links in the dashboard
        const links = usingCoreModules 
            ? NyalifeUtils.selectAll('a, button, .btn, [data-bs-toggle], .nav-link')
            : document.querySelectorAll('a, button, .btn, [data-bs-toggle], .nav-link');

        links.forEach(link => {
            // Remove old event listeners by cloning
            const newLink = link.cloneNode(true);
            if (link.parentNode) {
                link.parentNode.replaceChild(newLink, link);
            }

            // Add new event listener - use core Utils.on if available
            if (usingCoreModules) {
                NyalifeUtils.on(newLink, 'click', function(e) {
                    NyalifeUtils.log('Clicked:', this);
                    const href = this.getAttribute('href');

                    // For links with hrefs, ensure they actually navigate
                    if (href && !href.startsWith('#') && !this.hasAttribute('data-bs-toggle')) {
                        e.preventDefault();
                        NyalifeUtils.log('Navigating to:', href);
                        window.location.href = href;
                    }
                });
            } else {
                newLink.addEventListener('click', function(e) {
                    console.log('Clicked:', this);
                    const href = this.getAttribute('href');

                    // For links with hrefs, ensure they actually navigate
                    if (href && !href.startsWith('#') && !this.hasAttribute('data-bs-toggle')) {
                        e.preventDefault();
                        console.log('Navigating to:', href);
                        window.location.href = href;
                    }
                });
            }
        });

        if (usingCoreModules) {
            NyalifeUtils.log('Fixed ' + links.length + ' clickable elements');
        } else {
            console.log('Fixed', links.length, 'clickable elements');
        }
    }

    // Fix z-index issues
    function fixZIndexIssues() {
        const elementsToFix = usingCoreModules 
            ? NyalifeUtils.selectAll('.sidebar, .navbar, .card, .btn, a, button')
            : document.querySelectorAll('.sidebar, .navbar, .card, .btn, a, button');

        elementsToFix.forEach((el, index) => {
            // Set position and z-index
            el.style.position = 'relative';

            // Assign z-index based on type
            if (el.classList.contains('btn') || el.tagName === 'A' || el.tagName === 'BUTTON') {
                el.style.zIndex = '100';
            } else if (el.classList.contains('card')) {
                el.style.zIndex = '5';
            } else if (el.classList.contains('sidebar')) {
                el.style.zIndex = '10';
            }
        });

        console.log('Fixed z-index for', elementsToFix.length, 'elements');
    }

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM ready, applying fixes');

        // Apply fixes
        setTimeout(() => {
            fixLinkClicks();
            fixZIndexIssues();
            console.log('All fixes applied. Dashboard should be working now.');
        }, 500);
    });

    // Expose reload function
    window.fixDashboard = function() {
        fixLinkClicks();
        fixZIndexIssues();
        console.log('Dashboard fixes applied');
    };
})();