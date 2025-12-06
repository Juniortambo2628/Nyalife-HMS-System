<?php
/**
 * Nyalife HMS - AJAX Authentication Handler
 * 
 * Direct handler for AJAX authentication requests that bypasses routing issues.
 */

// Include required files
require_once __DIR__ . '/../../core/DatabaseManager.php';
require_once __DIR__ . '/../../core/SessionManager.php';
require_once __DIR__ . '/../../core/ErrorHandler.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../models/UserModel.php';

// Start session if not already started
SessionManager::ensureStarted();

// Set base URL for redirects
$baseUrl = '/Nyalife-HMS-System';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
        exit;
    }

    // Get credentials
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) && $_POST['remember'] == '1';

    // Attempt authentication
    $auth = Auth::getInstance();
    
    if ($auth->authenticate($username, $password, $remember)) {
        // Success
        $role = $auth->getUserRole();
        $redirectUrl = $baseUrl . '/dashboard';
        if ($role) {
            $redirectUrl .= '/' . strtolower($role);
        }
        echo json_encode([
            'success' => true,
            'message' => 'Login successful! Welcome back.',
            'redirect' => $redirectUrl
        ]);
    } else {
        // Failed
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password. Please try again.'
        ]);
    }
} catch (Exception $e) {
    // Log error
    ErrorHandler::logSystemError($e, __METHOD__);
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
