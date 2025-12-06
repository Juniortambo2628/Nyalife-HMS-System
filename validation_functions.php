<?php
/**
 * Nyalife HMS - Validation Functions
 * 
 * This file contains functions for form validation.
 */

// Prevent multiple inclusions
if (!function_exists('validateRequiredFields')) {
    require_once __DIR__ . '/constants.php';

    /**
     * Validate required fields
     * 
     * @param array $data Form data
     * @param array $fields Required fields
     * @return array Validation result with 'valid' and 'missing' keys
     */
    function validateRequiredFields($data, $fields) {
        $missing = [];
        
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing[] = $field;
            }
        }
        
        return [
            'valid' => empty($missing),
            'missing' => $missing
        ];
    }
}

if (!function_exists('validateEmail')) {
    /**
     * Validate email address
     * 
     * @param string $email Email address to validate
     * @return bool Whether email is valid
     */
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('validatePassword')) {
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return bool True if password is valid, false otherwise
     */
    function validatePassword($password) {
        // Check minimum length
        if (!defined('MIN_PASSWORD_LENGTH')) {
            define('MIN_PASSWORD_LENGTH', 8);
        }
        
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            return false;
        }
        
        // Set default password requirements if not defined
        if (!defined('PASSWORD_REQUIRES_UPPERCASE')) define('PASSWORD_REQUIRES_UPPERCASE', true);
        if (!defined('PASSWORD_REQUIRES_LOWERCASE')) define('PASSWORD_REQUIRES_LOWERCASE', true);
        if (!defined('PASSWORD_REQUIRES_NUMBER')) define('PASSWORD_REQUIRES_NUMBER', true);
        if (!defined('PASSWORD_REQUIRES_SPECIAL')) define('PASSWORD_REQUIRES_SPECIAL', true);
        
        // Check for uppercase letters if required
        if (PASSWORD_REQUIRES_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check for lowercase letters if required
        if (PASSWORD_REQUIRES_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check for numbers if required
        if (PASSWORD_REQUIRES_NUMBER && !preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Check for special characters if required
        if (PASSWORD_REQUIRES_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
}

if (!function_exists('getPasswordValidationErrors')) {
    /**
     * Get password validation errors
     * 
     * @param string $password Password to validate
     * @return array Array of validation errors
     */
    function getPasswordValidationErrors($password) {
        $errors = [];
        
        if (!defined('MIN_PASSWORD_LENGTH')) {
            define('MIN_PASSWORD_LENGTH', 8);
        }
        
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            $errors[] = "Password must be at least " . MIN_PASSWORD_LENGTH . " characters long";
        }
        
        // Set default password requirements if not defined
        if (!defined('PASSWORD_REQUIRES_UPPERCASE')) define('PASSWORD_REQUIRES_UPPERCASE', true);
        if (!defined('PASSWORD_REQUIRES_LOWERCASE')) define('PASSWORD_REQUIRES_LOWERCASE', true);
        if (!defined('PASSWORD_REQUIRES_NUMBER')) define('PASSWORD_REQUIRES_NUMBER', true);
        if (!defined('PASSWORD_REQUIRES_SPECIAL')) define('PASSWORD_REQUIRES_SPECIAL', true);
        
        if (PASSWORD_REQUIRES_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (PASSWORD_REQUIRES_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (PASSWORD_REQUIRES_NUMBER && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (PASSWORD_REQUIRES_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return $errors;
    }
}

if (!function_exists('validateDate')) {
    /**
     * Validate date format
     * 
     * @param string $date Date string to validate
     * @param string $format Expected date format
     * @return bool Whether date is valid
     */
    function validateDate($date, $format = 'Y-m-d') {
        if (!defined('DATE_FORMAT')) {
            define('DATE_FORMAT', 'Y-m-d');
        }
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

if (!function_exists('validateTime')) {
    /**
     * Validate time format
     * 
     * @param string $time Time string to validate
     * @param string $format Expected time format
     * @return bool Whether time is valid
     */
    function validateTime($time, $format = 'H:i') {
        if (!defined('TIME_FORMAT')) {
            define('TIME_FORMAT', 'H:i');
        }
        $d = DateTime::createFromFormat($format, $time);
        return $d && $d->format($format) === $time;
    }
}

if (!function_exists('validatePhone')) {
    /**
     * Validate phone number
     * 
     * @param string $phone Phone number to validate
     * @return bool Whether phone number is valid
     */
    function validatePhone($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if phone number is between 9-15 digits
        return strlen($phone) >= 9 && strlen($phone) <= 15;
    }
}

if (!function_exists('validateFileUpload')) {
    /**
     * Validate file upload
     * 
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size in bytes
     * @return array Array of validation errors
     */
    function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
        // Set default values if not provided
        if ($allowedTypes === null) {
            if (!defined('ALLOWED_IMAGE_TYPES')) {
                define('ALLOWED_IMAGE_TYPES', [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'application/pdf' => 'pdf',
                    'application/msword' => 'doc',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
                ]);
            }
            $allowedTypes = ALLOWED_IMAGE_TYPES;
        }
        
        if ($maxSize === null) {
            if (!defined('MAX_UPLOAD_SIZE')) {
                define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB default
            }
            $maxSize = MAX_UPLOAD_SIZE;
        }
        
        $errors = [];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'The uploaded file exceeds the maximum file size limit.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = 'The uploaded file was only partially uploaded.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = 'No file was uploaded.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errors[] = 'Missing a temporary folder.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errors[] = 'Failed to write file to disk.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errors[] = 'A PHP extension stopped the file upload.';
                    break;
                default:
                    $errors[] = 'Unknown upload error occurred.';
            }
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'The uploaded file exceeds the maximum file size of ' . 
                       formatFileSize($maxSize) . '.';
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, array_keys($allowedTypes))) {
            $errors[] = 'The uploaded file type is not allowed. Allowed types: ' . 
                       implode(', ', array_values($allowedTypes)) . '.';
        }
        
        return $errors;
    }
    
    /**
     * Format file size in human readable format
     * 
     * @param int $bytes File size in bytes
     * @param int $precision Number of decimal places
     * @return string Formatted file size
     */
    function formatFileSize($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('validateUsername')) {
    /**
     * Validate username
     * 
     * @param string $username Username to validate
     * @return array Array of validation errors
     */
    function validateUsername($username) {
        $errors = [];
        
        if (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        }
        
        if (strlen($username) > 50) {
            $errors[] = 'Username cannot exceed 50 characters.';
        }
        
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, periods, underscores, and hyphens.';
        }
        
        return $errors;
    }
}

if (!function_exists('validateName')) {
    /**
     * Validate name (first name, last name)
     * 
     * @param string $name Name to validate
     * @return array Array of validation errors
     */
    function validateName($name) {
        $errors = [];
        
        if (empty(trim($name))) {
            $errors[] = 'Name cannot be empty.';
        }
        
        if (strlen($name) > 100) {
            $errors[] = 'Name cannot exceed 100 characters.';
        }
        
        if (!preg_match("/^[a-zA-Z'\s-]+$/", $name)) {
            $errors[] = 'Name can only contain letters, spaces, hyphens, and apostrophes.';
        }
        
        return $errors;
    }
}

if (!function_exists('validatePostalCode')) {
    /**
     * Validate postal code
     * 
     * @param string $postalCode Postal code to validate
     * @return bool Whether postal code is valid
     */
    function validatePostalCode($postalCode) {
        // Remove all non-alphanumeric characters
        $postalCode = preg_replace('/[^A-Z0-9]/', '', strtoupper($postalCode));
        
        // Check if postal code matches common formats (US, Canada, UK, etc.)
        return (bool)preg_match('/^([0-9]{5}(-[0-9]{4})?|[A-Z][0-9][A-Z] ?[0-9][A-Z][0-9]|[A-Z]{1,2}[0-9][A-Z0-9]? ?[0-9][A-Z]{2}|GIR ?0A{2})$/', $postalCode);
    }
}

if (!function_exists('sanitizeInput')) {
    /**
     * Sanitize input data
     * 
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    function sanitizeInput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = sanitizeInput($value);
            }
        } else if (is_string($data)) {
            // Remove whitespace from the beginning and end
            $data = trim($data);
            // Convert special characters to HTML entities
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $data;
    }
}
?>