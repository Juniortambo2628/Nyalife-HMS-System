<?php
/**
 * Database Test Script
 * Test database connection and check if tables exist
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Database connection successful!\n\n";

    // Check if tables exist
    $tables = [
        'patients',
        'vital_signs',
        'consultations',
        'prescriptions',
        'lab_test_requests',
        'lab_test_items',
        'lab_test_types'
    ];

    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";

            // Get row count
            $countResult = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $countResult->fetch(PDO::FETCH_ASSOC)['count'];
            echo "  - Rows: $count\n";
        } else {
            echo "✗ Table '$table' does NOT exist\n";
        }
    }

    // Test specific patient data
    echo "\n--- Testing Patient ID 3 ---\n";
    $patientResult = $pdo->query("SELECT * FROM patients WHERE patient_id = 3");
    if ($patientResult->rowCount() > 0) {
        $patient = $patientResult->fetch(PDO::FETCH_ASSOC);
        echo "✓ Patient 3 exists: " . $patient['first_name'] . ' ' . $patient['last_name'] . "\n";

        // Check vitals for patient 3
        $vitalsResult = $pdo->query("SELECT COUNT(*) as count FROM vital_signs WHERE patient_id = 3");
        $vitalsCount = $vitalsResult->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  - Vital signs: $vitalsCount records\n";

        // Check consultations for patient 3
        $consultationsResult = $pdo->query("SELECT COUNT(*) as count FROM consultations WHERE patient_id = 3");
        $consultationsCount = $consultationsResult->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  - Consultations: $consultationsCount records\n";

        // Check prescriptions for patient 3
        $prescriptionsResult = $pdo->query("SELECT COUNT(*) as count FROM prescriptions WHERE patient_id = 3");
        $prescriptionsCount = $prescriptionsResult->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  - Prescriptions: $prescriptionsCount records\n";
    } else {
        echo "✗ Patient 3 does NOT exist\n";
    }

} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
