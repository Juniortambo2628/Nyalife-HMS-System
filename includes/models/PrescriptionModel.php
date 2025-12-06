<?php
/**
 * Prescription Model
 * Handles all database operations related to prescriptions
 */
class PrescriptionModel extends BaseModel {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'prescriptions';
        $this->primaryKey = 'prescription_id';
    }
    
    /**
     * Create a new prescription
     * 
     * @param array $data Prescription data
     * @return int|bool Last insert ID or false on failure
     */
    public function createPrescription($data) {
        try {
            // Begin transaction
            $this->db->begin_transaction();
            
            // Set default values if not provided
            if (!isset($data['prescription_date'])) {
                $data['prescription_date'] = date('Y-m-d');
            }
            
            if (!isset($data['status'])) {
                $data['status'] = 'pending';
            }
            
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            
            // Generate prescription number
            $data['prescription_number'] = $this->generatePrescriptionNumber();
            
            // Insert prescription record
            $prescriptionSql = "INSERT INTO prescriptions (
                prescription_number, patient_id, doctor_id, appointment_id, 
                prescription_date, notes, status, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($prescriptionSql);
            $stmt->bind_param(
                "siiisssis",
                $data['prescription_number'],
                $data['patient_id'],
                $data['doctor_id'],
                $data['appointment_id'],
                $data['prescription_date'],
                $data['notes'],
                $data['status'],
                $data['created_by'],
                $data['created_at']
            );
            
            $success = $stmt->execute();
            
            if (!$success) {
                $this->db->rollback();
                throw new Exception("Failed to create prescription: " . $stmt->error);
            }
            
            $prescriptionId = $this->db->insert_id;
            
            // Insert prescription items
            if (isset($data['items']) && is_array($data['items']) && !empty($data['items'])) {
                $itemSql = "INSERT INTO prescription_items (
                    prescription_id, medication_id, dosage, frequency, 
                    duration, instructions, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $itemStmt = $this->db->prepare($itemSql);
                
                foreach ($data['items'] as $item) {
                    $itemStmt->bind_param(
                        "iississ",
                        $prescriptionId,
                        $item['medication_id'],
                        $item['dosage'],
                        $item['frequency'],
                        $item['duration'],
                        $item['instructions'],
                        $data['created_at']
                    );
                    
                    $itemSuccess = $itemStmt->execute();
                    
                    if (!$itemSuccess) {
                        $this->db->rollback();
                        throw new Exception("Failed to create prescription item: " . $itemStmt->error);
                    }
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            return $prescriptionId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Get prescription by ID with items
     * 
     * @param int $prescriptionId Prescription ID
     * @return array|null Prescription data or null if not found
     */
    public function getPrescriptionWithItems($prescriptionId) {
        try {
            // Get prescription details
            $prescriptionSql = "SELECT p.*,
                                CONCAT(pat_user.first_name, ' ', pat_user.last_name) as patient_name,
                                CONCAT(doc_user.first_name, ' ', doc_user.last_name) as doctor_name,
                                CONCAT(created_user.first_name, ' ', created_user.last_name) as created_by_name,
                                CONCAT(dispensed_user.first_name, ' ', dispensed_user.last_name) as dispensed_by_name
                           FROM prescriptions p
                           JOIN patients pat ON p.patient_id = pat.patient_id
                           JOIN users pat_user ON pat.user_id = pat_user.user_id
                           JOIN doctors doc ON p.doctor_id = doc.doctor_id
                           JOIN users doc_user ON doc.user_id = doc_user.user_id
                           JOIN users created_user ON p.created_by = created_user.user_id
                           LEFT JOIN users dispensed_user ON p.dispensed_by = dispensed_user.user_id
                           WHERE p.prescription_id = ?";
            
            $stmt = $this->db->prepare($prescriptionSql);
            $stmt->bind_param("i", $prescriptionId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return null;
            }
            
            $prescription = $result->fetch_assoc();
            
            // Get prescription items
            $itemsSql = "SELECT pi.*, m.medication_name, m.medication_type, m.unit, m.strength
                        FROM prescription_items pi
                        JOIN medications m ON pi.medication_id = m.medication_id
                        WHERE pi.prescription_id = ?
                        ORDER BY pi.item_id ASC";
            
            $itemsStmt = $this->db->prepare($itemsSql);
            $itemsStmt->bind_param("i", $prescriptionId);
            $itemsStmt->execute();
            
            $itemsResult = $itemsStmt->get_result();
            
            $items = [];
            while ($item = $itemsResult->fetch_assoc()) {
                $items[] = $item;
            }
            
            $prescription['items'] = $items;
            $prescription['item_count'] = count($items);
            
            return $prescription;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * Get all prescriptions with basic details
     * 
     * @param array $filters Optional filters (status, patient_id, doctor_id)
     * @return array Array of prescriptions
     */
    public function getAllPrescriptions($filters = []) {
        try {
            $sql = "SELECT p.*,
                          CONCAT(pat_user.first_name, ' ', pat_user.last_name) as patient_name,
                          CONCAT(doc_user.first_name, ' ', doc_user.last_name) as doctor_name,
                          (SELECT COUNT(*) FROM prescription_items WHERE prescription_id = p.prescription_id) as item_count
                   FROM prescriptions p
                   JOIN patients pat ON p.patient_id = pat.patient_id
                   JOIN users pat_user ON pat.user_id = pat_user.user_id
                   JOIN doctors doc ON p.doctor_id = doc.doctor_id
                   JOIN users doc_user ON doc.user_id = doc_user.user_id
                   WHERE 1=1";
            
            $params = [];
            $types = "";
            
            // Apply filters
            if (isset($filters['status']) && !empty($filters['status'])) {
                $sql .= " AND p.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            if (isset($filters['patient_id']) && !empty($filters['patient_id'])) {
                $sql .= " AND p.patient_id = ?";
                $params[] = $filters['patient_id'];
                $types .= "i";
            }
            
            if (isset($filters['doctor_id']) && !empty($filters['doctor_id'])) {
                $sql .= " AND p.doctor_id = ?";
                $params[] = $filters['doctor_id'];
                $types .= "i";
            }
            
            $sql .= " ORDER BY p.prescription_date DESC, p.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $prescriptions = [];
            while ($row = $result->fetch_assoc()) {
                $prescriptions[] = $row;
            }
            
            return $prescriptions;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get pending prescriptions
     * 
     * @return array Array of pending prescriptions
     */
    public function getPendingPrescriptions() {
        return $this->getAllPrescriptions(['status' => 'pending']);
    }
    
    /**
     * Get completed (dispensed) prescriptions
     * 
     * @param int $limit Optional limit of results
     * @return array Array of dispensed prescriptions
     */
    public function getCompletedPrescriptions($limit = null) {
        try {
            $sql = "SELECT p.*,
                          CONCAT(pat_user.first_name, ' ', pat_user.last_name) as patient_name,
                          CONCAT(doc_user.first_name, ' ', doc_user.last_name) as doctor_name,
                          CONCAT(disp_user.first_name, ' ', disp_user.last_name) as dispensed_by_name,
                          (SELECT COUNT(*) FROM prescription_items WHERE prescription_id = p.prescription_id) as item_count
                   FROM prescriptions p
                   JOIN patients pat ON p.patient_id = pat.patient_id
                   JOIN users pat_user ON pat.user_id = pat_user.user_id
                   JOIN doctors doc ON p.doctor_id = doc.doctor_id
                   JOIN users doc_user ON doc.user_id = doc_user.user_id
                   JOIN users disp_user ON p.dispensed_by = disp_user.user_id
                   WHERE p.status = 'dispensed'
                   ORDER BY p.dispensed_at DESC";
            
            if ($limit !== null) {
                $sql .= " LIMIT " . intval($limit);
            }
            
            $result = $this->db->query($sql);
            
            $prescriptions = [];
            while ($row = $result->fetch_assoc()) {
                $prescriptions[] = $row;
            }
            
            return $prescriptions;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }
    
    /**
     * Get prescriptions for a specific patient
     * 
     * @param int $patientId Patient ID
     * @return array Array of prescriptions
     */
    public function getPatientPrescriptions($patientId) {
        return $this->getAllPrescriptions(['patient_id' => $patientId]);
    }
    
    /**
     * Get prescriptions for a specific doctor
     * 
     * @param int $doctorId Doctor ID
     * @return array Array of prescriptions
     */
    public function getDoctorPrescriptions($doctorId) {
        return $this->getAllPrescriptions(['doctor_id' => $doctorId]);
    }
    
    /**
     * Update prescription status
     * 
     * @param int $prescriptionId Prescription ID
     * @param string $status New status
     * @param int $userId User ID who made the update
     * @return bool Success status
     */
    public function updateStatus($prescriptionId, $status, $userId) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($status === 'dispensed') {
                $data['dispensed_by'] = $userId;
                $data['dispensed_at'] = date('Y-m-d H:i:s');
            }
            
            return $this->update($prescriptionId, $data);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Dispense prescription
     * 
     * @param int $prescriptionId Prescription ID
     * @param int $dispensedBy User ID who dispensed
     * @param array $dispensedItems List of dispensed items with quantities
     * @return bool Success status
     */
    public function dispensePrescription($prescriptionId, $dispensedBy, $dispensedItems = []) {
        try {
            $this->db->begin_transaction();
            
            // Update prescription status
            $statusUpdated = $this->updateStatus($prescriptionId, 'dispensed', $dispensedBy);
            
            if (!$statusUpdated) {
                $this->db->rollback();
                return false;
            }
            
            // Record dispensed items if provided
            if (!empty($dispensedItems)) {
                $dispenseSql = "INSERT INTO medication_dispensing 
                               (prescription_id, medication_id, quantity_dispensed, 
                                dispensed_by, dispensed_at) 
                               VALUES (?, ?, ?, ?, ?)";
                
                $dispenseStmt = $this->db->prepare($dispenseSql);
                $now = date('Y-m-d H:i:s');
                
                foreach ($dispensedItems as $item) {
                    $dispenseStmt->bind_param(
                        "iiiss",
                        $prescriptionId,
                        $item['medication_id'],
                        $item['quantity'],
                        $dispensedBy,
                        $now
                    );
                    
                    $dispenseSuccess = $dispenseStmt->execute();
                    
                    if (!$dispenseSuccess) {
                        $this->db->rollback();
                        return false;
                    }
                    
                    // Update medication inventory
                    // This would be implemented in a MedicationModel
                    // and would deduct the dispensed quantity
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Cancel prescription
     * 
     * @param int $prescriptionId Prescription ID
     * @param string $reason Optional cancellation reason
     * @param int $cancelledBy User ID who cancelled
     * @return bool Success status
     */
    public function cancelPrescription($prescriptionId, $reason, $cancelledBy) {
        try {
            $data = [
                'status' => 'cancelled',
                'notes' => $reason,
                'updated_at' => date('Y-m-d H:i:s'),
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->update($prescriptionId, $data);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * Generate a unique prescription number
     * 
     * @return string Prescription number
     */
    private function generatePrescriptionNumber() {
        $prefix = 'RX';
        $year = date('Y');
        $month = date('m');
        
        // Get the last prescription number with this prefix and year/month
        $sql = "SELECT prescription_number FROM prescriptions 
                WHERE prescription_number LIKE '{$prefix}{$year}{$month}%'
                ORDER BY prescription_id DESC LIMIT 1";
        
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastNumber = $row['prescription_number'];
            
            // Extract the sequence number and increment
            $sequence = (int)substr($lastNumber, strlen($prefix) + strlen($year) + strlen($month));
            $sequence++;
        } else {
            // Start with 1 if no existing numbers
            $sequence = 1;
        }
        
        // Format sequence with leading zeros (4 digits)
        $formattedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $year . $month . $formattedSequence;
    }
}
