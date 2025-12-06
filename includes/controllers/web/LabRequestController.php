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
require_once __DIR__ . '/../../includes/helpers/AuditLogger.php';

class LabRequestController extends WebController {
    private $labTestModel;
    private $patientModel;
    private $userModel;
    private $auditLogger;
    private $cache;
    
    // Rate limiting constants
    private const RATE_LIMIT = 60; // Max requests per minute
    private const RATE_LIMIT_WINDOW = 60; // 60 seconds
    
    // Valid statuses for filtering
    private const VALID_STATUSES = [
        'pending', 'in_progress', 'completed', 'cancelled', 'all'
    ];
    
    public function __construct() {
        parent::__construct();
        
        // Initialize models
        $this->labTestModel = new LabTestModel();
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
        $this->auditLogger = new AuditLogger();
        
        // Initialize cache (using session for simplicity, consider Redis/Memcached for production)
        $this->initCache();
        
        // Security checks
        $this->requireLogin();
        $this->checkCsrfToken();
        $this->checkRateLimit();
        
        // Role-based access control
        $this->enforceRoleAccess();
    }
    
    /**
     * Initialize cache system
     */
    private function initCache() {
        // In a production environment, replace this with Redis/Memcached
        if (!isset($_SESSION['cache'])) {
            $_SESSION['cache'] = [];
        }
        $this->cache = &$_SESSION['cache'];
    }
    
    /**
     * Enforce role-based access control
     */
    private function enforceRoleAccess() {
        $allowedRoles = ['admin', 'lab_technician', 'doctor', 'patient'];
        if (!in_array($this->userRole, $allowedRoles)) {
            $this->logSecurityEvent('unauthorized_access_attempt', [
                'user_id' => $this->userId,
                'role' => $this->userRole,
                'ip' => $this->getClientIp()
            ]);
            $this->redirectWithError('You do not have permission to access this section', '/dashboard');
            exit;
        }
    }
    
    /**
     * List all lab test requests with pagination and filtering
     */
    public function index() {
        try {
            $startTime = microtime(true);
            
            // Input validation and sanitization
            $status = $this->getValidatedStatus($_GET['status'] ?? 'pending');
            $page = max(1, (int)($this->sanitizeInput($_GET['page'] ?? 1)));
            $perPage = $this->getValidPerPage($_GET['per_page'] ?? 10);
            $search = $this->sanitizeInput($_GET['search'] ?? '');
            
            // Get requests based on user role with proper access control
            list($requests, $total) = $this->getFilteredRequests($status, $search, $page, $perPage);
            
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
                    'url' => "/lab/requests?status=$status&search=" . urlencode($search) . "&per_page=$perPage&page="
                ]
            ];
            
            $this->render('lab/requests/index', $viewData);
            
        } catch (Exception $e) {
            $this->handleError('Error loading lab requests', $e);
            $this->redirect('/lab/requests');
        }
    }
    
    /**
     * Get filtered requests based on user role and filters
     */
    private function getFilteredRequests($status, $search, $page, $perPage) {
        $requests = [];
        $total = 0;
        
        if (in_array($this->userRole, ['admin', 'lab_technician'])) {
            // Admins and lab techs can see all requests
            $requests = $this->labTestModel->getRequestsByStatus($status, $search, $page, $perPage);
            $total = $this->labTestModel->countRequestsByStatus($status, $search);
        } elseif ($this->userRole === 'doctor') {
            // Doctors can see requests they've created
            $requests = $this->labTestModel->getRequestsByDoctor($this->userId, $status, $search, $page, $perPage);
            $total = $this->labTestModel->countRequestsByDoctor($this->userId, $status, $search);
        } elseif ($this->userRole === 'patient') {
            // Patients can see their own requests
            $patientId = $this->getPatientIdForCurrentUser();
            if ($patientId) {
                $requests = $this->labTestModel->getRequestsByPatient($patientId, $status, $search, $page, $perPage);
                $total = $this->labTestModel->countRequestsByPatient($patientId, $status, $search);
            }
        }
        
        return [$requests, $total];
    }
    
    /**
     * Get patient ID for the current user
     */
    private function getPatientIdForCurrentUser() {
        if ($this->userRole === 'patient') {
            $patient = $this->patientModel->getByUserId($this->userId);
            return $patient ? $patient['id'] : null;
        }
        return null;
    }
    
    /**
     * Validate and sanitize status parameter
     */
    private function getValidatedStatus($status) {
        return in_array($status, self::VALID_STATUSES) ? $status : 'pending';
    }
    
    /**
     * Validate and sanitize items per page
     */
    private function getValidPerPage($perPage) {
        $perPage = (int)$perPage;
        return min(100, max(5, $perPage)); // Limit to 5-100 items per page
    }
    
    /**
     * Sanitize input to prevent XSS
     */
    private function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Log performance metrics
     */
    private function logPerformance($method, $startTime) {
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
    private function logSecurityEvent($event, $details = []) {
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
    private function getClientIp() {
        $ip = $_SERVER['HTTP_CLIENT_IP'] ?? 
              $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return is_array($ip) ? $ip[0] : $ip;
    }
    
    /**
     * Check and enforce rate limiting
     */
    private function checkRateLimit() {
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
            echo $this->renderError('Too many requests. Please try again later.', 429);
            exit;
        }
        
        $this->incrementRateLimit($key);
    }
    
    /**
     * Generate a rate limit key based on user and IP
     */
    private function getRateLimitKey() {
        return 'user_' . $this->userId . '_' . md5($this->getClientIp() . $_SERVER['REQUEST_URI']);
    }
    
    /**
     * Get current rate limit count
     */
    private function getRateLimitCount($key) {
        if (isset($this->cache[$key])) {
            list($count, $timestamp) = explode('|', $this->cache[$key]);
            if (time() - $timestamp < self::RATE_LIMIT_WINDOW) {
                return (int)$count;
            }
        }
        return 0;
    }
    
    /**
     * Increment rate limit counter
     */
    private function incrementRateLimit($key) {
        $count = $this->getRateLimitCount($key) + 1;
        $this->cache[$key] = $count . '|' . time();
    }
    
    /**
     * Verify CSRF token for POST requests
     */
    private function checkCsrfToken() {
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
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Generate a CSRF token
     */
    public function getCsrfToken() {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }
    
    /**
     * Validate CSRF token
     */
    private function validateCsrfToken($token) {
        return isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $token);
    }
    
    /**
     * Enhanced error handler with logging
     */
    protected function handleError($message, Exception $e = null) {
        $errorId = uniqid('err_');
        $errorDetails = [
            'error_id' => $errorId,
            'message' => $message,
            'exception' => $e ? [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ] : null,
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'uri' => $_SERVER['REQUEST_URI'],
                'params' => $_REQUEST,
                'ip' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ],
            'user' => [
                'id' => $this->userId,
                'role' => $this->userRole
            ],
            'timestamp' => date('c')
        ];
        
        // Log to error log
        error_log(json_encode($errorDetails, JSON_PRETTY_PRINT));
        
        // Log to security audit
        $this->logSecurityEvent('error_occurred', [
            'error_id' => $errorId,
            'message' => $message,
            'exception' => $e ? $e->getMessage() : null
        ]);
        
        // Return user-friendly error
        if ($this->isAjaxRequest()) {
            http_response_code(500);
            echo json_encode([
                'error' => 'An error occurred. Reference: ' . $errorId,
                'debug' => $this->isDebugMode() ? $errorDetails : null
            ]);
            exit;
        }
        
        // For non-AJAX, show error page
        $this->render('error/500', [
            'error' => 'An error occurred',
            'errorId' => $errorId,
            'debugInfo' => $this->isDebugMode() ? $errorDetails : null
        ], 'error');
        exit;
    }
    
    /**
     * Check if debug mode is enabled
     */
    private function isDebugMode() {
        return defined('DEBUG_MODE') && DEBUG_MODE === true;
    }
    
    /**
     * Get requests for the current user
     */
    public function index() {
        try {
            // Get requests based on user role with proper access control
            list($requests, $total) = $this->getFilteredRequests('pending', '', 1, 10);  
            
            // Get test types for the create form
            $testTypes = $this->labTestModel->getAllTestTypes();
            
            // Render the view
            $this->render('lab/requests', [
                'requests' => $requests,
                'testTypes' => $testTypes,  
                'status' => 'pending',
                'pagination' => [
                    'total' => $total,
                    'page' => 1,
                    'perPage' => 10,
                    'url' => "/lab/requests?status=pending&page=1"
                ]
            ]);
            
        } catch (Exception $e) {
            $this->handleError('Error loading lab requests', $e);
        }
    }
    
    /**
     * Show create lab test request form
     */
    public function create() {
        try {
            // Only doctors can create test requests
            if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
                $this->redirectWithError('You do not have permission to request lab tests', '/lab/requests');
                return;
            }
            
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
            $appointmentId = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;
            if ($patientId) {
                $patient = $this->patientModel->getWithUserData($patientId);
                if (!$patient) {
                    $this->redirectWithError('Patient not found', '/lab/requests');
                    return;
                }
            }
            
            // Get test types
            $testTypes = $this->labTestModel->getAllTestTypes();
            
            // Get recent tests for this patient
            $recentTests = [];
            if ($patientId) {
                $recentTests = $this->labTestModel->getRecentTestsByPatient($patientId, 5);
            }
            
            $this->render('lab/request_form', [
                'patient' => $patient,
                'appointmentId' => $appointmentId,
                'testTypes' => $testTypes,
                'recentTests' => $recentTests
            ]);
                
        } catch (Exception $e) {
            $this->handleError('Error loading lab request form', $e);
        }
    }
    
    /**
     * Store a new lab test request
     */
    public function store() {
        try {
            // Only doctors can create test requests
            if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
                $this->jsonResponse(['success' => false, 'message' => 'You do not have permission to request lab tests'], 403);
                return;
            }
            
            // Validate input
            $required = ['patient_id', 'test_type_id', 'clinical_notes'];
            $missing = [];
            $data = [];
            
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $missing[] = $field;
                } else {
                    $data[$field] = $_POST[$field];
                }
            }
            
            if (!empty($missing)) {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'Missing required fields: ' . implode(', ', $missing)
                ], 400);
                return;
            }
            
            // Get patient and test type details
            $patient = $this->patientModel->getWithUserData($data['patient_id']);
            if (!$patient) {
                $this->jsonResponse(['success' => false, 'message' => 'Patient not found'], 404);
                return;
            }
            
            $testType = $this->labTestModel->getTestTypeById($data['test_type_id']);
            if (!$testType) {
                $this->jsonResponse(['success' => false, 'message' => 'Test type not found'], 404);
                return;
            }
            
            // Prepare request data
            $requestData = [
                'patient_id' => $data['patient_id'],
                'test_type_id' => $data['test_type_id'],
                'requested_by' => $this->userId,
                'clinical_notes' => $data['clinical_notes'],
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
                // Log the action
                $this->logAction('lab_request_created', [
                    'request_id' => $requestId,
                    'patient_id' => $data['patient_id'],
                    'test_type' => $testType['test_name']
                ]);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Lab test request created successfully',
                    'redirect' => '/lab/requests/view/' . $requestId
                ]);
            } else {
                throw new Exception('Failed to create lab test request');
            }
            
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
    public function view($requestId) {
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
            
            $this->render('lab/view_request', [
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
    public function updateStatus($requestId) {
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
            $success = $this->labTestModel->updateRequestStatus($requestId, $status, $notes, $this->userId);
            
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
    public function saveResults($requestId) {
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
                if (empty($result['parameter_id']) || !isset($result['result_value'])) {
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
            
            if (empty($results)) {
                $this->jsonResponse(['success' => false, 'message' => 'No valid results provided'], 400);
                return;
            }
            
            // Save results
            $success = $this->labTestModel->saveTestResults($results);
            
            if ($success) {
                // Update request status to completed if not already
                if ($request['status'] !== 'completed') {
                    $this->labTestModel->updateRequestStatus($requestId, 'completed', 'Test results recorded', $this->userId);
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
    public function printResults($requestId) {
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
            
            $this->render('lab/print_results', [
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
    private function canViewRequest($request) {
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
            $patient = $this->patientModel->getPatientByUserId($this->userId);
            return $patient && $patient['patient_id'] == $request['patient_id'];
        }
        
        return false;
    }
    
    /**
     * Check if current user can edit a request
     */
    private function canEditRequest($request) {
        // Only pending or in-progress requests can be edited
        if (!in_array($request['status'], ['pending', 'in_progress'])) {
            return false;
        }
        
        // Admins and lab techs can edit any request
        if ($this->userRole === 'admin' || $this->userRole === 'lab_technician') {
            return true;
        }
        
        // Doctors can edit their own pending requests
        if ($this->userRole === 'doctor' && $request['requested_by'] == $this->userId && $request['status'] === 'pending') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if current user can delete a request
     */
    private function canDeleteRequest($request) {
        // Only pending requests can be deleted
        if ($request['status'] !== 'pending') {
            return false;
        }
        
        // Admins can delete any pending request
        if ($this->userRole === 'admin') {
            return true;
        }
        
        // Doctors can delete their own pending requests
        if ($this->userRole === 'doctor' && $request['requested_by'] == $this->userId) {
            return true;
        }
        
        return false;
    }
}
