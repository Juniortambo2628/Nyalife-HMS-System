<?php

/**
 * Nyalife HMS - Doctor Model
 *
 * Model for handling doctor data.
 */

require_once __DIR__ . '/BaseModel.php';

class DoctorModel extends BaseModel
{
    protected $table = 'doctors';
    protected $primaryKey = 'doctor_id';

    /**
     * Get all doctors with user and department information
     *
     * @param bool $activeOnly Whether to return only active doctors
     * @return array Array of doctors
     */
    public function getAllDoctorsWithDetails($activeOnly = true)
    {
        try {
            $sql = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, 
                    u.gender, u.date_of_birth, u.profile_image,
                    dep.name as department_name, s.specialization
                    FROM {$this->table} d
                    JOIN users u ON d.user_id = u.user_id
                    LEFT JOIN departments dep ON d.department_id = dep.department_id
                    LEFT JOIN specializations s ON d.specialization_id = s.specialization_id";

            if ($activeOnly) {
                $sql .= " WHERE d.is_active = 1";
            }

            $sql .= " ORDER BY u.last_name, u.first_name ASC";

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
     * Get doctor by user ID
     *
     * @param int $userId User ID
     * @return array|null Doctor data or null if not found
     */
    public function getByUserId($userId)
    {
        try {
            $sql = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone,
                    u.gender, u.date_of_birth, u.profile_image,
                    dep.name as department_name, s.specialization
                    FROM {$this->table} d
                    JOIN users u ON d.user_id = u.user_id
                    LEFT JOIN departments dep ON d.department_id = dep.department_id
                    LEFT JOIN specializations s ON d.specialization_id = s.specialization_id
                    WHERE d.user_id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $userId);
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
     * Get doctors by department
     *
     * @param int $departmentId Department ID
     * @param bool $activeOnly Whether to return only active doctors
     * @return array Array of doctors in the department
     */
    public function getDoctorsByDepartment($departmentId, $activeOnly = true)
    {
        try {
            $sql = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone,
                    u.gender, u.date_of_birth, u.profile_image,
                    s.specialization
                    FROM {$this->table} d
                    JOIN users u ON d.user_id = u.user_id
                    LEFT JOIN specializations s ON d.specialization_id = s.specialization_id
                    WHERE d.department_id = ?";

            if ($activeOnly) {
                $sql .= " AND d.is_active = 1";
            }

            $sql .= " ORDER BY u.last_name, u.first_name ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $departmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $doctors = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $doctors;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get doctors by specialization
     *
     * @param int $specializationId Specialization ID
     * @param bool $activeOnly Whether to return only active doctors
     * @return array Array of doctors with the specialization
     */
    public function getDoctorsBySpecialization($specializationId, $activeOnly = true)
    {
        try {
            $sql = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone,
                    u.gender, u.date_of_birth, u.profile_image,
                    dep.name as department_name, s.specialization
                    FROM {$this->table} d
                    JOIN users u ON d.user_id = u.user_id
                    LEFT JOIN departments dep ON d.department_id = dep.department_id
                    LEFT JOIN specializations s ON d.specialization_id = s.specialization_id
                    WHERE d.specialization_id = ?";

            if ($activeOnly) {
                $sql .= " AND d.is_active = 1";
            }

            $sql .= " ORDER BY u.last_name, u.first_name ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $specializationId);
            $stmt->execute();
            $result = $stmt->get_result();
            $doctors = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $doctors;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Create a new doctor
     *
     * @param array $data Doctor data
     * @return int|false New doctor ID or false on failure
     */
    public function createDoctor($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (
                        user_id, department_id, specialization_id, 
                        license_number, experience_years, is_active, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $userId = $data['user_id'] ?? null;
            $departmentId = $data['department_id'] ?? null;
            $specializationId = $data['specialization_id'] ?? null;
            $licenseNumber = $data['license_number'] ?? '';
            $experienceYears = $data['experience_years'] ?? 0;
            $isActive = isset($data['is_active']) ? 1 : 1;
            $createdAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'iiisiss',
                $userId,
                $departmentId,
                $specializationId,
                $licenseNumber,
                $experienceYears,
                $isActive,
                $createdAt
            );

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
     * Update doctor
     *
     * @param int $doctorId Doctor ID
     * @param array $data Updated doctor data
     * @return bool Success status
     */
    public function updateDoctor($doctorId, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                    department_id = ?, 
                    specialization_id = ?, 
                    license_number = ?, 
                    experience_years = ?, 
                    is_active = ?, 
                    updated_at = ?
                    WHERE {$this->primaryKey} = ?";

            $departmentId = $data['department_id'] ?? null;
            $specializationId = $data['specialization_id'] ?? null;
            $licenseNumber = $data['license_number'] ?? '';
            $experienceYears = $data['experience_years'] ?? 0;
            $isActive = isset($data['is_active']) ? 1 : 0;
            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'iisissi',
                $departmentId,
                $specializationId,
                $licenseNumber,
                $experienceYears,
                $isActive,
                $updatedAt,
                $doctorId
            );

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get doctor statistics
     *
     * @return array Doctor statistics
     */
    public function getDoctorStatistics()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_doctors,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_doctors,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_doctors,
                        AVG(experience_years) as avg_experience
                    FROM {$this->table}";

            $result = $this->db->query($sql);

            if (!$result) {
                throw new Exception("Query failed: " . $this->db->error);
            }

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [
                'total_doctors' => 0,
                'active_doctors' => 0,
                'inactive_doctors' => 0,
                'avg_experience' => 0
            ];
        }
    }

    /**
     * Search doctors
     *
     * @param string $searchTerm Search term
     * @param bool $activeOnly Whether to return only active doctors
     * @return array Array of matching doctors
     */
    public function searchDoctors($searchTerm, $activeOnly = true)
    {
        try {
            $sql = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone,
                    u.gender, u.date_of_birth, u.profile_image,
                    dep.name as department_name, s.specialization
                    FROM {$this->table} d
                    JOIN users u ON d.user_id = u.user_id
                    LEFT JOIN departments dep ON d.department_id = dep.department_id
                    LEFT JOIN specializations s ON d.specialization_id = s.specialization_id
                    WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR 
                           u.email LIKE ? OR d.license_number LIKE ?)";

            if ($activeOnly) {
                $sql .= " AND d.is_active = 1";
            }

            $sql .= " ORDER BY u.last_name, u.first_name ASC";

            $searchPattern = "%{$searchTerm}%";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ssss', $searchPattern, $searchPattern, $searchPattern, $searchPattern);
            $stmt->execute();
            $result = $stmt->get_result();
            $doctors = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $doctors;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
}
