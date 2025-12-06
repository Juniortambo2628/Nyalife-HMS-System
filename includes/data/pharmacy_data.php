<?php
/**
 * Nyalife HMS - Pharmacy Data Access Layer
 *
 * This file provides standardized functions for pharmacy data operations.
 */

require_once __DIR__ . '/../db_utils.php';

/**
 * Get prescription count by status
 *
 * @param string $status Status to count (pending, processing, completed, etc.)
 * @return int Count of prescriptions with the given status
 */
// Note: getPrescriptionCount() is already defined in functions.php

/**
 * Get pending prescriptions
 *
 * @param int $pharmacistId Optional pharmacist ID to filter by
 * @param int $limit Optional result limit
 * @return array Pending prescriptions
 */
if (!function_exists('getPendingPrescriptions')) {
    function getPendingPrescriptions($pharmacistId = null, $limit = null)
    {
        $sql = "SELECT p.*, 
            pt.patient_number,
            CONCAT(pu.first_name, ' ', pu.last_name) AS patient_name,
            CONCAT(du.first_name, ' ', du.last_name) AS doctor_name
            FROM prescriptions p
            JOIN patients pt ON p.patient_id = pt.patient_id
            JOIN users pu ON pt.user_id = pu.user_id
            JOIN doctors d ON p.doctor_id = d.doctor_id
            JOIN users du ON d.user_id = du.user_id
            WHERE p.status = 'pending'";

        $params = [];

        if ($pharmacistId) {
            $sql .= " AND (p.pharmacist_id IS NULL OR p.pharmacist_id = ?)";
            $params[] = $pharmacistId;
        }

        $sql .= " ORDER BY p.prescribed_date ASC";

        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }

        return dbSelect($sql, $params);
    }
}

/**
 * Get prescription details
 *
 * @param int $prescriptionId Prescription ID
 * @return array|null Prescription details or null if not found
 */
function getPrescriptionDetails($prescriptionId)
{
    $prescription = dbSelectOne(
        "SELECT p.*, 
        pt.patient_number,
        CONCAT(pu.first_name, ' ', pu.last_name) AS patient_name,
        CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
        CONCAT(su.first_name, ' ', su.last_name) AS pharmacist_name
        FROM prescriptions p
        JOIN patients pt ON p.patient_id = pt.patient_id
        JOIN users pu ON pt.user_id = pu.user_id
        JOIN doctors d ON p.doctor_id = d.doctor_id
        JOIN users du ON d.user_id = du.user_id
        LEFT JOIN staff s ON p.pharmacist_id = s.staff_id
        LEFT JOIN users su ON s.user_id = su.user_id
        WHERE p.prescription_id = ?",
        [$prescriptionId]
    );

    if (!$prescription) {
        return null;
    }

    // Get prescription items
    $prescription['items'] = dbSelect(
        "SELECT pi.*, m.medication_name, m.generic_name, m.unit_price
        FROM prescription_items pi
        JOIN medications m ON pi.medication_id = m.medication_id
        WHERE pi.prescription_id = ?",
        [$prescriptionId]
    );

    return $prescription;
}

/**
 * Process a prescription
 *
 * @param int $prescriptionId Prescription ID
 * @param int $pharmacistId Pharmacist staff ID
 * @return bool Success status
 */
function processPrescription($prescriptionId, $pharmacistId)
{
    return dbUpdate(
        "UPDATE prescriptions SET 
        pharmacist_id = ?, 
        status = 'processing', 
        updated_at = NOW()
        WHERE prescription_id = ? AND status = 'pending'",
        [$pharmacistId, $prescriptionId]
    );
}

/**
 * Complete a prescription
 *
 * @param int $prescriptionId Prescription ID
 * @param array $dispensedItems Array of items dispensed
 * @return bool Success status
 */
function completePrescription($prescriptionId, $dispensedItems = []): bool
{
    try {
        $db = dbBeginTransaction();

        // Update prescription status
        $updateSuccess = dbUpdate(
            "UPDATE prescriptions SET 
            status = 'completed', 
            dispensed_date = NOW(), 
            updated_at = NOW()
            WHERE prescription_id = ? AND status = 'processing'",
            [$prescriptionId]
        );

        if (!$updateSuccess) {
            dbRollbackTransaction($db);
            return false;
        }

        // Update inventory for dispensed items
        foreach ($dispensedItems as $item) {
            $updateStock = dbUpdate(
                "UPDATE medications SET 
                stock_quantity = stock_quantity - ?, 
                updated_at = NOW()
                WHERE medication_id = ? AND stock_quantity >= ?",
                [$item['quantity'], $item['medication_id'], $item['quantity']]
            );

            if (!$updateStock) {
                dbRollbackTransaction($db);
                return false;
            }

            // Record dispensing
            $recordDispensing = dbInsert(
                "INSERT INTO medication_dispensing 
                (prescription_id, medication_id, quantity, dispensed_by, dispensed_date)
                VALUES (?, ?, ?, ?, NOW())",
                [$prescriptionId, $item['medication_id'], $item['quantity'], $item['pharmacist_id']]
            );

            if (!$recordDispensing) {
                dbRollbackTransaction($db);
                return false;
            }
        }

        dbCommitTransaction($db);
        return true;
    } catch (Exception $e) {
        if (isset($db)) {
            dbRollbackTransaction($db);
        }

        // Log error
        if (function_exists('logDatabaseError')) {
            logDatabaseError($e->getMessage());
        }

        return false;
    }
}

/**
 * Get recent dispensed medications
 *
 * @param int $pharmacistId Optional pharmacist ID to filter by
 * @param int $limit Optional result limit
 * @return array Recent dispensed medications
 */
function getRecentDispensedMedications($pharmacistId = null, $limit = 5)
{
    $sql = "SELECT 
        md.*, 
        m.medication_name,
        p.prescription_id,
        CONCAT(u.first_name, ' ', u.last_name) AS patient_name
        FROM medication_dispensing md
        JOIN medications m ON md.medication_id = m.medication_id
        JOIN prescriptions p ON md.prescription_id = p.prescription_id
        JOIN patients pt ON p.patient_id = pt.patient_id
        JOIN users u ON pt.user_id = u.user_id
        WHERE 1=1";

    $params = [];

    if ($pharmacistId) {
        $sql .= " AND md.dispensed_by = ?";
        $params[] = $pharmacistId;
    }

    $sql .= " ORDER BY md.dispensed_date DESC";

    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }

    return dbSelect($sql, $params);
}

/**
 * Get medication inventory
 *
 * @param bool $lowStockOnly Whether to only get low stock items
 * @param int $limit Optional result limit
 * @return array Medication inventory
 */
function getMedicationInventory($lowStockOnly = false, $limit = null)
{
    $sql = "SELECT 
        m.*,
        CASE 
            WHEN m.stock_quantity <= m.reorder_level THEN 'low'
            WHEN m.stock_quantity > m.reorder_level * 2 THEN 'high'
            ELSE 'normal'
        END AS stock_status
        FROM medications m";

    $params = [];

    if ($lowStockOnly) {
        $sql .= " WHERE m.stock_quantity <= m.reorder_level";
    }

    $sql .= " ORDER BY 
        CASE 
            WHEN m.stock_quantity <= m.reorder_level THEN 1
            ELSE 2
        END,
        m.medication_name";

    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }

    return dbSelect($sql, $params);
}

/**
 * Get pharmacist statistics
 *
 * @param int $pharmacistId Pharmacist staff ID
 * @return array Pharmacist statistics
 */
function getPharmacistStatistics($pharmacistId): array
{
    $today = date('Y-m-d');
    $firstDayOfMonth = date('Y-m-01');

    return [
        'today_dispensed' => dbSelectValue(
            "SELECT COUNT(*) 
            FROM medication_dispensing 
            WHERE dispensed_by = ? AND DATE(dispensed_date) = ?",
            [$pharmacistId, $today]
        ),
        'pending_prescriptions' => dbSelectValue(
            "SELECT COUNT(*) 
            FROM prescriptions 
            WHERE status = 'pending'"
        ),
        'low_stock' => dbSelectValue(
            "SELECT COUNT(*) 
            FROM medications 
            WHERE stock_quantity <= reorder_level"
        ),
        'monthly_dispensed' => dbSelectValue(
            "SELECT COUNT(*) 
            FROM medication_dispensing 
            WHERE dispensed_by = ? AND DATE(dispensed_date) BETWEEN ? AND ?",
            [$pharmacistId, $firstDayOfMonth, $today]
        ),
        'total_prescriptions_processed' => dbSelectValue(
            "SELECT COUNT(*) 
            FROM prescriptions 
            WHERE pharmacist_id = ? AND status = 'completed'",
            [$pharmacistId]
        )
    ];
}

/**
 * Search medications
 *
 * @param string $searchTerm Search term
 * @param int $limit Optional result limit
 * @return array Matching medications
 */
function searchMedications($searchTerm, $limit = null)
{
    $searchParam = "%$searchTerm%";

    $sql = "SELECT * FROM medications
            WHERE medication_name LIKE ? 
            OR generic_name LIKE ?
            OR description LIKE ?
            OR category LIKE ?
            ORDER BY medication_name";

    $params = [
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam
    ];

    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }

    return dbSelect($sql, $params);
}
