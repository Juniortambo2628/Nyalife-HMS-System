/**
 * Nyalife HMS - AJAX Utilities
 * 
 * This file contains utility functions for making AJAX requests consistently.
 * Updated to use the core API module when available.
 */

// AJAX utilities namespace - uses core API module when available
const AjaxUtils = {
    /**
     * Make an AJAX request using the Fetch API
     * 
     * @param {string} url - The URL to send the request to
     * @param {object} options - Request options
     * @returns {Promise} - Promise that resolves with the response data
     */
    request: function(url, options = {}) {
        // Use core NyalifeAPI module if available
        if (window.NyalifeAPI && typeof NyalifeAPI.request === 'function') {
            // Convert options format to match NyalifeAPI
            const apiOptions = {
                method: options.method || 'GET',
                data: options.data || null,
                headers: options.headers || {},
                showLoader: options.showLoader,
                loaderMessage: options.loaderMessage,
                handleErrors: options.handleError
            };
            
            return NyalifeAPI.request(url, apiOptions);
        }
        
        // Fallback to original implementation
        // Default options
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            data: null,
            showLoader: true,
            loaderMessage: 'Loading...',
            handleError: true
        };

        // Merge options
        const mergedOptions = {...defaultOptions, ...options };

        // Create fetch options
        const fetchOptions = {
            method: mergedOptions.method,
            headers: mergedOptions.headers,
            credentials: 'same-origin' // Include cookies for same-origin requests
        };

        // Add body data if provided
        if (mergedOptions.data) {
            if (mergedOptions.headers['Content-Type'] === 'application/json') {
                fetchOptions.body = JSON.stringify(mergedOptions.data);
            } else if (mergedOptions.headers['Content-Type'] === 'application/x-www-form-urlencoded') {
                const urlEncoded = new URLSearchParams();
                for (const key in mergedOptions.data) {
                    urlEncoded.append(key, mergedOptions.data[key]);
                }
                fetchOptions.body = urlEncoded.toString();
            } else if (mergedOptions.data instanceof FormData) {
                fetchOptions.body = mergedOptions.data;
                // Let the browser set Content-Type for FormData
                delete fetchOptions.headers['Content-Type'];
            }
        }

        // Show loader if available
        if (mergedOptions.showLoader && window.NyalifeLoader) {
            window.NyalifeLoader.show(mergedOptions.loaderMessage);
        }

        // Send the request
        return fetch(url, fetchOptions)
            .then(response => {
                // Try to parse response as JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => {
                        // Return both response and parsed data
                        return { response, data };
                    });
                } else {
                    // Parse as text if not JSON
                    return response.text().then(text => {
                        return { response, data: text };
                    });
                }
            })
            .then(({ response, data }) => {
                // Hide loader if available
                if (mergedOptions.showLoader && window.NyalifeLoader) {
                    window.NyalifeLoader.hide();
                }

                // Check if response was successful
                if (!response.ok) {
                    // Handle error response
                    const error = new Error(
                        typeof data === 'object' && data.message ?
                        data.message :
                        `Request failed with status ${response.status}`
                    );
                    error.response = response;
                    error.data = data;
                    throw error;
                }

                return data;
            })
            .catch(error => {
                // Hide loader if available
                if (mergedOptions.showLoader && window.NyalifeLoader) {
                    window.NyalifeLoader.hide();
                }

                // Handle error display - use NyalifeCoreUI if available, otherwise fall back to ModalUtils
                if (mergedOptions.handleError) {
                    const message = error.message || 'An error occurred while processing your request.';
                    if (window.NyalifeCoreUI && typeof NyalifeCoreUI.showNotification === 'function') {
                        NyalifeCoreUI.showNotification('error', message);
                    } else if (window.ModalUtils && typeof ModalUtils.showToast === 'function') {
                        ModalUtils.showToast('Error', message, 'error');
                    }
                }

                throw error;
            });
    },

    /**
     * Shorthand for GET requests
     * 
     * @param {string} url - The URL to send the request to
     * @param {object} options - Request options
     * @returns {Promise} - Promise that resolves with the response data
     */
    get: function(url, options = {}) {
        return this.request(url, {
            method: 'GET',
            ...options
        });
    },

    /**
     * Shorthand for POST requests
     * 
     * @param {string} url - The URL to send the request to
     * @param {object} data - The data to send
     * @param {object} options - Request options
     * @returns {Promise} - Promise that resolves with the response data
     */
    post: function(url, data, options = {}) {
        return this.request(url, {
            method: 'POST',
            data: data,
            ...options
        });
    },

    /**
     * Shorthand for PUT requests
     * 
     * @param {string} url - The URL to send the request to
     * @param {object} data - The data to send
     * @param {object} options - Request options
     * @returns {Promise} - Promise that resolves with the response data
     */
    put: function(url, data, options = {}) {
        return this.request(url, {
            method: 'PUT',
            data: data,
            ...options
        });
    },

    /**
     * Shorthand for DELETE requests
     * 
     * @param {string} url - The URL to send the request to
     * @param {object} options - Request options
     * @returns {Promise} - Promise that resolves with the response data
     */
    delete: function(url, options = {}) {
        return this.request(url, {
            method: 'DELETE',
            ...options
        });
    },

    /**
     * Submit a form via AJAX (convenience method that uses FormUtils)
     * 
     * @param {string} formId - The ID of the form to submit
     * @param {object} options - Options for form submission
     * @returns {Promise} - Promise that resolves with the response data
     */
    submitForm: function(formId, options = {}) {
        if (window.FormUtils && typeof FormUtils.submitFormAjax === 'function') {
            return FormUtils.submitFormAjax(formId, options);
        } else {
            console.error('FormUtils not available. Include form-utils.js before using this method.');
            return Promise.reject(new Error('FormUtils not available'));
        }
    }
};

// Export for ES modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AjaxUtils;
}