<?php

/**
 * Nyalife HMS - Base Controller
 *
 * Abstract base class for all controllers.
 */

require_once __DIR__ . '/../core/DatabaseManager.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/ErrorHandler.php';

abstract class BaseController
{
    protected \mysqli $db;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        $this->db = DatabaseManager::getInstance()->getConnection();
    }

    /**
     * Render a view with data
     *
     * @param string $view View file name
     * @param array $data Data to pass to the view
     */
    protected function render(string $view, $data = []): void
    {
        // Extract data to make it available in view scope
        extract($data);

        // Include the view file
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$view}.php");
        }

        include $viewPath;
    }

    /**
     * Get the base URL for the application
     *
     * @return string Base URL
     */
    protected function getBaseUrl(): string
    {
        // Use the global getBaseUrl function for consistency across the application
        return getBaseUrl();
    }

    /**
     * Redirect to a URL
     *
     * @param string $url URL to redirect to
     */
    protected function redirect($url): void
    {
        header("Location: {$url}");
        exit; // exit() is required after redirect to prevent further execution
    }

    /**
     * Get a parameter from $_GET or $_POST
     *
     * @param string $name Parameter name
     * @param mixed $default Default value
     * @return mixed Parameter value
     */
    protected function getParam($name, mixed $default = null): mixed
    {
        return $_GET[$name] ?? $_POST[$name] ?? $default;
    }

    /**
     * Check if user is logged in
     *
     * @return bool True if logged in
     */
    protected function isLoggedIn(): bool
    {
        return SessionManager::has('user_id');
    }

    /**
     * Get current user ID
     *
     * @return int|null User ID or null if not logged in
     */
    protected function getCurrentUserId(): ?int
    {
        return SessionManager::get('user_id');
    }

    /**
     * Get current user role
     *
     * @return string|null User role or null if not logged in
     */
    protected function getCurrentUserRole(): ?string
    {
        return SessionManager::get('role');
    }

    /**
     * Check if current user has a specific role
     *
     * @param string|array $role Role or roles to check
     * @return bool True if user has role
     */
    protected function hasRole($role): bool
    {
        $userRole = $this->getCurrentUserRole();
        if ($userRole === null || $userRole === '' || $userRole === '0') {
            return false;
        }

        if (is_array($role)) {
            return in_array($userRole, $role);
        }

        return $userRole === $role;
    }
}
