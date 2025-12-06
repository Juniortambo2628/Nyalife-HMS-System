<?php
/**
 * Nyalife HMS - User Controller
 * 
 * Controller for managing users.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/UserModel.php';

class UserController extends WebController {
    protected $userModel;
    protected $allowedRoles = ['admin'];
    
    /**
     * Initialize the controller
     */
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->pageTitle = 'User Management - Nyalife HMS';
    }
    
    /**
     * Display all users
     * 
     * @return void
     */
    public function index() {
        $users = $this->userModel->getAllUsers();
        $this->renderView('users/index', [
            'users' => $users
        ]);
    }
    
    /**
     * Display user creation form
     * 
     * @return void
     */
    public function create() {
        $roles = $this->userModel->getAllRoles();
        $this->renderView('users/create', [
            'roles' => $roles
        ]);
    }
    
    /**
     * Store a new user
     * 
     * @return void
     */
    public function store() {
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/create');
            return;
        }
        
        // Get form data
        $formData = $this->processFormData([
            'first_name', 'last_name', 'email', 'phone', 
            'username', 'password', 'confirm_password', 'role_id'
        ]);
        
        if (!$formData) {
            $this->redirect('/users/create');
            return;
        }
        
        // Validate form data
        $errors = $this->validateUserData($formData);
        
        if (!empty($errors)) {
            // Store errors in session
            SessionManager::set('form_errors', $errors);
            SessionManager::set('form_data', $formData);
            
            // Redirect back to creation form
            $this->redirect('/users/create');
            return;
        }
        
        try {
            // Create user
            $userId = $this->userModel->createUser($formData);
            
            if ($userId) {
                $this->setFlashMessage('success', 'User created successfully');
                $this->redirect('/users');
            } else {
                throw new Exception('Failed to create user');
            }
        } catch (Exception $e) {
            // Log error
            ErrorHandler::logSystemError($e, __METHOD__);
            
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
     * @return void
     */
    public function view($id) {
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
     * @return void
     */
    public function edit($id) {
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
     * @return void
     */
    public function update($id) {
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
        
        if (!$formData) {
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
        
        if (!empty($errors)) {
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
     * @return void
     */
    public function delete($id) {
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
     * 
     * @return void
     */
    public function profile() {
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
     * Update user profile
     * 
     * @return void
     */
    public function updateProfile() {
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
        
        if (!$formData) {
            $this->redirect('/profile');
            return;
        }
        
        // Check if password is being updated
        if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
            // Verify current password
            $user = $this->userModel->getUserById($userId);
            if (!password_verify($_POST['current_password'], $user['password'])) {
                $this->setFlashMessage('error', 'Current password is incorrect');
                $this->redirect('/profile');
                return;
            }
            
            $formData['password'] = $_POST['new_password'];
            $formData['confirm_password'] = $_POST['confirm_new_password'] ?? '';
        }
        
        // Validate form data
        $errors = $this->validateUserData($formData, $userId);
        
        if (!empty($errors)) {
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
     * Validate user data
     * 
     * @param array $data User data
     * @param int|null $userId User ID for update (null for create)
     * @return array Array of errors (empty if no errors)
     */
    private function validateUserData($data, $userId = null) {
        $errors = [];
        
        // Validate email
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        // Validate password for new users or password updates
        if (isset($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters long';
            } elseif (!preg_match('/[A-Z]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one uppercase letter';
            } elseif (!preg_match('/[a-z]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one lowercase letter';
            } elseif (!preg_match('/[0-9]/', $data['password'])) {
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
