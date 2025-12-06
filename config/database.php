<?php
/**
 * Nyalife HMS Database Configuration
 * 
 * This file contains the configuration settings for the database connection.
 */

function connectDB() {
    // Load DB credentials from .env variables
    $host = $_ENV['DB_HOST'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];
    $name = $_ENV['DB_NAME'];

    $conn = new mysqli($host, $user, $pass, $name);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}


// Function to sanitize input data
function sanitize($conn, $data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($conn, $value);
        }
    } else {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = $conn->real_escape_string($data);
    }
    return $data;
}

// Common database operations

// Function to execute a SELECT query and return results as an associative array
function selectQuery($sql, $params = []) {
    $conn = connectDB();
    $result = [];
    
    try {
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
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
                    // Store by reference in array
                    $bindParams[] = &$params[$key];
                }
                
                // Create a dynamic call to bind_param with proper references
                $refParams = array_merge(array($types), $bindParams);
                call_user_func_array(array($stmt, 'bind_param'), $refParams);
            }
            
            $stmt->execute();
            $query_result = $stmt->get_result();
            
            if ($query_result) {
                while ($row = $query_result->fetch_assoc()) {
                    $result[] = $row;
                }
            }
            
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
    
    $conn->close();
    return $result;
}

// Function to execute an INSERT, UPDATE, or DELETE query
function executeQuery($sql, $params = []) {
    $conn = connectDB();
    $result = false;
    
    try {
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
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
                    // Store by reference in array
                    $bindParams[] = &$params[$key];
                }
                
                // Create a dynamic call to bind_param with proper references
                $refParams = array_merge(array($types), $bindParams);
                call_user_func_array(array($stmt, 'bind_param'), $refParams);
            }
            
            $result = $stmt->execute();
            
            if ($result && strpos(strtoupper($sql), 'INSERT') === 0) {
                $result = $stmt->insert_id;
            }
            
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
    
    $conn->close();
    return $result;
}

// Function to get a single row from a SELECT query
function selectSingle($sql, $params = []) {
    $result = selectQuery($sql, $params);
    return !empty($result) ? $result[0] : null;
}

// Function to begin a transaction
function beginTransaction() {
    $conn = connectDB();
    $conn->begin_transaction();
    return $conn;
}

// Function to commit a transaction
function commitTransaction($conn) {
    $conn->commit();
    $conn->close();
}

// Function to rollback a transaction
function rollbackTransaction($conn) {
    $conn->rollback();
    $conn->close();
}
?> 