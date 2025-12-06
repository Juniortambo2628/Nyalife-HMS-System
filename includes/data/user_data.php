<?php
/**
 * Nyalife HMS - User Data Access Layer
 *
 * This file provides standardized functions for user data operations.
 */

require_once __DIR__ . '/../db_utils.php';

/**
 * Get a user by ID
 *
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
if (!function_exists('getUser')) {
    function getUser($userId)
    {
        return dbSelectOne(
            "SELECT u.*, r.role_name
             FROM users u
             JOIN roles r ON u.role_id = r.role_id
             WHERE u.user_id = ?",
            [$userId]
        );
    }
}

/**
 * Get a user by username
 *
 * @param string $username Username
 * @return array|null User data or null if not found
 */
if (!function_exists('getUserByUsername')) {
    function getUserByUsername($username)
    {
        return dbSelectOne(
            "SELECT u.*, r.role_name
             FROM users u
             JOIN roles r ON u.role_id = r.role_id
             WHERE u.username = ?",
            [$username]
        );
    }
}

/**
 * Get a user by email
 *
 * @param string $email Email address
 * @return array|null User data or null if not found
 */
if (!function_exists('getUserByEmail')) {
    function getUserByEmail($email)
    {
        return dbSelectOne(
            "SELECT u.*, r.role_name
             FROM users u
             JOIN roles r ON u.role_id = r.role_id
             WHERE u.email = ?",
            [$email]
        );
    }
}

/**
 * Get all users
 *
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array Users data
 */
function getAllUsers($limit = null, $offset = null)
{
    $sql = "SELECT u.*, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            ORDER BY u.last_name, u.first_name";

    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params = [$limit];

        if ($offset !== null) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
        }

        return dbSelect($sql, $params);
    }

    return dbSelect($sql);
}

/**
 * Get users by role
 *
 * @param int $roleId Role ID
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array Users with the specified role
 */
function getUsersByRole($roleId, $limit = null, $offset = null)
{
    $sql = "SELECT u.*, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            WHERE u.role_id = ?
            ORDER BY u.last_name, u.first_name";

    $params = [$roleId];

    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params[] = $limit;

        if ($offset !== null) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
        }
    }

    return dbSelect($sql, $params);
}

/**
 * Create a new user
 *
 * @param array $userData User data (username, password, etc.)
 * @return int|bool New user ID or false on failure
 */
function createUser($userData)
{
    try {
        // Hash password
        if (isset($userData['password'])) {
            $userData['password'] = password_hash((string) $userData['password'], PASSWORD_DEFAULT);
        }

        // Insert user
        return dbInsert(
            "INSERT INTO users (
                username, password, first_name, last_name, email, 
                phone, gender, date_of_birth, address, role_id, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $userData['username'],
                $userData['password'],
                $userData['first_name'] ?? null,
                $userData['last_name'] ?? null,
                $userData['email'] ?? null,
                $userData['phone'] ?? null,
                $userData['gender'] ?? null,
                $userData['date_of_birth'] ?? null,
                $userData['address'] ?? null,
                $userData['role_id']
            ]
        );
    } catch (Exception $e) {
        // Log error
        if (function_exists('logDatabaseError')) {
            logDatabaseError($e->getMessage());
        }
        return false;
    }
}

/**
 * Update a user
 *
 * @param int $userId User ID
 * @param array $userData User data to update
 * @return bool Success status
 */
function updateUser($userId, $userData)
{
    try {
        // Remove empty password
        if (isset($userData['password']) && empty($userData['password'])) {
            unset($userData['password']);
        } elseif (isset($userData['password'])) {
            // Hash password if provided
            $userData['password'] = password_hash((string) $userData['password'], PASSWORD_DEFAULT);
        }

        // Update user
        $columns = [];
        $params = [];

        foreach ($userData as $key => $value) {
            $columns[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $userId;

        return dbUpdate(
            "UPDATE users SET " . implode(', ', $columns) . " WHERE user_id = ?",
            $params
        );
    } catch (Exception $e) {
        // Log error
        if (function_exists('logDatabaseError')) {
            logDatabaseError($e->getMessage());
        }
        return false;
    }
}

/**
 * Delete a user
 *
 * @param int $userId User ID
 * @return bool Success status
 */
function deleteUser($userId)
{
    try {
        // Set user as inactive instead of deleting
        return dbUpdate(
            "UPDATE users SET is_active = 0, updated_at = NOW() WHERE user_id = ?",
            [$userId]
        );

        // Or actually delete if needed:
        // return dbDelete("DELETE FROM users WHERE user_id = ?", [$userId]);
    } catch (Exception $e) {
        // Log error
        if (function_exists('logDatabaseError')) {
            logDatabaseError($e->getMessage());
        }
        return false;
    }
}

/**
 * Search for users
 *
 * @param string $searchTerm Search term
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array Matching users
 */
function searchUsers($searchTerm, $limit = null, $offset = null)
{
    $searchParam = "%$searchTerm%";

    $sql = "SELECT u.*, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            WHERE u.username LIKE ? 
            OR u.first_name LIKE ? 
            OR u.last_name LIKE ?
            OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
            OR u.email LIKE ?
            OR u.phone LIKE ?
            ORDER BY u.last_name, u.first_name";

    $params = [
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam
    ];

    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params[] = $limit;

        if ($offset !== null) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
        }
    }

    return dbSelect($sql, $params);
}

/**
 * Change user password
 *
 * @param int $userId User ID
 * @param string $newPassword New password
 * @return bool Success status
 */
function changeUserPassword($userId, $newPassword)
{
    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        return dbUpdate(
            "UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?",
            [$hashedPassword, $userId]
        );
    } catch (Exception $e) {
        // Log error
        if (function_exists('logDatabaseError')) {
            logDatabaseError($e->getMessage());
        }
        return false;
    }
}

/**
 * Verify user password
 *
 * @param int $userId User ID
 * @param string $password Password to verify
 * @return bool Whether password is correct
 */
function verifyUserPassword($userId, $password)
{
    $user = getUser($userId);

    if (!$user) {
        return false;
    }

    return password_verify($password, (string) $user['password']);
}

/**
 * Get all roles
 *
 * @return array Roles data
 */
function getAllRoles()
{
    return dbSelect("SELECT * FROM roles ORDER BY role_name");
}

/**
 * Get user notifications
 *
 * @param int $userId User ID
 * @param int $limit Optional limit
 * @param bool $unreadOnly Whether to get only unread notifications
 * @return array User notifications
 */
if (!function_exists('getUserNotifications')) {
    function getUserNotifications($userId, $limit = null, $unreadOnly = false)
    {
        $sql = "SELECT *
                FROM notifications
                WHERE user_id = ?";

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        $sql .= " ORDER BY created_at DESC";

        $params = [$userId];

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }

        return dbSelect($sql, $params);
    }
}

/**
 * Mark a notification as read
 *
 * @param int $notificationId Notification ID
 * @return bool Success status
 */
if (!function_exists('markNotificationAsRead')) {
    function markNotificationAsRead($notificationId)
    {
        return dbUpdate(
            "UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE notification_id = ?",
            [$notificationId]
        );
    }
}

/**
 * Create a notification
 *
 * @param int $userId User ID
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type (info, warning, success, error)
 * @param string $link Optional link
 * @return int|bool New notification ID or false on failure
 */
if (!function_exists('createNotification')) {
    function createNotification($userId, $title, $message, $type = 'info', $link = null)
    {
        return dbInsert(
            "INSERT INTO notifications (user_id, title, message, type, link, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$userId, $title, $message, $type, $link]
        );
    }
}

/**
 * Log user login
 *
 * @param int $userId User ID
 * @param string $ip IP address
 * @param string $userAgent User agent
 * @return bool Success status
 */
function logUserLogin($userId, $ip = null, $userAgent = null)
{
    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    }

    if ($userAgent === null) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    return dbInsert(
        "INSERT INTO login_logs (user_id, ip_address, user_agent, login_time)
         VALUES (?, ?, ?, NOW())",
        [$userId, $ip, $userAgent]
    );
}
