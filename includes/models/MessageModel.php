<?php

/**
 * Nyalife HMS - Message Model
 *
 * This model handles all database operations related to messages
 */

require_once __DIR__ . '/BaseModel.php';

class MessageModel extends BaseModel
{
    protected $table = 'messages';
    protected $primaryKey = 'message_id';

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'message',
        'priority',
        'is_read',
        'is_archived',
        'is_deleted'
    ];

    protected $rules = [
        'sender_id' => 'required|integer|min:1',
        'recipient_id' => 'required|integer|min:1',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
        'priority' => 'in:low,normal,high'
    ];

    /**
     * Get messages for a specific user (inbox)
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param bool $unreadOnly
     * @return array
     */
    public function getInboxMessages($userId, $limit = 50, $offset = 0, $unreadOnly = false)
    {
        try {
            $sql = "SELECT m.*, 
                           u.first_name as sender_first_name, 
                           u.last_name as sender_last_name,
                           u.email as sender_email
                    FROM {$this->table} m
                    INNER JOIN users u ON m.sender_id = u.user_id
                    WHERE m.recipient_id = ? 
                    AND m.is_deleted = 0
                    AND m.is_archived = 0";

            if ($unreadOnly) {
                $sql .= " AND m.is_read = 0";
            }

            $sql .= " ORDER BY m.created_at DESC LIMIT ? OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $userId, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            $stmt->close();
            return $messages;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::getSentMessages');
            return [];
        }
    }

    /**
     * Get sent messages for a specific user
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getSentMessages($userId, $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT m.*, 
                           u.first_name as recipient_first_name, 
                           u.last_name as recipient_last_name,
                           u.email as recipient_email
                    FROM {$this->table} m
                    INNER JOIN users u ON m.recipient_id = u.user_id
                    WHERE m.sender_id = ? 
                    AND m.is_deleted = 0
                    ORDER BY m.created_at DESC 
                    LIMIT ? OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $userId, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            $stmt->close();
            return $messages;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::getSentMessages');
            return [];
        }
    }

    /**
     * Get archived messages for a specific user
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getArchivedMessages($userId, $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT m.*, 
                           u.first_name as sender_first_name, 
                           u.last_name as sender_last_name,
                           u.email as sender_email
                    FROM {$this->table} m
                    INNER JOIN users u ON m.sender_id = u.user_id
                    WHERE m.recipient_id = ? 
                    AND m.is_archived = 1 
                    AND m.is_deleted = 0
                    ORDER BY m.archived_at DESC 
                    LIMIT ? OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $userId, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            $stmt->close();
            return $messages;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::getArchivedMessages');
            return [];
        }
    }

    /**
     * Get a specific message with sender/recipient details
     *
     * @param int $messageId
     * @param int $userId User ID to verify access
     * @return array|null
     */
    public function getMessageWithDetails($messageId, $userId)
    {
        try {
            $sql = "SELECT m.*,
                           s.first_name as sender_first_name,
                           s.last_name as sender_last_name,
                           s.email as sender_email,
                           r.first_name as recipient_first_name,
                           r.last_name as recipient_last_name,
                           r.email as recipient_email
                    FROM {$this->table} m
                    INNER JOIN users s ON m.sender_id = s.user_id
                    INNER JOIN users r ON m.recipient_id = r.user_id
                    WHERE m.message_id = ?
                    AND (m.sender_id = ? OR m.recipient_id = ?)
                    AND m.is_deleted = 0";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $messageId, $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $stmt->close();
                return $row;
            }

            $stmt->close();
            return null;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::getMessageWithDetails');
            return null;
        }
    }

    /**
     * Mark message as read
     *
     * @param int $messageId
     * @param int $userId
     * @return bool
     */
    public function markAsRead($messageId, $userId)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET is_read = 1, updated_at = NOW() 
                    WHERE message_id = ? AND recipient_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $messageId, $userId);
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::markAsRead');
            return false;
        }
    }

    /**
     * Archive message
     *
     * @param int $messageId
     * @param int $userId
     * @return bool
     */
    public function archiveMessage($messageId, $userId)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET is_archived = 1, archived_at = NOW(), updated_at = NOW() 
                    WHERE message_id = ? AND (sender_id = ? OR recipient_id = ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $messageId, $userId, $userId);
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::archiveMessage');
            return false;
        }
    }

    /**
     * Soft delete message
     *
     * @param int $messageId
     * @param int $userId
     * @return bool
     */
    public function deleteMessage($messageId, $userId)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET is_deleted = 1, deleted_at = NOW(), updated_at = NOW() 
                    WHERE message_id = ? AND (sender_id = ? OR recipient_id = ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $messageId, $userId, $userId);
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::deleteMessage');
            return false;
        }
    }

    /**
     * Get unread message count for user
     *
     * @param int $userId
     */
    public function getUnreadCount($userId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM {$this->table} 
                    WHERE recipient_id = ? 
                    AND is_read = 0 
                    AND is_deleted = 0";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int)$row['count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::getUnreadCount');
            return 0;
        }
    }

    /**
     * Send a new message
     *
     * @param array $data
     * @return int|false Message ID on success, false on failure
     */
    public function sendMessage($data)
    {
        try {
            // Validate the data
            if (!$this->validate($data)) {
                return false;
            }

            // Set default priority if not provided
            if (!isset($data['priority']) || !in_array($data['priority'], ['low', 'normal', 'high'])) {
                $data['priority'] = 'normal';
            }

            // Create the message
            $messageId = parent::create($data);

            return $messageId;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::sendMessage');
            return false;
        }
    }

    /**
     * Search messages
     *
     * @param int $userId
     * @param string $query
     * @param string $type (inbox, sent, archived)
     * @param int $limit
     * @return array
     */
    public function searchMessages($userId, $query, $type = 'inbox', $limit = 50)
    {
        try {
            $baseCondition = "";
            $joinTable = "";

            switch ($type) {
                case 'sent':
                    $baseCondition = "m.sender_id = ?";
                    $joinTable = "INNER JOIN users u ON m.recipient_id = u.user_id";
                    break;
                case 'archived':
                    $baseCondition = "m.recipient_id = ? AND m.is_archived = 1";
                    $joinTable = "INNER JOIN users u ON m.sender_id = u.user_id";
                    break;
                default: // inbox
                    $baseCondition = "m.recipient_id = ? AND m.is_archived = 0";
                    $joinTable = "INNER JOIN users u ON m.sender_id = u.user_id";
            }

            $sql = "SELECT m.*, 
                           u.first_name, 
                           u.last_name,
                           u.email
                    FROM {$this->table} m
                    {$joinTable}
                    WHERE {$baseCondition}
                    AND m.is_deleted = 0
                    AND (m.subject LIKE ? OR m.message LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)
                    ORDER BY m.created_at DESC 
                    LIMIT ?";

            $searchTerm = "%$query%";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('isssi', $userId, $searchTerm, $searchTerm, $searchTerm, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            $stmt->close();
            return $messages;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::searchMessages');
            return [];
        }
    }

    /**
     * Get message statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public function getMessageStats($userId)
    {
        try {
            $stats = [];

            // Total inbox messages
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE recipient_id = ? AND is_deleted = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_inbox'] = (int)$result->fetch_assoc()['count'];
            $stmt->close();

            // Unread messages
            $stats['unread'] = $this->getUnreadCount($userId);

            // Archived messages
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE recipient_id = ? AND is_archived = 1 AND is_deleted = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['archived'] = (int)$result->fetch_assoc()['count'];
            $stmt->close();

            // Sent messages
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE sender_id = ? AND is_deleted = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['sent'] = (int)$result->fetch_assoc()['count'];
            $stmt->close();

            return $stats;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::getMessageStats');
            return [];
        }
    }
}
