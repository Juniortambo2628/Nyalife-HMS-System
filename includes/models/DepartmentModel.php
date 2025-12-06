<?php

/**
 * Nyalife HMS - Department Model
 *
 * Model for handling department data.
 */

require_once __DIR__ . '/BaseModel.php';

class DepartmentModel extends BaseModel
{
    protected $table = 'departments';
    protected $primaryKey = 'department_id';

    /**
     * Get all departments with staff count
     *
     * @return array Array of departments with staff count
     */
    public function getAllDepartmentsWithStaffCount()
    {
        try {
            $sql = "SELECT d.*, COUNT(s.staff_id) as staff_count
                    FROM {$this->table} d
                    LEFT JOIN staff s ON d.department_id = s.department_id
                    GROUP BY d.department_id
                    ORDER BY d.name ASC";

            $result = $this->db->query($sql);

            if (!$result) {
                throw new Exception("Query failed: " . $this->db->error);
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get department by name
     *
     * @param string $name Department name
     * @return array|null Department data or null if not found
     */
    public function getByName($name)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE name = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('s', $name);
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
     * Get active departments only
     *
     * @return array Array of active departments
     */
    public function getActiveDepartments()
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name ASC";
            $result = $this->db->query($sql);

            if (!$result) {
                throw new Exception("Query failed: " . $this->db->error);
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get staff in a department
     *
     * @param int $departmentId Department ID
     * @return array Array of staff in the department
     */
    public function getStaffInDepartment($departmentId)
    {
        try {
            $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone
                    FROM staff s
                    JOIN users u ON s.user_id = u.user_id
                    WHERE s.department_id = ?
                    ORDER BY u.last_name, u.first_name ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $departmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $staff = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $staff;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Create a new department
     *
     * @param array $data Department data
     * @return int|false New department ID or false on failure
     */
    public function createDepartment($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (name, description, is_active, created_at) 
                    VALUES (?, ?, ?, ?)";

            $name = $data['name'] ?? '';
            $description = $data['description'] ?? '';
            $isActive = isset($data['is_active']) ? 1 : 1; // Default to active
            $createdAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ssis', $name, $description, $isActive, $createdAt);

            if ($stmt->execute()) {
                return $stmt->insert_id;
            }

            return false;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update department
     *
     * @param int $departmentId Department ID
     * @param array $data Updated department data
     * @return bool Success status
     */
    public function updateDepartment($departmentId, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                    name = ?, 
                    description = ?, 
                    is_active = ?, 
                    updated_at = ?
                    WHERE {$this->primaryKey} = ?";

            $name = $data['name'] ?? '';
            $description = $data['description'] ?? '';
            $isActive = isset($data['is_active']) ? 1 : 0;
            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ssisi', $name, $description, $isActive, $updatedAt, $departmentId);

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Check if department has staff
     *
     * @param int $departmentId Department ID
     * @return bool True if department has staff
     */
    public function hasStaff($departmentId)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM staff WHERE department_id = ?";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $departmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int)$row['count'] > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get department statistics
     *
     * @return array Department statistics
     */
    public function getDepartmentStatistics()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_departments,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_departments,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_departments
                    FROM {$this->table}";

            $result = $this->db->query($sql);

            if (!$result) {
                throw new Exception("Query failed: " . $this->db->error);
            }

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [
                'total_departments' => 0,
                'active_departments' => 0,
                'inactive_departments' => 0
            ];
        }
    }
}
