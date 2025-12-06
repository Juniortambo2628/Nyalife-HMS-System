<?php
/**
 * Nyalife HMS Database Configuration
 * 
 * This file contains the configuration settings for the database connection.
 */

if (!function_exists('connectDB')) {
    function connectDB() {
        // Check if we're in development environment (localhost)
        $isLocal = (isset($_SERVER['HTTP_HOST']) && 
                   ($_SERVER['HTTP_HOST'] === 'localhost' || 
                    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));
        
        if ($isLocal) {
            // Use XAMPP default credentials for local development
            $host = 'localhost';
            $user = 'root';
            $pass = '';
            $name = 'nyalifew_hms_prod';
        } else {
            // Load DB credentials from .env variables with fallbacks for production
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
            $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'nyalifew_admin_prod';
            $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? 'NYALIFEADMIN123';
            $name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'nyalifew_hms_prod';
        }

        $conn = new mysqli($host, $user, $pass, $name);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $conn->set_charset("utf8mb4");
        return $conn;
    }
}

// Function to sanitize input data
if (!function_exists('sanitize')) {
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
}

// Common database operations

// Function to execute a SELECT query and return results as an associative array
if (!function_exists('selectQuery')) {
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
}

// Function to execute an INSERT, UPDATE, or DELETE query
if (!function_exists('executeQuery')) {
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
}

// Function to get a single row from a SELECT query
if (!function_exists('selectSingle')) {
    function selectSingle($sql, $params = []) {
        $result = selectQuery($sql, $params);
        return !empty($result) ? $result[0] : null;
    }
}

// Function to begin a transaction
if (!function_exists('beginTransaction')) {
    function beginTransaction() {
        $conn = connectDB();
        $conn->begin_transaction();
        return $conn;
    }
}

// Function to commit a transaction
if (!function_exists('commitTransaction')) {
    function commitTransaction($conn) {
        $success = $conn->commit();
        $conn->close();
        return $success;
    }
}

// Function to rollback a transaction
if (!function_exists('rollbackTransaction')) {
    function rollbackTransaction($conn) {
        $success = $conn->rollback();
        $conn->close();
        return $success;
    }
}