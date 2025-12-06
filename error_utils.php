<?php
/**
 * Nyalife HMS - Error Handling Utilities
 * 
 * This file provides standardized error handling and logging functions.
 */

// Define error constants
define('ERROR_LOG_FILE', __DIR__ . '/../logs/errors.log');
define('ERROR_TYPE_SYSTEM', 'system');
define('ERROR_TYPE_DATABASE', 'database');
define('ERROR_TYPE_VALIDATION', 'validation');
define('ERROR_TYPE_SECURITY', 'security');
define('ERROR_TYPE_API', 'api');

/**
 * Log an error to the error log file
 * 
 * @param string $message Error message
 * @param string $errorType Type of error
 * @param array $context Additional context data
 * @return bool Success status
 */
function logError($message, $errorType = ERROR_TYPE_SYSTEM, $context = []) {
    // Create logs directory if it doesn't exist
    $logsDir = dirname(ERROR_LOG_FILE);
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0755, true);
    }
    
    // Format the log entry
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userId = $_SESSION['user_id'] ?? 'guest';
    $url = $_SERVER['REQUEST_URI'] ?? 'unknown';
    
    // Convert context to JSON
    $contextJson = !empty($context) ? json_encode($context) : '';
    
    // Format the log message
    $logMessage = sprintf(
        "[%s] [%s] [IP: %s] [User: %s] [URL: %s] %s %s\n",
        $timestamp,
        strtoupper($errorType),
        $ip,
        $userId,
        $url,
        $message,
        $contextJson
    );
    
    // Write to log file
    return error_log($logMessage, 3, ERROR_LOG_FILE);
}

/**
 * Log a database error
 * 
 * @param string $message Error message
 * @param string $query SQL query that caused the error
 * @param array $params Query parameters
 * @return bool Success status
 */
function logDatabaseError($message, $query = '', $params = []) {
    $context = [
        'query' => $query,
        'params' => $params
    ];
    
    return logError($message, ERROR_TYPE_DATABASE, $context);
}

/**
 * Log a validation error
 * 
 * @param string $message Error message
 * @param array $validationErrors Validation errors
 * @return bool Success status
 */
function logValidationError($message, $validationErrors = []) {
    return logError($message, ERROR_TYPE_VALIDATION, ['errors' => $validationErrors]);
}

/**
 * Log a security error
 * 
 * @param string $message Error message
 * @param array $context Additional context data
 * @return bool Success status
 */
function logSecurityError($message, $context = []) {
    return logError($message, ERROR_TYPE_SECURITY, $context);
}

/**
 * Log an API error
 * 
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 * @param array $context Additional context data
 * @return bool Success status
 */
function logApiError($message, $statusCode = 400, $context = []) {
    $context['status_code'] = $statusCode;
    return logError($message, ERROR_TYPE_API, $context);
}

/**
 * Display a user-friendly error message
 * 
 * @param string $message Error message to display
 * @param bool $logError Whether to log the error
 * @param string $errorType Type of error
 * @return void
 */
function displayError($message, $logError = true, $errorType = ERROR_TYPE_SYSTEM) {
    if ($logError) {
        logError($message, $errorType);
    }
    
    echo '<div class="alert alert-danger">';
    echo '<strong>Error:</strong> ' . htmlspecialchars($message);
    echo '</div>';
}

/**
 * Handle an exception
 * 
 * @param Exception $e Exception to handle
 * @param bool $displayError Whether to display the error
 * @param string $errorType Type of error
 * @return void
 */
function handleException($e, $displayError = true, $errorType = ERROR_TYPE_SYSTEM) {
    $message = $e->getMessage();
    $context = [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    
    logError($message, $errorType, $context);
    
    if ($displayError) {
        // In production, display a generic error message
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            displayError('An error occurred. Please try again or contact support.');
        } else {
            // In development, display detailed error information
            displayError($message);
        }
    }
}

/**
 * Set up custom error handlers
 * 
 * @return void
 */
function setupErrorHandlers() {
    // Set custom exception handler
    set_exception_handler(function($e) {
        handleException($e);
    });
    
    // Set custom error handler
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        $context = [
            'error_number' => $errno,
            'file' => $errfile,
            'line' => $errline
        ];
        
        logError($errstr, ERROR_TYPE_SYSTEM, $context);
        
        // Return false to allow PHP's internal error handler to handle the error
        return false;
    });
}

/**
 * Validate form data and return any errors
 * 
 * @param array $data Form data to validate
 * @param array $rules Validation rules
 * @return array Validation errors (empty if none)
 */
function validateFormData($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        // Skip if field doesn't have rules
        if (empty($fieldRules)) continue;
        
        // Check each rule for the field
        foreach ($fieldRules as $rule => $ruleValue) {
            $fieldValue = $data[$field] ?? null;
            
            switch ($rule) {
                case 'required':
                    if ($ruleValue && (is_null($fieldValue) || trim($fieldValue) === '')) {
                        $errors[$field][] = 'This field is required.';
                    }
                    break;
                    
                case 'min_length':
                    if (!is_null($fieldValue) && strlen($fieldValue) < $ruleValue) {
                        $errors[$field][] = "Must be at least $ruleValue characters.";
                    }
                    break;
                    
                case 'max_length':
                    if (!is_null($fieldValue) && strlen($fieldValue) > $ruleValue) {
                        $errors[$field][] = "Must be no more than $ruleValue characters.";
                    }
                    break;
                    
                case 'email':
                    if ($ruleValue && !is_null($fieldValue) && !filter_var($fieldValue, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = 'Must be a valid email address.';
                    }
                    break;
                    
                case 'numeric':
                    if ($ruleValue && !is_null($fieldValue) && !is_numeric($fieldValue)) {
                        $errors[$field][] = 'Must be a number.';
                    }
                    break;
                    
                case 'matches':
                    if ($fieldValue !== ($data[$ruleValue] ?? null)) {
                        $errors[$field][] = "Must match $ruleValue field.";
                    }
                    break;
            }
        }
    }
    
    return $errors;
}
?> 