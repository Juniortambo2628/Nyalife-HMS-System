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
if (!defined('APP_PATH')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];

    if ($domainName === 'localhost' || str_contains((string) $domainName, '127.0.0.1')) {
        define('APP_PATH', '/Nyalife-HMS-System');
    } else {
        $scriptDir = dirname((string) $_SERVER['SCRIPT_NAME'], 4);
        define('APP_PATH', ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir);
    }
}

$baseUrl = APP_PATH;

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
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] == '1';

    // Attempt authentication
    $auth = Auth::getInstance();

    if ($auth->authenticate($username, $password, $remember)) {
        // Success
        $role = $auth->getUserRole();
        $redirectUrl = $baseUrl . '/dashboard';
        if ($role !== null && $role !== '' && $role !== '0') {
            $redirectUrl .= '/' . strtolower((string) $role);
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
    ErrorHandler::logSystemError($e, 'auth_handler.php');

    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
