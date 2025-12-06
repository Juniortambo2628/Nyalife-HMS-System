<?php
/**
 * Nyalife HMS - Web Router
 * 
 * Handles routing of web requests to appropriate controllers and actions.
 */

require_once __DIR__ . '/ErrorHandler.php';

class WebRouter {
    private $routes = [];
    private $namedRoutes = [];
    private $notFoundCallback;
    
    /**
     * Register a route
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $pattern URL pattern with optional parameters (e.g. /patients/:id)
     * @param string $controller Controller class name
     * @param string $action Controller method to call
     * @param string|null $name Optional route name for URL generation
     * @return WebRouter For method chaining
     */
    public function register($method, $pattern, $controller, $action, $name = null) {
        $route = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
        
        $this->routes[] = $route;
        
        // Store named route if provided
        if ($name !== null) {
            $this->namedRoutes[$name] = $pattern;
        }
        
        return $this;
    }
    
    /**
     * Set callback for 404 Not Found
     * 
     * @param callable $callback Function to call when no route is matched
     */
    public function setNotFoundCallback($callback) {
        $this->notFoundCallback = $callback;
    }
    
    /**
     * Convert URL pattern to regex pattern
     * 
     * @param string $pattern URL pattern with optional parameters
     * @return array Regex pattern and parameter names
     */
    private function patternToRegex($pattern) {
        $paramNames = [];
        $pattern = str_replace('/', '\/', $pattern);
        
        // Convert :param to regex capture group and store param name
        $pattern = preg_replace_callback('/\:([a-zA-Z0-9_]+)/', function($matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            return '([^\/]+)';
        }, $pattern);
        
        return ['/^' . $pattern . '$/', $paramNames];
    }
    
    /**
     * Dispatch a request to the appropriate controller
     * 
     * @param string $method HTTP method
     * @param string $uri Request URI
     * @return bool Whether a route was matched and handled
     */
    public function dispatch($method, $uri) {
        // Remove query string if any
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Trim trailing slash
        $uri = rtrim($uri, '/');
        
        // Add slash for root
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Debug output for routing troubleshooting
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            // Uncomment to debug routing issues
            // error_log("Routing request: {$method} {$uri}");
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }
            
            list($pattern, $paramNames) = $this->patternToRegex($route['pattern']);
            
            if (preg_match($pattern, $uri, $matches)) {
                try {
                    // Remove the full match
                    array_shift($matches);
                    
                    // Map matches to parameter names
                    $params = [];
                    foreach ($matches as $i => $match) {
                        if (isset($paramNames[$i])) {
                            $params[$paramNames[$i]] = $match;
                        } else {
                            $params[] = $match;
                        }
                    }
                    
                    // Load controller class
                    $controllerName = $route['controller'];
                    $controllerFile = __DIR__ . '/../controllers/web/' . $controllerName . '.php';
                    
                    if (file_exists($controllerFile)) {
                        require_once $controllerFile;
                    } else {
                        throw new Exception("Controller file not found: {$controllerFile}");
                    }
                    
                    // Check if controller class exists
                    if (!class_exists($controllerName)) {
                        throw new Exception("Controller class not found: {$controllerName}");
                    }
                    
                    // Instantiate controller
                    $controller = new $controllerName();
                    
                    // Check if action method exists
                    if (!method_exists($controller, $route['action'])) {
                        throw new Exception("Action method not found: {$route['action']}");
                    }
                    
                    // Call controller method with parameters
                    call_user_func_array([$controller, $route['action']], $params);
                    
                    return true;
                } catch (Exception $e) {
                    ErrorHandler::logSystemError($e, 'WebRouter::dispatch');
                    
                    http_response_code(500);
                    include __DIR__ . '/../views/error.php';
                    
                    return false;
                }
            }
        }
        
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // Log the 404 error for monitoring
        error_log("404 Not Found: {$requestUri}");
        
        if (is_callable($this->notFoundCallback)) {
            call_user_func($this->notFoundCallback);
        } else {
            $this->handleNotFound($requestUri);
        }
        
        return false;
    }
    
    private function handleNotFound($requestUri) {
        // Enhanced debugging for 404 errors
        $debugInfo = "";
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $debugInfo = "\nOriginal Request URI: {$_SERVER['REQUEST_URI']}";
            $debugInfo .= "\nProcessed URI: {$requestUri}";
            $debugInfo .= "\nScript Name: {$_SERVER['SCRIPT_NAME']}";
            $debugInfo .= "\nPHP_SELF: {$_SERVER['PHP_SELF']}";
            if (isset($_SERVER['PATH_INFO'])) {
                $debugInfo .= "\nPATH_INFO: {$_SERVER['PATH_INFO']}";
            }
            $debugInfo .= "\nAPP_PATH: " . (defined('APP_PATH') ? APP_PATH : 'Not defined');
        }
        
        // Log the 404 error with enhanced debug info
        error_log("404 Not Found: {$requestUri}{$debugInfo}");
        
        http_response_code(404);
        
        // Collect available routes for debugging
        $availableRoutes = [];
        foreach ($this->namedRoutes as $name => $pattern) {
            $availableRoutes[$name] = $pattern;
        }
        
        // Generate more helpful error message for developers
        $errorData = [
            'errorMessage' => "Page not found: {$requestUri}",
            'errorCode' => 404,
            'availableRoutes' => defined('DEBUG_MODE') && DEBUG_MODE ? $availableRoutes : null,
            'suggestedRoutes' => $this->findSimilarRoutes($requestUri),
            'debugInfo' => defined('DEBUG_MODE') && DEBUG_MODE ? $debugInfo : null
        ];
        
        // Extract error data so it's available in the error view
        extract($errorData);
        include __DIR__ . '/../views/error.php';
    }
    
    /**
     * Find similar routes to the requested URI to help with debugging
     * 
     * @param string $requestUri The requested URI
     * @return array List of similar routes
     */
    private function findSimilarRoutes($requestUri) {
        $suggestions = [];
        $requestParts = explode('/', trim($requestUri, '/'));
        
        foreach ($this->namedRoutes as $name => $pattern) {
            $patternParts = explode('/', trim($pattern, '/'));
            
            // Only consider patterns with similar structure
            if (count($requestParts) === count($patternParts)) {
                $similarity = 0;
                foreach ($requestParts as $i => $part) {
                    if (isset($patternParts[$i])) {
                        // If pattern part starts with ':', it's a parameter, count as similar
                        if (strpos($patternParts[$i], ':') === 0) {
                            $similarity++;
                        } elseif ($patternParts[$i] === $part) {
                            $similarity++;
                        }
                    }
                }
                
                // If more than half parts match, consider it a suggestion
                if ($similarity > count($requestParts) / 2) {
                    $suggestions[$name] = [
                        'pattern' => $pattern,
                        'similarity' => $similarity
                    ];
                }
            }
        }
        
        // Sort by similarity (highest first)
        uasort($suggestions, function($a, $b) {
            return $b['similarity'] - $a['similarity'];
        });
        
        return $suggestions;
    }
    
    /**
     * Generate a URL for a named route
     * 
     * @param string $name Route name
     * @param array $params Route parameters
     * @param array $queryParams Optional query parameters
     * @return string|null URL or null if route not found
     */
    public function generateUrl($name, $params = [], $queryParams = []) {
        if (!isset($this->namedRoutes[$name])) {
            return null;
        }
        
        $pattern = $this->namedRoutes[$name];
        
        // Replace route parameters
        foreach ($params as $paramName => $paramValue) {
            $pattern = str_replace(':' . $paramName, urlencode($paramValue), $pattern);
        }
        
        // Add query string if provided
        if (!empty($queryParams)) {
            $pattern .= '?' . http_build_query($queryParams);
        }
        
        // Add application path prefix
        if (defined('APP_PATH')) {
            $pattern = rtrim(APP_PATH, '/') . $pattern;
        }
        
        return $pattern;
    }
}
