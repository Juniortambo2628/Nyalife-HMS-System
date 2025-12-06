/**
 * Nyalife HMS - Date Utility Functions
 * 
 * Modern date utilities using date-fns library
 * Replaces custom date manipulation with well-tested library functions
 */

import { format, formatDistanceToNow, differenceInYears, parseISO, isValid } from 'date-fns';

/**
 * Date utilities namespace using date-fns
 */
export const DateUtils = {
    /**
     * Format a date string to localized display format
     * @param {string|Date} dateInput - ISO date string or Date object to format
     * @param {string} formatString - Optional format specification (default: 'MMM d, yyyy')
     * @returns {string} Formatted date string
     */
    formatDate(dateInput, formatString = 'MMM d, yyyy') {
        if (!dateInput) return '';

        try {
            const date = typeof dateInput === 'string' ? parseISO(dateInput) : dateInput;
            
            if (!isValid(date)) {
                console.warn('Invalid date provided to formatDate:', dateInput);
                return String(dateInput);
            }

            return format(date, formatString);
        } catch (error) {
            console.error('Error formatting date:', error);
            return String(dateInput);
        }
    },

    /**
     * Format a time string to localized display format
     * @param {string|Date} timeInput - Time string to format (can be ISO date or time)
     * @param {boolean} includeSeconds - Whether to include seconds
     * @returns {string} Formatted time string
     */
    formatTime(timeInput, includeSeconds = false) {
        if (!timeInput) return '';

        try {
            let date;

            if (typeof timeInput === 'string') {
                if (timeInput.includes('T') || timeInput.includes('-')) {
                    // Full ISO date
                    date = parseISO(timeInput);
                } else if (timeInput.includes(':')) {
                    // Just time string (HH:MM:SS)
                    const [hours, minutes, seconds = 0] = timeInput.split(':').map(Number);
                    date = new Date();
                    date.setHours(hours, minutes, seconds);
                } else {
                    return timeInput;
                }
            } else {
                date = timeInput;
            }

            if (!isValid(date)) {
                return String(timeInput);
            }

            const formatString = includeSeconds ? 'h:mm:ss a' : 'h:mm a';
            return format(date, formatString);
        } catch (error) {
            console.error('Error formatting time:', error);
            return String(timeInput);
        }
    },

    /**
     * Format a datetime string to localized display format
     * @param {string|Date} dateTimeInput - ISO datetime string to format
     * @param {boolean} includeSeconds - Whether to include seconds in the time
     * @returns {string} Formatted datetime string
     */
    formatDateTime(dateTimeInput, includeSeconds = false) {
        if (!dateTimeInput) return '';

        try {
            const date = typeof dateTimeInput === 'string' ? parseISO(dateTimeInput) : dateTimeInput;
            
            if (!isValid(date)) {
                return String(dateTimeInput);
            }

            const formatString = includeSeconds ? 'MMM d, yyyy \'at\' h:mm:ss a' : 'MMM d, yyyy \'at\' h:mm a';
            return format(date, formatString);
        } catch (error) {
            console.error('Error formatting datetime:', error);
            return String(dateTimeInput);
        }
    },

    /**
     * Calculate age from date of birth
     * Uses date-fns differenceInYears for accurate calculation
     * @param {string|Date} dobInput - Date of birth
     * @returns {number|null} Age in years
     */
    calculateAge(dobInput) {
        if (!dobInput) return null;

        try {
            const dob = typeof dobInput === 'string' ? parseISO(dobInput) : dobInput;
            
            if (!isValid(dob)) {
                return null;
            }

            return differenceInYears(new Date(), dob);
        } catch (error) {
            console.error('Error calculating age:', error);
            return null;
        }
    },

    /**
     * Get a relative time string (e.g., "2 hours ago")
     * Uses date-fns formatDistanceToNow for consistent formatting
     * @param {string|Date} dateTimeInput - ISO datetime string or Date object
     * @param {boolean} addSuffix - Whether to add "ago" suffix (default: true)
     * @returns {string} Relative time string
     */
    getRelativeTimeString(dateTimeInput, addSuffix = true) {
        if (!dateTimeInput) return '';

        try {
            const date = typeof dateTimeInput === 'string' ? parseISO(dateTimeInput) : dateTimeInput;
            
            if (!isValid(date)) {
                return String(dateTimeInput);
            }

            return formatDistanceToNow(date, { addSuffix });
        } catch (error) {
            console.error('Error getting relative time string:', error);
            return String(dateTimeInput);
        }
    },

    /**
     * Format a date range
     * @param {string|Date} startDateInput - Start date
     * @param {string|Date} endDateInput - End date
     * @returns {string} Formatted date range string
     */
    formatDateRange(startDateInput, endDateInput) {
        if (!startDateInput || !endDateInput) return '';

        try {
            const startDate = typeof startDateInput === 'string' ? parseISO(startDateInput) : startDateInput;
            const endDate = typeof endDateInput === 'string' ? parseISO(endDateInput) : endDateInput;

            if (!isValid(startDate) || !isValid(endDate)) {
                return `${startDateInput} - ${endDateInput}`;
            }

            // If same day, show date with time range
            if (format(startDate, 'yyyy-MM-dd') === format(endDate, 'yyyy-MM-dd')) {
                return `${format(startDate, 'MMM d, yyyy h:mm a')} - ${format(endDate, 'h:mm a')}`;
            }

            // Different days
            return `${format(startDate, 'MMM d, yyyy')} - ${format(endDate, 'MMM d, yyyy')}`;
        } catch (error) {
            console.error('Error formatting date range:', error);
            return `${startDateInput} - ${endDateInput}`;
        }
    },

    /**
     * Parse a date string to a Date object
     * @param {string} dateString - Date string to parse
     * @returns {Date|null} Parsed Date object or null if invalid
     */
    parseDate(dateString) {
        if (!dateString) return null;

        try {
            const date = parseISO(dateString);
            return isValid(date) ? date : null;
        } catch (error) {
            console.error('Error parsing date:', error);
            return null;
        }
    },

    /**
     * Check if a date is valid
     * @param {string|Date} dateInput - Date to validate
     * @returns {boolean} True if valid
     */
    isValidDate(dateInput) {
        if (!dateInput) return false;

        try {
            const date = typeof dateInput === 'string' ? parseISO(dateInput) : dateInput;
            return isValid(date);
        } catch (error) {
            return false;
        }
    }
};

// Maintain backward compatibility with global namespace
if (typeof window !== 'undefined') {
    window.DateUtils = DateUtils;
}

// Default export
export default DateUtils;