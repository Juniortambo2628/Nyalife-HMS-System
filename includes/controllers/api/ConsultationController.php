<?php

/**
 * Nyalife HMS - Consultation API Controller
 *
 * This controller handles all consultation-related API requests.
 */

require_once __DIR__ . '/ApiController.php';

class ConsultationController extends ApiController
{
    public $userRole;
    public $conn;
    /**
     * Save consultation data
     */
    public function saveConsultation(): void
    {
        // Only doctors can save consultations
        if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
            $this->sendError('Only doctors can save consultations', 403);
            return;
        }

        // Get request data
        $data = $this->getRequestData();

        // Validate required fields
        $requiredFields = ['patient_id', 'appointment_id', 'diagnosis'];

        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }

        try {
            // Get consultation data
            $patientId = intval($data['patient_id']);
            $appointmentId = intval($data['appointment_id']);
            $diagnosis = trim((string) $data['diagnosis']);
            $notes = isset($data['notes']) ? trim((string) $data['notes']) : '';
            $treatment = isset($data['treatment']) ? trim((string) $data['treatment']) : '';
            $healthEducation = isset($data['health_education']) ? trim((string) $data['health_education']) : '';
            $consultationDate = date('Y-m-d H:i:s');
            $doctorId = $this->getStaffId();

            // Begin transaction
            $this->conn->begin_transaction();

            // Check if this is an update to an existing consultation
            $consultationId = isset($data['consultation_id']) ? intval($data['consultation_id']) : 0;

            if ($consultationId > 0) {
                // Update existing consultation
                $query = "UPDATE consultations SET 
                        diagnosis = ?,
                        notes = ?,
                        treatment = ?,
                        health_education = ?,
                        updated_at = NOW()
                    WHERE consultation_id = ? AND doctor_id = ?";

                $stmt = $this->conn->prepare($query);

                if (!$stmt) {
                    throw new Exception("Query preparation failed: " . $this->conn->error);
                }

                $stmt->bind_param(
                    "ssssii",
                    $diagnosis,
                    $notes,
                    $treatment,
                    $healthEducation,
                    $consultationId,
                    $doctorId
                );

                $stmt->execute();

                if ($stmt->affected_rows === 0) {
                    // No rows affected could mean no changes or consultation not found
                    // Let's check if the consultation exists
                    $checkQuery = "SELECT consultation_id FROM consultations WHERE consultation_id = ?";
                    $checkStmt = $this->conn->prepare($checkQuery);
                    $checkStmt->bind_param("i", $consultationId);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();

                    if ($checkResult->num_rows === 0) {
                        throw new Exception("Consultation not found");
                    }
                    // Otherwise, no changes were made, which is fine
                }
            } else {
                // Create new consultation
                $query = "INSERT INTO consultations (
                        patient_id,
                        appointment_id,
                        prescribed_by,
                        consultation_date,
                        diagnosis,
                        notes,
                        treatment,
                        health_education,
                        status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open')";

                $stmt = $this->conn->prepare($query);

                if (!$stmt) {
                    throw new Exception("Query preparation failed: " . $this->conn->error);
                }

                $stmt->bind_param(
                    "iiisssss",
                    $patientId,
                    $appointmentId,
                    $doctorId,
                    $consultationDate,
                    $diagnosis,
                    $notes,
                    $treatment,
                    $healthEducation
                );

                $stmt->execute();

                $consultationId = $stmt->insert_id;

                if ($consultationId === 0) {
                    throw new Exception("Failed to create consultation");
                }
            }

            // Handle vital signs if provided
            if (isset($data['vital_signs']) && is_array($data['vital_signs'])) {
                $vitalSigns = $data['vital_signs'];

                // Insert vital signs
                $query = "INSERT INTO vital_signs (
                        consultation_id,
                        temperature,
                        blood_pressure,
                        pulse_rate,
                        respiratory_rate,
                        height,
                        weight,
                        bmi,
                        spo2,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $this->conn->prepare($query);

                if (!$stmt) {
                    throw new Exception("Query preparation failed: " . $this->conn->error);
                }

                $temperature = $vitalSigns['temperature'] ?? null;
                $bloodPressure = $vitalSigns['blood_pressure'] ?? null;
                $pulseRate = $vitalSigns['pulse_rate'] ?? null;
                $respiratoryRate = $vitalSigns['respiratory_rate'] ?? null;
                $height = $vitalSigns['height'] ?? null;
                $weight = $vitalSigns['weight'] ?? null;
                $bmi = $vitalSigns['bmi'] ?? null;
                $spo2 = $vitalSigns['spo2'] ?? null;

                $stmt->bind_param(
                    "idsiiiddd",
                    $consultationId,
                    $temperature,
                    $bloodPressure,
                    $pulseRate,
                    $respiratoryRate,
                    $height,
                    $weight,
                    $bmi,
                    $spo2
                );

                $stmt->execute();
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'message' => 'Consultation saved successfully',
                'consultation_id' => $consultationId
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error saving consultation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Finalize consultation
     */
    public function finalizeConsultation(): void
    {
        // Only doctors can finalize consultations
        if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
            $this->sendError('Only doctors can finalize consultations', 403);
            return;
        }

        // Get request data
        $data = $this->getRequestData();

        // Validate required fields
        $requiredFields = ['consultation_id'];

        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }

        try {
            $consultationId = intval($data['consultation_id']);
            $doctorId = $this->getStaffId();

            // Update consultation status to 'closed'
            $query = "UPDATE consultations SET 
                    status = 'closed',
                    updated_at = NOW()
                WHERE consultation_id = ? AND doctor_id = ?";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            $stmt->bind_param(
                "ii",
                $consultationId,
                $doctorId
            );

            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                $this->sendError('Consultation not found or you do not have permission to finalize it');
                return;
            }

            // Also update appointment status if appointment_id exists
            $query = "UPDATE appointments a
                    JOIN consultations c ON a.appointment_id = c.appointment_id
                    SET a.status = 'completed'
                    WHERE c.consultation_id = ?";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            $stmt->bind_param("i", $consultationId);
            $stmt->execute();

            // Return success response
            $this->sendResponse([
                'message' => 'Consultation finalized successfully'
            ]);
        } catch (Exception $e) {
            $this->sendError('Error finalizing consultation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Save prescription
     */
    public function savePrescription(): void
    {
        // Only doctors can create prescriptions
        if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
            $this->sendError('Only doctors can create prescriptions', 403);
            return;
        }

        // Get request data
        $data = $this->getRequestData();

        // Validate required fields
        $requiredFields = ['consultation_id', 'patient_id', 'medications'];

        if (!$this->validateParams($data, $requiredFields)) {
            return;
        }

        try {
            $consultationId = intval($data['consultation_id']);
            $patientId = intval($data['patient_id']);
            $medications = $data['medications'];
            $doctorId = $this->getStaffId();

            // Validate medications array
            if (!is_array($medications) || $medications === []) {
                $this->sendError('No medications specified');
                return;
            }

            // Begin transaction
            $this->conn->begin_transaction();

            // Create prescription
            $query = "INSERT INTO prescriptions (
                    consultation_id,
                    patient_id,
                    prescribed_by,
                    status,
                    created_at
                ) VALUES (?, ?, ?, 'active', NOW())";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            $stmt->bind_param(
                "iii",
                $consultationId,
                $patientId,
                $doctorId
            );

            $stmt->execute();

            $prescriptionId = $stmt->insert_id;

            if ($prescriptionId === 0) {
                throw new Exception("Failed to create prescription");
            }

            // Add medications to prescription
            $query = "INSERT INTO prescription_items (
                    prescription_id,
                    medication_id,
                    dosage,
                    frequency,
                    duration,
                    instructions,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending')";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            foreach ($medications as $medication) {
                // Validate medication data
                if (!isset($medication['medication_id'])) {
                    continue;
                }
                if (!isset($medication['dosage'])) {
                    continue;
                }
                if (!isset($medication['frequency'])) {
                    continue;
                }
                if (!isset($medication['duration'])) {
                    continue;
                }
                $medicationId = intval($medication['medication_id']);
                $dosage = trim((string) $medication['dosage']);
                $frequency = trim((string) $medication['frequency']);
                $duration = trim((string) $medication['duration']);
                $instructions = isset($medication['instructions']) ? trim((string) $medication['instructions']) : '';

                $stmt->bind_param(
                    "iissss",
                    $prescriptionId,
                    $medicationId,
                    $dosage,
                    $frequency,
                    $duration,
                    $instructions
                );

                $stmt->execute();
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'message' => 'Prescription created successfully',
                'prescription_id' => $prescriptionId
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error creating prescription: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Save lab request
     */
    public function saveLabRequest(): void
    {
        // Get request parameters
        $consultationId = $this->getIntParam('consultation_id');
        $testTypes = $this->getParam('test_types', []);
        $notes = $this->getParam('notes', '');
        $priority = $this->getParam('priority', 'routine');

        if ($consultationId <= 0) {
            $this->sendError('Invalid consultation ID');
            return;
        }

        if (empty($testTypes)) {
            $this->sendError('No test types selected');
            return;
        }

        try {
            // Check if consultation exists
            $stmt = $this->db->prepare("SELECT * FROM consultations WHERE consultation_id = ?");
            $stmt->bind_param('i', $consultationId);
            $stmt->execute();
            $result = $stmt->get_result();
            $consultation = $result->fetch_assoc();
            $stmt->close();

            if (!$consultation) {
                $this->sendError('Consultation not found');
                return;
            }

            // Get patient and doctor IDs
            $patientId = $consultation['patient_id'];
            $doctorId = $consultation['doctor_id'];

            // Validate priority
            if (!in_array($priority, ['routine', 'urgent', 'stat'])) {
                $priority = 'routine';
            }

            // Begin transaction
            $this->conn->begin_transaction();

            // Create lab request
            $query = "INSERT INTO lab_test_requests (
                    consultation_id,
                    patient_id,
                    prescribed_by,
                    priority,
                    notes,
                    status,
                    request_date
                ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            $stmt->bind_param(
                "iiiss",
                $consultationId,
                $patientId,
                $doctorId,
                $priority,
                $notes
            );

            $stmt->execute();

            $requestId = $stmt->insert_id;

            if ($requestId === 0) {
                throw new Exception("Failed to create lab request");
            }

            // Add test types to request
            $query = "INSERT INTO lab_test_items (
                    request_id,
                    test_type_id,
                    status
                ) VALUES (?, ?, 'pending')";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->conn->error);
            }

            foreach ($testTypes as $testTypeId) {
                $testTypeId = intval($testTypeId);

                if ($testTypeId <= 0) {
                    continue;
                }

                $stmt->bind_param("ii", $requestId, $testTypeId);
                $stmt->execute();
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'message' => 'Lab request created successfully',
                'request_id' => $requestId
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error creating lab request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get lab requests for a consultation
     */
    public function getLabRequests(): void
    {
        // Get request parameters
        $consultationId = $this->getIntParam('consultation_id');

        if ($consultationId <= 0) {
            $this->sendError('Invalid consultation ID');
            return;
        }

        try {
            // Check if user has access to this consultation
            if (!$this->checkConsultationAccess($consultationId)) {
                $this->sendError('You do not have permission to view this consultation', 403);
                return;
            }

            // Get lab requests
            $query = "SELECT lr.*, 
                        CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                        CONCAT(p.first_name, ' ', p.last_name) as patient_name
                      FROM lab_test_requests lr
                      JOIN staff s ON lr.doctor_id = s.staff_id 
                      JOIN users d ON s.user_id = d.user_id
                      JOIN patients pat ON lr.patient_id = pat.patient_id
                      JOIN users p ON pat.user_id = p.user_id
                      WHERE lr.consultation_id = ?
                      ORDER BY lr.request_date DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $consultationId);
            $stmt->execute();
            $result = $stmt->get_result();

            $requests = [];

            while ($row = $result->fetch_assoc()) {
                $requestId = $row['request_id'];

                // Get test items for this request
                $itemsQuery = "SELECT lri.*, ltt.test_name, ltt.category, ltt.price
                               FROM lab_test_items lri
                               JOIN lab_test_types ltt ON lri.test_type_id = ltt.test_type_id
                               WHERE lri.request_id = ?";

                $itemsStmt = $this->conn->prepare($itemsQuery);
                $itemsStmt->bind_param("i", $requestId);
                $itemsStmt->execute();
                $itemsResult = $itemsStmt->get_result();

                $testItems = [];

                while ($item = $itemsResult->fetch_assoc()) {
                    $testItems[] = $item;
                }

                $row['tests'] = $testItems;
                $requests[] = $row;
            }

            $this->sendResponse($requests);
        } catch (Exception $e) {
            $this->sendError('Error retrieving lab requests: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get prescriptions for a consultation
     */
    public function getPrescriptions(): void
    {
        // Get request parameters
        $consultationId = $this->getIntParam('consultation_id');

        if ($consultationId <= 0) {
            $this->sendError('Invalid consultation ID');
            return;
        }

        try {
            // Check if user has access to this consultation
            if (!$this->checkConsultationAccess($consultationId)) {
                $this->sendError('You do not have permission to view this consultation', 403);
                return;
            }

            // Get prescriptions
            $query = "SELECT p.*, 
                        CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                        CONCAT(pat.first_name, ' ', pat.last_name) as patient_name
                      FROM prescriptions p
                      JOIN staff s ON p.doctor_id = s.staff_id 
                      JOIN users d ON s.user_id = d.user_id
                      JOIN patients pt ON p.patient_id = pt.patient_id
                      JOIN users pat ON pt.user_id = pat.user_id
                      WHERE p.consultation_id = ?
                      ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $consultationId);
            $stmt->execute();
            $result = $stmt->get_result();

            $prescriptions = [];

            while ($row = $result->fetch_assoc()) {
                $prescriptionId = $row['prescription_id'];

                // Get medication items for this prescription
                $itemsQuery = "SELECT pi.*, m.name as medication_name, m.form, m.strength
                               FROM prescription_items pi
                               JOIN medications m ON pi.medication_id = m.medication_id
                               WHERE pi.prescription_id = ?";

                $itemsStmt = $this->conn->prepare($itemsQuery);
                $itemsStmt->bind_param("i", $prescriptionId);
                $itemsStmt->execute();
                $itemsResult = $itemsStmt->get_result();

                $medicationItems = [];

                while ($item = $itemsResult->fetch_assoc()) {
                    $medicationItems[] = $item;
                }

                $row['medications'] = $medicationItems;
                $prescriptions[] = $row;
            }

            $this->sendResponse($prescriptions);
        } catch (Exception $e) {
            $this->sendError('Error retrieving prescriptions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a lab request
     */
    public function deleteLabRequest(): void
    {
        // Only doctors can delete lab requests
        if ($this->userRole !== 'doctor') {
            $this->sendError('Unauthorized. Only doctors can delete laboratory requests.', 403);
            return;
        }

        // Get request ID
        $requestId = $this->getIntParam('request_id', 'POST');

        if ($requestId <= 0) {
            $this->sendError('Invalid lab request ID');
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Get lab request to check if it belongs to an open consultation and is pending
            $query = "SELECT r.*, c.consultation_status
                    FROM lab_test_requests r
                    JOIN consultations c ON r.consultation_id = c.consultation_id
                    WHERE r.request_id = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();
            $labRequest = $result->fetch_assoc();

            if (!$labRequest) {
                $this->conn->rollback();
                $this->sendError('Lab request not found');
                return;
            }

            // Only allow deletion of pending lab requests
            if ($labRequest['status'] != 'pending') {
                $this->conn->rollback();
                $this->sendError('Cannot delete lab request that is already being processed');
                return;
            }

            // Check if consultation is finalized
            if ($labRequest['consultation_status'] == 'closed') {
                $this->conn->rollback();
                $this->sendError('Cannot delete lab request from a finalized consultation');
                return;
            }

            // Delete lab test items first
            $query = "DELETE FROM lab_test_items WHERE request_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $requestId);
            if (!$stmt->execute()) {
                $this->conn->rollback();
                $this->sendError('Failed to delete lab test items');
                return;
            }

            // Delete lab request
            $query = "DELETE FROM lab_test_requests WHERE request_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $requestId);
            if (!$stmt->execute()) {
                $this->conn->rollback();
                $this->sendError('Failed to delete lab request');
                return;
            }

            // Log the deletion
            $auditDescription = "Lab request ID: $requestId deleted by user ID: " . $this->userId;
            $auditQuery = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, created_at) 
                        VALUES (?, 'delete', 'lab_request', ?, ?, ?, NOW())";
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            $stmt = $this->conn->prepare($auditQuery);
            $stmt->bind_param("iiss", $this->userId, $requestId, $auditDescription, $ipAddress);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'message' => 'Lab request deleted successfully'
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error deleting lab request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a referral
     */
    public function deleteReferral(): void
    {
        // Only doctors can delete referrals
        if ($this->userRole !== 'doctor') {
            $this->sendError('Unauthorized. Only doctors can delete referrals.', 403);
            return;
        }

        // Get referral ID
        $referralId = $this->getIntParam('referral_id', 'POST');

        if ($referralId <= 0) {
            $this->sendError('Invalid referral ID');
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Get referral to check if it belongs to an open consultation
            $stmt = $this->db->prepare("SELECT r.*, c.consultation_status
                FROM referrals r
                JOIN consultations c ON r.consultation_id = c.consultation_id
                WHERE r.referral_id = ?");
            $stmt->bind_param('i', $referralId);
            $stmt->execute();
            $result = $stmt->get_result();
            $referral = $result->fetch_assoc();
            $stmt->close();

            if (!$referral) {
                $this->conn->rollback();
                $this->sendError('Referral not found');
                return;
            }

            // Check if consultation is finalized
            if ($referral['consultation_status'] == 'closed') {
                $this->conn->rollback();
                $this->sendError('Cannot delete referral from a finalized consultation');
                return;
            }

            // Delete referral
            $sql = "DELETE FROM referrals WHERE referral_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $referralId);
            $result = $stmt->execute();
            $stmt->close();

            if (!$result) {
                $this->conn->rollback();
                $this->sendError('Failed to delete referral');
                return;
            }

            // Delete associated notifications if any
            $sql = "DELETE FROM notifications WHERE reference_id = ? AND reference_type = 'referral'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $referralId);
            $stmt->execute();
            $stmt->close();

            // Log the deletion
            $auditDescription = "Referral ID: $referralId deleted by user ID: " . $this->userId;
            $auditSQL = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, created_at) 
                        VALUES (?, 'delete', 'referral', ?, ?, ?, NOW())";
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            $stmt = $this->db->prepare($auditSQL);
            $stmt->bind_param('iiss', $this->userId, $referralId, $auditDescription, $ipAddress);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'success' => true,
                'message' => 'Referral deleted successfully'
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();

            // Return error response
            $this->sendError('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Delete a follow-up appointment
     */
    public function deleteFollowup(): void
    {
        // Only doctors can delete follow-ups
        if ($this->userRole !== 'doctor') {
            $this->sendError('Unauthorized. Only doctors can delete follow-up appointments.', 403);
            return;
        }

        // Get follow-up ID
        $followupId = $this->getIntParam('followup_id', 'POST');

        if ($followupId <= 0) {
            $this->sendError('Invalid follow-up ID');
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Get follow-up to check if it belongs to an open consultation
            $stmt = $this->db->prepare("SELECT f.*, c.consultation_status
                FROM follow_ups f
                JOIN consultations c ON f.consultation_id = c.consultation_id
                WHERE f.follow_up_id = ?");
            $stmt->bind_param('i', $followupId);
            $stmt->execute();
            $result = $stmt->get_result();
            $followup = $result->fetch_assoc();
            $stmt->close();

            if (!$followup) {
                $this->conn->rollback();
                $this->sendError('Follow-up appointment not found');
                return;
            }

            // Check if consultation is finalized
            if ($followup['consultation_status'] == 'closed') {
                $this->conn->rollback();
                $this->sendError('Cannot delete follow-up from a finalized consultation');
                return;
            }

            // Delete follow-up
            $sql = "DELETE FROM follow_ups WHERE follow_up_id = ?";
            $result = $this->execute($sql, [$followupId]);

            if ($result === false || $result === 0) {
                $this->conn->rollback();
                $this->sendError('Failed to delete follow-up appointment');
                return;
            }

            // Delete associated notifications if any
            $sql = "DELETE FROM notifications WHERE reference_id = ? AND reference_type = 'followup'";
            $this->execute($sql, [$followupId]);

            // Log the deletion
            $auditDescription = "Follow-up ID: $followupId deleted by user ID: " . $this->userId;
            $auditSQL = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, created_at) 
                        VALUES (?, 'delete', 'followup', ?, ?, ?, NOW())";
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            $this->execute($auditSQL, [$this->userId, $followupId, $auditDescription, $ipAddress]);

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'success' => true,
                'message' => 'Follow-up appointment deleted successfully'
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();

            // Return error response
            $this->sendError('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Save follow-up appointment
     */
    public function saveFollowup(): void
    {
        // Only doctors can create/edit follow-ups
        if ($this->userRole !== 'doctor') {
            $this->sendError('Unauthorized. Only doctors can manage follow-up appointments.', 403);
            return;
        }

        // Get data from request
        $followupId = $this->getIntParam('followup_id', 'POST');
        $patientId = $this->getIntParam('patient_id', 'POST');
        $consultationId = $this->getIntParam('consultation_id', 'POST');
        $followupDate = $this->getParam('followup_date', 'POST');
        $followupType = $this->getParam('followup_type', 'POST');
        $reason = $this->getParam('reason', 'POST');
        $status = $this->getParam('status', 'POST') ?: 'scheduled';
        $notes = $this->getParam('notes', 'POST') ?: '';

        // Validate required fields
        if ($patientId <= 0) {
            $this->sendError('Patient ID is required');
            return;
        }

        if ($consultationId <= 0) {
            $this->sendError('Consultation ID is required');
            return;
        }

        if (empty($followupDate)) {
            $this->sendError('Follow-up date is required');
            return;
        }

        if (empty($reason)) {
            $this->sendError('Reason for follow-up is required');
            return;
        }

        // Get current timestamp
        $timestamp = date('Y-m-d H:i:s');

        // Get the doctor's staff ID
        $doctorId = $this->getStaffId();

        if ($doctorId === null || $doctorId === 0) {
            $this->sendError('Doctor profile not found');
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Check if consultation is finalized
            $consultation = $this->fetchOne(
                "SELECT consultation_status FROM consultations WHERE consultation_id = ?",
                [$consultationId]
            );

            if ($consultation === null || $consultation === []) {
                $this->conn->rollback();
                $this->sendError('Consultation not found');
                return;
            }

            if ($consultation['consultation_status'] == 'closed') {
                $this->conn->rollback();
                $this->sendError('Cannot modify follow-ups for a finalized consultation');
                return;
            }

            if ($followupId > 0) {
                // Update existing follow-up
                $sql = "UPDATE follow_ups SET 
                        patient_id = ?,
                        consultation_id = ?,
                        doctor_id = ?,
                        follow_up_date = ?,
                        follow_up_type = ?,
                        reason = ?,
                        status = ?,
                        notes = ?,
                        updated_at = ?
                        WHERE follow_up_id = ?";

                $result = $this->execute(
                    $sql,
                    [
                        $patientId,
                        $consultationId,
                        $doctorId,
                        $followupDate,
                        $followupType,
                        $reason,
                        $status,
                        $notes,
                        $timestamp,
                        $followupId
                    ]
                );

                if ($result === false || $result === 0) {
                    $this->conn->rollback();
                    $this->sendError('Failed to update follow-up appointment');
                    return;
                }
            } else {
                // Insert new follow-up
                $sql = "INSERT INTO follow_ups (
                        patient_id,
                        consultation_id,
                        prescribed_by,
                        follow_up_date,
                        follow_up_type,
                        reason,
                        status,
                        notes,
                        created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $this->conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Query preparation failed: " . $this->conn->error);
                }

                $stmt->bind_param(
                    "iiisssssi",
                    $patientId,
                    $consultationId,
                    $doctorId,
                    $followupDate,
                    $followupType,
                    $reason,
                    $status,
                    $notes,
                    $this->userId
                );

                $stmt->execute();

                $followupId = $stmt->insert_id;

                if (!$followupId) {
                    $this->conn->rollback();
                    $this->sendError('Failed to create follow-up appointment');
                    return;
                }
            }

            // Create a notification for the patient
            $patientUserId = $this->fetchOne(
                "SELECT u.user_id FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = ?",
                [$patientId]
            );

            if ($patientUserId !== null && $patientUserId !== []) {
                $formattedDate = date('M d, Y', strtotime((string) $followupDate));

                $notificationSQL = "INSERT INTO notifications (user_id, title, message, notification_type, reference_id, reference_type, created_at) 
                                  VALUES (?, 'Follow-up Appointment', 'You have a follow-up appointment scheduled for $formattedDate.', 
                                  'followup', ?, 'followup', NOW())";

                $this->execute($notificationSQL, [$patientUserId['user_id'], $followupId]);
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'success' => true,
                'followup_id' => $followupId,
                'message' => ($followupId > 0 ? 'Follow-up appointment updated successfully' : 'Follow-up appointment scheduled successfully')
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();

            // Return error response
            $this->sendError('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Delete a prescription item
     */
    public function deletePrescription(): void
    {
        // Only doctors can delete prescriptions
        if ($this->userRole !== 'doctor') {
            $this->sendError('Unauthorized. Only doctors can delete prescriptions.', 403);
            return;
        }

        // Get item ID
        $itemId = $this->getIntParam('item_id', 'POST');

        if ($itemId <= 0) {
            $this->sendError('Invalid prescription item ID');
            return;
        }

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Get prescription item to check if it belongs to an open consultation
            $prescriptionItem = $this->fetchOne(
                "SELECT pi.*, p.consultation_id
                FROM prescription_items pi
                JOIN prescriptions p ON pi.prescription_id = p.prescription_id
                WHERE pi.item_id = ?",
                [$itemId]
            );

            if ($prescriptionItem === null || $prescriptionItem === []) {
                $this->conn->rollback();
                $this->sendError('Prescription item not found');
                return;
            }

            // Check if consultation is finalized
            $consultation = $this->fetchOne(
                "SELECT consultation_status FROM consultations WHERE consultation_id = ?",
                [$prescriptionItem['consultation_id']]
            );

            if ($consultation === null || $consultation === []) {
                $this->conn->rollback();
                $this->sendError('Consultation not found');
                return;
            }

            if ($consultation['consultation_status'] == 'closed') {
                $this->conn->rollback();
                $this->sendError('Cannot delete prescription from a finalized consultation');
                return;
            }

            // Delete prescription item
            $sql = "DELETE FROM prescription_items WHERE item_id = ?";
            $result = $this->execute($sql, [$itemId]);

            if ($result === false || $result === 0) {
                $this->conn->rollback();
                $this->sendError('Failed to delete prescription item');
                return;
            }

            // Check if this was the last item in the prescription
            $remainingItems = $this->fetchOne(
                "SELECT COUNT(*) as count FROM prescription_items WHERE prescription_id = ?",
                [$prescriptionItem['prescription_id']]
            );

            // If no items left, delete the prescription record too
            if ($remainingItems['count'] == 0) {
                $sql = "DELETE FROM prescriptions WHERE prescription_id = ?";
                $this->execute($sql, [$prescriptionItem['prescription_id']]);
            }

            // Log the deletion
            $auditDescription = "Prescription item ID: $itemId deleted by user ID: " . $this->userId;
            $auditSQL = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, created_at) 
                        VALUES (?, 'delete', 'prescription_item', ?, ?, ?, NOW())";
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            $this->execute($auditSQL, [$this->userId, $itemId, $auditDescription, $ipAddress]);

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'success' => true,
                'message' => 'Prescription item deleted successfully'
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();

            // Return error response
            $this->sendError('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Save a referral
     */
    public function saveReferral(): void
    {
        // Only doctors can create/edit referrals
        if ($this->userRole !== 'doctor') {
            $this->sendError('Unauthorized. Only doctors can manage referrals.', 403);
            return;
        }

        // Get data from POST
        $referralId = $this->getIntParam('referral_id', 'POST');
        $patientId = $this->getIntParam('patient_id', 'POST');
        $consultationId = $this->getIntParam('consultation_id', 'POST');
        $referralDate = $this->getParam('referral_date', 'POST');
        $referredToName = $this->getParam('referred_to_name', 'POST');
        $referredToSpecialty = $this->getParam('referred_to_specialty', 'POST');
        $referredToFacility = $this->getParam('referred_to_facility', 'POST');
        $referredToContact = $this->getParam('referred_to_contact', 'POST');
        $reason = $this->getParam('reason', 'POST');
        $priority = $this->getParam('priority', 'POST') ?: 'routine';
        $status = $this->getParam('status', 'POST') ?: 'pending';
        $notes = $this->getParam('notes', 'POST') ?: '';

        // Validate required fields
        if ($patientId <= 0) {
            $this->sendError('Patient ID is required');
            return;
        }

        if ($consultationId <= 0) {
            $this->sendError('Consultation ID is required');
            return;
        }

        if (empty($referralDate)) {
            $this->sendError('Referral date is required');
            return;
        }

        if (empty($referredToName)) {
            $this->sendError('Referred to name is required');
            return;
        }

        if (empty($referredToSpecialty)) {
            $this->sendError('Specialty is required');
            return;
        }

        if (empty($reason)) {
            $this->sendError('Reason for referral is required');
            return;
        }

        // Get the doctor's staff ID
        $doctorStaff = $this->fetchOne(
            "SELECT staff_id FROM staff WHERE user_id = ?",
            [$this->userId]
        );

        if ($doctorStaff === null || $doctorStaff === []) {
            $this->sendError('Doctor profile not found');
            return;
        }

        $doctorId = $doctorStaff['staff_id'];

        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Check if consultation is finalized
            $consultation = $this->fetchOne(
                "SELECT consultation_status FROM consultations WHERE consultation_id = ?",
                [$consultationId]
            );

            if ($consultation === null || $consultation === []) {
                $this->conn->rollback();
                $this->sendError('Consultation not found');
                return;
            }

            if ($consultation['consultation_status'] == 'closed') {
                $this->conn->rollback();
                $this->sendError('Cannot modify referrals for a finalized consultation');
                return;
            }

            // Prepare referral data
            $referralData = [
                'patient_id' => $patientId,
                'consultation_id' => $consultationId,
                'referring_doctor_id' => $doctorId,
                'referral_date' => $referralDate,
                'referred_to_name' => $referredToName,
                'referred_to_specialty' => $referredToSpecialty,
                'referred_to_facility' => $referredToFacility,
                'referred_to_contact' => $referredToContact,
                'reason' => $reason,
                'priority' => $priority,
                'status' => $status,
                'notes' => $notes
            ];

            if ($referralId > 0) {
                // Update existing referral
                $updateFields = [];
                $updateParams = [];

                foreach ($referralData as $key => $value) {
                    $updateFields[] = "$key = ?";
                    $updateParams[] = $value;
                }

                // Add updated_at timestamp
                $updateFields[] = "updated_at = NOW()";

                // Add referral ID to params
                $updateParams[] = $referralId;

                // Update referral
                $sql = "UPDATE referrals SET ";
                $sql .= implode(", ", $updateFields);
                $sql .= " WHERE referral_id = ?";

                $result = $this->execute($sql, $updateParams);

                if ($result === false || $result === 0) {
                    $this->conn->rollback();
                    $this->sendError('Failed to update referral');
                    return;
                }
            } else {
                // Insert new referral
                $referralData['created_by'] = $this->userId;

                $columns = implode(", ", array_keys($referralData));
                $placeholders = implode(", ", array_fill(0, count($referralData), "?"));

                $sql = "INSERT INTO referrals ($columns) VALUES ($placeholders)";

                $stmt = $this->conn->prepare($sql);

                if (!$stmt) {
                    $this->conn->rollback();
                    $this->sendError('Failed to prepare statement: ' . $this->conn->error);
                    return;
                }

                // Bind parameters
                $this->bindParams($stmt, array_values($referralData));

                if (!$stmt->execute()) {
                    $this->conn->rollback();
                    $this->sendError('Failed to execute statement: ' . $stmt->error);
                    return;
                }

                $referralId = $stmt->insert_id;
                $stmt->close();

                if (!$referralId) {
                    $this->conn->rollback();
                    $this->sendError('Failed to create referral');
                    return;
                }
            }

            // Create a notification for the patient
            $patientUserId = $this->fetchOne(
                "SELECT u.user_id FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = ?",
                [$patientId]
            );

            if ($patientUserId !== null && $patientUserId !== []) {
                $notificationSQL = "INSERT INTO notifications (user_id, title, message, notification_type, reference_id, reference_type, created_at) 
                                   VALUES (?, 'Referral Created', 'You have been referred to a specialist. Please check your referrals section for details.', 
                                   'referral', ?, NOW())";

                $this->execute($notificationSQL, [$patientUserId['user_id'], $referralId]);
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'success' => true,
                'referral_id' => $referralId,
                'message' => ($referralId > 0 ? 'Referral updated successfully' : 'Referral created successfully')
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();

            // Return error response
            $this->sendError('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Check if user has access to a consultation
     *
     * @param int $consultationId Consultation ID
     * @return bool True if user has access
     */
    private function checkConsultationAccess(int $consultationId): bool
    {
        $query = "SELECT c.consultation_id
                  FROM consultations c
                  WHERE c.consultation_id = ?";

        $params = [$consultationId];

        // Add role-specific restrictions
        if ($this->userRole === 'patient') {
            $patientId = $this->getPatientId();
            $query .= " AND c.patient_id = ?";
            $params[] = $patientId;
        } elseif ($this->userRole === 'doctor') {
            $doctorId = $this->getStaffId();
            $query .= " AND c.doctor_id = ?";
            $params[] = $doctorId;
        } elseif ($this->userRole !== 'admin' && $this->userRole !== 'nurse') {
            // If not admin, patient, doctor, or nurse, no access
            return false;
        }

        // Execute query
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        // Bind parameters dynamically
        $this->bindParams($stmt, $params);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    /**
     * Get pending consultations count
     */
    public function pendingCount(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            $role = $this->getCurrentUserRole();

            $count = 0;

            if ($role === 'admin') {
                // Admin sees all pending consultations
                $sql = "SELECT COUNT(*) as count FROM consultations WHERE consultation_status IN ('open', 'pending')";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Query preparation failed: " . $this->conn->error);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $count = $row['count'] ?? 0;
                $stmt->close();
            } elseif ($role === 'doctor') {
                // Doctors see their own pending consultations
                $doctorId = $this->getStaffId();
                if ($doctorId !== null && $doctorId !== 0) {
                    $sql = "SELECT COUNT(*) as count FROM consultations 
                            WHERE doctor_id = ? AND consultation_status IN ('open', 'pending')";
                    $stmt = $this->conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Query preparation failed: " . $this->conn->error);
                    }
                    $stmt->bind_param('i', $doctorId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $count = $row['count'] ?? 0;
                    $stmt->close();
                }
            } elseif ($role === 'patient') {
                // Patients see their own pending consultations
                $patientId = $this->getPatientId();
                if ($patientId !== null && $patientId !== 0) {
                    $sql = "SELECT COUNT(*) as count FROM consultations 
                            WHERE patient_id = ? AND consultation_status IN ('open', 'pending')";
                    $stmt = $this->conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Query preparation failed: " . $this->conn->error);
                    }
                    $stmt->bind_param('i', $patientId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $count = $row['count'] ?? 0;
                    $stmt->close();
                }
            }

            $this->sendResponse(['count' => (int)$count]);
        } catch (Exception $e) {
            $this->sendError('Failed to get pending consultations count: ' . $e->getMessage(), 500);
        }
    }
}
