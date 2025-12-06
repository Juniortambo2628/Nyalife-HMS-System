/**
 * Nyalife HMS - Date Utility Functions
 * 
 * This file contains common functions for date and time manipulation
 * Updated to work with the core modules while maintaining backward compatibility
 */

// Check if core module is available
if (typeof NyalifeUtils === 'undefined' || !NyalifeUtils.formatDate) {
    console.log('DateUtils initialized in legacy mode');
}

// Date utilities namespace
const DateUtils = {
    /**
     * Format a date string to localized display format
     * @param {string} dateString - ISO date string to format
     * @param {string} format - Optional format specification
     * @returns {string} Formatted date string
     */
    formatDate: function(dateString, format = null) {
        if (!dateString) return '';

        const date = new Date(dateString);

        // Check if date is valid
        if (isNaN(date.getTime())) return dateString;

        if (format === 'short') {
            return date.toLocaleDateString();
        } else if (format === 'long') {
            return date.toLocaleDateString(undefined, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } else {
            // Default format
            return date.toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }
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
     * Format a datetime string to localized display format
     * @param {string} dateTimeString - ISO datetime string to format
     * @param {boolean} includeSeconds - Whether to include seconds in the time
     * @returns {string} Formatted datetime string
     */
    formatDateTime: function(dateTimeString, includeSeconds = false) {
        if (!dateTimeString) return '';

        const date = new Date(dateTimeString);

        // Check if date is valid
        if (isNaN(date.getTime())) return dateTimeString;

        // Format date part
        const formattedDate = this.formatDate(dateTimeString);

        // Format time part
        const formattedTime = this.formatTime(dateTimeString, includeSeconds);

        return `${formattedDate} at ${formattedTime}`;
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
     * Get a relative time string (e.g., "2 hours ago")
     * @param {string} dateTimeString - ISO datetime string
     * @returns {string} Relative time string
     */
    getRelativeTimeString: function(dateTimeString) {
        if (!dateTimeString) return '';

        const date = new Date(dateTimeString);

        // Check if date is valid
        if (isNaN(date.getTime())) return dateTimeString;

        // Use RelativeTimeFormat API if available
        if (typeof Intl !== 'undefined' && Intl.RelativeTimeFormat) {
            const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
            const now = new Date();
            const diffInSeconds = Math.floor((date - now) / 1000);

            if (Math.abs(diffInSeconds) < 60) {
                return rtf.format(diffInSeconds, 'second');
            }

            const diffInMinutes = Math.floor(diffInSeconds / 60);
            if (Math.abs(diffInMinutes) < 60) {
                return rtf.format(diffInMinutes, 'minute');
            }

            const diffInHours = Math.floor(diffInMinutes / 60);
            if (Math.abs(diffInHours) < 24) {
                return rtf.format(diffInHours, 'hour');
            }

            const diffInDays = Math.floor(diffInHours / 24);
            if (Math.abs(diffInDays) < 30) {
                return rtf.format(diffInDays, 'day');
            }

            const diffInMonths = Math.floor(diffInDays / 30);
            if (Math.abs(diffInMonths) < 12) {
                return rtf.format(diffInMonths, 'month');
            }

            const diffInYears = Math.floor(diffInMonths / 12);
            return rtf.format(diffInYears, 'year');
        } else {
            // Fallback for browsers without RelativeTimeFormat support
            const now = new Date();
            const diffInMs = date - now;
            const diffInSecs = Math.floor(Math.abs(diffInMs) / 1000);
            const isInPast = diffInMs < 0;

            const timeUnits = [
                { unit: 'year', seconds: 31536000 },
                { unit: 'month', seconds: 2592000 },
                { unit: 'day', seconds: 86400 },
                { unit: 'hour', seconds: 3600 },
                { unit: 'minute', seconds: 60 },
                { unit: 'second', seconds: 1 }
            ];

            for (const { unit, seconds }
                of timeUnits) {
                const value = Math.floor(diffInSecs / seconds);
                if (value >= 1) {
                    const plural = value > 1 ? 's' : '';
                    return isInPast ?
                        `${value} ${unit}${plural} ago` :
                        `in ${value} ${unit}${plural}`;
                }
            }

            return isInPast ? 'just now' : 'now';
        }
    },

    /**
     * Format a date range
     * @param {string} startDateString - Start date as ISO string
     * @param {string} endDateString - End date as ISO string 
     * @returns {string} Formatted date range string
     */
    formatDateRange: function(startDateString, endDateString) {
        if (!startDateString || !endDateString) return '';

        const startDate = new Date(startDateString);
        const endDate = new Date(endDateString);

        // Check if dates are valid
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
            return `${startDateString} - ${endDateString}`;
        }

        // If same day, just show one date
        if (startDate.toDateString() === endDate.toDateString()) {
            return `${this.formatDate(startDateString)} ${this.formatTime(startDateString)} - ${this.formatTime(endDateString)}`;
        }

        // Different days
        return `${this.formatDate(startDateString)} - ${this.formatDate(endDateString)}`;
    }
};

// Export for ES modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DateUtils;
}