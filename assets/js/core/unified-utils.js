/**
 * Nyalife HMS - Unified Utilities System
 * 
 * This file consolidates all utility functionality from:
 * - common/utils.js
 * - core/utils.js
 * - nyalife.js utilities
 * - common/date-utils.js
 * - common/validation.js
 * 
 * Provides a single, consistent utilities interface across the application.
 */

const NyalifeUtils = (function() {
    // Public methods
    return {
        /**
         * Easy selector helper function
         * @param {string} selector - CSS selector
         * @returns {Element} The selected DOM element
         */
        select: (selector) => document.querySelector(selector),

        /**
         * Easy selector helper function for multiple elements
         * @param {string} selector - CSS selector
         * @returns {NodeList} The selected DOM elements
         */
        selectAll: (selector) => document.querySelectorAll(selector),

        /**
         * Safely access nested objects
         * @param {Object} obj - The object to access
         * @param {string} path - The path to the property
         * @param {*} defaultValue - Default value if property doesn't exist
         * @returns {*} The value or default
         */
        get: (obj, path, defaultValue = undefined) => {
            const travel = (regexp) =>
                String.prototype.split
                .call(path, regexp)
                .filter(Boolean)
                .reduce((res, key) => (res !== null && res !== undefined ? res[key] : res), obj);
            const result = travel(/[,[\]]+?/) || travel(/[,[\].]+?/);
            return result === undefined || result === obj ? defaultValue : result;
        },

        /**
         * Check if an element exists in the DOM
         * @param {string} selector - CSS selector
         * @returns {boolean} True if element exists
         */
        exists: (selector) => !!document.querySelector(selector),

        /**
         * Safely add event listener
         * @param {Element|string} element - Element or selector
         * @param {string} event - Event name
         * @param {Function} callback - Event handler
         */
        on: (element, event, callback) => {
            const el = typeof element === 'string' ? document.querySelector(element) : element;
            if (el) {
                el.addEventListener(event, callback);
            }
        },

        /**
         * Log messages with consistent formatting
         * @param {string} message - Message to log
         * @param {string} level - Log level (log, warn, error)
         */
        log: (message, level = 'log') => {
            const timestamp = new Date().toISOString();
            console[level](`[Nyalife ${timestamp}] ${message}`);
        },

        /**
         * Format a date using the specified format
         * @param {Date|string} date - Date to format
         * @param {string} format - Format string (e.g., 'YYYY-MM-DD HH:mm:ss')
         * @returns {string} Formatted date string
         */
        formatDate: function(date, format = 'YYYY-MM-DD') {
            if (!date) return '';
            
            // Convert to Date object if string
            if (typeof date === 'string') {
                date = new Date(date);
            }
            
            // Check if date is valid
            if (isNaN(date.getTime())) {
                return '';
            }
            
            // Extract date components
            const year = date.getFullYear();
            const month = date.getMonth() + 1;
            const day = date.getDate();
            const hours = date.getHours();
            const minutes = date.getMinutes();
            const seconds = date.getSeconds();
            
            // Format mapping
            const mapping = {
                'YYYY': year,
                'YY': String(year).slice(-2),
                'MM': String(month).padStart(2, '0'),
                'M': month,
                'DD': String(day).padStart(2, '0'),
                'D': day,
                'HH': String(hours).padStart(2, '0'),
                'H': hours,
                'hh': String(hours % 12 || 12).padStart(2, '0'),
                'h': hours % 12 || 12,
                'mm': String(minutes).padStart(2, '0'),
                'm': minutes,
                'ss': String(seconds).padStart(2, '0'),
                's': seconds,
                'A': hours < 12 ? 'AM' : 'PM',
                'a': hours < 12 ? 'am' : 'pm'
            };
            
            // Replace format tokens
            return format.replace(/(YYYY|YY|MM|M|DD|D|HH|H|hh|h|mm|m|ss|s|A|a)/g, match => {
                return mapping[match] !== undefined ? mapping[match] : match;
            });
        },

        /**
         * Format a time string to localized display format
         * @param {string} timeString - Time string to format (can be ISO date or time)
         * @param {boolean} includeSeconds - Whether to include seconds
         * @returns {string} Formatted time string
         */
        formatTime: function(timeString, includeSeconds = false) {
            if (!timeString) return '';

            let time;

            // Handle different time string formats
            if (timeString.includes('T') || timeString.includes('-')) {
                // This is probably a full ISO date
                time = new Date(timeString);
            } else if (timeString.includes(':')) {
                // This is probably just a time string (HH:MM:SS)
                const [hours, minutes, seconds] = timeString.split(':').map(Number);
                time = new Date();
                time.setHours(hours, minutes, seconds || 0);
            } else {
                // Just return the original if we can't parse
                return timeString;
            }

            // Check if time is valid
            if (isNaN(time.getTime())) return timeString;

            // Format the time
            const options = {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            };

            if (includeSeconds) {
                options.second = '2-digit';
            }

            return time.toLocaleTimeString(undefined, options);
        },

        /**
         * Calculate age from date of birth
         * @param {string} dobString - Date of birth as a string
         * @returns {number} Age in years
         */
        calculateAge: function(dobString) {
            if (!dobString) return null;

            const dob = new Date(dobString);

            // Check if date is valid
            if (isNaN(dob.getTime())) return null;

            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();

            // Adjust age if birthday hasn't occurred yet this year
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }

            return age;
        },

        /**
         * Format currency values
         * @param {number} amount - The amount to format
         * @param {string} currency - Currency code (default: 'KES')
         * @param {string} locale - Locale (e.g., 'en-US', 'sw-KE')
         * @returns {string} Formatted currency string
         */
        formatCurrency: function(amount, currency = 'KES', locale = 'en-KE') {
            if (amount === null || amount === undefined) return '';
            
            try {
                return new Intl.NumberFormat(locale, {
                    style: 'currency',
                    currency: currency
                }).format(amount);
            } catch (error) {
                // Fallback for older browsers
                return currency + ' ' + parseFloat(amount).toFixed(2);
            }
        },

        /**
         * Generate a random string (useful for IDs)
         * @param {number} length - Length of the string
         * @returns {string} Random string
         */
        randomString: function(length = 8) {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        },

        /**
         * Debounce function to limit how often a function can be called
         * @param {Function} func - Function to debounce
         * @param {number} wait - Wait time in milliseconds
         * @returns {Function} Debounced function
         */
        debounce: function(func, wait = 300) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        },

        /**
         * Get URL query parameters
         * @param {string} [name] - Parameter name to retrieve (if omitted, returns all)
         * @returns {Object|string} Parameter value or all parameters
         */
        getQueryParams: function(name) {
            const urlSearchParams = new URLSearchParams(window.location.search);
            const params = Object.fromEntries(urlSearchParams.entries());
            
            return name ? params[name] : params;
        },

        /**
         * Get base URL from meta tag or construct it
         * @returns {string} Base URL
         */
        getBaseUrl: function() {
            const metaBase = document.querySelector('meta[name="base-url"]');
            if (metaBase) {
                return metaBase.getAttribute('content');
            }
            
            // Fallback: construct from window.location
            const origin = window.location.origin;
            const pathArray = window.location.pathname.split('/');
            const baseDir = pathArray[1] === 'Nyalife-HMS-System' ? '/Nyalife-HMS-System' : '';
            
            return origin + baseDir;
        },

        /**
         * Generate a URL using named routes
         * @param {string} routeName - Name of the route
         * @param {Object} params - Route parameters
         * @returns {string} Generated URL
         */
        route: function(routeName, params = {}) {
            // Check if routes are defined in window.nyalifeRoutes
            if (!window.nyalifeRoutes || !window.nyalifeRoutes[routeName]) {
                console.error(`Route "${routeName}" not found`);
                return '#';
            }
            
            let url = window.nyalifeRoutes[routeName];
            
            // Replace named parameters
            for (const param in params) {
                const pattern = new RegExp(`:${param}\\b`, 'g');
                url = url.replace(pattern, params[param]);
            }
            
            // Ensure URL starts with base URL
            const baseUrl = this.getBaseUrl();
            if (!url.startsWith('http') && !url.startsWith(baseUrl)) {
                url = baseUrl + (url.startsWith('/') ? url : '/' + url);
            }
            
            return url;
        },

        /**
         * Load a JavaScript file dynamically
         * @param {string} url - Script URL
         * @returns {Promise} Promise resolving when script is loaded
         */
        loadScript: function(url) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = url;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        },

        /**
         * Create a cookie
         * @param {string} name - Cookie name
         * @param {string} value - Cookie value
         * @param {number} days - Days until expiration
         */
        setCookie: function(name, value, days = 30) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = `expires=${date.toUTCString()}`;
            document.cookie = `${name}=${value};${expires};path=/;SameSite=Strict`;
        },

        /**
         * Get a cookie value
         * @param {string} name - Cookie name
         * @returns {string} Cookie value
         */
        getCookie: function(name) {
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i].trim();
                if (cookie.startsWith(name + '=')) {
                    return cookie.substring(name.length + 1);
                }
            }
            return '';
        },

        /**
         * Check if a value is empty (null, undefined, empty string, or only whitespace)
         * @param {*} value - Value to check
         * @returns {boolean} True if empty, false otherwise
         */
        isEmpty: function(value) {
            return value === null || value === undefined || 
                   (typeof value === 'string' && value.trim() === '');
        },

        /**
         * Validate email format
         * @param {string} email - Email to validate
         * @returns {boolean} True if valid, false otherwise
         */
        isValidEmail: function(email) {
            if (this.isEmpty(email)) return false;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * Validate phone number (basic format check)
         * @param {string} phone - Phone number to validate
         * @returns {boolean} True if valid, false otherwise
         */
        isValidPhone: function(phone) {
            if (this.isEmpty(phone)) return false;
            // Basic phone validation (can be customized for specific formats)
            const phoneRegex = /^[+]?[(]?[0-9]{1,4}[)]?[-\s.]?[0-9]{3,4}[-\s.]?[0-9]{3,4}$/;
            return phoneRegex.test(phone);
        },

        /**
         * Check if value meets minimum length requirement
         * @param {string} value - Value to check
         * @param {number} minLength - Minimum length required
         * @returns {boolean} True if valid, false otherwise
         */
        hasMinLength: function(value, minLength) {
            if (this.isEmpty(value)) return false;
            return value.length >= minLength;
        },

        /**
         * Check if value doesn't exceed maximum length
         * @param {string} value - Value to check
         * @param {number} maxLength - Maximum length allowed
         * @returns {boolean} True if valid, false otherwise
         */
        hasMaxLength: function(value, maxLength) {
            if (this.isEmpty(value)) return true;
            return value.length <= maxLength;
        },

        /**
         * Check if value is a number
         * @param {*} value - Value to check
         * @returns {boolean} True if valid number, false otherwise
         */
        isNumber: function(value) {
            if (this.isEmpty(value)) return false;
            return !isNaN(parseFloat(value)) && isFinite(value);
        },

        /**
         * Check if value is within a range
         * @param {number} value - Value to check
         * @param {number} min - Minimum allowed value
         * @param {number} max - Maximum allowed value
         * @returns {boolean} True if within range, false otherwise
         */
        isInRange: function(value, min, max) {
            if (!this.isNumber(value)) return false;
            const numValue = parseFloat(value);
            return numValue >= min && numValue <= max;
        }
    };
})();

// Create backward compatibility aliases
window.NyalifeValidation = NyalifeUtils;
window.DateUtils = NyalifeUtils;

// For CommonJS environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NyalifeUtils;
} 