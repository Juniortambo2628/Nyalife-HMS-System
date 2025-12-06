<?php

/**
 * Nyalife HMS - Insurance API Controller
 *
 * This controller handles all insurance-related API requests.
 */

require_once __DIR__ . '/ApiController.php';

class InsuranceController extends ApiController
{
    /**
     * Get insurance policy details
     */
    public function getPolicy(): void
    {
        // Verify access rights based on user role
        if (
            $this->userRole !== 'admin' && $this->userRole !== 'receptionist' &&
            $this->userRole !== 'doctor' && $this->userRole !== 'patient'
        ) {
            $this->sendError('Unauthorized access', 403);
            return;
        }

        // Get policy ID from request
        $policyId = $this->getIntParam('policy_id');

        if ($policyId === 0) {
            $this->sendError('Policy ID is required');
            return;
        }

        try {
            // Get policy details
            $query = "SELECT ip.*, ic.company_name, p.patient_number, 
                      CONCAT(u.first_name, ' ', u.last_name) as patient_name
                      FROM insurance_policies ip 
                      JOIN insurance_companies ic ON ip.company_id = ic.company_id
                      JOIN patients p ON ip.patient_id = p.patient_id
                      JOIN users u ON p.user_id = u.user_id
                      WHERE ip.policy_id = ?";

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $policyId);
            $stmt->execute();
            $result = $stmt->get_result();
            $policy = $result->fetch_assoc();
            $stmt->close();

            // If patient role, check if policy belongs to the user
            if ($this->userRole === 'patient') {
                $patientId = $this->getPatientId();
                if ($policy['patient_id'] != $patientId) {
                    $this->sendError('You do not have permission to view this policy', 403);
                    return;
                }
            }

            if (!$policy) {
                $this->sendError('Policy not found');
                return;
            }

            // Return policy details
            $this->sendResponse([
                'success' => true,
                'policy' => $policy
            ]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving policy: ' . $e->getMessage());
        }
    }

    /**
     * Add or update insurance policy
     */
    public function savePolicy(): void
    {
        // Only admin and receptionist can manage policies
        if ($this->userRole !== 'admin' && $this->userRole !== 'receptionist') {
            $this->sendError('Unauthorized. Only administrators and receptionists can manage insurance policies.', 403);
            return;
        }

        // Get data from request
        $policyId = $this->getIntParam('policy_id', 'POST');
        $isUpdate = $policyId !== 0;
        $patientId = $this->getIntParam('patient_id', 'POST');
        $companyId = $this->getIntParam('company_id', 'POST');
        $policyNumber = $this->getParam('policy_number', 'POST');
        $coverageType = $this->getParam('coverage_type', 'POST');
        $coverageLimit = $this->getFloatParam('coverage_limit', 'POST');
        $coveragePercentage = $this->getIntParam('coverage_percentage', 'POST');
        $startDate = $this->getParam('start_date', 'POST');
        $endDate = $this->getParam('end_date', 'POST');
        $status = $this->getParam('status', 'POST') ?: 'active';

        // Validate required fields
        if ($patientId === 0) {
            $this->sendError('Patient ID is required');
            return;
        }

        if ($companyId === 0) {
            $this->sendError('Insurance company ID is required');
            return;
        }

        if (empty($policyNumber)) {
            $this->sendError('Policy number is required');
            return;
        }

        if (empty($coverageType)) {
            $this->sendError('Coverage type is required');
            return;
        }

        // Begin transaction
        $this->conn->begin_transaction();

        try {
            if ($policyId !== 0) {
                // Update existing policy
                $sql = "UPDATE insurance_policies SET
                        patient_id = ?,
                        company_id = ?,
                        policy_number = ?,
                        coverage_type = ?,
                        coverage_limit = ?,
                        coverage_percentage = ?,
                        start_date = ?,
                        end_date = ?,
                        status = ?,
                        updated_at = NOW(),
                        updated_by = ?
                        WHERE policy_id = ?";

                $params = [
                    $patientId,
                    $companyId,
                    $policyNumber,
                    $coverageType,
                    $coverageLimit,
                    $coveragePercentage,
                    $startDate,
                    $endDate,
                    $status,
                    $this->userId,
                    $policyId
                ];

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param('i', $policyId);
                $result = $stmt->execute();
                $stmt->close();

                if (!$result) {
                    $this->conn->rollback();
                    $this->sendError('Failed to update insurance policy');
                    return;
                }
            } else {
                // Create new policy
                $sql = "INSERT INTO insurance_policies (
                        patient_id, company_id, policy_number, coverage_type,
                        coverage_limit, coverage_percentage, start_date, end_date,
                        status, created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $this->conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Query preparation failed: " . $this->conn->error);
                }

                $stmt->bind_param(
                    "iissddsssi",
                    $patientId,
                    $companyId,
                    $policyNumber,
                    $coverageType,
                    $coverageLimit,
                    $coveragePercentage,
                    $startDate,
                    $endDate,
                    $status,
                    $this->userId
                );

                $stmt->execute();

                $policyId = $stmt->insert_id;

                if (!$policyId) {
                    $this->conn->rollback();
                    $this->sendError('Failed to create insurance policy');
                    return;
                }
            }

            // Create notification for patient
            $stmt = $this->db->prepare("SELECT u.user_id FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = ?");
            $stmt->bind_param('i', $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            $patientUserId = $result->fetch_assoc();
            $stmt->close();

            if ($patientUserId) {
                $stmt = $this->db->prepare("SELECT company_name FROM insurance_companies WHERE company_id = ?");
                $stmt->bind_param('i', $companyId);
                $stmt->execute();
                $result = $stmt->get_result();
                $companyName = $result->fetch_assoc();
                $stmt->close();

                $notificationMessage = "Your insurance policy with " . $companyName['company_name'] .
                                      " has been " . ($isUpdate ? 'updated' : 'added') . " to your profile.";

                $notificationSQL = "INSERT INTO notifications (user_id, title, message, notification_type, reference_id, reference_type, created_at) 
                                   VALUES (?, 'Insurance Policy Update', ?, 'insurance', ?, 'policy', NOW())";

                $stmt = $this->db->prepare($notificationSQL);
                $stmt->bind_param('isi', $patientUserId['user_id'], $notificationMessage, $policyId);
                $stmt->execute();
                $stmt->close();
            }

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'success' => true,
                'policy_id' => $policyId,
                'message' => ($isUpdate ? 'Insurance policy updated successfully' : 'Insurance policy created successfully')
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error saving insurance policy: ' . $e->getMessage());
        }
    }

    /**
     * Delete insurance policy
     */
    public function deletePolicy(): void
    {
        // Only admin and receptionist can delete policies
        if ($this->userRole !== 'admin' && $this->userRole !== 'receptionist') {
            $this->sendError('Unauthorized. Only administrators and receptionists can delete insurance policies.', 403);
            return;
        }

        // Get policy ID from request
        $policyId = $this->getIntParam('policy_id', 'POST');

        if ($policyId === 0) {
            $this->sendError('Policy ID is required');
            return;
        }

        // Begin transaction
        $this->conn->begin_transaction();

        try {
            // Get policy details before deleting (for notification)
            $policy = $this->fetchOne(
                "SELECT ip.*, p.patient_id, u.user_id, ic.company_name 
                FROM insurance_policies ip
                JOIN patients p ON ip.patient_id = p.patient_id
                JOIN users u ON p.user_id = u.user_id
                JOIN insurance_companies ic ON ip.company_id = ic.company_id
                WHERE ip.policy_id = ?",
                [$policyId]
            );

            if ($policy === null || $policy === []) {
                $this->conn->rollback();
                $this->sendError('Policy not found');
                return;
            }

            // Check if policy is associated with any active invoices
            $activeInvoices = $this->fetchOne(
                "SELECT COUNT(*) as count FROM invoices 
                WHERE insurance_policy_id = ? AND status IN ('pending', 'partially_paid')",
                [$policyId]
            );

            if ($activeInvoices && $activeInvoices['count'] > 0) {
                $this->conn->rollback();
                $this->sendError('Cannot delete policy that is associated with active invoices');
                return;
            }

            // Delete policy
            $sql = "DELETE FROM insurance_policies WHERE policy_id = ?";
            $result = $this->execute($sql, [$policyId]);

            if ($result === false || $result === 0) {
                $this->conn->rollback();
                $this->sendError('Failed to delete insurance policy');
                return;
            }

            // Create notification for patient
            if (isset($policy['user_id'])) {
                $notificationMessage = "Your insurance policy " . $policy['policy_number'] .
                                      " with " . $policy['company_name'] . " has been removed from your profile.";

                $notificationSQL = "INSERT INTO notifications (user_id, title, message, notification_type, reference_type, created_at) 
                                   VALUES (?, 'Insurance Policy Removed', ?, 'insurance', 'policy', NOW())";

                $this->execute($notificationSQL, [$policy['user_id'], $notificationMessage]);
            }

            // Log the deletion
            $auditDescription = "Insurance policy ID: $policyId deleted by user ID: " . $this->userId;
            $auditSQL = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, created_at) 
                        VALUES (?, 'delete', 'insurance_policy', ?, ?, ?, NOW())";
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            $this->execute($auditSQL, [$this->userId, $policyId, $auditDescription, $ipAddress]);

            // Commit transaction
            $this->conn->commit();

            // Return success response
            $this->sendResponse([
                'success' => true,
                'message' => 'Insurance policy deleted successfully'
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            $this->sendError('Error deleting insurance policy: ' . $e->getMessage());
        }
    }

    /**
     * Calculate insurance coverage
     */
    public function calculateCoverage(): void
    {
        // Verify access rights based on user role
        if (
            $this->userRole !== 'admin' && $this->userRole !== 'receptionist' &&
            $this->userRole !== 'cashier' && $this->userRole !== 'doctor'
        ) {
            $this->sendError('Unauthorized access', 403);
            return;
        }

        // Get parameters
        $policyId = $this->getIntParam('policy_id');
        $amount = $this->getFloatParam('amount');
        $serviceType = $this->getParam('service_type');

        if ($policyId === 0) {
            $this->sendError('Policy ID is required');
            return;
        }

        if ($amount === 0.0) {
            $this->sendError('Amount is required');
            return;
        }

        if (empty($serviceType)) {
            $this->sendError('Service type is required');
            return;
        }

        try {
            // Get policy details
            $policy = $this->fetchOne(
                "SELECT * FROM insurance_policies WHERE policy_id = ?",
                [$policyId]
            );

            if ($policy === null || $policy === []) {
                $this->sendError('Policy not found');
                return;
            }

            // Check if policy is active
            if ($policy['status'] !== 'active') {
                $this->sendError('Policy is not active');
                return;
            }

            // Check if policy is expired
            $today = date('Y-m-d');
            if ($policy['end_date'] < $today) {
                $this->sendError('Policy has expired');
                return;
            }

            // Calculate coverage based on policy type
            $coveragePercentage = $policy['coverage_percentage'] ?? 0;
            $coverageAmount = ($amount * $coveragePercentage) / 100;

            // Check if exceeds coverage limit
            $usedCoverage = $this->fetchOne(
                "SELECT SUM(coverage_amount) AS total FROM insurance_claims WHERE policy_id = ? AND status IN ('approved', 'pending')",
                [$policyId]
            );

            $totalUsed = $usedCoverage['total'] ?? 0;
            $remainingCoverage = $policy['coverage_limit'] - $totalUsed;

            if ($coverageAmount > $remainingCoverage) {
                $coverageAmount = $remainingCoverage;
            }

            $patientAmount = $amount - $coverageAmount;

            // Return coverage calculation
            $this->sendResponse([
                'success' => true,
                'total_amount' => $amount,
                'coverage_amount' => $coverageAmount,
                'patient_amount' => $patientAmount,
                'coverage_percentage' => $coveragePercentage,
                'remaining_coverage' => $remainingCoverage
            ]);
        } catch (Exception $e) {
            $this->sendError('Error calculating coverage: ' . $e->getMessage());
        }
    }

    /**
     * Get patient's insurance policies
     */
    public function getPatientPolicies(): void
    {
        // Get patient ID
        $patientId = $this->getIntParam('patient_id');

        // If user is patient, check if they're accessing their own data
        if ($this->userRole === 'patient') {
            $userPatientId = $this->getPatientId();
            if ($patientId && $patientId != $userPatientId) {
                $this->sendError('You can only view your own insurance policies', 403);
                return;
            }

            // If no patient ID was provided, use the logged-in patient's ID
            if ($patientId === 0) {
                $patientId = $userPatientId;
            }
        } elseif (!in_array($this->userRole, ['admin', 'receptionist', 'doctor', 'cashier'])) {
            // Only these roles can access patient insurance data
            $this->sendError('Unauthorized access', 403);
            return;
        }

        if ($patientId === null || $patientId === 0) {
            $this->sendError('Patient ID is required');
            return;
        }

        try {
            // Get all policies for the patient
            $query = "SELECT ip.*, ic.company_name, ic.company_logo, ic.contact_info 
                      FROM insurance_policies ip
                      JOIN insurance_companies ic ON ip.company_id = ic.company_id
                      WHERE ip.patient_id = ?
                      ORDER BY ip.status = 'active' DESC, ip.end_date DESC";

            $policies = $this->fetchAll($query, [$patientId]);

            // Return policies
            $this->sendResponse([
                'success' => true,
                'policies' => $policies
            ]);
        } catch (Exception $e) {
            $this->sendError('Error retrieving insurance policies: ' . $e->getMessage());
        }
    }
}
