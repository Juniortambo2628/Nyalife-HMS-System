<?php
/**
 * Lab Test Model
 * Handles all database operations related to lab tests
 */
class LabTestModel extends BaseModel {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'lab_tests';
        $this->primaryKey = 'test_id';
    }
    
    /**
     * Get all lab test types
     * 
     * @return array Array of test types
     */
    public function getAllTestTypes() {
        try {
            $sql = "SELECT * FROM lab_test_types ORDER BY test_name ASC";
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
     * Get lab test type by ID
     * 
     * @param int $testTypeId Test type ID
     * @return array|null Test type data or null if not found
     */
    public function getTestTypeById($testTypeId) {
        try {
            $sql = "SELECT * FROM lab_test_types WHERE type_id = ?";
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
    public function createTestRequest($data) {
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
    public function getRequestById($requestId) {
        try {
            $sql = "SELECT r.*, 
                           t.test_name, t.description as test_description, t.normal_range,
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           CONCAT(d_user.first_name, ' ', d_user.last_name) as doctor_name,
                           CONCAT(req_user.first_name, ' ', req_user.last_name) as requested_by_name,
                           CONCAT(proc_user.first_name, ' ', proc_user.last_name) as processed_by_name
                   FROM lab_requests r
                   JOIN lab_test_types t ON r.test_type_id = t.type_id
                   JOIN patients p ON r.patient_id = p.patient_id
                   JOIN users p_user ON p.user_id = p_user.user_id
                   LEFT JOIN doctors d ON r.doctor_id = d.doctor_id
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
    public function updateRequest($requestId, $data) {
        try {
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            
            return $this->update($requestId, $data, 'request_id');
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
    public function processTest($requestId, $data) {
        try {
            $this->db->begin_transaction();
            
            // Update request status
            $requestData = [
                'status' => 'completed',
                'processed_by' => $data['processed_by'],
                'processed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $requestUpdateSuccess = $this->update($requestId, $requestData, 'request_id');
            
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
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Get pending lab tests
     * 
     * @return array Array of pending tests
     */
    public function getPendingTests() {
        try {
            $sql = "SELECT r.*, 
                           t.test_name, 
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           CONCAT(req_user.first_name, ' ', req_user.last_name) as requested_by_name
                   FROM lab_requests r
                   JOIN lab_test_types t ON r.test_type_id = t.type_id
                   JOIN patients p ON r.patient_id = p.patient_id
                   JOIN users p_user ON p.user_id = p_user.user_id
                   JOIN users req_user ON r.requested_by = req_user.user_id
                   WHERE r.status = 'pending'
                   ORDER BY 
                        CASE 
                            WHEN r.priority = 'urgent' THEN 1
                            WHEN r.priority = 'stat' THEN 2
                            ELSE 3
                        END,
                        r.request_date ASC";
            
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
     * Get completed lab tests
     * 
     * @param int $limit Optional limit of results
     * @return array Array of completed tests
     */
    public function getCompletedTests($limit = null) {
        try {
            $sql = "SELECT r.*, 
                           t.test_name, 
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           CONCAT(proc_user.first_name, ' ', proc_user.last_name) as performed_by_name
                   FROM lab_requests r
                   JOIN lab_test_types t ON r.test_type_id = t.type_id
                   JOIN patients p ON r.patient_id = p.patient_id
                   JOIN users p_user ON p.user_id = p_user.user_id
                   JOIN users proc_user ON r.processed_by = proc_user.user_id
                   WHERE r.status = 'completed'
                   ORDER BY r.processed_at DESC";
            
            if ($limit !== null) {
                $sql .= " LIMIT " . intval($limit);
            }
            
            $result = $this->db->query($sql);
            
            if (!$result) {
                throw new Exception("Query execution failed: " . $this->db->error);
            }
            
            $completedTests = [];
            while ($row = $result->fetch_assoc()) {
                $completedTests[] = $row;
            }
            
            return $completedTests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get test results for a specific request
     * 
     * @param int $requestId Request ID
     * @return array|null Test results or null if not found
     */
    public function getTestResults($requestId) {
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
    public function getPatientTests($patientId) {
        try {
            $sql = "SELECT r.*, 
                           t.test_name,
                           CONCAT(req_user.first_name, ' ', req_user.last_name) as requested_by_name
                   FROM lab_requests r
                   JOIN lab_test_types t ON r.test_type_id = t.type_id
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
    private function generateRequestNumber() {
        $prefix = 'LAB';
        $year = date('Y');
        $month = date('m');
        
        // Get the last request number with this prefix and year/month
        $sql = "SELECT request_number FROM lab_requests 
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
        $formattedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $year . $month . $formattedSequence;
    }
}
