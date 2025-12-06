<?php
/**
 * Nyalife HMS - Debug Helper
 * 
 * Helps with error handling for AJAX requests
 */

// Ensure PHP errors don't break JSON responses
function setupAjaxErrorHandling() {
    // For AJAX requests, set up error handling to return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        
        // Set appropriate content type
        header('Content-Type: application/json');
        
        // Custom error handler
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            $error = [
                'success' => false,
                'message' => 'PHP Error: ' . $errstr,
                'debug' => [
                    'file' => $errfile,
                    'line' => $errline,
                    'type' => $errno
                ]
            ];
            
            echo json_encode($error);
            exit;
        });
        
        // Register shutdown function to catch fatal errors
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $errorResponse = [
                    'success' => false,
                    'message' => 'Fatal Error: ' . $error['message'],
                    'debug' => [
                        'file' => $error['file'],
                        'line' => $error['line'],
                        'type' => $error['type']
                    ]
                ];
                
                echo json_encode($errorResponse);
            }
        });
    }
}

// Create standard response for AJAX requests
function createAjaxResponse($success, $message, $data = null) {
    return [
        'success' => (bool)$success,
        'message' => $message,
        'data' => $data
    ];
}

// Include the json_response helper
require_once __DIR__ . '/utils/json_response.php';
