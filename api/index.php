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
    
    // Process URL to get path
    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = '/api/';
    $path = substr($requestUri, strpos($requestUri, $basePath) + strlen($basePath));
    $path = parse_url($path, PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Create router
    $router = new Router();
    
    // Register API routes with names for easier reference
    
    // Appointment routes
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
    
    // Dispatch the request
    $router->dispatch($method, $path);
    
} catch (Exception $e) {
    // Handle any unhandled exceptions
    Router::handleException($e, DEBUG_MODE);
}
