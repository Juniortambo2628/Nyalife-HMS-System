<?php

/**
 * Post-deploy log cleanup script — deployed to public_html/log-cleanup.php
 *
 * Access via: https://nyalifewomensclinic.net/log-cleanup.php?key=<LOG_CLEANUP_TOKEN>
 *
 * Truncates storage/logs/laravel.log to zero bytes to prevent the log
 * from growing unbounded on shared hosting where there is no SSH.
 *
 * Production's log was observed at hundreds of KB from repeated consultation
 * store failures (parity INT column). Once the parity fix lands and the
 * migration runs, new errors should stop, but the file still needs a one-off
 * truncate to reclaim space.
 */

$key = $_GET['key'] ?? '';

if ($key === '' || $key !== ($_ENV['LOG_CLEANUP_TOKEN'] ?? getenv('LOG_CLEANUP_TOKEN'))) {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

// On cPanel, public_html is a sibling of nyalife_core (the Laravel root).
$logPath = dirname(__DIR__, 1) . '/nyalife_core/storage/logs/laravel.log';

// If running locally or in an alternate layout, fall back to the standard path.
if (! file_exists($logPath)) {
    $logPath = dirname(__DIR__) . '/storage/logs/laravel.log';
}

if (! file_exists($logPath)) {
    echo "No log file found.\n";
    exit(0);
}

$bytes = filesize($logPath);
file_put_contents($logPath, '');

echo "Truncated. Freed " . number_format($bytes) . " bytes.\n";
