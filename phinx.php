<?php
/**
 * Phinx Configuration File for Nyalife HMS
 * 
 * This configuration file sets up Phinx for database migrations.
 */

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'"); // Remove quotes and whitespace
        $_ENV[$name] = $value;
        putenv(sprintf('%s=%s', $name, $value));
    }
}

// Database configuration
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'nyalifew_hms_prod';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';
$dbPort = $_ENV['DB_PORT'] ?? '3306';
$dbCharset = 'utf8mb4';

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => $dbHost,
            'name' => $dbName,
            'user' => $dbUser,
            'pass' => $dbPass,
            'port' => $dbPort,
            'charset' => $dbCharset,
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'production' => [
            'adapter' => 'mysql',
            'host' => $dbHost,
            'name' => $dbName,
            'user' => $dbUser,
            'pass' => $dbPass,
            'port' => $dbPort,
            'charset' => $dbCharset,
            'collation' => 'utf8mb4_unicode_ci',
        ]
    ],
    'version_order' => 'creation'
];
