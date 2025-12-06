<?php
/**
 * Nyalife HMS - Base Web Controller
 * 
 * Base controller for web pages.
 */

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/SessionManager.php';

abstract class WebController extends BaseController {
    protected $auth;
    protected $pageTitle;
    protected $requiresLogin = true;
    protected $allowedRoles = [];
    protected $data = []; // Added to fix dynamic property deprecation warning
    
    /**
     * Initialize the controller
     */
    public function __construct() {
        parent::__construct();
        $this->auth = Auth::getInstance();
        
        // Check authentication if required
        if ($this->requiresLogin) {
            $this->auth->requireLogin();
            
            // Check role restrictions if any
            if (!empty($this->allowedRoles)) {
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
     * @return void
     */
    public function run($action, $params = []) {
        if (!method_exists($this, $action)) {
            $this->showError('Page not found', 404);
            return;
        }
        
        call_user_func_array([$this, $action], $params);
    }
    
    /**
     * Set a flash message to be displayed on the next page load
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     * @return void
     */
    protected function setFlashMessage($type, $message) {
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
    protected function getFlashMessages() {
        $flashMessages = SessionManager::get('flash_messages', []);
        SessionManager::set('flash_messages', []);
        return $flashMessages;
    }
    
    /**
     * Initialize flash messages for the view
     * 
     * @return void
     */
    private function initFlashMessages() {
        $this->data['flashMessages'] = $this->getFlashMessages();
    }
    
    /**
     * Render a view with layout
     * 
     * @param string $view View file name (without .php extension)
     * @param array $data Data to pass to the view
     * @param string $layout Layout file name (without .php extension)
     * @return void
     */
    protected function renderView($view, $data = [], $layout = 'default') {
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
     * @return void
     */
    protected function showError($message, $code = 500, $exception = null) {
        // Log the error if an exception was provided
        if ($exception instanceof Exception) {
            ErrorHandler::logSystemError($exception, get_class($this) . '::' . debug_backtrace()[1]['function']);
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
     * @return void
     */
    protected function handleException($e, $ajaxAware = true) {
        // Log the exception
        ErrorHandler::logSystemError($e, get_class($this) . '::' . debug_backtrace()[1]['function']);
        
        // Determine if this is an AJAX request
        $isAjax = $ajaxAware && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        // Get appropriate status code
        $code = ($e instanceof HttpException) ? $e->getCode() : 500;
        if ($code < 400 || $code > 599) {
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
        } else {
            // Show error page for regular requests
            $this->showError($e->getMessage(), $code, $e);
        }
    }
    
    /**
     * Handle an error for form processing and set appropriate flash message
     * 
     * @param Exception $e The exception to handle
     * @param string $redirectUrl URL to redirect to
     * @return void
     */
    protected function handleFormError($e, $redirectUrl) {
        // Log the error
        ErrorHandler::logSystemError($e, get_class($this) . '::' . debug_backtrace()[1]['function']);
        
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
    protected function isDebugMode() {
        return defined('DEBUG_MODE') && DEBUG_MODE === true;
    }
    
    /**
     * Get default data for all views
     * 
     * @return array Default data
     */
    protected function getDefaultData() {
        $data = [
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
            'currentYear' => date('Y')
        ];
        
        return $data;
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url URL to redirect to
     * @return void
     */
    protected function redirect($url) {
        // If URL is relative (doesn't start with http:// or https://), prepend the base URL
        if (!preg_match('/^https?:\/\//i', $url)) {
            // Remove leading slash if present for consistency
            $url = ltrim($url, '/');
            $baseUrl = rtrim($this->getBaseUrl(), '/');
            $url = $baseUrl . '/' . $url;
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
     * @return void
     */
    protected function redirectToRoute($routeName, $routeParams = [], $queryParams = []) {
        // Get base URL
        $baseUrl = $this->getBaseUrl();
        
        // Special handling for dashboard routes
        if ($routeName === 'dashboard.default') {
            $role = SessionManager::get('role', 'patient');
            $url = rtrim($baseUrl, '/') . "/dashboard/" . $role;
            
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }
            
            header("Location: {$url}");
            exit;
        }
        
        // For all other routes, use the router
        $router = new WebRouter();
        
        // Generate the route URL
        $routeUrl = $router->generateUrl($routeName, $routeParams);
        
        if ($routeUrl === null) {
            // If route not found in router, construct URL manually
            $url = rtrim($baseUrl, '/') . '/' . ltrim($routeName, '/');
            
            // Replace route parameters
            foreach ($routeParams as $key => $value) {
                $url = str_replace(':' . $key, $value, $url);
            }
            
            // Add query parameters if any
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }
        } else {
            // Use the generated URL from router
            $url = $routeUrl;
            
            // Add query parameters if any
            if (!empty($queryParams)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($queryParams);
            }
        }
        
        // Ensure the URL is absolute
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
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
    protected function processFormData($requiredFields = []) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        
        $data = $_POST;
        
        // Validate required fields
        if (!empty($requiredFields)) {
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || trim($data[$field]) === '') {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                $this->setFlashMessage('error', 'The following fields are required: ' . implode(', ', $missingFields));
                return false;
            }
        }
        
        return $data;
    }
}
