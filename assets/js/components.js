/**
 * Nyalife HMS - Components Loader
 * Unified component system with standardized loader
 */

// Create the Components namespace
window.NyalifeComponents = (function() {
    // Private variables
    let componentCache = {};
    let loadedComponents = [];
    let ajaxSettings = {
        baseUrl: '',
        defaultContainer: '#main-content',
        loaderMessage: 'Loading component...',
        showLoader: true,
        cacheComponents: true
    };

    /**
     * Initialize the components system
     * @param {Object} options - Configuration options
     */
    const init = function(options = {}) {
        // Merge options with default settings
        Object.assign(ajaxSettings, options);
        
        // Get base URL from meta tag if available
        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
        if (baseUrlMeta && !ajaxSettings.baseUrl) {
            ajaxSettings.baseUrl = baseUrlMeta.getAttribute('content');
        }
        
        // Set up event delegation for component links
        document.addEventListener('click', function(event) {
            const target = event.target.closest('[data-component]');
            if (target) {
                event.preventDefault();
                
                const componentName = target.getAttribute('data-component');
                const container = target.getAttribute('data-container') || ajaxSettings.defaultContainer;
                
                loadComponent(componentName, container);
            }
        });
        
        // Set up form submission for component forms
        document.addEventListener('submit', function(event) {
            const form = event.target;
            if (form.hasAttribute('data-component-form')) {
                event.preventDefault();
                
                const componentName = form.getAttribute('data-component-form');
                const container = form.getAttribute('data-container') || ajaxSettings.defaultContainer;
                
                submitComponentForm(form, componentName, container);
            }
        });
        
        console.log('Nyalife Components initialized');
    };
    
    /**
     * Load a component via AJAX
     * @param {string} componentName - Name of the component to load
     * @param {string} container - CSS selector for the container
     * @param {Object} data - Optional data to send with the request
     * @param {Function} callback - Optional callback after load
     */
    const loadComponent = function(componentName, container, data = {}, callback) {
        // Check cache first if enabled
        if (ajaxSettings.cacheComponents && componentCache[componentName]) {
            renderCachedComponent(componentName, container, callback);
            return;
        }
        
        // Show loader
        this.showLoader();
        
        // Build the URL
        const url = `${ajaxSettings.baseUrl}/components/${componentName}`;
        
        // Make the AJAX request
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            // Cache the component if caching is enabled
            if (ajaxSettings.cacheComponents) {
                componentCache[componentName] = html;
            }
            
            // Render the component
            const containerElement = document.querySelector(container);
            if (containerElement) {
                containerElement.innerHTML = html;
                
                // Initialize any scripts in the component
                initComponentScripts(containerElement);
                
                // Track loaded component
                loadedComponents.push(componentName);
                
                // Call the callback if provided
                if (typeof callback === 'function') {
                    callback(html, containerElement);
                }
                
                // Dispatch a custom event
                const event = new CustomEvent('componentLoaded', {
                    detail: {
                        component: componentName,
                        container: containerElement
                    }
                });
                document.dispatchEvent(event);
            }
            
            // Hide loader
            this.hideLoader();
        })
        .catch(error => {
            console.error('Error loading component:', error);
            
            // Show error message
            const containerElement = document.querySelector(container);
            if (containerElement) {
                containerElement.innerHTML = `
                    <div class="alert alert-danger">
                        Error loading component: ${error.message}
                    </div>
                `;
            }
            
            // Hide loader
            this.hideLoader();
        });
    };
    
    /**
     * Render a cached component
     * @param {string} componentName - Name of the component
     * @param {string} container - CSS selector for the container
     * @param {Function} callback - Optional callback after render
     */
    const renderCachedComponent = function(componentName, container, callback) {
        const html = componentCache[componentName];
        const containerElement = document.querySelector(container);
        
        if (containerElement) {
            containerElement.innerHTML = html;
            
            // Initialize any scripts in the component
            initComponentScripts(containerElement);
            
            // Call the callback if provided
            if (typeof callback === 'function') {
                callback(html, containerElement);
            }
            
            // Dispatch a custom event
            const event = new CustomEvent('componentLoaded', {
                detail: {
                    component: componentName,
                    container: containerElement,
                    fromCache: true
                }
            });
            document.dispatchEvent(event);
        }
    };
    
    /**
     * Submit a form to load a component
     * @param {Element} form - The form element
     * @param {string} componentName - Name of the component
     * @param {string} container - CSS selector for the container
     */
    const submitComponentForm = function(form, componentName, container) {
        // Show loader
        this.showLoader();
        
        // Build the URL
        const url = form.getAttribute('action') || `${ajaxSettings.baseUrl}/components/${componentName}`;
        
        // Collect form data
        const formData = new FormData(form);
        
        // Make the AJAX request
        fetch(url, {
            method: form.getAttribute('method') || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            // Render the component
            const containerElement = document.querySelector(container);
            if (containerElement) {
                containerElement.innerHTML = html;
                
                // Initialize any scripts in the component
                initComponentScripts(containerElement);
                
                // Dispatch a custom event
                const event = new CustomEvent('componentFormSubmitted', {
                    detail: {
                        component: componentName,
                        container: containerElement,
                        form: form
                    }
                });
                document.dispatchEvent(event);
            }
            
            // Hide loader
            this.hideLoader();
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            
            // Show error message
            const containerElement = document.querySelector(container);
            if (containerElement) {
                containerElement.innerHTML = `
                    <div class="alert alert-danger">
                        Error submitting form: ${error.message}
                    </div>
                `;
            }
            
            // Hide loader
            this.hideLoader();
        });
    };
    
    /**
     * Initialize scripts within a component
     * @param {Element} container - The component container
     */
    const initComponentScripts = function(container) {
        // Execute any script tags
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            
            // Copy attributes
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            
            // Copy inline script content
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            
            // Replace the old script with the new one
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    };
    
    /**
     * Show loader - Use the unified NyalifeLoader if available
     */
    const showLoader = function() {
        if (!ajaxSettings.showLoader) return;
        
        // Use inline spinner instead of full page loader
        const message = ajaxSettings.loaderMessage || 'Loading...';
        
        // If NyalifeLoader exists, use it
        if (typeof NyalifeLoader !== 'undefined' && typeof NyalifeLoader.show === 'function') {
            NyalifeLoader.show(message);
            return;
        }
        
        // Fallback to Nyalife.loader if available
        if (window.Nyalife && typeof Nyalife.loader !== 'undefined' && typeof Nyalife.loader.show === 'function') {
            Nyalife.loader.show(message);
            return;
        }
        
        // Final fallback to simple loader
        const loader = document.createElement('div');
        loader.id = 'ajax-loader';
        loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        loader.style.position = 'fixed';
        loader.style.top = '50%';
        loader.style.left = '50%';
        loader.style.transform = 'translate(-50%, -50%)';
        loader.style.zIndex = '9999';
        document.body.appendChild(loader);
    };
    
    /**
     * Hide loader - Use the unified NyalifeLoader if available
     */
    const hideLoader = function() {
        if (!ajaxSettings.showLoader) return;
        
        // If NyalifeLoader exists, use it
        if (typeof NyalifeLoader !== 'undefined' && typeof NyalifeLoader.hide === 'function') {
            NyalifeLoader.hide();
            return;
        }
        
        // Fallback to Nyalife.loader if available
        if (window.Nyalife && typeof Nyalife.loader !== 'undefined' && typeof Nyalife.loader.hide === 'function') {
            Nyalife.loader.hide();
            return;
        }
        
        // Final fallback to remove simple loader
        const loader = document.getElementById('ajax-loader');
        if (loader) {
            loader.remove();
        }
    };
    
    // Public API
    return {
        init,
        loadComponent,
        submitComponentForm,
        showLoader,
        hideLoader
    };
})();

// Initialize components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    NyalifeComponents.init();
    
    // Make it available globally
    window.NyalifeComponents = NyalifeComponents;
});