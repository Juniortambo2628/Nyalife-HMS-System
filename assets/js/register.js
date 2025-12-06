/**
 * Nyalife HMS - Registration JavaScript
 * 
 * This file contains the JavaScript functionality for handling registration form submissions.
 * Updated to use the Nyalife core modules for form handling, API calls, and notifications.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if core modules are available
    const usingCoreModules = typeof NyalifeForms !== 'undefined' && typeof NyalifeAPI !== 'undefined';
    
    if (usingCoreModules) {
        // Using data attributes for form handling, most functionality is automatic
        // This script only handles any custom callbacks or additional functionality
        
        // Find all registration forms
        const registrationForms = document.querySelectorAll('#registrationForm, [data-nyalife-form="true"][action*="register"]');
        
        registrationForms.forEach(form => {
            // Get the form ID or generate one if needed
            const formId = form.id || 'registrationForm-' + Math.random().toString(36).substr(2, 9);
            if (!form.id) form.id = formId;
            
            // Initialize the form with the core module
            NyalifeForms.initForm(formId, {
                validateOnBlur: true,
                submitViaAjax: true,
                resetAfterSubmit: true,
                onSuccess: function(response) {
                    // Show success message
                    NyalifeCoreUI.showNotification('success', response.message || 'Registration successful!');
                    
                    // Redirect to login page after delay
                    setTimeout(function() {
                        // Show login modal if it exists
                        const loginModal = document.getElementById('loginModal');
                        if (loginModal) {
                            const modal = new bootstrap.Modal(loginModal);
                            modal.show();
                        } else {
                            // Otherwise redirect to home page
                            window.location.href = '/Nyalife-HMS-System/';
                        }
                    }, 2000);
                },
                onError: function(error) {
                    // Error handling is already done by the core module
                    console.error('Registration error:', error);
                }
            });
        });
    } else {
        // Fallback for legacy support
        if (typeof jQuery !== 'undefined') {
            // Handle registration form submission using jQuery
            $('#registrationForm').on('submit', function(e) {
                e.preventDefault();

                // Show the loader
                if (typeof NyalifeLoader !== 'undefined') {
                    NyalifeLoader.show('Processing registration...');
                }

                // Get form data
                const formData = $(this).serialize();
                const action = $(this).attr('action');

                // Send AJAX request
                $.ajax({
                    type: 'POST',
                    url: action,
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            if (typeof showAlert === 'function') {
                                showAlert('success', response.message);
                            }

                            // Reset form
                            $('#registrationForm')[0].reset();

                            // Redirect to login page after delay
                            setTimeout(function() {
                                // Show login modal if it exists
                                if ($('#loginModal').length) {
                                    $('#loginModal').modal('show');
                                } else {
                                    // Otherwise redirect to home page
                                    window.location.href = '/Nyalife-HMS-System/';
                                }
                            }, 2000);
                        } else {
                            // Show error message
                            if (typeof showAlert === 'function') {
                                showAlert('error', response.message);
                            } else {
                                alert(response.message);
                            }
                        }

                        // Hide the loader
                        if (typeof NyalifeLoader !== 'undefined') {
                            NyalifeLoader.hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        // Show error message
                        if (typeof showAlert === 'function') {
                            showAlert('error', 'An error occurred during registration. Please try again.');
                        } else {
                            alert('An error occurred during registration. Please try again.');
                        }

                        // Hide the loader
                        if (typeof NyalifeLoader !== 'undefined') {
                            NyalifeLoader.hide();
                        }
                    }
                });
            });
            
            // Password validation
            $('#password, #confirm_password').on('keyup', function() {
                const password = $('#password').val();
                const confirmPassword = $('#confirm_password').val();

                // Check if passwords match
                if (password !== '' && confirmPassword !== '') {
                    if (password === confirmPassword) {
                        $('#confirm_password').removeClass('is-invalid').addClass('is-valid');
                        $('#passwordMatchFeedback').removeClass('invalid-feedback').addClass('valid-feedback').text('Passwords match');
                    } else {
                        $('#confirm_password').removeClass('is-valid').addClass('is-invalid');
                        $('#passwordMatchFeedback').removeClass('valid-feedback').addClass('invalid-feedback').text('Passwords do not match');
                    }
                }
            });
        }
    }
});