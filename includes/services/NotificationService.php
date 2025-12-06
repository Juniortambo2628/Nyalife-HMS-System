<?php
/**
 * Nyalife HMS - Notification Service
 *
 * Service for handling notification business logic and delivery.
 */

require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../models/StaffModel.php';
require_once __DIR__ . '/../models/MessageModel.php';

class NotificationService
{
    private readonly \NotificationModel $notificationModel;
    private readonly \AppointmentModel $appointmentModel;
    private readonly \UserModel $userModel;
    private readonly \PatientModel $patientModel;
    private readonly \StaffModel $staffModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
        $this->patientModel = new PatientModel();
        $this->staffModel = new StaffModel();
    }

    /**
     * Send appointment notification
     *
     * @param int $appointmentId Appointment ID
     * @param string $type Notification type
     * @param array $additionalData Additional data for the notification
     * @return array Array of created notification IDs
     */
    public function sendAppointmentNotification($appointmentId, $type, $additionalData = [])
    {
        try {
            // Get appointment details with all related data
            $appointmentData = $this->getAppointmentNotificationData($appointmentId);

            if (!$appointmentData) {
                throw new Exception("Appointment not found: {$appointmentId}");
            }

            // Merge any additional data
            $appointmentData = array_merge($appointmentData, $additionalData);

            // Get notification template
            $template = $this->notificationModel->getNotificationTemplate($type, $appointmentData);

            // Create notifications for both patient and doctor
            $createdNotifications = $this->notificationModel->createAppointmentNotification(
                $appointmentId,
                $type,
                $template['title'],
                $template['message'],
                $appointmentData
            );

            // Log the notification creation
            error_log("NotificationService: Created " . count($createdNotifications) . " notifications for appointment {$appointmentId}, type: {$type}");

            return $createdNotifications;
        } catch (Exception $e) {
            error_log("NotificationService error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get comprehensive appointment data for notifications
     *
     * @param int $appointmentId Appointment ID
     * @return array|null Appointment data or null if not found
     */
    private function getAppointmentNotificationData($appointmentId): ?array
    {
        try {
            // Get appointment details
            $appointment = $this->appointmentModel->getAppointmentDetails($appointmentId);

            if (!$appointment) {
                return null;
            }

            $notificationData = [
                'appointment_id' => $appointment['appointment_id'],
                'appointment_date' => date('F d, Y', strtotime((string) $appointment['appointment_date'])),
                'appointment_time' => date('h:i A', strtotime((string) $appointment['appointment_time'])),
                'appointment_status' => $appointment['status'],
                'patient_name' => $appointment['patient_name'],
                'doctor_name' => $appointment['doctor_name'],
                'reason' => $appointment['reason'] ?? 'General consultation'
            ];

            // Get patient user ID (for registered patients)
            if (!empty($appointment['patient_id'])) {
                $patientDetails = $this->patientModel->getWithUserData($appointment['patient_id']);
                if ($patientDetails && !empty($patientDetails['user_id'])) {
                    $notificationData['patient_user_id'] = $patientDetails['user_id'];
                    $notificationData['patient_email'] = $patientDetails['email'];
                    $notificationData['patient_phone'] = $patientDetails['phone'];
                } else {
                    // This might be a guest patient, check if there's contact info
                    $guestInfo = $this->getGuestPatientInfo($appointment['patient_id']);
                    if ($guestInfo) {
                        $notificationData['patient_email'] = $guestInfo['email'];
                        $notificationData['patient_phone'] = $guestInfo['phone'];
                    }
                }
            }

            // Get doctor user ID from staff table
            if (!empty($appointment['doctor_id'])) {
                // Query staff table to get user_id from staff_id
                try {
                    $staff = $this->staffModel->find($appointment['doctor_id']);
                    if ($staff && !empty($staff['user_id'])) {
                        $notificationData['doctor_user_id'] = $staff['user_id'];
                    }
                } catch (Exception $e) {
                    error_log("Error getting doctor user ID: " . $e->getMessage());
                }
            }

            return $notificationData;
        } catch (Exception $e) {
            error_log("Error getting appointment notification data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get guest patient information (for non-registered patients)
     *
     * @param int $patientId Patient ID
     * @return array|null Guest patient info or null
     */
    private function getGuestPatientInfo($patientId): ?array
    {
        // This is a placeholder - you might need to implement this based on how
        // guest appointments are stored in your system
        try {
            // Check if there's a separate guest appointments table or
            // if guest info is stored in the patients table
            $patient = $this->patientModel->find($patientId);

            if ($patient && empty($patient['user_id'])) {
                // This is likely a guest patient
                return [
                    'email' => $patient['email'] ?? null,
                    'phone' => $patient['phone'] ?? null
                ];
            }

            return null;
        } catch (Exception $e) {
            error_log("Error getting guest patient info: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get notifications for a user (for dashboard/header display)
     *
     * @param int $userId User ID
     * @param int $limit Number of notifications to retrieve
     * @param bool $unreadOnly Whether to get only unread notifications
     * @return array Array of notifications
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false)
    {
        return $this->notificationModel->getByUserId($userId, $limit, $unreadOnly);
    }

    /**
     * Get unread notification count for a user
     *
     * @param int $userId User ID
     * @return int Number of unread notifications
     */
    public function getUnreadCount($userId)
    {
        return $this->notificationModel->getUnreadCount($userId);
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId Notification ID
     * @param int $userId User ID (for security)
     * @return bool True on success, false on failure
     */
    public function markAsRead($notificationId, $userId)
    {
        return $this->notificationModel->markAsRead($notificationId, $userId);
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param int $userId User ID
     * @return bool True on success, false on failure
     */
    public function markAllAsRead($userId)
    {
        return $this->notificationModel->markAllAsRead($userId);
    }

    /**
     * Send appointment created notification
     *
     * @param int $appointmentId Appointment ID
     * @return array Array of created notification IDs
     */
    public function sendAppointmentCreatedNotification($appointmentId)
    {
        return $this->sendAppointmentNotification($appointmentId, 'appointment_created');
    }

    /**
     * Send appointment updated notification
     *
     * @param int $appointmentId Appointment ID
     * @param array $changes Changes made to the appointment
     * @return array Array of created notification IDs
     */
    public function sendAppointmentUpdatedNotification($appointmentId, $changes = [])
    {
        $additionalData = [];
        if (!empty($changes)) {
            $additionalData['changes'] = $changes;
        }

        return $this->sendAppointmentNotification($appointmentId, 'appointment_updated', $additionalData);
    }

    /**
     * Send appointment cancelled notification
     *
     * @param int $appointmentId Appointment ID
     * @param string $cancellationReason Reason for cancellation
     * @return array Array of created notification IDs
     */
    public function sendAppointmentCancelledNotification($appointmentId, $cancellationReason = '')
    {
        $additionalData = [];
        if ($cancellationReason) {
            $additionalData['cancellation_reason'] = $cancellationReason;
        }

        return $this->sendAppointmentNotification($appointmentId, 'appointment_cancelled', $additionalData);
    }

    /**
     * Send appointment completed notification
     *
     * @param int $appointmentId Appointment ID
     * @return array Array of created notification IDs
     */
    public function sendAppointmentCompletedNotification($appointmentId)
    {
        return $this->sendAppointmentNotification($appointmentId, 'appointment_completed');
    }

    /**
     * Send appointment reminder notification
     *
     * @param int $appointmentId Appointment ID
     * @return array Array of created notification IDs
     */
    public function sendAppointmentReminderNotification($appointmentId)
    {
        return $this->sendAppointmentNotification($appointmentId, 'appointment_reminder');
    }

    /**
     * Clean up old notifications
     *
     * @param int $daysOld Number of days old to delete (default: 90)
     * @return int Number of deleted notifications
     */
    public function cleanupOldNotifications($daysOld = 90)
    {
        return $this->notificationModel->deleteOldNotifications($daysOld);
    }

    /**
     * Send message notification
     *
     * @param int $messageId Message ID
     * @param array $messageData Message data including sender and recipient info
     * @return array Array of created notification IDs
     */
    public function sendMessageNotification($messageId, $messageData)
    {
        try {
            // Get sender information
            $sender = $this->userModel->find($messageData['sender_id']);
            if (!$sender) {
                throw new Exception("Sender not found: {$messageData['sender_id']}");
            }

            $senderName = trim($sender['first_name'] . ' ' . $sender['last_name']);

            // Prepare notification data
            $title = "New Message from {$senderName}";
            $message = "You have received a new message: " . substr((string) $messageData['subject'], 0, 50) . (strlen((string) $messageData['subject']) > 50 ? '...' : '');

            // Create notification for the recipient
            $notificationId = $this->notificationModel->create([
                'user_id' => $messageData['recipient_id'],
                'type' => 'message_received',
                'title' => $title,
                'message' => $message,
                'priority' => $messageData['priority'] ?? 'normal',
                'channel' => 'system', // Only system notification for now
                'metadata' => json_encode([
                    'message_id' => $messageId,
                    'sender_id' => $messageData['sender_id'],
                    'sender_name' => $senderName,
                    'subject' => $messageData['subject']
                ])
            ]);

            if ($notificationId) {
                error_log("NotificationService: Created message notification {$notificationId} for message {$messageId}");
                return [$notificationId];
            }

            return [];
        } catch (Exception $e) {
            error_log("NotificationService message notification error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get notification statistics for admin dashboard
     *
     * @return array Notification statistics
     */
    public function getNotificationStats()
    {
        try {
            // This would require additional methods in NotificationModel
            // For now, return basic stats
            return [
                'total_notifications' => 0,
                'unread_notifications' => 0,
                'notifications_today' => 0,
                'failed_notifications' => 0
            ];
        } catch (Exception $e) {
            error_log("Error getting notification stats: " . $e->getMessage());
            return [];
        }
    }
}
