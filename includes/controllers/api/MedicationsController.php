<?php
/**
 * Nyalife HMS - Medications API Controller
 * 
 * This controller handles all medication-related API requests.
 */

require_once __DIR__ . '/ApiController.php';

class MedicationsController extends ApiController {
    
    /**
     * Get all medications with pagination and filtering
     * 
     * @return void
     */
    public function getMedications() {
        // Verify access rights based on user role
        if (!in_array($this->userRole, ['admin', 'doctor', 'pharmacist', 'nurse'])) {
            $this->sendError('Unauthorized access', 403);
            return;
        }
        
        try {
            // Get pagination parameters
            $page = max(1, $this->getIntParam('page') ?: 1);
            $limit = max(10, min(100, $this->getIntParam('limit') ?: 25));
            $offset = ($page - 1) * $limit;
            
            // Get filter parameters
            $search = $this->getParam('search');
            $category = $this->getParam('category');
            $status = $this->getParam('status');
            
            // Build query
            $query = "SELECT * FROM medications WHERE 1=1";
            $countQuery = "SELECT COUNT(*) as total FROM medications WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($search)) {
                $searchTerm = "%$search%";
                $query .= " AND (medication_name LIKE ? OR generic_name LIKE ? OR form LIKE ?)";
                $countQuery .= " AND (medication_name LIKE ? OR generic_name LIKE ? OR form LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($category)) {
                $query .= " AND category = ?";
                $countQuery .= " AND category = ?";
                $params[] = $category;
            }
            
            if (!empty($status)) {
                if ($status === 'active') {
                    $query .= " AND is_active = 1";
                    $countQuery .= " AND is_active = 1";
                } elseif ($status === 'inactive') {
                    $query .= " AND is_active = 0";
                    $countQuery .= " AND is_active = 0";
                }
            }
            
            // Add sorting
            $query .= " ORDER BY medication_name ASC";
            
            // Add pagination
            $query .= " LIMIT ? OFFSET ?";
            $countParams = $params;
            $params[] = $limit;
            $params[] = $offset;
            
            // Execute queries
            $medications = $this->fetchAll($query, $params);
            $totalCount = $this->fetchOne($countQuery, $countParams);
            
            // Calculate pagination metadata
            $totalPages = ceil($totalCount['total'] / $limit);
            
            // Return response
            $this->sendResponse([
                'success' => true,
                'medications' => $medications,
                'pagination' => [
                    'total' => (int)$totalCount['total'],
                    'per_page' => $limit,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving medications: ' . $e->getMessage());
        }
    }
    
    /**
     * Get a single medication by ID
     * 
     * @return void
     */
    public function getMedication() {
        // Verify access rights based on user role
        if (!in_array($this->userRole, ['admin', 'doctor', 'pharmacist', 'nurse'])) {
            $this->sendError('Unauthorized access', 403);
            return;
        }
        
        // Get medication ID
        $medicationId = $this->getIntParam('medication_id');
        
        if (!$medicationId) {
            $this->sendError('Medication ID is required');
            return;
        }
        
        try {
            // Get medication details
            $medication = $this->fetchOne(
                "SELECT * FROM medications WHERE medication_id = ?",
                [$medicationId]
            );
            
            if (!$medication) {
                $this->sendError('Medication not found');
                return;
            }
            
            // Get stock information
            $stock = $this->fetchAll(
                "SELECT * FROM medication_stock WHERE medication_id = ? ORDER BY expiry_date ASC",
                [$medicationId]
            );
            
            // Return medication details
            $this->sendResponse([
                'success' => true,
                'medication' => $medication,
                'stock' => $stock
            ]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving medication: ' . $e->getMessage());
        }
    }
    
    /**
     * Add or update medication
     * 
     * @return void
     */
    public function saveMedication() {
        // Only admin and pharmacist can manage medications
        if ($this->userRole !== 'admin' && $this->userRole !== 'pharmacist') {
            $this->sendError('Unauthorized. Only administrators and pharmacists can manage medications.', 403);
            return;
        }
        
        // Get data from request
        $medicationId = $this->getIntParam('medication_id', 'POST');
        $medicationName = $this->getParam('medication_name', 'POST');
        $genericName = $this->getParam('generic_name', 'POST');
        $category = $this->getParam('category', 'POST');
        $form = $this->getParam('form', 'POST');
        $strength = $this->getParam('strength', 'POST');
        $unit = $this->getParam('unit', 'POST');
        $reorderLevel = $this->getIntParam('reorder_level', 'POST');
        $unitPrice = $this->getFloatParam('unit_price', 'POST');
        $manufacturer = $this->getParam('manufacturer', 'POST');
        $description = $this->getParam('description', 'POST') ?: '';
        $isActive = $this->getParam('is_active', 'POST') !== null ? (bool)$this->getParam('is_active', 'POST') : true;
        
        // Validate required fields
        if (empty($medicationName)) {
            $this->sendError('Medication name is required');
            return;
        }
        
        if (empty($form)) {
            $this->sendError('Medication form is required');
            return;
        }
        
        if (empty($strength)) {
            $this->sendError('Strength is required');
            return;
        }
        
        if (empty($unit)) {
            $this->sendError('Unit is required');
            return;
        }
        
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            if ($medicationId) {
                // Update existing medication
                $sql = "UPDATE medications SET
                        medication_name = ?,
                        generic_name = ?,
                        category = ?,
                        form = ?,
                        strength = ?,
                        unit = ?,
                        reorder_level = ?,
                        unit_price = ?,
                        manufacturer = ?,
                        description = ?,
                        is_active = ?,
                        updated_at = NOW(),
                        updated_by = ?
                        WHERE medication_id = ?";
                
                $params = [
                    $medicationName,
                    $genericName,
                    $category,
                    $form,
                    $strength,
                    $unit,
                    $reorderLevel,
                    $unitPrice,
                    $manufacturer,
                    $description,
                    $isActive ? 1 : 0,
                    $this->userId,
                    $medicationId
                ];
                
                $result = $this->execute($sql, $params);
                
                if (!$result) {
                    $this->conn->rollback();
                    $this->sendError('Failed to update medication');
                    return;
                }
            } else {
                // Check if medication with same name and form already exists
                $existingMed = $this->fetchOne(
                    "SELECT * FROM medications WHERE medication_name = ? AND form = ? AND strength = ? AND unit = ?",
                    [$medicationName, $form, $strength, $unit]
                );
                
                if ($existingMed) {
                    $this->conn->rollback();
                    $this->sendError('A medication with the same name, form, strength and unit already exists');
                    return;
                }
                
                // Create new medication
                $sql = "INSERT INTO medications (
                        medication_name, generic_name, category, form, strength, unit,
                        reorder_level, unit_price, manufacturer, description, is_active,
                        created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $this->conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Query preparation failed: " . $this->conn->error);
                }
                
                $isActiveInt = $isActive ? 1 : 0;
                
                $stmt->bind_param(
                    "ssssssidssis",
                    $medicationName,
                    $genericName,
                    $category,
                    $form,
                    $strength,
                    $unit,
                    $reorderLevel,
                    $unitPrice,
                    $manufacturer,
                    $description,
                    $isActiveInt,
                    $this->userId
                );
                
                $stmt->execute();
                
                $medicationId = $stmt->insert_id;
                
                if (!$medicationId) {
                    $this->conn->rollback();
                    $this->sendError('Failed to create medication');
                    return;
                }
                
                // Log the activity
                $auditDescription = "Added new medication: $medicationName, $strength $unit, $form";
                $auditSQL = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, created_at) 
                            VALUES (?, 'create', 'medication', ?, ?, ?, NOW())";
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                
                $this->execute($auditSQL, [$this->userId, $medicationId, $auditDescription, $ipAddress]);
            }
            
            // Commit transaction
            $this->conn->commit();
            
            // Return success response
            $this->sendResponse([
                'success' => true,
                'medication_id' => $medicationId,
                'message' => ($medicationId ? 'Medication updated successfully' : 'Medication created successfully')
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error saving medication: ' . $e->getMessage());
        }
    }
    
    /**
     * Update medication status (active/inactive)
     * 
     * @return void
     */
    public function toggleStatus() {
        // Only admin and pharmacist can manage medications
        if ($this->userRole !== 'admin' && $this->userRole !== 'pharmacist') {
            $this->sendError('Unauthorized. Only administrators and pharmacists can manage medications.', 403);
            return;
        }
        
        // Get data from request
        $medicationId = $this->getIntParam('medication_id', 'POST');
        $isActive = (bool)$this->getParam('is_active', 'POST');
        
        if (!$medicationId) {
            $this->sendError('Medication ID is required');
            return;
        }
        
        try {
            // Get current medication data
            $medication = $this->fetchOne(
                "SELECT * FROM medications WHERE medication_id = ?",
                [$medicationId]
            );
            
            if (!$medication) {
                $this->sendError('Medication not found');
                return;
            }
            
            // Update status
            $sql = "UPDATE medications SET is_active = ?, updated_at = NOW(), updated_by = ? WHERE medication_id = ?";
            $params = [$isActive ? 1 : 0, $this->userId, $medicationId];
            
            $result = $this->execute($sql, $params);
            
            if (!$result) {
                $this->sendError('Failed to update medication status');
                return;
            }
            
            // Log the activity
            $action = $isActive ? 'activated' : 'deactivated';
            $auditDescription = "Medication ID: $medicationId ($medication[medication_name]) $action by user ID: " . $this->userId;
            $auditSQL = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, created_at) 
                         VALUES (?, ?, 'medication', ?, ?, ?, NOW())";
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            
            $this->execute($auditSQL, [
                $this->userId, 
                $isActive ? 'activate' : 'deactivate', 
                $medicationId, 
                $auditDescription, 
                $ipAddress
            ]);
            
            // Return success response
            $this->sendResponse([
                'success' => true,
                'message' => 'Medication status updated successfully',
                'is_active' => $isActive
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Error updating medication status: ' . $e->getMessage());
        }
    }
    
    /**
     * Update medication stock
     * 
     * @return void
     */
    public function updateStock() {
        // Only admin and pharmacist can manage stock
        if ($this->userRole !== 'admin' && $this->userRole !== 'pharmacist') {
            $this->sendError('Unauthorized. Only administrators and pharmacists can manage medication stock.', 403);
            return;
        }
        
        // Get data from request
        $medicationId = $this->getIntParam('medication_id', 'POST');
        $batchNumber = $this->getParam('batch_number', 'POST');
        $quantity = $this->getIntParam('quantity', 'POST');
        $expiryDate = $this->getParam('expiry_date', 'POST');
        $costPrice = $this->getFloatParam('cost_price', 'POST');
        $notes = $this->getParam('notes', 'POST') ?: '';
        
        // Validate required fields
        if (!$medicationId) {
            $this->sendError('Medication ID is required');
            return;
        }
        
        if (empty($batchNumber)) {
            $this->sendError('Batch number is required');
            return;
        }
        
        if ($quantity <= 0) {
            $this->sendError('Quantity must be greater than zero');
            return;
        }
        
        if (empty($expiryDate)) {
            $this->sendError('Expiry date is required');
            return;
        }
        
        // Check if expiry date is in the future
        $today = date('Y-m-d');
        if ($expiryDate <= $today) {
            $this->sendError('Expiry date must be in the future');
            return;
        }
        
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            // Check if medication exists
            $medication = $this->fetchOne(
                "SELECT * FROM medications WHERE medication_id = ?",
                [$medicationId]
            );
            
            if (!$medication) {
                $this->conn->rollback();
                $this->sendError('Medication not found');
                return;
            }
            
            // Check if batch already exists
            $existingBatch = $this->fetchOne(
                "SELECT * FROM medication_stock WHERE medication_id = ? AND batch_number = ?",
                [$medicationId, $batchNumber]
            );
            
            if ($existingBatch) {
                // Update existing batch
                $sql = "UPDATE medication_stock SET 
                        quantity = quantity + ?,
                        expiry_date = ?,
                        cost_price = ?,
                        notes = ?,
                        updated_at = NOW(),
                        updated_by = ?
                        WHERE stock_id = ?";
                
                $params = [
                    $quantity,
                    $expiryDate,
                    $costPrice,
                    $notes,
                    $this->userId,
                    $existingBatch['stock_id']
                ];
                
                $result = $this->execute($sql, $params);
                
                if (!$result) {
                    $this->conn->rollback();
                    $this->sendError('Failed to update stock');
                    return;
                }
                
                $stockId = $existingBatch['stock_id'];
            } else {
                // Add new batch
                $sql = "INSERT INTO medication_stock (
                        medication_id, batch_number, quantity, expiry_date,
                        cost_price, notes, created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $this->conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Query preparation failed: " . $this->conn->error);
                }
                
                $stmt->bind_param(
                    "isisdsi",
                    $medicationId,
                    $batchNumber,
                    $quantity,
                    $expiryDate,
                    $costPrice,
                    $notes,
                    $this->userId
                );
                
                $stmt->execute();
                
                $stockId = $stmt->insert_id;
                
                if (!$stockId) {
                    $this->conn->rollback();
                    $this->sendError('Failed to add stock');
                    return;
                }
            }
            
            // Create stock transaction record
            $sql = "INSERT INTO stock_transactions (
                    medication_id, stock_id, transaction_type, quantity,
                    batch_number, expiry_date, cost_price, created_by, created_at
                ) VALUES (?, ?, 'add', ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $medicationId,
                $stockId,
                $quantity,
                $batchNumber,
                $expiryDate,
                $costPrice,
                $this->userId
            ];
            
            $result = $this->execute($sql, $params);
            
            if (!$result) {
                $this->conn->rollback();
                $this->sendError('Failed to record stock transaction');
                return;
            }
            
            // Commit transaction
            $this->conn->commit();
            
            // Return success response
            $this->sendResponse([
                'success' => true,
                'message' => 'Medication stock updated successfully',
                'stock_id' => $stockId
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error updating stock: ' . $e->getMessage());
        }
    }
} 