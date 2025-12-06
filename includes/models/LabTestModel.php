<?php

/**
 * Lab Test Model
 * Handles all database operations related to lab tests
 */
class LabTestModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'lab_test_requests';
        $this->primaryKey = 'request_id';
    }



    /**
     * Get lab test type by ID
     *
     * @param int $testTypeId Test type ID
     * @return array|null Test type data or null if not found
     */
    public function getTestTypeById($testTypeId)
    {
        try {
            $sql = "SELECT * FROM lab_test_types WHERE test_type_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $testTypeId);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return null;
            }

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Create a new lab test request
     *
     * @param array $data Test request data
     * @return int|bool Last insert ID or false on failure
     */
    public function createTestRequest($data)
    {
        try {
            if (!isset($data['request_date'])) {
                $data['request_date'] = date('Y-m-d');
            }

            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            $data['request_number'] = $this->generateRequestNumber();
            $data['status'] = 'pending';

            return $this->create($data);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get lab test request by ID
     *
     * @param int $requestId Request ID
     * @return array|null Request data or null if not found
     */
    public function getRequestById($requestId)
    {
        try {
            $sql = "SELECT r.*, 
                           t.name as test_name, t.description as test_description, t.normal_range,
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           CONCAT(d_user.first_name, ' ', d_user.last_name) as doctor_name,
                           CONCAT(req_user.first_name, ' ', req_user.last_name) as requested_by_name,
                           CONCAT(proc_user.first_name, ' ', proc_user.last_name) as processed_by_name
                   FROM lab_test_requests r
                   JOIN lab_tests t ON r.test_id = t.id
                   JOIN patients p ON r.patient_id = p.patient_id
                   JOIN users p_user ON p.user_id = p_user.user_id
                   LEFT JOIN staff d ON r.doctor_id = d.staff_id
                   LEFT JOIN users d_user ON d.user_id = d_user.user_id
                   JOIN users req_user ON r.requested_by = req_user.user_id
                   LEFT JOIN users proc_user ON r.processed_by = proc_user.user_id
                   WHERE r.request_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return null;
            }

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Update lab test request
     *
     * @param int $requestId Request ID
     * @param array $data Request data
     * @return bool Success status
     */
    public function updateRequest($requestId, $data)
    {
        try {
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            return $this->update($requestId, $data);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Process a lab test and record results
     *
     * @param int $requestId Request ID
     * @param array $data Results data
     * @return bool Success status
     */
    public function processTest($requestId, $data): bool
    {
        try {
            $this->db->begin_transaction();

            // Update request status
            $requestData = [
                'status' => 'completed',
                'processed_by' => $data['processed_by'],
                'processed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $requestUpdateSuccess = $this->update($requestId, $requestData);

            if (!$requestUpdateSuccess) {
                $this->db->rollback();
                return false;
            }

            // Create test result record
            $resultData = [
                'request_id' => $requestId,
                'result_value' => $data['result_value'],
                'interpretation' => $data['interpretation'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_abnormal' => $data['is_abnormal'] ?? 0,
                'created_by' => $data['processed_by'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $resultSql = "INSERT INTO lab_results 
                          (request_id, result_value, interpretation, notes, is_abnormal, created_by, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($resultSql);
            $stmt->bind_param(
                "isssiss",
                $resultData['request_id'],
                $resultData['result_value'],
                $resultData['interpretation'],
                $resultData['notes'],
                $resultData['is_abnormal'],
                $resultData['created_by'],
                $resultData['created_at']
            );

            $resultSuccess = $stmt->execute();

            if (!$resultSuccess) {
                $this->db->rollback();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get pending lab tests
     *
     * @return array Array of pending tests
     */
    public function getPendingTests()
    {
        try {
            $sql = "SELECT s.id as request_id,
                           s.sample_id,
                           t.test_name as test_name, 
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           p.patient_id,
                           p.patient_number,
                           CONCAT(u.first_name, ' ', u.last_name) as requested_by_name,
                           s.collected_date as request_date,
                           CASE WHEN s.urgent = 1 THEN 'urgent' ELSE 'routine' END as priority
                   FROM lab_samples s
                   JOIN lab_test_types t ON s.test_type_id = t.test_type_id
                   JOIN patients p ON s.patient_id = p.patient_id
                   JOIN users p_user ON p.user_id = p_user.user_id
                   LEFT JOIN users u ON s.collected_by = u.user_id
                   WHERE s.status IN ('registered', 'in_progress', 'pending_results')
                   ORDER BY 
                        CASE 
                            WHEN s.urgent = 1 THEN 1
                            ELSE 2
                        END,
                        s.collected_date ASC";

            $result = $this->db->query($sql);

            if (!$result) {
                throw new Exception("Query execution failed: " . $this->db->error);
            }

            $pendingTests = [];
            while ($row = $result->fetch_assoc()) {
                $pendingTests[] = $row;
            }

            return $pendingTests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get completed lab tests with pagination and search
     *
     * @param string $search Search term (optional)
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @return array Completed test data
     */
    public function getCompletedTests(string $search = '', $page = 1, $perPage = 10)
    {
        try {
            $offset = ($page - 1) * $perPage;
            $searchTerm = '%' . $search . '%';

            $sql = "SELECT s.*, 
                           t.test_name as test_name, 
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           p.patient_number,
                           CONCAT(u.first_name, ' ', u.last_name) as completed_by_name
                    FROM lab_samples s
                    JOIN lab_test_types t ON s.test_type_id = t.test_type_id
                    JOIN patients p ON s.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    LEFT JOIN users u ON s.completed_by = u.user_id
                    WHERE s.status = 'completed'";

            // Add search condition if search term provided
            if ($search !== '' && $search !== '0') {
                $sql .= " AND (s.sample_id LIKE ? OR 
                               p_user.first_name LIKE ? OR 
                               p_user.last_name LIKE ? OR 
                               p.patient_number LIKE ? OR
                               t.test_name LIKE ?)";

                $stmt = $this->db->prepare($sql . " ORDER BY s.completed_at DESC LIMIT ?, ?");
                $stmt->bind_param("sssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $offset, $perPage);
            } else {
                $stmt = $this->db->prepare($sql . " ORDER BY s.completed_at DESC LIMIT ?, ?");
                $stmt->bind_param("ii", $offset, $perPage);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $tests = [];
            while ($row = $result->fetch_assoc()) {
                $tests[] = $row;
            }

            return $tests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count completed tests with search filter
     *
     * @param string $search Search term (optional)
     * @return int Count of completed tests
     */
    public function countCompletedTests(?string $search = ''): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM lab_samples s
                    JOIN lab_test_types t ON s.test_type_id = t.test_type_id
                    JOIN patients p ON s.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    WHERE s.status = 'completed'";

            // Add search condition if search term provided
            if ($search !== null && $search !== '' && $search !== '0') {
                $searchTerm = '%' . $search . '%';
                $sql .= " AND (s.sample_id LIKE ? OR 
                               p_user.first_name LIKE ? OR 
                               p_user.last_name LIKE ? OR 
                               p.patient_number LIKE ? OR
                               t.test_name LIKE ?)";

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            } else {
                $stmt = $this->db->prepare($sql);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int)$row['count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Get active test types (for dropdown)
     *
     * @return array Active test types
     */
    public function getActiveTestTypes()
    {
        try {
            $sql = "SELECT * FROM lab_test_types WHERE is_active = 1 ORDER BY test_name ASC";
            $result = $this->db->query($sql);

            if (!$result) {
                throw new Exception("Query execution failed: " . $this->db->error);
            }

            $testTypes = [];
            while ($row = $result->fetch_assoc()) {
                $testTypes[] = $row;
            }

            return $testTypes;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Register a new lab sample
     *
     * @param array $data Sample data
     * @return int|bool Last insert ID or false on failure
     */
    public function registerSample($data)
    {
        try {
            $sql = "INSERT INTO lab_samples (
                        sample_id, patient_id, test_type_id, sample_type, 
                        collected_date, collected_by, collected_at, 
                        status, notes, urgent
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $urgent = isset($data['urgent']) ? 1 : 0;

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "siisssssis",
                $data['sample_id'],
                $data['patient_id'],
                $data['test_type_id'],
                $data['sample_type'],
                $data['collected_date'],
                $data['collected_by'],
                $data['collected_at'],
                $data['status'],
                $data['notes'],
                $urgent
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to register sample: " . $stmt->error);
            }

            return $stmt->insert_id;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get samples by status
     *
     * @param string $status Sample status
     * @param string $search Search term (optional)
     * @param int $page Current page number
     * @param int $perPage Records per page
     * @return array Sample data
     */
    public function getSamplesByStatus($status, string $search = '', $page = 1, $perPage = 10)
    {
        try {
            $offset = ($page - 1) * $perPage;
            $searchTerm = '%' . $search . '%';

            $sql = "SELECT s.*, 
                           t.test_name, 
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           p.patient_number
                    FROM lab_samples s
                    JOIN lab_test_types t ON s.test_type_id = t.test_type_id
                    JOIN patients p ON s.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    WHERE s.status = ?";

            // Add search condition if search term provided
            if ($search !== '' && $search !== '0') {
                $sql .= " AND (s.sample_id LIKE ? OR 
                               p_user.first_name LIKE ? OR 
                               p_user.last_name LIKE ? OR 
                               p.patient_number LIKE ? OR
                               t.test_name LIKE ?)";

                $stmt = $this->db->prepare($sql . " ORDER BY s.collected_at DESC LIMIT ?, ?");
                $stmt->bind_param("ssssssii", $status, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $offset, $perPage);
            } else {
                $stmt = $this->db->prepare($sql . " ORDER BY s.collected_at DESC LIMIT ?, ?");
                $stmt->bind_param("sii", $status, $offset, $perPage);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $samples = [];
            while ($row = $result->fetch_assoc()) {
                $samples[] = $row;
            }

            return $samples;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count samples by status
     *
     * @param string $status Sample status
     * @param string $search Search term (optional)
     * @return int Count of samples
     */
    public function countSamplesByStatus($status, ?string $search = ''): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM lab_samples s
                    JOIN lab_test_types t ON s.test_type_id = t.test_type_id
                    JOIN patients p ON s.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    WHERE s.status = ?";

            // Add search condition if search term provided
            if ($search !== null && $search !== '' && $search !== '0') {
                $searchTerm = '%' . $search . '%';
                $sql .= " AND (s.sample_id LIKE ? OR 
                               p_user.first_name LIKE ? OR 
                               p_user.last_name LIKE ? OR 
                               p.patient_number LIKE ? OR
                               t.test_name LIKE ?)";

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ssssss", $status, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("s", $status);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int)$row['count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Get sample by ID
     *
     * @param int|string $sampleId Sample ID (int) or sample_id (string)
     * @return array|null Sample data or null if not found
     */
    public function getSampleById($sampleId)
    {
        try {
            $sql = "SELECT s.*, 
                           t.test_name, t.description as test_description,
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           p.patient_number
                    FROM lab_samples s
                    JOIN lab_test_types t ON s.test_type_id = t.test_type_id
                    JOIN patients p ON s.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    WHERE s.sample_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $sampleId);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return null;
            }

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Update sample status
     *
     * @param string $sampleId Sample ID
     * @param string $status New status
     * @param string $notes Optional notes
     * @return bool Success status
     */
    public function updateSampleStatus($sampleId, $status, $notes = '')
    {
        try {
            $sql = "UPDATE lab_samples 
                    SET status = ?, 
                        notes = ?, 
                        updated_at = NOW() 
                    WHERE sample_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sss", $status, $notes, $sampleId);

            return $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Save test result
     *
     * @param array $data Result data
     * @return int|bool Last insert ID or false on failure
     */
    public function saveTestResult(array $data)
    {
        try {
            // Get the numeric sample ID from the string sample ID
            $sql = "SELECT id FROM lab_samples WHERE sample_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $data['sample_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("Sample not found: " . $data['sample_id']);
            }

            $sampleRow = $result->fetch_assoc();
            $numericSampleId = $sampleRow['id'];

            $sql = "INSERT INTO lab_results (
                        sample_id, parameter_id, result_value, 
                        recorded_by, recorded_at
                    ) VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        result_value = VALUES(result_value),
                        recorded_by = VALUES(recorded_by),
                        recorded_at = VALUES(recorded_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "iisis",
                $numericSampleId,
                $data['parameter_id'],
                $data['result_value'],
                $data['recorded_by'],
                $data['recorded_at']
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to save result: " . $stmt->error);
            }

            return $stmt->insert_id ?: true;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Mark sample as completed
     *
     * @param string $sampleId Sample ID
     * @param int $userId User who completed the sample
     * @return bool Success status
     */
    public function completeSample($sampleId, $userId)
    {
        try {
            $sql = "UPDATE lab_samples 
                    SET status = 'completed', 
                        completed_by = ?, 
                        completed_at = NOW(), 
                        updated_at = NOW() 
                    WHERE sample_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("is", $userId, $sampleId);

            return $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get test results for a specific sample
     *
     * @param string $sampleId Sample ID (string format like LTS-20250726-15B2)
     * @return array Array of test results
     */
    public function getSampleResults($sampleId)
    {
        try {
            $sql = "SELECT r.*, 
                           p.parameter_name,
                           p.unit,
                           p.reference_range
                    FROM lab_results r
                    JOIN lab_test_parameters p ON r.parameter_id = p.parameter_id
                    JOIN lab_samples s ON r.sample_id = s.id
                    WHERE s.sample_id = ?
                    ORDER BY p.sequence ASC, p.parameter_name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $sampleId);
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
     * Get test results for a specific request
     *
     * @param int $requestId Request ID
     * @return array|null Test result data
     */
    public function getTestResults($requestId)
    {
        try {
            $sql = "SELECT * FROM lab_results WHERE request_id = ? ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return null;
            }

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get lab tests for a specific patient
     *
     * @param int $patientId Patient ID
     * @return array Array of lab tests
     */
    public function getPatientTests($patientId)
    {
        try {
            $sql = "SELECT r.*, 
                           t.test_name,
                           CONCAT(req_user.first_name, ' ', req_user.last_name) as requested_by_name
                   FROM lab_test_requests r
                   JOIN lab_test_types t ON r.test_type_id = t.test_type_id
                   JOIN users req_user ON r.requested_by = req_user.user_id
                   WHERE r.patient_id = ?
                   ORDER BY r.request_date DESC, r.status ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $patientId);
            $stmt->execute();

            $result = $stmt->get_result();

            $patientTests = [];
            while ($row = $result->fetch_assoc()) {
                $patientTests[] = $row;
            }

            return $patientTests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Generate a unique request number
     *
     * @return string Request number
     */
    private function generateRequestNumber(): string
    {
        $prefix = 'LAB';
        $year = date('Y');
        $month = date('m');

        // Get the last request number with this prefix and year/month
        $sql = "SELECT request_number FROM lab_test_requests 
                WHERE request_number LIKE '{$prefix}{$year}{$month}%'
                ORDER BY request_id DESC LIMIT 1";

        $result = $this->db->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastNumber = $row['request_number'];

            // Extract the sequence number and increment
            $sequence = (int)substr($lastNumber, strlen($prefix) + strlen($year) + strlen($month));
            $sequence++;
        } else {
            // Start with 1 if no existing numbers
            $sequence = 1;
        }

        // Format sequence with leading zeros (4 digits)
        $formattedSequence = str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);

        return $prefix . $year . $month . $formattedSequence;
    }

    /**
     * Get lab tests for a specific consultation
     *
     * @param int $consultationId Consultation ID
     * @return array Array of lab tests for the consultation
     */
    public function getTestsByConsultation($consultationId)
    {
        try {
            // Check if the lab_tests table exists
            $checkTableSql = "SHOW TABLES LIKE 'lab_test_requests'";
            $tableResult = $this->db->query($checkTableSql);

            if (!$tableResult || $tableResult->num_rows === 0) {
                // Table doesn't exist, return empty array
                error_log("Lab tests tables don't exist yet. Returning empty array.");
                return [];
            }

            $sql = "SELECT r.* 
                   FROM lab_test_requests r
                   WHERE r.consultation_id = ?
                   ORDER BY r.request_date DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                error_log("Error preparing statement: " . $this->db->error);
                return [];
            }

            $stmt->bind_param("i", $consultationId);
            $stmt->execute();

            $result = $stmt->get_result();

            $tests = [];
            while ($row = $result->fetch_assoc()) {
                $tests[] = $row;
            }

            return $tests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count total test types matching search criteria
     *
     * @param string $search Search term
     * @return int Total count
     */
    public function countTestTypes($search = ''): int
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM lab_test_types";

            if (!empty($search)) {
                $sql .= " WHERE test_name LIKE ? OR description LIKE ? OR category LIKE ?";
                $searchParam = "%$search%";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
            } else {
                $stmt = $this->db->prepare($sql);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int)$row['total'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Get all test types with pagination and search
     *
     * @param string $search Search term
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Array of test types
     */
    public function getAllTestTypes($search = '', $page = 1, $perPage = 20)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT * FROM lab_test_types";

            if (!empty($search)) {
                $sql .= " WHERE test_name LIKE ? OR description LIKE ? OR category LIKE ?";
                $sql .= " ORDER BY test_name ASC LIMIT ? OFFSET ?";
                $searchParam = "%$search%";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $perPage, $offset);
            } else {
                $sql .= " ORDER BY test_name ASC LIMIT ? OFFSET ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ii", $perPage, $offset);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $testTypes = [];
            while ($row = $result->fetch_assoc()) {
                $testTypes[] = $row;
            }

            return $testTypes;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get all parameters
     *
     * @return array Array of parameters
     */
    public function getAllParameters()
    {
        try {
            $sql = "SELECT * FROM lab_test_parameters ORDER BY parameter_name ASC";
            $result = $this->db->query($sql);

            if (!$result) {
                throw new Exception("Query execution failed: " . $this->db->error);
            }

            $parameters = [];
            while ($row = $result->fetch_assoc()) {
                $parameters[] = $row;
            }

            return $parameters;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get parameters by test ID
     *
     * @param int $testId Test ID
     * @return array Array of parameters
     */
    public function getParametersByTestId($testId)
    {
        try {
            $sql = "SELECT * FROM lab_test_parameters WHERE test_id = ? ORDER BY sequence ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $testId);
            $stmt->execute();

            $result = $stmt->get_result();

            $parameters = [];
            while ($row = $result->fetch_assoc()) {
                $parameters[] = $row;
            }

            return $parameters;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Create a new test type
     *
     * @param array $data Test type data
     * @return int|bool Last insert ID or false on failure
     */
    public function createTestType($data): int|string|false
    {
        try {
            $sql = "INSERT INTO lab_test_types (test_name, description, category, price, turnaround_time, instructions_file, is_active, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $turnaroundTime = $data['turnaround_time'] ?? null;
            $instructionsFile = $data['instructions_file'] ?? null;

            $stmt->bind_param(
                "sssdssiis",
                $data['test_name'],
                $data['description'],
                $data['category'],
                $data['price'],
                $turnaroundTime,
                $instructionsFile,
                $data['is_active'],
                $data['created_by'],
                $data['created_at']
            );

            if ($stmt->execute()) {
                return $this->db->insert_id;
            }

            return false;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update a test type
     *
     * @param int $testId Test ID
     * @param array $data Test type data
     * @return bool Success status
     */
    public function updateTestType($testId, $data)
    {
        try {
            $sql = "UPDATE lab_test_types SET 
                    test_name = ?, 
                    description = ?, 
                    category = ?, 
                    price = ?, 
                    turnaround_time = ?, 
                    instructions_file = ?, 
                    is_active = ?, 
                    updated_by = ?, 
                    updated_at = ? 
                    WHERE test_type_id = ?";

            $turnaroundTime = $data['turnaround_time'] ?? null;
            $instructionsFile = $data['instructions_file'] ?? null;

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "sssdssiisi",
                $data['test_name'],
                $data['description'],
                $data['category'],
                $data['price'],
                $turnaroundTime,
                $instructionsFile,
                $data['is_active'],
                $data['updated_by'],
                $data['updated_at'],
                $testId
            );

            return $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Create a new parameter
     *
     * @param array $data Parameter data
     * @return int|bool Last insert ID or false on failure
     */
    public function createParameter(array $data): int|string|false
    {
        try {
            $sql = "INSERT INTO lab_test_parameters (test_id, parameter_name, unit, reference_range, sequence, is_active, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "isssiiss",
                $data['test_id'],
                $data['parameter_name'],
                $data['unit'],
                $data['reference_range'],
                $data['sequence'],
                $data['is_active'],
                $data['created_by'],
                $data['created_at']
            );

            if ($stmt->execute()) {
                return $this->db->insert_id;
            }

            return false;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update a parameter
     *
     * @param int $parameterId Parameter ID
     * @param array $data Parameter data
     * @return bool Success status
     */
    public function updateParameter($parameterId, array $data)
    {
        try {
            $sql = "UPDATE lab_test_parameters SET 
                    parameter_name = ?, 
                    unit = ?, 
                    reference_range = ?, 
                    sequence = ?, 
                    is_active = ?, 
                    updated_by = ?, 
                    updated_at = ? 
                    WHERE parameter_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "sssiissi",
                $data['parameter_name'],
                $data['unit'],
                $data['reference_range'],
                $data['sequence'],
                $data['is_active'],
                $data['updated_by'],
                $data['updated_at'],
                $parameterId
            );

            return $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete a parameter
     *
     * @param int $parameterId Parameter ID
     * @return bool Success status
     */
    public function deleteParameter($parameterId)
    {
        try {
            $sql = "DELETE FROM lab_test_parameters WHERE parameter_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $parameterId);

            return $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete all parameters for a test
     *
     * @param int $testId Test ID
     * @return bool Success status
     */
    public function deleteParametersByTestId($testId)
    {
        try {
            $sql = "DELETE FROM lab_test_parameters WHERE test_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $testId);

            return $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete a test type
     *
     * @param int $testId Test ID
     * @return bool Success status
     */
    public function deleteTestType($testId)
    {
        try {
            $sql = "DELETE FROM lab_test_types WHERE test_type_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $testId);

            return $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Check if test type is in use
     *
     * @param int $testId Test ID
     * @return bool True if in use
     */
    public function isTestTypeInUse($testId)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM lab_test_requests WHERE test_type_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $testId);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int)$row['count'] > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get requests by status
     *
     * @param string $status Request status
     * @param string $search Search term
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Array of requests
     */
    public function getRequestsByStatus($status, $search = '', $page = 1, $perPage = 10)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT r.*, 
                           t.test_name, 
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           CONCAT(d_user.first_name, ' ', d_user.last_name) as doctor_name
                    FROM lab_test_requests r
                    JOIN lab_test_types t ON r.test_type_id = t.test_type_id
                    JOIN patients p ON r.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    LEFT JOIN staff d ON r.doctor_id = d.staff_id
                    LEFT JOIN users d_user ON d.user_id = d_user.user_id
                    WHERE r.status = ?";

            if (!empty($search)) {
                $sql .= " AND (r.request_number LIKE ? OR p_user.first_name LIKE ? OR p_user.last_name LIKE ?)";
                $searchParam = "%$search%";
                $stmt = $this->db->prepare($sql . " ORDER BY r.request_date DESC LIMIT ?, ?");
                $stmt->bind_param("ssssii", $status, $searchParam, $searchParam, $searchParam, $offset, $perPage);
            } else {
                $stmt = $this->db->prepare($sql . " ORDER BY r.request_date DESC LIMIT ?, ?");
                $stmt->bind_param("sii", $status, $offset, $perPage);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $requests = [];
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }

            return $requests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count requests by status
     *
     * @param string $status Request status
     * @param string $search Search term
     * @return int Count
     */
    public function countRequestsByStatus($status, $search = ''): int
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM lab_test_requests r
                    JOIN patients p ON r.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    WHERE r.status = ?";

            if (!empty($search)) {
                $sql .= " AND (r.request_number LIKE ? OR p_user.first_name LIKE ? OR p_user.last_name LIKE ?)";
                $searchParam = "%$search%";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ssss", $status, $searchParam, $searchParam, $searchParam);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("s", $status);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int)$row['total'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Get requests by doctor
     *
     * @param int $doctorId Doctor ID
     * @param string $search Search term
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Array of requests
     */
    public function getRequestsByDoctor($doctorId, $search = '', $page = 1, $perPage = 10)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT r.*, 
                           t.test_name, 
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name
                    FROM lab_test_requests r
                    JOIN lab_test_types t ON r.test_type_id = t.test_type_id
                    JOIN patients p ON r.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    WHERE r.doctor_id = ?";

            if (!empty($search)) {
                $sql .= " AND (r.request_number LIKE ? OR p_user.first_name LIKE ? OR p_user.last_name LIKE ?)";
                $searchParam = "%$search%";
                $stmt = $this->db->prepare($sql . " ORDER BY r.request_date DESC LIMIT ?, ?");
                $stmt->bind_param("isssii", $doctorId, $searchParam, $searchParam, $searchParam, $offset, $perPage);
            } else {
                $stmt = $this->db->prepare($sql . " ORDER BY r.request_date DESC LIMIT ?, ?");
                $stmt->bind_param("iii", $doctorId, $offset, $perPage);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $requests = [];
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }

            return $requests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count requests by doctor
     *
     * @param int $doctorId Doctor ID
     * @param string $search Search term
     * @return int Count
     */
    public function countRequestsByDoctor($doctorId, $search = ''): int
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM lab_test_requests r
                    JOIN patients p ON r.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    WHERE r.doctor_id = ?";

            if (!empty($search)) {
                $sql .= " AND (r.request_number LIKE ? OR p_user.first_name LIKE ? OR p_user.last_name LIKE ?)";
                $searchParam = "%$search%";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("isss", $doctorId, $searchParam, $searchParam, $searchParam);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $doctorId);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int)$row['total'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Get requests by patient
     *
     * @param int $patientId Patient ID
     * @param string $search Search term
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Array of requests
     */
    public function getRequestsByPatient($patientId, $search = '', $page = 1, $perPage = 10)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT r.*, 
                           t.test_name, 
                           CONCAT(d_user.first_name, ' ', d_user.last_name) as doctor_name
                    FROM lab_test_requests r
                    JOIN lab_test_types t ON r.test_type_id = t.test_type_id
                    LEFT JOIN staff d ON r.doctor_id = d.staff_id
                    LEFT JOIN users d_user ON d.user_id = d_user.user_id
                    WHERE r.patient_id = ?";

            if (!empty($search)) {
                $sql .= " AND (r.request_number LIKE ? OR t.test_name LIKE ?)";
                $searchParam = "%$search%";
                $stmt = $this->db->prepare($sql . " ORDER BY r.request_date DESC LIMIT ?, ?");
                $stmt->bind_param("issii", $patientId, $searchParam, $searchParam, $offset, $perPage);
            } else {
                $stmt = $this->db->prepare($sql . " ORDER BY r.request_date DESC LIMIT ?, ?");
                $stmt->bind_param("iii", $patientId, $offset, $perPage);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $requests = [];
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }

            return $requests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count requests by patient
     *
     * @param int $patientId Patient ID
     * @param string $search Search term
     * @return int Count
     */
    public function countRequestsByPatient($patientId, $search = ''): int
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM lab_test_requests r
                    JOIN lab_test_types t ON r.test_type_id = t.test_type_id
                    WHERE r.patient_id = ?";

            if (!empty($search)) {
                $sql .= " AND (r.request_number LIKE ? OR t.test_name LIKE ?)";
                $searchParam = "%$search%";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("iss", $patientId, $searchParam, $searchParam);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $patientId);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int)$row['total'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Get recent tests by patient
     *
     * @param int $patientId Patient ID
     * @param int $limit Limit number of results
     * @return array Array of recent tests
     */
    public function getRecentTestsByPatient($patientId, $limit = 5)
    {
        try {
            $sql = "SELECT r.*, t.test_name, t.description
                    FROM lab_test_requests r
                    JOIN lab_test_types t ON r.test_type_id = t.test_type_id
                    WHERE r.patient_id = ?
                    ORDER BY r.request_date DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $patientId, $limit);
            $stmt->execute();

            $result = $stmt->get_result();

            $tests = [];
            while ($row = $result->fetch_assoc()) {
                $tests[] = $row;
            }

            return $tests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Update request status
     *
     * @param int $requestId Request ID
     * @param string $status New status
     * @param int $userId User ID who updated
     * @return bool Success status
     */
    public function updateRequestStatus($requestId, $status, $userId)
    {
        try {
            $sql = "UPDATE lab_test_requests SET 
                    status = ?, 
                    processed_by = ?, 
                    processed_at = ? 
                    WHERE request_id = ?";

            $processedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sisi", $status, $userId, $processedAt, $requestId);

            return $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Ensure test result records exist for a request
     *
     * @param int $requestId Request ID
     * @return bool Success status
     */
    public function ensureTestResultRecords($requestId): bool
    {
        try {
            // Get request details
            $request = $this->getRequestById($requestId);
            if (!$request) {
                return false;
            }

            // Get test parameters
            $parameters = $this->getParametersByTestId($request['test_type_id']);

            // Create result records for each parameter
            foreach ($parameters as $parameter) {
                $sql = "INSERT IGNORE INTO lab_test_results 
                        (request_id, parameter_id, result_value, recorded_by, recorded_at) 
                        VALUES (?, ?, '', ?, ?)";

                $recordedAt = date('Y-m-d H:i:s');

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("iiis", $requestId, $parameter['parameter_id'], $request['processed_by'], $recordedAt);
                $stmt->execute();
            }

            return true;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Save test results
     *
     * @param int $requestId Request ID
     * @param array $results Array of results
     * @param int $userId User ID who recorded results
     * @return bool Success status
     */
    public function saveTestResults($requestId, $results, $userId): bool
    {
        try {
            $recordedAt = date('Y-m-d H:i:s');

            foreach ($results as $parameterId => $resultValue) {
                $sql = "UPDATE lab_test_results SET 
                        result_value = ?, 
                        recorded_by = ?, 
                        recorded_at = ? 
                        WHERE request_id = ? AND parameter_id = ?";

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ssiii", $resultValue, $userId, $recordedAt, $requestId, $parameterId);
                $stmt->execute();
            }

            return true;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get total count of lab tests
     *
     * @return int Total lab test count
     */
    public function getCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int)$row['count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Get count of lab tests by status
     *
     * @param string $status Status to count
     * @return int Count of lab tests with the specified status
     */
    public function getCountByStatus($status): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('s', $status);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int)$row['count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }
}
