<?php
/**
 * Nyalife HMS - Notification Functions
 * Helper functions for managing system notifications
 */

/**
 * Create a new notification
 * 
 * @param int $user_id The user to notify
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type (e.g., 'appointment', 'lab_result', 'payment')
 * @param int|null $reference_id Related entity ID
 * @param string|null $reference_type Related entity type
 * @return bool Success status
 */
function createNotification($user_id, $title, $message, $type, $reference_id = null, $reference_type = null) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (
                user_id, title, message, notification_type,
                reference_id, reference_type, is_read
            ) VALUES (?, ?, ?, ?, ?, ?, 0)
        ");
        
        $stmt->bind_param("isssss", 
            $user_id, $title, $message, $type,
            $reference_id, $reference_type
        );
        
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get unread notifications for a user
 * 
 * @param int $user_id The user ID
 * @param int $limit Maximum number of notifications to return
 * @return array Array of notifications
 */
function getUnreadNotifications($user_id, $limit = 10) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT *
            FROM notifications
            WHERE user_id = ? AND is_read = 0
            ORDER BY created_at DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Mark a notification as read
 * 
 * @param int $notification_id The notification ID
 * @param int $user_id The user ID (for security check)
 * @return bool Success status
 */
function markNotificationAsRead($notification_id, $user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE notification_id = ? AND user_id = ?
        ");
        
        $stmt->bind_param("ii", $notification_id, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Mark all notifications as read for a user
 * 
 * @param int $user_id The user ID
 * @return bool Success status
 */
function markAllNotificationsAsRead($user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE user_id = ? AND is_read = 0
        ");
        
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error marking all notifications as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a notification
 * 
 * @param int $notification_id The notification ID
 * @param int $user_id The user ID (for security check)
 * @return bool Success status
 */
function deleteNotification($notification_id, $user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            DELETE FROM notifications
            WHERE notification_id = ? AND user_id = ?
        ");
        
        $stmt->bind_param("ii", $notification_id, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error deleting notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get notification count for a user
 * 
 * @param int $user_id The user ID
 * @return int Number of unread notifications
 */
function getNotificationCount($user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE user_id = ? AND is_read = 0
        ");
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        error_log("Error getting notification count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Create notifications for multiple users
 * 
 * @param array $user_ids Array of user IDs
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type
 * @param int|null $reference_id Related entity ID
 * @param string|null $reference_type Related entity type
 * @return bool Success status
 */
function createBulkNotifications($user_ids, $title, $message, $type, $reference_id = null, $reference_type = null) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("
            INSERT INTO notifications (
                user_id, title, message, notification_type,
                reference_id, reference_type, is_read
            ) VALUES (?, ?, ?, ?, ?, ?, 0)
        ");
        
        foreach ($user_ids as $user_id) {
            $stmt->bind_param("isssss", 
                $user_id, $title, $message, $type,
                $reference_id, $reference_type
            );
            $stmt->execute();
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error creating bulk notifications: " . $e->getMessage());
        return false;
    }
} 