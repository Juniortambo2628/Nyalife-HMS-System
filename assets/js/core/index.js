/**
 * Nyalife HMS - Core Integration Script
 * 
 * This file loads all core modules in the correct order and handles 
 * initialization. Include this single file in your HTML to load the
 * entire core framework.
 */

// Ensure DOM is ready before initializing
document.addEventListener('DOMContentLoaded', function() {
    console.log('Nyalife HMS Core Framework initializing...');
    
    // Create global namespaces
    window.Nyalife = window.Nyalife || {};
    window.NyalifeCore = window.NyalifeCore || {
        forms: {},
        api: {},
        ui: {}
    };
    
    // Initialize core components in the correct order
    initLoader();
    initNotifications();
    initAPI();
    initForms();
    initUtils();
    
    // Initialize page-specific functionality
    initPageModules();
    
    console.log('Nyalife HMS Core Framework initialized successfully');
    
    // Dispatch a custom event to signal that the core is ready
    document.dispatchEvent(new CustomEvent('nyalife:core:ready'));
});

/**
 * Initialize loader component
 */
function initLoader() {
    if (typeof NyalifeLoader !== 'undefined') {
        if (typeof NyalifeLoader.init === 'function') {
            NyalifeLoader.init();
        }
        
        // Add to core namespace for unified access
        window.NyalifeCore.loader = NyalifeLoader;
    }
}

/**
 * Initialize notifications component
 */
function initNotifications() {
    if (typeof NyalifeCoreUI !== 'undefined') {
        // Add to global namespace for easy access
        window.NyalifeCore.ui = NyalifeCoreUI;
        
        // Add legacy compatibility methods
        if (!window.showAlert && typeof NyalifeCoreUI.showAlert === 'function') {
            window.showAlert = function(type, message, timeout) {
                return NyalifeCoreUI.showAlert(type, message, { timeout: timeout });
            };
        }
        
        if (!window.showToast && typeof NyalifeCoreUI.showToast === 'function') {
            window.showToast = function(title, message, type, timeout) {
                return NyalifeCoreUI.showToast(title, message, type, { timeout: timeout });
            };
        }
    }
}

/**
 * Initialize API client
 */
function initAPI() {
    if (typeof NyalifeAPI !== 'undefined') {
        // Add to core namespace
        window.NyalifeCore.api = NyalifeAPI;
        
        // Add default headers for CSRF protection if using Laravel
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            NyalifeAPI.defaultHeaders = NyalifeAPI.defaultHeaders || {};
            NyalifeAPI.defaultHeaders['X-CSRF-TOKEN'] = csrfToken;
        }
    }
}

/**
 * Initialize forms component
 */
function initForms() {
    if (typeof NyalifeForms !== 'undefined') {
        // Make sure NyalifeCore is initialized
        window.NyalifeCore = window.NyalifeCore || {
            forms: {},
            api: {},
            ui: {}
        };
        
        // Add to core namespace
        window.NyalifeCore.forms = NyalifeForms;
        
        // Auto-initialize forms with data-nyalife-form attribute
        document.querySelectorAll('form[data-nyalife-form]').forEach(form => {
            // Get options from data attributes
            const options = {
                validateOnBlur: form.dataset.validateBlur !== 'false',
                validateOnSubmit: form.dataset.validateSubmit !== 'false',
                submitViaAjax: form.dataset.ajax !== 'false',
                resetAfterSubmit: form.dataset.reset === 'true'
            };
            
            // Initialize the form
            NyalifeForms.initForm(form, options);
        });
    }
}

/**
 * Initialize utilities component
 */
function initUtils() {
    if (typeof NyalifeUtils !== 'undefined') {
        // Add to core namespace
        window.NyalifeCore.utils = NyalifeUtils;
    }
}

/**
 * Initialize page-specific modules based on the current page
 */
function initPageModules() {
    // Get current page identifier from body data attribute if available
    const pageId = document.body.dataset.page;
    
    // Initialize based on common page elements
    
    // Auth pages (login, register)
    if (document.getElementById('loginForm') || document.getElementById('registerPatientForm')) {
        if (typeof initAuthForms === 'function') {
            initAuthForms();
        }
    }
    
    // Medical history forms
    const medicalHistoryForm = document.querySelector('form[data-medical-history]');
    if (medicalHistoryForm) {
        if (typeof initMedicalHistoryForm === 'function') {
            initMedicalHistoryForm(medicalHistoryForm);
        }
    }
    
    // Dashboard
    if (pageId === 'dashboard' || document.querySelector('.dashboard-stats')) {
        // Initialize dashboard-specific functionality
        if (typeof initDashboard === 'function') {
            initDashboard();
        }
    }
}
