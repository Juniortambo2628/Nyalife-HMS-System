<?php
/**
 * Check prescription data for patient 3
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking prescriptions for patient 3...\n";

    $result = $pdo->query("SELECT p.*, pi.item_id, pi.medication_id, pi.dosage, m.medication_name
                           FROM prescriptions p
                           LEFT JOIN prescription_items pi ON p.prescription_id = pi.prescription_id
                           LEFT JOIN medications m ON pi.medication_id = m.medication_id
                           WHERE p.patient_id = 3");

    $prescriptions = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $prescriptionId = $row['prescription_id'];
        if (!isset($prescriptions[$prescriptionId])) {
            $prescriptions[$prescriptionId] = [
                'id' => $prescriptionId,
                'medications' => []
            ];
        }
        if ($row['medication_name']) {
            $prescriptions[$prescriptionId]['medications'][] = $row['medication_name'] . ' (' . $row['dosage'] . ')';
        }
    }

    foreach ($prescriptions as $prescription) {
        echo "Prescription ID: " . $prescription['id'] . "\n";
        echo "  Medications: " . (empty($prescription['medications']) ? 'None' : implode(', ', $prescription['medications'])) . "\n";
    }

    $countResult = $pdo->query("SELECT COUNT(*) as count FROM prescription_items WHERE prescription_id IN (SELECT prescription_id FROM prescriptions WHERE patient_id = 3)");
    $count = $countResult->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Total prescription items: " . $count . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
