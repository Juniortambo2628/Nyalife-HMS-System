<?php

/**
 * Nyalife HMS - Lab Test API Controller
 *
 * This controller handles all lab test related API requests.
 */

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../data/lab_data.php';

class LabTestController extends ApiController
{
    public $conn;
    public $userRole;
    /**
     * Get lab test result by ID
     */
    public function getTestResult(): void
    {
        // Get test ID from request
        $testId = $this->getIntParam('test_id');

        if ($testId <= 0) {
            $this->sendError('Invalid test ID');
            return;
        }

        try {
            // Get test result details
            $query = "SELECT lt.*, 
                   t.test_name,
                   t.description as test_description,
                   CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   p.patient_number
                FROM lab_test_items lt
                JOIN lab_test_types t ON lt.test_id = t.test_type_id
                JOIN staff s ON lt.doctor_id = s.staff_id 
                JOIN users d ON s.user_id = d.user_id
                JOIN patients pa ON lt.patient_id = pa.patient_id
                JOIN users p ON pa.user_id = p.user_id
                WHERE lt.test_item_id = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $testId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->sendError('Test result not found', 404);
                return;
            }

            $test = $result->fetch_assoc();

            // Check access control
            if (!$this->checkTestAccessPermission($test)) {
                $this->sendError('You do not have permission to view this test result', 403);
                return;
            }

            // Get test parameters and their results
            $query = "SELECT p.parameter_name,
                    p.reference_range,
                    p.unit,
                    r.result_value,
                    r.remarks
                 FROM lab_test_parameters p
                 LEFT JOIN lab_results r ON p.parameter_id = r.parameter_id 
                     AND r.test_item_id = ?
                 WHERE p.test_id = ?
                 ORDER BY p.parameter_name";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $testId, $test['test_id']);
            $stmt->execute();
            $parameters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Generate HTML for the result
            $html = $this->generateTestResultHtml($test, $parameters);

            // Return the HTML content
            $this->sendResponse([
                'html' => $html
            ]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving test result: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new lab test request
     */
    public function createTestRequest(): void
    {
        // Only doctors can create test requests
        if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
            $this->sendError('Only doctors can create lab test requests', 403);
            return;
        }

        // Get request data
        $data = $this->getRequestData();

        // Validate required fields
        $requiredFields = ['patient_id', 'test_id', 'priority', 'notes'];

        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }

        // Get doctor ID
        $doctorId = $this->getStaffId();

        // Validate doctor ID
        if ($doctorId === null || $doctorId === 0) {
            $this->sendError('Doctor ID not found for current user');
            return;
        }

        try {
            // Insert test request
            $query = "INSERT INTO lab_test_items (
                    patient_id, doctor_id, test_id, priority, 
                    notes, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $status = 'pending';
            $createdAt = date('Y-m-d H:i:s');

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            $stmt->bind_param(
                "iiissss",
                $data['patient_id'],
                $doctorId,
                $data['test_id'],
                $data['priority'],
                $data['notes'],
                $status,
                $createdAt
            );

            if (!$stmt->execute()) {
                throw new Exception("Execution failed: " . $stmt->error);
            }

            $testItemId = $stmt->insert_id;

            // Return success response
            $this->sendResponse([
                'message' => 'Lab test request created successfully',
                'test_item_id' => $testItemId
            ], 201);
        } catch (Exception $e) {
            $this->sendError('Error creating lab test request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update lab test status
     */
    public function updateTestStatus(): void
    {
        // Only lab technicians can update test status
        if ($this->userRole !== 'lab_technician' && $this->userRole !== 'admin') {
            $this->sendError('Only lab technicians can update test status', 403);
            return;
        }

        // Get request data
        $data = $this->getRequestData();

        // Validate required fields
        $requiredFields = ['test_item_id', 'status'];

        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }

        $testItemId = intval($data['test_item_id']);
        $status = $data['status'];

        // Validate status
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            $this->sendError('Invalid status value');
            return;
        }

        try {
            // Update status
            $query = "UPDATE lab_test_items SET status = ? WHERE test_item_id = ?";
            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            $stmt->bind_param("si", $status, $testItemId);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                $this->sendError('No changes made to test status or test not found');
                return;
            }

            // Return success response
            $this->sendResponse([
                'message' => 'Lab test status updated successfully',
                'status' => $status
            ]);
        } catch (Exception $e) {
            $this->sendError('Error updating lab test status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Save test results
     */
    public function saveTestResults(): void
    {
        // Only lab technicians can save test results
        if ($this->userRole !== 'lab_technician' && $this->userRole !== 'admin') {
            $this->sendError('Only lab technicians can save test results', 403);
            return;
        }

        // Get request data
        $data = $this->getRequestData();

        // Validate required fields
        $requiredFields = ['test_item_id', 'parameters'];

        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }

        $testItemId = intval($data['test_item_id']);
        $parameters = $data['parameters'];

        // Validate parameters format
        if (!is_array($parameters) || $parameters === []) {
            $this->sendError('Invalid parameters format');
            return;
        }

        try {
            // Start transaction
            $this->conn->begin_transaction();

            // Update test status to completed
            $query = "UPDATE lab_test_items SET 
                    status = 'completed', 
                    performed_by = ?, 
                    performed_at = ?
                    WHERE test_item_id = ?";

            $performedBy = $this->getStaffId();
            $performedAt = date('Y-m-d H:i:s');

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("isi", $performedBy, $performedAt, $testItemId);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Test item not found or no changes made");
            }

            // Insert or update results for each parameter
            foreach ($parameters as $param) {
                if (!isset($param['parameter_id'])) {
                    continue;
                }
                if (!isset($param['result_value'])) {
                    continue;
                }
                // Check if result already exists
                $query = "SELECT result_id FROM lab_results 
                        WHERE test_item_id = ? AND parameter_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ii", $testItemId, $param['parameter_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                $remarks = $param['remarks'] ?? '';

                if ($result->num_rows > 0) {
                    // Update existing result
                    $row = $result->fetch_assoc();
                    $resultId = $row['result_id'];

                    $query = "UPDATE lab_results 
                            SET result_value = ?, remarks = ?
                            WHERE result_id = ?";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bind_param("ssi", $param['result_value'], $remarks, $resultId);
                    $stmt->execute();
                } else {
                    // Insert new result
                    $query = "INSERT INTO lab_results 
                            (test_item_id, parameter_id, result_value, remarks)
                            VALUES (?, ?, ?, ?)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bind_param("iiss", $testItemId, $param['parameter_id'], $param['result_value'], $remarks);
                    $stmt->execute();
                }
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'message' => 'Test results saved successfully',
                'test_item_id' => $testItemId
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error saving test results: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get lab test requests
     */
    public function getLabRequests(): void
    {
        // Get request parameters
        $status = $this->getStringParam('status');
        $priority = $this->getStringParam('priority');
        $startDate = $this->getStringParam('start_date');
        $endDate = $this->getStringParam('end_date');
        $limit = $this->getIntParam('limit', 'GET', 50);

        try {
            // Build query based on user role to ensure proper authorization
            $query = "SELECT lt.*, 
                    t.test_name,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    p.patient_number
                    FROM lab_test_items lt
                    JOIN lab_test_types t ON lt.test_id = t.test_type_id
                    JOIN staff s ON lt.doctor_id = s.staff_id 
                    JOIN users d ON s.user_id = d.user_id
                    JOIN patients pa ON lt.patient_id = pa.patient_id
                    JOIN users p ON pa.user_id = p.user_id
                    WHERE 1=1";

            $params = [];
            $types = "";

            // Filter by role-specific access
            if ($this->userRole === 'patient') {
                $patientId = $this->getPatientId();
                $query .= " AND lt.patient_id = ?";
                $params[] = $patientId;
                $types .= "i";
            } elseif ($this->userRole === 'doctor') {
                $doctorId = $this->getStaffId();
                $query .= " AND lt.doctor_id = ?";
                $params[] = $doctorId;
                $types .= "i";
            }

            // Apply filters
            if ($status !== '' && $status !== '0') {
                $query .= " AND lt.status = ?";
                $params[] = $status;
                $types .= "s";
            }

            if ($priority !== '' && $priority !== '0') {
                $query .= " AND lt.priority = ?";
                $params[] = $priority;
                $types .= "s";
            }

            if ($startDate && $this->isValidDate($startDate)) {
                $query .= " AND DATE(lt.created_at) >= ?";
                $params[] = $startDate;
                $types .= "s";
            }

            if ($endDate && $this->isValidDate($endDate)) {
                $query .= " AND DATE(lt.created_at) <= ?";
                $params[] = $endDate;
                $types .= "s";
            }

            $query .= " ORDER BY lt.created_at DESC LIMIT ?";
            $params[] = $limit;
            $types .= "i";

            // Execute query
            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            // Bind parameters dynamically
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $labRequests = $result->fetch_all(MYSQLI_ASSOC);

            // Return lab requests
            $this->sendResponse($labRequests);
        } catch (Exception $e) {
            $this->sendError('Error retrieving lab requests: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper: Check if user has permission to view test result
     *
     * @param array $test Test data
     * @return bool True if user has permission
     */
    private function checkTestAccessPermission(array $test): bool
    {
        // Admin can view all
        if ($this->userRole === 'admin') {
            return true;
        }

        // Lab technicians can view all
        if ($this->userRole === 'lab_technician') {
            return true;
        }

        // Doctors can view tests they ordered
        if ($this->userRole === 'doctor') {
            $doctorId = $this->getStaffId();
            return $doctorId === $test['doctor_id'];
        }

        // Patients can view their own tests
        if ($this->userRole === 'patient') {
            $patientId = $this->getPatientId();
            return $patientId === $test['patient_id'];
        }

        // By default, deny access
        return false;
    }

    /**
     * Helper: Generate HTML for test result
     *
     * @param array $test Test data
     * @param array $parameters Test parameters
     * @return string HTML content
     */
    private function generateTestResultHtml($test, $parameters): string
    {
        $html = '
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Patient Information</h5>
                    <p>
                        <strong>Name:</strong> ' . htmlspecialchars((string) $test['patient_name']) . '<br>
                        <strong>Patient ID:</strong> ' . htmlspecialchars((string) $test['patient_number']) . '
                    </p>
                </div>
                <div class="col-md-6">
                    <h5>Test Information</h5>
                    <p>
                        <strong>Test Name:</strong> ' . htmlspecialchars((string) $test['test_name']) . '<br>
                        <strong>Requested By:</strong> Dr. ' . htmlspecialchars((string) $test['doctor_name']) . '<br>
                        <strong>Date:</strong> ' . date('M d, Y', strtotime($test['performed_at'] ?? $test['created_at'])) . '
                    </p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <h5>Test Results</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Result</th>
                                    <th>Unit</th>
                                    <th>Reference Range</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>';

        foreach ($parameters as $param) {
            $result_value = $param['result_value'] ?? 'Pending';
            $remarks = $param['remarks'] ?? '';

            // Determine if result is within normal range
            $is_normal = true;
            if ($result_value !== 'Pending' && $param['reference_range']) {
                $range = explode('-', (string) $param['reference_range']);
                if (count($range) === 2) {
                    $min = floatval(trim($range[0]));
                    $max = floatval(trim($range[1]));
                    $value = floatval($result_value);
                    $is_normal = ($value >= $min && $value <= $max);
                }
            }

            $html .= '
                <tr>
                    <td>' . htmlspecialchars((string) $param['parameter_name']) . '</td>
                    <td class="' . ($is_normal ? 'text-success' : 'text-danger') . '">' .
                        htmlspecialchars((string) $result_value) . '</td>
                    <td>' . htmlspecialchars((string) $param['unit']) . '</td>
                    <td>' . htmlspecialchars((string) $param['reference_range']) . '</td>
                    <td>' . htmlspecialchars((string) $remarks) . '</td>
                </tr>';
        }

        $html .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>';

        // Add test description if available
        if (!empty($test['test_description'])) {
            $html .= '
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Test Description</h5>
                    <p>' . nl2br(htmlspecialchars((string) $test['test_description'])) . '</p>
                </div>
            </div>';
        }

        // Add any additional notes
        if (!empty($test['notes'])) {
            $html .= '
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Additional Notes</h5>
                    <p>' . nl2br(htmlspecialchars((string) $test['notes'])) . '</p>
                </div>
            </div>';
        }

        return $html . '
        </div>';
    }

    /**
     * Helper: Validate date format (YYYY-MM-DD)
     *
     * @param string $date Date string
     * @return bool True if date is valid
     */
    private function isValidDate(string $date): bool
    {
        if (in_array(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date), [0, false], true)) {
            return false;
        }

        $dateArr = explode('-', $date);
        return checkdate((int)$dateArr[1], (int)$dateArr[2], (int)$dateArr[0]);
    }

    /**
     * Get lab test results for a request
     */
    public function getLabResults(): void
    {
        // Get request ID from request
        $requestId = $this->getIntParam('request_id');

        if ($requestId === 0) {
            $this->sendError('Request ID is required');
            return;
        }

        try {
            // Get lab request details
            $query = "SELECT r.*, 
                    DATE_FORMAT(r.request_date, '%M %d, %Y %h:%i %p') as request_date_formatted,
                    c.consultation_id,
                    d.first_name as doctor_firstname, d.last_name as doctor_lastname,
                    p.first_name as patient_firstname, p.last_name as patient_lastname,
                    p.patient_number
                    FROM lab_test_requests r
                    JOIN consultations c ON r.consultation_id = c.consultation_id
                    JOIN patients pt ON c.patient_id = pt.patient_id
                    JOIN users p ON pt.user_id = p.user_id
                    LEFT JOIN staff s ON r.doctor_id = s.staff_id
                    LEFT JOIN users d ON s.user_id = d.user_id
                    WHERE r.request_id = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->sendError('Lab request not found');
                return;
            }

            $request = $result->fetch_assoc();

            // Check if the user has permission to view these results
            if (!$this->userHasPermissionForLabRequest($request)) {
                $this->sendError('You do not have permission to view these lab results', 403);
                return;
            }

            // Get test items with results
            $query = "SELECT i.*, 
                    t.test_name, t.category, t.normal_range as test_normal_range, t.units as test_units,
                    rf.reference_range_min, rf.reference_range_max, rf.unit as reference_unit,
                    CASE 
                        WHEN i.result IS NOT NULL AND rf.reference_range_min IS NOT NULL AND rf.reference_range_max IS NOT NULL 
                        THEN (
                            CASE 
                                WHEN CAST(i.result AS DECIMAL(10,2)) < rf.reference_range_min THEN 'low'
                                WHEN CAST(i.result AS DECIMAL(10,2)) > rf.reference_range_max THEN 'high'
                                ELSE 'normal'
                            END
                        )
                        ELSE NULL
                    END as result_flag
                    FROM lab_test_items i
                    JOIN lab_test_types t ON i.test_type_id = t.test_type_id
                    LEFT JOIN lab_reference_ranges rf ON t.test_type_id = rf.test_type_id
                    WHERE i.request_id = ?
                    ORDER BY t.category, t.test_name";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $testItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Group tests by category
            $categorizedTests = [];
            foreach ($testItems as $item) {
                $category = $item['category'];
                if (!isset($categorizedTests[$category])) {
                    $categorizedTests[$category] = [];
                }
                $categorizedTests[$category][] = $item;
            }

            // Return success response
            $this->sendResponse([
                'success' => true,
                'request' => $request,
                'test_items' => $testItems,
                'categorized_tests' => $categorizedTests
            ]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving lab results: ' . $e->getMessage());
        }
    }

    /**
     * Check if the current user has permission to view the lab request
     *
     * @param array $request The lab request data
     * @return bool True if user has permission, false otherwise
     */
    private function userHasPermissionForLabRequest(array $request): bool
    {
        // Admin and lab technician can view all lab requests
        if (in_array($this->userRole, ['admin', 'lab_technician'])) {
            return true;
        }

        // Doctors can view lab requests they created or for their patients
        if ($this->userRole === 'doctor') {
            $doctorId = $this->getStaffId();
            if ($request['doctor_id'] == $doctorId) {
                return true;
            }

            // Check if this patient is assigned to this doctor
            try {
                $query = "SELECT 1 FROM patient_doctor_assignments 
                         WHERE doctor_id = ? AND patient_id = (
                             SELECT patient_id FROM consultations 
                             WHERE consultation_id = ?
                         )";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ii", $doctorId, $request['consultation_id']);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    return true;
                }
            } catch (Exception) {
                // On error, fallback to false for safety
                return false;
            }
        }

        // Patients can view their own lab results
        if ($this->userRole === 'patient') {
            try {
                $query = "SELECT 1 FROM consultations c
                         JOIN patients p ON c.patient_id = p.patient_id
                         WHERE c.consultation_id = ? AND p.user_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ii", $request['consultation_id'], $this->userId);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    return true;
                }
            } catch (Exception) {
                return false;
            }
        }

        return false;
    }
}
