<?php

/**
 * Nyalife HMS - Lab Request Model
 *
 * Model for handling lab test requests.
 */

require_once __DIR__ . '/BaseModel.php';

class LabRequestModel extends BaseModel
{
    protected $table = 'lab_requests';
    protected $primaryKey = 'request_id';

    /**
     * Get request with details
     *
     * @param int $requestId Request ID
     * @return array|null Request data with details or null if not found
     */
    public function getRequestWithDetails($requestId)
    {
        try {
            $sql = "SELECT lr.*, 
                           CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                           pu.phone as patient_phone,
                           CONCAT(ru.first_name, ' ', ru.last_name) as requested_by_name,
                           CONCAT(au.first_name, ' ', au.last_name) as assigned_to_name,
                           CONCAT(su.first_name, ' ', su.last_name) as sample_collected_by_name,
                           c.consultation_date, c.diagnosis
                           FROM {$this->table} lr
                           JOIN patients p ON lr.patient_id = p.patient_id
                           JOIN users pu ON p.user_id = pu.user_id
                           JOIN users ru ON lr.requested_by = ru.user_id
                           LEFT JOIN users au ON lr.assigned_to = au.user_id
                           LEFT JOIN users su ON lr.sample_collected_by = su.user_id
                           LEFT JOIN consultations c ON lr.consultation_id = c.consultation_id
                           WHERE lr.request_id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $requestId);
            $stmt->execute();
            $result = $stmt->get_result();
            $request = $result->fetch_assoc();
            $stmt->close();

            return $request;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get requests by patient
     *
     * @param int $patientId Patient ID
     * @param string $status Optional status filter
     * @return array Array of requests
     */
    public function getRequestsByPatient($patientId, $status = null)
    {
        try {
            $sql = "SELECT lr.*, 
                           CONCAT(ru.first_name, ' ', ru.last_name) as requested_by_name,
                           CONCAT(au.first_name, ' ', au.last_name) as assigned_to_name
                           FROM {$this->table} lr
                           JOIN users ru ON lr.requested_by = ru.user_id
                           LEFT JOIN users au ON lr.assigned_to = au.user_id
                           WHERE lr.patient_id = ?";

            $params = [$patientId];

            if ($status) {
                $sql .= " AND lr.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY lr.request_date DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $requests = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $requests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get requests by consultation
     *
     * @param int $consultationId Consultation ID
     * @return array Array of requests
     */
    public function getRequestsByConsultation($consultationId)
    {
        try {
            $sql = "SELECT lr.*, 
                           CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                           CONCAT(ru.first_name, ' ', ru.last_name) as requested_by_name
                           FROM {$this->table} lr
                           JOIN patients p ON lr.patient_id = p.patient_id
                           JOIN users pu ON p.user_id = pu.user_id
                           JOIN users ru ON lr.requested_by = ru.user_id
                           WHERE lr.consultation_id = ?
                           ORDER BY lr.request_date DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $consultationId);
            $stmt->execute();
            $result = $stmt->get_result();
            $requests = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $requests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get pending requests
     *
     * @param string $priority Optional priority filter
     * @param int $limit Maximum number of results
     * @return array Array of pending requests
     */
    public function getPendingRequests($priority = null, $limit = 50)
    {
        try {
            $sql = "SELECT lr.*, 
                           CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                           pu.phone as patient_phone,
                           CONCAT(ru.first_name, ' ', ru.last_name) as requested_by_name
                           FROM {$this->table} lr
                           JOIN patients p ON lr.patient_id = p.patient_id
                           JOIN users pu ON p.user_id = pu.user_id
                           JOIN users ru ON lr.requested_by = ru.user_id
                           WHERE lr.status = 'pending'";

            $params = [];

            if ($priority) {
                $sql .= " AND lr.priority = ?";
                $params[] = $priority;
            }

            $sql .= " ORDER BY 
                        CASE 
                            WHEN lr.priority = 'stat' THEN 1
                            WHEN lr.priority = 'urgent' THEN 2
                            WHEN lr.priority = 'routine' THEN 3
                            ELSE 4
                        END,
                        lr.request_date ASC
                        LIMIT ?";

            $params[] = $limit;

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $requests = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $requests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get requests with filters
     *
     * @param array $filters Filters to apply
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Array of requests
     */
    public function getRequestsFiltered(array $filters = [], $page = 1, $perPage = 20)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT lr.*, 
                           CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                           pu.phone as patient_phone,
                           CONCAT(ru.first_name, ' ', ru.last_name) as requested_by_name,
                           CONCAT(au.first_name, ' ', au.last_name) as assigned_to_name
                           FROM {$this->table} lr
                           JOIN patients p ON lr.patient_id = p.patient_id
                           JOIN users pu ON p.user_id = pu.user_id
                           JOIN users ru ON lr.requested_by = ru.user_id
                           LEFT JOIN users au ON lr.assigned_to = au.user_id
                           WHERE 1=1";

            $params = [];
            $types = '';

            if (!empty($filters['status'])) {
                $sql .= " AND lr.status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }

            if (!empty($filters['priority'])) {
                $sql .= " AND lr.priority = ?";
                $params[] = $filters['priority'];
                $types .= 's';
            }

            if (!empty($filters['patient_id'])) {
                $sql .= " AND lr.patient_id = ?";
                $params[] = $filters['patient_id'];
                $types .= 'i';
            }

            if (!empty($filters['requested_by'])) {
                $sql .= " AND lr.requested_by = ?";
                $params[] = $filters['requested_by'];
                $types .= 'i';
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND lr.request_date >= ?";
                $params[] = $filters['date_from'];
                $types .= 's';
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND lr.request_date <= ?";
                $params[] = $filters['date_to'];
                $types .= 's';
            }

            $sql .= " ORDER BY lr.request_date DESC LIMIT ? OFFSET ?";
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
            $requests = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $requests;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count requests with filters
     *
     * @param array $filters Filters to apply
     * @return int Count of requests
     */
    public function countRequestsFiltered(array $filters = [])
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} lr
                    WHERE 1=1";

            $params = [];
            $types = '';

            if (!empty($filters['status'])) {
                $sql .= " AND lr.status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }

            if (!empty($filters['priority'])) {
                $sql .= " AND lr.priority = ?";
                $params[] = $filters['priority'];
                $types .= 's';
            }

            if (!empty($filters['patient_id'])) {
                $sql .= " AND lr.patient_id = ?";
                $params[] = $filters['patient_id'];
                $types .= 'i';
            }

            if (!empty($filters['requested_by'])) {
                $sql .= " AND lr.requested_by = ?";
                $params[] = $filters['requested_by'];
                $types .= 'i';
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND lr.request_date >= ?";
                $params[] = $filters['date_from'];
                $types .= 's';
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND lr.request_date <= ?";
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
     * Create a new request
     *
     * @param array $data Request data
     * @return int|false New request ID or false on failure
     */
    public function createRequest($data)
    {
        try {
            $this->beginTransaction();

            $sql = "INSERT INTO {$this->table} (
                        patient_id, consultation_id, requested_by, 
                        assigned_to, sample_collected_by, request_date, 
                        status, priority, notes, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $patientId = $data['patient_id'] ?? null;
            $consultationId = $data['consultation_id'] ?? null;
            $requestedBy = $data['requested_by'] ?? null;
            $assignedTo = $data['assigned_to'] ?? null;
            $sampleCollectedBy = $data['sample_collected_by'] ?? null;
            $requestDate = $data['request_date'] ?? date('Y-m-d H:i:s');
            $status = $data['status'] ?? 'pending';
            $priority = $data['priority'] ?? 'routine';
            $notes = $data['notes'] ?? '';
            $createdAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'iiiiisssss',
                $patientId,
                $consultationId,
                $requestedBy,
                $assignedTo,
                $sampleCollectedBy,
                $requestDate,
                $status,
                $priority,
                $notes,
                $createdAt
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create request");
            }

            $requestId = $stmt->insert_id;
            $stmt->close();

            $this->commitTransaction();
            return $requestId;
        } catch (Exception $e) {
            $this->rollbackTransaction();
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update request
     *
     * @param int $requestId Request ID
     * @param array $data Request data
     * @return bool Success status
     */
    public function updateRequest($requestId, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                    patient_id = ?, consultation_id = ?, requested_by = ?, 
                    assigned_to = ?, sample_collected_by = ?, request_date = ?, 
                    status = ?, priority = ?, notes = ?, updated_at = ?
                    WHERE {$this->primaryKey} = ?";

            $patientId = $data['patient_id'] ?? null;
            $consultationId = $data['consultation_id'] ?? null;
            $requestedBy = $data['requested_by'] ?? null;
            $assignedTo = $data['assigned_to'] ?? null;
            $sampleCollectedBy = $data['sample_collected_by'] ?? null;
            $requestDate = $data['request_date'] ?? date('Y-m-d H:i:s');
            $status = $data['status'] ?? 'pending';
            $priority = $data['priority'] ?? 'routine';
            $notes = $data['notes'] ?? '';
            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'iiiiisssssi',
                $patientId,
                $consultationId,
                $requestedBy,
                $assignedTo,
                $sampleCollectedBy,
                $requestDate,
                $status,
                $priority,
                $notes,
                $updatedAt,
                $requestId
            );

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update request status
     *
     * @param int $requestId Request ID
     * @param string $status New status
     * @param int $userId User ID making the change
     * @return bool Success status
     */
    public function updateStatus($requestId, $status, $userId)
    {
        try {
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = ?";

            if ($status === 'completed') {
                $sql .= ", completed_at = ?";
            }

            $sql .= " WHERE {$this->primaryKey} = ?";

            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            if ($status === 'completed') {
                $completedAt = date('Y-m-d H:i:s');
                $stmt->bind_param('sssi', $status, $updatedAt, $completedAt, $requestId);
            } else {
                $stmt->bind_param('ssi', $status, $updatedAt, $requestId);
            }

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Assign request to lab technician
     *
     * @param int $requestId Request ID
     * @param int $assignedTo User ID of assigned technician
     * @return bool Success status
     */
    public function assignRequest($requestId, $assignedTo)
    {
        try {
            $sql = "UPDATE {$this->table} SET assigned_to = ?, updated_at = ? WHERE {$this->primaryKey} = ?";

            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('isi', $assignedTo, $updatedAt, $requestId);

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Mark sample as collected
     *
     * @param int $requestId Request ID
     * @param int $collectedBy User ID of collector
     * @return bool Success status
     */
    public function markSampleCollected($requestId, $collectedBy)
    {
        try {
            $sql = "UPDATE {$this->table} SET sample_collected_by = ?, updated_at = ? WHERE {$this->primaryKey} = ?";

            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('isi', $collectedBy, $updatedAt, $requestId);

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get request statistics
     *
     * @param string $period Period (today, week, month, year)
     * @return array Request statistics
     */
    public function getRequestStatistics($period = 'month')
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
                        COUNT(*) as total_requests,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_requests,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_requests,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_requests,
                        SUM(CASE WHEN priority = 'stat' THEN 1 ELSE 0 END) as stat_requests,
                        SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_requests,
                        SUM(CASE WHEN priority = 'routine' THEN 1 ELSE 0 END) as routine_requests
                    FROM {$this->table}
                    WHERE request_date BETWEEN ? AND ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ss', $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [
                'total_requests' => 0,
                'pending_requests' => 0,
                'processing_requests' => 0,
                'completed_requests' => 0,
                'cancelled_requests' => 0,
                'stat_requests' => 0,
                'urgent_requests' => 0,
                'routine_requests' => 0
            ];
        }
    }

    /**
     * Get status class for styling
     *
     * @param string $status Request status
     * @return string CSS class name
     */
    public function getStatusClass($status): string
    {
        return match ($status) {
            'pending' => 'badge bg-warning',
            'processing' => 'badge bg-info',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get priority class for styling
     *
     * @param string $priority Request priority
     * @return string CSS class name
     */
    public function getPriorityClass($priority): string
    {
        return match ($priority) {
            'stat' => 'badge bg-danger',
            'urgent' => 'badge bg-warning',
            'routine' => 'badge bg-primary',
            default => 'badge bg-secondary',
        };
    }
}
