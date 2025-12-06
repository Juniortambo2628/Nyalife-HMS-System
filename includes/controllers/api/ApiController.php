<?php

/**
 * Nyalife HMS - Base API Controller
 *
 * This class provides standardized methods for handling API requests,
 * authentication, and response formatting.
 */

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../core/DatabaseManager.php';
require_once __DIR__ . '/../../core/SessionManager.php';
require_once __DIR__ . '/../../core/ErrorHandler.php';

class ApiController extends BaseController
{
    protected \mysqli $conn;

    /** @var bool */
    protected $isAuthenticated = false;

    /** @var int|null */
    protected $userId;

    /** @var string|null */
    protected $userRole;

    /**
     * Initialize the API controller
     *
     * @param bool $requireAuth Whether authentication is required for this endpoint
     * @param array $allowedRoles Roles allowed to access this endpoint (empty array = all roles)
     */
    public function __construct($requireAuth = true, $allowedRoles = [])
    {
        // Call parent constructor to initialize database connection
        parent::__construct();

        // Set content type to JSON
        header('Content-Type: application/json');

        // Store the database connection in conn property for backward compatibility
        $this->conn = $this->db;

        // Check authentication if required
        if ($requireAuth) {
            $this->checkAuthentication($allowedRoles);
        }
    }

    /**
     * Check if user is authenticated and has required roles
     *
     * @param array $allowedRoles Roles allowed to access this endpoint
     * @return bool True if authenticated and authorized
     */
    protected function checkAuthentication($allowedRoles = []): bool
    {
        // Use SessionManager to check if user is logged in
        SessionManager::ensureStarted();

        // Check if user is logged in
        if (!SessionManager::has('user_id')) {
            $this->sendError('Unauthorized access', 401);
            return false;
        }

        // Set authentication data
        $this->isAuthenticated = true;
        $this->userId = SessionManager::get('user_id');
        $this->userRole = SessionManager::get('role');

        // Check role restrictions if any
        if (!empty($allowedRoles) && !in_array($this->userRole, $allowedRoles)) {
            $this->sendError('You do not have permission to access this resource', 403);
            return false;
        }

        return true;
    }

    /**
     * Send success response
     *
     * @param mixed $data Data to include in response
     * @param int $status HTTP status code
     */
    protected function sendResponse(mixed $data, $status = 200): void
    {
        http_response_code($status);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit; // exit() is acceptable for API responses to prevent further output
    }

    /**
     * Send error response
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param array $errors Validation errors array
     * @param Exception|null $exception Exception object for debugging
     */
    protected function sendError(string $message, $status = 400, $errors = [], $exception = null): void
    {
        // Log API errors
        ErrorHandler::logApiError($message, $_SERVER['REQUEST_URI'] ?? 'unknown');

        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if ($exception && $this->isDebugMode()) {
            $response['debug'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        http_response_code($status);
        echo json_encode($response);
        exit; // exit() is acceptable for API responses to prevent further output
    }

    /**
     * Send error response (alias for sendError for backward compatibility)
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param array $errors Validation errors array
     * @param Exception|null $exception Exception object for debugging
     */
    protected function sendErrorResponse(string $message, $status = 400, $errors = [], $exception = null): void
    {
        $this->sendError($message, $status, $errors, $exception);
    }

    /**
     * Require authentication
     *
     * @param array $allowedRoles Roles allowed to access this endpoint
     * @return bool True if authenticated and authorized
     */
    protected function requireAuth($allowedRoles = []): bool
    {
        return $this->checkAuthentication($allowedRoles);
    }

    /**
     * Validate request method
     *
     * @param array $allowedMethods Array of allowed HTTP methods
     * @return bool True if method is allowed
     */
    protected function validateRequest($allowedMethods = ['GET']): bool
    {
        $currentMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($currentMethod, $allowedMethods)) {
            $this->sendError("Method not allowed. Allowed methods: " . implode(', ', $allowedMethods), 405);
            return false;
        }

        return true;
    }

    /**
     * Get integer parameter from request
     *
     * @param string $name Parameter name
     * @param string $method Request method (GET, POST)
     * @param int $default Default value if parameter not found
     * @return int Parameter value
     */
    protected function getIntParam($name, $method = 'GET', $default = 0): int
    {
        $method = strtoupper($method);
        if ($method === 'GET' && isset($_GET[$name])) {
            return intval($_GET[$name]);
        }

        if ($method === 'POST' && isset($_POST[$name])) {
            return intval($_POST[$name]);
        }

        return $default;
    }

    /**
     * Get string parameter from request
     *
     * @param string $name Parameter name
     * @param string $method Request method (GET, POST)
     * @param string $default Default value if parameter not found
     * @return string Parameter value
     */
    protected function getStringParam($name, $method = 'GET', $default = ''): string
    {
        $method = strtoupper($method);
        if ($method === 'GET' && isset($_GET[$name])) {
            return trim((string) $_GET[$name]);
        }

        if ($method === 'POST' && isset($_POST[$name])) {
            return trim((string) $_POST[$name]);
        }

        return $default;
    }

    /**
     * Get a float parameter from request
     *
     * @param string $name Parameter name
     * @param string $method Request method (GET or POST)
     * @param float $default Default value
     * @return float Parameter value
     */
    protected function getFloatParam($name, $method = 'GET', $default = 0.0): float
    {
        $method = strtoupper($method);
        if ($method === 'GET' && isset($_GET[$name])) {
            return (float)$_GET[$name];
        }

        if ($method === 'POST' && isset($_POST[$name])) {
            return (float)$_POST[$name];
        }

        return $default;
    }

    /**
     * Get all request data as array
     *
     * @param string $method Request method (GET, POST)
     * @return array Request data
     */
    protected function getRequestData($method = 'POST'): array
    {
        $method = strtoupper($method);
        if ($method === 'GET') {
            return $_GET;
        }

        if ($method === 'POST') {
            // Check for JSON input
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (str_contains((string) $contentType, 'application/json')) {
                $json = file_get_contents('php://input');
                return json_decode($json, true) ?: [];
            }
            return $_POST;
        }

        return [];
    }

    /**
     * Validate required parameters
     *
     * @param array $params Parameters to validate
     * @param array $required Required parameter names
     * @return bool True if all required parameters are present
     */
    protected function validateParams($params, $required): bool
    {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($params[$field]) || (is_string($params[$field]) && trim($params[$field]) === '')) {
                $missing[] = $field;
            }
        }

        if ($missing !== []) {
            $this->sendError('Missing required fields: ' . implode(', ', $missing));
            return false;
        }

        return true;
    }

    /**
     * Get patient ID from user ID
     *
     * @return int|null Patient ID or null if not found
     */
    protected function getPatientId(): ?int
    {
        if ($this->userRole === 'patient') {
            try {
                return getPatientIdFromUserId($this->userId);
            } catch (Exception $e) {
                ErrorHandler::logDatabaseError($e, 'ApiController::getPatientId');
                return null;
            }
        }
        return null;
    }

    /**
     * Get staff ID from user ID
     *
     * @return int|null Staff ID or null if not found
     */
    protected function getStaffId(): ?int
    {
        if (in_array($this->userRole, ['doctor', 'nurse', 'pharmacist', 'lab_technician'])) {
            try {
                return getStaffIdFromUserId($this->userId);
            } catch (Exception $e) {
                ErrorHandler::logDatabaseError($e, 'ApiController::getStaffId');
                return null;
            }
        }
        return null;
    }

    /**
     * Helper method for binding parameters to prepared statements
     *
     * @param mysqli_stmt $stmt Prepared statement
     * @param array $params Parameters to bind
     * @return bool Success status
     */
    protected function bindParams($stmt, $params): bool
    {
        if (empty($params)) {
            return true;
        }

        $types = '';
        $bindParams = [];

        foreach ($params as $key => $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            // Store by reference
            $bindParams[] = &$params[$key];
        }

        // Create dynamic call to bind_param
        array_unshift($bindParams, $types);
        return call_user_func_array($stmt->bind_param(...), $bindParams);
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
     * Handle an exception with standardized API error response
     *
     * @param Exception $e The exception to handle
     * @param int $status HTTP status code
     */
    protected function handleException($e, $status = 500): void
    {
        // Log the exception
        ErrorHandler::logSystemError($e, static::class . '::' . debug_backtrace()[1]['function']);

        // Send error response with exception details in debug mode
        $this->sendError('An error occurred: ' . $e->getMessage(), $status, [], $e);
    }

    /**
     * Process and validate request data against a model
     *
     * @param array $requiredFields Required field names
     * @param BaseModel|null $model Model instance to validate against (optional)
     * @return array|false Request data or false if validation failed
     */
    protected function processRequestData($requiredFields = [], $model = null): array|false
    {
        // Get request data
        $data = $this->getRequestData();

        // Validate required fields
        if (!$this->validateParams($data, $requiredFields)) {
            return false;
        }

        // Validate against model if provided
        if ($model instanceof BaseModel && !$model->validate($data)) {
            $this->sendError('Validation failed', 422, $model->getErrors());
            return false;
        }

        return $data;
    }

    /**
     * Execute a query with parameters
     *
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return bool|int True for success, insert_id for INSERT queries, false for failure
     */
    protected function execute($sql, $params = []): bool|int
    {
        try {
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            if (!empty($params) && !$this->bindParams($stmt, $params)) {
                throw new Exception("Parameter binding failed");
            }

            $result = $stmt->execute();

            if ($result && str_starts_with(strtoupper($sql), 'INSERT')) {
                $result = $stmt->insert_id;
            }

            $stmt->close();
            return $result;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, 'ApiController::execute');
            return false;
        }
    }

    /**
     * Fetch a single row from a query
     *
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array|null Row data or null if not found
     */
    protected function fetchOne($sql, $params = []): ?array
    {
        try {
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            if (!empty($params) && !$this->bindParams($stmt, $params)) {
                throw new Exception("Parameter binding failed");
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stmt->close();
                return $row;
            }

            $stmt->close();
            return null;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, 'ApiController::fetchOne');
            return null;
        }
    }

    /**
     * Fetch all rows from a query
     *
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array Array of row data or empty array if no results
     */
    protected function fetchAll($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            if (!empty($params) && !$this->bindParams($stmt, $params)) {
                throw new Exception("Parameter binding failed");
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $rows = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
            }

            $stmt->close();
            return $rows;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, 'ApiController::fetchAll');
            return [];
        }
    }
}
