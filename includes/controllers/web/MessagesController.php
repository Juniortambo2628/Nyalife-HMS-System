<?php

/**
 * Nyalife HMS - Messages Web Controller
 *
 * This controller handles web-based messaging functionality
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/MessageModel.php';
require_once __DIR__ . '/../../models/UserModel.php';

class MessagesController extends WebController
{
    private readonly \MessageModel $messageModel;

    private readonly \UserModel $userModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->messageModel = new MessageModel();
        $this->userModel = new UserModel();
    }

    /**
     * Show messages inbox
     */
    public function index(): void
    {
        // Auth is already handled in constructor

        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $type = $_GET['type'] ?? 'inbox';

            switch ($type) {
                case 'sent':
                    $messages = $this->messageModel->getSentMessages($this->userId, $limit, $offset);
                    break;
                case 'archived':
                    $messages = $this->messageModel->getArchivedMessages($this->userId, $limit, $offset);
                    break;
                default:
                    $messages = $this->messageModel->getInboxMessages($this->userId, $limit, $offset);
                    $type = 'inbox';
            }

            $stats = $this->messageModel->getMessageStats($this->userId);

            $this->renderView('messages/index', [
                'messages' => $messages,
                'stats' => $stats,
                'currentType' => $type,
                'currentPage' => $page,
                'title' => ucfirst((string) $type) . ' Messages'
            ]);
        } catch (Exception $e) {
            $this->handleError("Error loading messages", $e);
            $this->redirectWithError('Error loading messages', '/dashboard');
        }
    }

    /**
     * Show compose message form
     */
    public function compose(): void
    {
        // Auth is already handled in constructor

        try {
            // Get all active users for recipient selection
            $users = $this->userModel->getAllActiveUsers($this->userId);

            $replyMessage = null;
            $forwardMessage = null;
            $mode = 'compose';

            // Handle reply parameter
            if (isset($_GET['reply']) && !empty($_GET['reply'])) {
                $replyId = (int)$_GET['reply'];
                $replyMessage = $this->messageModel->getMessageWithDetails($replyId, $this->userId);

                if ($replyMessage && $replyMessage['recipient_id'] == $this->userId) {
                    $mode = 'reply';
                } else {
                    $replyMessage = null; // Invalid reply message
                }
            }

            // Handle forward parameter
            if (isset($_GET['forward']) && !empty($_GET['forward'])) {
                $forwardId = (int)$_GET['forward'];
                $forwardMessage = $this->messageModel->getMessageWithDetails($forwardId, $this->userId);

                if ($forwardMessage && ($forwardMessage['recipient_id'] == $this->userId || $forwardMessage['sender_id'] == $this->userId)) {
                    $mode = 'forward';
                } else {
                    $forwardMessage = null; // Invalid forward message
                }
            }

            $this->renderView('messages/compose', [
                'users' => $users,
                'replyMessage' => $replyMessage,
                'forwardMessage' => $forwardMessage,
                'mode' => $mode,
                'title' => ucfirst($mode) . ' Message'
            ]);
        } catch (Exception $e) {
            $this->handleError("Error loading compose form", $e);
            $this->redirectWithError('Error loading compose form', '/messages');
        }
    }

    /**
     * Show specific message
     *
     * @param int $id Message ID from route parameter
     */
    public function show($id = 0): void
    {
        // Auth is already handled in constructor

        $messageId = (int)$id;
        if ($messageId <= 0) {
            $this->redirectWithError('Invalid message ID', '/messages');
            return;
        }

        try {
            $message = $this->messageModel->getMessageWithDetails($messageId, $this->userId);

            if (!$message) {
                $this->redirectWithError('Message not found', '/messages');
                return;
            }

            // Mark as read if user is the recipient
            if ($message['recipient_id'] == $this->userId && !$message['is_read']) {
                $this->messageModel->markAsRead($messageId, $this->userId);
                $message['is_read'] = 1;
            }

            $this->renderView('messages/show', [
                'message' => $message,
                'title' => $message['subject']
            ]);
        } catch (Exception $e) {
            $this->handleError("Error loading message", $e);
            $this->redirectWithError('Error loading message', '/messages');
        }
    }

    /**
     * Process sending a message
     */
    public function send(): void
    {
        // Auth is already handled in constructor

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError('Invalid request method', '/messages/compose');
            return;
        }

        try {
            $messageData = [
                'sender_id' => $this->userId,
                'recipient_id' => (int)($_POST['recipient_id'] ?? 0),
                'subject' => trim($_POST['subject'] ?? ''),
                'message' => trim($_POST['message'] ?? ''),
                'priority' => $_POST['priority'] ?? 'normal'
            ];

            // Validate required fields
            if (empty($messageData['recipient_id']) || empty($messageData['subject']) || empty($messageData['message'])) {
                $this->redirectWithError('Please fill in all required fields', '/messages/compose');
                return;
            }

            // Send message
            $messageId = $this->messageModel->sendMessage($messageData);

            if (!$messageId) {
                $this->redirectWithError('Failed to send message', '/messages/compose');
                return;
            }

            $this->redirectWithSuccess('Message sent successfully', '/messages');
        } catch (Exception $e) {
            $this->handleError("Error sending message", $e);
            $this->redirectWithError('Error sending message', '/messages/compose');
        }
    }

    /**
     * Archive a message (AJAX)
     */
    public function archive(): void
    {
        // Auth is already handled in constructor

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 400);
            return;
        }

        try {
            $messageId = (int)($_POST['message_id'] ?? 0);
            if ($messageId <= 0) {
                $this->jsonResponse(['error' => 'Invalid message ID'], 400);
                return;
            }

            $success = $this->messageModel->archiveMessage($messageId, $this->userId);

            if ($success) {
                $this->jsonResponse(['message' => 'Message archived successfully']);
            } else {
                $this->jsonResponse(['error' => 'Failed to archive message'], 500);
            }
        } catch (Exception $e) {
            $this->handleError("Error archiving message", $e);
            $this->jsonResponse(['error' => 'Error archiving message'], 500);
        }
    }

    /**
     * Delete a message (AJAX)
     */
    public function delete(): void
    {
        // Auth is already handled in constructor

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 400);
            return;
        }

        try {
            $messageId = (int)($_POST['message_id'] ?? 0);
            if ($messageId <= 0) {
                $this->jsonResponse(['error' => 'Invalid message ID'], 400);
                return;
            }

            $success = $this->messageModel->deleteMessage($messageId, $this->userId);

            if ($success) {
                $this->jsonResponse(['message' => 'Message deleted successfully']);
            } else {
                $this->jsonResponse(['error' => 'Failed to delete message'], 500);
            }
        } catch (Exception $e) {
            $this->handleError("Error deleting message", $e);
            $this->jsonResponse(['error' => 'Error deleting message'], 500);
        }
    }

    /**
     * Mark message as read (AJAX)
     */
    public function markRead(): void
    {
        // Auth is already handled in constructor

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 400);
            return;
        }

        try {
            $messageId = (int)($_POST['message_id'] ?? 0);
            if ($messageId <= 0) {
                $this->jsonResponse(['error' => 'Invalid message ID'], 400);
                return;
            }

            $success = $this->messageModel->markAsRead($messageId, $this->userId);

            if ($success) {
                $this->jsonResponse(['message' => 'Message marked as read']);
            } else {
                $this->jsonResponse(['error' => 'Failed to mark message as read'], 500);
            }
        } catch (Exception $e) {
            $this->handleError("Error marking message as read", $e);
            $this->jsonResponse(['error' => 'Error marking message as read'], 500);
        }
    }

    /**
     * Search messages
     */
    public function search(): void
    {
        // Auth is already handled in constructor

        $query = trim($_GET['q'] ?? '');
        if ($query === '' || $query === '0') {
            $this->redirectWithError('Search query is required', '/messages');
            return;
        }

        try {
            $type = $_GET['type'] ?? 'inbox';
            $messages = $this->messageModel->searchMessages($this->userId, $query, $type, 50);
            $stats = $this->messageModel->getMessageStats($this->userId);

            $this->renderView('messages/search', [
                'messages' => $messages,
                'stats' => $stats,
                'query' => $query,
                'type' => $type,
                'title' => "Search Results for: $query"
            ]);
        } catch (Exception $e) {
            $this->handleError("Error searching messages", $e);
            $this->redirectWithError('Error searching messages', '/messages');
        }
    }
}
