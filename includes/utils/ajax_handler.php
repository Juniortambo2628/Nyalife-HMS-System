<?php
/**
 * Nyalife HMS - AJAX Handler Utility
 * 
 * Provides utilities for handling AJAX requests consistently
 */

/**
 * Detect if the current request is an AJAX request
 * 
 * @return bool True if the request is an AJAX request, false otherwise
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Set up error handling for AJAX requests
 * This ensures that errors are returned as JSON instead of HTML
 * 
 * @return void
 */
function setupAjaxErrorHandling() {
    if (isAjaxRequest()) {
        // Disable error reporting for production, but log errors
        ini_set('display_errors', 0);
        
        // Register error handler for AJAX requests
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $errstr,
                'debug' => [
                    'file' => $errfile,
                    'line' => $errline,
                    'type' => $errno
                ]
            ]);
            exit;
        });
        
        // Register shutdown function to catch fatal errors
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Fatal error: ' . $error['message'],
                    'debug' => [
                        'file' => $error['file'],
                        'line' => $error['line'],
                        'type' => $error['type']
                    ]
                ]);
            }
        });
    }
}
