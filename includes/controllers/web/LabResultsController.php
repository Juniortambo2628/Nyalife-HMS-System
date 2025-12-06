<?php

/**
 * Lab Results Controller
 * Handles viewing and downloading lab test results for patients
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/LabTestModel.php';
require_once __DIR__ . '/../../models/UserModel.php';

class LabResultsController extends WebController
{
    private readonly \PatientModel $patientModel;

    public function __construct()
    {
        parent::__construct();
        $this->patientModel = new PatientModel();
    }

    /**
     * View a lab test result
     * Maps test_item_id from lab_test_items table to result_id for compatibility
     */
    public function view($resultId): void
    {
        try {
            // resultId is actually test_item_id from lab_test_items
            $db = DatabaseManager::getInstance()->getConnection();

            // Query based on actual schema: lab_test_items has test_item_id, request_id, test_type_id, status, result_value, notes, etc.
            $sql = "SELECT 
                        ti.test_item_id,
                        ti.request_id,
                        ti.test_type_id,
                        ti.status,
                        ti.result_value,
                        ti.result,
                        ti.result_interpretation,
                        COALESCE(ti.normal_range, lt.normal_range) as normal_range,
                        ti.units,
                        ti.notes,
                        ti.performed_at,
                        ti.sample_reported_at,
                        ti.performed_by,
                        ti.verified_by,
                        lr.patient_id,
                        lr.request_date,
                        lr.completed_at,
                        lr.status as request_status,
                        lt.test_name,
                        lt.description as test_description,
                        lt.normal_range,
                        CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                        CONCAT(req_user.first_name, ' ', req_user.last_name) as requested_by_name,
                        COALESCE(CONCAT(v_user.first_name, ' ', v_user.last_name), CONCAT(p_user2.first_name, ' ', p_user2.last_name)) as verified_by_name
                    FROM lab_test_items ti
                    JOIN lab_test_requests lr ON ti.request_id = lr.request_id
                    LEFT JOIN lab_test_types lt ON ti.test_type_id = lt.test_type_id
                    LEFT JOIN patients p ON lr.patient_id = p.patient_id
                    LEFT JOIN users p_user ON p.user_id = p_user.user_id
                    LEFT JOIN users req_user ON lr.requested_by = req_user.user_id
                    LEFT JOIN users v_user ON ti.verified_by = v_user.user_id
                    LEFT JOIN users p_user2 ON ti.performed_by = p_user2.user_id
                    WHERE ti.test_item_id = ?";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $db->error);
            }

            $stmt->bind_param("i", $resultId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->redirectWithError('Lab test result not found', '/dashboard');
                return;
            }

            $labResult = $result->fetch_assoc();

            // Check if current user can view this result (patients can only view their own)
            $currentUser = SessionManager::get('user');
            if (!$currentUser) {
                $this->redirectWithError('Please login to view lab results', '/login');
                return;
            }

            $userRole = SessionManager::get('role');
            $userId = SessionManager::get('user_id');
            $patientId = $this->patientModel->getPatientIdByUserId($userId);
            if ($userRole === 'patient' && $patientId != $labResult['patient_id']) {
                $this->redirectWithError('You do not have permission to view this result', '/dashboard');
                return;
            }

            // Get all results for this request (if there are multiple items)
            $allResults = $this->getTestRequestResults($labResult['request_id']);

            // Get patient details
            $patient = $this->patientModel->getWithUserData($labResult['patient_id']);

            $this->renderView('lab-results/view', [
                'labResult' => $labResult,
                'allResults' => $allResults,
                'patient' => $patient,
                'currentUser' => $currentUser
            ]);
        } catch (Exception $e) {
            $this->handleError('Error viewing lab result', $e);
        }
    }

    /**
     * Download lab test result (PDF or print)
     */
    public function download($resultId): void
    {
        try {
            // Similar to view but render as PDF or downloadable format
            $db = DatabaseManager::getInstance()->getConnection();

            $sql = "SELECT 
                        ti.*,
                        lr.request_id,
                        lr.patient_id,
                        lr.request_date,
                        lt.test_name,
                        CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name
                    FROM lab_test_items ti
                    JOIN lab_test_requests lr ON ti.request_id = lr.request_id
                    LEFT JOIN lab_test_types lt ON ti.test_type_id = lt.test_type_id
                    LEFT JOIN patients p ON lr.patient_id = p.patient_id
                    LEFT JOIN users p_user ON p.user_id = p_user.user_id
                    WHERE ti.test_item_id = ?";

            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $resultId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->redirectWithError('Lab test result not found', '/dashboard');
                return;
            }

            $labResult = $result->fetch_assoc();

            // Check permissions
            $currentUser = SessionManager::get('user');
            if (!$currentUser) {
                $this->redirectWithError('Please login to download lab results', '/login');
                return;
            }

            $userRole = SessionManager::get('role');
            $userId = SessionManager::get('user_id');
            $patientId = $this->patientModel->getPatientIdByUserId($userId);
            if ($userRole === 'patient' && $patientId != $labResult['patient_id']) {
                $this->redirectWithError('You do not have permission to download this result', '/dashboard');
                return;
            }

            // Get patient details
            $patient = $this->patientModel->getWithUserData($labResult['patient_id']);

            // For now, redirect to view page with print parameter
            // In future, can implement actual PDF generation
            $this->renderView('lab-results/view', [
                'labResult' => $labResult,
                'patient' => $patient,
                'currentUser' => $currentUser,
                'print' => true
            ], 'print');
        } catch (Exception $e) {
            $this->handleError('Error downloading lab result', $e);
        }
    }

    /**
     * Get all results for a test request
     */
    private function getTestRequestResults(int $requestId): array
    {
        try {
            $sql = "SELECT 
                        ti.*,
                        lt.test_name,
                        lt.description,
                        lt.normal_range
                    FROM lab_test_items ti
                    LEFT JOIN lab_test_types lt ON ti.test_type_id = lt.test_type_id
                    WHERE ti.request_id = ?
                    ORDER BY ti.test_item_id ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();

            $results = [];
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }

            return $results;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Index page - list all lab results for current patient/user
     */
    public function index(): void
    {
        try {
            $currentUser = SessionManager::get('user');
            if (!$currentUser) {
                $this->redirectWithError('Please login to view lab results', '/login');
                return;
            }

            // Get role from session (same way DashboardController does it)
            $userRole = SessionManager::get('role');
            $userId = SessionManager::get('user_id');

            $labResults = [];

            // If patient, get their results
            if ($userRole === 'patient') {
                $patientId = $this->patientModel->getPatientIdByUserId($userId);
                if ($patientId) {
                    $labResults = $this->getPatientLabResults($patientId);
                }
            } else {
                // For admin/doctor, show all results (with filters)
                $labResults = $this->getAllLabResults();
            }

            $this->renderView('lab-results/index', [
                'labResults' => $labResults,
                'currentUser' => $currentUser
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading lab results', $e);
        }
    }

    /**
     * Get patient lab results (using same query as PatientController)
     */
    private function getPatientLabResults(int $patientId): array
    {
        try {
            $sql = "SELECT 
                        ti.test_item_id as result_id,
                        ti.status as result_status,
                        ti.result_value,
                        COALESCE(ti.sample_reported_at, ti.performed_at, lr.completed_at, lr.request_date) as test_date,
                        lt.test_name,
                        COALESCE(CONCAT(ud.first_name, ' ', ud.last_name), CONCAT(up.first_name, ' ', up.last_name)) as doctor_name
                    FROM lab_test_items ti
                    JOIN lab_test_requests lr ON ti.request_id = lr.request_id
                    LEFT JOIN lab_test_types lt ON ti.test_type_id = lt.test_type_id
                    LEFT JOIN users ud ON ti.verified_by = ud.user_id
                    LEFT JOIN users up ON ti.performed_by = up.user_id
                    WHERE lr.patient_id = ? AND ti.status IN ('completed', 'pending', 'processing')
                    ORDER BY test_date DESC";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }

            $stmt->bind_param('i', $patientId);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }

            $result = $stmt->get_result();

            $labResults = [];
            while ($row = $result->fetch_assoc()) {
                $labResults[] = $row;
            }

            return $labResults;
        } catch (Exception $e) {
            error_log("Error getting lab results: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all lab results (for admin/doctor view)
     */
    private function getAllLabResults(): array
    {
        try {
            $sql = "SELECT 
                        ti.test_item_id as result_id,
                        ti.status as result_status,
                        ti.result_value,
                        COALESCE(ti.sample_reported_at, ti.performed_at, lr.completed_at, lr.request_date) as test_date,
                        lt.test_name,
                        CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                        COALESCE(CONCAT(ud.first_name, ' ', ud.last_name), CONCAT(up.first_name, ' ', up.last_name)) as doctor_name
                    FROM lab_test_items ti
                    JOIN lab_test_requests lr ON ti.request_id = lr.request_id
                    LEFT JOIN lab_test_types lt ON ti.test_type_id = lt.test_type_id
                    LEFT JOIN patients p ON lr.patient_id = p.patient_id
                    LEFT JOIN users p_user ON p.user_id = p_user.user_id
                    LEFT JOIN users ud ON ti.verified_by = ud.user_id
                    LEFT JOIN users up ON ti.performed_by = up.user_id
                    WHERE ti.status IN ('completed', 'pending', 'processing')
                    ORDER BY test_date DESC
                    LIMIT 100";

            $result = $this->db->query($sql);

            $labResults = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $labResults[] = $row;
                }
            }

            return $labResults;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
}
