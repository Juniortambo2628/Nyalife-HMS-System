/**
 * Nyalife HMS - HTTP Client
 * 
 * Configured axios instance for API requests
 * Replaces custom fetch wrappers with axios
 */

import axios from 'axios';

// Get base URL from global config or default
const baseURL = window.baseUrl || '';

// Create axios instance with default configuration
const httpClient = axios.create({
    baseURL: baseURL,
    timeout: 30000, // 30 seconds
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// Request interceptor
httpClient.interceptors.request.use(
    (config) => {
        // Ensure baseURL is set from global config or meta tag if not already absolute
        if (!config.baseURL) {
            if (window.baseUrl) {
                config.baseURL = window.baseUrl;
            } else {
                const metaBaseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content');
                if (metaBaseUrl) {
                    config.baseURL = metaBaseUrl;
                    // Update window.baseUrl for future use
                    window.baseUrl = metaBaseUrl;
                }
            }
        }

        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            config.headers['X-CSRF-TOKEN'] = csrfToken;
        }

        // Fix for axios not prepending baseURL if url starts with /
        if (config.baseURL && config.url && config.url.startsWith('/') && !config.url.startsWith('//')) {
            config.url = config.url.substring(1);
        }

        // Show loader unless explicitly disabled
        if (!config.headers['X-No-Loader']) {
            showLoader();
        }

        // Log request in development
        if (window.DEBUG_MODE || localStorage.getItem('debug') === 'true') {
            console.log('🚀 Request:', config.method.toUpperCase(), config.url, config.data || config.params);
        }

        return config;
    },
    (error) => {
        hideLoader();
        console.error('❌ Request Error:', error);
        return Promise.reject(error);
    }
);

// Response interceptor
httpClient.interceptors.response.use(
    (response) => {
        hideLoader();

        // Log response in development
        if (window.DEBUG_MODE || localStorage.getItem('debug') === 'true') {
            console.log('✅ Response:', response.config.url, response.data);
        }

        return response;
    },
    (error) => {
        hideLoader();

        // Log error
        console.error('❌ Response Error:', error);

        // Handle specific error codes
        if (error.response) {
            switch (error.response.status) {
                case 401:
                    // Unauthorized - redirect to login
                    handleUnauthorized();
                    break;
                case 403:
                    // Forbidden
                    showError('You do not have permission to perform this action.');
                    break;
                case 404:
                    showError('The requested resource was not found.');
                    break;
                case 422:
                    // Validation error
                    handleValidationErrors(error.response.data);
                    break;
                case 500:
                    showError('A server error occurred. Please try again later.');
                    break;
                default:
                    showError(error.response.data.message || 'An error occurred. Please try again.');
            }
        } else if (error.request) {
            // Request was made but no response received
            showError('No response from server. Please check your connection.');
        } else {
            // Something else happened
            showError('An unexpected error occurred.');
        }

        return Promise.reject(error);
    }
);

/**
 * Show loading indicator
 */
function showLoader() {
    if (typeof window.NyalifeLoader !== 'undefined' && window.NyalifeLoader.show) {
        window.NyalifeLoader.show();
    } else {
        // Fallback: add loading class to body
        document.body.classList.add('loading');
    }
}

/**
 * Hide loading indicator
 */
function hideLoader() {
    if (typeof window.NyalifeLoader !== 'undefined' && window.NyalifeLoader.hide) {
        window.NyalifeLoader.hide();
    } else {
        // Fallback: remove loading class from body
        document.body.classList.remove('loading');
    }
}

/**
 * Handle unauthorized access
 */
function handleUnauthorized() {
    // Clear session data
    sessionStorage.clear();
    
    // Show message
    showError('Your session has expired. Please log in again.');
    
    // Redirect to login after a short delay
    setTimeout(() => {
        window.location.href = `${baseURL}/login`;
    }, 2000);
}

/**
 * Handle validation errors
 * @param {Object} data - Error response data
 */
function handleValidationErrors(data) {
    if (data.errors && typeof data.errors === 'object') {
        // Display first error
        const firstField = Object.keys(data.errors)[0];
        const firstError = data.errors[firstField][0];
        showError(firstError);
    } else if (data.message) {
        showError(data.message);
    } else {
        showError('Validation failed. Please check your input.');
    }
}

/**
 * Show error message
 * @param {string} message - Error message
 */
function showError(message) {
    if (typeof window.NyalifeUtils !== 'undefined' && window.NyalifeUtils.showNotification) {
        window.NyalifeUtils.showNotification(message, 'error');
    } else {
        // Fallback to alert
        alert(message);
    }
}

// Export configured instance and methods
export default httpClient;

// Named exports for convenience
export const get = (url, config) => httpClient.get(url, config);
export const post = (url, data, config) => httpClient.post(url, data, config);
export const put = (url, data, config) => httpClient.put(url, data, config);
export const del = (url, config) => httpClient.delete(url, config);
export const patch = (url, data, config) => httpClient.patch(url, data, config);

// Make available globally for backward compatibility
if (typeof window !== 'undefined') {
    window.httpClient = httpClient;
}
