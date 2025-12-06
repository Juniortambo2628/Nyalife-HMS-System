/**
 * Nyalife HMS - Core API Module
 * 
 * Provides a unified way to make API requests with consistent error handling
 */

const NyalifeAPI = (function() {
    // Private variables
    const defaultOptions = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        showLoader: true,
        handleErrors: true
    };

    /**
     * Shows the loader if available
     * @param {string} message - Message to display in loader
     */
    function showLoader(message = 'Loading...') {
        if (window.NyalifeLoader && typeof window.NyalifeLoader.show === 'function') {
            window.NyalifeLoader.show(message);
        } else {
            console.log('Loader: ' + message);
        }
    }

    /**
     * Hides the loader if available
     */
    function hideLoader() {
        if (window.NyalifeLoader && typeof window.NyalifeLoader.hide === 'function') {
            window.NyalifeLoader.hide();
        } else {
            console.log('Loader hidden');
        }
    }

    /**
     * Handle API errors in a consistent way
     * @param {Error|Object} error - Error object or response data
     */
    function handleError(error) {
        if (window.NyalifeCoreUI && typeof window.NyalifeCoreUI.showNotification === 'function') {
            let message = 'An error occurred while processing your request.';

            if (error.message) {
                message = error.message;
            } else if (typeof error === 'string') {
                message = error;
            } else if (error.data && error.data.message) {
                message = error.data.message;
            }

            window.NyalifeCoreUI.showNotification('error', message);
        }

        // Log to console for debugging
        console.error('API Error:', error);
    }

    /**
     * Process the response from an API call
     * @param {Response} response - Fetch Response object
     * @returns {Promise} - Promise resolving to the parsed data
     */
    function processResponse(response) {
        // Check for redirection - handle login redirects differently
        // Handle redirects for successful responses (status 200-299)
        if (response.redirected && response.ok) {
            return response.text().then((text) => {
                try {
                    // First try to parse as JSON
                    const jsonData = JSON.parse(text);
                    return {
                        ...jsonData,
                        success: true,
                        redirected: true,
                        redirect: response.url
                    };
                } catch (e) {
                    // If not JSON, return as text
                    return {
                        success: true,
                        message: 'Authentication successful',
                        redirect: response.url,
                        redirected: true
                    };
                }
            });
        }

        // Get content type to determine how to parse the response
        const contentType = response.headers.get('Content-Type') || '';

        // First try to get the response as text to inspect it
        return response.text().then(text => {
            // Check if we got HTML despite expecting JSON
            const looksLikeHtml = text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html');

            if (contentType.includes('application/json') && !looksLikeHtml) {
                // It's valid JSON, parse it
                try {
                    const data = JSON.parse(text);
                    if (!response.ok) {
                        const error = new Error(data.message || response.statusText);
                        error.response = response;
                        error.data = data;
                        throw error;
                    }
                    return data;
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    const error = new Error('Invalid JSON response from server');
                    error.originalError = e;
                    error.responseText = text;
                    throw error;
                }
            }

            // Handle text response (HTML or plain text)
            if (!response.ok) {
                const error = new Error(response.statusText || 'Server error');
                error.response = response;
                error.responseText = text;
                error.isHtml = looksLikeHtml;
                throw error;
            }

            return text;
        });
    }

    /**
     * Prepare the request body based on data and content type
     * @param {Object|FormData} data - The data to send
     * @param {Object} headers - Request headers
     * @returns {string|FormData|URLSearchParams|null} - Processed body
     */
    function prepareRequestBody(data, headers) {
        if (!data) return null;

        // Handle FormData directly
        if (data instanceof FormData) {
            // Let the browser set content type for FormData
            delete headers['Content-Type'];
            return data;
        }

        // Handle URL encoded form data
        if (headers['Content-Type'] === 'application/x-www-form-urlencoded') {
            if (typeof data === 'string') {
                return data; // Already encoded
            }

            // Create URL search params
            const params = new URLSearchParams();
            for (const key in data) {
                params.append(key, data[key]);
            }
            return params.toString();
        }

        // Default to JSON
        if (headers['Content-Type'] === 'application/json') {
            return JSON.stringify(data);
        }

        return data;
    }

    // Public methods
    return {
        // Public loader functions
        showLoader,
        hideLoader,

        // Public request headers
        defaultHeaders: defaultOptions.headers,

        /**
         * Make a request to the API
         * @param {string} url - The endpoint URL
         * @param {Object} options - Request options
         * @returns {Promise} - Promise resolving to the response data
         */
        request: function(url, options = {}) {
            // Merge with default options
            const mergedOptions = {...defaultOptions, ...options };
            const { method = 'GET', data, showLoader, loaderMessage, handleErrors, ...fetchOptions } = mergedOptions;

            // Prepare headers
            const headers = {...defaultOptions.headers, ...fetchOptions.headers };

            // Build fetch options
            const reqOptions = {
                method,
                headers,
                credentials: 'same-origin',
                ...fetchOptions
            };

            // Add body if we have data
            if (data) {
                reqOptions.body = prepareRequestBody(data, headers);
            }

            // Show loader if enabled
            if (showLoader) {
                // Call the module-level showLoader function, not the parameter
                NyalifeAPI.showLoader(loaderMessage || 'Loading...');
            }

            // Make the request
            return fetch(url, reqOptions)
                .then(response => {
                    return processResponse(response);
                })
                .then(data => {
                    if (showLoader) NyalifeAPI.hideLoader();
                    return data;
                })
                .catch(error => {
                    if (showLoader) NyalifeAPI.hideLoader();
                    if (handleErrors) handleError(error);
                    throw error;
                });
        },

        /**
         * Make a GET request
         * @param {string} url - The endpoint URL
         * @param {Object} options - Request options
         * @returns {Promise} - Promise resolving to the response data
         */
        get: function(url, options = {}) {
            return this.request(url, {
                method: 'GET',
                ...options
            });
        },

        /**
         * Make a POST request
         * @param {string} url - The endpoint URL
         * @param {Object} data - The data to send
         * @param {Object} options - Request options
         * @returns {Promise} - Promise resolving to the response data
         */
        post: function(url, data, options = {}) {
            return this.request(url, {
                method: 'POST',
                data,
                ...options
            });
        },

        /**
         * Make a PUT request
         * @param {string} url - The endpoint URL
         * @param {Object} data - The data to send
         * @param {Object} options - Request options
         * @returns {Promise} - Promise resolving to the response data
         */
        put: function(url, data, options = {}) {
            return this.request(url, {
                method: 'PUT',
                data,
                ...options
            });
        },

        /**
         * Make a DELETE request
         * @param {string} url - The endpoint URL
         * @param {Object} options - Request options
         * @returns {Promise} - Promise resolving to the response data
         */
        delete: function(url, options = {}) {
            return this.request(url, {
                method: 'DELETE',
                ...options
            });
        },

        /**
         * Submit a form via AJAX
         * @param {string|Element} form - Form element or form ID
         * @param {Object} options - Additional options
         * @returns {Promise} - Promise resolving to the response data
         */
        submitForm: function(form, options = {}) {
            // Get form element if string was provided
            if (typeof form === 'string') {
                form = document.getElementById(form);
            }

            if (!form || form.tagName !== 'FORM') {
                return Promise.reject(new Error('Invalid form provided'));
            }

            // Extract form data
            const formData = new FormData(form);
            const method = (form.getAttribute('method') || 'POST').toUpperCase();
            const action = form.getAttribute('action') || window.location.href;

            // Determine content type based on form enctype
            const enctype = form.getAttribute('enctype') || 'application/x-www-form-urlencoded';
            const headers = {
                'Content-Type': enctype,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            };

            // Parse and add custom headers from data-headers attribute
            try {
                const headersAttr = form.getAttribute('data-headers');
                if (headersAttr) {
                    const customHeaders = JSON.parse(headersAttr);
                    Object.assign(headers, customHeaders);
                }
            } catch (e) {
                console.warn('Failed to parse data-headers attribute:', e);
            }

            // Make the request
            return this.request(action, {
                method,
                data: formData,
                headers: {
                    ...headers,
                    ...(options.headers || {})
                },
                ...options
            });
        }
    };
})();

// For CommonJS environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NyalifeAPI;
}