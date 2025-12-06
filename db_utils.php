<?php
/**
 * Nyalife HMS - Database Utilities
 * 
 * This file provides a standardized interface for database operations.
 * All database interactions should use these functions for consistency.
 */

// Include the database configuration
require_once __DIR__ . '/config/database.php';

/**
 * Execute a SELECT query and return all results
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to the query
 * @return array Results as associative array
 */
function dbSelect($sql, $params = []) {
    return selectQuery($sql, $params);
}

/**
 * Execute a SELECT query and return a single row
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to the query
 * @return array|null Single row as associative array or null if no results
 */
function dbSelectOne($sql, $params = []) {
    return selectSingle($sql, $params);
}

/**
 * Execute a SELECT query and return a single value
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to the query
 * @return mixed|null Single value or null if no results
 */
function dbSelectValue($sql, $params = []) {
    $result = selectSingle($sql, $params);
    return $result ? reset($result) : null;
}

/**
 * Execute an INSERT query
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to the query
 * @return int|bool Last insert ID or false on failure
 */
function dbInsert($sql, $params = []) {
    return executeQuery($sql, $params);
}

/**
 * Execute an UPDATE query
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to the query
 * @return bool Success status
 */
function dbUpdate($sql, $params = []) {
    return executeQuery($sql, $params);
}

/**
 * Execute a DELETE query
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to the query
 * @return bool Success status
 */
function dbDelete($sql, $params = []) {
    return executeQuery($sql, $params);
}

/**
 * Start a database transaction
 * 
 * @return object Database connection for transaction
 */
function dbBeginTransaction() {
    return beginTransaction();
}

/**
 * Commit a database transaction
 * 
 * @param object $conn Database connection
 * @return bool Success status
 */
function dbCommitTransaction($conn) {
    return commitTransaction($conn);
}

/**
 * Rollback a database transaction
 * 
 * @param object $conn Database connection
 * @return bool Success status
 */
function dbRollbackTransaction($conn) {
    return rollbackTransaction($conn);
}

/**
 * Insert data into a table
 * 
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int|bool Last insert ID or false on failure
 */
function dbInsertIntoTable($table, $data) {
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    
    $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    return dbInsert($sql, array_values($data));
}

/**
 * Update data in a table
 * 
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @param string $whereColumn Column name for WHERE clause
 * @param mixed $whereValue Value for WHERE clause
 * @return bool Success status
 */
function dbUpdateTable($table, $data, $whereColumn, $whereValue) {
    $columns = array_keys($data);
    $setClause = implode(' = ?, ', $columns) . ' = ?';
    
    $sql = "UPDATE $table SET $setClause WHERE $whereColumn = ?";
    
    $params = array_values($data);
    $params[] = $whereValue;
    
    return dbUpdate($sql, $params);
}

/**
 * Delete a row from a table
 * 
 * @param string $table Table name
 * @param string $whereColumn Column name for WHERE clause
 * @param mixed $whereValue Value for WHERE clause
 * @return bool Success status
 */
function dbDeleteFromTable($table, $whereColumn, $whereValue) {
    $sql = "DELETE FROM $table WHERE $whereColumn = ?";
    return dbDelete($sql, [$whereValue]);
}

/**
 * Check if a value exists in a table
 * 
 * @param string $table Table name
 * @param string $column Column name
 * @param mixed $value Value to check
 * @return bool Whether the value exists
 */
function dbValueExists($table, $column, $value) {
    $sql = "SELECT COUNT(*) AS count FROM $table WHERE $column = ?";
    $result = dbSelectValue($sql, [$value]);
    return $result > 0;
}

/**
 * Count rows in a table with optional WHERE clause
 * 
 * @param string $table Table name
 * @param string $whereColumn Optional column name for WHERE clause
 * @param mixed $whereValue Optional value for WHERE clause
 * @return int Row count
 */
function dbCountRows($table, $whereColumn = null, $whereValue = null) {
    $sql = "SELECT COUNT(*) AS count FROM $table";
    
    if ($whereColumn !== null) {
        $sql .= " WHERE $whereColumn = ?";
        return dbSelectValue($sql, [$whereValue]);
    }
    
    return dbSelectValue($sql);
}
?> 