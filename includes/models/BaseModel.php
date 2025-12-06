<?php

/**
 * Nyalife HMS - Base Model
 *
 * Abstract base class for all data models.
 * Provides common functionality for database operations and data validation.
 */

require_once __DIR__ . '/../core/DatabaseManager.php';
require_once __DIR__ . '/../core/ErrorHandler.php';
require_once __DIR__ . '/../../validation_functions.php';

abstract class BaseModel
{
    protected \mysqli $db;
    
    /** @var string */
    protected $table;
    
    /** @var string */
    protected $primaryKey = 'id';
    
    /** @var array */
    protected $validationRules = [];
    
    /** @var array */
    protected $relationships = [];
    
    /** @var array */
    protected $errors = [];

    /**
     * Initialize the model
     */
    /**
     * Get the database connection object
     *
     * @return mysqli|null The mysqli database connection object or null if not connected.
     */
    public function getDbConnection(): ?mysqli
    {
        return $this->db;
    }

    /**
     * Get list of columns for the current table
     *
     * @return array Array of column names
     */
    public function getTableColumns(): array
    {
        static $columnsCache = [];

        if (empty($this->table)) {
            return [];
        }

        if (isset($columnsCache[$this->table])) {
            return $columnsCache[$this->table];
        }

        try {
            $sql = "SHOW COLUMNS FROM `{$this->table}`";
            $result = $this->db->query($sql);
            $cols = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($row['Field'])) {
                        $cols[] = $row['Field'];
                    }
                }
                $result->free();
            }
            $columnsCache[$this->table] = $cols;
            return $cols;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::getTableColumns');
            return [];
        }
    }

    /**
     * Initialize the model
     */
    public function __construct()
    {
        $this->db = DatabaseManager::getInstance()->getConnection();
    }

    /**
     * Find a record by ID
     *
     * @param mixed $id The primary key value
     * @return array|null The record as associative array or null if not found
     */
    public function find(mixed $id): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();

            return $data;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::find');
            return null;
        }
    }

    /**
     * Find all records matching conditions
     *
     * @param array $conditions WHERE conditions as key-value pairs
     * @param string|null $orderBy ORDER BY clause
     * @param int|null $limit LIMIT clause
     * @param int|null $offset OFFSET clause
     * @return array Array of records
     */
    public function findAll(array $conditions = [], ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];

            // Add WHERE clause if conditions are provided
            if ($conditions !== []) {
                $sql .= " WHERE ";
                $clauses = [];

                foreach ($conditions as $key => $value) {
                    $clauses[] = "$key = ?";
                    $params[] = $value;
                }

                $sql .= implode(' AND ', $clauses);
            }

            // Add ORDER BY clause if provided
            if ($orderBy !== null && $orderBy !== '' && $orderBy !== '0') {
                $sql .= " ORDER BY $orderBy";
            }

            // Add LIMIT and OFFSET clauses if provided
            if ($limit !== null) {
                $sql .= " LIMIT ?";
                $params[] = $limit;

                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                    $params[] = $offset;
                }
            }

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            // Bind parameters if any
            if ($params !== []) {
                $types = '';
                $bindParams = [];

                foreach ($params as $key => $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                    $bindParams[] = &$params[$key];
                }

                array_unshift($bindParams, $types);
                call_user_func_array($stmt->bind_param(...), $bindParams);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $data;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::findAll');
            return [];
        }
    }

    /**
     * Create a new record
     *
     * @param array $data Data to insert as key-value pairs
     * @return int|bool Last insert ID or false on failure
     */
    public function create(array $data)
    {
        try {
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');

            $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";

            // Debug the SQL query
            error_log("BaseModel::create - SQL: {$sql}");
            error_log("BaseModel::create - Table: {$this->table}");
            error_log("BaseModel::create - Data: " . print_r($data, true));

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                $error = "Query preparation failed: " . $this->db->error;
                error_log("BaseModel::create - ERROR: " . $error);
                throw new Exception($error);
            }

            // Bind parameters - fix the reference issue
            $types = '';
            $bindParams = [];
            $bindValues = array_values($data); // Get just the values in order

            foreach ($bindValues as $key => $value) {
                if ($value === null) {
                    $types .= 's'; // Treat NULL as a string type for binding
                    $bindValues[$key] = null; // Ensure it's explicitly NULL
                } elseif (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } elseif (is_string($value)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
            }

            // Debug parameter types and binding
            error_log("BaseModel::create - Parameter types: {$types}");
            error_log("BaseModel::create - Parameter count: " . count($bindValues));

            // Create new array with references
            $bindParams[] = $types;
            $counter = count($bindValues);
            for ($i = 0; $i < $counter; $i++) {
                $bindParams[] = &$bindValues[$i];
            }

            // Apply the parameter binding
            call_user_func_array($stmt->bind_param(...), $bindParams);

            // Execute the statement
            $result = $stmt->execute();

            if (!$result) {
                $error = "Execute failed: " . $stmt->error;
                error_log("BaseModel::create - EXECUTE ERROR: " . $error);
                throw new Exception($error);
            }

            // Get the insert ID
            $insertId = $this->db->insert_id;
            error_log("BaseModel::create - Success! Insert ID: {$insertId}");

            $stmt->close();
            return $insertId;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::create');
            error_log("BaseModel::create - Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing record
     *
     * @param mixed $id Primary key value
     * @param array $data Data to update as key-value pairs
     * @return bool Success status
     */
    public function update(mixed $id, array $data): bool
    {
        try {
            $setClause = [];
            foreach ($data as $key => $value) {
                $setClause[] = "$key = ?";
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            // Add ID to the end of data array
            $data[$this->primaryKey] = $id;

            // Bind parameters
            $types = '';
            $bindParams = [];

            foreach ($data as $key => $value) {
                if (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } elseif (is_string($value)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $bindParams[] = &$data[$key];
            }

            array_unshift($bindParams, $types);
            call_user_func_array($stmt->bind_param(...), $bindParams);

            $stmt->execute();
            $success = ($stmt->affected_rows > 0);
            $stmt->close();

            return $success;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::update');
            return false;
        }
    }

    /**
     * Delete a record
     *
     * @param mixed $id Primary key value
     * @return bool Success status
     */
    public function delete(mixed $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $id);
            $stmt->execute();
            $success = ($stmt->affected_rows > 0);
            $stmt->close();

            return $success;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::delete');
            return false;
        }
    }

    /**
     * Count records based on conditions
     *
     * @param array $conditions WHERE conditions as key-value pairs
     * @return int Count of records
     */
    public function count(array $conditions = []): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $params = [];

            // Add WHERE clause if conditions are provided
            if ($conditions !== []) {
                $sql .= " WHERE ";
                $clauses = [];

                foreach ($conditions as $key => $value) {
                    $clauses[] = "$key = ?";
                    $params[] = $value;
                }

                $sql .= implode(' AND ', $clauses);
            }

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            // Bind parameters if any
            if ($params !== []) {
                $types = '';
                $bindParams = [];

                foreach ($params as $key => $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                    $bindParams[] = &$params[$key];
                }

                array_unshift($bindParams, $types);
                call_user_func_array($stmt->bind_param(...), $bindParams);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int)$row['count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::count');
            return 0;
        }
    }

    /**
     * Begin a database transaction
     *
     * @return bool Success status
     */
    public function beginTransaction(): bool
    {
        try {
            return $this->db->begin_transaction();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::beginTransaction');
            return false;
        }
    }

    /**
     * Commit a database transaction
     *
     * @return bool Success status
     */
    public function commitTransaction(): bool
    {
        try {
            return $this->db->commit();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::commitTransaction');
            return false;
        }
    }

    /**
     * Rollback a database transaction
     *
     * @return bool Success status
     */
    public function rollbackTransaction(): bool
    {
        try {
            return $this->db->rollback();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::rollbackTransaction');
            return false;
        }
    }

    /**
     * Validate data against defined rules
     *
     * @param array $data Data to validate
     * @return bool True if validation passes, false otherwise
     */
    public function validate(array $data): bool
    {
        $this->errors = [];

        foreach ($this->validationRules as $field => $rules) {
            if (!isset($data[$field]) && str_contains((string) $rules, 'required')) {
                $this->errors[$field] = "The {$field} field is required.";
                continue;
            }

            if (!isset($data[$field])) {
                continue; // Skip validation for optional fields that aren't present
            }

            $value = $data[$field];
            $ruleList = explode('|', (string) $rules);

            foreach ($ruleList as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;

                switch ($ruleName) {
                    case 'required':
                        if (empty($value) && $value !== '0') {
                            $this->errors[$field] = "The {$field} field is required.";
                        }
                        break;

                    case 'min':
                        if (strlen((string) $value) < $ruleParam) {
                            $this->errors[$field] = "The {$field} field must be at least {$ruleParam} characters.";
                        }
                        break;

                    case 'max':
                        if (strlen((string) $value) > $ruleParam) {
                            $this->errors[$field] = "The {$field} field cannot exceed {$ruleParam} characters.";
                        }
                        break;

                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field] = "The {$field} field must be a valid email address.";
                        }
                        break;

                    case 'numeric':
                        if (!is_numeric($value)) {
                            $this->errors[$field] = "The {$field} field must be a number.";
                        }
                        break;

                    case 'date':
                        if (in_array(strtotime((string) $value), [0, false], true)) {
                            $this->errors[$field] = "The {$field} field must be a valid date.";
                        }
                        break;

                    case 'in':
                        $allowedValues = explode(',', (string) $ruleParam);
                        if (!in_array($value, $allowedValues)) {
                            $this->errors[$field] = "The {$field} field must be one of: " . implode(', ', $allowedValues);
                        }
                        break;
                }

                // If there's already an error for this field, stop validating it
                if (isset($this->errors[$field])) {
                    break;
                }
            }
        }

        return $this->errors === [];
    }

    /**
     * Get validation errors
     *
     * @return array Validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get related records based on a defined relationship
     *
     * @param string $relation Relationship name
     * @param mixed $id Primary key value of the parent record
     * @return array|null Related records or null on error
     */
    public function getRelated(string $relation, mixed $id): ?array
    {
        if (!isset($this->relationships[$relation])) {
            ErrorHandler::log("Relationship '{$relation}' not defined in " . static::class, ErrorHandler::LOG_LEVEL_ERROR);
            return null;
        }

        $rel = $this->relationships[$relation];

        try {
            $sql = "SELECT * FROM {$rel['table']} WHERE {$rel['foreignKey']} = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $data;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . "::getRelated[{$relation}]");
            return null;
        }
    }

    /**
     * Execute a custom SQL query
     *
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @param bool $fetchAll Whether to fetch all results or just one row
     * @return array|bool|null Query results, success status for non-SELECT queries, or null on error
     */
    public function query(string $sql, array $params = [], bool $fetchAll = true)
    {
        try {
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            // Bind parameters if any
            if ($params !== []) {
                $types = '';
                $bindParams = [];

                foreach ($params as $key => $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                    $bindParams[] = &$params[$key];
                }

                array_unshift($bindParams, $types);
                call_user_func_array($stmt->bind_param(...), $bindParams);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result === false) {
                return ($stmt->affected_rows > 0); // For INSERT, UPDATE, DELETE queries
            }

            $data = $fetchAll ? $result->fetch_all(MYSQLI_ASSOC) : $result->fetch_assoc();

            $stmt->close();
            return $data;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, static::class . '::query');
            return null;
        }
    }
}
