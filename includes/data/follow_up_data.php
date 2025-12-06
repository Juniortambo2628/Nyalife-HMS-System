<?php
/**
 * Nyalife HMS - Follow-up Data Functions
 *
 * Standardized functions for follow-up data operations.
 */

require_once __DIR__ . '/../core/DatabaseManager.php';
require_once __DIR__ . '/../core/ErrorHandler.php';

/**
 * Get follow-up with details
 *
 * @param int $followUpId Follow-up ID
 * @return array|null Follow-up data with details or null if not found
 */
function getFollowUpWithDetails($followUpId)
{
    try {
        $db = DatabaseManager::getInstance()->getConnection();

        $sql = "SELECT fu.*, 
                       CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                       pu.email as patient_email, pu.phone as patient_phone,
                       CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                       c.consultation_date, c.diagnosis, c.treatment_plan,
                       CONCAT(cu.first_name, ' ', cu.last_name) as created_by_name
                       FROM follow_ups fu
                       JOIN patients p ON fu.patient_id = p.patient_id
                       JOIN users pu ON p.user_id = pu.user_id
                       JOIN consultations c ON fu.consultation_id = c.consultation_id
                       JOIN users du ON c.doctor_id = du.user_id
                       JOIN users cu ON fu.created_by = cu.user_id
                       WHERE fu.follow_up_id = ?";

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $db->error);
        }

        $stmt->bind_param('i', $followUpId);
        $stmt->execute();
        $result = $stmt->get_result();
        $followUp = $result->fetch_assoc();
        $stmt->close();

        return $followUp;
    } catch (Exception $e) {
        ErrorHandler::logDatabaseError($e, __FUNCTION__);
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
function getFollowUpsByPatient($patientId, $status = null)
{
    try {
        $db = DatabaseManager::getInstance()->getConnection();

        $sql = "SELECT fu.*, 
                       CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                       c.consultation_date, c.diagnosis
                       FROM follow_ups fu
                       JOIN consultations c ON fu.consultation_id = c.consultation_id
                       JOIN users du ON c.doctor_id = du.user_id
                       WHERE fu.patient_id = ?";

        $params = [$patientId];

        if ($status) {
            $sql .= " AND fu.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY fu.follow_up_date DESC";

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $db->error);
        }

        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $followUps = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $followUps;
    } catch (Exception $e) {
        ErrorHandler::logDatabaseError($e, __FUNCTION__);
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
function getFollowUpsByDoctor($doctorId, $status = null)
{
    try {
        $db = DatabaseManager::getInstance()->getConnection();

        $sql = "SELECT fu.*, 
                       CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                       pu.phone as patient_phone,
                       c.consultation_date, c.diagnosis
                       FROM follow_ups fu
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

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $db->error);
        }

        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $followUps = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $followUps;
    } catch (Exception $e) {
        ErrorHandler::logDatabaseError($e, __FUNCTION__);
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
function getUpcomingFollowUps($days = 7, $doctorId = null)
{
    try {
        $db = DatabaseManager::getInstance()->getConnection();

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$days} days"));

        $sql = "SELECT fu.*, 
                       CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                       pu.phone as patient_phone,
                       CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                       c.consultation_date, c.diagnosis
                       FROM follow_ups fu
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

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $db->error);
        }

        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $followUps = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $followUps;
    } catch (Exception $e) {
        ErrorHandler::logDatabaseError($e, __FUNCTION__);
        return [];
    }
}

/**
 * Create follow-up
 *
 * @param array $data Follow-up data
 * @return int|false New follow-up ID or false on failure
 */
function createFollowUp($data)
{
    try {
        $db = DatabaseManager::getInstance()->getConnection();

        $sql = "INSERT INTO follow_ups (
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

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $db->error);
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

        return $followUpId;
    } catch (Exception $e) {
        ErrorHandler::logDatabaseError($e, __FUNCTION__);
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
function updateFollowUp($followUpId, $data)
{
    try {
        $db = DatabaseManager::getInstance()->getConnection();

        $sql = "UPDATE follow_ups SET 
                follow_up_date = ?, follow_up_type = ?, reason = ?, 
                status = ?, notes = ?, updated_at = ?
                WHERE follow_up_id = ?";

        $followUpDate = $data['follow_up_date'] ?? date('Y-m-d');
        $followUpType = $data['follow_up_type'] ?? 'general';
        $reason = $data['reason'] ?? '';
        $status = $data['status'] ?? 'scheduled';
        $notes = $data['notes'] ?? '';
        $updatedAt = date('Y-m-d H:i:s');

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $db->error);
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
        ErrorHandler::logDatabaseError($e, __FUNCTION__);
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
function updateFollowUpStatus($followUpId, $status)
{
    try {
        $db = DatabaseManager::getInstance()->getConnection();

        $sql = "UPDATE follow_ups SET status = ?, updated_at = ? WHERE follow_up_id = ?";

        $updatedAt = date('Y-m-d H:i:s');

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $db->error);
        }

        $stmt->bind_param('ssi', $status, $updatedAt, $followUpId);

        return $stmt->execute() && $stmt->affected_rows > 0;
    } catch (Exception $e) {
        ErrorHandler::logDatabaseError($e, __FUNCTION__);
        return false;
    }
}

/**
 * Delete follow-up
 *
 * @param int $followUpId Follow-up ID
 * @return bool Success status
 */
function deleteFollowUp($followUpId)
{
    try {
        $db = DatabaseManager::getInstance()->getConnection();

        $sql = "DELETE FROM follow_ups WHERE follow_up_id = ?";

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $db->error);
        }

        $stmt->bind_param('i', $followUpId);

        return $stmt->execute() && $stmt->affected_rows > 0;
    } catch (Exception $e) {
        ErrorHandler::logDatabaseError($e, __FUNCTION__);
        return false;
    }
}

/**
 * Get follow-up statistics
 *
 * @param string $period Period (today, week, month, year)
 * @return array Follow-up statistics
 */
function getFollowUpStatistics($period = 'month')
{
    try {
        $db = DatabaseManager::getInstance()->getConnection();

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
                FROM follow_ups
                WHERE follow_up_date BETWEEN ? AND ?";

        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    } catch (Exception $e) {
        ErrorHandler::logDatabaseError($e, __FUNCTION__);
        return [
            'total_follow_ups' => 0,
            'scheduled_follow_ups' => 0,
            'completed_follow_ups' => 0,
            'cancelled_follow_ups' => 0,
            'no_show_follow_ups' => 0
        ];
    }
}
