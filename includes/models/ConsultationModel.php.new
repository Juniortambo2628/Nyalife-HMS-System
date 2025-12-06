<?php
/**
 * Consultation Model
 * Handles database operations for consultations
 */
class ConsultationModel extends BaseModel {
    protected $table = 'consultations';
    protected $primaryKey = 'consultation_id';
    
    // Status constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    /**
     * Get consultation by ID with related data
     * 
     * @param int $id Consultation ID
     * @return array|false Consultation data or false if not found
     */
    public function getConsultationById($id) {
        $sql = "SELECT 
                    c.*, 
                    p.first_name as patient_first_name, 
                    p.last_name as patient_last_name,
                    p.user_id as patient_user_id,
                    d.first_name as doctor_first_name, 
                    d.last_name as doctor_last_name,
                    d.user_id as doctor_user_id,
                    a.appointment_date, 
                    a.appointment_time,
                    u.email as patient_email,
                    u.phone as patient_phone,
                    u.gender as patient_gender,
                    u.date_of_birth as patient_dob,
                    pt.patient_number,
                    pt.blood_group,
                    pt.height as patient_height,
                    pt.weight as patient_weight,
                    pt.allergies,
                    pt.chronic_diseases
                FROM consultations c
                LEFT JOIN appointments a ON a.appointment_id = c.appointment_id
                LEFT JOIN patients pt ON pt.patient_id = c.patient_id
                LEFT JOIN users p ON p.user_id = pt.user_id
                LEFT JOIN users d ON d.user_id = c.doctor_id
                LEFT JOIN users u ON u.user_id = p.user_id
                WHERE c.consultation_id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        $consultation = $result->fetch_assoc();
        
        // Decode JSON fields
        if ($consultation && !empty($consultation['vital_signs'])) {
            $consultation['vital_signs'] = json_decode($consultation['vital_signs'], true);
        }
        
        return $consultation;
    }

    /**
     * Get consultations by patient ID
     * 
     * @param int $patientId Patient ID
     * @return array Array of consultations
     */
    public function getConsultationsByPatient($patientId) {
        $sql = "SELECT c.*, 
                       d.first_name as doctor_first_name, 
                       d.last_name as doctor_last_name
                FROM consultations c
                LEFT JOIN users d ON d.user_id = c.doctor_id
                WHERE c.patient_id = ?
                ORDER BY c.consultation_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $consultations = [];
        while ($row = $result->fetch_assoc()) {
            $consultations[] = $row;
        }
        
        return $consultations;
    }

    /**
     * Get all consultations with optional filters and related data
     * 
     * @param array $filters Optional filters (patient_id, doctor_id, status, date_from, date_to)
     * @return array Array of consultation records with related data
     */
    public function getConsultations($filters = []) {
        try {
            // Start building the query
            $sql = "SELECT 
                        c.consultation_id as id,
                        c.patient_id,
                        c.doctor_id,
                        c.consultation_date,
                        c.consultation_time,
                        c.chief_complaint,
                        c.medical_history,
                        c.vital_signs,
                        c.physical_examination,
                        c.diagnosis,
                        c.treatment_plan,
                        c.prescription,
                        c.notes,
                        c.status,
                        c.created_at,
                        c.updated_at,
                        
                        -- Patient info
                        p.user_id as patient_user_id,
                        p.first_name as patient_first_name,
                        p.last_name as patient_last_name,
                        p.email as patient_email,
                        p.phone as patient_phone,
                        p.date_of_birth as patient_dob,
                        p.gender as patient_gender,
                        
                        -- Doctor info
                        d.first_name as doctor_first_name,
                        d.last_name as doctor_last_name,
                        d.email as doctor_email,
                        d.phone as doctor_phone,
                        
                        -- Patient medical record
                        pt.blood_group,
                        pt.allergies,
                        pt.chronic_diseases as chronic_conditions,
                        
                        -- Calculate patient age
                        TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as patient_age,
                        
                        -- Formatted names for display
                        CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                        CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                        
                        -- Format date and time for display
                        DATE_FORMAT(c.consultation_date, '%Y-%m-%d') as formatted_date,
                        DATE_FORMAT(c.consultation_time, '%h:%i %p') as formatted_time
                        
                    FROM consultations c
                    INNER JOIN patients pt ON pt.patient_id = c.patient_id
                    INNER JOIN users p ON p.user_id = pt.user_id
                    INNER JOIN users d ON d.user_id = c.doctor_id
                    WHERE 1=1";
            
            $params = [];
            $types = '';
            
            // Apply filters
            if (!empty($filters['patient_id'])) {
                $sql .= " AND c.patient_id = ?";
                $params[] = $filters['patient_id'];
                $types .= 'i';
            }
            
            if (!empty($filters['doctor_id'])) {
                $sql .= " AND c.doctor_id = ?";
                $params[] = $filters['doctor_id'];
                $types .= 'i';
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND c.status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND c.consultation_date >= ?";
                $params[] = $filters['date_from'];
                $types .= 's';
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND c.consultation_date <= ?";
                $params[] = $filters['date_to'];
                $types .= 's';
            }
            
            // Order by most recent first
            $sql .= " ORDER BY c.consultation_date DESC, c.consultation_time DESC";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters if any
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $consultations = [];
            
            while ($row = $result->fetch_assoc()) {
                // Process and enhance the data
                $row['formatted_date'] = date('M j, Y', strtotime($row['consultation_date']));
                $row['formatted_time'] = date('g:i A', strtotime($row['consultation_time']));
                $row['patient_full_name'] = $row['patient_first_name'] . ' ' . $row['patient_last_name'];
                $row['doctor_full_name'] = 'Dr. ' . $row['doctor_first_name'] . ' ' . $row['doctor_last_name'];
                $row['status_class'] = $this->getStatusClass($row['status']);
                $row['status_label'] = ucfirst(str_replace('_', ' ', $row['status']));
                
                // Truncate long text fields for table display
                if (!empty($row['diagnosis'])) {
                    $row['diagnosis_short'] = $this->truncateText($row['diagnosis'], 50);
                }
                
                if (!empty($row['treatment_plan'])) {
                    $row['treatment_plan_short'] = $this->truncateText($row['treatment_plan'], 30);
                }
                
                $consultations[] = $row;
            }
            
            return $consultations;
            
        } catch (Exception $e) {
            error_log("Error in getConsultations(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get consultation by appointment ID
     * 
     * @param int $appointmentId The appointment ID to search for
     * @return array|false Consultation data or false if not found
     */
    public function getConsultationByAppointment($appointmentId) {
        $sql = "SELECT c.*, 
                       p.first_name as patient_first_name, 
                       p.last_name as patient_last_name,
                       d.first_name as doctor_first_name, 
                       d.last_name as doctor_last_name
                FROM consultations c
                LEFT JOIN patients pt ON pt.patient_id = c.patient_id
                LEFT JOIN users p ON p.user_id = pt.user_id
                LEFT JOIN users d ON d.user_id = c.doctor_id
                WHERE c.appointment_id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        $consultation = $result->fetch_assoc();
        
        // Decode JSON fields
        if ($consultation && !empty($consultation['vital_signs'])) {
            $consultation['vital_signs'] = json_decode($consultation['vital_signs'], true);
        }
        
        return $consultation;
    }

    /**
     * Get CSS class for consultation status
     * 
     * @param string $status The status to get the class for
     * @return string The CSS class
     */
    public function getStatusClass($status) {
        switch ($status) {
            case self::STATUS_SCHEDULED:
                return 'badge bg-primary';
            case self::STATUS_IN_PROGRESS:
                return 'badge bg-warning text-dark';
            case self::STATUS_COMPLETED:
                return 'badge bg-success';
            case self::STATUS_CANCELLED:
                return 'badge bg-danger';
            case self::STATUS_NO_SHOW:
                return 'badge bg-secondary';
            default:
                return 'badge bg-secondary';
        }
    }

    /**
     * Truncate text to a specific length and add ellipsis if needed
     * 
     * @param string $text The text to truncate
     * @param int $length Maximum length before truncation
     * @return string Truncated text with ellipsis if needed
     */
    public function truncateText($text, $length = 100) {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }

    /**
     * Delete a consultation
     * 
     * @param int $id Consultation ID
     * @return bool True on success, false on failure
     */
    public function deleteConsultation($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
