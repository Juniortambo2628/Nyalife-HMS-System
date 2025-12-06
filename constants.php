<?php
/**
 * Nyalife HMS - Constants
 * Global constants used throughout the application
 */

// Application constants
if (!defined('APP_NAME')) define('APP_NAME', 'Nyalife HMS');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
if (!defined('ROOT_PATH')) define('ROOT_PATH', APP_ROOT); // For backward compatibility

// Environment detection is handled in config.php
// Use the environment variable if already set, otherwise detect from host
$environment = $_ENV['APP_ENV'] ?? null;

// If environment is not set, detect it from the host
if ($environment === null) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    
    // Check if we're in local development
    if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false || 
        strpos($host, '.local') !== false || 
        $serverName === 'localhost' || strpos($serverName, '127.0.0.1') !== false) {
        $environment = 'development';
    } else {
        // If no server variables are available (CLI), default to development
        if (empty($host) && empty($serverName)) {
            $environment = 'development';
        } else {
            $environment = 'production';
        }
    }
}

// Define application path for URL handling
if (!defined('APP_PATH')) {
    if ($environment === 'development') {
        define('APP_PATH', '/Nyalife-HMS-System');
    } else {
        // For production environments
        define('APP_PATH', '');
    }
}

// Session constants
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600); // 1 hour
if (!defined('REMEMBER_LIFETIME')) define('REMEMBER_LIFETIME', 2592000); // 30 days

// File upload constants
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', APP_ROOT . '/uploads/');
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
if (!defined('ALLOWED_FILE_TYPES')) define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Email constants
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USER')) define('SMTP_USER', '');
if (!defined('SMTP_PASS')) define('SMTP_PASS', '');
if (!defined('SMTP_FROM')) define('SMTP_FROM', '');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', APP_NAME);

// Security constants
if (!defined('HASH_COST')) define('HASH_COST', 12); // For password hashing
if (!defined('TOKEN_LENGTH')) define('TOKEN_LENGTH', 32); // For remember me tokens
if (!defined('CSRF_TOKEN_NAME')) define('CSRF_TOKEN_NAME', 'csrf_token');
if (!defined('CSRF_TOKEN_LENGTH')) define('CSRF_TOKEN_LENGTH', 32);

// Get base URL function if not already defined
// (Removed to avoid redeclaration. Now only in functions.php)

// Define APP_URL after getBaseUrl function is defined
// (Removed to avoid redeclaration. Now only in functions.php)

// System Information
if (!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'Nyalife HMS');
if (!defined('SYSTEM_VERSION')) define('SYSTEM_VERSION', '1.0.0');
if (!defined('SYSTEM_DESCRIPTION')) define('SYSTEM_DESCRIPTION', 'Hospital Management System');

// Theme Colors
if (!defined('PRIMARY_COLOR')) define('PRIMARY_COLOR', '#058b7c');
if (!defined('SECONDARY_COLOR')) define('SECONDARY_COLOR', '#d41559');

// File Upload Paths
if (!defined('PROFILE_IMAGES_PATH')) define('PROFILE_IMAGES_PATH', UPLOAD_DIR . 'profile_images');
if (!defined('DOCUMENTS_PATH')) define('DOCUMENTS_PATH', UPLOAD_DIR . 'documents');
if (!defined('REPORTS_PATH')) define('REPORTS_PATH', UPLOAD_DIR . 'reports');

// File Upload Limits
if (!defined('MAX_UPLOAD_SIZE')) define('MAX_UPLOAD_SIZE', MAX_FILE_SIZE);
if (!defined('ALLOWED_IMAGE_TYPES')) define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
if (!defined('ALLOWED_DOCUMENT_TYPES')) define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Pagination
if (!defined('ITEMS_PER_PAGE')) define('ITEMS_PER_PAGE', 10);
if (!defined('MAX_PAGINATION_LINKS')) define('MAX_PAGINATION_LINKS', 5);

// Password Policy
if (!defined('MIN_PASSWORD_LENGTH')) define('MIN_PASSWORD_LENGTH', 8);
if (!defined('PASSWORD_REQUIRES_UPPERCASE')) define('PASSWORD_REQUIRES_UPPERCASE', true);
if (!defined('PASSWORD_REQUIRES_LOWERCASE')) define('PASSWORD_REQUIRES_LOWERCASE', true);
if (!defined('PASSWORD_REQUIRES_NUMBER')) define('PASSWORD_REQUIRES_NUMBER', true);
if (!defined('PASSWORD_REQUIRES_SPECIAL')) define('PASSWORD_REQUIRES_SPECIAL', true);

// Date/Time Formats
if (!defined('DATE_FORMAT')) define('DATE_FORMAT', 'Y-m-d');
if (!defined('TIME_FORMAT')) define('TIME_FORMAT', 'H:i:s');
if (!defined('DATETIME_FORMAT')) define('DATETIME_FORMAT', 'Y-m-d H:i:s');
if (!defined('DISPLAY_DATE_FORMAT')) define('DISPLAY_DATE_FORMAT', 'd M Y');
if (!defined('DISPLAY_TIME_FORMAT')) define('DISPLAY_TIME_FORMAT', 'h:i A');
if (!defined('DISPLAY_DATETIME_FORMAT')) define('DISPLAY_DATETIME_FORMAT', 'd M Y h:i A');

// API Settings
if (!defined('API_VERSION')) define('API_VERSION', 'v1');
if (!defined('API_RATE_LIMIT')) define('API_RATE_LIMIT', 100); // requests per minute
if (!defined('API_TIMEOUT')) define('API_TIMEOUT', 30); // seconds

// Cache Settings
if (!defined('CACHE_ENABLED')) define('CACHE_ENABLED', true);
if (!defined('CACHE_LIFETIME')) define('CACHE_LIFETIME', 3600); // 1 hour

// Error Reporting
if (!defined('LOG_ERRORS')) define('LOG_ERRORS', true);
if (!defined('ERROR_LOG_PATH')) define('ERROR_LOG_PATH', APP_ROOT . '/logs/error.log');

// Audit Logging
if (!defined('AUDIT_LOG_TO_FILE')) define('AUDIT_LOG_TO_FILE', false); // Set to true to enable file logging for audit logs

// System Roles
if (!defined('ROLE_ADMIN')) define('ROLE_ADMIN', 'admin');
if (!defined('ROLE_DOCTOR')) define('ROLE_DOCTOR', 'doctor');
if (!defined('ROLE_NURSE')) define('ROLE_NURSE', 'nurse');
if (!defined('ROLE_LAB_TECH')) define('ROLE_LAB_TECH', 'lab_technician');
if (!defined('ROLE_PHARMACIST')) define('ROLE_PHARMACIST', 'pharmacist');
if (!defined('ROLE_PATIENT')) define('ROLE_PATIENT', 'patient');

// Status Constants
if (!defined('STATUS_ACTIVE')) define('STATUS_ACTIVE', 'active');
if (!defined('STATUS_INACTIVE')) define('STATUS_INACTIVE', 'inactive');
if (!defined('STATUS_PENDING')) define('STATUS_PENDING', 'pending');
if (!defined('STATUS_COMPLETED')) define('STATUS_COMPLETED', 'completed');
if (!defined('STATUS_CANCELLED')) define('STATUS_CANCELLED', 'cancelled');

// Appointment Status
if (!defined('APPOINTMENT_SCHEDULED')) define('APPOINTMENT_SCHEDULED', 'scheduled');
if (!defined('APPOINTMENT_COMPLETED')) define('APPOINTMENT_COMPLETED', 'completed');
if (!defined('APPOINTMENT_CANCELLED')) define('APPOINTMENT_CANCELLED', 'cancelled');
if (!defined('APPOINTMENT_NO_SHOW')) define('APPOINTMENT_NO_SHOW', 'no_show');

// Lab Request Status
if (!defined('LAB_REQUEST_PENDING')) define('LAB_REQUEST_PENDING', 'pending');
if (!defined('LAB_REQUEST_IN_PROGRESS')) define('LAB_REQUEST_IN_PROGRESS', 'in_progress');
if (!defined('LAB_REQUEST_COMPLETED')) define('LAB_REQUEST_COMPLETED', 'completed');
if (!defined('LAB_REQUEST_CANCELLED')) define('LAB_REQUEST_CANCELLED', 'cancelled');

// Prescription Status
if (!defined('PRESCRIPTION_PENDING')) define('PRESCRIPTION_PENDING', 'pending');
if (!defined('PRESCRIPTION_DISPENSED')) define('PRESCRIPTION_DISPENSED', 'dispensed');
if (!defined('PRESCRIPTION_CANCELLED')) define('PRESCRIPTION_CANCELLED', 'cancelled');

// Payment Status
if (!defined('PAYMENT_PENDING')) define('PAYMENT_PENDING', 'pending');
if (!defined('PAYMENT_COMPLETED')) define('PAYMENT_COMPLETED', 'completed');
if (!defined('PAYMENT_FAILED')) define('PAYMENT_FAILED', 'failed');
if (!defined('PAYMENT_REFUNDED')) define('PAYMENT_REFUNDED', 'refunded');

// Notification Types
if (!defined('NOTIFICATION_INFO')) define('NOTIFICATION_INFO', 'info');
if (!defined('NOTIFICATION_SUCCESS')) define('NOTIFICATION_SUCCESS', 'success');
if (!defined('NOTIFICATION_WARNING')) define('NOTIFICATION_WARNING', 'warning');
if (!defined('NOTIFICATION_ERROR')) define('NOTIFICATION_ERROR', 'error');