<?php
/**
 * Nyalife HMS - User Model
 * 
 * Model for handling user data.
 */

require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel {
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $rolesTable = 'roles';
    
    /**
     * Get user by username
     * 
     * @param string $username Username
     * @return array|null User data or null if not found
     */
    public function getByUsername($username) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE username = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            return $user;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * Get user by email
     * 
     * @param string $email Email address
     * @return array|null User data or null if not found
     */
    public function getByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            return $user;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * Get user with role information
     * 
     * @param int $userId User ID
     * @return array|null User data with role or null if not found
     */
    public function getWithRole($userId) {
        try {
            $sql = "SELECT u.*, r.role_name, r.role_id 
                    FROM {$this->table} u 
                    JOIN roles r ON u.role_id = r.role_id 
                    WHERE u.user_id = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            return $user;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * Authenticate user
     * 
     * @param string $username Username or email
     * @param string $password Password
     * @return array|false User data if authentication successful, false otherwise
     */
    public function authenticate($username, $password) {
        try {
            $sql = "SELECT u.user_id, u.username, u.password, u.first_name, u.last_name, 
                          u.email, u.is_active, r.role_name, r.role_id 
                    FROM {$this->table} u 
                    JOIN roles r ON u.role_id = r.role_id 
                    WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('ss', $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if (!$user) {
                return false;
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                return false;
            }
            
            // Update last login time
            $this->updateLastLogin($user['user_id']);
            
            return $user;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Update last login time
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    private function updateLastLogin($userId) {
        try {
            $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();
            
            return true;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Create a remember token for a user
     * 
     * @param int $userId User ID
     * @param int $days Days until token expires
     * @return string|false Token or false on failure
     */
    public function createRememberToken($userId, $days = 30) {
        try {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime("+{$days} days"));
            
            $sql = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('iss', $userId, $token, $expires);
            $stmt->execute();
            $stmt->close();
            
            return $token;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get the count of active users
     * 
     * @return int Number of active users
     */
    public function getActiveUserCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_active = 1";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return (int)$row['count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0; // Return 0 on error
        }
    }
    
    /**
     * Check a remember token
     * 
     * @param string $token Remember token
     * @return array|false User data if token valid, false otherwise
     */
    public function checkRememberToken($token) {
        try {
            $sql = "SELECT u.*, r.role_name 
                    FROM remember_tokens rt 
                    JOIN {$this->table} u ON rt.user_id = u.user_id 
                    JOIN roles r ON u.role_id = r.role_id 
                    WHERE rt.token = ? AND rt.expires_at > NOW() AND u.is_active = 1";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            return $user ?: false;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Delete a remember token
     * 
     * @param string $token Remember token
     * @return bool Success status
     */
    public function deleteRememberToken($token) {
        try {
            $sql = "DELETE FROM remember_tokens WHERE token = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $stmt->close();
            
            return true;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Create a new user
     * 
     * @param array $userData User data
     * @return int|false User ID or false on failure
     */
    public function createUser($userData) {
        // Ensure password is hashed
        if (isset($userData['password']) && !empty($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        
        // Set default values
        if (!isset($userData['is_active'])) {
            $userData['is_active'] = 1;
        }
        
        if (!isset($userData['created_at'])) {
            $userData['created_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->create($userData);
    }
    
    /**
     * Update a user's profile
     * 
     * @param int $userId User ID
     * @param array $userData User data
     * @return bool Success status
     */
    public function updateProfile($userId, $userData) {
        // Don't update password through this method
        if (isset($userData['password'])) {
            unset($userData['password']);
        }
        
        // Set updated timestamp
        $userData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($userId, $userData);
    }
    
    /**
     * Change a user's password
     * 
     * @param int $userId User ID
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function changePassword($userId, $newPassword) {
        $data = [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($userId, $data);
    }
    
    /**
     * Get all users with their role information
     * 
     * @return array Array of users
     */
    public function getAllUsers() {
        try {
            $sql = "SELECT u.*, r.role_name 
                    FROM {$this->table} u 
                    JOIN {$this->rolesTable} r ON u.role_id = r.role_id 
                    ORDER BY u.user_id DESC";
            $result = $this->db->query($sql);
            
            if (!$result) {
                throw new Exception("Query execution failed: " . $this->db->error);
            }
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            
            return $users;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get recent users with their role information
     * 
     * @param int $limit Number of recent users to fetch
     * @return array Array of recent users
     */
    public function getRecentUsers($limit) {
        try {
            $sql = "SELECT u.*, r.role_name 
                    FROM {$this->table} u 
                    JOIN {$this->rolesTable} r ON u.role_id = r.role_id 
                    ORDER BY u.user_id DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $stmt->close();
            
            return $users;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get user by ID with role information
     * 
     * @param int $userId User ID
     * @return array|null User data or null if not found
     */
    public function getUserById($userId) {
        try {
            $sql = "SELECT u.*, r.role_name 
                    FROM {$this->table} u 
                    JOIN {$this->rolesTable} r ON u.role_id = r.role_id 
                    WHERE u.{$this->primaryKey} = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            return $user;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * Get all roles
     * 
     * @return array Array of roles
     */
    public function getAllRoles() {
        try {
            $sql = "SELECT * FROM {$this->rolesTable} ORDER BY role_id";
            $result = $this->db->query($sql);
            
            if (!$result) {
                throw new Exception("Query execution failed: " . $this->db->error);
            }
            
            $roles = [];
            while ($row = $result->fetch_assoc()) {
                $roles[] = $row;
            }
            
            return $roles;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Check if an email exists (excluding a specified user)
     * 
     * @param string $email Email to check
     * @param int|null $excludeUserId User ID to exclude from check (for updates)
     * @return bool True if email exists, false otherwise
     */
    public function emailExists($email, $excludeUserId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
            $params = ['s', $email];
            
            if ($excludeUserId) {
                $sql .= " AND {$this->primaryKey} != ?";
                $params = ['si', $email, $excludeUserId];
            }
            
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param(...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Check if a username exists (excluding a specified user)
     * 
     * @param string $username Username to check
     * @param int|null $excludeUserId User ID to exclude from check (for updates)
     * @return bool True if username exists, false otherwise
     */
    public function usernameExists($username, $excludeUserId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
            $params = ['s', $username];
            
            if ($excludeUserId) {
                $sql .= " AND {$this->primaryKey} != ?";
                $params = ['si', $username, $excludeUserId];
            }
            
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param(...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Update a user
     * 
     * @param int $userId User ID
     * @param array $userData User data
     * @return bool Success status
     */
    public function updateUser($userId, $userData) {
        // Handle password separately if provided
        if (isset($userData['password']) && !empty($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        } else if (isset($userData['password'])) {
            // Don't update password if empty
            unset($userData['password']);
        }
        
        // Set updated timestamp
        $userData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($userId, $userData);
    }
    
    /**
     * Delete a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteUser($userId) {
        // First check if user exists
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }
        
        // Delete user
        return $this->delete($userId);
    }
    
    /**
     * Get doctor's staff ID by user ID
     * 
     * @param int $userId User ID
     * @return int|false Staff ID or false if not found
     */
    public function getDoctorIdByUserId($userId) {
        try {
            $sql = "SELECT staff_id FROM staff WHERE user_id = ? AND position LIKE '%doctor%' OR position LIKE '%Doctor%' OR position LIKE '%physician%' OR position LIKE '%Physician%' LIMIT 1";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row ? (int)$row['staff_id'] : false;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Get active doctors
     * 
     * @return array Array of active doctors
     */
    public function getDoctors() {
        return $this->getAllDoctors(true);
    }
    
    /**
     * Get all doctors
     * 
     * @param bool $activeOnly Whether to return only active doctors
     * @return array Array of doctors
     */
    public function getAllDoctors($activeOnly = false) {
        try {
            $sql = "SELECT u.*, r.role_name, s.staff_id, s.department, s.position, s.qualification,
                           s.license_number, s.specialization, s.years_of_experience, s.bio
                    FROM {$this->table} u 
                    JOIN {$this->rolesTable} r ON u.role_id = r.role_id 
                    LEFT JOIN staff s ON u.user_id = s.user_id
                    WHERE r.role_name = 'doctor'";
            
            if ($activeOnly) {
                $sql .= " AND u.is_active = 1";
            }
            
            $sql .= " ORDER BY u.first_name, u.last_name";
            
            $result = $this->db->query($sql);
            
            if (!$result) {
                throw new Exception("Query execution failed: " . $this->db->error);
            }
            
            $doctors = [];
            while ($row = $result->fetch_assoc()) {
                $doctors[] = $row;
            }
            
            return $doctors;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
}
