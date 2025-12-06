<?php

/**
 * Nyalife HMS - Medications API Controller
 *
 * This controller handles all medication-related API requests.
 */

require_once __DIR__ . '/ApiController.php';

class MedicationsController extends ApiController
{
    /**
     * Get all medications with pagination and filtering
     */
    public function getMedications(): void
    {
        // Verify access rights based on user role
        if (!in_array($this->userRole, ['admin', 'doctor', 'pharmacist', 'nurse'])) {
            $this->sendError('Unauthorized access', 403);
            return;
        }

        try {
            // Get pagination parameters
            $page = max(1, $this->getIntParam('page') !== 0 ? $this->getIntParam('page') : 1);
            $limit = max(10, min(100, $this->getIntParam('limit') !== 0 ? $this->getIntParam('limit') : 25));
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
            $stmt = $this->db->prepare($query);
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $medications = [];
            while ($row = $result->fetch_assoc()) {
                $medications[] = $row;
            }
            $stmt->close();

            $stmt = $this->db->prepare($countQuery);
            $stmt->bind_param(str_repeat('s', count($countParams)), ...$countParams);
            $stmt->execute();
            $result = $stmt->get_result();
            $totalCount = $result->fetch_assoc();
            $stmt->close();

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
     */
    public function getMedication(): void
    {
        // Verify access rights based on user role
        if (!in_array($this->userRole, ['admin', 'doctor', 'pharmacist', 'nurse'])) {
            $this->sendError('Unauthorized access', 403);
            return;
        }

        // Get medication ID
        $medicationId = $this->getIntParam('medication_id');

        if ($medicationId === 0) {
            $this->sendError('Medication ID is required');
            return;
        }

        try {
            // Get medication details
            $stmt = $this->db->prepare("SELECT * FROM medications WHERE medication_id = ?");
            $stmt->bind_param("i", $medicationId);
            $stmt->execute();
            $medication = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$medication) {
                $this->sendError('Medication not found');
                return;
            }

            // Get stock information
            $stmt = $this->db->prepare("SELECT * FROM medication_stock WHERE medication_id = ? ORDER BY expiry_date ASC");
            $stmt->bind_param("i", $medicationId);
            $stmt->execute();
            $stock = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

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
     */
    public function saveMedication(): void
    {
        // Only admin and pharmacist can manage medications
        if ($this->userRole !== 'admin' && $this->userRole !== 'pharmacist') {
            $this->sendError('Unauthorized. Only administrators and pharmacists can manage medications.', 403);
            return;
        }

        // Get data from request
        $medicationId = $this->getIntParam('medication_id', 'POST');
        $isUpdate = $medicationId !== 0;
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
        $sideEffects = $this->getParam('side_effects', 'POST') ?: '';
        $contraindications = $this->getParam('contraindications', 'POST') ?: '';
        $dosageInstructions = $this->getParam('dosage_instructions', 'POST') ?: '';
        $storageConditions = $this->getParam('storage_conditions', 'POST') ?: '';
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
            if ($medicationId !== 0) {
                // Update existing medication
                $result = $this->execute(
                    "UPDATE medications SET 
                        medication_name = ?, generic_name = ?, form = ?, strength = ?, unit = ?,
                        category = ?, description = ?, side_effects = ?, contraindications = ?,
                        dosage_instructions = ?, storage_conditions = ?, is_active = ?, updated_at = NOW()
                    WHERE medication_id = ?",
                    [$medicationName, $genericName, $form, $strength, $unit, $category, $description,
                     $sideEffects, $contraindications, $dosageInstructions, $storageConditions, $isActive, $medicationId]
                );

                if ($result === false || $result === 0) {
                    $this->conn->rollback();
                    $this->sendError('Failed to update medication');
                    return;
                }
            } else {
                // Check if medication with same name and form already exists
                $stmt = $this->db->prepare("SELECT * FROM medications WHERE medication_name = ? AND form = ? AND strength = ? AND unit = ?");
                $stmt->bind_param("ssss", $medicationName, $form, $strength, $unit);
                $stmt->execute();
                $existingMed = $stmt->get_result()->fetch_assoc();
                $stmt->close();

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

                $stmt = $this->db->prepare($auditSQL);
                $stmt->bind_param("issssi", $this->userId, $medicationId, $auditDescription, $ipAddress);
                $stmt->execute();
                $stmt->close();
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'success' => true,
                'medication_id' => $medicationId,
                'message' => ($isUpdate ? 'Medication updated successfully' : 'Medication created successfully')
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error saving medication: ' . $e->getMessage());
        }
    }

    /**
     * Update medication status (active/inactive)
     */
    public function toggleStatus(): void
    {
        // Only admin and pharmacist can manage medications
        if ($this->userRole !== 'admin' && $this->userRole !== 'pharmacist') {
            $this->sendError('Unauthorized. Only administrators and pharmacists can manage medications.', 403);
            return;
        }

        // Get data from request
        $medicationId = $this->getIntParam('medication_id', 'POST');
        $isActive = (bool)$this->getParam('is_active', 'POST');

        if ($medicationId === 0) {
            $this->sendError('Medication ID is required');
            return;
        }

        try {
            // Get current medication data
            $stmt = $this->db->prepare("SELECT * FROM medications WHERE medication_id = ?");
            $stmt->bind_param("i", $medicationId);
            $stmt->execute();
            $medication = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$medication) {
                $this->sendError('Medication not found');
                return;
            }

            // Update status
            $stmt = $this->db->prepare("UPDATE medications SET is_active = ?, updated_at = NOW() WHERE medication_id = ?");
            $isActiveInt = $isActive ? 1 : 0;
            $stmt->bind_param("ii", $isActiveInt, $medicationId);
            $result = $stmt->execute();
            $stmt->close();

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

            $stmt = $this->db->prepare($auditSQL);
            $action = $isActive ? 'activate' : 'deactivate';
            $stmt->bind_param("issssi", $this->userId, $action, $medicationId, $auditDescription, $ipAddress);
            $stmt->execute();
            $stmt->close();

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
     */
    public function updateStock(): void
    {
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
        if ($medicationId === 0) {
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
            $stmt = $this->db->prepare("SELECT * FROM medications WHERE medication_id = ?");
            $stmt->bind_param("i", $medicationId);
            $stmt->execute();
            $medication = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$medication) {
                $this->conn->rollback();
                $this->sendError('Medication not found');
                return;
            }

            // Check if batch already exists
            $stmt = $this->db->prepare("SELECT * FROM medication_stock WHERE medication_id = ? AND batch_number = ?");
            $stmt->bind_param("is", $medicationId, $batchNumber);
            $stmt->execute();
            $existingBatch = $stmt->get_result()->fetch_assoc();
            $stmt->close();

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

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param(
                    "isisdsi",
                    $quantity,
                    $expiryDate,
                    $costPrice,
                    $notes,
                    $this->userId,
                    $existingBatch['stock_id']
                );
                $result = $stmt->execute();
                $stmt->close();

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

                $result = $stmt->execute();

                if (!$result) {
                    $this->conn->rollback();
                    $this->sendError('Failed to add stock');
                    return;
                }

                $stockId = $stmt->insert_id;
                $stmt->close();
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

            $stmt = $this->db->prepare($sql);
            $transactionType = 'add';
            $stmt->bind_param(
                "isisdssi",
                $medicationId,
                $stockId,
                $transactionType,
                $quantity,
                $batchNumber,
                $expiryDate,
                $costPrice,
                $this->userId
            );
            $result = $stmt->execute();
            $stmt->close();

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
