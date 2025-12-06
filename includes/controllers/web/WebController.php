<?php

/**
 * Nyalife HMS - Base Web Controller
 *
 * Base controller for web pages.
 */

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/SessionManager.php';

abstract class WebController extends BaseController
{
    protected \Auth $auth;

    /** @var string */
    protected $pageTitle;

    /** @var bool */
    protected $requiresLogin = true;

    /** @var array */
    protected $allowedRoles = [];

    /** @var array */
    protected $data = []; // Added to fix dynamic property deprecation warning

    /** @var int|null */
    protected $userId; // Add userId property

    /** @var array */
    protected $params = []; // Add params property for route parameters

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->auth = Auth::getInstance();

        // Initialize userId from session
        $this->userId = SessionManager::get('user_id');

        // Check authentication if required
        if ($this->requiresLogin) {
            $this->auth->requireLogin();

            // Check role restrictions if any
            if ($this->allowedRoles !== []) {
                $this->auth->requireRole($this->allowedRoles);
            }
        }

        // Initialize any flash messages
        $this->initFlashMessages();
    }

    /**
     * Run the controller action
     *
     * @param string $action Action name
     * @param array $params Action parameters
     */
    public function run($action, $params = []): void
    {
        if (!method_exists($this, $action)) {
            $this->showError('Page not found', 404);
            return;
        }

        // Store parameters for access via $this->params if needed
        $this->params = $params;

        call_user_func_array([$this, $action], $params);
    }

    /**
     * Set a flash message to be displayed on the next page load
     *
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     */
    protected function setFlashMessage($type, $message): void
    {
        $flashMessages = SessionManager::get('flash_messages', []);
        $flashMessages[] = [
            'type' => $type,
            'message' => $message
        ];
        SessionManager::set('flash_messages', $flashMessages);
    }

    /**
     * Get and remove all flash messages
     *
     * @return array Flash messages
     */
    protected function getFlashMessages(): array
    {
        $flashMessages = SessionManager::get('flash_messages', []);
        SessionManager::set('flash_messages', []);
        return $flashMessages;
    }

    /**
     * Initialize flash messages for the view
     */
    private function initFlashMessages(): void
    {
        $this->data['flashMessages'] = $this->getFlashMessages();
    }

    /**
     * Render a view with layout
     *
     * @param string $view View file name (without .php extension)
     * @param array $data Data to pass to the view
     * @param string $layout Layout file name (without .php extension)
     */
    protected function renderView(string $view, $data = [], string $layout = 'default'): void
    {
        // Merge data with defaults
        $data = array_merge($this->getDefaultData(), $data);

        // Capture view content
        ob_start();
        extract($data);
        include __DIR__ . '/../../views/' . $view . '.php';
        $content = ob_get_clean();

        // Render layout with content
        extract(array_merge($data, ['content' => $content]));
        include __DIR__ . '/../../views/layouts/' . $layout . '.php';
    }

    /**
     * Show an error page
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param Exception|null $exception The exception that caused the error
     */
    protected function showError($message, $code = 500, $exception = null): void
    {
        // Log the error if an exception was provided
        if ($exception instanceof Exception) {
            ErrorHandler::logSystemError($exception, static::class . '::' . debug_backtrace()[1]['function']);
        }

        // Set HTTP status code
        http_response_code($code);

        // Render error view
        $this->renderView('error', [
            'errorMessage' => $message,
            'errorCode' => $code,
            'errorDetails' => $exception ? $exception->getMessage() : null,
            'showDetails' => $this->isDebugMode()
        ]);
        exit;
    }

    /**
     * Handle an exception and show appropriate error page or message
     *
     * @param Exception $e The exception to handle
     * @param bool $ajaxAware Whether to return JSON for AJAX requests
     */
    protected function handleException($e, $ajaxAware = true): void
    {
        // Log the exception
        ErrorHandler::logSystemError($e, static::class . '::' . debug_backtrace()[1]['function']);

        // Determine if this is an AJAX request
        $isAjax = $ajaxAware && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Get appropriate status code
        // $e is always an Exception (typed parameter), so getCode() is always available
        $code = $e->getCode() ?: 500; // Use 500 if getCode() returns 0 or falsy value
        // Ensure code is valid HTTP status code (400-599 range)
        if (!is_int($code) || $code < 100 || $code > 599) {
            $code = 500;
        }
        // Code is already validated above, ensure it's in error range
        if ($code < 400) {
            $code = 500; // Default to 500 if not a valid HTTP error code
        }

        if ($isAjax) {
            // Return JSON error for AJAX requests
            header('Content-Type: application/json');
            http_response_code($code);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $code,
                'details' => $this->isDebugMode() ? $e->getTraceAsString() : null
            ]);
            exit;
        }
        // Show error page for regular requests
        $this->showError($e->getMessage(), $code, $e);
    }

    /**
     * Handle an error for form processing and set appropriate flash message
     *
     * @param Exception $e The exception to handle
     * @param string $redirectUrl URL to redirect to
     */
    protected function handleFormError(\Exception $e, $redirectUrl): void
    {
        // Log the error
        ErrorHandler::logSystemError($e, static::class . '::' . debug_backtrace()[1]['function']);

        // Set flash message
        $this->setFlashMessage('error', 'An error occurred: ' . $e->getMessage());

        // Redirect to the specified URL
        $this->redirect($redirectUrl);
    }

    /**
     * Check if we're in debug mode
     *
     * @return bool True if in debug mode
     */
    protected function isDebugMode(): bool
    {
        return defined('DEBUG_MODE') && DEBUG_MODE;
    }

    /**
     * Get default data for all views
     *
     * @return array Default data
     */
    protected function getDefaultData(): array
    {
        return [
            'pageTitle' => $this->pageTitle,
            'currentUser' => [
                'id' => SessionManager::get('user_id'),
                'username' => SessionManager::get('username'),
                'role' => SessionManager::get('role'),
                'firstName' => SessionManager::get('first_name'),
                'lastName' => SessionManager::get('last_name')
            ],
            'isLoggedIn' => $this->auth->isLoggedIn(),
            'baseUrl' => $this->getBaseUrl(),
            'currentYear' => date('Y'),
            'activeMenu' => $this->determineActiveMenu()
        ];
    }

    /**
     * Determine the active menu based on the current URL
     *
     * @return string The active menu identifier
     */
    protected function determineActiveMenu(): string
    {
        // Get the current URL path
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';

        // Remove query string if present
        $currentUrl = preg_replace('/\?.*$/', '', (string) $currentUrl);

        // Remove base URL/APP_PATH if present
        // APP_PATH is always defined in constants.php (either '/Nyalife-HMS-System' or '')
        if (defined('APP_PATH')) {
            $appPath = APP_PATH;
            // In development, APP_PATH is '/Nyalife-HMS-System', in production it's ''
            // Only replace if non-empty
            $currentUrl = str_replace($appPath, '', $currentUrl);
        }

        // Ensure URL starts with a slash
        if (!empty($currentUrl) && $currentUrl[0] !== '/') {
            $currentUrl = '/' . $currentUrl;
        }
        // Determine active menu based on URL path
        if (preg_match('#^/dashboard#', $currentUrl)) {
            return 'dashboard';
        }
        if (preg_match('#^/patients#', $currentUrl)) {
            return 'patients';
        }
        if (preg_match('#^/appointments#', $currentUrl)) {
            return 'appointments';
        }
        if (preg_match('#^/consultations#', $currentUrl)) {
            return 'consultations';
        }
        if (preg_match('#^/lab#', $currentUrl)) {
            return 'lab';
        }
        if (preg_match('#^/pharmacy#', $currentUrl)) {
            return 'pharmacy';
        }
        if (preg_match('#^/prescriptions#', $currentUrl)) {
            return 'prescriptions';
        }
        if (preg_match('#^/users#', $currentUrl) || preg_match('#^/settings#', $currentUrl) || preg_match('#^/reports#', $currentUrl)) {
            return 'admin';
        }

        // Determine active menu based on URL path
        if (preg_match('#^/profile#', $currentUrl)) {
            return 'profile';
        }

        // Default to empty string if no match
        return '';
    }

    /**
     * Redirect to a URL
     *
     * @param string $url URL to redirect to
     */
    protected function redirect($url): void
    {
        // If URL is relative (doesn't start with http:// or https://), prepend the base URL
        if (in_array(preg_match('/^https?:\/\//i', $url), [0, false], true)) {
            // Remove leading slash if present for consistency
            $url = ltrim($url, '/');
            $baseUrl = rtrim($this->getBaseUrl(), '/');
            $url = $baseUrl . '/' . $url;
        }

        // Debug information if needed
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Redirecting to: {$url}");
        }

        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect to a route within the application
     *
     * @param string $routeName Named route to redirect to
     * @param array $routeParams Route parameters for placeholder values
     * @param array $queryParams Query parameters to append to the URL
     */
    protected function redirectToRoute($routeName, $routeParams = [], $queryParams = []): void
    {
        // Get base URL
        $baseUrl = $this->getBaseUrl();

        // Debug logging
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("WebController::redirectToRoute - Route: {$routeName}");
            error_log("WebController::redirectToRoute - Params: " . json_encode($routeParams));
            error_log("WebController::redirectToRoute - Base URL: {$baseUrl}");
        }

        // Special handling for dashboard routes
        if ($routeName === 'dashboard.default') {
            $role = SessionManager::get('role', 'patient');

            // Use APP_PATH constant to ensure consistency
            $url = rtrim($baseUrl, '/') . "/dashboard/" . $role;

            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("WebController::redirectToRoute - Redirecting to dashboard: {$url}");
            }

            header("Location: {$url}");
            exit;
        }

        // For all other routes, use the router singleton instance
        $router = WebRouter::getInstance();

        // Generate the route URL
        $routeUrl = $router->generateUrl($routeName, $routeParams);

        if ($routeUrl === null) {
            // If route not found in router, log error and redirect to dashboard
            error_log("WebController::redirectToRoute - Error: Route '{$routeName}' not found");

            // Fallback to dashboard
            $this->setFlashMessage('error', 'Navigation error: Page not found');
            $this->redirectToRoute('dashboard.default');
            exit;
        }

        // Add query parameters if any
        if (!empty($queryParams)) {
            $routeUrl .= (str_contains((string) $routeUrl, '?') ? '&' : '?') . http_build_query($queryParams);
        }

        // Ensure the URL is absolute but avoid duplicate base URLs
        if (in_array(preg_match('/^https?:\/\//i', (string) $routeUrl), [0, false], true)) {
            // Check if the route URL already starts with the base URL
            $url = str_starts_with((string) $routeUrl, $baseUrl) ? $routeUrl : rtrim($baseUrl, '/') . '/' . ltrim((string) $routeUrl, '/');
        } else {
            $url = $routeUrl;
        }

        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("WebController::redirectToRoute - Final URL: {$url}");
        }

        // Redirect to the final URL
        header("Location: {$url}");
        exit;
    }

    /**
     * Process POST form data
     *
     * @param array $requiredFields Required field names
     * @return array|false Form data or false if validation failed
     */
    protected function processFormData($requiredFields = []): array|false
    {
        // Note: Cannot add return type : array|false as PHP < 8.0 doesn't support union types
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("WebController::processFormData - Not a POST request");
            return false;
        }

        $data = $_POST;
        error_log("WebController::processFormData - Raw POST data: " . print_r($data, true));

        // Validate required fields
        if (!empty($requiredFields)) {
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                    $missingFields[] = $field;
                }
            }

            if ($missingFields !== []) {
                error_log("WebController::processFormData - Missing fields: " . implode(', ', $missingFields));
                $this->setFlashMessage('error', 'The following fields are required: ' . implode(', ', $missingFields));
                return false;
            }
        }

        error_log("WebController::processFormData - Returning processed data: " . print_r($data, true));
        return $data;
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
     * Handle errors for operations
     *
     * @param string $message Error message
     * @param Exception|null $e Exception if available
     */
    protected function handleError($message, ?Exception $e = null): void
    {
        if ($e instanceof \Exception) {
            ErrorHandler::logSystemError($e, __METHOD__);
        }

        $this->setFlashMessage('error', $message);
        $this->redirect('/dashboard');
    }

    /**
     * Redirect with error message
     *
     * @param string $message Error message
     * @param string $url URL to redirect to
     */
    protected function redirectWithError($message, $url): void
    {
        $this->setFlashMessage('error', $message);
        $this->redirect($url);
    }

    /**
     * Redirect with success message
     *
     * @param string $message Success message
     * @param string $url URL to redirect to
     */
    protected function redirectWithSuccess($message, $url): void
    {
        $this->setFlashMessage('success', $message);
        $this->redirect($url);
    }

    /**
     * Send a JSON response
     *
     * @param array $data Data to send as JSON
     * @param int $statusCode HTTP status code
     */
    protected function jsonResponse($data, $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
