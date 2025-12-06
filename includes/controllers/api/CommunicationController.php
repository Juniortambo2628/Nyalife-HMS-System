<?php
/**
 * Nyalife HMS - Communication API Controller
 * 
 * This controller handles all messaging-related API requests.
 */

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../modules/communication/communication_functions.php';

class CommunicationController extends ApiController {
    
    /**
     * Send a message to another user
     * 
     * @return void
     */
    public function sendMessage() {
        // Get request data
        $data = $this->getRequestData();
        
        // Validate required fields
        $requiredFields = ['recipient_id', 'subject', 'message'];
        
        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }
        
        try {
            // Validate recipient
            $recipient_id = intval($data['recipient_id']);
            if ($recipient_id <= 0) {
                $this->sendError('Invalid recipient');
                return;
            }
            
            // Get message details
            $subject = trim($data['subject']);
            $message = trim($data['message']);
            $priority = $data['priority'] ?? 'normal';
            
            // Validate priority
            if (!in_array($priority, ['low', 'normal', 'high'])) {
                $priority = 'normal';
            }
            
            // Send message
            $message_id = sendMessage(
                $this->userId,
                $recipient_id,
                $subject,
                $message,
                $priority
            );
            
            if (!$message_id) {
                $this->sendError('Failed to send message');
                return;
            }
            
            // Log activity
            logActivity(
                $this->userId,
                'message_sent',
                "Sent message to user ID: $recipient_id"
            );
            
            // Return success response
            $this->sendResponse([
                'message' => 'Message sent successfully',
                'message_id' => $message_id
            ], 201);
            
        } catch (Exception $e) {
            $this->sendError('Error sending message: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Archive a message
     * 
     * @return void
     */
    public function archiveMessage() {
        // Get request data
        $data = $this->getRequestData();
        
        // Validate required fields
        $requiredFields = ['message_id'];
        
        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }
        
        try {
            // Validate message ID
            $message_id = intval($data['message_id']);
            if ($message_id <= 0) {
                $this->sendError('Invalid message ID');
                return;
            }
            
            // Archive message
            $success = archiveMessage($message_id, $this->userId);
            
            if (!$success) {
                $this->sendError('Failed to archive message');
                return;
            }
            
            // Log activity
            logActivity(
                $this->userId,
                'message_archived',
                "Archived message ID: $message_id"
            );
            
            // Return success response
            $this->sendResponse([
                'message' => 'Message archived successfully'
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Error archiving message: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete a message
     * 
     * @return void
     */
    public function deleteMessage() {
        // Get request data
        $data = $this->getRequestData();
        
        // Validate required fields
        $requiredFields = ['message_id'];
        
        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }
        
        try {
            // Validate message ID
            $message_id = intval($data['message_id']);
            if ($message_id <= 0) {
                $this->sendError('Invalid message ID');
                return;
            }
            
            // Delete message
            $success = deleteMessage($message_id, $this->userId);
            
            if (!$success) {
                $this->sendError('Failed to delete message');
                return;
            }
            
            // Log activity
            logActivity(
                $this->userId,
                'message_deleted',
                "Deleted message ID: $message_id"
            );
            
            // Return success response
            $this->sendResponse([
                'message' => 'Message deleted successfully'
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Error deleting message: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get users for message composition
     * 
     * @return void
     */
    public function getUsers() {
        // Get search term
        $search = $this->getStringParam('search');
        
        try {
            // Get users
            $users = getMessageUsers($search);
            
            // Return users list
            $this->sendResponse($users);
            
        } catch (Exception $e) {
            $this->sendError('Error retrieving users: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Mark a message as read
     * 
     * @return void
     */
    public function markMessageAsRead() {
        // Get request data
        $data = $this->getRequestData();
        
        // Validate required fields
        $requiredFields = ['message_id'];
        
        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }
        
        try {
            // Validate message ID
            $message_id = intval($data['message_id']);
            if ($message_id <= 0) {
                $this->sendError('Invalid message ID');
                return;
            }
            
            // Mark message as read
            $success = markMessageAsRead($message_id, $this->userId);
            
            if (!$success) {
                $this->sendError('Failed to mark message as read');
                return;
            }
            
            // Return success response
            $this->sendResponse([
                'message' => 'Message marked as read'
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Error marking message as read: ' . $e->getMessage(), 500);
        }
    }
} 