/**
 * Nyalife HMS - Loader Module
 * 
 * Provides consistent loading indicators across the application
 */

const NyalifeLoader = (function() {
    // Private variables
    let activeLoaders = 0;
    let globalLoader = null;

    /**
     * Create the global loader element if it doesn't exist
     * @returns {HTMLElement} The loader element
     */
    function getOrCreateLoader() {
        if (globalLoader) return globalLoader;

        // Check if loader already exists in DOM
        globalLoader = document.getElementById('globalLoader');
        if (globalLoader) return globalLoader;

        // Create loader
        globalLoader = document.createElement('div');
        globalLoader.id = 'globalLoader';
        globalLoader.className = 'd-flex justify-content-center align-items-center position-fixed w-100 h-100 bg-white bg-opacity-75';
        globalLoader.style.top = '0';
        globalLoader.style.left = '0';
        globalLoader.style.zIndex = '9999';

        // Create loader content
        globalLoader.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="loader-message mt-2">Loading...</div>
            </div>
        `;

        // Initially hide the loader
        globalLoader.classList.add('d-none');

        // Add to body
        document.body.appendChild(globalLoader);

        return globalLoader;
    }

    // Public methods
    return {
        /**
         * Initialize the loader
         */
        init: function() {
            // Create the loader if it doesn't exist
            getOrCreateLoader();

            // Add AJAX request handlers if jQuery is available
            if (window.jQuery) {
                $(document).ajaxStart(() => {
                    this.show();
                });

                $(document).ajaxStop(() => {
                    this.hide();
                });

                // Handle form submissions
                $('form:not(.no-loader)').on('submit', () => {
                    this.show('Processing...');
                });
            }

            // Add fetch API interceptors with proxy
            if (window.fetch) {
                const originalFetch = window.fetch;

                window.fetch = function(...args) {
                    NyalifeLoader.show();

                    return originalFetch.apply(this, args)
                        .then(response => {
                            NyalifeLoader.hide();
                            return response;
                        })
                        .catch(err => {
                            NyalifeLoader.hide();
                            throw err;
                        });
                };
            }

            return this;
        },

        /**
         * Show the loader
         * @param {string} message - Optional message to display
         */
        show: function(message = 'Loading...') {
            const loader = getOrCreateLoader();

            // Update message if provided
            if (message) {
                const messageElement = loader.querySelector('.loader-message');
                if (messageElement) {
                    messageElement.textContent = message;
                }
            }

            // Increment active loaders counter
            activeLoaders++;

            // Show the loader
            loader.classList.remove('d-none');
            document.body.classList.add('overflow-hidden');

            return this;
        },

        /**
         * Hide the loader
         */
        hide: function() {
            const loader = getOrCreateLoader();

            // Decrement active loaders counter
            activeLoaders = Math.max(0, activeLoaders - 1);

            // Only hide if no active loaders
            if (activeLoaders === 0) {
                loader.classList.add('d-none');
                document.body.classList.remove('overflow-hidden');
            }

            return this;
        },

        /**
         * Force hide the loader regardless of active count
         */
        forceHide: function() {
            const loader = getOrCreateLoader();

            // Reset counter
            activeLoaders = 0;

            // Hide loader
            loader.classList.add('d-none');
            document.body.classList.remove('overflow-hidden');

            return this;
        }
    };
})();

// For CommonJS environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NyalifeLoader;
}