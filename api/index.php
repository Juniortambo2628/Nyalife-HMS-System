<?php
/**
 * Nyalife HMS - API Entry Point
 * 
 * This file serves as the entry point for all API requests.
 */

// Enable error handling for API responses
// Load configuration which defines DEBUG_MODE
require_once __DIR__ . '/../includes/config.php';

try {
    // Load required files
    require_once __DIR__ . '/../includes/core/Router.php';
    require_once __DIR__ . '/../includes/core/ErrorHandler.php';
    
    // Include controllers
    require_once __DIR__ . '/../includes/controllers/api/AppointmentController.php';
    require_once __DIR__ . '/../includes/controllers/api/UserController.php';
    require_once __DIR__ . '/../includes/controllers/api/PatientController.php';
    require_once __DIR__ . '/../includes/controllers/api/ValidationController.php';
    require_once __DIR__ . '/../includes/controllers/api/ApiNotificationsController.php';
    require_once __DIR__ . '/../includes/controllers/api/CommunicationController.php';
    
    // Process URL to get path
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = '/api/';
    
    // Find the position of /api/ in the path (handles subdirectory like /Nyalife-HMS-System/api/)
    $apiPos = strpos($requestUri, $basePath);
    if ($apiPos !== false) {
        $path = substr($requestUri, $apiPos + strlen($basePath));
    } else {
        // Fallback: if /api/ not found, try to extract after 'api/'
        if (($pos = strpos($requestUri, 'api/')) !== false) {
            $path = substr($requestUri, $pos + strlen('api/'));
        } else {
            $path = '';
        }
    }
    
    // Remove leading/trailing slashes
    $path = trim($path, '/');
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Create router
    $router = new Router();
    
    // Register API routes with names for easier reference
    
    // Appointment routes (more specific routes first)
    $router->register('GET', 'appointments/pending-count', 'AppointmentController', 'pendingCount', 'api.appointments.pending-count');
    $router->register('GET', 'appointments/(\d+)', 'AppointmentController', 'getAppointment', 'api.appointment.view');
    $router->register('GET', 'appointments', 'AppointmentController', 'getAppointments', 'api.appointments.list');
    $router->register('POST', 'appointments', 'AppointmentController', 'createAppointment', 'api.appointment.create');
    $router->register('PUT', 'appointments/(\d+)/status', 'AppointmentController', 'updateStatus', 'api.appointment.status');
    $router->register('DELETE', 'appointments/(\d+)', 'AppointmentController', 'cancelAppointment', 'api.appointment.cancel');
    
    // User routes
    $router->register('GET', 'users/(\d+)', 'UserController', 'getUser', 'api.user.view');
    $router->register('GET', 'users', 'UserController', 'getUsers', 'api.users.list');
    $router->register('GET', 'profile', 'UserController', 'getProfile', 'api.user.profile');
    
    // Patient routes
    $router->register('GET', 'patients/(\d+)', 'PatientController', 'getPatient', 'api.patient.view');
    $router->register('GET', 'patients', 'PatientController', 'getPatients', 'api.patients.list');
    
    // Validation routes (for client-side checks)
    $router->register('POST', 'validate-email', 'ValidationController', 'validateEmail', 'api.validate.email');
    $router->register('POST', 'validate-appointment', 'ValidationController', 'validateAppointment', 'api.validate.appointment');
    $router->register('GET', 'available-slots', 'ValidationController', 'getAvailableSlots', 'api.available.slots');
    
    // Notification routes
    $router->register('GET', 'notifications', 'ApiNotificationsController', 'index', 'api.notifications.list');
    $router->register('GET', 'notifications/count', 'ApiNotificationsController', 'count', 'api.notifications.count');
    $router->register('PUT', 'notifications/(\d+)/read', 'ApiNotificationsController', 'markAsRead', 'api.notification.read');
    $router->register('PUT', 'notifications/mark-all-read', 'ApiNotificationsController', 'markAllAsRead', 'api.notifications.mark-all-read');
    
    // Messages routes  
    $router->register('GET', 'messages/inbox', 'CommunicationController', 'inbox', 'api.messages.inbox');
    $router->register('POST', 'messages', 'CommunicationController', 'sendMessage', 'api.messages.send');
    
    // Dispatch the request
    $router->dispatch($method, $path);
    
} catch (Exception $e) {
    // Handle any unhandled exceptions
    Router::handleException($e, DEBUG_MODE);
}
