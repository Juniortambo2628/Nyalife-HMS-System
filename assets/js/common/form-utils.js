/**
 * Nyalife HMS - Form Utility Functions
 * 
 * This file contains common functions for form handling, validation, and submission.
 * Updated to use the core forms module when available.
 */

// Form utilities namespace - delegates to core module when available
const FormUtils = {
    /**
     * Initialize a form with validation
     * @param {string} formId - The ID of the form to initialize
     * @param {object} options - Configuration options
     * @returns {boolean} Whether initialization was successful
     */
    initForm: function(formId, options = {}) {
        // Use core NyalifeForms module if available
        if (window.NyalifeForms && typeof NyalifeForms.initForm === 'function') {
            // Convert options to match NyalifeForms
            const coreOptions = {
                validateOnBlur: options.validateOnBlur,
                validateOnSubmit: options.validateOnSubmit,
                submitViaAjax: options.submitHandler ? false : true,
                resetAfterSubmit: options.resetAfterSubmit,
                customValidator: options.customValidation,
                showErrors: options.useToasts !== false,
                onValid: options.onValid,
                onInvalid: options.onInvalid,
                onSuccess: options.onSuccess,
                onError: options.onError
            };
            
            return NyalifeForms.initForm(formId, coreOptions);
        }
        
        // Fallback to legacy implementation
        const form = document.getElementById(formId);
        if (!form) {
            console.error(`Form with ID "${formId}" not found`);
            return false;
        }

        // Default options
        const defaultOptions = {
            validateOnBlur: true,
            validateOnSubmit: true,
            customValidation: null,
            onValid: null,
            onInvalid: null,
            submitHandler: null,
            resetAfterSubmit: false,
            useToasts: true,
            disableSubmitOnInvalid: true
        };

        // Merge options
        const mergedOptions = {...defaultOptions, ...options };

        // Validate fields on blur
        if (mergedOptions.validateOnBlur) {
            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('blur', () => {
                    this.validateField(field);
                });
            });
        }

        // Handle form submission
        if (mergedOptions.validateOnSubmit || mergedOptions.submitHandler) {
            form.addEventListener('submit', (event) => {
                if (mergedOptions.validateOnSubmit) {
                    event.preventDefault();

                    // Validate all fields
                    const isValid = this.validateForm(form,
                        mergedOptions.customValidation);

                    // Call the appropriate callback
                    if (isValid) {
                        if (typeof mergedOptions.onValid === 'function') {
                            mergedOptions.onValid(form);
                        }

                        // Handle form submission
                        if (mergedOptions.submitHandler) {
                            mergedOptions.submitHandler(form, event);
                        } else {
                            form.submit();
                        }

                        // Reset form if needed
                        if (mergedOptions.resetAfterSubmit) {
                            form.reset();
                        }
                    } else {
                        if (typeof mergedOptions.onInvalid === 'function') {
                            mergedOptions.onInvalid(form);
                        }

                        // Use NyalifeCoreUI if available, otherwise fall back to ModalUtils
                        if (mergedOptions.useToasts) {
                            if (window.NyalifeCoreUI && typeof NyalifeCoreUI.showNotification === 'function') {
                                NyalifeCoreUI.showNotification('error', 'Please check the form for errors');
                            } else if (window.ModalUtils && typeof ModalUtils.showToast === 'function') {
                                ModalUtils.showToast(
                                    'Validation Error',
                                    'Please check the form for errors',
                                    'error'
                                );
                            }
                        }
                    }
                } else if (mergedOptions.submitHandler) {
                    event.preventDefault();
                    mergedOptions.submitHandler(form, event);
                }
            });
        }

        return true;
    },

    /**
     * Validate an individual form field
     * @param {HTMLElement} field - The field to validate
     * @param {function} customValidator - Optional custom validator function
     * @returns {boolean} Whether the field is valid
     */
    validateField: function(field, customValidator = null) {
        // Skip disabled or hidden fields
        if (field.disabled || field.type === 'hidden') {
            return true;
        }

        let isValid = field.checkValidity();
        let errorMessage = '';

        // Get validation message
        if (!isValid) {
            errorMessage = field.validationMessage;
        }

        // Apply custom validation if provided
        if (isValid && typeof customValidator === 'function') {
            const customValidation = customValidator(field);
            if (customValidation !== true) {
                isValid = false;
                errorMessage = customValidation || 'Invalid value';
            }
        }

        // Update UI based on validation result
        this.updateFieldValidationUI(field, isValid, errorMessage);

        return isValid;
    },

    /**
     * Validate an entire form
     * @param {HTMLElement|string} form - The form element or form ID
     * @param {function} customValidator - Optional custom validator function
     * @returns {boolean} Whether the form is valid
     */
    validateForm: function(form, customValidator = null) {
        // Get form element if string was provided
        if (typeof form === 'string') {
            form = document.getElementById(form);
        }

        if (!form) {
            console.error('Form not found');
            return false;
        }

        // Track overall form validity
        let isValid = true;

        // Validate each field
        form.querySelectorAll('input, select, textarea').forEach(field => {
            const fieldValid = this.validateField(field, customValidator);
            if (!fieldValid) {
                isValid = false;

                // Focus the first invalid field
                if (field.classList.contains('is-invalid') && !field.dataset.focusedInvalid) {
                    field.focus();
                    field.dataset.focusedInvalid = 'true';
                }
            }
        });

        return isValid;
    },

    /**
     * Update a field's UI based on validation status
     * @param {HTMLElement} field - The field to update
     * @param {boolean} isValid - Whether the field is valid
     * @param {string} errorMessage - Error message to display if invalid
     */
    updateFieldValidationUI: function(field, isValid, errorMessage = '') {
        // Remove existing validation classes
        field.classList.remove('is-valid', 'is-invalid');

        // Remove existing error messages
        const fieldContainer = field.closest('.form-group, .mb-3');
        if (fieldContainer) {
            const existingFeedback = fieldContainer.querySelector('.invalid-feedback');
            if (existingFeedback) {
                existingFeedback.remove();
            }
        }

        // Add appropriate class based on validation result
        if (isValid) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');

            // Add error message
            if (errorMessage && fieldContainer) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = errorMessage;
                fieldContainer.appendChild(feedback);
            }
        }
    },

    /**
     * Reset a form's validation state
     * @param {HTMLElement|string} form - The form element or form ID
     * @param {boolean} resetValues - Whether to also reset form values
     */
    resetFormValidation: function(form, resetValues = false) {
        // Get form element if string was provided
        if (typeof form === 'string') {
            form = document.getElementById(form);
        }

        if (!form) {
            console.error('Form not found');
            return;
        }

        // Reset form values if requested
        if (resetValues) {
            form.reset();
        }

        // Reset validation state
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.classList.remove('is-valid', 'is-invalid');
            delete field.dataset.focusedInvalid;
        });

        // Remove error messages
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
    },

    /**
     * Serialize form data into an object
     * @param {HTMLElement|string} form - The form element or form ID
     * @returns {object} Serialized form data
     */
    serializeForm: function(form) {
        // Get form element if string was provided
        if (typeof form === 'string') {
            form = document.getElementById(form);
        }

        if (!form) {
            console.error('Form not found');
            return {};
        }

        // Use FormData API to get form data
        const formData = new FormData(form);
        const serialized = {};

        // Convert FormData to a regular object
        for (const [key, value] of formData.entries()) {
            // Handle array inputs (checkboxes, multi-selects)
            if (key.endsWith('[]')) {
                const k = key.slice(0, -2);
                if (!serialized[k]) {
                    serialized[k] = [];
                }
                serialized[k].push(value);
            } else {
                serialized[key] = value;
            }
        }

        return serialized;
    },

    /**
     * Populate a form with data
     * @param {HTMLElement|string} form - The form element or form ID
     * @param {object} data - Data to populate the form with
     */
    populateForm: function(form, data) {
        // Get form element if string was provided
        if (typeof form === 'string') {
            form = document.getElementById(form);
        }

        if (!form || !data) {
            console.error('Form or data not provided');
            return;
        }

        // Loop through form elements
        form.querySelectorAll('input, select, textarea').forEach(field => {
            const name = field.name || field.id;

            if (name && data.hasOwnProperty(name)) {
                // Set value based on element type
                if (field.type === 'checkbox') {
                    field.checked = Boolean(data[name]);
                } else if (field.type === 'radio') {
                    field.checked = (field.value === data[name]);
                } else if (field.tagName === 'SELECT' && field.multiple) {
                    // For multi-select
                    if (Array.isArray(data[name])) {
                        Array.from(field.options).forEach(option => {
                            option.selected = data[name].includes(option.value);
                        });
                    }
                } else {
                    field.value = data[name];
                }

                // Trigger change event for any elements that need it
                if (field.tagName === 'SELECT') {
                    field.dispatchEvent(new Event('change'));
                }
            }
        });
    },

    /**
     * Submit a form via AJAX
     * @param {HTMLElement|string} form - The form element or form ID
     * @param {object} options - AJAX options
     * @returns {Promise} Promise that resolves with the response
     */
    submitFormAjax: function(form, options = {}) {
        // Get form element if string was provided
        if (typeof form === 'string') {
            form = document.getElementById(form);
        }

        if (!form) {
            return Promise.reject(new Error('Form not found'));
        }

        // Default options
        const defaultOptions = {
            method: form.method || 'POST',
            url: form.action || window.location.href,
            contentType: 'application/json',
            dataType: 'json',
            validate: true,
            resetOnSuccess: false,
            showSuccessMessage: true,
            successMessage: 'Form submitted successfully',
            showErrorMessage: true,
            errorMessage: 'Error submitting form'
        };

        // Merge options
        const mergedOptions = {...defaultOptions, ...options };

        // Validate form if required
        if (mergedOptions.validate && !this.validateForm(form)) {
            return Promise.reject(new Error('Form validation failed'));
        }

        // Serialize form data
        const formData = this.serializeForm(form);

        // Create request options
        const requestOptions = {
            method: mergedOptions.method,
            headers: {
                'Content-Type': mergedOptions.contentType
            }
        };

        // Add body based on content type
        if (mergedOptions.contentType === 'application/json') {
            requestOptions.body = JSON.stringify(formData);
        } else if (mergedOptions.contentType === 'application/x-www-form-urlencoded') {
            const urlEncoded = new URLSearchParams();
            for (const key in formData) {
                urlEncoded.append(key, formData[key]);
            }
            requestOptions.body = urlEncoded.toString();
        } else {
            // Use FormData for multipart/form-data
            requestOptions.body = new FormData(form);
            delete requestOptions.headers['Content-Type']; // Let browser set it
        }

        // Show loader if available
        if (window.NyalifeLoader) {
            NyalifeLoader.show('Submitting...');
        }

        // Send the request
        return fetch(mergedOptions.url, requestOptions)
            .then(response => {
                // Try to parse response as JSON
                if (mergedOptions.dataType === 'json') {
                    return response.json()
                        .catch(() => {
                            // If parsing fails, return text
                            return response.text();
                        })
                        .then(data => {
                            // Check if response is ok (status 200-299)
                            if (!response.ok) {
                                throw new Error(
                                    typeof data === 'object' && data.message ?
                                    data.message :
                                    mergedOptions.errorMessage
                                );
                            }
                            return data;
                        });
                } else {
                    // Return text for other types
                    return response.text().then(text => {
                        if (!response.ok) {
                            throw new Error(text || mergedOptions.errorMessage);
                        }
                        return text;
                    });
                }
            })
            .then(data => {
                // Hide loader if available
                if (window.NyalifeLoader) {
                    NyalifeLoader.hide();
                }

                // Show success message if enabled
                if (mergedOptions.showSuccessMessage && window.ModalUtils) {
                    let message = mergedOptions.successMessage;

                    // Use message from response if available
                    if (typeof data === 'object' && data.message) {
                        message = data.message;
                    }

                    ModalUtils.showToast('Success', message, 'success');
                }

                // Reset form if requested
                if (mergedOptions.resetOnSuccess) {
                    form.reset();
                    this.resetFormValidation(form);
                }

                return data;
            })
            .catch(error => {
                // Hide loader if available
                if (window.NyalifeLoader) {
                    NyalifeLoader.hide();
                }

                // Show error message if enabled
                if (mergedOptions.showErrorMessage && window.ModalUtils) {
                    ModalUtils.showToast('Error', error.message, 'error');
                }

                throw error;
            });
    }
};

// Export for ES modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormUtils;
}