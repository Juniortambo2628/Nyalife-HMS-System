<?php
/**
 * AuditLogger - Handles logging of security and audit events
 */
class AuditLogger {
    private $db;
    private $table = 'audit_logs';
    private $enabled = true;
    private $logToFile = false;
    private $logFilePath;
    
    public function __construct($dbConnection) {
        try {
            $this->db = $dbConnection;
            
            if (!$this->db) {
                throw new Exception("Database connection not provided to AuditLogger.");
            }
            
            // Set up file logging if enabled
            $this->logToFile = defined('AUDIT_LOG_TO_FILE') && AUDIT_LOG_TO_FILE === true;
            
            if ($this->logToFile) {
                $logDir = dirname(__DIR__) . '/logs';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
                $this->logFilePath = $logDir . '/audit_' . date('Y-m-d') . '.log';
            }
            
        } catch (Exception $e) {
            // If database connection fails, disable database logging
            $this->enabled = false;
            error_log('AuditLogger initialization error: ' . $e->getMessage());
        }
    }
    
    /**
     * Log an audit event
     * 
     * @param array $data Event data including:
     *   - user_id: ID of the user performing the action
     *   - action: Action performed (e.g., 'login_attempt', 'record_updated')
     *   - entity_type: Type of entity affected (e.g., 'user', 'patient')
     *   - entity_id: ID of the affected entity (if applicable)
     *   - details: Additional details about the event
     *   - ip_address: IP address of the user
     *   - user_agent: User agent string
     *   - created_at: Timestamp of the event
     * @return bool True if logging was successful, false otherwise
     */
    public function log($data) {
        if (!$this->enabled) {
            return false;
        }
        
        try {
            // Set default values
            $data = array_merge([
                'user_id' => null,
                'action' => 'unknown',
                'entity_type' => null,
                'entity_id' => null,
                'details' => null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ], $data);
            
            // Convert details to JSON if it's an array
            if (is_array($data['details'])) {
                $data['details'] = json_encode($data['details'], JSON_PRETTY_PRINT);
            }
            
            // Log to database
            $this->logToDatabase($data);
            
            // Log to file if enabled
            if ($this->logToFile) {
                $this->logToFile($data);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('Audit log error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log event to database
     */
    private function logToDatabase($data) {
        try {
            $sql = "INSERT INTO {$this->table} 
                   (user_id, action, entity_type, entity_id, details, ip_address, user_agent, created_at)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'ississss',
                $data['user_id'],
                $data['action'],
                $data['entity_type'],
                $data['entity_id'],
                $data['details'],
                $data['ip_address'],
                $data['user_agent'],
                $data['created_at']
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log('Database audit log error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log event to file
     */
    private function logToFile($data) {
        try {
            $logEntry = sprintf(
                "[%s] %s: %s\n" .
                "User: %s | IP: %s | Agent: %s\n" .
                "Entity: %s (%s)\n" .
                "Details: %s\n" .
                str_repeat("-", 80) . "\n",
                $data['created_at'],
                strtoupper($data['action']),
                $this->getActionDescription($data['action']),
                $data['user_id'] ?: 'guest',
                $data['ip_address'],
                $data['user_agent'],
                $data['entity_type'] ?: 'N/A',
                $data['entity_id'] ?: 'N/A',
                $data['details'] ?: 'No details'
            );
            
            file_put_contents($this->logFilePath, $logEntry, FILE_APPEND);
            
        } catch (Exception $e) {
            error_log('File audit log error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get human-readable description for an action
     */
    private function getActionDescription($action) {
        $descriptions = [
            'login_attempt' => 'User login attempt',
            'login_success' => 'User logged in successfully',
            'login_failed' => 'Failed login attempt',
            'logout' => 'User logged out',
            'password_reset' => 'Password reset requested',
            'password_changed' => 'Password changed',
            'unauthorized_access' => 'Unauthorized access attempt',
            'permission_denied' => 'Permission denied',
            'record_created' => 'Record created',
            'record_updated' => 'Record updated',
            'record_deleted' => 'Record deleted',
            'settings_updated' => 'Settings updated',
            'error_occurred' => 'An error occurred',
            'security_violation' => 'Security policy violation',
            'data_exported' => 'Data exported',
            'data_imported' => 'Data imported',
            'user_created' => 'User account created',
            'user_updated' => 'User account updated',
            'user_deactivated' => 'User account deactivated',
            'role_changed' => 'User role changed',
            'permission_updated' => 'User permissions updated',
            'api_access' => 'API access',
            'file_uploaded' => 'File uploaded',
            'file_downloaded' => 'File downloaded',
            'file_deleted' => 'File deleted',
            'bulk_operation' => 'Bulk operation performed'
        ];
        
        return $descriptions[$action] ?? ucwords(str_replace('_', ' ', $action));
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
    public function getLogs($filters = []) {
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
            
            if ($filters['date_from']) {
                $where[] = 'created_at >= ?';
                $params[] = $filters['date_from'] . ' 00:00:00';
                $types .= 's';
            }
            
            if ($filters['date_to']) {
                $where[] = 'created_at <= ?';
                $params[] = $filters['date_to'] . ' 23:59:59';
                $types .= 's';
            }
            
            if ($filters['search']) {
                $where[] = '(details LIKE ? OR ip_address LIKE ? OR user_agent LIKE ?)';
                $searchTerm = "%{$filters['search']}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
                $types .= 'sss';
            }
            
            // Build query
            $sql = "SELECT * FROM {$this->table}";
            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            // Add limit and offset
            if ($filters['limit'] > 0) {
                $sql .= ' LIMIT ? OFFSET ?';
                $params = array_merge($params, [(int)$filters['limit'], (int)$filters['offset']]);
                $types .= 'ii';
            }
            
            $stmt = $this->db->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                // Parse details JSON if it exists
                if (!empty($row['details'])) {
                    $decoded = json_decode($row['details'], true);
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
    public function countLogs($filters = []) {
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
            
            if ($filters['date_from']) {
                $where[] = 'created_at >= ?';
                $params[] = $filters['date_from'] . ' 00:00:00';
                $types .= 's';
            }
            
            if ($filters['date_to']) {
                $where[] = 'created_at <= ?';
                $params[] = $filters['date_to'] . ' 23:59:59';
                $types .= 's';
            }
            
            if ($filters['search']) {
                $where[] = '(details LIKE ? OR ip_address LIKE ? OR user_agent LIKE ?)';
                $searchTerm = "%{$filters['search']}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
                $types .= 'sss';
            }
            
            // Build query
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            
            $stmt = $this->db->prepare($sql);
            
            if (!empty($params)) {
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
    public function cleanupOldLogs($daysOld = 90) {
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
                $logDir = dirname($this->logFilePath);
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
