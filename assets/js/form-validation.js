/**
 * Nyalife HMS - Form Validation
 * Handles real-time validation, data persistence, and error handling for forms
 * 
 * This file is maintained for backward compatibility with the new core forms module.
 * New implementations should use the NyalifeForms module directly.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if core modules are available
    if (typeof NyalifeForms !== 'undefined') {
        // Register this legacy module with the core framework
        if (typeof NyalifeUtils !== 'undefined') {
            NyalifeUtils.log('Form validation module loaded - Running in compatibility mode');
        }
        
        // Initialize only the components not handled by the core framework
        initializePasswordValidation();
        initializeFormPersistence();
        setupModalBehavior();
        loadSavedFormData();
    } else {
        // Core framework not available, initialize all components
        console.log('Core framework not detected - Running in standalone mode');
        initializeFormValidation();
        initializePasswordValidation();
        initializeFormPersistence();
        setupLiveFormValidation();
        setupModalBehavior();
        loadSavedFormData();
        
        // Force immediate validation initialization
        setTimeout(function() {
            setupLiveFormValidation();
        }, 100);
    }
});

/**
 * Initialize form validation for all forms
 */
function initializeFormValidation() {
    // Password requirement validation
    initializePasswordValidation();

    // Save form data as user types
    initializeFormPersistence();

    // Prevent modal closing on form errors
    preventModalCloseOnErrors();

    // Initialize form submission handlers with validation
    initializeFormSubmissionHandlers();
}

/**
 * Validate password requirements in real-time
 */
function initializePasswordValidation() {
    // Get all password fields
    const passwordFields = document.querySelectorAll('input[type="password"][id$="password"]');

    passwordFields.forEach(function(passwordField) {
        const form = passwordField.closest('form');

        // Skip if not in a form or not a primary password field (could be confirm_password)
        if (!form || passwordField.id.includes('confirm')) return;

        // Create password requirements UI if not exists
        let requirementsContainer = form.querySelector('.password-requirements');

        if (!requirementsContainer) {
            requirementsContainer = document.createElement('div');
            requirementsContainer.className = 'password-requirements small mt-1';
            passwordField.parentNode.appendChild(requirementsContainer);

            requirementsContainer.innerHTML = `
                <div class="requirements-title text-muted mb-1">Password must contain:</div>
                <div class="d-flex flex-wrap">
                    <div class="requirement me-3 mb-1" data-requirement="length">
                        <i class="fas fa-times-circle text-danger me-1"></i> At least 8 characters
                    </div>
                    <div class="requirement me-3 mb-1" data-requirement="uppercase">
                        <i class="fas fa-times-circle text-danger me-1"></i> Uppercase letter
                    </div>
                    <div class="requirement me-3 mb-1" data-requirement="lowercase">
                        <i class="fas fa-times-circle text-danger me-1"></i> Lowercase letter
                    </div>
                    <div class="requirement me-3 mb-1" data-requirement="number">
                        <i class="fas fa-times-circle text-danger me-1"></i> Number
                    </div>
                </div>
            `;
        }

        // Show requirements immediately when password field is focused
        passwordField.addEventListener('focus', function() {
            requirementsContainer.style.display = 'block';
        });

        // Add input event listener for real-time validation
        passwordField.addEventListener('input', function() {
            const password = this.value;
            validatePassword(password, requirementsContainer);

            // If there's a confirm password field, validate it too
            const confirmField = form.querySelector('input[id$="confirm_password"]');
            if (confirmField && confirmField.value) {
                validatePasswordMatch(password, confirmField.value, confirmField);
            }
        });

        // Add confirm password validation if exists
        const confirmField = form.querySelector('input[id$="confirm_password"]');
        if (confirmField) {
            // Create or find feedback element
            let feedbackElement = confirmField.nextElementSibling;
            if (!feedbackElement || !feedbackElement.classList.contains('password-match-feedback')) {
                feedbackElement = document.createElement('div');
                feedbackElement.className = 'password-match-feedback small mt-1';
                confirmField.parentNode.appendChild(feedbackElement);
            }

            confirmField.addEventListener('input', function() {
                const password = passwordField.value;
                const confirmPassword = this.value;
                validatePasswordMatch(password, confirmPassword, this);
            });

            // Also validate on focus
            confirmField.addEventListener('focus', function() {
                if (this.value) {
                    const password = passwordField.value;
                    validatePasswordMatch(password, this.value, this);
                }
            });
        }

        // Initial validation
        if (passwordField.value) {
            validatePassword(passwordField.value, requirementsContainer);
        }
    });
}

/**
 * Validate password against requirements
 */
function validatePassword(password, requirementsContainer) {
    // Get all requirement elements
    const requirements = {
        length: {
            element: requirementsContainer.querySelector('[data-requirement="length"]'),
            valid: password.length >= 8
        },
        uppercase: {
            element: requirementsContainer.querySelector('[data-requirement="uppercase"]'),
            valid: /[A-Z]/.test(password)
        },
        lowercase: {
            element: requirementsContainer.querySelector('[data-requirement="lowercase"]'),
            valid: /[a-z]/.test(password)
        },
        number: {
            element: requirementsContainer.querySelector('[data-requirement="number"]'),
            valid: /[0-9]/.test(password)
        }
    };

    // Update each requirement's status
    let allValid = true;

    Object.keys(requirements).forEach(function(req) {
        const requirement = requirements[req];
        if (!requirement.element) return; // Skip if element doesn't exist

        const icon = requirement.element.querySelector('i');
        if (!icon) return; // Skip if icon doesn't exist

        if (requirement.valid) {
            icon.className = 'fas fa-check-circle text-success me-1';
            requirement.element.classList.remove('text-danger');
            requirement.element.classList.add('text-success');
        } else {
            icon.className = 'fas fa-times-circle text-danger me-1';
            requirement.element.classList.remove('text-success');
            requirement.element.classList.add('text-danger');
            allValid = false;
        }
    });

    // Update form validation state
    const form = requirementsContainer.closest('form');
    const passwordInput = form.querySelector('input[type="password"]:not([id$="confirm_password"])');

    if (allValid) {
        passwordInput.setCustomValidity('');
        passwordInput.classList.remove('is-invalid');
        passwordInput.classList.add('is-valid');
    } else {
        passwordInput.setCustomValidity('Password does not meet all requirements');
        passwordInput.classList.remove('is-valid');
        passwordInput.classList.add('is-invalid');
    }

    return allValid;
}

/**
 * Validate that passwords match
 */
function validatePasswordMatch(password, confirmPassword, confirmField) {
    const feedbackElement = confirmField.parentNode.querySelector('.password-match-feedback');

    if (!feedbackElement) return;

    if (!confirmPassword) {
        feedbackElement.innerHTML = '';
        confirmField.classList.remove('is-valid', 'is-invalid');
        return;
    }

    if (password === confirmPassword) {
        feedbackElement.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> Passwords match';
        feedbackElement.className = 'password-match-feedback small mt-1 text-success';
        confirmField.setCustomValidity('');
        confirmField.classList.remove('is-invalid');
        confirmField.classList.add('is-valid');
        return true;
    } else {
        feedbackElement.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i> Passwords do not match';
        feedbackElement.className = 'password-match-feedback small mt-1 text-danger';
        confirmField.setCustomValidity('Passwords do not match');
        confirmField.classList.remove('is-valid');
        confirmField.classList.add('is-invalid');
        return false;
    }
}

/**
 * Initialize form data persistence using localStorage
 */
function initializeFormPersistence() {
    const forms = document.querySelectorAll('form');

    forms.forEach(function(form) {
        // Skip login forms for security reasons
        if (form.id === 'loginForm') return;

        const formId = form.id || `form-${Math.random().toString(36).substr(2, 9)}`;
        if (!form.id) form.id = formId;

        // Save form data as user types with debounce
        let saveTimeout;

        form.addEventListener('input', function(e) {
            // Don't save password fields
            if (e.target.type === 'password') return;

            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                saveFormData(form);
            }, 300); // Reduce timeout for quicker saving
        });

        // Add event to show "form data saved" indicator
        form.addEventListener('change', function(e) {
            // Don't save password fields
            if (e.target.type === 'password') return;

            saveFormData(form);

            // Show saved indicator
            let savedIndicator = form.querySelector('.form-saved-indicator');
            if (!savedIndicator) {
                savedIndicator = document.createElement('div');
                savedIndicator.className = 'form-saved-indicator text-success small mt-2 mb-2';
                savedIndicator.innerHTML = '<i class="fas fa-check-circle"></i> Form data saved';
                savedIndicator.style.position = 'fixed';
                savedIndicator.style.bottom = '10px';
                savedIndicator.style.right = '10px';
                savedIndicator.style.padding = '8px 15px';
                savedIndicator.style.backgroundColor = 'rgba(255,255,255,0.9)';
                savedIndicator.style.borderRadius = '4px';
                savedIndicator.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
                savedIndicator.style.zIndex = '9999';
                savedIndicator.style.opacity = '0';
                savedIndicator.style.transition = 'opacity 0.3s ease-in-out';
                document.body.appendChild(savedIndicator);
            }

            // Show and hide the indicator
            savedIndicator.style.opacity = '1';
            setTimeout(() => {
                savedIndicator.style.opacity = '0';
            }, 2000);
        });

        // Clear saved data on successful submission
        form.addEventListener('submit', function() {
            // We'll clear it only after successful submission in the AJAX handlers
        });

        // Add reset button event to clear saved data
        const resetButton = form.querySelector('button[type="reset"]');
        if (resetButton) {
            resetButton.addEventListener('click', function() {
                clearSavedFormData(formId);
            });
        }
    });
}

/**
 * Save form data to localStorage
 */
function saveFormData(form) {
    const formId = form.id;
    const formData = {};

    // Get all fields except passwords
    const fields = form.querySelectorAll('input:not([type="password"]), select, textarea');

    fields.forEach(function(field) {
        if (!field.name) return;

        if (field.type === 'checkbox' || field.type === 'radio') {
            formData[field.name] = field.checked;
        } else {
            formData[field.name] = field.value;
        }
    });

    localStorage.setItem(`nyalife_form_${formId}`, JSON.stringify(formData));
    return formData; // Return the saved data for possible use
}

/**
 * Load saved form data from localStorage
 */
function loadSavedFormData() {
    const forms = document.querySelectorAll('form');

    forms.forEach(function(form) {
        // Skip login forms for security reasons
        if (form.id === 'loginForm') return;

        const formId = form.id;
        if (!formId) return;

        const savedData = localStorage.getItem(`nyalife_form_${formId}`);
        if (!savedData) return;

        try {
            const formData = JSON.parse(savedData);
            let fieldsRestored = false;

            // Populate form fields
            Object.keys(formData).forEach(function(fieldName) {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (!field) return;

                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = formData[fieldName];
                    fieldsRestored = true;
                } else if (field.value !== formData[fieldName]) { // Only update if different
                    field.value = formData[fieldName];
                    fieldsRestored = true;

                    // Trigger change event to update any dependent fields
                    const event = new Event('change', { bubbles: true });
                    field.dispatchEvent(event);
                }
            });

            // Only show notification if fields were actually restored
            if (fieldsRestored) {
                // Add notification about restored form
                const formNotification = document.createElement('div');
                formNotification.className = 'alert alert-info alert-dismissible fade show mb-3';
                formNotification.innerHTML = `
                    <small>Your previously entered information has been restored. 
                    <button type="button" class="btn btn-sm btn-outline-primary clear-form-data ms-2">Clear saved data</button></small>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                // Insert at the beginning of the form or modal body
                const modalBody = form.closest('.modal-body');
                if (modalBody) {
                    modalBody.insertBefore(formNotification, modalBody.firstChild);
                } else {
                    form.prepend(formNotification);
                }

                // Add clear button event
                const clearButton = formNotification.querySelector('.clear-form-data');
                clearButton.addEventListener('click', function() {
                    clearSavedFormData(formId);
                    form.reset();
                    formNotification.remove();
                });
            }
        } catch (e) {
            console.error('Error loading saved form data:', e);
        }
    });
}

/**
 * Clear saved form data
 */
function clearSavedFormData(formId) {
    localStorage.removeItem(`nyalife_form_${formId}`);
}

/**
 * Prevent modals from closing when there are form errors
 */
function preventModalCloseOnErrors() {
    // Add event listener for data-bs-dismiss clicks
    document.addEventListener('click', function(e) {
        const dismissTrigger = e.target.closest('[data-bs-dismiss="modal"]');
        if (!dismissTrigger) return;

        const modal = dismissTrigger.closest('.modal');
        if (!modal) return;

        // Check if there's an active form with errors
        const form = modal.querySelector('form');
        if (!form) return;

        const hasInvalidFields = form.querySelector('.is-invalid');
        const hasErrorAlert = modal.querySelector('.alert-danger:not(.d-none)');

        if (hasInvalidFields || hasErrorAlert) {
            // Prevent default action
            e.preventDefault();
            e.stopPropagation();

            // Add shake animation to form
            form.classList.add('animate__animated', 'animate__shakeX');
            setTimeout(() => {
                form.classList.remove('animate__animated', 'animate__shakeX');
            }, 1000);

            // Scroll to first error
            const firstError = form.querySelector('.is-invalid, .alert-danger:not(.d-none)');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }, true);

    // Modify modal behavior to prevent closing on backdrop click if there are errors
    document.addEventListener('show.bs.modal', function(e) {
        const modal = e.target;
        const form = modal.querySelector('form');
        if (!form) return;

        // Store reference to original backdrop click handler
        const backdropClickHandler = function(event) {
            if (event.target === modal) {
                const hasInvalidFields = form.querySelector('.is-invalid');
                const hasErrorAlert = modal.querySelector('.alert-danger:not(.d-none)');

                if (hasInvalidFields || hasErrorAlert) {
                    // Prevent modal from closing
                    event.stopPropagation();

                    // Add shake animation to form
                    form.classList.add('animate__animated', 'animate__shakeX');
                    setTimeout(() => {
                        form.classList.remove('animate__animated', 'animate__shakeX');
                    }, 1000);
                }
            }
        };

        // Add event listener
        modal.addEventListener('click', backdropClickHandler);

        // Store handler to remove it later
        modal._backdropClickHandler = backdropClickHandler;
    });

    // Clean up event listener when modal is hidden
    document.addEventListener('hidden.bs.modal', function(e) {
        const modal = e.target;
        if (modal._backdropClickHandler) {
            modal.removeEventListener('click', modal._backdropClickHandler);
            delete modal._backdropClickHandler;
        }
    });
}

/**
 * Initialize form submission handlers with validation
 */
function initializeFormSubmissionHandlers() {
    // Update registration form handler
    const registerForm = document.getElementById('registerPatientForm');

    if (registerForm) {
        // Add live validation for all fields
        const inputs = registerForm.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                // Save form data as user types
                saveFormData(registerForm);

                // Validate field immediately
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });

        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Remove any existing alerts
            const alertElement = document.getElementById('registerPatientAlert');
            if (alertElement) {
                alertElement.className = 'alert d-none';
                alertElement.textContent = '';
                alertElement.style.display = 'none';
            }

            // Check form validity first
            if (!registerForm.checkValidity()) {
                e.stopPropagation();
                registerForm.classList.add('was-validated');

                // Show error message
                if (alertElement) {
                    alertElement.className = 'alert alert-danger';
                    alertElement.textContent = 'Please fix the highlighted errors before submitting.';
                    alertElement.style.display = 'block';
                }

                // Shake the form
                registerForm.classList.add('animate__animated', 'animate__shakeX');
                setTimeout(() => {
                    registerForm.classList.remove('animate__animated', 'animate__shakeX');
                }, 1000);

                // Scroll to first error
                const firstError = registerForm.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                return;
            }

            // Show loader
            const spinner = document.getElementById('registerPatientSpinner');
            if (spinner) {
                spinner.classList.remove('d-none');
            }

            // Get form data
            const formData = new FormData(registerForm);

            // Send AJAX request
            fetch(registerForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loader
                    if (spinner) {
                        spinner.classList.add('d-none');
                    }

                    if (data.success) {
                        // Show success message
                        if (alertElement) {
                            alertElement.className = 'alert alert-success';
                            alertElement.textContent = data.message;
                            alertElement.style.display = 'block';
                        }

                        // Clear saved form data
                        clearSavedFormData(registerForm.id);

                        // Reset form
                        registerForm.reset();

                        // Hide modal and show login after delay
                        setTimeout(() => {
                            const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerPatientModal'));
                            if (registerModal) {
                                registerModal.hide();

                                // Show login modal
                                setTimeout(() => {
                                    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                                    loginModal.show();
                                }, 500);
                            }
                        }, 2000);
                    } else {
                        // Show error message
                        if (alertElement) {
                            alertElement.className = 'alert alert-danger';
                            alertElement.textContent = data.message;
                            alertElement.style.display = 'block';
                        }

                        // Highlight the field with error if mentioned in the message
                        const errorMessage = data.message.toLowerCase();
                        const fields = registerForm.querySelectorAll('input, select, textarea');

                        fields.forEach(field => {
                            const fieldName = field.name.replace('_', ' ');
                            if (errorMessage.includes(fieldName)) {
                                field.classList.add('is-invalid');

                                // Scroll to the field
                                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        });
                    }
                })
                .catch(error => {
                    // Hide loader
                    if (spinner) {
                        spinner.classList.add('d-none');
                    }

                    // Show error message
                    if (alertElement) {
                        alertElement.className = 'alert alert-danger';
                        alertElement.textContent = 'An error occurred during registration. Please try again.';
                        alertElement.style.display = 'block';
                    }

                    console.error('Registration error:', error);
                });
        });
    }

    // Update login form handler
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Check form validity first
            if (!loginForm.checkValidity()) {
                e.stopPropagation();
                loginForm.classList.add('was-validated');
                return;
            }

            // Show loader
            if (typeof NyalifeLoader !== 'undefined') {
                NyalifeLoader.show('Authenticating...');
            }

            // Get form data
            const formData = new FormData(loginForm);

            // Send AJAX request
            fetch(loginForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const loginAlert = document.getElementById('loginAlert');

                    if (data.success) {
                        // Show success message
                        if (loginAlert) {
                            loginAlert.className = 'alert alert-success';
                            loginAlert.textContent = data.message;
                        } else if (typeof showAlert === 'function') {
                            showAlert('success', data.message);
                        }

                        // Redirect to dashboard
                        setTimeout(function() {
                            window.location.href = data.data.redirect || '/';
                        }, 1000);
                    } else {
                        // Hide loader
                        if (typeof NyalifeLoader !== 'undefined') {
                            NyalifeLoader.hide();
                        }

                        // Show error message
                        if (loginAlert) {
                            loginAlert.className = 'alert alert-danger';
                            loginAlert.textContent = data.message;
                        } else if (typeof showAlert === 'function') {
                            showAlert('error', data.message);
                        }
                    }
                })
                .catch(error => {
                    // Hide loader
                    if (typeof NyalifeLoader !== 'undefined') {
                        NyalifeLoader.hide();
                    }

                    // Show error message
                    const loginAlert = document.getElementById('loginAlert');
                    if (loginAlert) {
                        loginAlert.className = 'alert alert-danger';
                        loginAlert.textContent = 'An error occurred. Please try again.';
                    } else if (typeof showAlert === 'function') {
                        showAlert('error', 'An error occurred. Please try again.');
                    }

                    console.error('Login error:', error);
                });
        });
    }
}

/**
 * Set up live validation for all form fields
 */
function setupLiveFormValidation() {
    console.log('Setting up live form validation');
    // Target both login and registration forms
    const forms = document.querySelectorAll('#loginForm, #registerPatientForm');

    forms.forEach(form => {
        console.log('Processing form:', form.id);
        const inputs = form.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            // Skip submit buttons
            if (input.type === 'submit' || input.type === 'button') return;

            // Skip checkboxes for real-time validation
            if (input.type === 'checkbox') return;

            // Add input event for real-time validation
            input.addEventListener('input', function() {
                validateInput(this);

                // Special handling for passwords
                if (input.type === 'password') {
                    const passwordRequirements = form.querySelector('.password-requirements');
                    if (passwordRequirements) {
                        validatePassword(input.value, passwordRequirements);
                    }

                    // If this is a confirm password field, check matching
                    if (input.id === 'confirm_password') {
                        const password = form.querySelector('#password, #modal_password');
                        if (password && input.value !== password.value) {
                            input.setCustomValidity('Passwords do not match');
                            markInputAsInvalid(input, 'Passwords do not match');
                        } else {
                            input.setCustomValidity('');
                            markInputAsValid(input);
                        }
                    }
                }
            });

            // Add blur event for validation when focus leaves the field
            input.addEventListener('blur', function() {
                validateInput(this);
            });
        });

        // Add submit handler to forms
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Validate all inputs on submit
            inputs.forEach(input => {
                if (!validateInput(input)) {
                    isValid = false;
                }
            });

            // Check if passwords match for registration form
            if (form.id === 'registerPatientForm') {
                const password = form.querySelector('#password');
                const confirmPassword = form.querySelector('#confirm_password');

                if (password && confirmPassword && password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                    markInputAsInvalid(confirmPassword, 'Passwords do not match');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();

                // Show alert message
                const alertElement = form.querySelector('.alert');
                if (alertElement) {
                    alertElement.className = 'alert alert-danger';
                    alertElement.textContent = 'Please fix the errors before submitting.';
                    alertElement.style.display = 'block';
                }

                // Add shake animation to the form
                form.classList.add('animate__animated', 'animate__shakeX');
                setTimeout(() => {
                    form.classList.remove('animate__animated', 'animate__shakeX');
                }, 1000);
            }
        });
    });
}

/**
 * Ensure password requirements are visible
 */
function ensurePasswordRequirements(passwordField) {
    const form = passwordField.closest('form');
    let requirementsContainer = form.querySelector('.password-requirements');

    if (!requirementsContainer) {
        console.log('Creating password requirements for:', passwordField.id);
        requirementsContainer = document.createElement('div');
        requirementsContainer.className = 'password-requirements small mt-1';
        passwordField.parentNode.appendChild(requirementsContainer);

        requirementsContainer.innerHTML = `
            <div class="requirements-title text-muted mb-1">Password must contain:</div>
            <div class="d-flex flex-wrap">
                <div class="requirement me-3 mb-1" data-requirement="length">
                    <i class="fas fa-times-circle text-danger me-1"></i> At least 8 characters
                </div>
                <div class="requirement me-3 mb-1" data-requirement="uppercase">
                    <i class="fas fa-times-circle text-danger me-1"></i> Uppercase letter
                </div>
                <div class="requirement me-3 mb-1" data-requirement="lowercase">
                    <i class="fas fa-times-circle text-danger me-1"></i> Lowercase letter
                </div>
                <div class="requirement me-3 mb-1" data-requirement="number">
                    <i class="fas fa-times-circle text-danger me-1"></i> Number
                </div>
            </div>
        `;
    }

    return requirementsContainer;
}

/**
 * Validate a single field
 */
function validateField(field, showFeedback = false) {
    // Skip disabled or readonly fields
    if (field.disabled || field.readOnly) return true;

    let isValid = true;
    let feedbackMessage = '';

    // Remove existing feedback
    const existingFeedback = field.parentNode.querySelector('.validation-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }

    // Type-specific validation
    if (field.type === 'email') {
        isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value);
        if (!isValid && field.value.trim()) {
            feedbackMessage = 'Please enter a valid email address';
        }
    } else if (field.type === 'password' && !field.id.includes('confirm')) {
        // Password validation
        const requirementsContainer = field.parentNode.querySelector('.password-requirements');
        if (requirementsContainer && field.value.trim()) {
            isValid = validatePassword(field.value, requirementsContainer);
            feedbackMessage = 'Password does not meet requirements';
        }
    } else if (field.id.includes('confirm_password')) {
        // Password confirmation
        const passwordField = field.form.querySelector('input[type="password"]:not([id$="confirm_password"])');
        isValid = (field.value === passwordField.value);
        if (!isValid && field.value.trim()) {
            feedbackMessage = 'Passwords do not match';
        }
    } else if (field.required && field.value.trim() === '') {
        isValid = false;
        feedbackMessage = 'This field is required';
    }

    // Update field styling
    if (isValid) {
        field.classList.remove('is-invalid');
        if (field.value.trim() !== '') {
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
        }
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }

    // Show feedback message if requested
    if (showFeedback && !isValid && feedbackMessage) {
        const feedbackElement = document.createElement('div');
        feedbackElement.className = 'validation-feedback small text-danger mt-1';
        feedbackElement.textContent = feedbackMessage;
        field.parentNode.appendChild(feedbackElement);
    }

    return isValid;
}

/**
 * Validate an individual input element
 * @param {HTMLElement} input - The input element to validate
 * @returns {boolean} - Whether the input is valid
 */
function validateInput(input) {
    // Skip disabled fields
    if (input.disabled) return true;

    // Get validation message if any
    let isValid = input.checkValidity();

    if (isValid) {
        markInputAsValid(input);
    } else {
        let message = '';

        // Generate appropriate message based on validation state
        if (input.validity.valueMissing) {
            message = 'This field is required';
        } else if (input.validity.typeMismatch) {
            message = `Please enter a valid ${input.type}`;
        } else if (input.validity.tooShort) {
            message = `Please enter at least ${input.minLength} characters`;
        } else if (input.validity.tooLong) {
            message = `Please enter at most ${input.maxLength} characters`;
        } else if (input.validity.rangeUnderflow) {
            message = `Please enter a value of at least ${input.min}`;
        } else if (input.validity.rangeOverflow) {
            message = `Please enter a value of at most ${input.max}`;
        } else if (input.validity.patternMismatch) {
            message = input.title || 'Please match the requested format';
        } else if (input.validity.customError) {
            message = input.validationMessage;
        } else {
            message = 'Invalid value';
        }

        markInputAsInvalid(input, message);
    }

    return isValid;
}

/**
 * Mark an input as valid with visual indicators
 * @param {HTMLElement} input - The input element
 */
function markInputAsValid(input) {
    input.classList.add('is-valid');
    input.classList.remove('is-invalid');

    // Clear any existing feedback elements
    const formGroup = input.closest('.mb-3') || input.parentElement;
    const existingFeedback = formGroup.querySelector('.invalid-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }

    // Add valid feedback if needed
    if (!formGroup.querySelector('.valid-feedback')) {
        const feedback = document.createElement('div');
        feedback.className = 'valid-feedback';
        feedback.textContent = 'Looks good!';
        formGroup.appendChild(feedback);
    }
}

/**
 * Mark an input as invalid with visual indicators and error message
 * @param {HTMLElement} input - The input element
 * @param {string} message - The error message to display
 */
function markInputAsInvalid(input, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');

    // Find the container for the feedback
    const formGroup = input.closest('.mb-3') || input.parentElement;

    // Clear any existing feedback elements
    const existingFeedback = formGroup.querySelector('.invalid-feedback, .valid-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }

    // Add invalid feedback with the error message
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    feedback.textContent = message;
    formGroup.appendChild(feedback);
}

/**
 * Set up proper modal behavior to prevent closing with validation errors
 */
function setupModalBehavior() {
    // Get all modals with forms
    const modals = document.querySelectorAll('.modal');

    modals.forEach(modal => {
        const form = modal.querySelector('form');
        if (!form) return;

        // Get the modal instance
        const modalInstance = new bootstrap.Modal(modal);

        // Store original modal hiding behavior
        const originalHide = modalInstance.hide;

        // Override the hide method to check form validation first
        modalInstance.hide = function() {
            const isValid = form.checkValidity();

            // If the form has unsaved valid data, confirm before closing
            if (isValid && formHasChanges(form)) {
                if (!confirm('You have unsaved changes. Are you sure you want to close this form?')) {
                    return;
                }
            }

            // Call the original hide method
            originalHide.call(modalInstance);
        };

        // Handle the hide event to prevent closing with validation errors
        modal.addEventListener('hide.bs.modal', function(e) {
            const isValid = form.checkValidity();
            const hasChanges = formHasChanges(form);

            // If form is invalid and has changes, prevent closing
            if (!isValid && hasChanges) {
                e.preventDefault();
                e.stopPropagation();

                // Show validation errors
                form.classList.add('was-validated');

                // Show alert if available
                const alert = form.querySelector('.alert');
                if (alert) {
                    alert.className = 'alert alert-danger';
                    alert.textContent = 'Please fix the errors before closing.';
                    alert.style.display = 'block';
                }

                // Add shake effect
                form.classList.add('animate__animated', 'animate__shakeX');
                setTimeout(() => {
                    form.classList.remove('animate__animated', 'animate__shakeX');
                }, 1000);
            }
        });
    });
}

/**
 * Check if a form has user-entered changes
 * @param {HTMLElement} form - The form to check
 * @returns {boolean} - Whether the form has changes
 */
function formHasChanges(form) {
    const formData = new FormData(form);
    let hasChanges = false;

    for (const [name, value] of formData.entries()) {
        const input = form.elements[name];

        // Skip buttons
        if (input.type === 'submit' || input.type === 'button') continue;

        // For checkboxes and radio buttons
        if (input.type === 'checkbox' || input.type === 'radio') {
            if (input.checked !== input.defaultChecked) {
                hasChanges = true;
                break;
            }
        }
        // For select elements
        else if (input.tagName === 'SELECT') {
            if (input.selectedIndex !== input.defaultSelectedIndex) {
                hasChanges = true;
                break;
            }
        }
        // For everything else
        else if (value !== input.defaultValue) {
            hasChanges = true;
            break;
        }
    }

    return hasChanges;
}