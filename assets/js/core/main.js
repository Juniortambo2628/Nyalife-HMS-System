/**
 * Nyalife HMS - Core Main Entry Point
 * 
 * Loads all core modules and initializes them
 */

// Initialize core modules when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Register global error handler for uncaught errors
    window.addEventListener('error', function(event) {
        console.error('Uncaught error:', event.error);
        
        // Show error notification if available
        if (window.NyalifeCoreUI && typeof NyalifeCoreUI.showNotification === 'function') {
            NyalifeCoreUI.showNotification('error', 'An unexpected error occurred. Please try again.');
        }
    });
    
    // Don't call initForms here - it's already called in index.js
    // Check if it's already been initialized
    if (!window.NyalifeCore || !window.NyalifeCore.forms) {
        console.log('Forms not yet initialized, initializing now');
        // Only call if not already initialized from index.js
        if (typeof initForms === 'function') {
            initForms();
        }
    }
    
    console.log('Nyalife HMS core initialized successfully');
});

/**
 * Initialize forms with automatic validation and submission
 */
function initForms() {
    // Skip if NyalifeForms is not available
    if (!window.NyalifeForms) return;
    
    // Auto-initialize forms with data-nyalife-form attribute
    document.querySelectorAll('form[data-nyalife-form]').forEach(form => {
        // Get options from data attributes
        const options = {
            validateOnBlur: form.dataset.validateBlur !== 'false',
            validateOnSubmit: form.dataset.validateSubmit !== 'false',
            submitViaAjax: form.dataset.ajax !== 'false',
            resetAfterSubmit: form.dataset.reset === 'true',
            
            // Success handler
            onSuccess: function(response, formElement) {
                // Show success message if provided
                if (response && response.message && window.NyalifeCoreUI) {
                    NyalifeCoreUI.showNotification('success', response.message);
                }
                
                // Handle redirect if provided
                if (response && response.redirect) {
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                }
                
                // Trigger custom success event
                const event = new CustomEvent('nyalife:form:success', {
                    detail: { response, form: formElement }
                });
                formElement.dispatchEvent(event);
            },
            
            // Error handler
            onError: function(error, formElement) {
                // Show error message if available
                if (window.NyalifeCoreUI) {
                    let message = 'An error occurred. Please try again.';
                    
                    if (error.data && error.data.message) {
                        message = error.data.message;
                    } else if (error.message) {
                        message = error.message;
                    }
                    
                    NyalifeCoreUI.showNotification('error', message);
                    
                    // Handle validation errors
                    if (error.data && error.data.errors) {
                        NyalifeCoreUI.showFormErrors(formElement, error.data.errors);
                    }
                }
                
                // Trigger custom error event
                const event = new CustomEvent('nyalife:form:error', {
                    detail: { error, form: formElement }
                });
                formElement.dispatchEvent(event);
            }
        };
        
        // Initialize the form
        NyalifeForms.initForm(form, options);
    });
}

// Define route helper if needed
if (!window.nyalifeRoutes) {
    window.nyalifeRoutes = {};
}

/**
 * Add routes to the global routes object
 * @param {Object} routes - Routes to add
 */
function addRoutes(routes) {
    if (typeof routes !== 'object') return;
    
    Object.assign(window.nyalifeRoutes, routes);
}

// Export core modules to the global namespace for backwards compatibility
window.NyalifeInit = {
    addRoutes: addRoutes,
    initForms: initForms
};
