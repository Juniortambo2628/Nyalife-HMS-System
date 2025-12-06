<?php

/**
 * Nyalife HMS - Follow-up Model
 *
 * Model for handling follow-up appointment data.
 */

require_once __DIR__ . '/BaseModel.php';

class FollowUpModel extends BaseModel
{
    protected $table = 'follow_ups';
    protected $primaryKey = 'follow_up_id';

    /**
     * Get follow-up with patient and consultation details
     *
     * @param int $followUpId Follow-up ID
     * @return array|null Follow-up data with details or null if not found
     */
    public function getFollowUpWithDetails($followUpId)
    {
        try {
            $sql = "SELECT fu.*, 
                           CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                           pu.email as patient_email, pu.phone as patient_phone,
                           CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                           c.consultation_date, c.diagnosis, c.treatment_plan,
                           CONCAT(cu.first_name, ' ', cu.last_name) as created_by_name
                           FROM {$this->table} fu
                           JOIN patients p ON fu.patient_id = p.patient_id
                           JOIN users pu ON p.user_id = pu.user_id
                           JOIN consultations c ON fu.consultation_id = c.consultation_id
                           JOIN users du ON c.doctor_id = du.user_id
                           JOIN users cu ON fu.created_by = cu.user_id
                           WHERE fu.follow_up_id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $followUpId);
            $stmt->execute();
            $result = $stmt->get_result();
            $followUp = $result->fetch_assoc();
            $stmt->close();

            return $followUp;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get follow-ups by patient
     *
     * @param int $patientId Patient ID
     * @param string $status Optional status filter
     * @return array Array of follow-ups
     */
    public function getFollowUpsByPatient($patientId, $status = null)
    {
        try {
            $sql = "SELECT fu.*, 
                           CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                           c.consultation_date, c.diagnosis
                           FROM {$this->table} fu
                           JOIN consultations c ON fu.consultation_id = c.consultation_id
                           JOIN users du ON c.doctor_id = du.user_id
                           WHERE fu.patient_id = ?";

            $params = [$patientId];

            if ($status) {
                $sql .= " AND fu.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY fu.follow_up_date DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $followUps = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $followUps;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get follow-ups by doctor
     *
     * @param int $doctorId Doctor ID
     * @param string $status Optional status filter
     * @return array Array of follow-ups
     */
    public function getFollowUpsByDoctor($doctorId, $status = null)
    {
        try {
            $sql = "SELECT fu.*, 
                           CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                           pu.phone as patient_phone,
                           c.consultation_date, c.diagnosis
                           FROM {$this->table} fu
                           JOIN consultations c ON fu.consultation_id = c.consultation_id
                           JOIN patients p ON fu.patient_id = p.patient_id
                           JOIN users pu ON p.user_id = pu.user_id
                           WHERE c.doctor_id = ?";

            $params = [$doctorId];

            if ($status) {
                $sql .= " AND fu.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY fu.follow_up_date ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $followUps = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $followUps;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get follow-ups by consultation
     *
     * @param int $consultationId Consultation ID
     * @return array Array of follow-ups
     */
    public function getFollowUpsByConsultation($consultationId)
    {
        try {
            $sql = "SELECT fu.*, 
                           CONCAT(pu.first_name, ' ', pu.last_name) as patient_name
                           FROM {$this->table} fu
                           JOIN patients p ON fu.patient_id = p.patient_id
                           JOIN users pu ON p.user_id = pu.user_id
                           WHERE fu.consultation_id = ?
                           ORDER BY fu.follow_up_date ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $consultationId);
            $stmt->execute();
            $result = $stmt->get_result();
            $followUps = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $followUps;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get upcoming follow-ups
     *
     * @param int $days Number of days ahead to look
     * @param int $doctorId Optional doctor filter
     * @return array Array of upcoming follow-ups
     */
    public function getUpcomingFollowUps($days = 7, $doctorId = null)
    {
        try {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime("+{$days} days"));

            $sql = "SELECT fu.*, 
                           CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                           pu.phone as patient_phone,
                           CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                           c.consultation_date, c.diagnosis
                           FROM {$this->table} fu
                           JOIN consultations c ON fu.consultation_id = c.consultation_id
                           JOIN patients p ON fu.patient_id = p.patient_id
                           JOIN users pu ON p.user_id = pu.user_id
                           JOIN users du ON c.doctor_id = du.user_id
                           WHERE fu.follow_up_date BETWEEN ? AND ?
                           AND fu.status = 'scheduled'";

            $params = [$startDate, $endDate];

            if ($doctorId) {
                $sql .= " AND c.doctor_id = ?";
                $params[] = $doctorId;
            }

            $sql .= " ORDER BY fu.follow_up_date ASC, fu.follow_up_id ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $followUps = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $followUps;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Create a new follow-up
     *
     * @param array $data Follow-up data
     * @return int|false New follow-up ID or false on failure
     */
    public function createFollowUp($data)
    {
        try {
            $this->beginTransaction();

            $sql = "INSERT INTO {$this->table} (
                        patient_id, consultation_id, follow_up_date, 
                        follow_up_type, reason, status, notes, 
                        created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $patientId = $data['patient_id'] ?? null;
            $consultationId = $data['consultation_id'] ?? null;
            $followUpDate = $data['follow_up_date'] ?? date('Y-m-d');
            $followUpType = $data['follow_up_type'] ?? 'general';
            $reason = $data['reason'] ?? '';
            $status = $data['status'] ?? 'scheduled';
            $notes = $data['notes'] ?? '';
            $createdBy = $data['created_by'] ?? null;
            $createdAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'iisssssis',
                $patientId,
                $consultationId,
                $followUpDate,
                $followUpType,
                $reason,
                $status,
                $notes,
                $createdBy,
                $createdAt
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create follow-up");
            }

            $followUpId = $stmt->insert_id;
            $stmt->close();

            $this->commitTransaction();
            return $followUpId;
        } catch (Exception $e) {
            $this->rollbackTransaction();
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update follow-up
     *
     * @param int $followUpId Follow-up ID
     * @param array $data Follow-up data
     * @return bool Success status
     */
    public function updateFollowUp($followUpId, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                    follow_up_date = ?, follow_up_type = ?, reason = ?, 
                    status = ?, notes = ?, updated_at = ?
                    WHERE {$this->primaryKey} = ?";

            $followUpDate = $data['follow_up_date'] ?? date('Y-m-d');
            $followUpType = $data['follow_up_type'] ?? 'general';
            $reason = $data['reason'] ?? '';
            $status = $data['status'] ?? 'scheduled';
            $notes = $data['notes'] ?? '';
            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'ssssssi',
                $followUpDate,
                $followUpType,
                $reason,
                $status,
                $notes,
                $updatedAt,
                $followUpId
            );

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update follow-up status
     *
     * @param int $followUpId Follow-up ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($followUpId, $status)
    {
        try {
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = ? WHERE {$this->primaryKey} = ?";

            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ssi', $status, $updatedAt, $followUpId);

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get follow-ups with filters
     *
     * @param array $filters Filters to apply
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Array of follow-ups
     */
    public function getFollowUpsFiltered(array $filters = [], $page = 1, $perPage = 20)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT fu.*, 
                           CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                           pu.phone as patient_phone,
                           CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                           c.consultation_date, c.diagnosis
                           FROM {$this->table} fu
                           JOIN consultations c ON fu.consultation_id = c.consultation_id
                           JOIN patients p ON fu.patient_id = p.patient_id
                           JOIN users pu ON p.user_id = pu.user_id
                           JOIN users du ON c.doctor_id = du.user_id
                           WHERE 1=1";

            $params = [];
            $types = '';

            if (!empty($filters['status'])) {
                $sql .= " AND fu.status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }

            if (!empty($filters['type'])) {
                $sql .= " AND fu.follow_up_type = ?";
                $params[] = $filters['type'];
                $types .= 's';
            }

            if (!empty($filters['doctor_id'])) {
                $sql .= " AND c.doctor_id = ?";
                $params[] = $filters['doctor_id'];
                $types .= 'i';
            }

            if (!empty($filters['patient_id'])) {
                $sql .= " AND fu.patient_id = ?";
                $params[] = $filters['patient_id'];
                $types .= 'i';
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND fu.follow_up_date >= ?";
                $params[] = $filters['date_from'];
                $types .= 's';
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND fu.follow_up_date <= ?";
                $params[] = $filters['date_to'];
                $types .= 's';
            }

            $sql .= " ORDER BY fu.follow_up_date DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            $types .= 'ii';

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            // $params always has at least $perPage and $offset, so always bind
            $stmt->bind_param($types, ...$params);

            $stmt->execute();
            $result = $stmt->get_result();
            $followUps = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $followUps;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count follow-ups with filters
     *
     * @param array $filters Filters to apply
     * @return int Count of follow-ups
     */
    public function countFollowUpsFiltered(array $filters = [])
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} fu
                    JOIN consultations c ON fu.consultation_id = c.consultation_id
                    WHERE 1=1";

            $params = [];
            $types = '';

            if (!empty($filters['status'])) {
                $sql .= " AND fu.status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }

            if (!empty($filters['type'])) {
                $sql .= " AND fu.follow_up_type = ?";
                $params[] = $filters['type'];
                $types .= 's';
            }

            if (!empty($filters['doctor_id'])) {
                $sql .= " AND c.doctor_id = ?";
                $params[] = $filters['doctor_id'];
                $types .= 'i';
            }

            if (!empty($filters['patient_id'])) {
                $sql .= " AND fu.patient_id = ?";
                $params[] = $filters['patient_id'];
                $types .= 'i';
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND fu.follow_up_date >= ?";
                $params[] = $filters['date_from'];
                $types .= 's';
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND fu.follow_up_date <= ?";
                $params[] = $filters['date_to'];
                $types .= 's';
            }

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            if ($params !== []) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row['count'] ?? 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Get follow-up statistics
     *
     * @param string $period Period (today, week, month, year)
     * @return array Follow-up statistics
     */
    public function getFollowUpStatistics($period = 'month')
    {
        try {
            $startDate = '';
            $endDate = date('Y-m-d');

            $startDate = match ($period) {
                'today' => date('Y-m-d'),
                'week' => date('Y-m-d', strtotime('-1 week')),
                'month' => date('Y-m-d', strtotime('-1 month')),
                'year' => date('Y-m-d', strtotime('-1 year')),
                default => date('Y-m-d', strtotime('-1 month')),
            };

            $sql = "SELECT 
                        COUNT(*) as total_follow_ups,
                        SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_follow_ups,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_follow_ups,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_follow_ups,
                        SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show_follow_ups
                    FROM {$this->table}
                    WHERE follow_up_date BETWEEN ? AND ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ss', $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [
                'total_follow_ups' => 0,
                'scheduled_follow_ups' => 0,
                'completed_follow_ups' => 0,
                'cancelled_follow_ups' => 0,
                'no_show_follow_ups' => 0
            ];
        }
    }

    /**
     * Get status class for styling
     *
     * @param string $status Follow-up status
     * @return string CSS class name
     */
    public function getStatusClass($status): string
    {
        return match ($status) {
            'scheduled' => 'badge bg-primary',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger',
            'no_show' => 'badge bg-warning',
            default => 'badge bg-secondary',
        };
    }
}
