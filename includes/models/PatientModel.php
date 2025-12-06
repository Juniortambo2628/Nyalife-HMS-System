<?php

/**
 * Nyalife HMS - Patient Model
 *
 * Model for handling patient data.
 */

require_once __DIR__ . '/BaseModel.php';

class PatientModel extends BaseModel
{
    protected $table = 'patients';
    protected $primaryKey = 'patient_id';

    /**
     * Get patient by ID
     *
     * @param int $id Patient ID
     * @return array|null Patient data or null if not found
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $patient = $result->fetch_assoc();
            $stmt->close();

            return $patient;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get patient by user ID
     *
     * @param int $userId User ID
     * @return array|null Patient data or null if not found
     */
    public function getByUserId($userId)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $patient = $result->fetch_assoc();
            $stmt->close();

            return $patient;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get patient by patient number
     *
     * @param string $patientNumber Patient number
     * @return array|null Patient data or null if not found
     */
    public function getByPatientNumber($patientNumber)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE patient_number = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('s', $patientNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            $patient = $result->fetch_assoc();
            $stmt->close();

            return $patient;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get patient with user data
     *
     * @param int $patientId Patient ID
     * @return array|null Patient data with user details or null if not found
     */
    public function getWithUserData($patientId)
    {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, 
                          u.date_of_birth, u.address, u.username, u.is_active
                    FROM {$this->table} p 
                    JOIN users u ON p.user_id = u.user_id 
                    WHERE p.patient_id = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            $patient = $result->fetch_assoc();
            $stmt->close();

            return $patient;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get all patients with their user data
     *
     * @param int $limit Optional limit for pagination
     * @param int $offset Optional offset for pagination
     * @return array Array of patients with user details
     */
    public function getAllPatientsWithUserData($limit = null, $offset = 0)
    {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, 
                          u.date_of_birth, u.address, u.username, u.is_active,
                          CASE 
                              WHEN u.username LIKE 'guest_%' THEN 'Guest'
                              ELSE 'Internal'
                          END AS source_label
                    FROM {$this->table} p 
                    JOIN users u ON p.user_id = u.user_id 
                    ORDER BY p.created_at DESC, p.patient_id DESC";

            if ($limit !== null) {
                $sql .= " LIMIT ? OFFSET ?";
            }

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            if ($limit !== null) {
                $stmt->bind_param('ii', $limit, $offset);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $patients = [];

            while ($row = $result->fetch_assoc()) {
                $patients[] = $row;
            }

            $stmt->close();
            return $patients;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Search patients
     *
     * @param string $searchTerm Search term
     * @param int $limit Result limit
     * @param int $offset Result offset
     * @return array Search results
     */
    public function searchPatients($searchTerm, $limit = 10, $offset = 0)
    {
        try {
            $searchTerm = '%' . $searchTerm . '%';

            $sql = "SELECT p.*, u.first_name, u.last_name, u.gender, u.date_of_birth 
                    FROM {$this->table} p 
                    JOIN users u ON p.user_id = u.user_id 
                    WHERE p.patient_number LIKE ? 
                       OR u.first_name LIKE ? 
                       OR u.last_name LIKE ? 
                       OR u.email LIKE ? 
                       OR u.phone LIKE ?
                    ORDER BY u.last_name ASC, u.first_name ASC
                    LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('sssssii', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            $patients = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $patients;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get patient's medical history
     *
     * @param int $patientId Patient ID
     * @return array Medical history records
     */
    public function getMedicalHistory($patientId)
    {
        try {
            $sql = "SELECT mh.*, u.first_name, u.last_name
                    FROM medical_history mh
                    JOIN users u ON mh.recorded_by = u.user_id
                    WHERE mh.patient_id = ?
                    ORDER BY mh.created_at DESC";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            $history = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $history;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Add medical history record
     *
     * @param array $data Medical history data
     * @return int|false Record ID or false on failure
     */
    public function addMedicalHistory($data)
    {
        try {
            // Ensure required fields are present
            if (!isset($data['patient_id']) || !isset($data['description']) || !isset($data['created_by'])) {
                throw new Exception("Missing required fields for medical history");
            }

            // Set default date if not provided
            if (!isset($data['date_recorded'])) {
                $data['date_recorded'] = date('Y-m-d H:i:s');
            }

            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');

            $sql = "INSERT INTO medical_history (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            // Bind parameters
            $types = '';
            $bindParams = [];

            foreach ($data as $key => $value) {
                if (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } elseif (is_string($value)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $bindParams[] = &$data[$key];
            }

            array_unshift($bindParams, $types);
            call_user_func_array($stmt->bind_param(...), $bindParams);

            $stmt->execute();
            $insertId = $stmt->insert_id;
            $stmt->close();

            return $insertId;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Create a new patient
     *
     * @param array $userData User data
     * @param array $patientData Patient-specific data
     * @return int|false Patient ID or false on failure
     */
    public function createPatient($userData, $patientData)
    {
        try {
            // Start transaction
            $this->db->begin_transaction();

            // First create user
            $userModel = new UserModel();
            $userId = $userModel->createUser($userData, false); // Don't manage transaction internally

            if (!$userId) {
                $this->db->rollback();
                throw new Exception("Failed to create user record");
            }

            // Add user ID to patient data
            $patientData['user_id'] = $userId;

            // Generate patient number if not provided
            if (!isset($patientData['patient_number']) || empty($patientData['patient_number'])) {
                $patientData['patient_number'] = $this->generatePatientNumber();
            }

            // Map fields to match database schema
            if (isset($patientData['medical_conditions'])) {
                $patientData['chronic_diseases'] = $patientData['medical_conditions'];
                unset($patientData['medical_conditions']);
            }

            if (isset($patientData['emergency_contact_name'])) {
                $patientData['emergency_name'] = $patientData['emergency_contact_name'];
                unset($patientData['emergency_contact_name']);
            }

            if (isset($patientData['emergency_contact_phone'])) {
                $patientData['emergency_contact'] = $patientData['emergency_contact_phone'];
                unset($patientData['emergency_contact_phone']);
            }

            // Remove registration_date if it exists
            if (isset($patientData['registration_date'])) {
                unset($patientData['registration_date']);
            }

            // Create patient record
            $patientId = $this->create($patientData);

            if (!$patientId) {
                // Rollback on failure
                $this->db->rollback();
                throw new Exception("Failed to create patient record");
            }

            // Commit transaction
            $this->db->commit();

            return $patientId;
        } catch (Exception $e) {
            // Rollback on any exception
            $this->db->rollback();
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update a patient's profile
     *
     * @param int $patientId Patient ID
     * @param array $userData User data to update
     * @param array $patientData Patient data to update
     * @return bool Success status
     */
    public function updatePatient($patientId, $userData = [], $patientData = []): bool
    {
        try {
            // Start transaction
            $this->db->begin_transaction();

            // Get patient record to get user_id
            $patient = $this->find($patientId);

            if ($patient === null || $patient === []) {
                throw new Exception("Patient not found");
            }

            // Update user data if provided
            if (!empty($userData)) {
                $userModel = new UserModel();
                $userUpdated = $userModel->updateProfile($patient['user_id'], $userData);

                if (!$userUpdated) {
                    // Rollback on failure
                    $this->db->rollback();
                    throw new Exception("Failed to update user record");
                }
            }

            // Update patient data if provided
            if (!empty($patientData)) {
                $patientUpdated = $this->update($patientId, $patientData);

                if (!$patientUpdated) {
                    // Rollback on failure
                    $this->db->rollback();
                    throw new Exception("Failed to update patient record");
                }
            }

            // Commit transaction
            $this->db->commit();

            return true;
        } catch (Exception $e) {
            // Rollback on any exception
            $this->db->rollback();
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Generate a unique patient number
     *
     * @return string Patient number
     */
    private function generatePatientNumber(): string
    {
        $prefix = 'PT';
        $year = date('Y');

        // Get the last patient number with this prefix and year
        $sql = "SELECT patient_number FROM {$this->table} 
                WHERE patient_number LIKE '{$prefix}{$year}%'
                ORDER BY patient_id DESC LIMIT 1";

        $result = $this->db->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastNumber = $row['patient_number'];

            // Extract the sequence number and increment
            $sequence = (int)substr($lastNumber, strlen($prefix) + strlen($year));
            $sequence++;
        } else {
            // Start with 1 if no existing numbers
            $sequence = 1;
        }

        // Format sequence with leading zeros (6 digits)
        $formattedSequence = str_pad((string)$sequence, 6, '0', STR_PAD_LEFT);

        return $prefix . $year . $formattedSequence;
    }

    /**
     * Get all patients with user data
     *
     * @return array Array of patients
     */
    public function getAllPatients()
    {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender, 
                          u.date_of_birth, u.address, u.username, u.is_active,
                          CASE 
                              WHEN u.role_id = 6 OR u.username LIKE 'guest_%' THEN 'Guest'
                              ELSE 'Internal'
                          END AS source_label
                    FROM {$this->table} p 
                    JOIN users u ON p.user_id = u.user_id 
                    ORDER BY u.last_name ASC, u.first_name ASC";
            $result = $this->db->query($sql);

            if (!$result) {
                throw new Exception("Query execution failed: " . $this->db->error);
            }

            $patients = [];
            while ($row = $result->fetch_assoc()) {
                $patients[] = $row;
            }

            return $patients;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get patient ID by user ID
     *
     * @param int $userId User ID
     * @return int|null Patient ID or null if not found
     */
    public function getPatientIdByUserId($userId)
    {
        $patient = $this->getByUserId($userId);
        return $patient ? $patient['patient_id'] : null;
    }

    /**
     * Get recent patients who have appointments with a specific doctor
     *
     * @param int $doctorId Doctor's staff ID
     * @param int $limit Maximum number of patients to return
     * @return array Array of recent patients
     */
    public function getRecentPatientsByDoctor($doctorId, $limit = 5)
    {
        try {
            $sql = "SELECT DISTINCT p.*, u.first_name, u.last_name, u.email, u.phone, u.gender,
                           MAX(a.appointment_date) as last_appointment_date
                    FROM {$this->table} p
                    JOIN users u ON p.user_id = u.user_id
                    JOIN appointments a ON p.patient_id = a.patient_id
                    WHERE a.doctor_id = ?
                    GROUP BY p.patient_id
                    ORDER BY last_appointment_date DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ii', $doctorId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $patients = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $patients;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get the count of unique patients who have appointments with a specific doctor
     *
     * @param int $doctorId Doctor's staff ID
     * @return int Number of unique patients
     */
    public function getPatientCountByDoctor($doctorId): int
    {
        try {
            $sql = "SELECT COUNT(DISTINCT p.patient_id) as patient_count
                    FROM {$this->table} p
                    JOIN appointments a ON p.patient_id = a.patient_id
                    WHERE a.doctor_id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $doctorId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int)$row['patient_count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }
}
