<?php
/**
 * Nyalife HMS - Lab Data Functions
 * 
 * Contains functions for retrieving and manipulating lab test data.
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get all available lab tests
 * 
 * @param string $category Optional category filter
 * @return array Array of lab tests
 */
function getAllLabTests($category = null) {
    global $conn;
    
    $query = "SELECT * FROM lab_tests WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    $query .= " ORDER BY test_name ASC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get lab test details by ID
 * 
 * @param int $testId Test ID
 * @return array|null Test details or null if not found
 */
function getLabTestById($testId) {
    global $conn;
    
    $query = "SELECT * FROM lab_tests WHERE test_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get lab test parameters by test ID
 * 
 * @param int $testId Test ID
 * @return array Array of parameters
 */
function getLabTestParameters($testId) {
    global $conn;
    
    $query = "SELECT * FROM lab_test_parameters WHERE test_id = ? ORDER BY parameter_name ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $testId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get lab test requests for a patient
 * 
 * @param int $patientId Patient ID
 * @param string $status Optional status filter
 * @param int $limit Maximum number of results to return
 * @return array Array of lab test requests
 */
function getPatientLabTests($patientId, $status = null, $limit = 50) {
    global $conn;
    
    $query = "SELECT lt.*, 
              t.test_name,
              CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
              s.specialization as doctor_specialization,
              CONCAT(p.first_name, ' ', p.last_name) as patient_name
              FROM lab_test_items lt
              JOIN lab_tests t ON lt.test_id = t.test_id
              JOIN staff s ON lt.doctor_id = s.staff_id
              JOIN users d ON s.user_id = d.user_id
              JOIN patients pa ON lt.patient_id = pa.patient_id
              JOIN users p ON pa.user_id = p.user_id
              WHERE lt.patient_id = ?";
    
    $params = [$patientId];
    $types = "i";
    
    if ($status) {
        $query .= " AND lt.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $query .= " ORDER BY lt.created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get lab test requests for a doctor
 * 
 * @param int $doctorId Doctor's staff ID
 * @param string $status Optional status filter
 * @param int $limit Maximum number of results to return
 * @return array Array of lab test requests
 */
function getDoctorLabTests($doctorId, $status = null, $limit = 50) {
    global $conn;
    
    $query = "SELECT lt.*, 
              t.test_name,
              CONCAT(p.first_name, ' ', p.last_name) as patient_name,
              p.gender, p.date_of_birth,
              pa.patient_number
              FROM lab_test_items lt
              JOIN lab_tests t ON lt.test_id = t.test_id
              JOIN patients pa ON lt.patient_id = pa.patient_id
              JOIN users p ON pa.user_id = p.user_id
              WHERE lt.doctor_id = ?";
    
    $params = [$doctorId];
    $types = "i";
    
    if ($status) {
        $query .= " AND lt.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $query .= " ORDER BY lt.created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get pending lab test requests
 * 
 * @param string $priority Optional priority filter
 * @param int $limit Maximum number of results to return
 * @return array Array of pending lab test requests
 */
function getPendingLabTests($priority = null, $limit = 50) {
    global $conn;
    
    $query = "SELECT lt.*, 
              t.test_name,
              CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
              s.specialization as doctor_specialization,
              CONCAT(p.first_name, ' ', p.last_name) as patient_name,
              pa.patient_number
              FROM lab_test_items lt
              JOIN lab_tests t ON lt.test_id = t.test_id
              JOIN staff s ON lt.doctor_id = s.staff_id
              JOIN users d ON s.user_id = d.user_id
              JOIN patients pa ON lt.patient_id = pa.patient_id
              JOIN users p ON pa.user_id = p.user_id
              WHERE lt.status = 'pending'";
    
    $params = [];
    $types = "";
    
    if ($priority) {
        $query .= " AND lt.priority = ?";
        $params[] = $priority;
        $types .= "s";
    }
    
    $query .= " ORDER BY 
                CASE 
                    WHEN lt.priority = 'urgent' THEN 1
                    WHEN lt.priority = 'high' THEN 2
                    WHEN lt.priority = 'normal' THEN 3
                    WHEN lt.priority = 'low' THEN 4
                    ELSE 5
                END,
                lt.created_at ASC
                LIMIT ?";
    
    $params[] = $limit;
    $types .= "i";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get lab test results for a specific test item
 * 
 * @param int $testItemId Test item ID
 * @return array Array of test results
 */
function getLabTestResults($testItemId) {
    global $conn;
    
    $query = "SELECT r.*, p.parameter_name, p.unit, p.reference_range
              FROM lab_test_results r
              JOIN lab_test_parameters p ON r.parameter_id = p.parameter_id
              WHERE r.test_item_id = ?
              ORDER BY p.parameter_name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $testItemId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get lab test item details
 * 
 * @param int $testItemId Test item ID
 * @return array|null Test item details or null if not found
 */
function getLabTestItem($testItemId) {
    global $conn;
    
    $query = "SELECT lt.*, 
              t.test_name, t.description as test_description,
              CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
              s.specialization as doctor_specialization,
              CONCAT(p.first_name, ' ', p.last_name) as patient_name,
              pa.patient_number
              FROM lab_test_items lt
              JOIN lab_tests t ON lt.test_id = t.test_id
              JOIN staff s ON lt.doctor_id = s.staff_id
              JOIN users d ON s.user_id = d.user_id
              JOIN patients pa ON lt.patient_id = pa.patient_id
              JOIN users p ON pa.user_id = p.user_id
              WHERE lt.test_item_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $testItemId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Create a new lab test request
 * 
 * @param array $data Test request data
 * @return int|false New test item ID or false on failure
 */
function createLabTestRequest($data) {
    global $conn;
    
    $requiredFields = ['patient_id', 'doctor_id', 'test_id', 'priority', 'notes'];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return false;
        }
    }
    
    $query = "INSERT INTO lab_test_items (
                patient_id, doctor_id, test_id, priority, 
                notes, status, created_at
              ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $status = 'pending';
    $createdAt = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "iiissss",
        $data['patient_id'],
        $data['doctor_id'],
        $data['test_id'],
        $data['priority'],
        $data['notes'],
        $status,
        $createdAt
    );
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    
    return false;
}

/**
 * Update lab test status
 * 
 * @param int $testItemId Test item ID
 * @param string $status New status
 * @return bool Success flag
 */
function updateLabTestStatus($testItemId, $status) {
    global $conn;
    
    $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
    
    if (!in_array($status, $validStatuses)) {
        return false;
    }
    
    $query = "UPDATE lab_test_items SET status = ? WHERE test_item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $testItemId);
    
    return $stmt->execute() && $stmt->affected_rows > 0;
}

/**
 * Save lab test results
 * 
 * @param int $testItemId Test item ID
 * @param array $parameters Array of parameter results
 * @param int $performedBy Staff ID of the lab technician
 * @return bool Success flag
 */
function saveLabTestResults($testItemId, $parameters, $performedBy) {
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update test status to completed
        $query = "UPDATE lab_test_items SET 
                status = 'completed', 
                performed_by = ?, 
                performed_at = ?
                WHERE test_item_id = ?";
        
        $performedAt = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $performedBy, $performedAt, $testItemId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("Test item not found or no changes made");
        }
        
        // Insert or update results for each parameter
        foreach ($parameters as $param) {
            if (!isset($param['parameter_id']) || !isset($param['result_value'])) {
                continue;
            }
            
            // Check if result already exists
            $query = "SELECT result_id FROM lab_test_results 
                    WHERE test_item_id = ? AND parameter_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $testItemId, $param['parameter_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $remarks = isset($param['remarks']) ? $param['remarks'] : '';
            
            if ($result->num_rows > 0) {
                // Update existing result
                $row = $result->fetch_assoc();
                $resultId = $row['result_id'];
                
                $query = "UPDATE lab_test_results 
                        SET result_value = ?, remarks = ?
                        WHERE result_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssi", $param['result_value'], $remarks, $resultId);
                $stmt->execute();
            } else {
                // Insert new result
                $query = "INSERT INTO lab_test_results 
                        (test_item_id, parameter_id, result_value, remarks)
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iiss", $testItemId, $param['parameter_id'], $param['result_value'], $remarks);
                $stmt->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error saving lab test results: " . $e->getMessage());
        return false;
    }
}

/**
 * Get lab technician statistics
 * 
 * @return array Statistics
 */
function getLabTechnicianStatistics() {
    global $conn;
    
    $today = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('-1 week'));
    
    // Pending tests
    $query = "SELECT COUNT(*) as count FROM lab_test_items WHERE status = 'pending'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $pendingTests = $row['count'];
    
    // Processing tests
    $query = "SELECT COUNT(*) as count FROM lab_test_items WHERE status = 'processing'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $processingTests = $row['count'];
    
    // Completed tests today
    $query = "SELECT COUNT(*) as count FROM lab_test_items 
              WHERE status = 'completed' AND DATE(performed_at) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $completedToday = $row['count'];
    
    // Completed tests this week
    $query = "SELECT COUNT(*) as count FROM lab_test_items 
              WHERE status = 'completed' AND performed_at >= ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $weekStart);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $completedWeek = $row['count'];
    
    // Get urgent pending tests count
    $query = "SELECT COUNT(*) as count FROM lab_test_items 
              WHERE status = 'pending' AND priority = 'urgent'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $urgentPending = $row['count'];
    
    // Test types breakdown
    $query = "SELECT lt.test_id, t.test_name, COUNT(*) as count
              FROM lab_test_items lt
              JOIN lab_tests t ON lt.test_id = t.test_id
              WHERE lt.status = 'completed' AND lt.performed_at >= ?
              GROUP BY lt.test_id
              ORDER BY count DESC
              LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $weekStart);
    $stmt->execute();
    $result = $stmt->get_result();
    $testTypes = $result->fetch_all(MYSQLI_ASSOC);
    
    // Total tests
    $query = "SELECT COUNT(*) as count FROM lab_test_items";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalTests = $row['count'];
    
    return [
        'pending_tests' => $pendingTests,
        'processing_tests' => $processingTests,
        'completed_today' => $completedToday,
        'completed_week' => $completedWeek,
        'urgent_pending' => $urgentPending,
        'test_types' => $testTypes,
        'total_tests' => $totalTests
    ];
} 