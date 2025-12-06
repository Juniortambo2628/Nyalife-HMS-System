<?php
/**
 * Verify the test data was inserted correctly
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking vital signs data for patient ID 3...\n";

    $result = $pdo->query("SELECT * FROM vital_signs WHERE patient_id = 3");
    $vital = $result->fetch(PDO::FETCH_ASSOC);

    if ($vital) {
        echo "✓ Vital signs found:\n";
        echo "  - Blood Pressure: " . ($vital['blood_pressure'] ?? 'NULL') . "\n";
        echo "  - Heart Rate: " . ($vital['heart_rate'] ?? 'NULL') . "\n";
        echo "  - Respiratory Rate: " . ($vital['respiratory_rate'] ?? 'NULL') . "\n";
        echo "  - Temperature: " . ($vital['temperature'] ?? 'NULL') . "\n";
        echo "  - Oxygen Saturation: " . ($vital['oxygen_saturation'] ?? 'NULL') . "\n";
        echo "  - Recorded By: " . ($vital['recorded_by'] ?? 'NULL') . "\n";
    } else {
        echo "✗ No vital signs found for patient ID 3\n";
    }

    echo "\nChecking patient data...\n";
    $patientResult = $pdo->query("SELECT * FROM patients WHERE patient_id = 3");
    $patient = $patientResult->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        echo "✓ Patient found: " . $patient['first_name'] . ' ' . $patient['last_name'] . "\n";
    } else {
        echo "✗ Patient not found\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
