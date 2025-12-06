<?php
/**
 * Nyalife HMS - Patient Data Access Layer
 * 
 * This file provides standardized functions for patient data operations.
 */

require_once __DIR__ . '/../db_utils.php';
require_once __DIR__ . '/../id_generator.php';

/**
 * Get a patient by ID
 * 
 * @param string $patientId Patient ID
 * @return array|null Patient data or null if not found
 */
function getPatient($patientId) {
    return dbSelectOne(
        "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, u.date_of_birth, u.address
         FROM patients p
         JOIN users u ON p.user_id = u.user_id
         WHERE p.patient_id = ?",
        [$patientId]
    );
}

/**
 * Get patient by user ID
 * 
 * @param int $userId User ID
 * @return array|null Patient data or null if not found
 */
function getPatientByUserId($userId) {
    return dbSelectOne(
        "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, u.date_of_birth, u.address
         FROM patients p
         JOIN users u ON p.user_id = u.user_id
         WHERE p.user_id = ?",
        [$userId]
    );
}

/**
 * Get patient by patient number
 * 
 * @param string $patientNumber Patient number
 * @return array|null Patient data or null if not found
 */
function getPatientByNumber($patientNumber) {
    return dbSelectOne(
        "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, u.date_of_birth, u.address
         FROM patients p
         JOIN users u ON p.user_id = u.user_id
         WHERE p.patient_number = ?",
        [$patientNumber]
    );
}

/**
 * Get all patients
 * 
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array Patients data
 */
function getAllPatients($limit = null, $offset = null) {
    $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, u.date_of_birth
            FROM patients p
            JOIN users u ON p.user_id = u.user_id
            ORDER BY u.last_name, u.first_name";
    
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params = [$limit];
        
        if ($offset !== null) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
        }
        
        return dbSelect($sql, $params);
    }
    
    return dbSelect($sql);
}

/**
 * Count all patients
 * 
 * @return int Patient count
 */
function countPatients() {
    return dbSelectValue("SELECT COUNT(*) FROM patients");
}

/**
 * Create a new patient
 * 
 * @param array $userData User data (first_name, last_name, etc.)
 * @param array $patientData Patient data (blood_type, etc.)
 * @return string|bool New patient ID or false on failure
 */
function createPatient($userData, $patientData) {
    try {
        $db = dbBeginTransaction();
        
        // Create user account first
        $userData['role_id'] = 5; // Patient role ID
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $userId = dbInsert(
            "INSERT INTO users (
                username, password, first_name, last_name, email, 
                phone, gender, date_of_birth, address, role_id, 
                status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())",
            [
                $userData['username'],
                $userData['password'],
                $userData['first_name'],
                $userData['last_name'],
                $userData['email'],
                $userData['phone'],
                $userData['gender'],
                $userData['date_of_birth'],
                $userData['address'],
                $userData['role_id']
            ]
        );
        
        if (!$userId) {
            dbRollbackTransaction($db);
            return false;
        }
        
        // Generate patient number
        $patientNumber = generatePatientNumber();
        
        // Create patient record
        $patientId = dbInsert(
            "INSERT INTO patients (
                patient_number, user_id, blood_type, emergency_contact_name,
                emergency_contact_relation, emergency_contact_phone, registration_date
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [
                $patientNumber,
                $userId,
                $patientData['blood_type'] ?? null,
                $patientData['emergency_contact_name'] ?? null,
                $patientData['emergency_contact_relation'] ?? null,
                $patientData['emergency_contact_phone'] ?? null
            ]
        );
        
        if (!$patientId) {
            dbRollbackTransaction($db);
            return false;
        }
        
        dbCommitTransaction($db);
        return $patientId;
    } catch (Exception $e) {
        if (isset($db)) {
            dbRollbackTransaction($db);
        }
        // Log error
        if (function_exists('logDatabaseError')) {
            logDatabaseError($e->getMessage());
        }
        return false;
    }
}

/**
 * Update a patient
 * 
 * @param string $patientId Patient ID
 * @param array $userData User data to update
 * @param array $patientData Patient data to update
 * @return bool Success status
 */
function updatePatient($patientId, $userData = [], $patientData = []) {
    try {
        $db = dbBeginTransaction();
        
        // Get patient to find user ID
        $patient = getPatient($patientId);
        if (!$patient) {
            return false;
        }
        
        // Update user data if provided
        if (!empty($userData)) {
            // Remove password if it's empty
            if (isset($userData['password']) && empty($userData['password'])) {
                unset($userData['password']);
            } elseif (isset($userData['password'])) {
                // Hash password if provided
                $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }
            
            // Update user record
            $userColumns = [];
            $userParams = [];
            
            foreach ($userData as $key => $value) {
                $userColumns[] = "$key = ?";
                $userParams[] = $value;
            }
            
            if (!empty($userColumns)) {
                $userParams[] = $patient['user_id'];
                $userUpdateSuccess = dbUpdate(
                    "UPDATE users SET " . implode(', ', $userColumns) . " WHERE user_id = ?",
                    $userParams
                );
                
                if (!$userUpdateSuccess) {
                    dbRollbackTransaction($db);
                    return false;
                }
            }
        }
        
        // Update patient data if provided
        if (!empty($patientData)) {
            $patientColumns = [];
            $patientParams = [];
            
            foreach ($patientData as $key => $value) {
                $patientColumns[] = "$key = ?";
                $patientParams[] = $value;
            }
            
            if (!empty($patientColumns)) {
                $patientParams[] = $patientId;
                $patientUpdateSuccess = dbUpdate(
                    "UPDATE patients SET " . implode(', ', $patientColumns) . " WHERE patient_id = ?",
                    $patientParams
                );
                
                if (!$patientUpdateSuccess) {
                    dbRollbackTransaction($db);
                    return false;
                }
            }
        }
        
        dbCommitTransaction($db);
        return true;
    } catch (Exception $e) {
        if (isset($db)) {
            dbRollbackTransaction($db);
        }
        // Log error
        if (function_exists('logDatabaseError')) {
            logDatabaseError($e->getMessage());
        }
        return false;
    }
}

/**
 * Search for patients
 * 
 * @param string $searchTerm Search term
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array Matching patients
 */
function searchPatients($searchTerm, $limit = null, $offset = null) {
    $searchParam = "%$searchTerm%";
    
    $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, u.date_of_birth
            FROM patients p
            JOIN users u ON p.user_id = u.user_id
            WHERE p.patient_number LIKE ? 
            OR u.first_name LIKE ? 
            OR u.last_name LIKE ?
            OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
            OR u.email LIKE ?
            OR u.phone LIKE ?
            ORDER BY u.last_name, u.first_name";
    
    $params = [
        $searchParam, 
        $searchParam, 
        $searchParam, 
        $searchParam, 
        $searchParam, 
        $searchParam
    ];
    
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
        
        if ($offset !== null) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
        }
    }
    
    return dbSelect($sql, $params);
}

/**
 * Get patient appointments
 * 
 * @param string $patientId Patient ID
 * @param string $status Optional appointment status filter
 * @return array Appointments
 */
if (!function_exists('getPatientAppointments')) {
    function getPatientAppointments($patientId, $status = null) {
        $sql = "SELECT a.*, d.doctor_id, 
                CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
                dep.department_name
                FROM appointments a
                JOIN doctors d ON a.doctor_id = d.doctor_id
                JOIN users du ON d.user_id = du.user_id
                JOIN departments dep ON d.department_id = dep.department_id
                WHERE a.patient_id = ?";
        
        $params = [$patientId];
        
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        return dbSelect($sql, $params);
    }
}

/**
 * Get upcoming patient appointments
 * 
 * @param string $patientId Patient ID
 * @param int $limit Optional limit
 * @return array Upcoming appointments
 */
function getUpcomingPatientAppointments($patientId, $limit = null) {
    $sql = "SELECT a.*, d.doctor_id, 
            CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
            dep.department_name
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.doctor_id
            JOIN users du ON d.user_id = du.user_id
            JOIN departments dep ON d.department_id = dep.department_id
            WHERE a.patient_id = ?
            AND a.status IN ('scheduled', 'confirmed')
            AND (a.appointment_date > CURDATE() 
                 OR (a.appointment_date = CURDATE() AND a.appointment_time >= CURTIME()))
            ORDER BY a.appointment_date ASC, a.appointment_time ASC";
    
    $params = [$patientId];
    
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    return dbSelect($sql, $params);
}

/**
 * Get recent patient consultations
 * 
 * @param string $patientId Patient ID
 * @param int $limit Optional limit
 * @return array Recent consultations
 */
function getRecentPatientConsultations($patientId, $limit = null) {
    $sql = "SELECT c.*, 
            a.appointment_number,
            CONCAT(du.first_name, ' ', du.last_name) AS doctor_name
            FROM consultations c
            JOIN appointments a ON c.appointment_id = a.appointment_id
            JOIN doctors d ON a.doctor_id = d.doctor_id
            JOIN users du ON d.user_id = du.user_id
            WHERE a.patient_id = ?
            ORDER BY c.consultation_date DESC";
    
    $params = [$patientId];
    
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    return dbSelect($sql, $params);
}

/**
 * Get active patient prescriptions
 * 
 * @param string $patientId Patient ID
 * @return array Active prescriptions
 */
function getActivePatientPrescriptions($patientId) {
    $sql = "SELECT p.*, 
            CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
            DATE_ADD(p.prescribed_date, INTERVAL p.duration DAY) AS end_date
            FROM prescriptions p
            JOIN doctors d ON p.doctor_id = d.doctor_id
            JOIN users du ON d.user_id = du.user_id
            WHERE p.patient_id = ?
            AND p.status != 'completed'
            AND DATE_ADD(p.prescribed_date, INTERVAL p.duration DAY) >= CURDATE()
            ORDER BY p.prescribed_date DESC";
    
    return dbSelect($sql, [$patientId]);
}

/**
 * Get patient prescription items
 * 
 * @param string $prescriptionId Prescription ID
 * @return array Prescription items
 */
function getPatientPrescriptionItems($prescriptionId) {
    $sql = "SELECT pi.*, m.medication_name, m.generic_name
            FROM prescription_items pi
            JOIN medications m ON pi.medication_id = m.medication_id
            WHERE pi.prescription_id = ?";
    
    return dbSelect($sql, [$prescriptionId]);
}

/**
 * Get patient medical history
 * 
 * @param string $patientId Patient ID
 * @return array Medical history data
 */
function getPatientMedicalHistory($patientId) {
    return dbSelect(
        "SELECT * FROM medical_history WHERE patient_id = ? ORDER BY recorded_date DESC",
        [$patientId]
    );
}

/**
 * Add to patient medical history
 * 
 * @param string $patientId Patient ID
 * @param string $condition Medical condition
 * @param string $details Condition details
 * @param string $date Recorded date (YYYY-MM-DD)
 * @return int|bool ID of new record or false on failure
 */
function addPatientMedicalHistory($patientId, $condition, $details, $date = null) {
    if ($date === null) {
        $date = date('Y-m-d');
    }
    
    return dbInsert(
        "INSERT INTO medical_history (patient_id, condition_name, details, recorded_date, created_at)
         VALUES (?, ?, ?, ?, NOW())",
        [$patientId, $condition, $details, $date]
    );
}
?> 