<?php

/**
 * Nyalife HMS - Lab Request Web Controller
 *
 * Handles all lab test request related web requests with enhanced security and error handling
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/LabTestModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../helpers/AuditLogger.php';
require_once __DIR__ . '/../../core/SessionManager.php';
require_once __DIR__ . '/../../db_utils.php';
require_once __DIR__ . '/../../../functions.php';

class LabRequestController extends WebController
{
    // Constants for rate limiting
    public const RATE_LIMIT = 100; // Maximum number of requests per minute
    public const RATE_LIMIT_WINDOW = 60; // Window in seconds

    // Valid statuses for lab requests
    public const VALID_STATUSES = ['pending', 'processing', 'completed', 'cancelled'];

    private readonly \LabTestModel $labTestModel;
    
    private readonly \PatientModel $patientModel;
    
    private readonly \UserModel $userModel;
    
    private readonly \AuditLogger $auditLogger;
    
    private ?array $cache = null;
    protected $requiresLogin = true;
    protected $allowedRoles = ['admin', 'lab_technician', 'doctor', 'patient'];
    protected $userId;
    protected $userRole;

    public function __construct()
    {
        parent::__construct();

        // Get user info from session
        $this->userId = SessionManager::get('user_id');
        $this->userRole = SessionManager::get('user_role');

        // Initialize models
        $this->labTestModel = new LabTestModel();
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();

        // Initialize AuditLogger with database connection from the model
        $this->auditLogger = new AuditLogger($this->labTestModel->getDbConnection());

        // Initialize cache (using session for simplicity, consider Redis/Memcached for production)
        $this->initCache();

        // Security checks
        $this->checkCsrfToken();
        $this->checkRateLimit();
    }

    /**
     * Initialize cache system
     */
    private function initCache(): void
    {
        // In a production environment, replace this with Redis/Memcached
        if (!isset($_SESSION['cache'])) {
            $_SESSION['cache'] = [];
        }
        $this->cache = &$_SESSION['cache'];
    }

    /**
     * List all lab test requests with pagination and filtering
     */
    public function index(): void
    {
        try {
            $startTime = microtime(true);

            // Input validation and sanitization
            $status = $this->getValidatedStatus($_GET['status'] ?? 'pending');
            $page = max(1, (int)($this->sanitizeInput($_GET['page'] ?? 1)));
            $perPage = $this->getValidPerPage($_GET['per_page'] ?? 10);
            $search = $this->sanitizeInput($_GET['search'] ?? '');

            // Get requests based on user role with proper access control
            [$requests, $total] = $this->getFilteredRequests($status, $search, $page, $perPage);

            // Log performance
            $this->logPerformance(__METHOD__, $startTime);

            // Prepare view data
            $viewData = [
                'requests' => $requests,
                'status' => $status,
                'search' => $search,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'url' => "/lab/requests?status=$status&search=" . urlencode((string) $search) . "&per_page=$perPage&page="
                ]
            ];

            $this->renderView('lab/requests/index', $viewData);
        } catch (Exception $e) {
            $this->handleError('Error loading lab requests', $e);
            $this->redirect('/lab/requests');
        }
    }

    /**
     * Get filtered requests based on user role and filters
     */
    private function getFilteredRequests(string $status, string $search, int $page, int $perPage): array
    {
        $requests = [];
        $total = 0;

        if (in_array($this->userRole, ['admin', 'lab_technician'])) {
            // Admins and lab techs can see all requests
            $requests = $this->labTestModel->getRequestsByStatus($status, $search, $page, $perPage);
            $total = $this->labTestModel->countRequestsByStatus($status, $search);
        } elseif ($this->userRole === 'doctor') {
            // Doctors can see requests they've created
            $requests = $this->labTestModel->getRequestsByDoctor($this->userId, $search, $page, $perPage);
            $total = $this->labTestModel->countRequestsByDoctor($this->userId, $search);
        } elseif ($this->userRole === 'patient') {
            // Patients can see their own requests
            $patientId = $this->getPatientIdForCurrentUser();
            if ($patientId !== null && $patientId !== 0) {
                $requests = $this->labTestModel->getRequestsByPatient($patientId, $search, $page, $perPage);
                $total = $this->labTestModel->countRequestsByPatient($patientId, $search);
            }
        }

        return [$requests, $total];
    }

    /**
     * Get patient ID for the current user
     */
    private function getPatientIdForCurrentUser(): ?int
    {
        if ($this->userRole === 'patient') {
            $patient = $this->patientModel->getByUserId($this->userId);
            return $patient ? $patient['id'] : null;
        }
        return null;
    }

    /**
     * Validate and sanitize status parameter
     */
    private function getValidatedStatus(string $status): string
    {
        return in_array($status, self::VALID_STATUSES) ? $status : 'pending';
    }

    /**
     * Validate and sanitize items per page
     */
    private function getValidPerPage(mixed $perPage): int
    {
        $perPage = (int)$perPage;
        return min(100, max(5, $perPage)); // Limit to 5-100 items per page
    }

    /**
     * Sanitize input to prevent XSS
     */
    private function sanitizeInput(mixed $input): array|string
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim((string) $input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Log performance metrics
     */
    private function logPerformance(string $method, float $startTime): void
    {
        $executionTime = microtime(true) - $startTime;
        if ($executionTime > 1.0) { // Log if execution takes more than 1 second
            error_log(sprintf(
                'Performance: %s took %.3f seconds',
                $method,
                $executionTime
            ));
        }
    }

    /**
     * Log security-related events
     */
    private function logSecurityEvent(string $event, array $details = []): void
    {
        $this->auditLogger->log([
            'user_id' => $this->userId,
            'action' => $event,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'] ??
              $_SERVER['HTTP_X_FORWARDED_FOR'] ??
              $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return is_array($ip) ? $ip[0] : $ip;
    }

    /**
     * Check and enforce rate limiting
     */
    private function checkRateLimit(): void
    {
        $key = 'rate_limit:' . $this->getRateLimitKey();
        $current = $this->getRateLimitCount($key);

        if ($current >= self::RATE_LIMIT) {
            $this->logSecurityEvent('rate_limit_exceeded', [
                'user_id' => $this->userId,
                'ip' => $this->getClientIp(),
                'count' => $current
            ]);

            http_response_code(429);
            header('Retry-After: ' . self::RATE_LIMIT_WINDOW);
            $this->jsonResponse(['error' => 'Too many requests. Please try again later.'], 429);
            exit;
        }

        $this->incrementRateLimit($key);
    }

    /**
     * Generate a rate limit key based on user and IP
     */
    private function getRateLimitKey(): string
    {
        return 'user_' . $this->userId . '_' . md5($this->getClientIp() . $_SERVER['REQUEST_URI']);
    }

    /**
     * Get current rate limit count
     */
    private function getRateLimitCount(string $key): int
    {
        if (isset($this->cache[$key])) {
            [$count, $timestamp] = explode('|', (string) $this->cache[$key]);
            if (time() - (int)$timestamp < self::RATE_LIMIT_WINDOW) {
                return (int)$count;
            }
        }
        return 0;
    }

    /**
     * Increment rate limit counter
     */
    private function incrementRateLimit(string $key): void
    {
        $count = $this->getRateLimitCount($key) + 1;
        $this->cache[$key] = $count . '|' . time();
    }

    /**
     * Verify CSRF token for POST requests
     */
    private function checkCsrfToken(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

            if (!$token || !$this->validateCsrfToken($token)) {
                $this->logSecurityEvent('csrf_validation_failed', [
                    'user_id' => $this->userId,
                    'ip' => $this->getClientIp(),
                    'token' => $token
                ]);

                if ($this->isAjaxRequest()) {
                    http_response_code(419); // CSRF token mismatch
                    echo json_encode(['error' => 'Session expired. Please refresh the page.']);
                } else {
                    $this->redirectWithError('Session expired. Please try again.', '/');
                }
                exit;
            }
        }
    }

    /**
     * Check if the request is an AJAX request
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Generate a CSRF token
     *
     * @return string CSRF token
     */
    public function getCsrfToken(): string
    {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $token);
    }

    /**
     * Handle errors in a consistent way
     *
     * @param string $message User-friendly error message
     * @param Exception $e Exception if available
     */
    protected function handleError($message, ?Exception $e = null): void
    {
        // Log detailed error
        if ($e instanceof \Exception) {
            error_log("Error in " . static::class . ": " . $e->getMessage());
            error_log($e->getTraceAsString());
        }

        // Set flash message for user
        $this->setFlashMessage('error', $message);

        // Log security event but only if not related to database/logger issues
        try {
            // $this->db is always initialized from BaseController, check auditLogger and userId
            if ($this->auditLogger instanceof \AuditLogger && $this->userId) {
                $this->logSecurityEvent('error_occurred', [
                    'message' => $message,
                            'details' => $e instanceof \Exception ? $e->getMessage() : 'No exception details'
                        ]);
            }
        } catch (Exception $logException) {
            // Just log to error log if audit logging fails
            error_log("Failed to log security event: " . $logException->getMessage());
        }
    }

    /**
     * Check if debug mode is enabled
     */
    protected function isDebugMode(): bool
    {
        return defined('DEBUG_MODE') && DEBUG_MODE;
    }

    /**
     * Show create lab test request form
     */
    public function create(): void
    {
        try {
            // Only doctors can create test requests
            if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
                $this->redirectWithError('You do not have permission to request lab tests', '/lab/requests');
                return;
            }

            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
            $appointmentId = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

            // Get patient data if patient_id is provided
            $patient = null;
            if ($patientId !== 0) {
                $patient = $this->patientModel->getWithUserData($patientId);
                if (!$patient) {
                    $this->redirectWithError('Patient not found', '/lab/requests');
                    return;
                }
            }

            // Get all patients for the dropdown
            $patients = $this->patientModel->getAllPatientsWithUserData();

            // Get test types
            $testTypes = $this->labTestModel->getAllTestTypes();

            // Group test types by category
            $testCategories = [];
            foreach ($testTypes as $test) {
                $category = $test['category'] ?? 'General';
                if (!isset($testCategories[$category])) {
                    $testCategories[$category] = [
                        'name' => $category,
                        'tests' => []
                    ];
                }
                $testCategories[$category]['tests'][] = [
                    'id' => $test['test_type_id'],
                    'name' => $test['test_name'],
                    'description' => $test['description'] ?? '',
                    'price' => $test['price'] ?? 0
                ];
            }

            // Get recent tests for this patient
            $recentTests = [];
            if ($patientId !== 0) {
                $recentTests = $this->labTestModel->getRecentTestsByPatient($patientId, 5);
            }

            $this->renderView('lab/request_form', [
                'patient' => $patient,
                'patients' => $patients,
                'appointmentId' => $appointmentId,
                'testTypes' => $testTypes,
                'testCategories' => $testCategories,
                'recentTests' => $recentTests,
                'selectedPatientId' => $patientId
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading lab request form', $e);
        }
    }

    /**
     * Store a new lab test request
     */
    public function store(): void
    {
        try {
            // Only doctors can create test requests
            if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
                $this->jsonResponse(['success' => false, 'message' => 'You do not have permission to request lab tests'], 403);
                return;
            }

            // Validate input
            $required = ['patient_id', 'test_type_id'];
            $missing = [];
            $data = [];

            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $missing[] = $field;
                } else {
                    $data[$field] = $_POST[$field];
                }
            }

            if ($missing !== []) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missing)
                ], 400);
                return;
            }

            // Validate that at least one test is selected
            $testTypeIds = is_array($data['test_type_id']) ? $data['test_type_id'] : [$data['test_type_id']];
            $testTypeIds = array_filter($testTypeIds); // Remove any empty values
            if ($testTypeIds === []) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Please select at least one test'
                ], 400);
                return;
            }

            // Get patient details
            $patient = $this->patientModel->getWithUserData($data['patient_id']);
            if (!$patient) {
                $this->jsonResponse(['success' => false, 'message' => 'Patient not found'], 404);
                return;
            }

            // Validate all test types exist (testTypeIds already normalized above)
            foreach ($testTypeIds as $testTypeId) {
                $testType = $this->labTestModel->getTestTypeById($testTypeId);
                if (!$testType) {
                    $this->jsonResponse(['success' => false, 'message' => 'Test type not found: ' . $testTypeId], 404);
                    return;
                }
            }

            $createdRequests = [];

            // Create a request for each selected test
            foreach ($testTypeIds as $testTypeId) {
                // Prepare request data
                $requestData = [
                    'patient_id' => $data['patient_id'],
                    'test_type_id' => $testTypeId,
                    'requested_by' => $this->userId,
                    'clinical_notes' => $_POST['clinical_notes'] ?? '',
                    'priority' => $_POST['priority'] ?? 'normal',
                    'status' => 'pending',
                    'request_date' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Add appointment ID if provided
                if (!empty($_POST['appointment_id'])) {
                    $requestData['appointment_id'] = (int)$_POST['appointment_id'];
                }

                // Create the request
                $requestId = $this->labTestModel->createTestRequest($requestData);

                if ($requestId) {
                    $createdRequests[] = $requestId;

                    // Log the action
                    $this->logAction('lab_request_created', [
                        'request_id' => $requestId,
                        'patient_id' => $data['patient_id'],
                        'test_type_id' => $testTypeId
                    ]);
                } else {
                    throw new Exception('Failed to create lab test request for test type: ' . $testTypeId);
                }
            }

            // Send success response - at least one request must have been created
            // (if any request failed to create, an exception would have been thrown in the loop)
            // Since we validated testTypeIds is not empty and reached here without exception,
            // createdRequests must have at least one element
            $this->jsonResponse([
                'success' => true,
                'message' => 'Lab test request(s) created successfully',
                'redirect' => '/lab/requests'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error creating lab test request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a lab test request
     */
    public function view($requestId): void
    {
        try {
            $request = $this->labTestModel->getRequestById($requestId);

            if (!$request) {
                $this->redirectWithError('Lab test request not found', '/lab/requests');
                return;
            }

            // Check permissions
            if (!$this->canViewRequest($request)) {
                $this->redirectWithError('You do not have permission to view this request', '/lab/requests');
                return;
            }

            // Get test results if available
            $results = [];
            if ($request['status'] === 'completed') {
                $results = $this->labTestModel->getTestResults($requestId);
            }

            // Get patient details
            $patient = $this->patientModel->getWithUserData($request['patient_id']);

            $this->renderView('lab/view_request', [
                'request' => $request,
                'patient' => $patient,
                'results' => $results,
                'canEdit' => $this->canEditRequest($request),
                'canDelete' => $this->canDeleteRequest($request)
            ]);
        } catch (Exception $e) {
            $this->handleError('Error viewing lab test request', $e);
        }
    }

    /**
     * Update lab test request status
     */
    public function updateStatus(string $requestId): void
    {
        try {
            // Only lab technicians and admins can update status
            if ($this->userRole !== 'lab_technician' && $this->userRole !== 'admin') {
                $this->jsonResponse(['success' => false, 'message' => 'You do not have permission to update test status'], 403);
                return;
            }

            $request = $this->labTestModel->getRequestById($requestId);
            if (!$request) {
                $this->jsonResponse(['success' => false, 'message' => 'Lab test request not found'], 404);
                return;
            }

            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (!in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid status'], 400);
                return;
            }

            // Update status
            $success = $this->labTestModel->updateRequestStatus($requestId, $status, $this->userId);

            if ($success) {
                // Log the action
                $this->logAction('lab_request_status_updated', [
                    'request_id' => $requestId,
                    'old_status' => $request['status'],
                    'new_status' => $status,
                    'notes' => $notes
                ]);

                // If status is completed, create test result records if they don't exist
                if ($status === 'completed') {
                    $this->labTestModel->ensureTestResultRecords($requestId);
                }

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Lab test status updated successfully',
                    'redirect' => '/lab/requests/view/' . $requestId
                ]);
            } else {
                throw new Exception('Failed to update lab test status');
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error updating lab test status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save test results
     */
    public function saveResults(string $requestId): void
    {
        try {
            // Only lab technicians and admins can save results
            if ($this->userRole !== 'lab_technician' && $this->userRole !== 'admin') {
                $this->jsonResponse(['success' => false, 'message' => 'You do not have permission to save test results'], 403);
                return;
            }

            $request = $this->labTestModel->getRequestById($requestId);
            if (!$request) {
                $this->jsonResponse(['success' => false, 'message' => 'Lab test request not found'], 404);
                return;
            }

            // Validate input
            if (empty($_POST['results']) || !is_array($_POST['results'])) {
                $this->jsonResponse(['success' => false, 'message' => 'No results provided'], 400);
                return;
            }

            // Save each result
            $results = [];
            foreach ($_POST['results'] as $result) {
                if (empty($result['parameter_id'])) {
                    continue;
                }
                if (!isset($result['result_value'])) {
                    continue;
                }
                $results[] = [
                    'request_id' => $requestId,
                    'parameter_id' => $result['parameter_id'],
                    'result_value' => $result['result_value'],
                    'remarks' => $result['remarks'] ?? '',
                    'tested_by' => $this->userId,
                    'tested_at' => date('Y-m-d H:i:s')
                ];
            }

            if ($results === []) {
                $this->jsonResponse(['success' => false, 'message' => 'No valid results provided'], 400);
                return;
            }

            // Save results
            $success = $this->labTestModel->saveTestResults($requestId, $results, $this->userId);

            if ($success) {
                // Update request status to completed if not already
                if ($request['status'] !== 'completed') {
                    $this->labTestModel->updateRequestStatus($requestId, 'completed', $this->userId);
                }

                // Log the action
                $this->logAction('lab_test_results_saved', [
                    'request_id' => $requestId,
                    'result_count' => count($results)
                ]);

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Test results saved successfully',
                    'redirect' => '/lab/requests/view/' . $requestId
                ]);
            } else {
                throw new Exception('Failed to save test results');
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error saving test results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print lab test results
     */
    public function printResults(string $requestId): void
    {
        try {
            $request = $this->labTestModel->getRequestById($requestId);

            if (!$request) {
                $this->redirectWithError('Lab test request not found', '/lab/requests');
                return;
            }

            // Check permissions
            if (!$this->canViewRequest($request)) {
                $this->redirectWithError('You do not have permission to view this request', '/lab/requests');
                return;
            }

            // Get test results
            $results = $this->labTestModel->getTestResults($requestId);

            if (empty($results)) {
                $this->redirectWithError('No test results found for this request', '/lab/requests/view/' . $requestId);
                return;
            }

            // Get patient details
            $patient = $this->patientModel->getWithUserData($request['patient_id']);

            // Get doctor details
            $doctor = $this->userModel->getUserById($request['requested_by']);

            // Get lab technician details if results are available
            $labTech = null;
            if (!empty($results[0]['tested_by'])) {
                $labTech = $this->userModel->getUserById($results[0]['tested_by']);
            }

            $this->renderView('lab/print_results', [
                'request' => $request,
                'patient' => $patient,
                'doctor' => $doctor,
                'labTech' => $labTech,
                'results' => $results,
                'print' => true
            ], 'print');
        } catch (Exception $e) {
            $this->handleError('Error printing lab test results', $e);
        }
    }

    /**
     * Check if current user can view a request
     */
    private function canViewRequest(array $request): bool
    {
        // Admins and lab techs can view all requests
        if ($this->userRole === 'admin' || $this->userRole === 'lab_technician') {
            return true;
        }

        // Doctors can view requests they created
        if ($this->userRole === 'doctor' && $request['requested_by'] == $this->userId) {
            return true;
        }

        // Patients can view their own requests
        if ($this->userRole === 'patient') {
            $patientId = $this->patientModel->getPatientIdByUserId($this->userId);
            return $patientId && $patientId == $request['patient_id'];
        }

        return false;
    }

    /**
     * Check if current user can edit a request
     */
    private function canEditRequest(array $request): bool
    {
        // Only pending or in-progress requests can be edited
        if (!in_array($request['status'], ['pending', 'in_progress'])) {
            return false;
        }

        // Admins and lab techs can edit any request
        if ($this->userRole === 'admin' || $this->userRole === 'lab_technician') {
            return true;
        }
        // Doctors can edit their own pending requests
        return $this->userRole === 'doctor' && $request['requested_by'] == $this->userId && $request['status'] === 'pending';
    }

    /**
     * Check if current user can delete a request
     */
    private function canDeleteRequest(array $request): bool
    {
        // Only pending requests can be deleted
        if ($request['status'] !== 'pending') {
            return false;
        }

        // Admins can delete any pending request
        if ($this->userRole === 'admin') {
            return true;
        }
        // Doctors can delete their own pending requests
        return $this->userRole === 'doctor' && $request['requested_by'] == $this->userId;
    }

    /**
     * Log an action
     *
     * @param string $action Action name
     * @param array $details Action details
     */
    private function logAction(string $action, array $details): void
    {
        if ($this->auditLogger instanceof \AuditLogger && $this->userId) {
            $this->auditLogger->log([
                'user_id' => $this->userId,
                'action' => $action,
                'entity_type' => 'lab_request',
                'entity_id' => $details['request_id'] ?? null,
                'details' => $details
            ]);
        }
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
}
