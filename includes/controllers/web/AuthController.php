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
if (function_exists('setupAjaxErrorHandling')) {
    setupAjaxErrorHandling();
}

class AuthController extends WebController
{
    // Override to allow public access without login for most actions
    /** @var bool */
    protected $requiresLogin = false;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show login form
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if (Auth::getInstance()->isLoggedIn()) {
            $this->redirectToRoute('dashboard.default');
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

        // No extra scripts needed; auth-utils already included globally.

        // Add meta tag for base URL
        $data['headExtras'] = '<meta name="base-url" content="' . $this->getBaseUrl() . '">';

        $this->pageTitle = 'Login - Nyalife HMS';
        $this->renderView('auth/login', $data);
    }

    /**
     * Process login form
     */
    public function processLogin(): void
    {
        // Set error handling to ensure clean JSON responses for AJAX
        try {
            // Detect AJAX request - check both headers and content type
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                     strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                    (!empty($_SERVER['HTTP_ACCEPT']) &&
                     str_contains(strtolower((string) $_SERVER['HTTP_ACCEPT']), 'application/json')) ||
                    (!empty($_SERVER['CONTENT_TYPE']) &&
                     str_contains(strtolower((string) $_SERVER['CONTENT_TYPE']), 'application/json'));

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
                        'redirect' => $this->getBaseUrl() . '/dashboard'
                    ]);
                    exit;
                }
                $this->redirectToRoute('dashboard.default');
                return;
            }

            // Check if form was submitted
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                if ($isAjax) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid request method'
                    ]);
                    exit;
                }
                $this->redirect('/login');
                return;
            }

            // Get form data
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']) && $_POST['remember'] == '1';

            // Attempt to authenticate
            if ($this->auth->authenticate($username, $password, $remember)) {
                // Get user role and dashboard URL
                $userRole = strtolower((string) $this->auth->getUserRole());

                // Clear session messages immediately
                SessionManager::remove('auth_message');
                SessionManager::remove('auth_message_type');

                if ($isAjax) {
                    // Return consistent JSON structure with absolute URL
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'redirect' => $this->getBaseUrl() . '/dashboard/' . $userRole
                    ]);
                    exit;
                }
                // Redirect to role-specific dashboard
                $this->redirect('/dashboard/' . $userRole);
            } else {
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
                }
                // Redirect back to login
                $this->redirectToRoute('login');
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
            }
            // For non-AJAX, set error message and redirect
            SessionManager::set('auth_message', 'An error occurred during login. Please try again.');
            SessionManager::set('auth_message_type', 'danger');
            $this->redirectToRoute('login');
        }
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        // Logout
        $this->auth->logout();

        // Set success message
        SessionManager::set('auth_message', 'You have been successfully logged out.');
        SessionManager::set('auth_message_type', 'success');

        // Redirect to home
        $this->redirect('/');
    }

    /**
     * Show registration form
     */
    public function showRegister(): void
    {
        // Redirect if already logged in
        if ($this->auth->isLoggedIn()) {
            $this->redirectToRoute('dashboard.default');
            return;
        }

        $this->pageTitle = 'Register - Nyalife HMS';
        $this->renderView('auth/register');
    }

    /**
     * Process registration form
     */
    public function processRegister(): void
    {
        // Detect AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Redirect if already logged in
        if ($this->auth->isLoggedIn()) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'You are already logged in.'
                ]);
                exit;
            }
            $this->redirectToRoute('dashboard.default');
            return;
        }

        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request method'
                ]);
                exit;
            }
            $this->redirect('/register');
            return;
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
            'terms' => isset($_POST['terms']) && $_POST['terms'] == 'on',
            'address' => $_POST['address'] ?? null // Store address in users table
        ];

        // Map role name to role_id expected by UserModel::createUser
        // Default patient role id is 6 (used elsewhere in the app). If you have a roles table
        // and want dynamic mapping, replace this with a lookup.
        // Note: 'role' is always in userData array from POST (line 274), 'role_id' is added below
        $roleName = $userData['role'];
        $isPatient = (strtolower((string) $roleName) === 'patient');
        // role_id is not in initial userData array structure (line 264-277), add it based on role name
        // Since role_id is never in the initial array, we always need to add it
        // Default to patient role (6) for all cases
        $userData['role_id'] = 6;

        // Remove non-database fields before passing to UserModel
        // 'role' is a string name, we use 'role_id' instead
        // 'terms' is just for validation, not stored in users table
        unset($userData['role']);
        unset($userData['terms']);

        // Validate form data (before removing fields for validation check)
        $validationData = $userData;
        $validationData['terms'] = isset($_POST['terms']) && $_POST['terms'] == 'on';
        $validationData['role'] = $_POST['role'] ?? 'patient';
        $errors = $this->validateRegistrationData($validationData);

        if ($errors !== []) {
            // Handle errors
            if ($isAjax) {
                header('Content-Type: application/json');
                $errorMessage = empty($errors['general']) ? 'Please check your input and try again.' : $errors['general'];
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $errors
                ]);
                exit;
            }
            // Store errors in session
            SessionManager::set('registration_errors', $errors);
            SessionManager::set('registration_data', $userData);
            // Redirect back to registration form
            $this->redirect('/register');
            return;
        }

        try {
            // Create user
            $userModel = new UserModel();
            $userId = $userModel->createUser($userData);

            if ($userId) {
                // Create patient profile if role is patient
                if ($isPatient) {
                    $patientData = [
                        'user_id' => $userId,
                        'blood_group' => $_POST['blood_group'] ?? null,
                        'height' => $_POST['height'] ?? null,
                        'weight' => $_POST['weight'] ?? null
                        // Note: 'address' is stored in users table, not patients table
                    ];

                    $patientModel = new PatientModel();
                    $patientModel->create($patientData);
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
                }
                // Redirect to login
                $this->redirect('/login');
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
            }
            // Set error message
            SessionManager::set('registration_errors', ['general' => $errorMessage]);
            SessionManager::set('registration_data', $userData);
            // Redirect back to registration form
            $this->redirect('/register');
        }
    }

    /**
     * Validate registration data
     *
     * @param array $data Registration data
     * @return array Array of errors (empty if no errors)
     */
    private function validateRegistrationData(array $data): array
    {
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
            if (strlen((string) $data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters long';
            } elseif (in_array(preg_match('/[A-Z]/', (string) $data['password']), [0, false], true)) {
                $errors['password'] = 'Password must contain at least one uppercase letter';
            } elseif (in_array(preg_match('/[a-z]/', (string) $data['password']), [0, false], true)) {
                $errors['password'] = 'Password must contain at least one lowercase letter';
            } elseif (in_array(preg_match('/\d/', (string) $data['password']), [0, false], true)) {
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

    /**
     * Show patient registration form
     */
    public function showPatientRegister(): void
    {
        // Redirect if already logged in
        if (Auth::getInstance()->isLoggedIn()) {
            $this->redirectToRoute('dashboard.default');
            return;
        }

        $this->pageTitle = 'Patient Registration - Nyalife HMS';
        $this->renderView('auth/register-patient');
    }

    /**
     * Process patient registration
     */
    public function processPatientRegister(): void
    {
        try {
            // Validate required fields
            $requiredFields = [
                'first_name', 'last_name', 'email', 'phone',
                'date_of_birth', 'gender', 'username', 'password', 'confirm_password'
            ];

            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Validate email
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }

            // Validate password
            if (strlen((string) $_POST['password']) < 6) {
                throw new Exception('Password must be at least 6 characters long');
            }

            if ($_POST['password'] !== $_POST['confirm_password']) {
                throw new Exception('Passwords do not match');
            }

            // Check if email already exists
            require_once __DIR__ . '/../../models/UserModel.php';
            $userModel = new UserModel();

            if ($userModel->emailExists($_POST['email'])) {
                throw new Exception('Email address already registered');
            }

            if ($userModel->usernameExists($_POST['username'])) {
                throw new Exception('Username already taken');
            }

            // Create user data
            $userData = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password' => password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
                'role_id' => 6, // Patient role
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'phone' => $_POST['phone'],
                'date_of_birth' => $_POST['date_of_birth'],
                'gender' => $_POST['gender'],
                'address' => $_POST['address'] ?? ''
            ];

            // Create patient data (only patient-specific fields)
            $patientData = [
                'emergency_contact' => $_POST['emergency_contact'] ?? '',
                'allergies' => $_POST['allergies'] ?? ''
                // Note: medical_history is not a column in patients table
            ];

            // Create patient
            require_once __DIR__ . '/../../models/PatientModel.php';
            $patientModel = new PatientModel();

            $patientId = $patientModel->createPatient($userData, $patientData);

            if (!$patientId) {
                throw new Exception('Failed to create patient account');
            }

            // Auto-login the new patient
            $auth = Auth::getInstance();
            $auth->authenticate($_POST['username'], $_POST['password']);

            // Set success message
            SessionManager::set('auth_message', 'Patient account created successfully! Welcome to Nyalife HMS.');
            SessionManager::set('auth_message_type', 'success');

            // Redirect to dashboard
            $this->redirectToRoute('dashboard.default');
        } catch (Exception $e) {
            SessionManager::set('auth_message', $e->getMessage());
            SessionManager::set('auth_message_type', 'error');
            $this->redirectToRoute('register.patient');
        }
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword(): void
    {
        // Redirect if already logged in
        if (Auth::getInstance()->isLoggedIn()) {
            $this->redirectToRoute('dashboard.default');
            return;
        }

        $this->pageTitle = 'Forgot Password - Nyalife HMS';
        $this->renderView('auth/forgot-password');
    }

    /**
     * Process forgot password request
     */
    public function processForgotPassword(): void
    {
        try {
            if (empty($_POST['email'])) {
                throw new Exception('Email address is required');
            }

            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }

            require_once __DIR__ . '/../../models/UserModel.php';
            $userModel = new UserModel();

            $user = $userModel->getByEmail($_POST['email']);

            if (!$user) {
                // Don't reveal if email exists or not for security
                SessionManager::set('auth_message', 'If the email address exists in our system, you will receive a password reset link.');
                SessionManager::set('auth_message_type', 'info');
                $this->redirectToRoute('login');
                return;
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token in database
            $userModel->storePasswordResetToken($user['user_id'], $token, $expires);

            // Send reset email (implement email functionality)
            $this->sendPasswordResetEmail($_POST['email'], $token);

            SessionManager::set('auth_message', 'Password reset link has been sent to your email address.');
            SessionManager::set('auth_message_type', 'success');
            $this->redirectToRoute('login');
        } catch (Exception $e) {
            SessionManager::set('auth_message', $e->getMessage());
            SessionManager::set('auth_message_type', 'error');
            $this->redirectToRoute('forgot-password');
        }
    }

    /**
     * Show reset password form
     *
     * @param string $token Reset token
     */
    public function showResetPassword($token): void
    {
        // Redirect if already logged in
        if (Auth::getInstance()->isLoggedIn()) {
            $this->redirectToRoute('dashboard.default');
            return;
        }

        require_once __DIR__ . '/../../models/UserModel.php';
        $userModel = new UserModel();

        $resetData = $userModel->getPasswordResetToken($token);

        if (!$resetData || strtotime((string) $resetData['expires_at']) < time()) {
            SessionManager::set('auth_message', 'Invalid or expired reset token.');
            SessionManager::set('auth_message_type', 'error');
            $this->redirectToRoute('login');
            return;
        }

        $this->pageTitle = 'Reset Password - Nyalife HMS';
        $this->renderView('auth/reset-password', ['token' => $token]);
    }

    /**
     * Process password reset
     *
     * @param string $token Reset token
     */
    public function processResetPassword($token): void
    {
        try {
            if (empty($_POST['password']) || empty($_POST['confirm_password'])) {
                throw new Exception('Password and confirmation are required');
            }

            if (strlen((string) $_POST['password']) < 6) {
                throw new Exception('Password must be at least 6 characters long');
            }

            if ($_POST['password'] !== $_POST['confirm_password']) {
                throw new Exception('Passwords do not match');
            }

            require_once __DIR__ . '/../../models/UserModel.php';
            $userModel = new UserModel();

            $resetData = $userModel->getPasswordResetToken($token);

            if (!$resetData || strtotime((string) $resetData['expires_at']) < time()) {
                throw new Exception('Invalid or expired reset token');
            }

            // Update password
            $userModel->changePassword($resetData['user_id'], $_POST['password']);

            // Delete reset token
            $userModel->deletePasswordResetToken($token);

            SessionManager::set('auth_message', 'Password has been reset successfully. You can now login with your new password.');
            SessionManager::set('auth_message_type', 'success');
            $this->redirectToRoute('login');
        } catch (Exception $e) {
            SessionManager::set('auth_message', $e->getMessage());
            SessionManager::set('auth_message_type', 'error');
            $this->redirectToRoute('reset-password', ['token' => $token]);
        }
    }

    /**
     * Send password reset email
     *
     * @param string $email Email address
     * @param string $token Reset token
     */
    private function sendPasswordResetEmail($email, string $token): void
    {
        // Implementation for sending password reset email
        // This would integrate with your email system
        $resetUrl = $this->getBaseUrl() . '/reset-password/' . $token;

        // For now, just log the reset URL
        error_log("Password reset URL for $email: $resetUrl");

        // TODO: Implement actual email sending
        // You can use PHPMailer, SwiftMailer, or your preferred email library
    }
}
