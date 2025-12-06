<?php
/**
 * Nyalife HMS - Main Entry Point
 * 
 * This file serves as the entry point for all web requests.
 * It routes requests to the appropriate controllers.
 */

// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Set session started flag
if (!isset($GLOBALS['session_started'])) {
    $GLOBALS['session_started'] = true;
}

// APP_PATH is now defined in constants.php to avoid conflicts
// The constants.php file handles the environment detection and APP_PATH definition

// Get the request URI and clean it up
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Check for route parameter from .htaccess rewrite
if (isset($_GET['route'])) {
    $requestUri = '/' . $_GET['route'];
    // Remove route from $_GET to prevent it from interfering with controllers
    unset($_GET['route']);
} else if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
    // Handle index.php path info format (if using PATH_INFO)
    $requestUri = $_SERVER['PATH_INFO'];
} else {
    // Extract the path from REQUEST_URI
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

// Clean up the request URI - remove APP_PATH and index.php if present
if (defined('APP_PATH') && !empty(APP_PATH)) {
    // Handle both cases: when APP_PATH is in the URI and when it's not
    if (strpos($requestUri, APP_PATH) === 0) {
        $requestUri = substr($requestUri, strlen(APP_PATH));
    }
}

// Remove any index.php references
$requestUri = preg_replace('/^\/index\.php/', '', $requestUri);

// Remove query string if present
$requestUri = preg_replace('/\?.*$/', '', $requestUri);

// Always ensure request starts with a slash for consistency
if (empty($requestUri) || $requestUri[0] !== '/') {
    $requestUri = '/' . $requestUri;
}

// Debug routing information if in debug mode
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log("Processing route: {$requestUri}");
    error_log("Original URI: {$_SERVER['REQUEST_URI']}");
    error_log("APP_PATH: " . APP_PATH);
    error_log("HTTP_HOST: {$_SERVER['HTTP_HOST']}");
    error_log("SCRIPT_NAME: {$_SERVER['SCRIPT_NAME']}");
}

// Debug routing information if in debug mode
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log("Processing route: {$requestUri}");
    error_log("Original URI: {$_SERVER['REQUEST_URI']}");
    error_log("APP_PATH: " . APP_PATH);
    error_log("HTTP_HOST: {$_SERVER['HTTP_HOST']}");
    error_log("SCRIPT_NAME: {$_SERVER['SCRIPT_NAME']}");
}

// Create router - use the singleton instance
$router = WebRouter::getInstance();

// Define routes with names for easier URL generation
// Home page and services routes
$router->register('GET', '/', 'HomeController', 'index', 'home');
$router->register('GET', '/services', 'HomeController', 'services', 'services');
$router->register('GET', '/services/obstetrics', 'HomeController', 'obstetricsServices', 'services.obstetrics');
$router->register('GET', '/services/gynecology', 'HomeController', 'gynecologyServices', 'services.gynecology');
$router->register('GET', '/services/laboratory', 'HomeController', 'laboratoryServices', 'services.laboratory');
$router->register('GET', '/services/pharmacy', 'HomeController', 'pharmacyServices', 'services.pharmacy');
$router->register('GET', '/about', 'HomeController', 'about', 'about');
$router->register('GET', '/contact', 'HomeController', 'contact', 'contact');
$router->register('POST', '/contact', 'HomeController', 'sendContact', 'contact.send');

// Newsletter and social media routes
$router->register('POST', '/newsletter/subscribe', 'HomeController', 'subscribeNewsletter', 'newsletter.subscribe');
$router->register('GET', '/social/facebook', 'HomeController', 'redirectToFacebook', 'social.facebook');
$router->register('GET', '/social/twitter', 'HomeController', 'redirectToTwitter', 'social.twitter');
$router->register('GET', '/social/instagram', 'HomeController', 'redirectToInstagram', 'social.instagram');
$router->register('GET', '/social/linkedin', 'HomeController', 'redirectToLinkedIn', 'social.linkedin');

// Dashboard routes
$router->register('GET', '/dashboard', 'DashboardController', 'index', 'dashboard.default');
// Role-specific dashboard (doctor, nurse, admin, etc.)
$router->register('GET', '/dashboard/:role', 'DashboardController', 'index', 'dashboard.role');

// Patient routes
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
$router->register('GET', '/register', 'AuthController', 'showRegister', 'register');
$router->register('POST', '/register', 'AuthController', 'processRegister', 'register.process');
$router->register('GET', '/register/patient', 'AuthController', 'showPatientRegister', 'register.patient');
$router->register('POST', '/register/patient', 'AuthController', 'processPatientRegister', 'register.patient.process');
$router->register('GET', '/forgot-password', 'AuthController', 'showForgotPassword', 'forgot-password');
$router->register('POST', '/forgot-password', 'AuthController', 'processForgotPassword', 'forgot-password.process');
$router->register('GET', '/reset-password/:token', 'AuthController', 'showResetPassword', 'reset-password');
$router->register('POST', '/reset-password/:token', 'AuthController', 'processResetPassword', 'reset-password.process');

// User management routes
$router->register('GET', '/users', 'UserController', 'index', 'users.index');
$router->register('GET', '/users/create', 'UserController', 'create', 'users.create');
$router->register('POST', '/users/store', 'UserController', 'store', 'users.store');
$router->register('GET', '/users/view/:id', 'UserController', 'view', 'users.view');
$router->register('GET', '/users/edit/:id', 'UserController', 'edit', 'users.edit');
$router->register('POST', '/users/update/:id', 'UserController', 'update', 'users.update');
$router->register('GET', '/users/delete/:id', 'UserController', 'delete', 'users.delete');
// Profile routes
$router->register('GET', '/profile', 'UserController', 'profile', 'profile');
$router->register('GET', '/profile/edit', 'UserController', 'editProfile', 'profile.edit');
$router->register('POST', '/profile/update', 'UserController', 'updateProfile', 'profile.update');
$router->register('GET', '/profile/change-password', 'UserController', 'changePasswordForm', 'profile.change-password');
$router->register('POST', '/profile/change-password', 'UserController', 'changePassword', 'profile.change-password.process');

// Department routes (NEW)
$router->register('GET', '/departments', 'DepartmentController', 'index', 'departments.index');
$router->register('GET', '/departments/create', 'DepartmentController', 'create', 'departments.create');
$router->register('POST', '/departments/store', 'DepartmentController', 'store', 'departments.store');
$router->register('GET', '/departments/show/:id', 'DepartmentController', 'show', 'departments.show');
$router->register('GET', '/departments/edit/:id', 'DepartmentController', 'edit', 'departments.edit');
$router->register('POST', '/departments/update/:id', 'DepartmentController', 'update', 'departments.update');
$router->register('POST', '/departments/delete/:id', 'DepartmentController', 'delete', 'departments.delete');
$router->register('GET', '/departments/activate/:id', 'DepartmentController', 'activate', 'departments.activate');
$router->register('GET', '/departments/deactivate/:id', 'DepartmentController', 'deactivate', 'departments.deactivate');

// Invoice routes (NEW)
$router->register('GET', '/invoices', 'InvoiceController', 'index', 'invoices.index');
$router->register('GET', '/invoices/create', 'InvoiceController', 'create', 'invoices.create');
$router->register('POST', '/invoices/store', 'InvoiceController', 'store', 'invoices.store');
$router->register('GET', '/invoices/show/:id', 'InvoiceController', 'show', 'invoices.show');
$router->register('GET', '/invoices/edit/:id', 'InvoiceController', 'edit', 'invoices.edit');
$router->register('POST', '/invoices/update/:id', 'InvoiceController', 'update', 'invoices.update');
$router->register('POST', '/invoices/delete/:id', 'InvoiceController', 'delete', 'invoices.delete');
$router->register('GET', '/invoices/print/:id', 'InvoiceController', 'print', 'invoices.print');
$router->register('GET', '/invoices/export/csv', 'InvoiceController', 'exportCsv', 'invoices.export.csv');
$router->register('GET', '/invoices/export/pdf', 'InvoiceController', 'exportPdf', 'invoices.export.pdf');

// Payment routes (NEW)
$router->register('GET', '/payments', 'PaymentController', 'index', 'payments.index');
$router->register('GET', '/payments/create', 'PaymentController', 'create', 'payments.create');
$router->register('POST', '/payments/store', 'PaymentController', 'store', 'payments.store');
$router->register('GET', '/payments/show/:id', 'PaymentController', 'show', 'payments.show');
$router->register('GET', '/payments/edit/:id', 'PaymentController', 'edit', 'payments.edit');
$router->register('POST', '/payments/update/:id', 'PaymentController', 'update', 'payments.update');
$router->register('POST', '/payments/delete/:id', 'PaymentController', 'delete', 'payments.delete');
$router->register('GET', '/payments/print/:id', 'PaymentController', 'print', 'payments.print');
$router->register('GET', '/payments/complete/:id', 'PaymentController', 'complete', 'payments.complete');
$router->register('GET', '/payments/export/csv', 'PaymentController', 'exportCsv', 'payments.export.csv');
$router->register('GET', '/payments/export/pdf', 'PaymentController', 'exportPdf', 'payments.export.pdf');

// Follow-up routes (NEW)
$router->register('GET', '/follow-ups', 'FollowUpController', 'index', 'follow-ups.index');
$router->register('GET', '/follow-ups/create', 'FollowUpController', 'create', 'follow-ups.create');
$router->register('POST', '/follow-ups/store', 'FollowUpController', 'store', 'follow-ups.store');
$router->register('GET', '/follow-ups/show/:id', 'FollowUpController', 'show', 'follow-ups.show');
$router->register('GET', '/follow-ups/edit/:id', 'FollowUpController', 'edit', 'follow-ups.edit');
$router->register('POST', '/follow-ups/update/:id', 'FollowUpController', 'update', 'follow-ups.update');
$router->register('POST', '/follow-ups/delete/:id', 'FollowUpController', 'delete', 'follow-ups.delete');
$router->register('POST', '/follow-ups/update-status/:id', 'FollowUpController', 'updateStatus', 'follow-ups.update-status');

// Consultation Routes
$router->register('GET', '/consultations', 'ConsultationController', 'index', 'consultations.index');
$router->register('GET', '/consultations/create', 'ConsultationController', 'create', 'consultations.create.new');
$router->register('GET', '/consultations/create/:appointment_id', 'ConsultationController', 'create', 'consultations.create');
$router->register('POST', '/consultations/create', 'ConsultationController', 'store', 'consultations.create.post');
$router->register('POST', '/consultations/store', 'ConsultationController', 'store', 'consultations.store');
$router->register('GET', '/consultations/view/:id', 'ConsultationController', 'view', 'consultations.view');
$router->register('GET', '/consultations/edit/:id', 'ConsultationController', 'edit', 'consultations.edit');
$router->register('POST', '/consultations/edit/:id', 'ConsultationController', 'update', 'consultations.edit.post');
$router->register('POST', '/consultations/update/:id', 'ConsultationController', 'update', 'consultations.update');
$router->register('POST', '/consultations/update-field/:id', 'ConsultationController', 'updateField', 'consultations.update-field');
$router->register('POST', '/consultations/update-vitals/:id', 'ConsultationController', 'updateVitals', 'consultations.update-vitals');
$router->register('GET', '/consultations/print/:id', 'ConsultationController', 'print', 'consultations.print');

// Web Appointment Routes
$router->register('GET', '/appointments', 'AppointmentController', 'index', 'appointments.index');
$router->register('GET', '/appointments/create', 'AppointmentController', 'create', 'appointments.create');
$router->register('POST', '/appointments/store', 'AppointmentController', 'store', 'appointments.store');
$router->register('GET', '/appointments/view/:id', 'AppointmentController', 'view', 'appointments.view');
$router->register('GET', '/appointments/edit/:id', 'AppointmentController', 'edit', 'appointments.edit');
$router->register('POST', '/appointments/update/:id', 'AppointmentController', 'update', 'appointments.update');
$router->register('GET', '/appointments/cancel/:id', 'AppointmentController', 'cancel', 'appointments.cancel');
$router->register('GET', '/appointments/calendar', 'AppointmentController', 'calendar', 'appointments.calendar');
$router->register('GET', '/appointments/start/:id', 'AppointmentController', 'start', 'appointments.start');
$router->register('GET', '/appointments/check-in/:id', 'AppointmentController', 'checkIn', 'appointments.check-in');
$router->register('POST', '/appointments/:id/medical-history', 'AppointmentController', 'addMedicalHistory', 'appointments.medical-history');
$router->register('GET', '/appointments-redirect', 'AppointmentController', 'roleBasedRedirect', 'appointments.redirect');

// API Routes - Appointments (more specific routes first)
$router->register('GET', '/api/appointments/pending-count', 'api/AppointmentController', 'pendingCount', 'api.appointments.pending-count');
$router->register('GET', '/api/appointments/available-slots', 'api/AppointmentController', 'getAvailableSlots', 'api.appointments.available-slots');
$router->register('GET', '/api/appointments/stats', 'api/AppointmentController', 'stats', 'api.appointments.stats');
$router->register('GET', '/api/appointments', 'api/AppointmentController', 'index', 'api.appointments.index');
$router->register('POST', '/api/appointments', 'api/AppointmentController', 'create', 'api.appointments.create');
$router->register('GET', '/api/appointments/:id', 'api/AppointmentController', 'get', 'api.appointments.get');
$router->register('PUT', '/api/appointments/:id', 'api/AppointmentController', 'update', 'api.appointments.update');
$router->register('DELETE', '/api/appointments/:id', 'api/AppointmentController', 'cancel', 'api.appointments.cancel');

// API Routes - Consultations
$router->register('GET', '/api/consultations/pending-count', 'api/ConsultationController', 'pendingCount', 'api.consultations.pending-count');

// API Routes - Departments (NEW)
$router->register('GET', '/api/departments', 'api/DepartmentController', 'index', 'api.departments.index');
$router->register('POST', '/api/departments', 'api/DepartmentController', 'create', 'api.departments.create');
$router->register('GET', '/api/departments/:id', 'api/DepartmentController', 'show', 'api.departments.show');
$router->register('PUT', '/api/departments/:id', 'api/DepartmentController', 'update', 'api.departments.update');
$router->register('DELETE', '/api/departments/:id', 'api/DepartmentController', 'delete', 'api.departments.delete');

// API Routes - Invoices (NEW)
$router->register('GET', '/api/invoices', 'api/InvoiceController', 'index', 'api.invoices.index');
$router->register('POST', '/api/invoices', 'api/InvoiceController', 'create', 'api.invoices.create');
$router->register('GET', '/api/invoices/:id', 'api/InvoiceController', 'show', 'api.invoices.show');
$router->register('PUT', '/api/invoices/:id', 'api/InvoiceController', 'update', 'api.invoices.update');
$router->register('DELETE', '/api/invoices/:id', 'api/InvoiceController', 'delete', 'api.invoices.delete');

// API Routes - Payments (NEW)
$router->register('GET', '/api/payments', 'api/PaymentController', 'index', 'api.payments.index');
$router->register('POST', '/api/payments', 'api/PaymentController', 'create', 'api.payments.create');
$router->register('GET', '/api/payments/:id', 'api/PaymentController', 'show', 'api.payments.show');
$router->register('PUT', '/api/payments/:id', 'api/PaymentController', 'update', 'api.payments.update');
$router->register('DELETE', '/api/payments/:id', 'api/PaymentController', 'delete', 'api.payments.delete');
$router->register('PUT', '/api/payments/:id/status', 'api/PaymentController', 'updateStatus', 'api.payments.update-status');

// API Routes - Follow-ups (NEW)
$router->register('GET', '/api/follow-ups', 'api/FollowUpController', 'index', 'api.follow-ups.index');
$router->register('POST', '/api/follow-ups', 'api/FollowUpController', 'create', 'api.follow-ups.create');
$router->register('GET', '/api/follow-ups/:id', 'api/FollowUpController', 'show', 'api.follow-ups.show');
$router->register('PUT', '/api/follow-ups/:id', 'api/FollowUpController', 'update', 'api.follow-ups.update');
$router->register('DELETE', '/api/follow-ups/:id', 'api/FollowUpController', 'delete', 'api.follow-ups.delete');
$router->register('PUT', '/api/follow-ups/:id/status', 'api/FollowUpController', 'updateStatus', 'api.follow-ups.update-status');

// Prescription routes
$router->register('GET', '/prescriptions', 'PrescriptionController', 'index', 'prescriptions.index');
$router->register('GET', '/prescriptions/create', 'PrescriptionController', 'create', 'prescriptions.create');
$router->register('POST', '/prescriptions/store', 'PrescriptionController', 'store', 'prescriptions.store');
$router->register('GET', '/prescriptions/view/:id', 'PrescriptionController', 'view', 'prescriptions.view');
$router->register('GET', '/prescriptions/print/:id', 'PrescriptionController', 'print', 'prescriptions.print');
$router->register('GET', '/prescriptions/pending', 'PrescriptionController', 'pending', 'prescriptions.pending');
$router->register('GET', '/prescriptions/dispense/:id', 'PrescriptionController', 'dispense', 'prescriptions.dispense');
$router->register('GET', '/prescriptions/cancel/:id', 'PrescriptionController', 'cancel', 'prescriptions.cancel');

// Lab request routes
$router->register('GET', '/lab/requests', 'LabRequestController', 'index', 'lab.requests.index');
$router->register('GET', '/lab/requests/create', 'LabRequestController', 'create', 'lab.requests.create');
$router->register('GET', '/lab/request/new', 'LabRequestController', 'create', 'lab.requests.create.new');
$router->register('GET', '/lab-requests/new', 'LabRequestController', 'create', 'lab-requests.new');
$router->register('POST', '/lab/request/create', 'LabRequestController', 'store', 'lab.requests.store.alt');
$router->register('POST', '/lab/requests/store', 'LabRequestController', 'store', 'lab.requests.store');
$router->register('GET', '/lab/requests/view/:id', 'LabRequestController', 'view', 'lab.requests.view');
$router->register('GET', '/lab/requests/update-status/:id', 'LabRequestController', 'updateStatus', 'lab.requests.update-status');

// Lab test routes
$router->register('GET', '/lab/tests', 'LabTestController', 'index', 'lab.tests.index');
$router->register('GET', '/lab/tests/create', 'LabTestController', 'create', 'lab.tests.create');
$router->register('POST', '/lab/tests/store', 'LabTestController', 'store', 'lab.tests.store');
$router->register('GET', '/lab/tests/edit/:id', 'LabTestController', 'edit', 'lab.tests.edit');
$router->register('POST', '/lab/tests/update/:id', 'LabTestController', 'update', 'lab.tests.update');
$router->register('GET', '/lab/tests/view/:id', 'LabTestController', 'view', 'lab.tests.view');
$router->register('POST', '/lab/tests/delete/:id', 'LabTestController', 'delete', 'lab.tests.delete');
$router->register('GET', '/lab-tests/register-sample', 'LabTestController', 'registerSample', 'lab.tests.register-sample');
$router->register('POST', '/lab-tests/register-sample', 'LabTestController', 'storeRegisteredSample', 'lab.tests.register-sample.store');
$router->register('GET', '/lab-tests/pending', 'LabTestController', 'pending', 'lab.tests.pending');
$router->register('GET', '/lab-tests/completed', 'LabTestController', 'completed', 'lab.tests.completed');
$router->register('GET', '/lab-tests/manage', 'LabTestController', 'manage', 'lab.tests.manage');
$router->register('GET', '/lab-tests/update-result/:id', 'LabTestController', 'showUpdateResult', 'lab.tests.update-result.form');
$router->register('POST', '/lab-tests/update-result/:id', 'LabTestController', 'updateResult', 'lab.tests.update-result');
$router->register('GET', '/lab-tests/process/:id', 'LabTestController', 'showUpdateResult', 'lab.tests.process');
$router->register('GET', '/api/lab-tests/pending', 'LabTestController', 'getPendingTestsAjax', 'api.lab.tests.pending');
$router->register('GET', '/lab/samples/view/:id', 'LabTestController', 'viewSample', 'lab.samples.view');
$router->register('GET', '/lab/samples/results/:id', 'LabTestController', 'sampleResults', 'lab.samples.results');

// Vital Signs routes
$router->register('GET', '/vitals/create', 'VitalSignController', 'create', 'vitals.create');
$router->register('GET', '/vitals/create/:patient_id', 'VitalSignController', 'create', 'vitals.create.patient');
$router->register('GET', '/vitals/record', 'VitalSignController', 'create', 'vitals.record');
$router->register('GET', '/vitals/record/:patient_id', 'VitalSignController', 'create', 'vitals.record.patient');
$router->register('POST', '/vitals/store', 'VitalSignController', 'store', 'vitals.store');
$router->register('GET', '/vitals/view/:id', 'VitalSignController', 'view', 'vitals.view');
$router->register('GET', '/vitals/history/:patient_id', 'VitalSignController', 'history', 'vitals.history');

// Pharmacy routes
$router->register('GET', '/pharmacy/medicines', 'PharmacyController', 'medicines', 'pharmacy.medicines');
$router->register('GET', '/pharmacy/medicines/create', 'PharmacyController', 'createMedicine', 'pharmacy.medicines.create');
$router->register('POST', '/pharmacy/medicines/store', 'PharmacyController', 'storeMedicine', 'pharmacy.medicines.store');
$router->register('GET', '/pharmacy/medicines/show/:id', 'PharmacyController', 'showMedicine', 'pharmacy.medicines.show');
$router->register('GET', '/pharmacy/medicines/edit/:id', 'PharmacyController', 'editMedicine', 'pharmacy.medicines.edit');
$router->register('POST', '/pharmacy/medicines/update/:id', 'PharmacyController', 'updateMedicine', 'pharmacy.medicines.update');
$router->register('POST', '/pharmacy/medicines/toggle-status/:id', 'PharmacyController', 'toggleMedicineStatus', 'pharmacy.medicines.toggle-status');
$router->register('GET', '/pharmacy/inventory', 'PharmacyController', 'inventory', 'pharmacy.inventory');
$router->register('GET', '/pharmacy/inventory/add-stock', 'PharmacyController', 'addStock', 'pharmacy.inventory.add-stock');
$router->register('POST', '/pharmacy/inventory/add-stock', 'PharmacyController', 'storeStock', 'pharmacy.inventory.add-stock.store');
$router->register('GET', '/pharmacy/orders', 'PharmacyController', 'orders', 'pharmacy.orders');
$router->register('GET', '/medications/inventory', 'PharmacyController', 'inventory', 'medications.inventory');

// Add error routes for consistent redirection
$router->register('GET', '/modules/error/unauthorized', 'ErrorController', 'unauthorized', 'error.unauthorized');
$router->register('GET', '/unauthorized', 'ErrorController', 'unauthorized', 'error.unauthorized');

// Settings and Reports routes
$router->register('GET', '/settings', 'SettingsController', 'index', 'settings.index');
$router->register('GET', '/settings/users', 'SettingsController', 'users', 'settings.users');
$router->register('GET', '/settings/system', 'SettingsController', 'system', 'settings.system');
$router->register('GET', '/settings/database', 'SettingsController', 'database', 'settings.database');

$router->register('GET', '/reports', 'ReportsController', 'index', 'reports.index');
$router->register('GET', '/reports/appointments', 'ReportsController', 'appointments', 'reports.appointments');
$router->register('GET', '/reports/patients', 'ReportsController', 'patients', 'reports.patients');
$router->register('GET', '/reports/financial', 'ReportsController', 'financial', 'reports.financial');
$router->register('GET', '/reports/laboratory', 'ReportsController', 'laboratory', 'reports.laboratory');
$router->register('GET', '/reports/pharmacy', 'ReportsController', 'pharmacy', 'reports.pharmacy');

// Guest Appointment routes
$router->register('GET', '/guest-appointments', 'GuestAppointmentController', 'showBookingForm', 'guest-appointments.form');
$router->register('POST', '/guest-appointments/book', 'GuestAppointmentController', 'bookAppointment', 'guest-appointments.book');
$router->register('GET', '/guest-appointments/confirmation', 'GuestAppointmentController', 'confirmation', 'guest-appointments.confirmation');

// Lab Results routes
$router->register('GET', '/lab-results', 'LabResultsController', 'index', 'lab-results.index');
$router->register('GET', '/lab-results/view/:id', 'LabResultsController', 'view', 'lab-results.view');
$router->register('GET', '/lab-results/download/:id', 'LabResultsController', 'download', 'lab-results.download');

// Notifications routes
$router->register('GET', '/notifications', 'NotificationsController', 'index', 'notifications.index');

// API routes - Notifications
$router->register('GET', '/api/notifications', 'ApiNotificationsController', 'index', 'api.notifications.index');
$router->register('GET', '/api/notifications/count', 'ApiNotificationsController', 'count', 'api.notifications.count');
$router->register('PUT', '/api/notifications/:id/read', 'ApiNotificationsController', 'markAsRead', 'api.notifications.mark-read');
$router->register('PUT', '/api/notifications/mark-all-read', 'ApiNotificationsController', 'markAllAsRead', 'api.notifications.mark-all-read');

// Messages API routes
$router->register('GET', '/api/messages/inbox', 'CommunicationController', 'getInbox', 'api.messages.inbox');
$router->register('GET', '/api/messages/sent', 'CommunicationController', 'getSent', 'api.messages.sent');
$router->register('GET', '/api/messages/archived', 'CommunicationController', 'getArchived', 'api.messages.archived');
$router->register('GET', '/api/messages/search', 'CommunicationController', 'search', 'api.messages.search');
$router->register('GET', '/api/messages/users', 'CommunicationController', 'getUsers', 'api.messages.users');
$router->register('GET', '/api/messages/:id', 'CommunicationController', 'getMessage', 'api.messages.show');
$router->register('POST', '/api/messages/send', 'CommunicationController', 'sendMessage', 'api.messages.send');
$router->register('POST', '/api/messages/archive', 'CommunicationController', 'archiveMessage', 'api.messages.archive');
$router->register('POST', '/api/messages/delete', 'CommunicationController', 'deleteMessage', 'api.messages.delete');
$router->register('POST', '/api/messages/mark-read', 'CommunicationController', 'markMessageAsRead', 'api.messages.mark-read');

// Messages web routes
$router->register('GET', '/messages', 'MessagesController', 'index', 'messages.index');
$router->register('GET', '/messages/compose', 'MessagesController', 'compose', 'messages.compose');
$router->register('GET', '/messages/search', 'MessagesController', 'search', 'messages.search');
$router->register('GET', '/messages/:id', 'MessagesController', 'show', 'messages.show');
$router->register('POST', '/messages/send', 'MessagesController', 'send', 'messages.send');
$router->register('POST', '/messages/archive', 'MessagesController', 'archive', 'messages.archive');
$router->register('POST', '/messages/delete', 'MessagesController', 'delete', 'messages.delete');
$router->register('POST', '/messages/mark-read', 'MessagesController', 'markRead', 'messages.mark-read');

// API routes
$router->register('GET', '/api/doctors', 'DoctorsController', 'index', 'api.doctors.index');
$router->register('GET', '/api/doctors/:id', 'DoctorsController', 'show', 'api.doctors.show');
$router->register('GET', '/api/doctors/specialization/:specialization', 'DoctorsController', 'bySpecialization', 'api.doctors.by-specialization');

// Validation API routes
$router->register('POST', '/api/validate-email', 'ValidationController', 'validateEmail', 'api.validate.email');
// Username validation for live AJAX checks
$router->register('POST', '/api/validate-username', 'ValidationController', 'validateUsername', 'api.validate.username');
$router->register('POST', '/api/validate-appointment', 'ValidationController', 'validateAppointment', 'api.validate.appointment');
$router->register('GET', '/api/available-slots', 'ValidationController', 'getAvailableSlots', 'api.available.slots');
$router->register('GET', '/api/available-doctors', 'ValidationController', 'getAvailableDoctors', 'api.available.doctors');

// Add component routes for AJAX loading
$router->register('GET', '/components/:component', 'ComponentsController', 'handleRequest', 'components.load');
$router->register('POST', '/components/:component', 'ComponentsController', 'handleRequest', 'components.load.post');

// Set 404 handler
$router->setNotFoundCallback(function() {
    http_response_code(404);
    include __DIR__ . '/includes/views/error.php';
});

// Dispatch the request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
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
