<?php

/**
 * Vital Sign Model
 * Handles all database operations related to vital signs
 */

require_once __DIR__ . '/BaseModel.php';

class VitalSignModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'vital_signs';
        $this->primaryKey = 'vital_id';
    }

    /**
     * Create a new vital sign record
     *
     * @param array $data Vital sign data
     * @return int|bool Last insert ID or false on failure
     */
    public function createVitalSign($data)
    {
        try {
            // Set created_at if not provided
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            return $this->create($data);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get vital sign by ID
     *
     * @param int $id Vital sign ID
     * @return array|null Vital sign data or null if not found
     */
    public function getVitalSignById($id)
    {
        try {
            $sql = "SELECT v.*, 
                           CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                           CONCAT(r_user.first_name, ' ', r_user.last_name) as recorded_by_name
                    FROM vital_signs v
                    JOIN patients p ON v.patient_id = p.patient_id
                    JOIN users p_user ON p.user_id = p_user.user_id
                    LEFT JOIN users r_user ON v.recorded_by = r_user.user_id
                    WHERE v.vital_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $id);
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
     * Get vital signs by patient
     *
     * @param int $patientId Patient ID
     * @param int $limit Optional limit
     * @return array Vital signs data
     */
    public function getVitalSignsByPatient($patientId, $limit = null)
    {
        try {
            $sql = "SELECT v.*, 
                           CONCAT(r_user.first_name, ' ', r_user.last_name) as recorded_by_name
                    FROM vital_signs v
                    LEFT JOIN users r_user ON v.recorded_by = r_user.user_id
                    WHERE v.patient_id = ?
                    ORDER BY v.measured_at DESC, v.vital_id DESC";

            if ($limit !== null) {
                $sql .= " LIMIT ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ii", $patientId, $limit);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $patientId);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $vitalSigns = [];
            while ($row = $result->fetch_assoc()) {
                $vitalSigns[] = $row;
            }

            return $vitalSigns;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get latest vital sign for a patient
     *
     * @param int $patientId Patient ID
     * @return array|null Latest vital sign data or null if none found
     */
    public function getLatestVitalSignByPatient($patientId)
    {
        try {
            $vitalSigns = $this->getVitalSignsByPatient($patientId, 1);
            return empty($vitalSigns) ? null : $vitalSigns[0];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Update vital sign record
     *
     * @param int $id Vital sign ID
     * @param array $data Vital sign data
     * @return bool Success status
     */
    public function updateVitalSign($id, $data)
    {
        try {
            // Set updated_at if not provided
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            return $this->update($id, $data);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete vital sign record
     *
     * @param int $id Vital sign ID
     * @return bool Success status
     */
    public function deleteVitalSign($id)
    {
        try {
            return $this->delete($id);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
}
