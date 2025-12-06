<?php
/**
 * Nyalife HMS - Main Entry Point
 * 
 * This file serves as the entry point for all web requests.
 * It routes requests to the appropriate controllers.
 */

// Include the autoloader to ensure configs and constants are loaded consistently
require_once __DIR__ . '/includes/autoload.php';

// Load configuration which defines DEBUG_MODE
require_once __DIR__ . '/config.php';

// Global error handling
try {
    // Register core class autoloading
    spl_autoload_register(function($className) {
        // Look for core classes
        if (file_exists(__DIR__ . '/includes/core/' . $className . '.php')) {
            require_once __DIR__ . '/includes/core/' . $className . '.php';
            return;
        }
    });

// Start the session
SessionManager::ensureStarted();

// Define application path constant for use throughout the application
define('APP_PATH', '/Nyalife-HMS-System');

// Get the request URI and clean it up
$requestUri = $_SERVER['REQUEST_URI'];

// Handle index.php path info format (if using PATH_INFO)
if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
    $requestUri = $_SERVER['PATH_INFO'];
}

// Clean up the request URI
$requestUri = preg_replace('/^\/Nyalife-HMS-System/', '', $requestUri); // Remove app path if present
$requestUri = preg_replace('/^\/index\.php/', '', $requestUri); // Remove index.php if present

// Always ensure request starts with a slash for consistency
if (empty($requestUri) || $requestUri[0] !== '/') {
    $requestUri = '/' . $requestUri;
}

// Debug routing information if needed (comment out in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    // Uncomment below to debug routing issues
    // error_log("Processing route: {$requestUri}");
}

// Create router
$router = new WebRouter();

// Define routes with names for easier URL generation
$router->register('GET', '/', 'HomeController', 'index', 'home');
$router->register('GET', '/dashboard', 'DashboardController', 'index', 'dashboard.default');
// BEFORE:
$router->register('GET', '/dashboard/:role', 'DashboardController', 'index', 'dashboard');
// AFTER (match AuthController URL generation):
$router->register('GET', '/dashboard/:role', 'DashboardController', 'index', 'dashboard.default');
$router->register('GET', '/patients', 'PatientController', 'index', 'patients.index');
$router->register('GET', '/patients/view/:id', 'PatientController', 'view', 'patients.view');
$router->register('GET', '/patients/create', 'PatientController', 'create', 'patients.create');
$router->register('POST', '/patients/store', 'PatientController', 'store', 'patients.store');
$router->register('GET', '/patients/edit/:id', 'PatientController', 'edit', 'patients.edit');
$router->register('POST', '/patients/update/:id', 'PatientController', 'update', 'patients.update');
$router->register('POST', '/patients/:id/medical-history', 'PatientController', 'addMedicalHistory', 'patients.medical-history');

// Authentication routes
$router->register('GET', '/login', 'AuthController', 'showLogin', 'login');
$router->register('POST', '/login', 'AuthController', 'processLogin', 'login.process');
$router->register('GET', '/logout', 'AuthController', 'logout', 'logout');
$router->register('GET', '/profile', 'UserController', 'profile', 'profile');
$router->register('GET', '/register', 'AuthController', 'showRegister', 'register');
$router->register('POST', '/register', 'AuthController', 'processRegister', 'register.process');

// User management routes
$router->register('GET', '/users', 'UserController', 'index', 'users.index');
$router->register('GET', '/users/create', 'UserController', 'create', 'users.create');
$router->register('POST', '/users/store', 'UserController', 'store', 'users.store');
$router->register('GET', '/users/view/:id', 'UserController', 'view', 'users.view');
$router->register('GET', '/users/edit/:id', 'UserController', 'edit', 'users.edit');
$router->register('POST', '/users/update/:id', 'UserController', 'update', 'users.update');
$router->register('GET', '/users/delete/:id', 'UserController', 'delete', 'users.delete');
$router->register('GET', '/profile', 'UserController', 'profile', 'profile');
$router->register('POST', '/profile/update', 'UserController', 'updateProfile', 'profile.update');

// Consultation Routes
$router->register('GET', '/consultations', 'web\ConsultationController', 'index', 'consultations.index');
$router->register('GET', '/consultations/create/:appointment_id', 'web\ConsultationController', 'create', 'consultations.create');
$router->register('POST', '/consultations/store', 'web\ConsultationController', 'store', 'consultations.store');
$router->register('GET', '/consultations/view/:id', 'web\ConsultationController', 'view', 'consultations.view');
$router->register('GET', '/consultations/edit/:id', 'web\ConsultationController', 'edit', 'consultations.edit');
$router->register('POST', '/consultations/update/:id', 'web\ConsultationController', 'update', 'consultations.update');
$router->register('GET', '/consultations/print/:id', 'web\ConsultationController', 'print', 'consultations.print');

// Web Appointment Routes
$router->register('GET', '/appointments', 'AppointmentController', 'index', 'appointments.index');
$router->register('GET', '/appointments/create', 'AppointmentController', 'create', 'appointments.create');
$router->register('POST', '/appointments/store', 'AppointmentController', 'store', 'appointments.store');
$router->register('GET', '/appointments/view/:id', 'AppointmentController', 'view', 'appointments.view');
$router->register('GET', '/appointments/edit/:id', 'AppointmentController', 'edit', 'appointments.edit');
$router->register('POST', '/appointments/update/:id', 'AppointmentController', 'update', 'appointments.update');
$router->register('GET', '/appointments/cancel/:id', 'AppointmentController', 'cancel', 'appointments.cancel');
$router->register('GET', '/appointments/calendar', 'AppointmentController', 'calendar', 'appointments.calendar');
$router->register('POST', '/appointments/:id/medical-history', 'AppointmentController', 'addMedicalHistory', 'appointments.medical-history');
$router->register('GET', '/appointments-redirect', 'AppointmentController', 'roleBasedRedirect', 'appointments.redirect');

// API Routes - Appointments
$router->register('GET', '/api/appointments', 'api/AppointmentController', 'index', 'api.appointments.index');
$router->register('POST', '/api/appointments', 'api/AppointmentController', 'create', 'api.appointments.create');
$router->register('GET', '/api/appointments/:id', 'api/AppointmentController', 'get', 'api.appointments.get');
$router->register('PUT', '/api/appointments/:id', 'api/AppointmentController', 'update', 'api.appointments.update');
$router->register('DELETE', '/api/appointments/:id', 'api/AppointmentController', 'cancel', 'api.appointments.cancel');
$router->register('GET', '/api/appointments/available-slots', 'api/AppointmentController', 'getAvailableSlots', 'api.appointments.available-slots');
$router->register('GET', '/api/appointments/stats', 'api/AppointmentController', 'stats', 'api.appointments.stats');

// Prescription routes
$router->register('GET', '/prescriptions', 'PrescriptionController', 'index', 'prescriptions.index');
$router->register('GET', '/prescriptions/create', 'PrescriptionController', 'create', 'prescriptions.create');
$router->register('POST', '/prescriptions/store', 'PrescriptionController', 'store', 'prescriptions.store');
$router->register('GET', '/prescriptions/view/:id', 'PrescriptionController', 'view', 'prescriptions.view');
$router->register('GET', '/prescriptions/print/:id', 'PrescriptionController', 'print', 'prescriptions.print');

// Lab request routes
$router->register('GET', '/lab/requests', 'LabRequestController', 'index', 'lab.requests.index');
$router->register('GET', '/lab/requests/create', 'LabRequestController', 'create', 'lab.requests.create');
$router->register('POST', '/lab/requests/store', 'LabRequestController', 'store', 'lab.requests.store');
$router->register('GET', '/lab/requests/view/:id', 'LabRequestController', 'view', 'lab.requests.view');
$router->register('GET', '/lab/requests/update-status/:id', 'LabRequestController', 'updateStatus', 'lab.requests.update-status');

// Lab test routes
$router->register('GET', '/lab/tests', 'LabTestController', 'index', 'lab.tests.index');
$router->register('GET', '/lab/tests/create', 'LabTestController', 'create', 'lab.tests.create');
$router->register('POST', '/lab/tests/store', 'LabTestController', 'store', 'lab.tests.store');
$router->register('GET', '/lab/tests/edit/:id', 'LabTestController', 'edit', 'lab.tests.edit');
$router->register('POST', '/lab/tests/update/:id', 'LabTestController', 'update', 'lab.tests.update');

// Pharmacy routes
$router->register('GET', '/pharmacy/medicines', 'PharmacyController', 'medicines', 'pharmacy.medicines');
$router->register('GET', '/pharmacy/inventory', 'PharmacyController', 'inventory', 'pharmacy.inventory');
$router->register('GET', '/pharmacy/orders', 'PharmacyController', 'orders', 'pharmacy.orders');

// Profile routes (already have profile routes in user management, but adding specific ones for clarity)
$router->register('GET', '/profile/edit', 'UserController', 'editProfile', 'profile.edit');
$router->register('POST', '/profile/update', 'UserController', 'updateProfile', 'profile.update');
$router->register('GET', '/profile/change-password', 'UserController', 'changePasswordForm', 'profile.change-password');
$router->register('POST', '/profile/change-password', 'UserController', 'changePassword', 'profile.change-password.process');

// Set 404 handler
$router->setNotFoundCallback(function() {
    http_response_code(404);
    include __DIR__ . '/includes/views/error.php';
});

// Dispatch the request
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($method, $requestUri);

} catch (Exception $e) {
    // Log the error
    ErrorHandler::logSystemError($e, 'index.php');
    
    // In debug mode, show detailed error information
    if (DEBUG_MODE) {
        echo '<h1>System Error</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<h2>Stack Trace:</h2>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        // In production, show a user-friendly error page
        http_response_code(500);
        include __DIR__ . '/includes/views/error.php';
    }
}
