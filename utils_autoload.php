<?php
/**
 * Nyalife HMS - Utilities Autoloader
 * 
 * This file loads all utility files for the application.
 * Include this file to have access to all utility functions.
 */

// Database configuration (must be first)
require_once __DIR__ . '/../config/database.php';

// Core functions (must be second)
require_once __DIR__ . '/functions.php';

// Database utilities
require_once __DIR__ . '/db_utils.php';

// Authentication class (depends on database)
require_once __DIR__ . '/Auth.php';

// Date utilities
require_once __DIR__ . '/date_utils.php';

// Error handling utilities
require_once __DIR__ . '/error_utils.php';

// API utilities
require_once __DIR__ . '/api_utils.php';

// ID generator utilities
require_once __DIR__ . '/id_generator.php';

// Report utilities
require_once __DIR__ . '/report_utils.php';

// Modal functions
require_once __DIR__ . '/modal_functions.php';

// Validation functions
require_once __DIR__ . '/validation_functions.php';

// Notification functions
require_once __DIR__ . '/notification_functions.php';

// Data access layers
require_once __DIR__ . '/data/patient_data.php';
require_once __DIR__ . '/data/user_data.php';

// UI Components
require_once __DIR__ . '/components/modal.php';
require_once __DIR__ . '/components/alert.php';
require_once __DIR__ . '/components/table.php';
require_once __DIR__ . '/components/pagination.php';

/**
 * Helper function to include all utility functions in one go
 * 
 * @param string $category Optional category to include only specific utilities
 * @return bool Success status
 */
function includeUtils($category = 'all') {
    switch (strtolower($category)) {
        case 'db':
        case 'database':
            require_once __DIR__ . '/db_utils.php';
            break;
            
        case 'date':
        case 'datetime':
            require_once __DIR__ . '/date_utils.php';
            break;
            
        case 'error':
        case 'errors':
            require_once __DIR__ . '/error_utils.php';
            break;
            
        case 'api':
            require_once __DIR__ . '/api_utils.php';
            break;
            
        case 'id':
        case 'ids':
            require_once __DIR__ . '/id_generator.php';
            break;
            
        case 'report':
        case 'reports':
            require_once __DIR__ . '/report_utils.php';
            break;
            
        case 'modal':
        case 'modals':
            require_once __DIR__ . '/modal_functions.php';
            require_once __DIR__ . '/components/modal.php';
            break;
            
        case 'validation':
            require_once __DIR__ . '/validation_functions.php';
            break;
            
        case 'notification':
        case 'notifications':
            require_once __DIR__ . '/notification_functions.php';
            break;
            
        case 'data':
            require_once __DIR__ . '/data/patient_data.php';
            require_once __DIR__ . '/data/user_data.php';
            break;
            
        case 'components':
        case 'ui':
            require_once __DIR__ . '/components/modal.php';
            require_once __DIR__ . '/components/alert.php';
            require_once __DIR__ . '/components/table.php';
            require_once __DIR__ . '/components/pagination.php';
            break;
            
        case 'all':
        default:
            // The required_once statements at the top of this file
            // have already loaded everything
            break;
    }
    
    return true;
}
?> 