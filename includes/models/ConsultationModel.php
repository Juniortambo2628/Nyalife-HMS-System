<?php

/**
 * Consultation Model
 * Handles database operations for consultations
 */
class ConsultationModel extends BaseModel
{
    /** @var string */
    protected $table = 'consultations';
    
    /** @var string */
    protected $primaryKey = 'consultation_id';

    // Status constants
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    /**
     * Get consultation by ID with related data
     *
     * @param int $id Consultation ID
     * @return array|false Consultation data or false if not found
     */
    public function getConsultationById(int $id)
    {
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
                LEFT JOIN staff s ON s.staff_id = c.doctor_id
                LEFT JOIN users d ON d.user_id = s.user_id
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
    public function getConsultationsByPatient(int $patientId): array
    {
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
    public function getConsultations(array $filters = []): array
    {
        try {
            // Start building the query
            $sql = "SELECT 
                        c.consultation_id as id,
                        c.patient_id,
                        c.doctor_id,
                        c.consultation_date,
                        c.chief_complaint,
                        c.history_present_illness,
                        c.past_medical_history,
                        c.family_history,
                        c.social_history,
                        c.obstetric_history,
                        c.gynecological_history,
                        c.menstrual_history,
                        c.contraceptive_history,
                        c.sexual_history,
                        c.review_of_systems,
                        c.vital_signs,
                        c.physical_examination,
                        c.diagnosis,
                        c.treatment_plan,
                        c.follow_up_instructions,
                        c.notes,
                        c.consultation_status as status,
                        c.is_walk_in,
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
                        
                        -- Format date for display
                        DATE_FORMAT(c.consultation_date, '%Y-%m-%d') as formatted_date
                        
                    FROM consultations c
                    INNER JOIN patients pt ON pt.patient_id = c.patient_id
                    INNER JOIN users p ON p.user_id = pt.user_id
                    -- doctor_id in consultations references staff.staff_id; join via staff then users
                    INNER JOIN staff s ON s.staff_id = c.doctor_id
                    INNER JOIN users d ON d.user_id = s.user_id
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
                $sql .= " AND c.consultation_status = ?";
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
            $sql .= " ORDER BY c.consultation_date DESC";

            $stmt = $this->db->prepare($sql);

            // Bind parameters if any
            if ($params !== []) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $consultations = [];

            while ($row = $result->fetch_assoc()) {
                // Process and enhance the data
                $row['formatted_date'] = date('M j, Y', strtotime($row['consultation_date']));
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
    public function getConsultationByAppointment(int $appointmentId)
    {
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
    public function getStatusClass(string $status): string
    {
        return match ($status) {
            self::STATUS_SCHEDULED => 'badge bg-primary',
            self::STATUS_IN_PROGRESS => 'badge bg-warning text-dark',
            self::STATUS_COMPLETED => 'badge bg-success',
            self::STATUS_CANCELLED => 'badge bg-danger',
            self::STATUS_NO_SHOW => 'badge bg-secondary',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Truncate text to a specific length and add ellipsis if needed
     *
     * @param string $text The text to truncate
     * @param int $length Maximum length before truncation
     * @return string Truncated text with ellipsis if needed
     */
    public function truncateText(string $text, int $length = 100): string
    {
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
    public function deleteConsultation(int $id): bool
    {
        $sql = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /**
     * Create a new consultation record
     *
     * @param array $data Key-value pairs for the consultation columns
     * @return int|false   Insert ID on success, false on failure
     */
    public function createConsultation(array $data)
    {
        try {
            // Debug - Log the incoming data
            error_log("ConsultationModel::createConsultation - Incoming data: " . json_encode($data));

            // Encode vital signs as JSON if provided as an array
            if (isset($data['vital_signs']) && is_array($data['vital_signs'])) {
                $data['vital_signs'] = json_encode($data['vital_signs']);
            }

            // Auto-populate timestamps if the columns exist in the table
            $data['created_at'] ??= date('Y-m-d H:i:s');
            $data['updated_at'] ??= date('Y-m-d H:i:s');

            // Set is_walk_in flag if appointment_id is missing or empty
            if (empty($data['appointment_id'])) {
                $data['is_walk_in'] = 1;
                // For walk-in patients, we need to explicitly set appointment_id to NULL
                // since the database allows NULL values for this field
                $data['appointment_id'] = null;
                error_log("ConsultationModel::createConsultation - Setting is_walk_in=1 and appointment_id=NULL");
            } else {
                $data['is_walk_in'] = 0;
                error_log("ConsultationModel::createConsultation - Setting is_walk_in=0 (appointment_id is present)");
            }

            // Remove any keys with null/empty values EXCEPT appointment_id which we handle specially
            $filteredData = array_filter($data, function ($value, $key): bool {
                // Keep appointment_id even if it's null
                if ($key === 'appointment_id') {
                    return true;
                }
                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);

            // Ensure we only attempt to insert columns that exist in the consultations table
            $tableColumns = $this->getTableColumns();
            if ($tableColumns !== []) {
                $filteredData = array_intersect_key($filteredData, array_flip($tableColumns));
            }

            // Debug - Log the filtered data
            error_log("ConsultationModel::createConsultation - Filtered data: " . json_encode($filteredData));

            // Use BaseModel::create to perform the insert and return the new ID
            $result = $this->create($filteredData);
            error_log("ConsultationModel::createConsultation - Result of create: " . ($result ?: 'false'));
            return $result;
        } catch (Exception $e) {
            error_log("ConsultationModel::createConsultation - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing consultation record
     *
     * @param int $id The consultation ID to update
     * @param array $data Key-value pairs for the consultation columns
     * @return bool True on success, false on failure
     */
    public function updateConsultation($id, array $data)
    {
        try {
            // Debug - Log the incoming data
            error_log("ConsultationModel::updateConsultation - Incoming data: " . json_encode($data));

            // Encode vital signs as JSON if provided as an array
            if (isset($data['vital_signs']) && is_array($data['vital_signs'])) {
                $data['vital_signs'] = json_encode($data['vital_signs']);
            }

            // Auto-update timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Set is_walk_in flag if appointment_id is missing or empty
            if (isset($data['appointment_id']) && empty($data['appointment_id'])) {
                $data['is_walk_in'] = 1;
                $data['appointment_id'] = null;
            } elseif (isset($data['appointment_id'])) {
                $data['is_walk_in'] = 0;
            }

            // Remove any keys with null/empty values EXCEPT appointment_id which we handle specially
            $filteredData = array_filter($data, function ($value, $key): bool {
                // Keep appointment_id even if it's null
                if ($key === 'appointment_id') {
                    return true;
                }
                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);

            // Ensure we only attempt to update columns that exist in the consultations table
            $tableColumns = $this->getTableColumns();
            if ($tableColumns !== []) {
                $filteredData = array_intersect_key($filteredData, array_flip($tableColumns));
            }

            // Debug - Log the filtered data
            error_log("ConsultationModel::updateConsultation - Filtered data: " . json_encode($filteredData));

            // Use BaseModel::update to perform the update
            $result = $this->update($id, $filteredData);
            error_log("ConsultationModel::updateConsultation - Result of update: " . ($result ? 'true' : 'false'));
            return $result;
        } catch (Exception $e) {
            error_log("ConsultationModel::updateConsultation - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total count of consultations
     *
     * @return int Total consultation count
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
            error_log("ConsultationModel::getCount - Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get count of consultations by status
     *
     * @param string $status Status to count
     * @return int Count of consultations with the specified status
     */
    public function getCountByStatus(string $status): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE consultation_status = ?";
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
            error_log("ConsultationModel::getCountByStatus - Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update a specific field in a consultation
     *
     * @param int $consultationId Consultation ID
     * @param string $field Field name to update
     * @param mixed $value New value
     * @return bool Success status
     */
    public function updateField(int $consultationId, string $field, mixed $value): bool
    {
        try {
            $allowedFields = [
                'chief_complaint', 'history_present_illness', 'past_medical_history',
                'family_history', 'social_history', 'obstetric_history', 'gynecological_history',
                'menstrual_history', 'contraceptive_history', 'sexual_history',
                'review_of_systems', 'physical_examination', 'general_examination',
                'systems_examination', 'diagnosis', 'clinical_summary',
                'treatment_plan', 'follow_up_instructions', 'notes',
                'parity', 'current_pregnancy', 'past_obstetric',
                'diagnosis_confidence', 'differential_diagnosis', 'diagnostic_plan',
                'surgical_history'
            ];

            if (!in_array($field, $allowedFields)) {
                return false;
            }

            $sql = "UPDATE {$this->table} SET {$field} = ?, updated_at = NOW() WHERE {$this->primaryKey} = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('si', $value, $consultationId);
            $success = $stmt->execute();
            $stmt->close();

            if ($success) {
                // Update successful
                error_log("Consultation field updated: consultation_id=$consultationId, field=$field");
            }

            return $success;
        } catch (Exception $e) {
            error_log("ConsultationModel::updateField - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update vital signs in a consultation
     *
     * @param int $consultationId Consultation ID
     * @param array $vitalSigns Vital signs data
     * @return bool Success status
     */
    public function updateVitalSigns(int $consultationId, array $vitalSigns): bool
    {
        try {
            // Get current consultation
            $currentConsultation = $this->find($consultationId);
            if ($currentConsultation === null || $currentConsultation === []) {
                return false;
            }

            // Get current vital signs
            $currentVitalSigns = [];
            if (!empty($currentConsultation['vital_signs'])) {
                $currentVitalSigns = is_string($currentConsultation['vital_signs']) ?
                    json_decode($currentConsultation['vital_signs'], true) : $currentConsultation['vital_signs'];
            }

            // Merge with new data
            $updatedVitalSigns = array_merge($currentVitalSigns, $vitalSigns);

            // Update consultation
            $sql = "UPDATE {$this->table} SET vital_signs = ?, updated_at = NOW() WHERE {$this->primaryKey} = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $vitalSignsJson = json_encode($updatedVitalSigns);
            $stmt->bind_param('si', $vitalSignsJson, $consultationId);
            $success = $stmt->execute();
            $stmt->close();

            if ($success) {
                // Update successful
                error_log("Consultation vitals updated: consultation_id=$consultationId");
            }

            return $success;
        } catch (Exception $e) {
            error_log("ConsultationModel::updateVitalSigns - Error: " . $e->getMessage());
            return false;
        }
    }
}
