<?php

/**
 * Nyalife HMS - User Model
 *
 * Model for handling user data.
 */

require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $rolesTable = 'roles';

    /**
     * Get user by username
     *
     * @param string $username Username
     * @return array|null User data or null if not found
     */
    public function getByUsername($username)
    {
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
    public function getByEmail($email)
    {
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
    public function getWithRole($userId)
    {
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
    public function authenticate($username, $password)
    {
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
    private function updateLastLogin($userId): bool
    {
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
    public function createRememberToken($userId, $days = 30): string|false
    {
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
    public function getActiveUserCount(): int
    {
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
    public function checkRememberToken($token)
    {
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
    public function deleteRememberToken($token): bool
    {
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
     * @param bool $manageTransaction Whether to manage transaction internally (default: true)
     * @return int|false User ID or false on failure
     */
    public function createUser($userData, $manageTransaction = true)
    {
        try {
            // Debug: Log the user data
            error_log("UserModel::createUser - Input data: " . print_r($userData, true));

            // Ensure password is hashed
            if (isset($userData['password']) && !empty($userData['password'])) {
                $userData['password'] = password_hash((string) $userData['password'], PASSWORD_DEFAULT);
                error_log("UserModel::createUser - Password hashed successfully");
            } else {
                error_log("UserModel::createUser - Warning: Empty password provided");
                // Provide a default password if none is provided (not recommended for production)
                $userData['password'] = password_hash('ChangeMe123!', PASSWORD_DEFAULT);
            }

            // Set default values
            if (!isset($userData['is_active'])) {
                $userData['is_active'] = 1;
            }

            if (!isset($userData['created_at'])) {
                $userData['created_at'] = date('Y-m-d H:i:s');
            }

            // Ensure all required fields are present
            $requiredFields = ['username', 'password', 'role_id', 'first_name', 'last_name', 'email'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (!isset($userData[$field]) || (empty($userData[$field]) && $field !== 'password')) {
                    $missingFields[] = $field;
                }
            }

            if ($missingFields !== []) {
                error_log("UserModel::createUser - Missing required fields: " . implode(', ', $missingFields));
                error_log("UserModel::createUser - Available fields: " . implode(', ', array_keys($userData)));
                return false;
            }

            // Remove any non-database fields
            $nonDbFields = ['confirm_password', 'role', 'terms'];
            foreach ($nonDbFields as $field) {
                if (isset($userData[$field])) {
                    unset($userData[$field]);
                }
            }

            // Begin transaction only if managing transaction internally
            if ($manageTransaction) {
                $this->db->begin_transaction();
            }

            // Call the base create method
            $userId = $this->create($userData);

            if (!$userId) {
                error_log("UserModel::createUser - User creation failed in base create method");
                if ($manageTransaction) {
                    $this->db->rollback();
                }
                return false;
            }

            // If we got here, everything succeeded
            if ($manageTransaction) {
                $this->db->commit();
            }
            error_log("UserModel::createUser - User created successfully with ID: " . $userId);

            return $userId;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            error_log("UserModel::createUser - Exception: " . $e->getMessage());
            error_log("UserModel::createUser - Stack trace: " . $e->getTraceAsString());
            if ($manageTransaction) {
                $this->db->rollback();
            }
            return false;
        }
    }

    /**
     * Update a user's profile
     *
     * @param int $userId User ID
     * @param array $userData User data
     * @return bool Success status
     */
    public function updateProfile($userId, $userData): bool
    {
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
    public function changePassword($userId, $newPassword): bool
    {
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
    public function getAllUsers()
    {
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
    public function getRecentUsers($limit)
    {
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
    public function getUserById($userId)
    {
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
    public function getAllRoles()
    {
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
    public function emailExists($email, $excludeUserId = null)
    {
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
    public function usernameExists($username, $excludeUserId = null)
    {
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
    public function updateUser($userId, $userData)
    {
        // Prevent certain fields from being updated directly
        unset($userData['user_id'], $userData['username'], $userData['role_id'], $userData['password'], $userData['current_password']);

        if (empty($userData)) {
            return true; // Nothing to update
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
    public function deleteUser($userId)
    {
        // First check if user exists
        $user = $this->find($userId);
        if ($user === null || $user === []) {
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
    public function getDoctorIdByUserId($userId): int|false
    {
        try {
            // First check if the user has a doctor role
            $sql = "SELECT s.staff_id 
                    FROM staff s 
                    JOIN users u ON s.user_id = u.user_id 
                    JOIN roles r ON u.role_id = r.role_id 
                    WHERE s.user_id = ? AND r.role_name = 'doctor' 
                    LIMIT 1";

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
    public function getDoctors()
    {
        return $this->getAllDoctors(true);
    }

    /**
     * Get all doctors
     *
     * @param bool $activeOnly Whether to return only active doctors
     * @return array Array of doctors
     */
    public function getAllDoctors($activeOnly = false)
    {
        try {
            $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone, u.profile_image,
                           s.staff_id, s.specialization, s.department, s.position, s.qualification
                    FROM users u
                    JOIN staff s ON u.user_id = s.user_id
                    JOIN roles r ON u.role_id = r.role_id
                    WHERE r.role_name = 'doctor'";

            if ($activeOnly) {
                $sql .= " AND u.is_active = 1";
            }

            $sql .= " ORDER BY u.last_name, u.first_name";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $doctors = [];
            while ($row = $result->fetch_assoc()) {
                $doctors[] = $row;
            }

            $stmt->close();
            return $doctors;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get users by role name
     *
     * @param string $roleName Role name to filter by
     * @return array List of users with the specified role
     */
    public function getUsersByRole($roleName)
    {
        try {
            $sql = "SELECT u.user_id, u.username, u.first_name, u.last_name, 
                          u.email, u.phone, u.is_active, r.role_name 
                    FROM {$this->table} u 
                    JOIN roles r ON u.role_id = r.role_id 
                    WHERE r.role_name = ? AND u.is_active = 1
                    ORDER BY u.first_name, u.last_name";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('s', $roleName);
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
     * Get all active users (excluding specified user ID)
     *
     * @param int $excludeUserId User ID to exclude from results
     * @return array Array of active users
     */
    public function getAllActiveUsers($excludeUserId = null)
    {
        try {
            $sql = "SELECT u.user_id, u.username, u.first_name, u.last_name, 
                           u.email, u.phone, r.role_name as role
                    FROM {$this->table} u 
                    JOIN roles r ON u.role_id = r.role_id 
                    WHERE u.is_active = 1";

            $params = [];
            $types = '';

            if ($excludeUserId) {
                $sql .= " AND u.user_id != ?";
                $params[] = $excludeUserId;
                $types .= 'i';
            }

            $sql .= " ORDER BY r.role_name, u.first_name, u.last_name";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

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
     * Get total count of users
     *
     * @return int Total user count
     */
    public function getCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
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
            return 0;
        }
    }

    /**
     * Get count of users by role
     *
     * @param string $roleName Role name to count
     * @return int Count of users with the specified role
     */
    public function getCountByRole($roleName): int
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM {$this->table} u 
                    JOIN roles r ON u.role_id = r.role_id 
                    WHERE r.role_name = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('s', $roleName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int)$row['count'];
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Store password reset token
     *
     * @param int $userId User ID
     * @param string $token Reset token
     * @param string $expires Expiration date
     * @return bool Success status
     */
    public function storePasswordResetToken($userId, $token, $expires)
    {
        try {
            // First, delete any existing tokens for this user
            $this->deletePasswordResetTokenByUserId($userId);

            $sql = "INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at) 
                    VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('iss', $userId, $token, $expires);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get password reset token data
     *
     * @param string $token Reset token
     * @return array|null Token data or null if not found
     */
    public function getPasswordResetToken($token)
    {
        try {
            $sql = "SELECT user_id, token, expires_at, created_at 
                    FROM password_reset_tokens 
                    WHERE token = ? AND expires_at > NOW()";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();

            return $data;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Delete password reset token
     *
     * @param string $token Reset token
     * @return bool Success status
     */
    public function deletePasswordResetToken($token)
    {
        try {
            $sql = "DELETE FROM password_reset_tokens WHERE token = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('s', $token);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete password reset tokens by user ID
     *
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deletePasswordResetTokenByUserId($userId)
    {
        try {
            $sql = "DELETE FROM password_reset_tokens WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $userId);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Clean up expired password reset tokens
     *
     * @return bool Success status
     */
    public function cleanupExpiredResetTokens()
    {
        try {
            $sql = "DELETE FROM password_reset_tokens WHERE expires_at < NOW()";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
}
