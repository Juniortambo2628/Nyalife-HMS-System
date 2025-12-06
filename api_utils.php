<?php
/**
 * Nyalife HMS - API Utilities
 * 
 * This file contains utility functions for API endpoints across the application.
 */

/**
 * Send a JSON API response with proper headers
 * 
 * @param array $data Response data
 * @param int $statusCode HTTP status code
 */
function sendApiResponse($data, $statusCode = 200) {
    // Set HTTP response code
    http_response_code($statusCode);
    
    // Set appropriate headers
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    
    // Output JSON
    echo json_encode($data);
    exit;
}

/**
 * Send a successful API response
 * 
 * @param mixed $data Response data payload
 * @param string $message Success message
 * @param int $statusCode HTTP status code (default: 200)
 */
function sendSuccessResponse($data = null, $message = 'Operation completed successfully', $statusCode = 200) {
    $response = [
        'success' => true,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendApiResponse($response, $statusCode);
}

/**
 * Send an error API response
 * 
 * @param string $message Error message
 * @param int $statusCode HTTP status code (default: 400)
 * @param array $errors Validation errors (optional)
 * @param mixed $data Additional error data (optional)
 */
function sendErrorResponse($message, $statusCode = 400, $errors = null, $data = null) {
    $response = [
        'success' => false,
        'message' => $message
    ];
    
    if ($errors !== null) {
        $response['errors'] = $errors;
    }
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendApiResponse($response, $statusCode);
}

/**
 * Validate API request method
 * 
 * @param string|array $allowedMethods Allowed HTTP method(s)
 * @return bool Whether the current request method is allowed
 */
function validateRequestMethod($allowedMethods) {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if (is_array($allowedMethods)) {
        return in_array($method, $allowedMethods);
    } else {
        return $method === $allowedMethods;
    }
}

/**
 * Ensure API request has required parameters
 * 
 * @param array $requiredParams Names of required parameters
 * @param array $source Source array ($_GET, $_POST, etc)
 * @return array Missing parameters, empty if none
 */
function checkRequiredParams($requiredParams, $source = null) {
    // If source not specified, determine based on request method
    if ($source === null) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                $source = $_GET;
                break;
            case 'POST':
                $source = $_POST;
                break;
            case 'PUT':
            case 'DELETE':
                // For PUT/DELETE, parse the input
                parse_str(file_get_contents('php://input'), $source);
                break;
            default:
                $source = $_REQUEST;
        }
    }
    
    $missing = [];
    
    foreach ($requiredParams as $param) {
        if (!isset($source[$param]) || trim($source[$param]) === '') {
            $missing[] = $param;
        }
    }
    
    return $missing;
}

/**
 * Get JSON data from request body
 * 
 * @return array|null Decoded JSON data or null if invalid
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        return null;
    }
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }
    
    return $data;
}

/**
 * Handle API request with method validation
 * 
 * @param string|array $allowedMethods Allowed HTTP method(s)
 * @param callable $callback Function to handle the request
 */
function handleApiRequest($allowedMethods, $callback) {
    // Enable CORS for API endpoints
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    
    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Validate request method
    if (!validateRequestMethod($allowedMethods)) {
        $allowed = is_array($allowedMethods) ? implode(', ', $allowedMethods) : $allowedMethods;
        sendErrorResponse("Method not allowed. Use $allowed.", 405);
    }
    
    // Call the handler callback
    try {
        call_user_func($callback);
    } catch (Exception $e) {
        sendErrorResponse('Error processing request: ' . $e->getMessage(), 500);
    }
}

/**
 * Process a standard CRUD API endpoint
 * 
 * @param string $entityName Name of the entity (for logging)
 * @param string $idParam Name of the ID parameter
 * @param array $handlers Array of CRUD handlers ['create' => fn(), 'read' => fn(), etc.]
 */
function processApiCrud($entityName, $idParam, $handlers) {
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET[$idParam]) ? $_GET[$idParam] : null;
    
    try {
        switch ($method) {
            case 'GET':
                if ($id && isset($handlers['getOne'])) {
                    call_user_func($handlers['getOne'], $id);
                } elseif (isset($handlers['getAll'])) {
                    call_user_func($handlers['getAll']);
                } else {
                    sendErrorResponse("Operation not supported", 405);
                }
                break;
                
            case 'POST':
                if (isset($handlers['create'])) {
                    call_user_func($handlers['create']);
                } else {
                    sendErrorResponse("Operation not supported", 405);
                }
                break;
                
            case 'PUT':
                if ($id && isset($handlers['update'])) {
                    call_user_func($handlers['update'], $id);
                } else {
                    sendErrorResponse("Invalid request for update operation", 400);
                }
                break;
                
            case 'DELETE':
                if ($id && isset($handlers['delete'])) {
                    call_user_func($handlers['delete'], $id);
                } else {
                    sendErrorResponse("Invalid request for delete operation", 400);
                }
                break;
                
            default:
                sendErrorResponse("Method not allowed", 405);
        }
    } catch (Exception $e) {
        // Log error
        if (function_exists('logAction') && isset($_SESSION['user_id'])) {
            logAction(
                $_SESSION['user_id'], 
                'api_error', 
                $entityName, 
                $id, 
                $e->getMessage()
            );
        }
        
        sendErrorResponse('Server error: ' . $e->getMessage(), 500);
    }
}
?> 