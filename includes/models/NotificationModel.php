<?php

/**
 * Nyalife HMS - Notification Model
 *
 * Model for handling notification data and operations.
 */

require_once __DIR__ . '/BaseModel.php';

class NotificationModel extends BaseModel
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

    /**
     * Create a new notification
     *
     * @param array $data Notification data
     * @return int|false Notification ID on success, false on failure
     */
    public function create($data)
    {
        try {
            // Map to actual database schema
            $validData = [];

            // Required fields
            if (empty($data['title']) || empty($data['message'])) {
                throw new Exception('Missing required fields: title, message');
            }

            $validData['title'] = trim((string) $data['title']);
            $validData['message'] = trim((string) $data['message']);

            // Optional fields - map to actual database columns
            if (isset($data['user_id'])) {
                $validData['user_id'] = $data['user_id'];
            }

            // Map type to notification_type
            $validData['notification_type'] = $data['type'] ?? $data['notification_type'] ?? 'general';

            // Map appointment_id to reference_id and set reference_type
            if (isset($data['appointment_id'])) {
                $validData['reference_id'] = $data['appointment_id'];
                $validData['reference_type'] = 'appointment';
            } elseif (isset($data['reference_id'])) {
                $validData['reference_id'] = $data['reference_id'];
                $validData['reference_type'] = $data['reference_type'] ?? 'general';
            }

            // Set defaults
            $validData['is_read'] = $data['is_read'] ?? 0;
            $validData['created_at'] = date('Y-m-d H:i:s');
            $validData['updated_at'] = date('Y-m-d H:i:s');

            return parent::create($validData);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Create appointment notification for both registered users and guests
     *
     * @param int $appointmentId Appointment ID
     * @param string $type Notification type
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $appointmentData Appointment data for context
     * @return array Array of created notification IDs
     */
    public function createAppointmentNotification($appointmentId, $type, $title, $message, $appointmentData = [])
    {
        try {
            $createdNotifications = [];

            // Create notification for patient (if registered user)
            if (!empty($appointmentData['patient_user_id'])) {
                $patientNotificationId = $this->create([
                    'user_id' => $appointmentData['patient_user_id'],
                    'appointment_id' => $appointmentId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'priority' => $this->getPriorityByType($type),
                    'channel' => 'all' // System, email, and SMS if available
                ]);
                if ($patientNotificationId) {
                    $createdNotifications[] = $patientNotificationId;
                }
            } elseif (!empty($appointmentData['patient_email']) || !empty($appointmentData['patient_phone'])) {
                // Create notification for guest patient
                $guestNotificationId = $this->create([
                    'guest_email' => $appointmentData['patient_email'] ?? null,
                    'guest_phone' => $appointmentData['patient_phone'] ?? null,
                    'appointment_id' => $appointmentId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'priority' => $this->getPriorityByType($type),
                    'channel' => 'email' // Only email for guests initially
                ]);
                if ($guestNotificationId) {
                    $createdNotifications[] = $guestNotificationId;
                }
            }

            // Create notification for doctor
            if (!empty($appointmentData['doctor_user_id'])) {
                $doctorTitle = str_replace('Your appointment', 'Patient appointment', $title);
                $doctorMessage = str_replace('your appointment', 'patient appointment', $message);

                $doctorNotificationId = $this->create([
                    'user_id' => $appointmentData['doctor_user_id'],
                    'appointment_id' => $appointmentId,
                    'type' => $type,
                    'title' => $doctorTitle,
                    'message' => $doctorMessage,
                    'priority' => $this->getPriorityByType($type),
                    'channel' => 'system' // Doctors get system notifications primarily
                ]);
                if ($doctorNotificationId) {
                    $createdNotifications[] = $doctorNotificationId;
                }
            }

            return $createdNotifications;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get notifications for a user
     *
     * @param int $userId User ID
     * @param int $limit Number of notifications to retrieve
     * @param bool $unreadOnly Whether to get only unread notifications
     * @return array Array of notifications
     */
    public function getByUserId($userId, $limit = 20, $unreadOnly = false)
    {
        try {
            $whereClause = 'user_id = ?';
            $params = [$userId];

            if ($unreadOnly) {
                $whereClause .= ' AND is_read = 0';
            }

            $sql = "
                SELECT n.*, a.appointment_date, a.appointment_time, a.status as appointment_status
                FROM {$this->table} n
                LEFT JOIN appointments a ON (n.reference_id = a.appointment_id AND n.reference_type = 'appointment')
                WHERE {$whereClause}
                ORDER BY n.created_at DESC
                LIMIT ?
            ";

            $params[] = $limit;
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('i', count($params) - 1) . 'i';
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            $result = $stmt->get_result();
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                // Map database fields to expected field names for compatibility
                $row['type'] = $row['notification_type'];
                $row['appointment_id'] = $row['reference_id'];
                $row['time_ago'] = $this->timeAgo($row['created_at']);
                $notifications[] = $row;
            }

            $stmt->close();
            return $notifications;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get notifications for a guest by email
     *
     * @param string $email Guest email
     * @param int $limit Number of notifications to retrieve
     * @return array Array of notifications
     */
    public function getByGuestEmail($email, $limit = 20): array
    {
        // Note: Current database schema doesn't support guest notifications
        // Only user_id is supported, no guest_email column exists
        // Return empty array for now
        return [];
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId Notification ID
     * @param int|null $userId User ID (for security check)
     * @return bool True on success, false on failure
     */
    public function markAsRead($notificationId, $userId = null)
    {
        try {
            $whereClause = 'notification_id = ?';
            $params = [$notificationId];

            if ($userId) {
                $whereClause .= ' AND user_id = ?';
                $params[] = $userId;
            }

            $sql = "UPDATE {$this->table} SET is_read = 1, updated_at = NOW() WHERE {$whereClause}";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('i', count($params));
            $stmt->bind_param($types, ...$params);

            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param int $userId User ID
     * @return bool True on success, false on failure
     */
    public function markAllAsRead($userId)
    {
        try {
            $sql = "UPDATE {$this->table} SET is_read = 1, updated_at = NOW() WHERE user_id = ? AND is_read = 0";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $userId);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get unread notification count for a user
     *
     * @param int $userId User ID
     * @return int Number of unread notifications
     */
    public function getUnreadCount($userId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND is_read = 0";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $userId);
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
     * Delete old notifications (cleanup)
     *
     * @param int $daysOld Number of days old to delete
     * @return int Number of deleted notifications
     */
    public function deleteOldNotifications($daysOld = 90)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $daysOld);
            $stmt->execute();
            $deletedCount = $stmt->affected_rows;
            $stmt->close();

            return $deletedCount;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Update notification status (for email/SMS sending)
     *
     * @param int $notificationId Notification ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function updateStatus($notificationId, $status)
    {
        try {
            $validStatuses = ['unread', 'read', 'sent', 'failed'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid status: {$status}");
            }

            $updateData = ['status' => $status];
            if ($status === 'sent') {
                $updateData['sent_at'] = date('Y-m-d H:i:s');
            }

            return $this->update($notificationId, $updateData);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get priority level by notification type
     *
     * @param string $type Notification type
     * @return string Priority level
     */
    private function getPriorityByType($type): string
    {
        $priorities = [
            'appointment_created' => 'normal',
            'appointment_updated' => 'high',
            'appointment_cancelled' => 'high',
            'appointment_reminder' => 'normal',
            'appointment_completed' => 'normal',
            'message_received' => 'normal'
        ];

        return $priorities[$type] ?? 'normal';
    }

    /**
     * Generate notification message templates
     *
     * @param string $type Notification type
     * @param array $data Template data
     * @return array Array with title and message
     */
    public function getNotificationTemplate($type, $data = [])
    {
        $doctorName = $data['doctor_name'] ?? 'Doctor';
        $appointmentDate = $data['appointment_date'] ?? 'TBD';
        $appointmentTime = $data['appointment_time'] ?? 'TBD';

        // Message-specific data
        $senderName = $data['sender_name'] ?? 'Someone';
        $messageSubject = $data['subject'] ?? 'New Message';

        $templates = [
            'appointment_created' => [
                'title' => 'Appointment Scheduled',
                'message' => "Your appointment has been scheduled with Dr. {$doctorName} on {$appointmentDate} at {$appointmentTime}."
            ],
            'appointment_updated' => [
                'title' => 'Appointment Updated',
                'message' => "Your appointment with Dr. {$doctorName} has been updated. New date/time: {$appointmentDate} at {$appointmentTime}."
            ],
            'appointment_cancelled' => [
                'title' => 'Appointment Cancelled',
                'message' => "Your appointment with Dr. {$doctorName} scheduled for {$appointmentDate} at {$appointmentTime} has been cancelled."
            ],
            'appointment_reminder' => [
                'title' => 'Appointment Reminder',
                'message' => "Reminder: You have an appointment with Dr. {$doctorName} on {$appointmentDate} at {$appointmentTime}."
            ],
            'appointment_completed' => [
                'title' => 'Appointment Completed',
                'message' => "Your appointment with Dr. {$doctorName} has been completed. Thank you for visiting Nyalife HMS."
            ],
            'message_received' => [
                'title' => "New Message from {$senderName}",
                'message' => "You have received a new message: " . substr((string) $messageSubject, 0, 50) . (strlen((string) $messageSubject) > 50 ? '...' : '')
            ]
        ];

        return $templates[$type] ?? [
            'title' => 'Notification',
            'message' => 'You have a new notification.'
        ];
    }

    /**
     * Get notifications for a user with pagination support
     *
     * @param int $userId User ID
     * @param int $limit Number of notifications to retrieve
     * @param int $offset Offset for pagination
     * @param bool $unreadOnly Whether to fetch only unread notifications
     * @return array Array of notifications
     */
    public function getUserNotifications($userId, $limit = 20, $offset = 0, $unreadOnly = false)
    {
        try {
            $whereClause = 'user_id = ?';
            $params = [$userId];

            if ($unreadOnly) {
                $whereClause .= ' AND is_read = 0';
            }

            $sql = "
                SELECT n.*, a.appointment_date, a.appointment_time, a.status as appointment_status,
                       DATE_FORMAT(n.created_at, '%M %d, %Y at %h:%i %p') as formatted_date
                FROM {$this->table} n
                LEFT JOIN appointments a ON (n.reference_id = a.appointment_id AND n.reference_type = 'appointment')
                WHERE {$whereClause}
                ORDER BY n.created_at DESC
                LIMIT ? OFFSET ?
            ";

            $params[] = $limit;
            $params[] = $offset;
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('i', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                // Map database fields to expected field names for compatibility
                $row['type'] = $row['notification_type'];
                $row['appointment_id'] = $row['reference_id'];
                $row['time_ago'] = $this->timeAgo($row['created_at']);
                $notifications[] = $row;
            }

            return $notifications;
        } catch (Exception $e) {
            error_log("NotificationModel::getUserNotifications() - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total notification count for a user
     *
     * @param int $userId User ID
     * @param bool $unreadOnly Whether to count only unread notifications
     * @return int Total number of notifications
     */
    public function getUserNotificationCount($userId, $unreadOnly = false): int
    {
        try {
            $whereClause = 'user_id = ?';
            $params = [$userId];

            if ($unreadOnly) {
                $whereClause .= ' AND is_read = 0';
            }

            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return intval($row['count']);
        } catch (Exception $e) {
            error_log("NotificationModel::getUserNotificationCount() - Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper method to format time ago
     *
     * @param string $datetime
     */
    private function timeAgo($datetime): string
    {
        $time = time() - strtotime($datetime);

        if ($time < 60) {
            return 'just now';
        }
        if ($time < 3600) {
            return floor($time / 60) . ' minutes ago';
        }
        if ($time < 86400) {
            return floor($time / 3600) . ' hours ago';
        }
        if ($time < 2592000) {
            return floor($time / 86400) . ' days ago';
        }
        if ($time < 31536000) {
            return floor($time / 2592000) . ' months ago';
        }
        return floor($time / 31536000) . ' years ago';
    }
}
