<?php
/**
 * Nyalife HMS - Autoloader
 * Combines Composer's autoloader with project-specific logic
 */

// 1. Load Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Load configuration and constants
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';

// 3. Load essential utility files
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../validation_functions.php';
require_once __DIR__ . '/../db_utils.php';
require_once __DIR__ . '/core/Utilities.php';

// 4. Custom class autoloading (controllers, models)
spl_autoload_register(function($className) {
    if (strpos($className, '\\') !== false) {
        $parts = explode('\\', $className);
        $className = array_pop($parts);
        $namespace = strtolower(implode('/', $parts));

        $file = __DIR__ . '/controllers/' . $namespace . '/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    if (strpos($className, 'Controller') !== false) {
        $file = __DIR__ . '/controllers/web/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }

        $file = __DIR__ . '/controllers/api/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    if (strpos($className, 'Model') !== false) {
        $file = __DIR__ . '/models/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// 5. Optional: Adjust error reporting if not in debug mode
if (defined('DEBUG_MODE') && !DEBUG_MODE) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
}
