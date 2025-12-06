<?php

/**
 * Nyalife HMS - Medical History Model
 *
 * Model for handling medical history data.
 */

require_once __DIR__ . '/BaseModel.php';

class MedicalHistoryModel extends BaseModel
{
    protected $table = 'medical_history';
    protected $primaryKey = 'history_id';

    /**
     * Get medical history for a patient
     *
     * @param int $patientId Patient ID
     * @param array $filters Optional filters
     * @return array Array of medical history records
     */
    public function getPatientMedicalHistory($patientId, array $filters = [])
    {
        try {
            $sql = "SELECT mh.*, 
                    CONCAT(u.first_name, ' ', u.last_name) as recorded_by_name
                    FROM {$this->table} mh
                    LEFT JOIN users u ON mh.recorded_by = u.user_id
                    WHERE mh.patient_id = ?";

            $params = [$patientId];

            // Add filters
            if (!empty($filters['history_type'])) {
                $sql .= " AND mh.history_type = ?";
                $params[] = $filters['history_type'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND mh.date_occurred >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND mh.date_occurred <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY mh.date_occurred DESC, mh.created_at DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            // $params always has at least $patientId (line 32), so always bind
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);

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
     * @return int|bool Last insert ID or false on failure
     */
    public function addMedicalHistory($data)
    {
        try {
            // Validate required fields
            $required = ['patient_id', 'history_type', 'description', 'recorded_by'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Missing required field: {$field}");
                }
            }

            // Set default values
            if (!isset($data['date_occurred'])) {
                $data['date_occurred'] = date('Y-m-d');
            }

            if (!isset($data['is_ongoing'])) {
                $data['is_ongoing'] = 0;
            }

            return $this->create($data);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update medical history record
     *
     * @param int $historyId History record ID
     * @param array $data Update data
     * @return bool Success status
     */
    public function updateMedicalHistory($historyId, array $data)
    {
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');
            return $this->update($historyId, $data);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete medical history record
     *
     * @param int $historyId History record ID
     * @return bool Success status
     */
    public function deleteMedicalHistory($historyId)
    {
        try {
            return $this->delete($historyId);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get medical history by type
     *
     * @param int $patientId Patient ID
     * @param string $historyType History type
     * @return array Array of medical history records
     */
    public function getMedicalHistoryByType($patientId, $historyType)
    {
        return $this->getPatientMedicalHistory($patientId, ['history_type' => $historyType]);
    }

    /**
     * Get ongoing medical conditions
     *
     * @param int $patientId Patient ID
     * @return array Array of ongoing medical conditions
     */
    public function getOngoingConditions($patientId)
    {
        try {
            $sql = "SELECT mh.*, 
                    CONCAT(u.first_name, ' ', u.last_name) as recorded_by_name
                    FROM {$this->table} mh
                    LEFT JOIN users u ON mh.recorded_by = u.user_id
                    WHERE mh.patient_id = ? AND mh.is_ongoing = 1
                    ORDER BY mh.date_occurred DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            $conditions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $conditions;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get medical history summary for a patient
     *
     * @param int $patientId Patient ID
     * @return array Summary statistics
     */
    public function getMedicalHistorySummary($patientId)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_records,
                        COUNT(CASE WHEN is_ongoing = 1 THEN 1 END) as ongoing_conditions,
                        COUNT(CASE WHEN history_type = 'allergy' THEN 1 END) as allergies,
                        COUNT(CASE WHEN history_type = 'surgery' THEN 1 END) as surgeries,
                        COUNT(CASE WHEN history_type = 'illness' THEN 1 END) as illnesses,
                        MAX(date_occurred) as last_record_date
                    FROM {$this->table} 
                    WHERE patient_id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            $summary = $result->fetch_assoc();
            $stmt->close();

            return $summary;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get medical history types
     *
     * @return array Array of available history types
     */
    public function getHistoryTypes(): array
    {
        return [
            'surgery' => 'Surgery',
            'illness' => 'Illness',
            'injury' => 'Injury',
            'allergy' => 'Allergy',
            'immunization' => 'Immunization',
            'medication' => 'Medication',
            'family' => 'Family History',
            'pregnancy' => 'Pregnancy',
            'childbirth' => 'Childbirth'
        ];
    }
}
