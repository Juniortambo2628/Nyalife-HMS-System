<?php

/**
 * Nyalife HMS - Components Controller
 * This controller handles AJAX requests for loading components
 */

require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../functions.php';

class ComponentsController
{
    /**
     * Constructor - Initialize session if needed
     */
    public function __construct()
    {
        // Ensure session is started for flash messages
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Load a component
     * @param string $component The component name to load
     */
    public function load(string $component): void
    {
        // Check if it's an AJAX request
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(403);
            echo json_encode(['error' => 'Only AJAX requests are allowed']);
            return;
        }

        // Get the component path
        $componentPath = __DIR__ . '/../components/' . $component . '.php';

        // Check if the component exists
        if (!file_exists($componentPath)) {
            http_response_code(404);
            echo json_encode(['error' => 'Component not found']);
            return;
        }

        // Get POST data (if any)
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        // Extract data to variables
        extract($data);

        // Set required variables if not provided
        if (!isset($baseUrl)) {
            $baseUrl = getBaseUrl();
        }

        if (!isset($isLoggedIn)) {
            $isLoggedIn = isset($_SESSION['user_id']);
        }

        if (!isset($currentUser) && $isLoggedIn) {
            // Get current user info from session
            $currentUser = [
                'id' => $_SESSION['user_id'] ?? null,
                'firstName' => $_SESSION['first_name'] ?? '',
                'lastName' => $_SESSION['last_name'] ?? '',
                'role' => $_SESSION['role'] ?? '',
            ];
        }

        if (!isset($activeMenu)) {
            $activeMenu = '';
        }

        if (!isset($flashMessages)) {
            $flashMessages = $this->getFlashMessages();
        }

        // Define NYALIFE_INCLUDED to allow direct inclusion
        define('NYALIFE_INCLUDED', true);

        // Include the component
        ob_start();
        include $componentPath;
        $content = ob_get_clean();

        // Return the component HTML
        echo $content;
    }

    /**
     * Handle routing for component requests
     * @param array $params The URL parameters
     */
    public function handleRequest(array $params): void
    {
        // The first parameter should be the component name
        $component = $params[0] ?? null;

        if ($component) {
            $this->load($component);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Component name is required']);
        }
    }

    /**
     * Get and remove all flash messages
     *
     * @return array Flash messages
     */
    private function getFlashMessages(): array
    {
        $flashMessages = SessionManager::get('flash_messages', []);
        SessionManager::set('flash_messages', []);
        return $flashMessages;
    }
}
