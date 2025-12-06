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
 * Loader functionality
 */
Nyalife.loader = (function() {
    // Show the loader with optional custom message
    const show = function(message = 'Loading...') {
        // Use the new NyalifeLoader if available
        if (typeof NyalifeLoader !== 'undefined') {
            NyalifeLoader.show(message);
            return this;
        }

        // Fallback to old implementation
        const loader = document.getElementById('globalLoader');
        if (!loader) return;

        const messageElement = loader.querySelector('.loader-message');
        if (messageElement && message) {
            messageElement.textContent = message;
        }

        loader.classList.remove('d-none');
        document.body.classList.add('overflow-hidden');
        return this;
    };

    // Hide the loader
    const hide = function() {
        // Use the new NyalifeLoader if available
        if (typeof NyalifeLoader !== 'undefined') {
            NyalifeLoader.hide();
            return this;
        }

        // Fallback to old implementation
        const loader = document.getElementById('globalLoader');
        if (!loader) return;

        loader.classList.add('d-none');
        document.body.classList.remove('overflow-hidden');
        return this;
    };

    // Initialize loader AJAX handlers
    const init = function() {
        // Use the new NyalifeLoader if available
        if (typeof NyalifeLoader !== 'undefined') {
            NyalifeLoader.init();
            return this;
        }

        // Fallback AJAX handling with jQuery
        if (window.jQuery) {
            $(document).ajaxStart(function() {
                show();
            });

            // Hide the loader when all AJAX requests complete
            $(document).ajaxStop(function() {
                hide();
            });

            // Handle AJAX errors
            $(document).ajaxError(function(event, jqXHR, settings, error) {
                console.error('AJAX Error:', error);
                hide();

                // Show error alert if available
                if (typeof Nyalife.alerts !== 'undefined') {
                    Nyalife.alerts.show('error', 'An error occurred. Please try again.');
                }
            });

            // Add form submit handler to show loader
            $('form').on('submit', function() {
                // Only show loader if form doesn't have 'no-loader' class
                if (!$(this).hasClass('no-loader')) {
                    show('Processing...');
                }
            });
        }

        return this;
    };

    return {
        show,
        hide,
        init
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
 * Hero module functionality
 */
Nyalife.hero = (function() {
    // Initialize hero components
    const init = function() {
        if (document.querySelector('.hero')) {
            Nyalife.ui.initHeroSlider();
            Nyalife.ui.initServiceBoxes();
        }
    };

    return {
        init
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
 * UI functionality module
 */
Nyalife.ui = (function() {
    /**
     * Initialize the hero slider
     */
    const initHeroSlider = function() {
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.hero-dot');
        const prevBtn = document.getElementById('prev-slide');
        const nextBtn = document.getElementById('next-slide');
        let currentSlide = 0;

        if (!slides.length || !dots.length) return;

        // Function to change slide
        const goToSlide = function(index) {
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;

            // Update active slide
            slides.forEach(slide => slide.classList.remove('active'));
            slides[index].classList.add('active');

            // Update dots
            dots.forEach(dot => dot.classList.remove('active'));
            dots[index].classList.add('active');

            // Update current slide index
            currentSlide = index;

            // Update active tooltip if updateActiveColumn function exists
            if (typeof window.updateActiveColumn === 'function') {
                window.updateActiveColumn(index);
            }
        };

        // Set up navigation
        if (prevBtn) {
            prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));
        }

        // Set up dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });

        // Auto-advance slides every 7 seconds
        setInterval(() => {
            goToSlide(currentSlide + 1);
        }, 7000);
    };

    /**
     * Initialize service boxes and tooltips
     */
    const initServiceBoxes = function() {
        const serviceBoxes = document.querySelectorAll('.why-join-item');
        const tooltipContents = document.querySelectorAll('.tooltip-content');

        if (!serviceBoxes.length) return;

        // Show tooltip content for active service box
        window.updateActiveColumn = function(index) {
            // Show tooltip for the active slide (1-based index)
            const tooltipIndex = index % 4; // Ensure it loops back to first tooltip after 4th

            // Hide all tooltips first
            tooltipContents.forEach(tooltip => {
                tooltip.style.display = 'none';
            });

            // Show the active tooltip
            const activeTooltip = document.getElementById(`tooltip-${tooltipIndex + 1}`);
            if (activeTooltip) {
                activeTooltip.style.display = 'block';
            }

            // Debug logging removed to avoid console spam
            // console.log(`Setting active tooltip to index ${tooltipIndex + 1}`);
        };

        // Set up modal service content when clicking on service boxes
        serviceBoxes.forEach((box, index) => {
            box.addEventListener('click', function() {
                // Get tooltip content
                const tooltipEl = box.querySelector('.join-tooltip');
                const tooltipContent = tooltipEl ? tooltipEl.innerHTML : '';
                const modalContent = document.getElementById('modalServiceContent');

                if (modalContent && tooltipContent) {
                    modalContent.innerHTML = tooltipContent;
                }

                // Set the correct background image for modal
                const modalBg = document.querySelector('.service-modal-bg');
                if (modalBg) {
                    // Get any baseUrl that might be specified in the script tag
                    let baseUrl = '';
                    const baseUrlMeta = document.querySelector('meta[name="base-url"]');
                    if (baseUrlMeta) {
                        baseUrl = baseUrlMeta.getAttribute('content');
                    }

                    // Default background image path
                    let bgImagePath = 'assets/img/gallery/';

                    // Select appropriate background image based on service type
                    switch (index) {
                        case 0: // Obstetrics Care
                            bgImagePath += 'Obstetrics-care.jpg';
                            break;
                        case 1: // Gynecology Services
                            bgImagePath += 'Gynecology-services.jpg';
                            break;
                        case 2: // Lab Services
                            bgImagePath += 'Laboratory-services.JPG';
                            break;
                        case 3: // Pharmacy
                            bgImagePath += 'Pharmacy.jpg';
                            break;
                        default:
                            bgImagePath += 'Obstetrics-care.jpg';
                    }

                    // Set the background image with correct base URL
                    modalBg.style.backgroundImage = `url('${baseUrl}/${bgImagePath}')`;
                }
            });
        });

        // Set initial state - show first tooltip
        window.updateActiveColumn(0);
    };

    /**
     * Fix z-index issues with modals
     */
    const fixModals = function() {
        const modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(modal => {
            modal.style.zIndex = '1050';
        });

        const modalDialogs = document.querySelectorAll('.modal-dialog');
        modalDialogs.forEach(dialog => {
            dialog.style.zIndex = '1060';
        });

        const modalContents = document.querySelectorAll('.modal-content');
        modalContents.forEach(content => {
            content.style.background = 'rgba(255, 255, 255, 0.98)';
            content.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.3)';
        });

        const modalBackdrops = document.querySelectorAll('.modal-backdrop');
        if (modalBackdrops.length > 1) {
            // Keep only the last backdrop if multiple exist
            for (let i = 0; i < modalBackdrops.length - 1; i++) {
                modalBackdrops[i].remove();
            }

            // Style the remaining backdrop
            if (modalBackdrops[modalBackdrops.length - 1]) {
                modalBackdrops[modalBackdrops.length - 1].style.zIndex = '1040';
                modalBackdrops[modalBackdrops.length - 1].style.opacity = '0.7';
            }
        } else if (modalBackdrops.length === 1) {
            modalBackdrops[0].style.zIndex = '1040';
            modalBackdrops[0].style.opacity = '0.7';
        }
    };

    /**
     * Handle modal chaining (switching from one modal to another)
     */
    const handleModalChaining = function() {
        // Add event listeners to all buttons that open modals
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
            button.addEventListener('click', function() {
                // Small delay to ensure proper modal transition
                setTimeout(function() {
                    // Fix any z-index issues
                    fixModals();

                    // If there are multiple backdrops, keep only the last one
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    if (backdrops.length > 1) {
                        for (let i = 0; i < backdrops.length - 1; i++) {
                            backdrops[i].remove();
                        }
                    }
                }, 50);
            });
        });
    };

    /**
     * Initialize all UI components
     */
    const init = function() {
        // Set up modal fixes
        document.addEventListener('show.bs.modal', fixModals);
        document.addEventListener('shown.bs.modal', fixModals);

        // Set up modal chaining
        handleModalChaining();

        // Clean up modal backdrop issues on modal hidden
        document.addEventListener('hidden.bs.modal', function(event) {
            // Small delay to check if another modal is being shown
            setTimeout(function() {
                const openModals = document.querySelectorAll('.modal.show');

                // Only clean up if no modals are open
                if (openModals.length === 0) {
                    // Remove any lingering backdrops
                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                        backdrop.remove();
                    });

                    // Reset body styles
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            }, 50);
        });
    };

    return {
        init,
        initHeroSlider,
        initServiceBoxes,
        fixModals,
        handleModalChaining
    };
})();

/**
 * Initialize everything when the DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize loader
    Nyalife.loader.init();

    // Initialize UI
    Nyalife.ui.init();

    // Initialize authentication handlers
    Nyalife.auth.init();

    // Initialize hero if present
    Nyalife.hero.init();

    // Make Nyalife globally available
    window.Nyalife = Nyalife;
});