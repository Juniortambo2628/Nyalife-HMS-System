<?php

/**
 * Nyalife HMS - Communication API Controller
 *
 * This controller handles all messaging-related API requests.
 */

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../models/MessageModel.php';
require_once __DIR__ . '/../../services/NotificationService.php';

class CommunicationController extends ApiController
{
    private readonly \MessageModel $messageModel;

    private readonly \NotificationService $notificationService;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->messageModel = new MessageModel();
        $this->notificationService = new NotificationService();
    }

    /**
     * Get messages for current user (inbox)
     */
    public function getInbox(): void
    {
        try {
            $pageParam = $this->getStringParam('page');
            $page = max(1, (int)($pageParam !== '' ? $pageParam : '1'));
            $limitParam = $this->getStringParam('limit');
            $limit = min(50, max(1, (int)($limitParam !== '' ? $limitParam : '20')));
            $offset = ($page - 1) * $limit;
            $unreadOnlyParam = $this->getStringParam('unread_only');
            $unreadOnly = filter_var($unreadOnlyParam !== '' ? $unreadOnlyParam : 'false', FILTER_VALIDATE_BOOLEAN);

            $messages = $this->messageModel->getInboxMessages($this->userId, $limit, $offset, $unreadOnly);
            $stats = $this->messageModel->getMessageStats($this->userId);

            $this->sendResponse([
                'messages' => $messages,
                'stats' => $stats,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $stats['total_inbox'] ?? 0
                ]
            ]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving inbox: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get sent messages for current user
     */
    public function getSent(): void
    {
        try {
            $pageParam = $this->getStringParam('page');
            $page = max(1, (int)($pageParam !== '' ? $pageParam : '1'));
            $limitParam = $this->getStringParam('limit');
            $limit = min(50, max(1, (int)($limitParam !== '' ? $limitParam : '20')));
            $offset = ($page - 1) * $limit;

            $messages = $this->messageModel->getSentMessages($this->userId, $limit, $offset);
            $stats = $this->messageModel->getMessageStats($this->userId);

            $this->sendResponse([
                'messages' => $messages,
                'stats' => $stats,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $stats['sent'] ?? 0
                ]
            ]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving sent messages: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get archived messages for current user
     */
    public function getArchived(): void
    {
        try {
            $pageParam = $this->getStringParam('page');
            $page = max(1, (int)($pageParam !== '' ? $pageParam : '1'));
            $limitParam = $this->getStringParam('limit');
            $limit = min(50, max(1, (int)($limitParam !== '' ? $limitParam : '20')));
            $offset = ($page - 1) * $limit;

            $messages = $this->messageModel->getArchivedMessages($this->userId, $limit, $offset);
            $stats = $this->messageModel->getMessageStats($this->userId);

            $this->sendResponse([
                'messages' => $messages,
                'stats' => $stats,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $stats['archived'] ?? 0
                ]
            ]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving archived messages: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a specific message
     */
    public function getMessage(): void
    {
        $data = $this->getRequestData();

        if (!$this->validateParams($data, ['message_id'])) {
            return;
        }

        try {
            $messageId = (int)$data['message_id'];
            $message = $this->messageModel->getMessageWithDetails($messageId, $this->userId);

            if (!$message) {
                $this->sendError('Message not found', 404);
                return;
            }

            // Mark as read if user is the recipient
            if ($message['recipient_id'] == $this->userId && !$message['is_read']) {
                $this->messageModel->markAsRead($messageId, $this->userId);
                $message['is_read'] = 1;
            }

            $this->sendResponse(['message' => $message]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving message: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Search messages
     */
    public function search(): void
    {
        $query = $this->getStringParam('q');
        if ($query === '' || $query === '0') {
            $this->sendError('Search query is required');
            return;
        }

        try {
            $typeParam = $this->getStringParam('type');
            $type = $typeParam !== '' ? $typeParam : 'inbox';
            $limitParam = $this->getStringParam('limit');
            $limit = min(50, max(1, (int)($limitParam !== '' ? $limitParam : '20')));

            $messages = $this->messageModel->searchMessages($this->userId, $query, $type, $limit);

            $this->sendResponse([
                'messages' => $messages,
                'query' => $query,
                'type' => $type
            ]);
        } catch (Exception $e) {
            $this->sendError('Error searching messages: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Send a message to another user
     */
    public function sendMessage(): void
    {
        // Get request data
        $data = $this->getRequestData();

        // Validate required fields
        $requiredFields = ['recipient_id', 'subject', 'message'];

        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }

        try {
            // Prepare message data
            $messageData = [
                'sender_id' => $this->userId,
                'recipient_id' => (int)$data['recipient_id'],
                'subject' => trim((string) $data['subject']),
                'message' => trim((string) $data['message']),
                'priority' => $data['priority'] ?? 'normal'
            ];

            // Validate recipient
            if ($messageData['recipient_id'] <= 0) {
                $this->sendError('Invalid recipient');
                return;
            }

            // Send message using model
            $message_id = $this->messageModel->sendMessage($messageData);

            if (!$message_id) {
                $this->sendError('Failed to send message');
                return;
            }

            // Create notification for recipient about new message
            try {
                $this->notificationService->sendMessageNotification($message_id, $messageData);
            } catch (Exception $e) {
                // Log the error but don't fail the message sending
                error_log("Failed to create message notification: " . $e->getMessage());
            }

            // Log activity
            $this->logActivity(
                $this->userId,
                'message_sent',
                "Sent message to user ID: {$messageData['recipient_id']}"
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
     */
    public function archiveMessage(): void
    {
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

            // Archive message using model
            $success = $this->messageModel->archiveMessage($message_id, $this->userId);

            if (!$success) {
                $this->sendError('Failed to archive message');
                return;
            }

            // Log activity
            $this->logActivity(
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
     */
    public function deleteMessage(): void
    {
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

            // Delete message using model
            $success = $this->messageModel->deleteMessage($message_id, $this->userId);

            if (!$success) {
                $this->sendError('Failed to delete message');
                return;
            }

            // Log activity
            $this->logActivity(
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
     */
    public function getUsers(): void
    {
        // Get search term
        $search = $this->getStringParam('search');

        try {
            // Get users
            $users = $this->getMessageUsers($search);

            // Return users list
            $this->sendResponse($users);
        } catch (Exception $e) {
            $this->sendError('Error retrieving users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark a message as read
     */
    public function markMessageAsRead(): void
    {
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

            // Mark message as read using model
            $success = $this->messageModel->markAsRead($message_id, $this->userId);

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

    /**
     * Get users for messaging
     */
    private function getMessageUsers(string $search = ''): array
    {
        try {
            $sql = "SELECT user_id, first_name, last_name, email, role 
                    FROM users 
                    WHERE is_active = 1 AND user_id != ?";

            $params = [$this->userId];
            $types = 'i';

            if ($search !== '' && $search !== '0') {
                $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= 'sss';
            }

            $sql .= " ORDER BY first_name, last_name LIMIT 50";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $stmt->close();

            return $users;
        } catch (Exception $e) {
            error_log("Error getting message users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log activity
     */
    private function logActivity(int $userId, string $action, string $description): void
    {
        try {
            $sql = "INSERT INTO activity_logs (user_id, action, description, created_at) 
                    VALUES (?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iss', $userId, $action, $description);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
        }
    }
}
