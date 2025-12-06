<?php
/**
 * Nyalife HMS - Validation Functions
 * 
 * This file contains validation functions for form data and user input.
 */

/**
 * Validate email address
 * 
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * Validate phone number (basic validation)
 * 
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
if (!function_exists('validatePhone')) {
    function validatePhone($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid length (7-15 digits)
        return strlen($phone) >= 7 && strlen($phone) <= 15;
    }
}

/**
 * Validate date format (YYYY-MM-DD)
 * 
 * @param string $date Date string to validate
 * @return bool True if valid, false otherwise
 */
if (!function_exists('validateDate')) {
    function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

/**
 * Validate time format (HH:MM)
 * 
 * @param string $time Time string to validate
 * @return bool True if valid, false otherwise
 */
if (!function_exists('validateTime')) {
    function validateTime($time) {
        $t = DateTime::createFromFormat('H:i', $time);
        return $t && $t->format('H:i') === $time;
    }
}

/**
 * Validate required fields
 * 
 * @param array $data Data array
 * @param array $requiredFields Array of required field names
 * @return array Array of missing fields
 */
if (!function_exists('validateRequiredFields')) {
    function validateRequiredFields($data, $requiredFields) {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
}

/**
 * Sanitize string input
 * 
 * @param string $input Input string
 * @return string Sanitized string
 */
if (!function_exists('sanitizeString')) {
    function sanitizeString($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Sanitize array of strings
 * 
 * @param array $data Array of strings
 * @return array Sanitized array
 */
if (!function_exists('sanitizeArray')) {
    function sanitizeArray($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}

/**
 * Validate password strength
 * 
 * @param string $password Password to validate
 * @return bool True if strong enough, false otherwise
 */
if (!function_exists('validatePassword')) {
    function validatePassword($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        return strlen($password) >= 8 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
}

/**
 * Validate username format
 * 
 * @param string $username Username to validate
 * @return bool True if valid, false otherwise
 */
if (!function_exists('validateUsername')) {
    function validateUsername($username) {
        // Alphanumeric and underscore only, 3-20 characters
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }
}

/**
 * Validate file upload
 * 
 * @param array $file $_FILES array element
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return array Validation result with success and message
 */
if (!function_exists('validateFileUpload')) {
    function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        $result = ['success' => false, 'message' => ''];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $result['message'] = 'No file was uploaded';
            return $result;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $result['message'] = 'File size exceeds maximum allowed size';
            return $result;
        }
        
        // Check file type if allowed types are specified
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $result['message'] = 'File type not allowed';
                return $result;
            }
        }
        
        $result['success'] = true;
        return $result;
    }
}

/**
 * Validate numeric input
 * 
 * @param mixed $value Value to validate
 * @param float $min Minimum value (optional)
 * @param float $max Maximum value (optional)
 * @return bool True if valid, false otherwise
 */
if (!function_exists('validateNumeric')) {
    function validateNumeric($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $value = floatval($value);
        
        if ($min !== null && $value < $min) {
            return false;
        }
        
        if ($max !== null && $value > $max) {
            return false;
        }
        
        return true;
    }
}

/**
 * Validate integer input
 * 
 * @param mixed $value Value to validate
 * @param int $min Minimum value (optional)
 * @param int $max Maximum value (optional)
 * @return bool True if valid, false otherwise
 */
if (!function_exists('validateInteger')) {
    function validateInteger($value, $min = null, $max = null) {
        if (!is_numeric($value) || floor($value) != $value) {
            return false;
        }
        
        $value = intval($value);
        
        if ($min !== null && $value < $min) {
            return false;
        }
        
        if ($max !== null && $value > $max) {
            return false;
        }
        
        return true;
    }
} 