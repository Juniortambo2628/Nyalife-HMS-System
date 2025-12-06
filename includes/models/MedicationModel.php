<?php
/**
 * Medication Model
 * 
 * Handles all database operations related to medications and inventory
 */
class MedicationModel extends BaseModel {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'medications';
        $this->primaryKey = 'medication_id';
    }
    
    /**
     * Get all medications with optional filters
     * 
     * @param string $search Search term for medication name or code
     * @param string $category Filter by category
     * @param bool $inStockOnly Only return medications with stock > 0
     * @param int $page Page number for pagination
     * @param int $perPage Number of items per page
     * @return array Array of medications
     */
    public function getAllMedications($search = '', $category = '', $inStockOnly = false, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT m.*, c.category_name, 
                      (SELECT COUNT(*) FROM medication_batches b WHERE b.medication_id = m.medication_id AND b.expiry_date >= CURDATE() AND b.quantity > 0) as batch_count,
                      (SELECT SUM(quantity) FROM medication_batches b WHERE b.medication_id = m.medication_id AND b.expiry_date >= CURDATE() AND b.quantity > 0) as total_stock
                   FROM {$this->table} m
                   LEFT JOIN medication_categories c ON m.category_id = c.category_id
                   WHERE 1=1";
            
            $params = [];
            $types = '';
            
            if (!empty($search)) {
                $sql .= " AND (m.medication_name LIKE ? OR m.generic_name LIKE ? OR m.code LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= 'sss';
            }
            
            if (!empty($category)) {
                $sql .= " AND m.category_id = ?";
                $params[] = $category;
                $types .= 'i';
            }
            
            if ($inStockOnly) {
                $sql .= " HAVING total_stock > 0";
            }
            
            $sql .= " ORDER BY m.medication_name ASC";
            
            // Add pagination
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            $types .= 'ii';
            
            $stmt = $this->db->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $medications = [];
            while ($row = $result->fetch_assoc()) {
                $medications[] = $row;
            }
            
            return $medications;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get a single medication by ID
     * 
     * @param int $medicationId Medication ID
     * @return array|null Medication data or null if not found
     */
    public function getMedicationById($medicationId) {
        try {
            $sql = "SELECT m.*, c.category_name 
                   FROM {$this->table} m
                   LEFT JOIN medication_categories c ON m.category_id = c.category_id
                   WHERE m.medication_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $medicationId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return null;
            }
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * Get common/popular medications
     * 
     * @param int $limit Number of medications to return
     * @return array Array of common medications
     */
    public function getCommonMedications($limit = 20) {
        try {
            $sql = "SELECT m.*, COUNT(p.medication_id) as prescription_count
                   FROM {$this->table} m
                   LEFT JOIN prescription_items p ON m.medication_id = p.medication_id
                   GROUP BY m.medication_id
                   ORDER BY prescription_count DESC, m.medication_name ASC
                   LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            $medications = [];
            while ($row = $result->fetch_assoc()) {
                $medications[] = $row;
            }
            
            return $medications;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Create a new medication
     * 
     * @param array $data Medication data
     * @return int|bool New medication ID or false on failure
     */
    public function createMedication($data) {
        try {
            $sql = "INSERT INTO {$this->table} (
                code, medication_name, generic_name, category_id, description, 
                unit_type, unit_size, minimum_stock, reorder_quantity, 
                is_active, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'sssissdiis',
                $data['code'],
                $data['medication_name'],
                $data['generic_name'],
                $data['category_id'],
                $data['description'] ?? '',
                $data['unit_type'],
                $data['unit_size'],
                $data['minimum_stock'],
                $data['reorder_quantity'],
                $data['created_by']
            );
            
            $success = $stmt->execute();
            
            if ($success) {
                return $this->db->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Update a medication
     * 
     * @param int $medicationId Medication ID
     * @param array $data Updated medication data
     * @return bool True on success, false on failure
     */
    public function updateMedication($medicationId, $data) {
        try {
            $sql = "UPDATE {$this->table} SET
                code = ?,
                medication_name = ?,
                generic_name = ?,
                category_id = ?,
                description = ?,
                unit_type = ?,
                unit_size = ?,
                minimum_stock = ?,
                reorder_quantity = ?,
                is_active = ?,
                updated_by = ?,
                updated_at = NOW()
                WHERE medication_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'sssissdiiisi',
                $data['code'],
                $data['medication_name'],
                $data['generic_name'],
                $data['category_id'],
                $data['description'] ?? '',
                $data['unit_type'],
                $data['unit_size'],
                $data['minimum_stock'],
                $data['reorder_quantity'],
                $data['is_active'],
                $data['updated_by'],
                $medicationId
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Delete a medication
     * 
     * @param int $medicationId Medication ID
     * @return bool True on success, false on failure
     */
    public function deleteMedication($medicationId) {
        try {
            // Check if medication is in use
            $inUse = $this->isMedicationInUse($medicationId);
            
            if ($inUse) {
                throw new Exception('Cannot delete medication as it is already in use');
            }
            
            // Start transaction
            $this->db->begin_transaction();
            
            try {
                // Delete batches first
                $this->db->query("DELETE FROM medication_batches WHERE medication_id = $medicationId");
                
                // Delete the medication
                $sql = "DELETE FROM {$this->table} WHERE medication_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param('i', $medicationId);
                $success = $stmt->execute();
                
                if ($success) {
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollback();
                    return false;
                }
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            throw $e;
        }
    }
    
    /**
     * Check if a medication is in use (has prescriptions or inventory)
     * 
     * @param int $medicationId Medication ID
     * @return bool True if in use, false otherwise
     */
    public function isMedicationInUse($medicationId) {
        try {
            // Check prescription items
            $sql = "SELECT COUNT(*) as count FROM prescription_items WHERE medication_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $medicationId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result['count'] > 0) {
                return true;
            }
            
            // Check inventory batches
            $sql = "SELECT COUNT(*) as count FROM medication_batches WHERE medication_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $medicationId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            return $result['count'] > 0;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return true; // Assume in use to prevent accidental deletion
        }
    }
    
    /**
     * Get all medication categories
     * 
     * @return array Array of categories
     */
    public function getCategories() {
        try {
            $sql = "SELECT * FROM medication_categories ORDER BY category_name";
            $result = $this->db->query($sql);
            
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            
            return $categories;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get medication batches for a specific medication
     * 
     * @param int $medicationId Medication ID
     * @param bool $inStockOnly Only return batches with quantity > 0
     * @param bool $nonExpiredOnly Only return non-expired batches
     * @return array Array of batches
     */
    public function getBatches($medicationId, $inStockOnly = true, $nonExpiredOnly = true) {
        try {
            $sql = "SELECT * FROM medication_batches WHERE medication_id = ?";
            
            if ($inStockOnly) {
                $sql .= " AND quantity > 0";
            }
            
            if ($nonExpiredOnly) {
                $sql .= " AND (expiry_date IS NULL OR expiry_date >= CURDATE())";
            }
            
            $sql .= " ORDER BY expiry_date ASC, batch_number ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $medicationId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            $batches = [];
            while ($row = $result->fetch_assoc()) {
                $batches[] = $row;
            }
            
            return $batches;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Add a new medication batch
     * 
     * @param array $data Batch data
     * @return int|bool New batch ID or false on failure
     */
    public function addBatch($data) {
        try {
            $sql = "INSERT INTO medication_batches (
                medication_id, batch_number, quantity, unit_cost, selling_price, 
                manufacturing_date, expiry_date, supplier_id, notes, 
                created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'isdddsssis',
                $data['medication_id'],
                $data['batch_number'],
                $data['quantity'],
                $data['unit_cost'],
                $data['selling_price'],
                $data['manufacturing_date'],
                $data['expiry_date'],
                $data['supplier_id'],
                $data['notes'] ?? '',
                $data['created_by']
            );
            
            $success = $stmt->execute();
            
            if ($success) {
                // Update medication stock
                $this->updateStockLevel($data['medication_id']);
                
                return $this->db->insert_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Update a medication batch
     * 
     * @param int $batchId Batch ID
     * @param array $data Updated batch data
     * @return bool True on success, false on failure
     */
    public function updateBatch($batchId, $data) {
        try {
            $sql = "UPDATE medication_batches SET
                batch_number = ?,
                quantity = ?,
                unit_cost = ?,
                selling_price = ?,
                manufacturing_date = ?,
                expiry_date = ?,
                supplier_id = ?,
                notes = ?,
                updated_by = ?,
                updated_at = NOW()
                WHERE batch_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'sdddsssisi',
                $data['batch_number'],
                $data['quantity'],
                $data['unit_cost'],
                $data['selling_price'],
                $data['manufacturing_date'],
                $data['expiry_date'],
                $data['supplier_id'],
                $data['notes'] ?? '',
                $data['updated_by'],
                $batchId
            );
            
            $success = $stmt->execute();
            
            if ($success && isset($data['medication_id'])) {
                // Update medication stock
                $this->updateStockLevel($data['medication_id']);
            }
            
            return $success;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Delete a medication batch
     * 
     * @param int $batchId Batch ID
     * @return bool True on success, false on failure
     */
    public function deleteBatch($batchId) {
        try {
            // Get batch details first
            $sql = "SELECT medication_id, quantity FROM medication_batches WHERE batch_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $batchId);
            $stmt->execute();
            $batch = $stmt->get_result()->fetch_assoc();
            
            if (!$batch) {
                return false;
            }
            
            // Start transaction
            $this->db->begin_transaction();
            
            try {
                // Delete the batch
                $sql = "DELETE FROM medication_batches WHERE batch_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param('i', $batchId);
                $success = $stmt->execute();
                
                if ($success) {
                    // Update medication stock
                    $this->updateStockLevel($batch['medication_id']);
                    
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollback();
                    return false;
                }
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Update the stock level for a medication
     * 
     * @param int $medicationId Medication ID
     * @return bool True on success, false on failure
     */
    public function updateStockLevel($medicationId) {
        try {
            // Calculate total stock from non-expired batches
            $sql = "UPDATE {$this->table} m
                   SET m.current_stock = (
                       SELECT COALESCE(SUM(quantity), 0)
                       FROM medication_batches b
                       WHERE b.medication_id = m.medication_id
                       AND (b.expiry_date IS NULL OR b.expiry_date >= CURDATE())
                   )
                   WHERE m.medication_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $medicationId);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Decrement the stock level for a medication
     * 
     * @param int $medicationId Medication ID
     * @param float $quantity Quantity to decrement
     * @return bool True on success, false on failure
     */
    public function decrementStock($medicationId, $quantity) {
        try {
            // Start transaction
            $this->db->begin_transaction();
            
            try {
                // Get available batches (FIFO - First In First Out)
                $sql = "SELECT * FROM medication_batches 
                       WHERE medication_id = ? 
                       AND quantity > 0 
                       AND (expiry_date IS NULL OR expiry_date >= CURDATE())
                       ORDER BY expiry_date ASC, batch_id ASC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param('i', $medicationId);
                $stmt->execute();
                $batches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                $remainingQty = $quantity;
                
                foreach ($batches as $batch) {
                    if ($remainingQty <= 0) break;
                    
                    $deductQty = min($remainingQty, $batch['quantity']);
                    
                    // Update batch quantity
                    $updateSql = "UPDATE medication_batches 
                                 SET quantity = quantity - ? 
                                 WHERE batch_id = ? AND quantity >= ?";
                    
                    $updateStmt = $this->db->prepare($updateSql);
                    $updateStmt->bind_param('did', $deductQty, $batch['batch_id'], $deductQty);
                    $updateStmt->execute();
                    
                    if ($updateStmt->affected_rows === 0) {
                        throw new Exception("Failed to update stock for batch {$batch['batch_number']}");
                    }
                    
                    $remainingQty -= $deductQty;
                }
                
                if ($remainingQty > 0) {
                    throw new Exception("Insufficient stock for medication ID: $medicationId");
                }
                
                // Update medication stock level
                $this->updateStockLevel($medicationId);
                
                $this->db->commit();
                return true;
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Get low stock medications (below minimum stock level)
     * 
     * @param int $limit Maximum number of items to return
     * @return array Array of low stock medications
     */
    public function getLowStockMedications($limit = 50) {
        try {
            $sql = "SELECT m.*, c.category_name,
                   (SELECT SUM(quantity) FROM medication_batches b 
                    WHERE b.medication_id = m.medication_id 
                    AND (b.expiry_date IS NULL OR b.expiry_date >= CURDATE())
                   ) as current_stock
                   FROM {$this->table} m
                   LEFT JOIN medication_categories c ON m.category_id = c.category_id
                   WHERE m.is_active = 1
                   HAVING current_stock <= m.minimum_stock OR current_stock IS NULL
                   ORDER BY (m.minimum_stock - current_stock) DESC
                   LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            $medications = [];
            while ($row = $result->fetch_assoc()) {
                $medications[] = $row;
            }
            
            return $medications;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get expired or soon-to-expire medications
     * 
     * @param int $daysAhead Number of days ahead to check for expiry
     * @param int $limit Maximum number of items to return
     * @return array Array of expiring medications
     */
    public function getExpiringMedications($daysAhead = 30, $limit = 50) {
        try {
            $sql = "SELECT m.medication_name, b.batch_number, b.quantity, b.expiry_date,
                   DATEDIFF(b.expiry_date, CURDATE()) as days_until_expiry
                   FROM medication_batches b
                   JOIN {$this->table} m ON b.medication_id = m.medication_id
                   WHERE b.quantity > 0
                   AND b.expiry_date IS NOT NULL
                   AND b.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                   ORDER BY b.expiry_date ASC
                   LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $daysAhead, $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            $medications = [];
            while ($row = $result->fetch_assoc()) {
                $medications[] = $row;
            }
            
            return $medications;
            
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
}
