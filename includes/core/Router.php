<?php

/**
 * Nyalife HMS - Router
 *
 * Handles routing of requests to appropriate controllers and actions.
 */

require_once __DIR__ . '/ErrorHandler.php';

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];

    /**
     * Register a route
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $path URL path pattern with optional regex captures
     * @param string $controller Controller class name
     * @param string $action Controller method to call
     * @param string|null $name Optional route name for URL generation
     * @return Router For method chaining
     */
    public function register(string $method, string $path, string $controller, string $action, ?string $name = null): Router
    {
        $route = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];

        $this->routes[] = $route;

        // Store named route if provided
        if ($name !== null) {
            $this->namedRoutes[$name] = $path;
        }

        return $this;
    }

    /**
     * Dispatch a request to the appropriate controller
     *
     * @param string $method HTTP method
     * @param string $path URL path
     * @return bool Whether a route was matched and handled
     */
    public function dispatch(string $method, string $path): bool
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = '~^' . $route['path'] . '$~';

            if (preg_match($pattern, $path, $matches)) {
                try {
                    $controllerName = $route['controller'];
                    $actionName = $route['action'];

                    // Check if controller class exists
                    if (!class_exists($controllerName)) {
                        throw new Exception("Controller class not found: {$controllerName}");
                    }

                    // Instantiate controller
                    $controller = new $controllerName();

                    // Check if action method exists
                    if (!method_exists($controller, $actionName)) {
                        throw new Exception("Action method not found: {$actionName}");
                    }

                    // Remove the full match from $matches
                    array_shift($matches);

                    // Call controller method with captured parameters
                    call_user_func_array([$controller, $actionName], $matches);

                    return true;
                } catch (Exception $e) {
                    ErrorHandler::logSystemError($e, 'Router::dispatch');

                    // Send error response for API requests
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Internal server error'
                    ]);

                    return false;
                }
            }
        }

        // No route matched
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found'
        ]);

        return false;
    }

    /**
     * Generate a URL for a named route
     *
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string|null URL or null if route not found
     */
    public function generateUrl(string $name, array $params = []): ?string
    {
        if (!isset($this->namedRoutes[$name])) {
            return null;
        }

        $path = $this->namedRoutes[$name];

        // Replace route parameters
        foreach ($params as $value) {
            // Convert named placeholders to actual values
            // Example: 'user/(\d+)' with params['id' => 123] becomes 'user/123'
            $path = preg_replace('/\(\\d\+\)/', (string) $value, (string) $path, 1);
        }

        return $path;
    }

    /**
     * Handle API exceptions in a standardized way
     *
     * @param Exception $e The exception to handle
     * @param bool $showDetails Whether to include exception details in the response
     */
    public static function handleException(Exception $e, bool $showDetails = false): void
    {
        // Log the exception
        ErrorHandler::logSystemError($e, 'Router::handleException');

        // Prepare error response
        $response = [
            'success' => false,
            'message' => 'Internal server error'
        ];

        // Add debug details if requested
        if ($showDetails) {
            $response['debug'] = [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }

        // Send error response
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode($response);
    }
}
