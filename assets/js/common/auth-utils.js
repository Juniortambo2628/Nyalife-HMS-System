/**
 * Nyalife HMS - Authentication Utilities
 * 
 * This file contains utility functions for authentication forms.
 * Updated to use the core modules for consistency.
 */

// Import core modules and initialize forms - maintains compatibility with existing code
document.addEventListener('DOMContentLoaded', function() {
    // This file now serves as a compatibility layer
    // The actual logic is in ../auth.js to avoid duplication

    // For backward compatibility, check if auth.js has loaded
    if (typeof initAuthForms === 'function') {
        // If auth.js is loaded, let it handle initialization
        return;
    }

    // Otherwise handle initialization here for backward compatibility
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        if (window.NyalifeForms) {
            // Use core forms module if available
            NyalifeForms.initForm(loginForm, {
                submitViaAjax: true,
                resetAfterSubmit: false,
                loaderMessage: 'Authenticating...'
            });
        } else {
            // Fall back to legacy initialization
            initLoginForm();
        }
    }

    const registerForm = document.getElementById('registerPatientForm');
    if (registerForm) {
        if (window.NyalifeForms) {
            // Use core forms module if available
            NyalifeForms.initForm(registerForm, {
                submitViaAjax: true,
                resetAfterSubmit: true,
                loaderMessage: 'Creating account...'
            });
        } else {
            // Fall back to legacy initialization
            initRegisterForm();
        }
    }

    AuthUtils.init();
});

/**
 * Show login alert message
 * @param {string} type - Alert type (success, danger, warning, info)
 * @param {string} message - Alert message
 */
function showLoginAlert(type, message) {
    const alertDiv = $('#loginAlert');
    alertDiv.removeClass().addClass('alert alert-' + type);
    alertDiv.html(message);
    alertDiv.show();

    // Auto-hide after 5 seconds for success messages
    if (type === 'success') {
        setTimeout(function() {
            alertDiv.fadeOut();
        }, 5000);
    }
}

/**
 * Initialize login form functionality (works for both modal and regular forms)
 */
function initLoginForm() {
    $.ajaxSetup({
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        $('#loginAlert').hide();
        $('#loginSpinner').removeClass('d-none');

        const formData = $(this).serialize();
        const actionUrl = $(this).attr('action');

        $.ajax({
            type: 'POST',
            url: actionUrl,
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Handle redirect properly
                    if (response.redirect) {
                        // Ensure the redirect URL is absolute
                        let redirectUrl = response.redirect;
                        
                        // If it's not an absolute URL (doesn't start with http:// or https://),
                        // and it doesn't start with a slash, add a slash
                        if (!redirectUrl.match(/^https?:\/\//i) && !redirectUrl.startsWith('/')) {
                            redirectUrl = '/' + redirectUrl;
                        }
                        
                        // Get the base URL from meta tag if available
                        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
                        const baseUrl = baseUrlMeta ? baseUrlMeta.getAttribute('content') : '';
                        
                        // If we have a baseUrl and the redirectUrl is relative (starts with /),
                        // combine them properly
                        if (baseUrl && redirectUrl.startsWith('/')) {
                            // Remove trailing slash from baseUrl if present
                            const cleanBaseUrl = baseUrl.replace(/\/$/, '');
                            redirectUrl = cleanBaseUrl + redirectUrl;
                        }
                        
                        console.log('Redirecting to:', redirectUrl);
                        window.location.href = redirectUrl;
                    } else {
                        // Fallback to dashboard
                        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
                        const baseUrl = baseUrlMeta ? baseUrlMeta.getAttribute('content') : '';
                        window.location.href = baseUrl ? baseUrl + '/dashboard' : '/dashboard';
                    }
                } else {
                    showLoginAlert('danger', response.message);
                    $('#loginSpinner').addClass('d-none');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred. Please try again.';

                // FIX 5: Better error parsing
                try {
                    const data = JSON.parse(xhr.responseText);
                    errorMessage = data.message || errorMessage;
                } catch (e) {
                    console.error('Error parsing response', e);
                }

                showLoginAlert('danger', errorMessage);
                $('#loginSpinner').addClass('d-none');
            }
        });
    });
}

/**
 * Initialize registration form functionality (works for both modal and regular forms)
 */
function initRegisterForm() {
    // Ensure AJAX requests are properly identified
    $.ajaxSetup({
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
    // Show registration alert message
    window.showRegisterAlert = function(type, message) {
        const alertDiv = $('#registerPatientAlert');
        alertDiv.removeClass().addClass('alert alert-' + type);
        alertDiv.html(message);
        alertDiv.show();

        // Auto-hide after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(function() {
                alertDiv.fadeOut();
            }, 5000);
        }
    };

    // Handle registration form submission with AJAX
    $('#registerPatientForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous alerts
        $('#registerPatientAlert').hide();

        // Show spinner
        $('#registerPatientSpinner').removeClass('d-none');

        // Get form data
        const formData = $(this).serialize();
        const actionUrl = $(this).attr('action');

        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: actionUrl,
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showRegisterAlert('success', response.message);

                    // Reset form
                    $('#registerPatientForm')[0].reset();

                    // Redirect or show login after successful registration
                    setTimeout(function() {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            $('#registerPatientModal').modal('hide');
                            $('#loginModal').modal('show');
                        }
                    }, 2000);
                } else {
                    // Show error message
                    showRegisterAlert('danger', response.message);
                }

                // Hide spinner
                $('#registerPatientSpinner').addClass('d-none');
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors with improved debugging
                console.log('Registration error:', error);
                console.log('Response text:', xhr.responseText);
                console.log('Response status:', xhr.status);
                console.log('Response headers:', xhr.getAllResponseHeaders());

                let errorMessage = 'An error occurred. Please try again.';

                // Try to parse the response to see if it's returning HTML instead of JSON
                if (xhr.responseText && xhr.responseText.indexOf('<!DOCTYPE') >= 0) {
                    errorMessage = 'Server error: The server returned HTML instead of JSON.';
                }

                // If the response seems to be JSON but had parsing issues
                if (error === 'parsererror') {
                    errorMessage = 'Invalid JSON response from server.';
                    // Try to extract error message from the response
                    try {
                        const responseStart = xhr.responseText.substring(0, 100);
                        errorMessage += ' Response starts with: ' + responseStart;
                    } catch (e) {
                        // If we can't extract the response text, ignore
                    }
                }

                showRegisterAlert('danger', errorMessage);
                $('#registerPatientSpinner').addClass('d-none');
            }
        });
    });
}

/**
 * Authentication Utilities
 * Provides client-side authentication helpers
 */

const AuthUtils = {
    /**
     * Check if user is logged in by checking session
     */
    isLoggedIn: function() {
        // This could check for a session token or make an API call
        return document.body.hasAttribute('data-user-logged-in');
    },

    /**
     * Get current user role
     */
    getUserRole: function() {
        return document.body.getAttribute('data-user-role') || null;
    },

    /**
     * Get current user ID
     */
    getUserId: function() {
        return document.body.getAttribute('data-user-id') || null;
    },

    /**
     * Logout user
     */
    logout: function() {
        window.location.href = BASE_URL + '/logout';
    },

    /**
     * Redirect to login page
     */
    redirectToLogin: function() {
        window.location.href = BASE_URL + '/login';
    },

    /**
     * Check if user has specific role
     */
    hasRole: function(role) {
        const userRole = this.getUserRole();
        if (Array.isArray(role)) {
            return role.includes(userRole);
        }
        return userRole === role;
    },

    /**
     * Check if user has any of the specified roles
     */
    hasAnyRole: function(roles) {
        const userRole = this.getUserRole();
        return roles.includes(userRole);
    },

    /**
     * Set authentication data on page load
     */
    setAuthData: function(userId, userRole, isLoggedIn = true) {
        if (isLoggedIn) {
            document.body.setAttribute('data-user-logged-in', 'true');
            document.body.setAttribute('data-user-id', userId);
            document.body.setAttribute('data-user-role', userRole);
        } else {
            document.body.removeAttribute('data-user-logged-in');
            document.body.removeAttribute('data-user-id');
            document.body.removeAttribute('data-user-role');
        }
    },

    /**
     * Initialize auth utilities
     */
    init: function() {
        // Auto-logout on certain conditions
        this.checkSessionExpiry();
        
        // Set up periodic session check
        setInterval(() => {
            this.checkSessionExpiry();
        }, 300000); // Check every 5 minutes
    },

    /**
     * Check session expiry (basic implementation)
     */
    checkSessionExpiry: function() {
        // This is a basic implementation
        // In a real application, you might want to make an AJAX call to check session status
        if (this.isLoggedIn()) {
            // Optionally ping the server to verify session is still active
            // fetch(BASE_URL + '/api/session/check').then(response => {
            //     if (!response.ok) {
            //         this.redirectToLogin();
            //     }
            // });
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    AuthUtils.init();
});

// Export for global access
window.AuthUtils = AuthUtils;