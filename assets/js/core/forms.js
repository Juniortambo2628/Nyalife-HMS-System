/**
 * Nyalife HMS - Core Forms Module
 * 
 * Provides unified form handling, validation, and submission
 */

const NyalifeForms = (function() {
    // Private variables
    const defaults = {
        validateOnBlur: true,
        validateOnSubmit: true,
        submitViaAjax: true,
        resetAfterSubmit: false,
        showErrors: true,
        scrollToErrors: true,
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorFeedbackClass: 'invalid-feedback',
        successFeedbackClass: 'valid-feedback'
    };
    
    /**
     * Validate a single form field
     * @param {HTMLElement} field - The field to validate
     * @param {Function} customValidator - Optional custom validation function
     * @returns {Object} Validation result with isValid and message
     */
    function validateField(field, customValidator) {
        // Skip validation for disabled or hidden fields
        if (field.disabled || field.type === 'hidden') {
            return { isValid: true };
        }
        
        // Basic HTML5 validation
        let isValid = field.checkValidity();
        let message = field.validationMessage;
        
        // Apply custom validation if provided and basic validation passed
        if (isValid && typeof customValidator === 'function') {
            const customResult = customValidator(field);
            
            // Custom validator can return boolean or object
            if (typeof customResult === 'boolean') {
                isValid = customResult;
                message = isValid ? '' : 'Invalid value';
            } else if (customResult && typeof customResult === 'object') {
                isValid = !!customResult.isValid;
                message = customResult.message || message;
            }
        }
        
        return { isValid, message };
    }
    
    /**
     * Update field UI based on validation state
     * @param {HTMLElement} field - The field to update
     * @param {boolean} isValid - Whether the field is valid
     * @param {string} message - Error or success message
     * @param {Object} options - Validation options
     */
    function updateFieldUI(field, isValid, message, options) {
        // Remove existing validation classes
        field.classList.remove(options.errorClass, options.successClass);
        
        // Remove existing feedback elements
        const fieldContainer = field.closest('.form-group, .mb-3') || field.parentElement;
        if (fieldContainer) {
            fieldContainer.querySelectorAll('.' + options.errorFeedbackClass + ', .' + options.successFeedbackClass)
                .forEach(el => el.remove());
        }
        
        // Add appropriate class based on validation result
        if (isValid) {
            field.classList.add(options.successClass);
        } else {
            field.classList.add(options.errorClass);
            
            // Add feedback message if provided
            if (message && fieldContainer) {
                const feedback = document.createElement('div');
                feedback.className = options.errorFeedbackClass;
                feedback.innerHTML = message;
                fieldContainer.appendChild(feedback);
            }
        }
    }
    
    // Public methods
    return {
        /**
         * Initialize a form with validation and submission handling
         * @param {string|HTMLElement} form - Form element or form ID
         * @param {Object} options - Configuration options
         * @returns {boolean} Whether initialization was successful
         */
        initForm: function(form, options = {}) {
            // Get form element if string was provided
            if (typeof form === 'string') {
                form = document.getElementById(form);
            }
            
            if (!form || form.tagName !== 'FORM') {
                console.error('Invalid form provided');
                return false;
            }
            
            // Merge options with defaults
            const settings = {...defaults, ...options};
            
            // Store settings on form element
            form._nyalifeSettings = settings;
            
            // Handle field validation on blur
            if (settings.validateOnBlur) {
                form.querySelectorAll('input, select, textarea').forEach(field => {
                    field.addEventListener('blur', () => {
                        this.validateField(field, settings.customValidator);
                    });
                });
            }
            
            // Handle form submission
            form.addEventListener('submit', event => {
                if (settings.validateOnSubmit) {
                    // Validate the form first
                    const isValid = this.validateForm(form, settings.customValidator);
                    
                    if (!isValid) {
                        event.preventDefault();
                        
                        if (settings.scrollToErrors) {
                            // Focus first invalid field
                            const firstInvalid = form.querySelector('.' + settings.errorClass);
                            if (firstInvalid) {
                                firstInvalid.focus();
                                // Scroll to field
                                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                        
                        // Call onInvalid callback if provided
                        if (typeof settings.onInvalid === 'function') {
                            settings.onInvalid(form);
                        }
                        
                        return false;
                    }
                    
                    // Call onValid callback if provided
                    if (typeof settings.onValid === 'function') {
                        settings.onValid(form);
                    }
                }
                
                // If we should submit via AJAX
                if (settings.submitViaAjax) {
                    event.preventDefault();
                    
                    this.submitFormAjax(form, settings)
                        .then(response => {
                            // Call onSuccess callback if provided
                            if (typeof settings.onSuccess === 'function') {
                                settings.onSuccess(response, form);
                            }
                            
                            // Reset form if needed
                            if (settings.resetAfterSubmit) {
                                form.reset();
                            }
                        })
                        .catch(error => {
                            // Call onError callback if provided
                            if (typeof settings.onError === 'function') {
                                settings.onError(error, form);
                            }
                        });
                }
            });
            
            return true;
        },
        
        /**
         * Validate a form field
         * @param {HTMLElement} field - The field to validate
         * @param {Function} customValidator - Optional custom validation function
         * @returns {boolean} Whether the field is valid
         */
        validateField: function(field, customValidator) {
            // Get settings from the parent form
            const form = field.closest('form');
            const settings = form && form._nyalifeSettings ? form._nyalifeSettings : defaults;
            
            // Validate the field
            const result = validateField(field, customValidator);
            
            // Update UI
            updateFieldUI(field, result.isValid, result.message, settings);
            
            return result.isValid;
        },
        
        /**
         * Validate an entire form
         * @param {string|HTMLElement} form - Form element or form ID
         * @param {Function} customValidator - Optional custom validation function
         * @returns {boolean} Whether the form is valid
         */
        validateForm: function(form, customValidator) {
            // Get form element if string was provided
            if (typeof form === 'string') {
                form = document.getElementById(form);
            }
            
            if (!form || form.tagName !== 'FORM') {
                console.error('Invalid form provided');
                return false;
            }
            
            // Get form settings
            const settings = form._nyalifeSettings || defaults;
            
            // Track form validity
            let isValid = true;
            
            // Validate each field
            form.querySelectorAll('input, select, textarea').forEach(field => {
                const fieldValid = this.validateField(field, customValidator);
                if (!fieldValid) {
                    isValid = false;
                }
            });
            
            return isValid;
        },
        
        /**
         * Submit a form via AJAX
         * @param {string|HTMLElement} form - Form element or form ID
         * @param {Object} options - Additional options
         * @returns {Promise} Promise resolving with the response data
         */
        submitFormAjax: function(form, options = {}) {
            // Get form element if string was provided
            if (typeof form === 'string') {
                form = document.getElementById(form);
            }
            
            if (!form || form.tagName !== 'FORM') {
                return Promise.reject(new Error('Invalid form provided'));
            }
            
            // Get form settings
            const settings = {...(form._nyalifeSettings || defaults), ...options};
            
            // Use NyalifeAPI if available
            if (window.NyalifeAPI && typeof NyalifeAPI.submitForm === 'function') {
                return NyalifeAPI.submitForm(form, {
                    showLoader: settings.showLoader !== false,
                    loaderMessage: settings.loaderMessage || 'Submitting...'
                }).catch(error => {
                    // Enhanced error handling
                    console.log('Form submission error:', error);
                    
                    // Check for HTML response (likely a redirect or error page)
                    if (error.isHtml || (error.responseText && error.responseText.trim().startsWith('<!DOCTYPE'))) {
                        if (window.NyalifeCoreUI) {
                            NyalifeCoreUI.showNotification('error', 'The server returned an unexpected response. You may need to log in again.');
                        }
                        
                        // If this appears to be a login redirect, we could redirect the user
                        if (error.redirected) {
                            setTimeout(() => {
                                window.location.href = error.response.url || '/login.php';
                            }, 2000);
                        }
                        
                        throw new Error('Server returned HTML instead of JSON. Possible authentication issue.');
                    }
                    
                    // Handle validation errors from server
                    if (error.data && typeof error.data === 'object' && error.data.errors) {
                        if (settings.showErrors && window.NyalifeCoreUI) {
                            NyalifeCoreUI.showFormErrors(form, error.data.errors);
                        }
                    }
                    throw error;
                });
            }
            
            // Fallback if NyalifeAPI is not available
            return new Promise((resolve, reject) => {
                // Show loader if available
                if (settings.showLoader !== false && window.NyalifeLoader) {
                    NyalifeLoader.show(settings.loaderMessage || 'Submitting...');
                }
                
                // Extract form data
                const formData = new FormData(form);
                const method = form.getAttribute('method') || 'POST';
                const action = form.getAttribute('action') || window.location.href;
                
                // Create AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open(method, action, true);
                
                // Set default headers
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                
                // Parse and set custom headers from data-headers attribute
                try {
                    const headersAttr = form.getAttribute('data-headers');
                    if (headersAttr) {
                        const headers = JSON.parse(headersAttr);
                        Object.entries(headers).forEach(([key, value]) => {
                            xhr.setRequestHeader(key, value);
                        });
                    }
                } catch (e) {
                    console.warn('Failed to parse data-headers attribute:', e);
                }
                
                // Handle response
                xhr.onload = function() {
                    // Hide loader if available
                    if (settings.showLoader !== false && window.NyalifeLoader) {
                        NyalifeLoader.hide();
                    }
                    
                    let responseData = xhr.responseText;
                    
                    // Try to parse as JSON
                    try {
                        responseData = JSON.parse(xhr.responseText);
                    } catch (e) {
                        // Not JSON, leave as text
                    }
                    
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve(responseData);
                    } else {
                        const error = new Error(xhr.statusText);
                        error.xhr = xhr;
                        error.data = responseData;
                        
                        // Handle validation errors from server
                        if (responseData && typeof responseData === 'object' && responseData.errors) {
                            if (settings.showErrors && window.NyalifeCoreUI) {
                                NyalifeCoreUI.showFormErrors(form, responseData.errors);
                            }
                        }
                        
                        reject(error);
                    }
                };
                
                // Handle network errors
                xhr.onerror = function() {
                    // Hide loader if available
                    if (settings.showLoader !== false && window.NyalifeLoader) {
                        NyalifeLoader.hide();
                    }
                    
                    reject(new Error('Network error occurred'));
                };
                
                // Send the request
                xhr.send(formData);
            });
        },
        
        /**
         * Fill a form with data
         * @param {string|HTMLElement} form - Form element or form ID
         * @param {Object} data - Data to fill the form with
         */
        fillForm: function(form, data) {
            // Get form element if string was provided
            if (typeof form === 'string') {
                form = document.getElementById(form);
            }
            
            if (!form || form.tagName !== 'FORM' || !data) {
                return;
            }
            
            // Iterate through form fields
            form.querySelectorAll('[name]').forEach(field => {
                const name = field.getAttribute('name');
                if (data[name] !== undefined) {
                    // Handle different field types
                    switch (field.type) {
                        case 'checkbox':
                            field.checked = !!data[name];
                            break;
                        case 'radio':
                            field.checked = field.value === String(data[name]);
                            break;
                        case 'select-multiple':
                            // Handle multi-select
                            if (Array.isArray(data[name])) {
                                Array.from(field.options).forEach(option => {
                                    option.selected = data[name].includes(option.value);
                                });
                            }
                            break;
                        default:
                            field.value = data[name];
                    }
                    
                    // Trigger change event
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }
    };
})();

// For CommonJS environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NyalifeForms;
}
