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
});


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
                    // FIX 4: Use server-provided absolute URL
                    window.location.href = response.redirect;
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