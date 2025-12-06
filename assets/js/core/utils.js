/**
 * Nyalife HMS - Core Utilities Module
 * 
 * Common utility functions used across the application
 */

const NyalifeUtils = (function() {
    // Public methods
    return {
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
         * Format a number as currency
         * @param {number} amount - Amount to format
         * @param {string} currency - Currency code (e.g., 'USD', 'KES')
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
         * Debounce a function to prevent rapid repeated calls
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
        }
    };
})();

// For CommonJS environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NyalifeUtils;
}
