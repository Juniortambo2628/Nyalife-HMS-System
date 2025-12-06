/**
 * Nyalife HMS - Unified Loader System
 * 
 * Consolidated loader with simple circular progress and logo
 * Replaces all previous loader implementations
 */

const NyalifeLoader = (function() {
    // Private variables
    let activeLoaders = 0;
    let globalLoader = null;
    let isInitialized = false;
    let hideTimeout = null;
    let startTime = null;
    
    // Settings
    const settings = {
        minDisplayTime: 500,    // Minimum time to show loader
        fadeOutTime: 300,       // Fade out transition time
        logoPath: '/assets/img/logo/Logo2-transparent.png'
    };

    /**
     * Create the global loader element
     */
    function createLoader() {
        if (globalLoader) return globalLoader;
        
        // Check if loader already exists
        globalLoader = document.getElementById('nyalife-loader');
        if (globalLoader) return globalLoader;

        // Get base URL
        const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';

        // Create loader container
        globalLoader = document.createElement('div');
        globalLoader.id = 'nyalife-loader';
        globalLoader.className = 'nyalife-loader';
        
        globalLoader.innerHTML = `
            <div class="nyalife-loader-content">
                <div class="nyalife-loader-logo-container">
                    <img src="${baseUrl}${settings.logoPath}" alt="Nyalife HMS" class="nyalife-loader-logo">
                    <div class="nyalife-loader-circle">
                        <svg viewBox="0 0 50 50" class="nyalife-loader-svg">
                            <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="2" 
                                    stroke-linecap="round" stroke-dasharray="126" stroke-dashoffset="126"
                                    class="nyalife-loader-progress">
                            </circle>
                        </svg>
                    </div>
                </div>
                <div class="nyalife-loader-text">Loading...</div>
            </div>
        `;

        // Initially hide
        globalLoader.style.display = 'none';
        
        // Add to body
        document.body.appendChild(globalLoader);
        
        return globalLoader;
    }

    /**
     * Initialize the loader system
     */
    function init() {
        if (isInitialized) return;

        // Create loader
        createLoader();

        // Set up AJAX interceptors
        setupAjaxInterceptors();
        
        // Set up fetch interceptors
        setupFetchInterceptors();
        
        // Set up page transition handlers
        setupPageHandlers();
        
        // Set up safety mechanisms
        setupSafetyMechanisms();

        isInitialized = true;
        console.log('NyalifeLoader unified system initialized');
    }

    /**
     * Check if we're on a dashboard page (loader should be disabled)
     */
    function isDashboardPage() {
        const body = document.body;
        return body.classList.contains('dashboard-page') || 
               body.classList.contains('has-sidebar') ||
               window.location.pathname.includes('/dashboard') ||
               window.location.pathname.includes('/appointments') ||
               window.location.pathname.includes('/prescriptions') ||
               window.location.pathname.includes('/lab-results') ||
               window.location.pathname.includes('/patients') ||
               window.location.pathname.includes('/staff');
    }

    /**
     * Set up AJAX request interceptors
     */
    function setupAjaxInterceptors() {
        if (!window.jQuery) return;

        $(document).ajaxStart(function(event, xhr, settings) {
            // Don't show loader on dashboard pages
            if (isDashboardPage()) return;
            
            const shouldBypass = shouldBypassLoader(settings?.url, settings);
            if (!shouldBypass) {
                show();
            }
        });

        $(document).ajaxStop(function() {
            hide();
        });

        $(document).ajaxError(function() {
            hide();
        });

        // Handle form submissions (skip on dashboard pages)
        $('body').on('submit', 'form:not(.no-loader)', function() {
            if (!isDashboardPage()) {
                show('Processing...');
            }
        });
    }

    /**
     * Set up fetch API interceptors
     */
    function setupFetchInterceptors() {
        if (!window.fetch) return;

        const originalFetch = window.fetch;
        
        window.fetch = function(...args) {
            const url = args[0];
            const options = args[1] || {};
            
            // Don't show loader on dashboard pages
            if (isDashboardPage()) {
                return originalFetch.apply(this, args);
            }
            
            const shouldBypass = shouldBypassLoader(url, options);
            
            if (!shouldBypass) {
                show();
            }

            return originalFetch.apply(this, args)
                .then(response => {
                    if (!shouldBypass) {
                        hide();
                    }
                    return response;
                })
                .catch(err => {
                    if (!shouldBypass) {
                        hide();
                    }
                    throw err;
                });
        };
    }

    /**
     * Set up page transition handlers
     */
    function setupPageHandlers() {
        // Page transitions (skip on dashboard pages)
        document.addEventListener('click', function(e) {
            if (isDashboardPage()) return;
            
            const link = e.target.closest('a[href]');
            if (link && !link.hasAttribute('data-no-loader') && 
                link.href && !link.href.startsWith('#') && 
                !link.href.startsWith('javascript:') &&
                link.hostname === window.location.hostname) {
                show('Loading page...');
            }
        });

        // Page load events
        window.addEventListener('load', function() {
            hide();
        });

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                console.log('Page restored from cache - force hiding loader');
                forceHide();
            } else {
                setTimeout(() => hide(), 100);
            }
        });

        window.addEventListener('popstate', function() {
            forceHide();
        });

        window.addEventListener('beforeunload', function() {
            if (!isBackNavigation()) {
                show('Loading...');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => forceHide(), 50);
        });

        window.addEventListener('focus', function() {
            setTimeout(() => forceHide(), 100);
        });
    }

    /**
     * Set up safety mechanisms
     */
    function setupSafetyMechanisms() {
        // Check for stuck loaders every 3 seconds
        setInterval(() => {
            if (isVisible() && document.readyState === 'complete') {
                console.warn('Loader appears to be stuck - force hiding');
                forceHide();
            }
        }, 3000);

        // Emergency timeout after 10 seconds
        setTimeout(() => {
            if (isVisible()) {
                console.warn('Emergency loader timeout - force hiding');
                forceHide();
            }
        }, 10000);
    }

    /**
     * Check if request should bypass loader
     */
    function shouldBypassLoader(url, options = {}) {
        // Check for explicit bypass flags
        if (options.noLoader || 
            (options.headers && options.headers['X-No-Loader'])) {
            return true;
        }

        // Bypass for dropdown API calls
        if (typeof url === 'string') {
            const bypassPatterns = [
                '/api/messages/inbox',
                '/api/notifications',
                '/api/messages/users',
                '/components/',
                /\/api\/(messages|notifications)/
            ];

            return bypassPatterns.some(pattern => {
                if (pattern instanceof RegExp) {
                    return pattern.test(url);
                }
                return url.includes(pattern);
            });
        }

        // Additional check: if the request is coming from a dropdown context
        if (options.headers && options.headers['X-Requested-With'] === 'dropdown') {
            return true;
        }

        return false;
    }

    /**
     * Check if navigation is back button
     */
    function isBackNavigation() {
        return performance.navigation && performance.navigation.type === 2;
    }

    /**
     * Show the loader
     */
    function show(message = 'Loading...') {
        const loader = createLoader();

        // Clear any pending hide timeout
        if (hideTimeout) {
            clearTimeout(hideTimeout);
            hideTimeout = null;
        }

        // Update message
        const textElement = loader.querySelector('.nyalife-loader-text');
        if (textElement) {
            textElement.textContent = message;
        }

        // Increment counter
        activeLoaders++;

        // Show loader
        loader.style.display = 'flex';
        document.body.classList.add('nyalife-loader-active');

        // Record start time
        startTime = Date.now();

        return this;
    }

    /**
     * Hide the loader
     */
    function hide(immediate = false) {
        const loader = createLoader();

        if (immediate) {
            activeLoaders = 0;
        } else {
            activeLoaders = Math.max(0, activeLoaders - 1);
        }

        // Only hide if no active loaders
        if (activeLoaders === 0) {
            if (immediate) {
                loader.style.display = 'none';
                document.body.classList.remove('nyalife-loader-active');
            } else {
                // Calculate minimum display time
                const elapsed = Date.now() - (startTime || 0);
                const remaining = Math.max(0, settings.minDisplayTime - elapsed);

                if (!hideTimeout) {
                    hideTimeout = setTimeout(() => {
                        loader.style.display = 'none';
                        document.body.classList.remove('nyalife-loader-active');
                        hideTimeout = null;
                    }, remaining);
                }
            }
        }

        return this;
    }

    /**
     * Force hide the loader
     */
    function forceHide() {
        return hide(true);
    }

    /**
     * Check if loader is visible
     */
    function isVisible() {
        const loader = createLoader();
        return loader.style.display !== 'none';
    }

    /**
     * Get active loader count
     */
    function getActiveCount() {
        return activeLoaders;
    }

    // Public API
    return {
        init,
        show,
        hide,
        forceHide,
        isVisible,
        getActiveCount
    };
})();

// Auto-initialization disabled - loader will only show when explicitly called
// This prevents the loader from appearing on every page load
// Uncomment below if you want auto-init (not recommended):
/*
document.addEventListener('DOMContentLoaded', function() {
    NyalifeLoader.init();
});

window.addEventListener('load', function() {
    if (!NyalifeLoader.isVisible()) {
        NyalifeLoader.init();
    }
});
*/

// Create global namespaces for backward compatibility
window.Nyalife = window.Nyalife || {};
window.Nyalife.loader = NyalifeLoader;
window.Nyalife.Loader = NyalifeLoader;

window.NyalifeCore = window.NyalifeCore || {};
window.NyalifeCore.loader = NyalifeLoader;

// Export for CommonJS
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NyalifeLoader;
}
