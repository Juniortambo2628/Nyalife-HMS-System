/**
 * Nyalife HMS - Main JavaScript
 * Consolidated and modular JS functionality for Nyalife HMS
 */

// Create global namespace
window.Nyalife = window.Nyalife || {};

// Initialize core modules
document.addEventListener('DOMContentLoaded', function() {
    console.log('Nyalife HMS core modules initialized');
    
    // Make core modules accessible globally
    window.NyalifeAPI = window.NyalifeAPI || {};
    window.NyalifeCoreUI = window.NyalifeCoreUI || {};
    window.NyalifeForms = window.NyalifeForms || {};
    window.NyalifeUtils = window.NyalifeUtils || {};
});

// Legacy namespace for backward compatibility
const Nyalife = window.Nyalife || {};

/**
 * Core utility functions
 */
Nyalife.utils = (function() {
    /**
     * Easy selector helper function
     * @param {string} selector - CSS selector
     * @returns {Element} The selected DOM element
     */
    const select = (selector) => document.querySelector(selector);

    /**
     * Easy selector helper function for multiple elements
     * @param {string} selector - CSS selector
     * @returns {NodeList} The selected DOM elements
     */
    const selectAll = (selector) => document.querySelectorAll(selector);

    /**
     * Safely access nested objects
     * @param {Object} obj - The object to access
     * @param {string} path - The path to the property
     * @param {*} defaultValue - Default value if property doesn't exist
     * @returns {*} The value or default
     */
    const get = (obj, path, defaultValue = undefined) => {
        const travel = (regexp) =>
            String.prototype.split
            .call(path, regexp)
            .filter(Boolean)
            .reduce((res, key) => (res !== null && res !== undefined ? res[key] : res), obj);
        const result = travel(/[,[\]]+?/) || travel(/[,[\].]+?/);
        return result === undefined || result === obj ? defaultValue : result;
    };

    /**
     * Check if an element exists in the DOM
     * @param {string} selector - CSS selector
     * @returns {boolean} True if element exists
     */
    const exists = (selector) => !!select(selector);

    /**
     * Safely add event listener
     * @param {Element|string} element - Element or selector
     * @param {string} event - Event name
     * @param {Function} callback - Event handler
     */
    const on = (element, event, callback) => {
        const el = typeof element === 'string' ? select(element) : element;
        if (el) {
            el.addEventListener(event, callback);
        }
    };

    return {
        select,
        selectAll,
        get,
        exists,
        on
    };
})();


/**
 * Alert functionality
 */
Nyalife.alerts = (function() {
    /**
     * Show an alert notification
     * @param {string} type - Alert type (success, error, warning, info)
     * @param {string} message - Alert message
     * @param {number} timeout - Auto-dismiss timeout in ms (0 to disable)
     * @returns {Element} The created alert element
     */
    const show = function(type, message, timeout = 5000) {
        // Get the alerts container or create it if it doesn't exist
        let alertsContainer = document.getElementById('alertsContainer');
        if (!alertsContainer) {
            alertsContainer = document.createElement('div');
            alertsContainer.id = 'alertsContainer';
            alertsContainer.className = 'position-fixed bottom-0 end-0 p-3';
            alertsContainer.style.zIndex = '1080';
            document.body.appendChild(alertsContainer);
        }

        // Check for duplicate alerts with the same message
        const existingAlerts = alertsContainer.querySelectorAll('.alert');
        for (let i = 0; i < existingAlerts.length; i++) {
            if (existingAlerts[i].textContent.trim().includes(message.trim())) {
                return existingAlerts[i]; // Don't create duplicate alert
            }
        }

        // Map alert type to Bootstrap class
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        // Create a unique ID for the alert
        const alertId = 'alert-' + new Date().getTime();

        // Create the alert element
        const alertElement = document.createElement('div');
        alertElement.id = alertId;
        alertElement.className = `alert ${alertClass} alert-dismissible fade show`;
        alertElement.role = 'alert';
        alertElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Add the alert to the container
        alertsContainer.appendChild(alertElement);

        // Initialize the Bootstrap alert if available
        if (window.bootstrap && window.bootstrap.Alert) {
            const bsAlert = new bootstrap.Alert(alertElement);

            // Auto-dismiss the alert after the specified timeout
            if (timeout > 0) {
                setTimeout(() => {
                    if (alertElement.parentNode) { // Check if element still exists
                        bsAlert.close();
                    }
                }, timeout);
            }
        } else {
            // Fallback if Bootstrap is not available
            if (timeout > 0) {
                setTimeout(() => {
                    if (alertElement.parentNode) { // Check if element still exists
                        alertElement.remove();
                    }
                }, timeout);
            }
        }

        // Remove the alert from the DOM after it's closed
        alertElement.addEventListener('closed.bs.alert', function() {
            this.remove();
        });

        return alertElement;
    };

    return {
        show
    };
})();

/**
 * Authentication functionality
 */
Nyalife.auth = (function() {
    /**
     * Initialize login form
     */
    const initLogin = function() {
        const loginForm = Nyalife.utils.select('#loginForm');
        if (!loginForm) return;

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Hide any previous alerts
            const loginAlert = document.getElementById('loginAlert');
            if (loginAlert) {
                loginAlert.classList.add('d-none');
                loginAlert.classList.remove('alert-success', 'alert-danger');
            }

            // Show the loader
            if (Nyalife.loader) {
                Nyalife.loader.show('Authenticating...');
            }

            // Show spinner
            const loginSpinner = document.getElementById('loginSpinner');
            if (loginSpinner) loginSpinner.classList.remove('d-none');

            // Get form data
            const formData = new FormData(loginForm);
            const action = loginForm.getAttribute('action');

            // Send fetch request
            fetch(action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message only in the global alerts container
                        if (Nyalife.alerts) {
                            Nyalife.alerts.show('success', data.message);
                        }

                        // Get redirect URL from server response or use homepage
                        const redirectUrl = data.data && data.data.redirect ? data.data.redirect : '/';
                        console.log('Login successful. Redirecting to:', redirectUrl);

                        setTimeout(function() {
                            // First, clean up any modal backdrops
                            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                                backdrop.remove();
                            });

                            // Remove modal-open class and reset body styles
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';

                            // Reset any bootstrap modal instances
                            if (window.bootstrap && window.bootstrap.Modal) {
                                const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                                if (loginModal) {
                                    loginModal.dispose();
                                }
                            }

                            // Check if we're already on the homepage
                            const currentPath = window.location.pathname;
                            const isHomepage = currentPath === '/' ||
                                currentPath.endsWith('/index.php') ||
                                currentPath.endsWith('/');

                            // If we're already on the homepage, just reload the page
                            // Otherwise redirect to the URL provided by the server
                            if (isHomepage) {
                                window.location.reload();
                            } else {
                                window.location.href = redirectUrl;
                            }
                        }, 1000);
                    } else {
                        // Show error message in the login alert for immediate feedback
                        if (loginAlert) {
                            loginAlert.textContent = data.message || 'Login failed. Please check your credentials.';
                            loginAlert.classList.remove('d-none', 'alert-success');
                            loginAlert.classList.add('alert-danger');
                            loginAlert.style.zIndex = '2000';
                        }

                        // Hide the loader
                        if (Nyalife.loader) {
                            Nyalife.loader.hide();
                        }

                        // Hide spinner
                        if (loginSpinner) loginSpinner.classList.add('d-none');
                    }
                })
                .catch(error => {
                    console.error('Login error:', error);

                    // Show error message in the login alert
                    if (loginAlert) {
                        loginAlert.textContent = 'An error occurred. Please try again.';
                        loginAlert.classList.remove('d-none', 'alert-success');
                        loginAlert.classList.add('alert-danger');
                        loginAlert.style.zIndex = '2000';
                    }

                    // Also show global alert if available
                    if (Nyalife.alerts) {
                        Nyalife.alerts.show('error', 'An error occurred. Please try again.');
                    }

                    // Hide the loader
                    if (Nyalife.loader) {
                        Nyalife.loader.hide();
                    }

                    // Hide spinner
                    if (loginSpinner) loginSpinner.classList.add('d-none');
                });
        });
    };

    /**
     * Initialize registration form
     */
    const initRegistration = function() {
        const registrationForm = Nyalife.utils.select('#registerPatientForm');
        if (!registrationForm) return;

        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show the loader
            if (Nyalife.loader) {
                Nyalife.loader.show('Processing registration...');
            }

            // Get form data
            const formData = new FormData(registrationForm);
            const action = registrationForm.getAttribute('action');

            // Send fetch request
            fetch(action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        if (Nyalife.alerts) {
                            Nyalife.alerts.show('success', data.message);
                        }

                        // Reset form
                        registrationForm.reset();

                        // Close registration modal if it exists
                        if (window.bootstrap && window.bootstrap.Modal) {
                            const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerPatientModal'));
                            if (registerModal) {
                                registerModal.hide();
                            }
                        }

                        // Show login modal if it exists
                        setTimeout(function() {
                            if (document.getElementById('loginModal')) {
                                if (window.bootstrap && window.bootstrap.Modal) {
                                    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                                    loginModal.show();
                                }
                            }
                        }, 2000);
                    } else {
                        // Show error message
                        if (Nyalife.alerts) {
                            Nyalife.alerts.show('error', data.message);
                        }
                    }

                    // Hide the loader
                    if (Nyalife.loader) {
                        Nyalife.loader.hide();
                    }
                })
                .catch(error => {
                    console.error('Registration error:', error);

                    // Show error message
                    if (Nyalife.alerts) {
                        Nyalife.alerts.show('error', 'An error occurred during registration. Please try again.');
                    }

                    // Hide the loader
                    if (Nyalife.loader) {
                        Nyalife.loader.hide();
                    }
                });
        });

        // Password validation
        const password = Nyalife.utils.select('#register-password');
        const confirmPassword = Nyalife.utils.select('#register-confirm-password');

        if (password && confirmPassword) {
            [password, confirmPassword].forEach(field => {
                field.addEventListener('input', function() {
                    const passwordValue = password.value;
                    const confirmValue = confirmPassword.value;

                    // Check if passwords match
                    if (passwordValue !== '' && confirmValue !== '') {
                        if (passwordValue === confirmValue) {
                            confirmPassword.classList.remove('is-invalid');
                            confirmPassword.classList.add('is-valid');
                            const feedback = Nyalife.utils.select('#passwordMatchFeedback');
                            if (feedback) {
                                feedback.classList.remove('invalid-feedback');
                                feedback.classList.add('valid-feedback');
                                feedback.textContent = 'Passwords match';
                            }
                        } else {
                            confirmPassword.classList.remove('is-valid');
                            confirmPassword.classList.add('is-invalid');
                            const feedback = Nyalife.utils.select('#passwordMatchFeedback');
                            if (feedback) {
                                feedback.classList.remove('valid-feedback');
                                feedback.classList.add('invalid-feedback');
                                feedback.textContent = 'Passwords do not match';
                            }
                        }
                    }
                });
            });
        }
    };

    // Initialize all auth components
    const init = function() {
        initLogin();
        initRegistration();
    };

    return {
        init,
        initLogin,
        initRegistration
    };
})();

/**
 * Core UI functionality
 */
Nyalife.ui = (function() {
    const init = function() {
        // Initialize components when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Initializing all UI components from Nyalife.ui");
        });
    };

    return {
        init
    };
})();

// Automatically initialize modules on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('#loginForm') || document.querySelector('#registerForm')) {
        Nyalife.auth.init();
    }
    
    // Always initialize the UI module
    Nyalife.ui.init();
});