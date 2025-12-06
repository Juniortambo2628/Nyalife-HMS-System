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

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $validationRules = [];
    protected $relationships = [];
    protected $errors = [];
    
    /**
     * Initialize the model
     */
    /**
     * Get the database connection object
     * 
     * @return mysqli|null The mysqli database connection object or null if not connected.
     */
    public function getDbConnection() {
        return $this->db;
    }
    
    /**
     * Initialize the model
     */
    public function __construct() {
        $this->db = DatabaseManager::getInstance()->getConnection();
    }
    
    /**
     * Find a record by ID
     * 
     * @param mixed $id The primary key value
     * @return array|null The record as associative array or null if not found
     */
    public function find($id) {
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
            ErrorHandler::logDatabaseError($e, get_class($this) . '::find');
            return null;
        }
    }
    
    /**
     * Find all records matching conditions
     * 
     * @param array $conditions WHERE conditions as key-value pairs
     * @param string $orderBy ORDER BY clause
     * @param int $limit LIMIT clause
     * @param int $offset OFFSET clause
     * @return array Array of records
     */
    public function findAll($conditions = [], $orderBy = null, $limit = null, $offset = null) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            
            // Add WHERE clause if conditions are provided
            if (!empty($conditions)) {
                $sql .= " WHERE ";
                $clauses = [];
                
                foreach ($conditions as $key => $value) {
                    $clauses[] = "$key = ?";
                    $params[] = $value;
                }
                
                $sql .= implode(' AND ', $clauses);
            }
            
            // Add ORDER BY clause if provided
            if ($orderBy) {
                $sql .= " ORDER BY $orderBy";
            }
            
            // Add LIMIT and OFFSET clauses if provided
            if ($limit !== null) {
                $sql .= " LIMIT ?";
                $params[] = (int)$limit;
                
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                    $params[] = (int)$offset;
                }
            }
            
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            // Bind parameters if any
            if (!empty($params)) {
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
                call_user_func_array([$stmt, 'bind_param'], $bindParams);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $data;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, get_class($this) . '::findAll');
            return [];
        }
    }
    
    /**
     * Create a new record
     * 
     * @param array $data Data to insert as key-value pairs
     * @return int|bool Last insert ID or false on failure
     */
    public function create($data) {
        try {
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
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
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
            
            $stmt->execute();
            $insertId = $stmt->insert_id;
            $stmt->close();
            
            return $insertId;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, get_class($this) . '::create');
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
    public function update($id, $data) {
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
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
            
            $stmt->execute();
            $success = ($stmt->affected_rows > 0);
            $stmt->close();
            
            return $success;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, get_class($this) . '::update');
            return false;
        }
    }
    
    /**
     * Delete a record
     * 
     * @param mixed $id Primary key value
     * @return bool Success status
     */
    public function delete($id) {
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
            ErrorHandler::logDatabaseError($e, get_class($this) . '::delete');
            return false;
        }
    }
    
    /**
     * Count records based on conditions
     * 
     * @param array $conditions WHERE conditions as key-value pairs
     * @return int Count of records
     */
    public function count($conditions = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $params = [];
            
            // Add WHERE clause if conditions are provided
            if (!empty($conditions)) {
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
            if (!empty($params)) {
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
                call_user_func_array([$stmt, 'bind_param'], $bindParams);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return (int)$row['count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, get_class($this) . '::count');
            return 0;
        }
    }
    
    /**
     * Begin a database transaction
     * 
     * @return bool Success status
     */
    public function beginTransaction() {
        try {
            return $this->db->begin_transaction();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, get_class($this) . '::beginTransaction');
            return false;
        }
    }
    
    /**
     * Commit a database transaction
     * 
     * @return bool Success status
     */
    public function commitTransaction() {
        try {
            return $this->db->commit();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, get_class($this) . '::commitTransaction');
            return false;
        }
    }
    
    /**
     * Rollback a database transaction
     * 
     * @return bool Success status
     */
    public function rollbackTransaction() {
        try {
            return $this->db->rollback();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, get_class($this) . '::rollbackTransaction');
            return false;
        }
    }
    
    /**
     * Validate data against defined rules
     * 
     * @param array $data Data to validate
     * @return bool True if validation passes, false otherwise
     */
    public function validate($data) {
        $this->errors = [];
        
        foreach ($this->validationRules as $field => $rules) {
            if (!isset($data[$field]) && strpos($rules, 'required') !== false) {
                $this->errors[$field] = "The {$field} field is required.";
                continue;
            }
            
            if (!isset($data[$field])) {
                continue; // Skip validation for optional fields that aren't present
            }
            
            $value = $data[$field];
            $ruleList = explode('|', $rules);
            
            foreach ($ruleList as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParam = isset($ruleParts[1]) ? $ruleParts[1] : null;
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value) && $value !== '0') {
                            $this->errors[$field] = "The {$field} field is required.";
                        }
                        break;
                        
                    case 'min':
                        if (strlen($value) < $ruleParam) {
                            $this->errors[$field] = "The {$field} field must be at least {$ruleParam} characters.";
                        }
                        break;
                        
                    case 'max':
                        if (strlen($value) > $ruleParam) {
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
                        if (!strtotime($value)) {
                            $this->errors[$field] = "The {$field} field must be a valid date.";
                        }
                        break;
                        
                    case 'in':
                        $allowedValues = explode(',', $ruleParam);
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
        
        return empty($this->errors);
    }
    
    /**
     * Get validation errors
     * 
     * @return array Validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get related records based on a defined relationship
     * 
     * @param string $relation Relationship name
     * @param mixed $id Primary key value of the parent record
     * @return array|null Related records or null on error
     */
    public function getRelated($relation, $id) {
        if (!isset($this->relationships[$relation])) {
            ErrorHandler::log("Relationship '{$relation}' not defined in " . get_class($this), ErrorHandler::LOG_LEVEL_ERROR);
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
            ErrorHandler::logDatabaseError($e, get_class($this) . "::getRelated[{$relation}]");
            return null;
        }
    }
    
    /**
     * Execute a custom SQL query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @param bool $fetchAll Whether to fetch all results or just one row
     * @return array|null Query results or null on error
     */
    public function query($sql, $params = [], $fetchAll = true) {
        try {
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            // Bind parameters if any
            if (!empty($params)) {
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
                call_user_func_array([$stmt, 'bind_param'], $bindParams);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result === false) {
                return ($stmt->affected_rows > 0); // For INSERT, UPDATE, DELETE queries
            }
            
            if ($fetchAll) {
                $data = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $data = $result->fetch_assoc();
            }
            
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, get_class($this) . '::query');
            return null;
        }
    }
}
