<?php
/**
 * Nyalife HMS - Date Utilities
 * 
 * This file provides standardized date and time manipulation functions.
 * All date/time formatting should use these functions for consistency.
 */

// Set default date and time formats
define('DEFAULT_DATE_FORMAT', 'M d, Y');
define('DEFAULT_TIME_FORMAT', 'g:i A');
define('DEFAULT_DATETIME_FORMAT', 'M d, Y g:i A');

/**
 * Format a date string
 * 
 * @param string $dateString Date string to format (YYYY-MM-DD)
 * @param string $format Format string (default: M d, Y)
 * @return string Formatted date
 */
if (!function_exists('formatDate')) {
    function formatDate($dateString, $format = DEFAULT_DATE_FORMAT) {
        if (empty($dateString)) return '';
        
        try {
            $date = new DateTime($dateString);
            return $date->format($format);
        } catch (Exception $e) {
            return $dateString;
        }
    }
}

/**
 * Format a time string
 * 
 * @param string $timeString Time string to format (HH:MM:SS or datetime)
 * @param string $format Format string (default: g:i A)
 * @return string Formatted time
 */
if (!function_exists('formatTime')) {
    function formatTime($timeString, $format = DEFAULT_TIME_FORMAT) {
        if (empty($timeString)) return '';
        
        try {
            // Handle different time string formats
            if (strpos($timeString, ':') !== false && strlen($timeString) <= 8) {
                // This is just a time (HH:MM:SS)
                list($hours, $minutes, $seconds) = array_pad(explode(':', $timeString), 3, 0);
                $date = new DateTime();
                $date->setTime($hours, $minutes, $seconds);
            } else {
                // This is a full datetime
                $date = new DateTime($timeString);
            }
            
            return $date->format($format);
        } catch (Exception $e) {
            return $timeString;
        }
    }
}

/**
 * Format a datetime string
 * 
 * @param string $dateTimeString Datetime string to format
 * @param string $format Format string (default: M d, Y g:i A)
 * @return string Formatted datetime
 */
if (!function_exists('formatDateTime')) {
    function formatDateTime($dateTimeString, $format = DEFAULT_DATETIME_FORMAT) {
        if (empty($dateTimeString)) return '';
        
        try {
            $date = new DateTime($dateTimeString);
            return $date->format($format);
        } catch (Exception $e) {
            return $dateTimeString;
        }
    }
}

/**
 * Format a date range
 * 
 * @param string $startDate Start date (YYYY-MM-DD)
 * @param string $endDate End date (YYYY-MM-DD)
 * @param string $format Format for each date
 * @return string Formatted date range
 */
if (!function_exists('formatDateRange')) {
    function formatDateRange($startDate, $endDate, $format = DEFAULT_DATE_FORMAT) {
        if (empty($startDate) || empty($endDate)) return '';
        
        // If dates are the same, return just one date
        if ($startDate === $endDate) {
            return formatDate($startDate, $format);
        }
        
        return formatDate($startDate, $format) . ' - ' . formatDate($endDate, $format);
    }
}

/**
 * Format a datetime range
 * 
 * @param string $startDateTime Start datetime
 * @param string $endDateTime End datetime
 * @param string $dateFormat Format for date part
 * @param string $timeFormat Format for time part
 * @return string Formatted datetime range
 */
if (!function_exists('formatDateTimeRange')) {
    function formatDateTimeRange($startDateTime, $endDateTime, $dateFormat = 'M d, Y', $timeFormat = 'g:i A') {
        if (empty($startDateTime) || empty($endDateTime)) return '';
        
        try {
            $startDate = new DateTime($startDateTime);
            $endDate = new DateTime($endDateTime);
            
            // If same day, show only one date with both times
            if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
                return $startDate->format($dateFormat) . ' ' . 
                       $startDate->format($timeFormat) . ' - ' . 
                       $endDate->format($timeFormat);
            }
            
            // Different days, show full range
            return $startDate->format($dateFormat . ' ' . $timeFormat) . ' - ' . 
                   $endDate->format($dateFormat . ' ' . $timeFormat);
        } catch (Exception $e) {
            return "$startDateTime - $endDateTime";
        }
    }
}

/**
 * Calculate age from date of birth
 * 
 * @param string $dateOfBirth Date of birth (YYYY-MM-DD)
 * @return int Age in years
 */
if (!function_exists('calculateAge')) {
    function calculateAge($dateOfBirth) {
        if (empty($dateOfBirth)) return null;
        
        try {
            $dob = new DateTime($dateOfBirth);
            $now = new DateTime();
            $diff = $now->diff($dob);
            return $diff->y;
        } catch (Exception $e) {
            return null;
        }
    }
}

/**
 * Get current date in database format (YYYY-MM-DD)
 * 
 * @return string Current date
 */
if (!function_exists('getCurrentDate')) {
    function getCurrentDate() {
        return date('Y-m-d');
    }
}

/**
 * Get current time in database format (HH:MM:SS)
 * 
 * @return string Current time
 */
if (!function_exists('getCurrentTime')) {
    function getCurrentTime() {
        return date('H:i:s');
    }
}

/**
 * Get current datetime in database format (YYYY-MM-DD HH:MM:SS)
 * 
 * @return string Current datetime
 */
if (!function_exists('getCurrentDateTime')) {
    function getCurrentDateTime() {
        return date('Y-m-d H:i:s');
    }
}

/**
 * Add days to a date
 * 
 * @param string $dateString Date string (YYYY-MM-DD)
 * @param int $days Number of days to add
 * @return string New date
 */
if (!function_exists('addDays')) {
    function addDays($dateString, $days) {
        if (empty($dateString)) return '';
        
        try {
            $date = new DateTime($dateString);
            $date->modify("+$days days");
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            return $dateString;
        }
    }
}

/**
 * Get date range for time period
 * 
 * @param string $period Period (today, yesterday, this_week, this_month, etc.)
 * @return array Associative array with start_date and end_date
 */
if (!function_exists('getDateRange')) {
    function getDateRange($period) {
        $today = new DateTime();
        $start = new DateTime();
        $end = new DateTime();
        
        switch ($period) {
            case 'today':
                // Start and end are both today
                break;
                
            case 'yesterday':
                $start->modify('-1 day');
                $end->modify('-1 day');
                break;
                
            case 'this_week':
                $start->modify('monday this week');
                break;
                
            case 'last_week':
                $start->modify('monday last week');
                $end->modify('sunday last week');
                break;
                
            case 'this_month':
                $start->modify('first day of this month');
                break;
                
            case 'last_month':
                $start->modify('first day of last month');
                $end->modify('last day of last month');
                break;
                
            case 'this_year':
                $start->modify('first day of january this year');
                break;
                
            case 'last_year':
                $start->modify('first day of january last year');
                $end->modify('last day of december last year');
                break;
                
            case 'last_30_days':
                $start->modify('-30 days');
                break;
                
            case 'last_90_days':
                $start->modify('-90 days');
                break;
                
            default:
                // Default to today
                break;
        }
        
        return [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d')
        ];
    }
}

/**
 * Check if a date is in the past
 * 
 * @param string $dateString Date to check (YYYY-MM-DD)
 * @return bool True if date is in the past, false otherwise
 */
if (!function_exists('isDateInPast')) {
    function isDateInPast($dateString) {
        if (empty($dateString)) return false;
        
        try {
            $date = new DateTime($dateString);
            $today = new DateTime();
            $today->setTime(0, 0, 0); // Set to beginning of today
            
            return $date < $today;
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 * Check if a datetime is in the past
 * 
 * @param string $dateTimeString Datetime to check
 * @return bool True if datetime is in the past, false otherwise
 */
if (!function_exists('isDateTimeInPast')) {
    function isDateTimeInPast($dateTimeString) {
        if (empty($dateTimeString)) return false;
        
        try {
            $dateTime = new DateTime($dateTimeString);
            $now = new DateTime();
            
            return $dateTime < $now;
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 