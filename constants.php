<?php
/**
 * Nyalife HMS - Constants
 * Global constants used throughout the application
 */

// Application constants
define('APP_NAME', 'Nyalife HMS');
define('APP_VERSION', '1.0.0');
define('APP_ROOT', dirname(__DIR__));
define('ROOT_PATH', APP_ROOT); // For backward compatibility

// Session constants
define('SESSION_LIFETIME', 3600); // 1 hour
define('REMEMBER_LIFETIME', 2592000); // 30 days

// File upload constants
define('UPLOAD_DIR', APP_ROOT . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Email constants
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', '');
define('SMTP_FROM_NAME', APP_NAME);

// Security constants
define('HASH_COST', 12); // For password hashing
define('TOKEN_LENGTH', 32); // For remember me tokens
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LENGTH', 32);

// Get base URL function if not already defined
// (Removed to avoid redeclaration. Now only in functions.php)

// Define APP_URL after getBaseUrl function is defined
// (Removed to avoid redeclaration. Now only in functions.php)

// System Information
define('SYSTEM_NAME', 'Nyalife HMS');
define('SYSTEM_VERSION', '1.0.0');
define('SYSTEM_DESCRIPTION', 'Hospital Management System');

// Theme Colors
define('PRIMARY_COLOR', '#058b7c');
define('SECONDARY_COLOR', '#d41559');

// File Upload Paths
define('PROFILE_IMAGES_PATH', UPLOAD_DIR . 'profile_images');
define('DOCUMENTS_PATH', UPLOAD_DIR . 'documents');
define('REPORTS_PATH', UPLOAD_DIR . 'reports');

// File Upload Limits
define('MAX_UPLOAD_SIZE', MAX_FILE_SIZE);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Pagination
define('ITEMS_PER_PAGE', 10);
define('MAX_PAGINATION_LINKS', 5);

// Password Policy
define('MIN_PASSWORD_LENGTH', 8);
define('PASSWORD_REQUIRES_UPPERCASE', true);
define('PASSWORD_REQUIRES_LOWERCASE', true);
define('PASSWORD_REQUIRES_NUMBER', true);
define('PASSWORD_REQUIRES_SPECIAL', true);

// Date/Time Formats
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M Y');
define('DISPLAY_TIME_FORMAT', 'h:i A');
define('DISPLAY_DATETIME_FORMAT', 'd M Y h:i A');

// API Settings
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per minute
define('API_TIMEOUT', 30); // seconds

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// Error Reporting
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', APP_ROOT . '/logs/error.log');

// System Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_DOCTOR', 'doctor');
define('ROLE_NURSE', 'nurse');
define('ROLE_LAB_TECH', 'lab_technician');
define('ROLE_PHARMACIST', 'pharmacist');
define('ROLE_PATIENT', 'patient');

// Status Constants
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_PENDING', 'pending');
define('STATUS_COMPLETED', 'completed');
define('STATUS_CANCELLED', 'cancelled');

// Appointment Status
define('APPOINTMENT_SCHEDULED', 'scheduled');
define('APPOINTMENT_COMPLETED', 'completed');
define('APPOINTMENT_CANCELLED', 'cancelled');
define('APPOINTMENT_NO_SHOW', 'no_show');

// Lab Request Status
define('LAB_REQUEST_PENDING', 'pending');
define('LAB_REQUEST_IN_PROGRESS', 'in_progress');
define('LAB_REQUEST_COMPLETED', 'completed');
define('LAB_REQUEST_CANCELLED', 'cancelled');

// Prescription Status
define('PRESCRIPTION_PENDING', 'pending');
define('PRESCRIPTION_DISPENSED', 'dispensed');
define('PRESCRIPTION_CANCELLED', 'cancelled');

// Payment Status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_COMPLETED', 'completed');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_REFUNDED', 'refunded');

// Notification Types
define('NOTIFICATION_INFO', 'info');
define('NOTIFICATION_SUCCESS', 'success');
define('NOTIFICATION_WARNING', 'warning');
define('NOTIFICATION_ERROR', 'error');
?> 