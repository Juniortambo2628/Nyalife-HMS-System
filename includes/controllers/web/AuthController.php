<?php
/**
 * Nyalife HMS - Auth Controller
 * 
 * Controller for authentication-related actions.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../utils/json_response.php';
require_once __DIR__ . '/../../utils/ajax_handler.php';

// Set up error handling for AJAX requests
setupAjaxErrorHandling();

class AuthController extends WebController {
    // Override to allow public access without login for most actions
    protected $requiresLogin = false;
    
    /**
     * Initialize the controller
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Show login form
     * 
     * @return void
     */
    public function showLogin() {
        // Redirect if already logged in
        if (Auth::getInstance()->isLoggedIn()) {
            $this->redirect('/includes/views/dashboard');
            return;
        }
        
        // Get any auth messages from session
        $data = [];
        if (SessionManager::has('auth_message')) {
            $data['message'] = SessionManager::get('auth_message');
            $data['messageType'] = SessionManager::get('auth_message_type', 'info');
            
            // Clear the messages so they don't appear again on refresh
            SessionManager::remove('auth_message');
            SessionManager::remove('auth_message_type');
        }
        
        // Include the auth-utils.js script
        $data['scripts'] = ['common/auth-utils.js'];
        
        // Add meta tag for base URL
        $data['headExtras'] = '<meta name="base-url" content="' . $this->getBaseUrl() . '">';
        
        $this->pageTitle = 'Login - Nyalife HMS';
        $this->renderView('auth/login', $data);
    }
    
    /**
     * Process login form
     * 
     * @return void
     */
    public function processLogin() {
        // Set error handling to ensure clean JSON responses for AJAX
        try {
            // Detect AJAX request - check both headers and content type
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                    (!empty($_SERVER['HTTP_ACCEPT']) && 
                     strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false) ||
                    (!empty($_SERVER['CONTENT_TYPE']) && 
                     strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') !== false);

            // For AJAX requests, always send JSON
            if ($isAjax) {
                // Set content type header early to ensure JSON response
                header('Content-Type: application/json');
                // Ensure no caching
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
            }

            // Redirect if already logged in
            if (Auth::getInstance()->isLoggedIn()) {
                if ($isAjax) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Already logged in', 
                        'redirect' => 'dashboard'
                    ]);
                    exit;
                } else {
                    $this->redirectToRoute('dashboard.default');
                    return;
                }
            }
            
            // Check if form was submitted
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                if ($isAjax) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid request method'
                    ]);
                    exit;
                } else {
                    $this->redirect('/login');
                    return;
                }
            }
            
            // Get form data
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']) && $_POST['remember'] == '1';
            
            // Attempt to authenticate
            $auth = Auth::getInstance();
            if ($auth->authenticate($username, $password, $remember)) {
                // Get user role and dashboard URL
                $userRole = strtolower($auth->getUserRole());
        
                    // FIX 1: Use consistent route name
                    $dashboardUrl = $this->generateUrl('dashboard.default');
                    
                    // FIX 2: Clear session messages immediately
                    SessionManager::remove('auth_message');
                    SessionManager::remove('auth_message_type');

                    if ($isAjax) {
                        // FIX 3: Return consistent JSON structure
                        echo json_encode([
                            'success' => true,
                            'message' => 'Login successful',
                            'redirect' => $dashboardUrl // Absolute URL
                        ]);
                        exit;
                    } else {
                        header('Location: ' . $dashboardUrl);
                        exit;
                    }
                }
                 else {
                // Set error message
                $errorMessage = 'Invalid username or password. Please try again.';
                SessionManager::set('auth_message', $errorMessage);
                SessionManager::set('auth_message_type', 'danger');
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'message' => $errorMessage,
                        'error' => 'authentication_failed'
                    ]);
                    exit;
                } else {
                    // Redirect back to login
                    $this->redirectToRoute('login');
                }
            }
            } catch (Exception $e) {
            // Log the error
            ErrorHandler::logSystemError($e, __METHOD__);
            
            // If this is an AJAX request, return JSON error
            if (isset($isAjax) && $isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Server error: ' . $e->getMessage()
                ]);
                exit;
            } else {
                // For non-AJAX, set error message and redirect
                SessionManager::set('auth_message', 'An error occurred during login. Please try again.');
                SessionManager::set('auth_message_type', 'danger');
                $this->redirectToRoute('login');
            };
        }
    }
    
    /**
     * Logout user
     * 
     * @return void
     */
    public function logout() {
        // Logout
        Auth::getInstance()->logout();
        
        // Set success message
        SessionManager::set('auth_message', 'You have been successfully logged out.');
        SessionManager::set('auth_message_type', 'success');
        
        // Redirect to home
        $this->redirect('/');
    }
    
    /**
     * Show registration form
     * 
     * @return void
     */
    public function showRegister() {
        // Redirect if already logged in
        if (Auth::getInstance()->isLoggedIn()) {
            $this->redirect('/includes/views/dashboard/');
            return;
        }
        
        $this->pageTitle = 'Register - Nyalife HMS';
        $this->renderView('auth/register');
    }
    
    /**
     * Process registration form
     * 
     * @return void
     */
    public function processRegister() {
        // Detect AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
                
        // Redirect if already logged in
        if (Auth::getInstance()->isLoggedIn()) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'You are already logged in.'
                ]);
                exit;
            } else {
                $this->redirect('/includes/views/dashboard/');
                return;
            }
        }
        
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request method'
                ]);
                exit;
            } else {
                $this->redirect('/register');
                return;
            }
        }
        
        // Get form data
        $userData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'role' => $_POST['role'] ?? 'patient',
            'terms' => isset($_POST['terms']) && $_POST['terms'] == 'on'
        ];
        
        // Validate form data
        $errors = $this->validateRegistrationData($userData);
        
        if (!empty($errors)) {
            // Handle errors
            if ($isAjax) {
                header('Content-Type: application/json');
                $errorMessage = !empty($errors['general']) ? $errors['general'] : 'Please check your input and try again.';
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $errors
                ]);
                exit;
            } else {
                // Store errors in session
                SessionManager::set('registration_errors', $errors);
                SessionManager::set('registration_data', $userData);
                
                // Redirect back to registration form
                $this->redirect('/register');
                return;
            }
        }
        
        try {
            // Create user
            $userModel = new UserModel();
            $userId = $userModel->createUser($userData);
            
            if ($userId) {
                // Create patient profile if role is patient
                if ($userData['role'] === 'patient') {
                    $patientData = [
                        'user_id' => $userId,
                        'blood_group' => $_POST['blood_group'] ?? null,
                        'height' => $_POST['height'] ?? null,
                        'weight' => $_POST['weight'] ?? null,
                        'address' => $_POST['address'] ?? null
                    ];
                    
                    $patientModel = new PatientModel();
                    $patientModel->createPatient($patientData);
                }
                
                // Set success message
                SessionManager::set('auth_message', 'Registration successful! You can now login.');
                SessionManager::set('auth_message_type', 'success');
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! You can now login.',
                        'redirect' => '/login'
                    ]);
                    exit;
                } else {
                    // Redirect to login
                    $this->redirect('/login');
                }
            } else {
                throw new Exception('Failed to create user');
            }
        } catch (Exception $e) {
            // Log error
            ErrorHandler::logSystemError($e, __METHOD__);
            
            $errorMessage = 'Registration failed. Please try again later.';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]);
                exit;
            } else {
                // Set error message
                SessionManager::set('registration_errors', ['general' => $errorMessage]);
                SessionManager::set('registration_data', $userData);
                
                // Redirect back to registration form
                $this->redirect('/register');
            }
        }
    }
    
    /**
     * Validate registration data
     * 
     * @param array $data Registration data
     * @return array Array of errors (empty if no errors)
     */
    private function validateRegistrationData($data) {
        $errors = [];
        
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'username', 'password', 'confirm_password', 'gender', 'date_of_birth'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Validate email
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        // Validate password
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters long';
            } elseif (!preg_match('/[A-Z]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one uppercase letter';
            } elseif (!preg_match('/[a-z]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one lowercase letter';
            } elseif (!preg_match('/[0-9]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one number';
            }
        }
        
        // Validate confirm password
        if (!empty($data['password']) && !empty($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        // Validate terms
        if (!$data['terms']) {
            $errors['terms'] = 'You must agree to the terms and conditions';
        }
        
        // Check for existing username or email
        $userModel = new UserModel();
        if (!empty($data['username']) && $userModel->usernameExists($data['username'])) {
            $errors['username'] = 'Username is already taken';
        }
        
        if (!empty($data['email']) && $userModel->emailExists($data['email'])) {
            $errors['email'] = 'Email is already registered';
        }
        
        return $errors;
    }
}
