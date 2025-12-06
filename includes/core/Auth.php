<?php
/**
 * Nyalife HMS - Authentication Class
 * 
 * Centralized authentication and authorization handling.
 */

require_once __DIR__ . '/DatabaseManager.php';
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/ErrorHandler.php';
require_once __DIR__ . '/../models/UserModel.php';

class Auth {
    private static $instance = null;
    private $db;
    private $userModel;
    
    /**
     * Private constructor to prevent direct creation
     */
    private function __construct() {
        $this->db = DatabaseManager::getInstance()->getConnection();
        $this->userModel = new UserModel();
    }
    
    /**
     * Get the singleton instance
     * 
     * @return Auth The singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if logged in
     */
    public function isLoggedIn() {
        return SessionManager::has('user_id');
    }
    
    /**
     * Check if user has a specific role
     * 
     * @param string $role Role to check
     * @return bool True if user has role
     */
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return SessionManager::get('role') === $role;
    }
    
    /**
     * Check if user has any of the specified roles
     * 
     * @param array $roles Roles to check
     * @return bool True if user has any role
     */
    public function hasAnyRole($roles) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return in_array(SessionManager::get('role'), (array)$roles);
    }
    
    /**
     * Authenticate user
     * 
     * @param string $username Username or email
     * @param string $password Password
     * @param bool $remember Whether to remember the user
     * @return bool True if authentication successful
     */
    public function authenticate($username, $password, $remember = false) {
        try {
            // Authenticate user using UserModel
            $user = $this->userModel->authenticate($username, $password);
            
            if (!$user) {
                return false;
            }
            
            // Set session variables
            SessionManager::set('user_id', $user['user_id']);
            SessionManager::set('username', $user['username']);
            SessionManager::set('role', $user['role_name']);
            SessionManager::set('first_name', $user['first_name']);
            SessionManager::set('last_name', $user['last_name']);
            
            // Handle remember me
            if ($remember) {
                $token = $this->userModel->createRememberToken($user['user_id']);
                if ($token) {
                    setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
                }
            }
            
            return true;
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Check remember token
     * 
     * @return bool True if token valid and user logged in
     */
    public function checkRememberToken() {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        try {
            $token = $_COOKIE['remember_token'];
            $user = $this->userModel->checkRememberToken($token);
            
            if (!$user) {
                return false;
            }
            
            // Set session variables
            SessionManager::set('user_id', $user['user_id']);
            SessionManager::set('username', $user['username']);
            SessionManager::set('role', $user['role_name']);
            SessionManager::set('first_name', $user['first_name']);
            SessionManager::set('last_name', $user['last_name']);
            
            return true;
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Logout user
     * 
     * @param bool $redirect Whether to redirect after logout
     * @return void
     */
    public function logout($redirect = true) {
        try {
            // Remove remember token if exists
            if (isset($_COOKIE['remember_token'])) {
                $token = $_COOKIE['remember_token'];
                $this->userModel->deleteRememberToken($token);
                setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            }
            
            // Clear session
            SessionManager::destroy();
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
        }
    }
    
    /**
     * Redirect user based on role
     * 
     * @return void
     */
    public function redirectByRole() {
        if (!$this->isLoggedIn()) {
            $this->redirectToLogin();
            return;
        }
        
        $role = $this->getUserRole();
        $baseUrl = rtrim($this->getBaseUrl(), '/');
        $url = "{$baseUrl}/dashboard/" . strtolower($role);
        
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Redirect to login page with optional redirect back
     * 
     * @param string $redirectUrl URL to redirect to after login
     * @return void
     */
    protected function redirectToLogin($redirectUrl = '') {
        $loginUrl = rtrim($this->getBaseUrl(), '/') . '/login';
        
        if (!empty($redirectUrl)) {
            $loginUrl .= '?redirect=' . urlencode($redirectUrl);
        }
        
        header("Location: {$loginUrl}");
        exit;
    }
    
    /**
     * Require user to be logged in
     * 
     * @return void Redirects to login page if not logged in
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $baseUrl = $this->getBaseUrl();
            // Set a message informing user they need to log in
            SessionManager::set('auth_message', 'You must be logged in to access this page.');
            SessionManager::set('auth_message_type', 'warning');
            // Redirect to the login route defined in the router
            header('Location: ' . $baseUrl . '/index.php/login');
            exit;
        }
    }
    
    /**
     * Require user to have a specific role
     * 
     * @param string|array $role Role or roles required
     * @return void Redirects to dashboard or unauthorized page if not authorized
     */
    public function requireRole($role) {
        $this->requireLogin();
        
        if (!$this->hasAnyRole((array)$role)) {
            $baseUrl = $this->getBaseUrl();
            header('Location: ' . $baseUrl . '/modules/error/unauthorized.php');
            exit;
        }
    }
    
    /**
     * Get the current user ID
     * 
     * @return int|null User ID or null if not logged in
     */
    public function getUserId() {
        return SessionManager::get('user_id');
    }
    

    
    /**
     * Get base URL for the application
     * 
     * @return string Base URL including application directory
     */
    private function getBaseUrl() {
        // Use the global getBaseUrl function for consistency across the application
        return getBaseUrl();
    }
}
