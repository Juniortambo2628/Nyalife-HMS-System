<?php
/**
 * Nyalife HMS - Configuration
 * Main configuration file for the application
 */

use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

// Auto-detect environment
function detectEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    
    // Check if we're in production
    if ($host === 'www.nyalifewomensclinic.net' || $host === 'nyalifewomensclinic.net') {
        return 'production';
    }
    
    // Check if we're in local development
    if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false || strpos($host, '.local') !== false) {
        return 'development';
    }
    
    // If no server variables are available (CLI), default to development
    if (empty($host) && empty($serverName)) {
        return 'development';
    }
    
    // Default to production for safety
    return 'production';
}

$environment = detectEnvironment();

// Set environment variable for constants.php
$_ENV['APP_ENV'] = $environment;

// Load .env variables only once
if (!isset($_ENV['APP_NAME'])) {
    try {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_USER', 'DB_NAME']);
    } catch (Exception $e) {
        // If .env file doesn't exist, try to load from env.production
        if (file_exists(__DIR__ . '/env.production')) {
            $envContent = file_get_contents(__DIR__ . '/env.production');
            $lines = explode("\n", $envContent);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && strpos($line, '#') !== 0) {
                    $parts = explode('=', $line, 2);
                    if (count($parts) === 2) {
                        $key = trim($parts[0]);
                        $value = trim($parts[1], '"\'');
                        $_ENV[$key] = $value;
                        putenv("$key=$value");
                    }
                }
            }
        } else {
            // Set default production values if no env files exist
            $_ENV['APP_NAME'] = 'Nyalife HMS';
            $_ENV['APP_ENV'] = 'production';
            $_ENV['APP_DEBUG'] = 'false';
            $_ENV['DB_HOST'] = 'localhost';
            $_ENV['DB_NAME'] = 'nyalifew_hms_prod';
            $_ENV['DB_USER'] = 'nyalifew_admin_prod';
            $_ENV['DB_PASS'] = 'NYALIFEADMIN123';
            $_ENV['APP_URL'] = 'https://www.nyalifewomensclinic.net';
        }
    }
}

// Load constants
require_once __DIR__ . '/constants.php';

// Set debug mode based on environment
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', $environment === 'development');
}

// Debug mode is now properly set based on environment

// Error reporting based on environment
error_reporting($environment === 'development' ? E_ALL : 0);
ini_set('display_errors', $environment === 'development' ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', ERROR_LOG_PATH);

// Time zone
date_default_timezone_set('Africa/Nairobi');

// Character encoding
mb_internal_encoding('UTF-8');

// Create required directories if they don't exist
$required_dirs = [
    UPLOAD_DIR,
    dirname(ERROR_LOG_PATH),
    UPLOAD_DIR . 'patients',
    UPLOAD_DIR . 'documents',
    UPLOAD_DIR . 'temp',
    PROFILE_IMAGES_PATH,
    DOCUMENTS_PATH,
    REPORTS_PATH
];

foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Set proper permissions for upload and log directories
if (is_dir(UPLOAD_DIR)) {
    chmod(UPLOAD_DIR, 0755);
}
if (is_dir(dirname(ERROR_LOG_PATH))) {
    chmod(dirname(ERROR_LOG_PATH), 0755);
}