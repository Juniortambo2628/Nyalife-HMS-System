<?php
/**
 * Nyalife HMS - Autoloader
 * Combines Composer's autoloader with project-specific logic
 */

// 1. Load Composer autoload if present (production on cPanel may not have vendor/)
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// 2. Load configuration and constants (only if not already loaded)
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config.php';
}

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/database.php';
}

// 3. Load essential utility files (only if not already loaded)
if (!function_exists('getBaseUrl')) {
    require_once __DIR__ . '/../functions.php';
}

// 4. Custom class autoloading (controllers, models, core classes)
spl_autoload_register(function ($className): void {
    if (str_contains((string) $className, '\\')) {
        $parts = explode('\\', (string) $className);
        $className = array_pop($parts);
        $namespace = strtolower(implode('/', $parts));

        $file = __DIR__ . '/controllers/' . $namespace . '/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    if (str_contains((string) $className, 'Controller')) {
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

    if (str_contains((string) $className, 'Model')) {
        $file = __DIR__ . '/models/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Load core classes
    $file = __DIR__ . '/core/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Load helper classes
    $file = __DIR__ . '/helpers/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }
});

// 5. Optional: Adjust error reporting if not in debug mode
if (defined('DEBUG_MODE') && !DEBUG_MODE) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
}
