<?php
/**
 * Nyalife HMS - Department Data Functions
 *
 * Contains functions for retrieving and manipulating department data.
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get all departments with staff count
 *
 * @return array Array of departments with staff count
 */
function getAllDepartmentsWithStaffCount()
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in getAllDepartmentsWithStaffCount");
        return [];
    }

    $query = "SELECT d.*, COUNT(s.staff_id) as staff_count
              FROM departments d
              LEFT JOIN staff s ON d.department_id = s.department_id
              GROUP BY d.department_id
              ORDER BY d.department_name ASC";

    $result = $conn->query($query);

    if (!$result) {
        $conn->close();
        return [];
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $data;
}

/**
 * Get department by ID
 *
 * @param int $departmentId Department ID
 * @return array|null Department data or null if not found
 */
function getDepartmentById($departmentId)
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in function");
        return [];
    }

    $query = "SELECT * FROM departments WHERE department_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Get department by name
 *
 * @param string $name Department name
 * @return array|null Department data or null if not found
 */
function getDepartmentByName($name)
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in function");
        return [];
    }

    $query = "SELECT * FROM departments WHERE department_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Get active departments only
 *
 * @return array Array of active departments
 */
function getActiveDepartments()
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in function");
        return [];
    }

    $query = "SELECT * FROM departments WHERE is_active = 1 ORDER BY department_name ASC";
    $result = $conn->query($query);

    if (!$result) {
        $conn->close();
        return [];
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $data;
}

/**
 * Get staff in a department
 *
 * @param int $departmentId Department ID
 * @return array Array of staff in the department
 */
function getStaffInDepartment($departmentId)
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in function");
        return [];
    }

    $query = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone,
              u.gender, u.date_of_birth, u.profile_image
              FROM staff s
              JOIN users u ON s.user_id = u.user_id
              WHERE s.department_id = ?
              ORDER BY u.last_name, u.first_name ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $data;
}

/**
 * Create a new department
 *
 * @param array $data Department data
 * @return int|false New department ID or false on failure
 */
function createDepartment($data)
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in createDepartment");
        return false;
    }

    if (!isset($data['name']) || empty($data['name'])) {
        return false;
    }

    $query = "INSERT INTO departments (department_name, description, is_active, created_at) 
              VALUES (?, ?, ?, ?)";

    $name = $data['name'];
    $description = $data['description'] ?? '';
    $isActive = isset($data['is_active']) ? 1 : 1; // Default to active
    $createdAt = date('Y-m-d H:i:s');

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssis", $name, $description, $isActive, $createdAt);

    if ($stmt->execute()) {
        return $stmt->insert_id;
    }

    return false;
}

/**
 * Update department
 *
 * @param int $departmentId Department ID
 * @param array $data Updated department data
 * @return bool Success status
 */
function updateDepartment($departmentId, $data)
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in updateDepartment");
        return false;
    }

    $query = "UPDATE departments SET 
              department_name = ?, 
              description = ?, 
              is_active = ?, 
              updated_at = ?
              WHERE department_id = ?";

    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';
    $isActive = isset($data['is_active']) ? 1 : 0;
    $updatedAt = date('Y-m-d H:i:s');

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisi", $name, $description, $isActive, $updatedAt, $departmentId);

    return $stmt->execute() && $stmt->affected_rows > 0;
}

/**
 * Delete department
 *
 * @param int $departmentId Department ID
 * @return bool Success status
 */
function deleteDepartment($departmentId)
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in deleteDepartment");
        return false;
    }

    // Check if department has staff
    $query = "SELECT COUNT(*) as count FROM staff WHERE department_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        return false; // Cannot delete department with staff
    }

    $query = "DELETE FROM departments WHERE department_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $departmentId);

    return $stmt->execute() && $stmt->affected_rows > 0;
}

/**
 * Get department statistics
 *
 * @return array Department statistics
 */
function getDepartmentStatistics()
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in function");
        return [];
    }

    $query = "SELECT 
                COUNT(*) as total_departments,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_departments,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_departments
              FROM departments";

    $result = $conn->query($query);

    if (!$result) {
        return [
            'total_departments' => 0,
            'active_departments' => 0,
            'inactive_departments' => 0
        ];
    }

    return $result->fetch_assoc();
}

/**
 * Search departments
 *
 * @param string $searchTerm Search term
 * @return array Array of matching departments
 */
function searchDepartments($searchTerm)
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in function");
        return [];
    }

    $query = "SELECT d.*, COUNT(s.staff_id) as staff_count
              FROM departments d
              LEFT JOIN staff s ON d.department_id = s.department_id
              WHERE d.department_name LIKE ? OR d.description LIKE ?
              GROUP BY d.department_id
              ORDER BY d.department_name ASC";

    $searchPattern = "%{$searchTerm}%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $searchPattern, $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $data;
}

/**
 * Get departments with most staff
 *
 * @param int $limit Maximum number of departments to return
 * @return array Array of departments with staff count
 */
function getDepartmentsWithMostStaff($limit = 5)
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in function");
        return [];
    }

    $query = "SELECT d.*, COUNT(s.staff_id) as staff_count
              FROM departments d
              LEFT JOIN staff s ON d.department_id = s.department_id
              GROUP BY d.department_id
              ORDER BY staff_count DESC
              LIMIT ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $data;
}

/**
 * Check if department name exists
 *
 * @param string $name Department name
 * @param int $excludeId Department ID to exclude from check
 * @return bool True if name exists
 */
function departmentNameExists($name, $excludeId = null)
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in departmentNameExists");
        return false;
    }

    $query = "SELECT department_id FROM departments WHERE department_name = ?";
    $params = [$name];
    $types = "s";

    if ($excludeId) {
        $query .= " AND department_id != ?";
        $params[] = $excludeId;
        $types .= "i";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

/**
 * Get all staff members for dropdown selection
 *
 * @return array Array of staff members
 */
function getAllStaff()
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in getAllStaff");
        return [];
    }

    $query = "SELECT s.staff_id, s.user_id, u.first_name, u.last_name, u.role
              FROM staff s
              JOIN users u ON s.user_id = u.user_id
              WHERE s.is_active = 1
              ORDER BY u.last_name, u.first_name ASC";

    $result = $conn->query($query);

    if (!$result) {
        error_log("Query failed in getAllStaff: " . $conn->error);
        $conn->close();
        return [];
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $data;
}
