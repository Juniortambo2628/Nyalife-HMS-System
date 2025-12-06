<?php

/**
 * Nyalife HMS - Lab Parameter Model
 *
 * Model for handling lab test parameters.
 */

require_once __DIR__ . '/BaseModel.php';

class LabParameterModel extends BaseModel
{
    protected $table = 'lab_parameters';
    protected $primaryKey = 'parameter_id';

    /**
     * Get parameter with test type details
     *
     * @param int $parameterId Parameter ID
     * @return array|null Parameter data with details or null if not found
     */
    public function getParameterWithDetails($parameterId)
    {
        try {
            $sql = "SELECT lp.*, 
                           lt.test_name, lt.description as test_description
                           FROM {$this->table} lp
                           JOIN lab_test_types lt ON lp.test_type_id = lt.test_type_id
                           WHERE lp.parameter_id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $parameterId);
            $stmt->execute();
            $result = $stmt->get_result();
            $parameter = $result->fetch_assoc();
            $stmt->close();

            return $parameter;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get parameters by test type
     *
     * @param int $testTypeId Test type ID
     * @param bool $activeOnly Whether to return only active parameters
     * @return array Array of parameters
     */
    public function getParametersByTestType($testTypeId, $activeOnly = true)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE test_type_id = ?";

            if ($activeOnly) {
                $sql .= " AND is_active = 1";
            }

            $sql .= " ORDER BY display_order ASC, parameter_name ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $testTypeId);
            $stmt->execute();
            $result = $stmt->get_result();
            $parameters = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $parameters;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get all active parameters
     *
     * @param string $search Optional search term
     * @return array Array of parameters
     */
    public function getActiveParameters($search = '')
    {
        try {
            $sql = "SELECT lp.*, lt.test_name
                    FROM {$this->table} lp
                    JOIN lab_test_types lt ON lp.test_type_id = lt.test_type_id
                    WHERE lp.is_active = 1";

            $params = [];

            if (!empty($search)) {
                $searchTerm = "%{$search}%";
                $sql .= " AND (lp.parameter_name LIKE ? OR lp.description LIKE ? OR lt.test_name LIKE ?)";
                $params = [$searchTerm, $searchTerm, $searchTerm];
            }

            $sql .= " ORDER BY lt.test_name ASC, lp.display_order ASC, lp.parameter_name ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            if ($params !== []) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $parameters = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $parameters;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get parameters with filters
     *
     * @param array $filters Filters to apply
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Array of parameters
     */
    public function getParametersFiltered(array $filters = [], $page = 1, $perPage = 20)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT lp.*, lt.test_name, lt.description as test_description
                    FROM {$this->table} lp
                    JOIN lab_test_types lt ON lp.test_type_id = lt.test_type_id
                    WHERE 1=1";

            $params = [];
            $types = '';

            if (!empty($filters['test_type_id'])) {
                $sql .= " AND lp.test_type_id = ?";
                $params[] = $filters['test_type_id'];
                $types .= 'i';
            }

            if (!empty($filters['is_active'])) {
                $sql .= " AND lp.is_active = ?";
                $params[] = $filters['is_active'];
                $types .= 'i';
            }

            if (!empty($filters['search'])) {
                $searchTerm = "%{$filters['search']}%";
                $sql .= " AND (lp.parameter_name LIKE ? OR lp.description LIKE ? OR lt.test_name LIKE ?)";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
                $types .= 'sss';
            }

            $sql .= " ORDER BY lt.test_name ASC, lp.display_order ASC, lp.parameter_name ASC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            $types .= 'ii';

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            // $params always has at least $perPage and $offset, so always bind
            $stmt->bind_param($types, ...$params);

            $stmt->execute();
            $result = $stmt->get_result();
            $parameters = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $parameters;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count parameters with filters
     *
     * @param array $filters Filters to apply
     * @return int Count of parameters
     */
    public function countParametersFiltered(array $filters = [])
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} lp
                    JOIN lab_test_types lt ON lp.test_type_id = lt.test_type_id
                    WHERE 1=1";

            $params = [];
            $types = '';

            if (!empty($filters['test_type_id'])) {
                $sql .= " AND lp.test_type_id = ?";
                $params[] = $filters['test_type_id'];
                $types .= 'i';
            }

            if (!empty($filters['is_active'])) {
                $sql .= " AND lp.is_active = ?";
                $params[] = $filters['is_active'];
                $types .= 'i';
            }

            if (!empty($filters['search'])) {
                $searchTerm = "%{$filters['search']}%";
                $sql .= " AND (lp.parameter_name LIKE ? OR lp.description LIKE ? OR lt.test_name LIKE ?)";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
                $types .= 'sss';
            }

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            if ($params !== []) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row['count'] ?? 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Create a new parameter
     *
     * @param array $data Parameter data
     * @return int|false New parameter ID or false on failure
     */
    public function createParameter($data)
    {
        try {
            $this->beginTransaction();

            $sql = "INSERT INTO {$this->table} (
                        test_type_id, parameter_name, description, 
                        normal_range, units, display_order, is_active, 
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $testTypeId = $data['test_type_id'] ?? null;
            $parameterName = $data['parameter_name'] ?? '';
            $description = $data['description'] ?? '';
            $normalRange = $data['normal_range'] ?? '';
            $units = $data['units'] ?? '';
            $displayOrder = $data['display_order'] ?? 0;
            $isActive = $data['is_active'] ?? 1;
            $createdAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'issssiis',
                $testTypeId,
                $parameterName,
                $description,
                $normalRange,
                $units,
                $displayOrder,
                $isActive,
                $createdAt
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create parameter");
            }

            $parameterId = $stmt->insert_id;
            $stmt->close();

            $this->commitTransaction();
            return $parameterId;
        } catch (Exception $e) {
            $this->rollbackTransaction();
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update parameter
     *
     * @param int $parameterId Parameter ID
     * @param array $data Parameter data
     * @return bool Success status
     */
    public function updateParameter($parameterId, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                    test_type_id = ?, parameter_name = ?, description = ?, 
                    normal_range = ?, units = ?, display_order = ?, 
                    is_active = ?, updated_at = ?
                    WHERE {$this->primaryKey} = ?";

            $testTypeId = $data['test_type_id'] ?? null;
            $parameterName = $data['parameter_name'] ?? '';
            $description = $data['description'] ?? '';
            $normalRange = $data['normal_range'] ?? '';
            $units = $data['units'] ?? '';
            $displayOrder = $data['display_order'] ?? 0;
            $isActive = $data['is_active'] ?? 1;
            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'issssiisi',
                $testTypeId,
                $parameterName,
                $description,
                $normalRange,
                $units,
                $displayOrder,
                $isActive,
                $updatedAt,
                $parameterId
            );

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Toggle parameter active status
     *
     * @param int $parameterId Parameter ID
     * @return bool Success status
     */
    public function toggleActiveStatus($parameterId)
    {
        try {
            $sql = "UPDATE {$this->table} SET is_active = NOT is_active, updated_at = ? WHERE {$this->primaryKey} = ?";

            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('si', $updatedAt, $parameterId);

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete parameter
     *
     * @param int $parameterId Parameter ID
     * @return bool Success status
     */
    public function deleteParameter($parameterId)
    {
        try {
            // Check if parameter is in use
            $sql = "SELECT COUNT(*) as count FROM lab_test_items WHERE parameter_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $parameterId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($row['count'] > 0) {
                throw new Exception("Cannot delete parameter that is in use");
            }

            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $parameterId);

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get parameter statistics
     *
     * @return array Parameter statistics
     */
    public function getParameterStatistics()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_parameters,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_parameters,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_parameters,
                        COUNT(DISTINCT test_type_id) as test_types_with_parameters
                    FROM {$this->table}";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $statistics = $result->fetch_assoc();
            $stmt->close();

            return $statistics;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [
                'total_parameters' => 0,
                'active_parameters' => 0,
                'inactive_parameters' => 0,
                'test_types_with_parameters' => 0
            ];
        }
    }

    /**
     * Get parameters by test type with results
     *
     * @param int $testTypeId Test type ID
     * @param int $sampleId Sample ID
     * @return array Array of parameters with results
     */
    public function getParametersWithResults($testTypeId, $sampleId)
    {
        try {
            $sql = "SELECT lp.*, lr.result_value, lr.is_abnormal, lr.notes as result_notes
                    FROM {$this->table} lp
                    LEFT JOIN lab_results lr ON lp.parameter_id = lr.parameter_id AND lr.sample_id = ?
                    WHERE lp.test_type_id = ? AND lp.is_active = 1
                    ORDER BY lp.display_order ASC, lp.parameter_name ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ii', $sampleId, $testTypeId);
            $stmt->execute();
            $result = $stmt->get_result();
            $parameters = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $parameters;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
}
