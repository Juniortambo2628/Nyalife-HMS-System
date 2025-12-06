<?php

/**
 * Nyalife HMS - User Controller
 *
 * Controller for managing users.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/UserModel.php';

class UserController extends WebController
{
    protected \UserModel $userModel;

    /** @var array */
    protected $allowedRoles = [];

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->pageTitle = 'User Management - Nyalife HMS';
    }

    /**
     * Display all users
     */
    public function index(): void
    {
        $users = $this->userModel->getAllUsers();
        $this->renderView('users/index', [
            'users' => $users
        ]);
    }

    /**
     * Display user creation form
     */
    public function create(): void
    {
        $roles = $this->userModel->getAllRoles();
        $this->renderView('users/create', [
            'roles' => $roles
        ]);
    }

    /**
     * Store a new user
     */
    public function store(): void
    {
        // Debug: Log the request method and form data
        error_log("UserController::store - Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("UserController::store - POST data: " . print_r($_POST, true));

        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/create');
            return;
        }

        // Get form data directly
        $formData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'role_id' => $_POST['role_id'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ];

        // Debug: Log the processed form data
        error_log("UserController::store - Form data: " . print_r($formData, true));

        // Validate form data
        $errors = $this->validateUserData($formData);

        if ($errors !== []) {
            // Debug: Log validation errors
            error_log("UserController::store - Validation errors: " . print_r($errors, true));

            // Store errors in session
            SessionManager::set('form_errors', $errors);
            SessionManager::set('form_data', $formData);

            // Redirect back to creation form
            $this->redirect('/users/create');
            return;
        }

        try {
            // Debug: Check database connection
            $dbConnection = $this->userModel->getDbConnection();
            error_log("UserController::store - Database connection: " . ($dbConnection ? "Valid" : "Invalid"));

            // Handle doctor-specific data separately
            // Assuming role_id 2 is for doctors - role_id is always in formData, check value
            $isDoctorRole = (!empty($formData['role_id']) && $formData['role_id'] == 2);
            $doctorData = [];

            if ($isDoctorRole) {
                // Store doctor-specific data for later use, but don't include in user data
                if (isset($_POST['specialization']) && !empty($_POST['specialization'])) {
                    $doctorData['specialization'] = $_POST['specialization'];
                }

                if (isset($_POST['license_number']) && !empty($_POST['license_number'])) {
                    $doctorData['license_number'] = $_POST['license_number'];
                }
            }

            // Remove confirm_password before saving to database
            unset($formData['confirm_password']);

            // Create user
            $userId = $this->userModel->createUser($formData);

            // Debug: Log the result of user creation
            error_log("UserController::store - User creation result: " . ($userId ? "Success (ID: $userId)" : "Failed"));

            if ($userId) {
                // If this is a doctor, create staff entry
                if ($isDoctorRole) {
                    try {
                        // Generate employee ID
                        $employeeId = 'NYA-DOC-' . str_pad((string)$userId, 3, '0', STR_PAD_LEFT);

                        $staffData = [
                            'user_id' => $userId,
                            'employee_id' => $employeeId,
                            'department' => 'Medical',
                            'position' => 'Doctor',
                            'specialization' => $doctorData['specialization'] ?? 'General Medicine',
                            'qualification' => 'MBBS',
                            'join_date' => date('Y-m-d')
                        ];

                        // Create staff record
                        $sql = "INSERT INTO staff (user_id, employee_id, department, position, specialization, qualification, join_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $dbConnection->prepare($sql);
                        $stmt->bind_param(
                            'issssss',
                            $staffData['user_id'],
                            $staffData['employee_id'],
                            $staffData['department'],
                            $staffData['position'],
                            $staffData['specialization'],
                            $staffData['qualification'],
                            $staffData['join_date']
                        );

                        if ($stmt->execute()) {
                            error_log("UserController::store - Staff record created successfully for user ID: $userId");
                        } else {
                            error_log("UserController::store - Failed to create staff record: " . $stmt->error);
                        }
                        $stmt->close();
                    } catch (Exception $e) {
                        error_log("UserController::store - Staff creation error: " . $e->getMessage());
                    }
                }

                $this->setFlashMessage('success', 'User created successfully');
                $this->redirect('/users');
            } else {
                throw new Exception('Failed to create user - Database operation returned false');
            }
        } catch (Exception $e) {
            // Log error
            ErrorHandler::logSystemError($e, __METHOD__);
            error_log("UserController::store - Exception: " . $e->getMessage());

            // Set error message
            $this->setFlashMessage('error', 'Failed to create user: ' . $e->getMessage());

            // Redirect back to creation form
            $this->redirect('/users/create');
        }
    }

    /**
     * Display user details
     *
     * @param int $id User ID
     */
    public function view($id): void
    {
        $user = $this->userModel->getUserById($id);

        if (!$user) {
            $this->setFlashMessage('error', 'User not found');
            $this->redirect('/users');
            return;
        }

        $this->renderView('users/view', [
            'user' => $user
        ]);
    }

    /**
     * Display user edit form
     *
     * @param int $id User ID
     */
    public function edit($id): void
    {
        $user = $this->userModel->getUserById($id);

        if (!$user) {
            $this->setFlashMessage('error', 'User not found');
            $this->redirect('/users');
            return;
        }

        $roles = $this->userModel->getAllRoles();

        $this->renderView('users/edit', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Update a user
     *
     * @param int $id User ID
     */
    public function update($id): void
    {
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/edit/' . $id);
            return;
        }

        // Get user to update
        $user = $this->userModel->getUserById($id);

        if (!$user) {
            $this->setFlashMessage('error', 'User not found');
            $this->redirect('/users');
            return;
        }

        // Get form data
        $formData = $this->processFormData([
            'first_name', 'last_name', 'email', 'phone', 'role_id'
        ]);

        if ($formData === [] || $formData === false) {
            $this->redirect('/users/edit/' . $id);
            return;
        }

        // Check if password is being updated
        if (!empty($_POST['password'])) {
            $formData['password'] = $_POST['password'];
            $formData['confirm_password'] = $_POST['confirm_password'] ?? '';
        }

        // Validate form data
        $errors = $this->validateUserData($formData, $id);

        if ($errors !== []) {
            // Store errors in session
            SessionManager::set('form_errors', $errors);
            SessionManager::set('form_data', $formData);

            // Redirect back to edit form
            $this->redirect('/users/edit/' . $id);
            return;
        }

        try {
            // Update user
            $result = $this->userModel->updateUser($id, $formData);

            if ($result) {
                $this->setFlashMessage('success', 'User updated successfully');
                $this->redirect('/users/view/' . $id);
            } else {
                throw new Exception('Failed to update user');
            }
        } catch (Exception $e) {
            // Log error
            ErrorHandler::logSystemError($e, __METHOD__);

            // Set error message
            $this->setFlashMessage('error', 'Failed to update user: ' . $e->getMessage());

            // Redirect back to edit form
            $this->redirect('/users/edit/' . $id);
        }
    }

    /**
     * Delete a user
     *
     * @param int $id User ID
     */
    public function delete($id): void
    {
        // Get user to delete
        $user = $this->userModel->getUserById($id);

        if (!$user) {
            $this->setFlashMessage('error', 'User not found');
            $this->redirect('/users');
            return;
        }

        try {
            // Delete user
            $result = $this->userModel->deleteUser($id);

            if ($result) {
                $this->setFlashMessage('success', 'User deleted successfully');
                $this->redirect('/users');
            } else {
                throw new Exception('Failed to delete user');
            }
        } catch (Exception $e) {
            // Log error
            ErrorHandler::logSystemError($e, __METHOD__);

            // Set error message
            $this->setFlashMessage('error', 'Failed to delete user: ' . $e->getMessage());

            // Redirect back to users list
            $this->redirect('/users');
        }
    }

    /**
     * Display user profile
     */
    public function profile(): void
    {
        // Allow all authenticated users to access their profile
        $this->allowedRoles = [];

        $userId = SessionManager::get('user_id');
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            $this->setFlashMessage('error', 'Profile not found');
            $this->redirect('/dashboard');
            return;
        }

        $this->renderView('users/profile', [
            'user' => $user
        ]);
    }

    /**
     * Edit user profile
     */
    public function editProfile(): void
    {
        // Allow all authenticated users to access their profile
        $this->allowedRoles = [];

        $userId = SessionManager::get('user_id');
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            $this->setFlashMessage('error', 'Profile not found');
            $this->redirect('/dashboard');
            return;
        }

        $this->renderView('users/edit_profile', [
            'user' => $user,
            'pageTitle' => 'Edit Profile'
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(): void
    {
        // Allow all authenticated users to update their profile
        $this->allowedRoles = [];

        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile');
            return;
        }

        $userId = SessionManager::get('user_id');

        // Get form data
        $formData = $this->processFormData([
            'first_name', 'last_name', 'email', 'phone'
        ]);

        if ($formData === [] || $formData === false) {
            $this->redirect('/profile');
            return;
        }

        // Check if password is being updated
        if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
            // Verify current password
            $user = $this->userModel->getUserById($userId);
            if (!password_verify((string) $_POST['current_password'], (string) $user['password'])) {
                $this->setFlashMessage('error', 'Current password is incorrect');
                $this->redirect('/profile');
                return;
            }

            $formData['password'] = $_POST['new_password'];
            $formData['confirm_password'] = $_POST['confirm_new_password'] ?? '';
        }

        // Validate form data
        $errors = $this->validateUserData($formData, $userId);

        if ($errors !== []) {
            // Store errors in session
            SessionManager::set('form_errors', $errors);
            SessionManager::set('form_data', $formData);

            // Redirect back to profile
            $this->redirect('/profile');
            return;
        }

        try {
            // Update user
            $result = $this->userModel->updateUser($userId, $formData);

            if ($result) {
                // Update session data
                SessionManager::set('first_name', $formData['first_name']);
                SessionManager::set('last_name', $formData['last_name']);

                $this->setFlashMessage('success', 'Profile updated successfully');
                $this->redirect('/profile');
            } else {
                throw new Exception('Failed to update profile');
            }
        } catch (Exception $e) {
            // Log error
            ErrorHandler::logSystemError($e, __METHOD__);

            // Set error message
            $this->setFlashMessage('error', 'Failed to update profile: ' . $e->getMessage());

            // Redirect back to profile
            $this->redirect('/profile');
        }
    }

    /**
     * Show the change password form
     */
    public function changePasswordForm(): void
    {
        $userId = SessionManager::get('user_id');
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $this->renderView('users/change_password', [
            'user' => $user,
            'pageTitle' => 'Change Password'
        ]);
    }

    /**
     * Process password change
     */
    public function changePassword(): void
    {
        $userId = SessionManager::get('user_id');
        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile/change-password');
            return;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Get form data
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_new_password'] ?? '';

        // Validate current password
        if (!password_verify((string) $currentPassword, (string) $user['password'])) {
            $this->setFlashMessage('error', 'Current password is incorrect');
            $this->redirect('/profile/change-password');
            return;
        }

        // Validate new password
        if (strlen((string) $newPassword) < 8) {
            $this->setFlashMessage('error', 'New password must be at least 8 characters long');
            $this->redirect('/profile/change-password');
            return;
        }

        if (in_array(preg_match('/[A-Z]/', (string) $newPassword), [0, false], true)) {
            $this->setFlashMessage('error', 'New password must contain at least one uppercase letter');
            $this->redirect('/profile/change-password');
            return;
        }

        if (in_array(preg_match('/[a-z]/', (string) $newPassword), [0, false], true)) {
            $this->setFlashMessage('error', 'New password must contain at least one lowercase letter');
            $this->redirect('/profile/change-password');
            return;
        }

        if (in_array(preg_match('/\d/', (string) $newPassword), [0, false], true)) {
            $this->setFlashMessage('error', 'New password must contain at least one number');
            $this->redirect('/profile/change-password');
            return;
        }

        // Check if passwords match
        if ($newPassword !== $confirmPassword) {
            $this->setFlashMessage('error', 'New passwords do not match');
            $this->redirect('/profile/change-password');
            return;
        }

        try {
            // Update password
            $result = $this->userModel->updateUser($userId, [
                'password' => password_hash((string) $newPassword, PASSWORD_DEFAULT)
            ]);

            if ($result) {
                $this->setFlashMessage('success', 'Password changed successfully');
                $this->redirect('/profile');
            } else {
                throw new Exception('Failed to update password');
            }
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
            $this->setFlashMessage('error', 'Failed to change password: ' . $e->getMessage());
            $this->redirect('/profile/change-password');
        }
    }

    /**
     * Validate user data
     *
     * @param array $data User data
     * @param int|null $userId User ID for update (null for create)
     * @return array Array of errors (empty if no errors)
     */
    private function validateUserData($data, $userId = null): array
    {
        $errors = [];

        // Validate email
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        // Validate password for new users or password updates
        if (isset($data['password'])) {
            if (strlen((string) $data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters long';
            } elseif (in_array(preg_match('/[A-Z]/', (string) $data['password']), [0, false], true)) {
                $errors['password'] = 'Password must contain at least one uppercase letter';
            } elseif (in_array(preg_match('/[a-z]/', (string) $data['password']), [0, false], true)) {
                $errors['password'] = 'Password must contain at least one lowercase letter';
            } elseif (in_array(preg_match('/\d/', (string) $data['password']), [0, false], true)) {
                $errors['password'] = 'Password must contain at least one number';
            }

            // Confirm password
            if ($data['password'] !== ($data['confirm_password'] ?? '')) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
        }

        // Check for existing email (except for current user on update)
        if (!empty($data['email'])) {
            $exists = $this->userModel->emailExists($data['email'], $userId);
            if ($exists) {
                $errors['email'] = 'Email is already registered';
            }
        }

        // Check for existing username (except for current user on update)
        if (!empty($data['username'])) {
            $exists = $this->userModel->usernameExists($data['username'], $userId);
            if ($exists) {
                $errors['username'] = 'Username is already taken';
            }
        }

        return $errors;
    }
}
