/**
 * Nyalife HMS - Common utility functions
 * 
 * Modern utilities using lodash-es and nanoid
 * Replaces custom implementations with well-maintained packages
 */

import { debounce, throttle } from 'lodash-es';
import { nanoid } from 'nanoid';
import Swal from 'sweetalert2';

export const NyalifeUtils = {
    /**
     * Format currency values
     * @param {number} amount - The amount to format
     * @param {string} currency - Currency code (default: 'KES')
     * @returns {string} Formatted currency string
     */
    formatCurrency(amount, currency = 'KES') {
        try {
            return new Intl.NumberFormat('en-KE', {
                style: 'currency',
                currency: currency
            }).format(amount);
        } catch (error) {
            console.error('Error formatting currency:', error);
            return `${currency} ${amount}`;
        }
    },
    
    /**
     * Generate a random unique ID using nanoid
     * @param {number} length - Length of ID (default: 8)
     * @returns {string} Random ID
     */
    generateRandomId(length = 8) {
        return nanoid(length);
    },
    
    /**
     * Debounce function using lodash-es
     * Limits how often a function can be called
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @param {Object} options - Lodash debounce options
     * @returns {Function} Debounced function
     */
    debounce(func, wait = 300, options = {}) {
        return debounce(func, wait, options);
    },
    
    /**
     * Throttle function using lodash-es
     * Limits function execution to once per specified time period
     * @param {Function} func - Function to throttle
     * @param {number} wait - Wait time in milliseconds
     * @param {Object} options - Lodash throttle options
     * @returns {Function} Throttled function
     */
    throttle(func, wait = 300, options = {}) {
        return throttle(func, wait, options);
    },
    
    /**
     * Truncate text to specified length
     * @param {string} text - Text to truncate
     * @param {number} maxLength - Maximum length
     * @param {string} suffix - Suffix to add (default: '...')
     * @returns {string} Truncated text
     */
    truncate(text, maxLength, suffix = '...') {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength - suffix.length) + suffix;
    },
    
    /**
     * Show a toast/notification message
     * @param {string} message - Message to display
     * @param {string} type - Message type ('success', 'error', 'info', 'warning')
     * @param {number} duration - Duration in milliseconds
     */
    showNotification(message, type = 'info', duration = 3000) {
        // Use SweetAlert2 if available
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: type,
                title: message
            });
            return;
        }

        // Fallback to Bootstrap toast
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toastContainer = document.querySelector('.toast-container') || this.createToastContainer();
            const toastElement = this.createToastElement(message, type);
            toastContainer.appendChild(toastElement);
            
            const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: duration });
            toast.show();
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        } else {
            // Fallback to console
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    },
    
    /**
     * Create toast container if it doesn't exist
     * @returns {HTMLElement} Toast container element
     */
    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    },
    
    /**
     * Create toast element
     * @param {string} message - Message text
     * @param {string} type - Toast type
     * @returns {HTMLElement} Toast element
     */
    createToastElement(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${this.getBootstrapClass(type)} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${this.escapeHtml(message)}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        return toast;
    },
    
    /**
     * Get Bootstrap class for notification type
     * @param {string} type - Notification type
     * @returns {string} Bootstrap class
     */
    getBootstrapClass(type) {
        const classMap = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info'
        };
        return classMap[type] || 'info';
    },
    
    /**
     * Escape HTML to prevent XSS
     * @param {string} text - Text to escape
     * @returns {string} Escaped text
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    /**
     * Deep clone an object
     * @param {*} obj - Object to clone
     * @returns {*} Cloned object
     */
    deepClone(obj) {
        try {
            return JSON.parse(JSON.stringify(obj));
        } catch (error) {
            console.error('Error deep cloning object:', error);
            return obj;
        }
    },
    
    /**
     * Check if value is empty (null, undefined, empty string, empty array, empty object)
     * @param {*} value - Value to check
     * @returns {boolean} True if empty
     */
    isEmpty(value) {
        if (value === null || value === undefined) return true;
        if (typeof value === 'string') return value.trim() === '';
        if (Array.isArray(value)) return value.length === 0;
        if (typeof value === 'object') return Object.keys(value).length === 0;
        return false;
    }
};

// Make utilities available globally for backward compatibility
if (typeof window !== 'undefined') {
    window.NyalifeUtils = NyalifeUtils;
}

// Export as default and named export
export default NyalifeUtils;