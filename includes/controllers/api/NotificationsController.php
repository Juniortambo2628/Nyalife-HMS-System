<?php
/**
 * Nyalife HMS - Notifications API Controller
 * 
 * This controller handles all notification-related API requests.
 */

require_once __DIR__ . '/ApiController.php';

class NotificationsController extends ApiController {
    
    /**
     * Get user notifications
     * 
     * @return void
     */
    public function getNotifications() {
        // Validate user authentication
        if (!$this->userId) {
            $this->sendError('User not authenticated', 401);
            return;
        }
        
        try {
            // Get pagination parameters
            $limit = min(50, max(1, $this->getIntParam('limit') ?: 10));
            $offset = max(0, $this->getIntParam('offset') ?: 0);
            
            // Get unread count
            $unreadCount = $this->fetchOne(
                "SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND is_read = 0",
                [$this->userId]
            );
            
            // Get notifications
            $query = "SELECT * FROM notifications 
                      WHERE user_id = ? 
                      ORDER BY created_at DESC
                      LIMIT ? OFFSET ?";
                      
            $notifications = $this->fetchAll($query, [$this->userId, $limit, $offset]);
            
            // Return response
            $this->sendResponse([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => (int)$unreadCount['count'],
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Error retrieving notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark notification as read
     * 
     * @return void
     */
    public function markRead() {
        // Validate user authentication
        if (!$this->userId) {
            $this->sendError('User not authenticated', 401);
            return;
        }
        
        // Get notification ID
        $notificationId = $this->getIntParam('notification_id', 'POST');
        
        if (!$notificationId) {
            $this->sendError('Notification ID is required');
            return;
        }
        
        try {
            // Verify notification belongs to user
            $notification = $this->fetchOne(
                "SELECT * FROM notifications WHERE notification_id = ? AND user_id = ?",
                [$notificationId, $this->userId]
            );
            
            if (!$notification) {
                $this->sendError('Notification not found or does not belong to you');
                return;
            }
            
            // Mark notification as read
            $result = $this->execute(
                "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE notification_id = ?",
                [$notificationId]
            );
            
            if (!$result) {
                $this->sendError('Failed to mark notification as read');
                return;
            }
            
            // Get updated unread count
            $unreadCount = $this->fetchOne(
                "SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND is_read = 0",
                [$this->userId]
            );
            
            // Return success response
            $this->sendResponse([
                'success' => true,
                'message' => 'Notification marked as read',
                'unread_count' => (int)$unreadCount['count']
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Error marking notification as read: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark all notifications as read
     * 
     * @return void
     */
    public function markAllRead() {
        // Validate user authentication
        if (!$this->userId) {
            $this->sendError('User not authenticated', 401);
            return;
        }
        
        try {
            // Mark all notifications as read
            $result = $this->execute(
                "UPDATE notifications SET is_read = 1, read_at = NOW() 
                 WHERE user_id = ? AND is_read = 0",
                [$this->userId]
            );
            
            // Return success response
            $this->sendResponse([
                'success' => true,
                'message' => 'All notifications marked as read',
                'updated_count' => $this->conn->affected_rows
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Error marking notifications as read: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a notification
     * 
     * @return void
     */
    public function deleteNotification() {
        // Validate user authentication
        if (!$this->userId) {
            $this->sendError('User not authenticated', 401);
            return;
        }
        
        // Get notification ID
        $notificationId = $this->getIntParam('notification_id', 'POST');
        
        if (!$notificationId) {
            $this->sendError('Notification ID is required');
            return;
        }
        
        try {
            // Verify notification belongs to user
            $notification = $this->fetchOne(
                "SELECT * FROM notifications WHERE notification_id = ? AND user_id = ?",
                [$notificationId, $this->userId]
            );
            
            if (!$notification) {
                $this->sendError('Notification not found or does not belong to you');
                return;
            }
            
            // Delete notification
            $result = $this->execute(
                "DELETE FROM notifications WHERE notification_id = ?",
                [$notificationId]
            );
            
            if (!$result) {
                $this->sendError('Failed to delete notification');
                return;
            }
            
            // Return success response
            $this->sendResponse([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Error deleting notification: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a notification
     * 
     * @return void
     */
    public function createNotification() {
        // Only admin, doctor, and specific system roles can create notifications for others
        if (!in_array($this->userRole, ['admin', 'doctor', 'system'])) {
            $this->sendError('Unauthorized. You cannot create notifications for other users.', 403);
            return;
        }
        
        // Get request data
        $userId = $this->getIntParam('user_id', 'POST');
        $title = $this->getParam('title', 'POST');
        $message = $this->getParam('message', 'POST');
        $notificationType = $this->getParam('notification_type', 'POST') ?: 'system';
        $referenceId = $this->getIntParam('reference_id', 'POST');
        $referenceType = $this->getParam('reference_type', 'POST');
        
        // Validate required fields
        if (!$userId) {
            $this->sendError('User ID is required');
            return;
        }
        
        if (empty($title)) {
            $this->sendError('Title is required');
            return;
        }
        
        if (empty($message)) {
            $this->sendError('Message is required');
            return;
        }
        
        try {
            // Check if user exists
            $user = $this->fetchOne(
                "SELECT * FROM users WHERE user_id = ?",
                [$userId]
            );
            
            if (!$user) {
                $this->sendError('User not found');
                return;
            }
            
            // Insert notification
            $sql = "INSERT INTO notifications (
                    user_id, title, message, notification_type,
                    reference_id, reference_type, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }
            
            $stmt->bind_param(
                "isssss",
                $userId,
                $title,
                $message,
                $notificationType,
                $referenceId,
                $referenceType
            );
            
            $stmt->execute();
            
            $notificationId = $stmt->insert_id;
            
            if (!$notificationId) {
                throw new Exception('Failed to create notification');
            }
            
            // Return success response
            $this->sendResponse([
                'success' => true,
                'notification_id' => $notificationId,
                'message' => 'Notification created successfully'
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Error creating notification: ' . $e->getMessage());
        }
    }
} 