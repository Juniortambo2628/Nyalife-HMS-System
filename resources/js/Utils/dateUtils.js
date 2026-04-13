/**
 * Converts a Date object or string to a local ISO string (YYYY-MM-DDTHH:mm)
 * suitable for <input type="datetime-local" />
 */
export const toLocalISO = (date) => {
    const d = date ? new Date(date) : new Date();
    const tzOffset = d.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(d.getTime() - tzOffset)).toISOString().slice(0, 16);
    return localISOTime;
};

/**
 * Formats a date string into a human-readable format
 * Default: Apr 13, 2026 10:45 PM
 */
export const formatDateTime = (dateString, options = {}) => {
    if (!dateString) return 'N/A';
    
    try {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
            ...options
        }).format(date);
    } catch (e) {
        return dateString;
    }
};

/**
 * Formats a date string into just a date format
 * Default: Apr 13, 2026
 */
export const formatDateOnly = (dateString) => {
    return formatDateTime(dateString, { hour: undefined, minute: undefined });
};
