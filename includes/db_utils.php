<?php
/**
 * Nyalife HMS - Database Utility Functions
 *
 * This file contains database utility functions for common operations.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Execute a SELECT query and return a single row
 *
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return array|null Single row or null
 */
if (!function_exists('dbSelectOne')) {
    function dbSelectOne($sql, $params = [])
    {
        return selectSingle($sql, $params);
    }
}

/**
 * Execute a SELECT query and return multiple rows
 *
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return array Array of rows
 */
if (!function_exists('dbSelect')) {
    function dbSelect($sql, $params = [])
    {
        return selectQuery($sql, $params);
    }
}

/**
 * Execute a SELECT query and return a single value
 *
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return mixed Single value or null
 */
if (!function_exists('dbSelectValue')) {
    function dbSelectValue($sql, $params = [])
    {
        $result = selectSingle($sql, $params);
        return $result ? array_values($result)[0] : null;
    }
}

/**
 * Execute an INSERT query and return the last insert ID
 *
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return int|bool Last insert ID or false on failure
 */
if (!function_exists('dbInsert')) {
    function dbInsert($sql, $params = [])
    {
        return executeQuery($sql, $params);
    }
}

/**
 * Execute an UPDATE query
 *
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return bool Success status
 */
if (!function_exists('dbUpdate')) {
    function dbUpdate($sql, $params = []): bool
    {
        return executeQuery($sql, $params) !== false;
    }
}

/**
 * Execute a DELETE query
 *
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return bool Success status
 */
if (!function_exists('dbDelete')) {
    function dbDelete($sql, $params = []): bool
    {
        return executeQuery($sql, $params) !== false;
    }
}

/**
 * Begin a database transaction
 *
 * @param mixed $db Optional database connection (for backward compatibility)
 * @return bool Success status
 */
if (!function_exists('dbBeginTransaction')) {
    function dbBeginTransaction($db = null)
    {
        global $pdo;
        return $pdo->beginTransaction();
    }
}

/**
 * Commit a database transaction
 *
 * @param mixed $db Optional database connection (for backward compatibility)
 * @return bool Success status
 */
if (!function_exists('dbCommitTransaction')) {
    function dbCommitTransaction($db = null)
    {
        global $pdo;
        return $pdo->commit();
    }
}

/**
 * Rollback a database transaction
 *
 * @param mixed $db Optional database connection (for backward compatibility)
 * @return bool Success status
 */
if (!function_exists('dbRollbackTransaction')) {
    function dbRollbackTransaction($db = null)
    {
        global $pdo;
        return $pdo->rollBack();
    }
}

/**
 * Check if a record exists
 *
 * @param string $table Table name
 * @param string $column Column name
 * @param mixed $value Value to check
 * @return bool True if exists, false otherwise
 */
if (!function_exists('dbRecordExists')) {
    function dbRecordExists($table, $column, $value): bool
    {
        $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = ?";
        $result = selectSingle($sql, [$value]);
        return $result && $result['count'] > 0;
    }
}

/**
 * Get the count of records in a table
 *
 * @param string $table Table name
 * @param string $whereClause Optional WHERE clause
 * @param array $params Optional parameters for WHERE clause
 * @return int Record count
 */
if (!function_exists('dbCount')) {
    function dbCount($table, $whereClause = '', $params = [])
    {
        $sql = "SELECT COUNT(*) as count FROM $table";
        if ($whereClause) {
            $sql .= " WHERE $whereClause";
        }

        $result = selectSingle($sql, $params);
        return $result ? $result['count'] : 0;
    }
}

/**
 * Get a single record by ID
 *
 * @param string $table Table name
 * @param string $idColumn ID column name
 * @param mixed $id ID value
 * @return array|null Record or null
 */
if (!function_exists('dbGetById')) {
    function dbGetById($table, $idColumn, $id)
    {
        $sql = "SELECT * FROM $table WHERE $idColumn = ?";
        return selectSingle($sql, [$id]);
    }
}

/**
 * Get all records from a table
 *
 * @param string $table Table name
 * @param string $orderBy Optional ORDER BY clause
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array Array of records
 */
if (!function_exists('dbGetAll')) {
    function dbGetAll($table, $orderBy = '', $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM $table";

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params = [$limit];

            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }

            return selectQuery($sql, $params);
        }

        return selectQuery($sql);
    }
}

/**
 * Insert a record into a table
 *
 * @param string $table Table name
 * @param array $data Data to insert
 * @return int|bool Last insert ID or false on failure
 */
if (!function_exists('dbInsertRecord')) {
    function dbInsertRecord($table, $data)
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        $values = array_values($data);

        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        return executeQuery($sql, $values);
    }
}

/**
 * Update a record in a table
 *
 * @param string $table Table name
 * @param string $idColumn ID column name
 * @param mixed $id ID value
 * @param array $data Data to update
 * @return bool Success status
 */
if (!function_exists('dbUpdateRecord')) {
    function dbUpdateRecord($table, $idColumn, $id, $data): bool
    {
        $columns = array_keys($data);
        $setClause = implode(' = ?, ', $columns) . ' = ?';
        $values = array_values($data);
        $values[] = $id;

        $sql = "UPDATE $table SET $setClause WHERE $idColumn = ?";

        return executeQuery($sql, $values) !== false;
    }
}

/**
 * Delete a record from a table
 *
 * @param string $table Table name
 * @param string $idColumn ID column name
 * @param mixed $id ID value
 * @return bool Success status
 */
if (!function_exists('dbDeleteRecord')) {
    function dbDeleteRecord($table, $idColumn, $id): bool
    {
        $sql = "DELETE FROM $table WHERE $idColumn = ?";
        return executeQuery($sql, [$id]) !== false;
    }
}

/**
 * Search records in a table
 *
 * @param string $table Table name
 * @param array $searchColumns Columns to search in
 * @param string $searchTerm Search term
 * @param string $orderBy Optional ORDER BY clause
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array Array of matching records
 */
if (!function_exists('dbSearch')) {
    function dbSearch($table, $searchColumns, $searchTerm, $orderBy = '', $limit = null, $offset = null)
    {
        $searchConditions = [];
        $params = [];

        foreach ($searchColumns as $column) {
            $searchConditions[] = "$column LIKE ?";
            $params[] = "%$searchTerm%";
        }

        $whereClause = implode(' OR ', $searchConditions);
        $sql = "SELECT * FROM $table WHERE $whereClause";

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;

            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }
        }

        return selectQuery($sql, $params);
    }
}

/**
 * Log database errors
 *
 * @param string $message Error message
 * @param string $sql SQL query (optional)
 * @param array $params Query parameters (optional)
 * @return void
 */
if (!function_exists('logDatabaseError')) {
    function logDatabaseError($message, $sql = '', $params = []): void
    {
        $logMessage = date('Y-m-d H:i:s') . " - Database Error: $message";

        if ($sql) {
            $logMessage .= " - SQL: $sql";
        }

        if (!empty($params)) {
            $logMessage .= " - Params: " . json_encode($params);
        }

        error_log($logMessage);
    }
}
