<?php
/**
 * Nyalife HMS - Audit Logger
 *
 * Handles audit logging for system actions.
 */

require_once __DIR__ . '/../core/ErrorHandler.php';

class AuditLogger
{
    private $db;
    private string $table = 'audit_logs';
    private bool $logToFile = false;
    private ?string $logFilePath = null;

    /**
     * Constructor
     *
     * @param mysqli $db Database connection
     */
    public function __construct($db = null)
    {
        global $mysqli;
        $this->db = $db ?: $mysqli;

        try {
            if (!$this->db) {
                throw new Exception("Database connection not provided to AuditLogger.");
            }

            // Set up file logging if enabled
            $this->logToFile = defined('AUDIT_LOG_TO_FILE') && AUDIT_LOG_TO_FILE;

            if ($this->logToFile) {
                $logDir = dirname(__DIR__) . '/logs';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
                $this->logFilePath = $logDir . '/audit_' . date('Y-m-d') . '.log';
            }

        } catch (Exception $e) {
            error_log('AuditLogger initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Log an action
     *
     * @param array $data Log data
     * @return bool Success status
     */
    public function log($data)
    {
        try {
            // Required fields
            $userId = $data['user_id'] ?? null;
            $action = $data['action'] ?? '';
            $entityType = $data['entity_type'] ?? '';
            $entityId = $data['entity_id'] ?? null;
            $description = $data['description'] ?? '';

            // Optional fields
            $ipAddress = $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Prepare old and new values if provided
            $oldValues = isset($data['old_values']) ? json_encode($data['old_values']) : null;
            $newValues = isset($data['new_values']) ? json_encode($data['new_values']) : null;

            // Insert log entry
            $sql = "INSERT INTO audit_logs (
                        user_id, action, entity_type, entity_id, description, 
                        old_values, new_values, ip_address, user_agent
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'ississsss',
                $userId,
                $action,
                $entityType,
                $entityId,
                $description,
                $oldValues,
                $newValues,
                $ipAddress,
                $userAgent
            );

            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            error_log("Database audit log error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get audit logs for a specific entity
     *
     * @param string $entityType Entity type
     * @param int $entityId Entity ID
     * @param int $limit Maximum number of logs to return
     * @return array Audit logs
     */
    public function getEntityLogs($entityType, $entityId, $limit = 10)
    {
        try {
            $sql = "SELECT l.*, u.first_name, u.last_name, u.username
                    FROM audit_logs l
                    LEFT JOIN users u ON l.user_id = u.user_id
                    WHERE l.entity_type = ? AND l.entity_id = ?
                    ORDER BY l.created_at DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('sii', $entityType, $entityId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $logs = [];
            while ($row = $result->fetch_assoc()) {
                // Decode JSON values
                if (!empty($row['old_values'])) {
                    $row['old_values'] = json_decode((string) $row['old_values'], true);
                }
                if (!empty($row['new_values'])) {
                    $row['new_values'] = json_decode((string) $row['new_values'], true);
                }
                $logs[] = $row;
            }

            $stmt->close();

            return $logs;
        } catch (Exception $e) {
            error_log("Database audit log retrieval error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get audit logs for a specific user
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of logs to return
     * @return array Audit logs
     */
    public function getUserLogs($userId, $limit = 10)
    {
        try {
            $sql = "SELECT l.*, u.first_name, u.last_name, u.username
                    FROM audit_logs l
                    LEFT JOIN users u ON l.user_id = u.user_id
                    WHERE l.user_id = ?
                    ORDER BY l.created_at DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ii', $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $logs = [];
            while ($row = $result->fetch_assoc()) {
                // Decode JSON values
                if (!empty($row['old_values'])) {
                    $row['old_values'] = json_decode((string) $row['old_values'], true);
                }
                if (!empty($row['new_values'])) {
                    $row['new_values'] = json_decode((string) $row['new_values'], true);
                }
                $logs[] = $row;
            }

            $stmt->close();

            return $logs;
        } catch (Exception $e) {
            error_log("Database audit log retrieval error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get audit logs with filtering options
     *
     * @param array $filters Optional filters:
     *   - user_id: Filter by user ID
     *   - action: Filter by action type
     *   - entity_type: Filter by entity type
     *   - entity_id: Filter by entity ID
     *   - date_from: Filter logs after this date (YYYY-MM-DD)
     *   - date_to: Filter logs before this date (YYYY-MM-DD)
     *   - search: Search in details
     *   - limit: Maximum number of logs to return
     *   - offset: Offset for pagination
     * @return array Array of audit log entries
     */
    public function getLogs($filters = [])
    {
        try {
            $filters = array_merge([
                'user_id' => null,
                'action' => null,
                'entity_type' => null,
                'entity_id' => null,
                'date_from' => null,
                'date_to' => null,
                'search' => null,
                'limit' => 50,
                'offset' => 0
            ], $filters);

            $where = [];
            $params = [];
            $types = '';

            // Build WHERE clause
            if ($filters['user_id'] !== null) {
                $where[] = 'user_id = ?';
                $params[] = $filters['user_id'];
                $types .= 'i';
            }

            if ($filters['action']) {
                $where[] = 'action = ?';
                $params[] = $filters['action'];
                $types .= 's';
            }

            if ($filters['entity_type']) {
                $where[] = 'entity_type = ?';
                $params[] = $filters['entity_type'];
                $types .= 's';
            }

            if ($filters['entity_id']) {
                $where[] = 'entity_id = ?';
                $params[] = $filters['entity_id'];
                $types .= 'i';
            }

            // Optional fields for filtering
            if ($filters['date_from']) {
                $where[] = 'l.created_at >= ?';
                $params[] = $filters['date_from'];
                $types .= 's';
            }
            if ($filters['date_to']) {
                $where[] = 'l.created_at <= ?';
                $params[] = $filters['date_to'];
                $types .= 's';
            }
            if ($filters['search']) {
                $where[] = 'l.description LIKE ?';
                $params[] = '%' . $filters['search'] . '%';
                $types .= 's';
            }

            // Build query
            $sql = "SELECT * FROM {$this->table}";
            if ($where !== []) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $sql .= " ORDER BY created_at DESC";

            // Add limit and offset
            $limit = (int) $filters['limit'];
            if ($limit > 0) {
                $sql .= ' LIMIT ? OFFSET ?';
                $params = array_merge($params, [$limit, (int)$filters['offset']]);
                $types .= 'ii';
            }

            $stmt = $this->db->prepare($sql);

            if ($params !== []) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $logs = [];
            while ($row = $result->fetch_assoc()) {
                // Parse details JSON if it exists
                if (!empty($row['details'])) {
                    $decoded = json_decode((string) $row['details'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $row['details'] = $decoded;
                    }
                }
                $logs[] = $row;
            }

            return $logs;

        } catch (Exception $e) {
            error_log('Error retrieving audit logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get count of audit logs matching filters
     */
    public function countLogs($filters = []): int
    {
        try {
            $filters = array_merge([
                'user_id' => null,
                'action' => null,
                'entity_type' => null,
                'entity_id' => null,
                'date_from' => null,
                'date_to' => null,
                'search' => null
            ], $filters);

            $where = [];
            $params = [];
            $types = '';

            // Build WHERE clause (same as getLogs)
            if ($filters['user_id'] !== null) {
                $where[] = 'user_id = ?';
                $params[] = $filters['user_id'];
                $types .= 'i';
            }

            if ($filters['action']) {
                $where[] = 'action = ?';
                $params[] = $filters['action'];
                $types .= 's';
            }

            if ($filters['entity_type']) {
                $where[] = 'entity_type = ?';
                $params[] = $filters['entity_type'];
                $types .= 's';
            }

            if ($filters['entity_id']) {
                $where[] = 'entity_id = ?';
                $params[] = $filters['entity_id'];
                $types .= 'i';
            }

            // Optional fields for filtering
            if ($filters['date_from']) {
                $where[] = 'l.created_at >= ?';
                $params[] = $filters['date_from'];
                $types .= 's';
            }
            if ($filters['date_to']) {
                $where[] = 'l.created_at <= ?';
                $params[] = $filters['date_to'];
                $types .= 's';
            }
            if ($filters['search']) {
                $where[] = 'l.description LIKE ?';
                $params[] = '%' . $filters['search'] . '%';
                $types .= 's';
            }

            // Build query
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            if ($where !== []) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $stmt = $this->db->prepare($sql);

            if ($params !== []) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int)$row['total'];

        } catch (Exception $e) {
            error_log('Error counting audit logs: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clean up old audit logs
     *
     * @param int $daysOld Delete logs older than this many days
     * @return int Number of deleted logs
     */
    public function cleanupOldLogs($daysOld = 90)
    {
        try {
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-$daysOld days"));

            // Delete from database
            $sql = "DELETE FROM {$this->table} WHERE created_at < ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $cutoffDate);
            $stmt->execute();

            $deleted = $stmt->affected_rows;

            // Clean up old log files
            if ($this->logToFile) {
                $logDir = dirname((string) $this->logFilePath);
                $cutoffDate = strtotime("-$daysOld days");

                foreach (glob("$logDir/audit_*.log") as $file) {
                    if (filemtime($file) < $cutoffDate) {
                        unlink($file);
                    }
                }
            }

            return $deleted;

        } catch (Exception $e) {
            error_log('Error cleaning up old audit logs: ' . $e->getMessage());
            return 0;
        }
    }
}
