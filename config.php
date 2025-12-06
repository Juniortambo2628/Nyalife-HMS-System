<?php
/**
 * Nyalife HMS - Configuration
 * Main configuration file for the application
 */

use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

// Load .env variables only once
if (!isset($_ENV['APP_NAME'])) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_USER', 'DB_NAME']);
}

// Load constants
require_once __DIR__ . '/constants.php';

// Debug mode - set to true to enable debugging features
define('DEBUG_MODE', true); // Set to false in production

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);
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
