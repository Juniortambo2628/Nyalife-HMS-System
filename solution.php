<?php
/**
 * Nyalife HMS - Quick Solution Access
 * 
 * This file provides direct access to the application when Apache mod_rewrite isn't working.
 */

// Define current URL base
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $domainName . '/Nyalife-HMS-System';

// Start the session
require_once __DIR__ . '/includes/core/SessionManager.php';
SessionManager::ensureStarted();

// Include necessary controllers
require_once __DIR__ . '/includes/controllers/web/HomeController.php';

// Create and run the home controller
$controller = new HomeController();
$controller->index();
?>
