/**
 * Nyalife HMS - Validation Utilities
 * 
 * Modern validation using validator.js library
 * Replaces custom validation with well-tested library functions
 */

import validator from 'validator';

export const ValidationUtils = {
    /**
     * Validate email address
     * @param {string} email - Email to validate
     * @returns {boolean} True if valid
     */
    isValidEmail(email) {
        if (!email) return false;
        return validator.isEmail(email);
    },

    /**
     * Validate phone number
     * @param {string} phone - Phone number to validate
     * @param {string} locale - Locale for phone validation (default: 'en-KE' for Kenya)
     * @returns {boolean} True if valid
     */
    isValidPhone(phone, locale = 'en-KE') {
        if (!phone) return false;
        // Remove spaces and dashes for validation
        const cleanPhone = phone.replace(/[\s-]/g, '');
        return validator.isMobilePhone(cleanPhone, locale);
    },

    /**
     * Validate URL
     * @param {string} url - URL to validate
     * @param {Object} options - Validation options
     * @returns {boolean} True if valid
     */
    isValidUrl(url, options = {}) {
        if (!url) return false;
        return validator.isURL(url, options);
    },

    /**
     * Validate strong password
     * @param {string} password - Password to validate
     * @param {Object} options - Password strength options
     * @returns {boolean} True if valid
     */
    isStrongPassword(password, options = {}) {
        if (!password) return false;
        
        const defaultOptions = {
            minLength: 8,
            minLowercase: 1,
            minUppercase: 1,
            minNumbers: 1,
            minSymbols: 1,
            ...options
        };
        
        return validator.isStrongPassword(password, defaultOptions);
    },

    /**
     * Validate date string
     * @param {string} dateString - Date string to validate
     * @param {string} format - Expected date format (default: 'YYYY-MM-DD')
     * @returns {boolean} True if valid
     */
    isValidDate(dateString, format = 'YYYY-MM-DD') {
        if (!dateString) return false;
        return validator.isDate(dateString, { format });
    },

    /**
     * Validate numeric string
     * @param {string} value - Value to validate
     * @param {Object} options - Numeric validation options
     * @returns {boolean} True if valid
     */
    isNumeric(value, options = {}) {
        if (value === null || value === undefined) return false;
        return validator.isNumeric(String(value), options);
    },

    /**
     * Validate integer
     * @param {string|number} value - Value to validate
     * @param {Object} options - Integer validation options
     * @returns {boolean} True if valid
     */
    isInt(value, options = {}) {
        if (value === null || value === undefined) return false;
        return validator.isInt(String(value), options);
    },

    /**
     * Validate float
     * @param {string|number} value - Value to validate
     * @param {Object} options - Float validation options
     * @returns {boolean} True if valid
     */
    isFloat(value, options = {}) {
        if (value === null || value === undefined) return false;
        return validator.isFloat(String(value), options);
    },

    /**
     * Validate length
     * @param {string} value - String to validate
     * @param {Object} options - Length options { min, max }
     * @returns {boolean} True if valid
     */
    isLength(value, options = {}) {
        if (!value) return !options.min || options.min === 0;
        return validator.isLength(value, options);
    },

    /**
     * Sanitize and escape string
     * @param {string} value - String to escape
     * @returns {string} Escaped string
     */
    escape(value) {
        if (!value) return '';
        return validator.escape(value);
    },

    /**
     * Trim whitespace
     * @param {string} value - String to trim
     * @param {string} chars - Characters to trim (optional)
     * @returns {string} Trimmed string
     */
    trim(value, chars) {
        if (!value) return '';
        return validator.trim(value, chars);
    },

    /**
     * Check if string contains only alphabetic characters
     * @param {string} value - String to validate
     * @param {string} locale - Locale for validation
     * @returns {boolean} True if valid
     */
    isAlpha(value, locale = 'en-US') {
        if (!value) return false;
        return validator.isAlpha(value, locale);
    },

    /**
     * Check if string contains only alphanumeric characters
     * @param {string} value - String to validate
     * @param {string} locale - Locale for validation
     * @returns {boolean} True if valid
     */
    isAlphanumeric(value, locale = 'en-US') {
        if (!value) return false;
        return validator.isAlphanumeric(value, locale);
    },

    /**
     * Validate credit card number
     * @param {string} cardNumber - Card number to validate
     * @returns {boolean} True if valid
     */
    isCreditCard(cardNumber) {
        if (!cardNumber) return false;
        const cleaned = cardNumber.replace(/[\s-]/g, '');
        return validator.isCreditCard(cleaned);
    },

    /**
     * Validate postal code
     * @param {string} postalCode - Postal code to validate
     * @param {string} locale - Locale for validation (default: 'any')
     * @returns {boolean} True if valid
     */
    isPostalCode(postalCode, locale = 'any') {
        if (!postalCode) return false;
        return validator.isPostalCode(postalCode, locale);
    },

    /**
     * Custom validation: Kenyan national ID
     * @param {string} idNumber - ID number to validate
     * @returns {boolean} True if valid
     */
    isKenyanID(idNumber) {
        if (!idNumber) return false;
        // Kenyan IDs are typically 7-8 digits
        const cleaned = idNumber.replace(/[\s-]/g, '');
        return /^\d{7,8}$/.test(cleaned);
    },

    /**
     * Custom validation: Medical record number format
     * @param {string} mrn - Medical record number
     * @returns {boolean} True if valid
     */
    isMedicalRecordNumber(mrn) {
        if (!mrn) return false;
        // Assuming format: MRN-YYYYMMDD-XXXX
        return /^MRN-\d{8}-\d{4}$/.test(mrn);
    }
};

// Make utilities available globally for backward compatibility
if (typeof window !== 'undefined') {
    window.ValidationUtils = ValidationUtils;
}

// Export as default and named export
export default ValidationUtils;