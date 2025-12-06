<?php
/**
 * Nyalife HMS - Appointment Model
 * 
 * Model for handling appointment data.
 */

require_once __DIR__ . '/BaseModel.php';

class AppointmentModel extends BaseModel {
    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';
    
    /**
     * Get appointment count for a doctor
     * 
     * @param int $doctorId Doctor's staff ID
     * @return int Number of appointments
     */
    public function getDoctorAppointmentCount($doctorId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE doctor_id = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $doctorId);
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
     * Get appointments for a doctor by status
     * 
     * @param int $doctorId Doctor's staff ID
     * @param string $status Status to filter by
     * @return array Array of appointments
     */
    public function getDoctorAppointmentsByStatus($doctorId, $status) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE doctor_id = ? AND status = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('is', $doctorId, $status);
            $stmt->execute();
            $result = $stmt->get_result();
            $appointments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $appointments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get appointments for a doctor
     * 
     * @param int $doctorId Doctor's staff ID
     * @param string $date Optional date filter (format: YYYY-MM-DD)
     * @param string $status Optional status filter
     * @return array Array of appointments
     */
    public function getDoctorAppointments($doctorId, $date = null, $status = null) {
        try {
            $query = "SELECT a.*, 
                    p.patient_number,
                    CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                    pu.gender as patient_gender, pu.date_of_birth as patient_dob
                    FROM appointments a
                    JOIN patients p ON a.patient_id = p.patient_id
                    JOIN users pu ON p.user_id = pu.user_id
                    WHERE a.doctor_id = ?";
            
            $params = [$doctorId];
            
            if ($date) {
                $query .= " AND a.appointment_date = ?";
                $params[] = $date;
            }
            
            if ($status) {
                $query .= " AND a.status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            // Bind parameters
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
                $bindParams[] = &$params[$key];
            }
            
            array_unshift($bindParams, $types);
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $appointments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $appointments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get appointments for a patient
     * 
     * @param int $patientId Patient ID
     * @param string $date Optional date filter (format: YYYY-MM-DD)
     * @param string $status Optional status filter
     * @return array Array of appointments
     */
    public function getPatientAppointments($patientId, $date = null, $status = null) {
        try {
            $query = "SELECT a.*, 
                    CONCAT(du.first_name, ' ', du.last_name) as doctor_name
                    FROM appointments a
                    JOIN users du ON a.doctor_id = du.user_id
                    WHERE a.patient_id = ?";
            
            $params = [$patientId];
            
            if ($date) {
                $query .= " AND a.appointment_date = ?";
                $params[] = $date;
            }
            
            if ($status) {
                $query .= " AND a.status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            // Bind parameters
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
                $bindParams[] = &$params[$key];
            }
            
            array_unshift($bindParams, $types);
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $appointments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $appointments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get appointments with flexible filtering.
     * Joins with patients, users (for patient names), staff (for doctor details), and users (for doctor names).
     *
     * @param array $filters Associative array of filters.
     *                       Supported filters: 'doctor_id', 'patient_id', 'appointment_date', 'status'.
     * @return array Array of appointments.
     */
    public function getAppointmentsFiltered(array $filters = []) {
        try {
            $sql = "SELECT \n                        a.*, \n                        p.patient_number,\n                        CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,\n                        pu.gender as patient_gender, \n                        pu.date_of_birth as patient_dob,\n                        CONCAT(du.first_name, ' ', du.last_name) as doctor_name,\n                        s.department as doctor_department,\n                        s.specialization as doctor_specialization\n                    FROM \n                        {$this->table} a\n                    LEFT JOIN \n                        patients p ON a.patient_id = p.patient_id\n                    LEFT JOIN \n                        users pu ON p.user_id = pu.user_id\n                    LEFT JOIN \n                        staff s ON a.doctor_id = s.staff_id  -- Assuming appointments.doctor_id is staff.staff_id\n                    LEFT JOIN \n                        users du ON s.user_id = du.user_id   -- To get doctor's name from users table via staff table\n                    WHERE 1=1"; // Start with a true condition for easier AND appending

            $params = [];
            $types = '';

            if (!empty($filters['doctor_id'])) {
                $sql .= " AND a.doctor_id = ?";
                $params[] = $filters['doctor_id'];
                $types .= 'i';
            }
            if (!empty($filters['patient_id'])) {
                $sql .= " AND a.patient_id = ?";
                $params[] = $filters['patient_id'];
                $types .= 'i';
            }
            if (!empty($filters['appointment_date'])) {
                $sql .= " AND a.appointment_date = ?";
                $params[] = $filters['appointment_date'];
                $types .= 's';
            }
            if (!empty($filters['status'])) {
                $sql .= " AND a.status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }
            // Add more filters as needed

            $sql .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                // Log the SQL query along with the error for better debugging
                throw new Exception("Query preparation failed: " . $this->db->error . " | SQL: " . $sql);
            }

            if (!empty($types) && !empty($params)) {
                // Use array_values to ensure params are passed in the correct order for splat operator
                $stmt->bind_param($types, ...array_values($params));
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $appointments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $appointments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }


    /**
     * Get appointment details by ID with all related information
     * 
     * @param int $appointmentId Appointment ID
     * @return array|null Appointment details or null if not found
     */
    public function getAppointmentDetails($appointmentId) {
        try {
            $query = "SELECT a.*, 
                    CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                    CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                    p.patient_number, p.blood_group, pu.gender as patient_gender,
                    pu.date_of_birth as patient_dob
                    FROM appointments a
                    JOIN patients p ON a.patient_id = p.patient_id
                    JOIN users pu ON p.user_id = pu.user_id
                    JOIN users du ON a.doctor_id = du.user_id
                    WHERE a.appointment_id = ?";
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $appointmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $appointment = $result->fetch_assoc();
            $stmt->close();
            
            // If appointment exists, get medical history related to this appointment
            if ($appointment) {
                // Get consultation if exists
                $consultQuery = "SELECT * FROM consultations WHERE appointment_id = ?";
                $stmt = $this->db->prepare($consultQuery);
                if ($stmt) {
                    $stmt->bind_param('i', $appointmentId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $consultation = $result->fetch_assoc();
                    $stmt->close();
                    
                    if ($consultation) {
                        $appointment['consultation'] = $consultation;
                    }
                }
                
                // Get patient medical history
                $historyQuery = "SELECT * FROM medical_history WHERE patient_id = ? ORDER BY date_occurred DESC";
                $stmt = $this->db->prepare($historyQuery);
                if ($stmt) {
                    $stmt->bind_param('i', $appointment['patient_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $medicalHistory = $result->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                    
                    $appointment['medical_history'] = $medicalHistory;
                }
            }
            
            return $appointment;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * Get available time slots for a doctor on a specific date
     * 
     * @param int $doctorId Doctor staff ID
     * @param string $date Date in YYYY-MM-DD format
     * @return array Available time slots
     */
    public function getAvailableTimeSlots($doctorId, $date) {
        try {
            // First, get the doctor's schedule for that day of week
            $dayOfWeek = date('w', strtotime($date));
            $scheduleQuery = "SELECT start_time, end_time, appointment_duration 
                            FROM doctor_schedules 
                            WHERE doctor_id = ? AND day_of_week = ?";
            
            $stmt = $this->db->prepare($scheduleQuery);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('ii', $doctorId, $dayOfWeek);
            $stmt->execute();
            $result = $stmt->get_result();
            $schedule = $result->fetch_assoc();
            $stmt->close();
            
            if (!$schedule) {
                return []; // Doctor doesn't work on this day
            }
            
            // Get existing appointments for that doctor on that date
            $appointmentsQuery = "SELECT appointment_time FROM appointments 
                                WHERE doctor_id = ? AND appointment_date = ?";
            
            $stmt = $this->db->prepare($appointmentsQuery);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('is', $doctorId, $date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $bookedTimes = [];
            while ($row = $result->fetch_assoc()) {
                $bookedTimes[] = $row['appointment_time'];
            }
            $stmt->close();
            
            // Generate available time slots
            $startTime = strtotime($schedule['start_time']);
            $endTime = strtotime($schedule['end_time']);
            $duration = $schedule['appointment_duration'] * 60; // Convert to seconds
            
            $availableSlots = [];
            for ($time = $startTime; $time < $endTime; $time += $duration) {
                $formattedTime = date('H:i:s', $time);
                if (!in_array($formattedTime, $bookedTimes)) {
                    $availableSlots[] = $formattedTime;
                }
            }
            
            return $availableSlots;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Create a new appointment
     * 
     * @param array $data Appointment data
     * @return int|bool Last insert ID or false on failure
     */
    public function createAppointment($data) {
        // Ensure created_at is set
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->create($data);
    }
    
    /**
     * Update appointment status
     * 
     * @param int $appointmentId Appointment ID
     * @param string $status New status
     * @param int $updatedBy User ID who updated the status
     * @return bool Success status
     */
    public function updateStatus($appointmentId, $status, $updatedBy) {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($appointmentId, $data);
    }
    
    /**
     * Update an appointment
     * 
     * @param int $appointmentId Appointment ID
     * @param array $data Appointment data
     * @return bool Success status
     */
    public function updateAppointment($appointmentId, $data) {
        // Set updated timestamp
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($appointmentId, $data);
    }
    
    /**
     * Cancel an appointment
     * 
     * @param int $appointmentId Appointment ID
     * @param string $reason Optional cancellation reason
     * @return bool Success status
     */
    public function cancelAppointment($appointmentId, $reason = null) {
        $data = [
            'status' => 'cancelled',
            'notes' => $reason ? ("Cancellation reason: " . $reason) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($appointmentId, $data);
    }
    
    /**
     * Add medical history to a consultation
     * 
     * @param int $appointmentId Appointment ID
     * @param array $historyData Medical history data including consultation details
     * @return int|bool The inserted ID or false on failure
     */
    public function addMedicalHistory($appointmentId, $historyData) {
        try {
            // First, check if this appointment exists
            $appointment = $this->getById($appointmentId);
            
            if (!$appointment) {
                throw new Exception("Appointment not found");
            }
            
            // Validate required fields
            if (empty($historyData['diagnosis']) || empty($historyData['treatment_plan'])) {
                throw new Exception("Diagnosis and treatment plan are required");
            }
            
            // Begin transaction
            $this->db->begin_transaction();
            
            // Insert into medical_history table
            $medicalHistorySql = "INSERT INTO medical_history 
                               (appointment_id, diagnosis, treatment_plan, notes, created_at) 
                               VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($medicalHistorySql);
            if (!$stmt) {
                throw new Exception("Failed to prepare medical history query: " . $this->db->error);
            }
            
            $stmt->bind_param("isss", 
                $appointmentId, 
                $historyData['diagnosis'], 
                $historyData['treatment_plan'], 
                $historyData['notes'] ?? ''
            );
            
            $historyResult = $stmt->execute();
            if (!$historyResult) {
                throw new Exception("Failed to save medical history: " . $stmt->error);
            }
            
            $historyId = $stmt->insert_id;
            $stmt->close();
            
            // If this is a consultation-related history entry, add consultation record
            if (isset($historyData['is_consultation']) && $historyData['is_consultation'] && !empty($historyId)) {
                if (empty($historyData['recorded_by'])) {
                    throw new Exception("Recorded by user ID is required for consultation");
                }
                
                $consultationData = [
                    'appointment_id' => $appointmentId,
                    'patient_id' => $appointment['patient_id'],
                    'doctor_id' => $appointment['doctor_id'],
                    'consultation_date' => date('Y-m-d H:i:s'),
                    'chief_complaint' => $historyData['chief_complaint'] ?? null,
                    'diagnosis' => $historyData['diagnosis'] ?? 'Pending',
                    'treatment_plan' => $historyData['treatment_plan'] ?? null,
                    'follow_up_instructions' => $historyData['follow_up_instructions'] ?? null,
                    'notes' => $historyData['consultation_notes'] ?? null,
                    'consultation_status' => 'open',
                    'created_by' => $historyData['recorded_by'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $consultSql = "INSERT INTO consultations (
                        appointment_id, patient_id, doctor_id, consultation_date, chief_complaint,
                        diagnosis, treatment_plan, follow_up_instructions, notes, consultation_status,
                        created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->db->prepare($consultSql);
                
                if (!$stmt) {
                    throw new Exception("Consultation query preparation failed: " . $this->db->error);
                }
                
                $stmt->bind_param(
                    'iiisssssssis',
                    $consultationData['appointment_id'],
                    $consultationData['patient_id'],
                    $consultationData['doctor_id'],
                    $consultationData['consultation_date'],
                    $consultationData['chief_complaint'],
                    $consultationData['diagnosis'],
                    $consultationData['treatment_plan'],
                    $consultationData['follow_up_instructions'],
                    $consultationData['notes'],
                    $consultationData['consultation_status'],
                    $consultationData['created_by'],
                    $consultationData['created_at']
                );
                
                $consultResult = $stmt->execute();
                if (!$consultResult) {
                    throw new Exception("Failed to save consultation: " . $stmt->error);
                }
                
                $consultationId = $stmt->insert_id;
                $stmt->close();
                
                // Update appointment status to completed
                $updateResult = $this->updateStatus($appointmentId, 'completed', $historyData['recorded_by']);
                if (!$updateResult) {
                    throw new Exception("Failed to update appointment status");
                }
            }
            
            // Commit the transaction if we get here
            $this->db->commit();
            return $historyId;
        } catch (Exception $e) {
            // Rollback the transaction on error
            if ($this->db) {
                $this->db->rollback();
            }
            
            // Log the error with more context
            $errorMessage = sprintf(
                'Error in %s: %s. Appointment ID: %d',
                __METHOD__,
                $e->getMessage(),
                $appointmentId
            );
            
            error_log($errorMessage);
            
            // Re-throw the exception to be handled by the caller
            throw $e;
        }
    }
    
    /**
     * Get upcoming appointments
     * 
     * @param int $doctorId Optional doctor ID to filter by
     * @param int $limit Optional limit of results
     * @return array Array of appointments
     */
    public function getUpcomingAppointments($doctorId = null, $limit = 5) {
        try {
            $query = "SELECT a.*, 
                    CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                    CONCAT(du.first_name, ' ', du.last_name) as doctor_name
                    FROM appointments a
                    JOIN patients p ON a.patient_id = p.patient_id
                    JOIN users pu ON p.user_id = pu.user_id
                    JOIN users du ON a.doctor_id = du.user_id
                    WHERE a.appointment_date >= CURDATE() 
                    AND a.status = 'scheduled'";
            
            $params = [];
            $types = '';
            
            if ($doctorId) {
                $query .= " AND a.doctor_id = ?";
                $params[] = $doctorId;
                $types .= 'i';
            }
            
            $query .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $appointments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $appointments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get today's appointments for a doctor
     * 
     * @param int $doctorId Doctor ID
     * @return array Array of today's appointments
     */
    public function getTodayAppointments($doctorId) {
        return $this->getDoctorAppointments($doctorId, date('Y-m-d'));
    }
    

    /**
     * Count appointments by status
     * 
     * @param string $status Status to count
     * @param int $doctorId Optional doctor ID to filter by. If null, counts all appointments with the status.
     * @return int Count of appointments
     */
    public function countAppointmentsByStatus($status, $doctorId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?";
            $params = [$status]; // Parameter for status
            $types = 's';     // Type for status parameter
            
            if ($doctorId !== null) {
                $query .= " AND doctor_id = ?";
                $params[] = $doctorId; // Add doctorId to params
                $types .= 'i';        // Add type for doctorId
            }
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param($types, ...$params); 
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return intval($row['count'] ?? 0); // Use null coalescing for safety
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }
    
    /**
     * Count total appointments for a doctor
     * 
     * @param int $doctorId Doctor ID
     * @return int Count of appointments
     */
    public function countDoctorAppointments($doctorId) {
        try {
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE doctor_id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $doctorId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return intval($row['count'] ?? 0);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }
    
    /**
     * Count unique patients for a doctor
     * 
     * @param int $doctorId Doctor ID
     * @return int Count of unique patients
     */
    public function countDoctorPatients($doctorId) {
        try {
            $query = "SELECT COUNT(DISTINCT patient_id) as count FROM {$this->table} WHERE doctor_id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $doctorId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return intval($row['count'] ?? 0);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }
    
    /**
     * Get all appointments for a specific date
     * 
     * @param string $date Date in Y-m-d format
     * @return array List of appointments
     */
    public function getAllAppointmentsByDate($date) {
        try {
            $sql = "SELECT a.*, 
                          CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                          CONCAT(d_user.first_name, ' ', d_user.last_name) as doctor_name
                   FROM {$this->table} a
                   JOIN patients p ON a.patient_id = p.patient_id
                   JOIN users p_user ON p.user_id = p_user.user_id
                   JOIN doctors d ON a.doctor_id = d.doctor_id
                   JOIN users d_user ON d.user_id = d_user.user_id
                   WHERE a.appointment_date = ?
                   ORDER BY a.appointment_time ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $date);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
            
            return $appointments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get upcoming appointments within a date range
     *
     * @param string $startDate Start date in Y-m-d format
     * @param int $days Number of days to include from start date
     * @return array List of upcoming appointments
     */
    public function getUpcomingAppointmentsByDateRange($startDate, $days = 7) {
        try {
            // Calculate the end date
            $endDate = date('Y-m-d', strtotime($startDate . ' + ' . $days . ' days'));
            
            $sql = "SELECT a.*, 
                          CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                          CONCAT(d_user.first_name, ' ', d_user.last_name) as doctor_name
                   FROM {$this->table} a
                   JOIN patients p ON a.patient_id = p.patient_id
                   JOIN users p_user ON p.user_id = p_user.user_id
                   JOIN doctors d ON a.doctor_id = d.doctor_id
                   JOIN users d_user ON d.user_id = d_user.user_id
                   WHERE a.appointment_date > ? AND a.appointment_date <= ?
                   AND a.status = 'scheduled'
                   ORDER BY a.appointment_date ASC, a.appointment_time ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
            
            return $appointments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get upcoming appointments for a specific doctor for the next X days
     *
     * @param int $doctorId Doctor ID
     * @param string $startDate Start date in Y-m-d format
     * @param int $days Number of days to include
     * @return array List of upcoming appointments
     */
    public function getUpcomingDoctorAppointments($doctorId, $startDate, $days = 7) {
        try {
            // Calculate the end date
            $endDate = date('Y-m-d', strtotime($startDate . ' + ' . $days . ' days'));
            
            $sql = "SELECT a.*, 
                          CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name
                   FROM {$this->table} a
                   JOIN patients p ON a.patient_id = p.patient_id
                   JOIN users p_user ON p.user_id = p_user.user_id
                   WHERE a.doctor_id = ? 
                   AND a.appointment_date > ? AND a.appointment_date <= ?
                   AND a.status = 'scheduled'
                   ORDER BY a.appointment_date ASC, a.appointment_time ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iss", $doctorId, $startDate, $endDate);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
            
            return $appointments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get past appointments for a specific patient
     *
     * @param int $patientId Patient ID
     * @param int $limit Maximum number of appointments to return
     * @return array List of past appointments
     */
    public function getPastPatientAppointments($patientId, $limit = 5) {
        try {
            $today = date('Y-m-d');
            
            $sql = "SELECT a.*, 
                          CONCAT(d_user.first_name, ' ', d_user.last_name) as doctor_name
                   FROM {$this->table} a
                   JOIN doctors d ON a.doctor_id = d.doctor_id
                   JOIN users d_user ON d.user_id = d_user.user_id
                   WHERE a.patient_id = ? 
                   AND (a.appointment_date < ? OR (a.appointment_date = ? AND a.status = 'completed'))
                   ORDER BY a.appointment_date DESC, a.appointment_time DESC
                   LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("issi", $patientId, $today, $today, $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
            
            return $appointments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get the total number of appointments in the system
     *
     * @return int Total appointment count
     */
    public function getTotalAppointmentsCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $result = $this->db->query($sql);
            
            if ($result && $row = $result->fetch_assoc()) {
                return (int)$row['count'];
            }
            
            return 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }
}