<?php
/**
 * Check lab-related data
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking lab-related tables...\n";

    // Check lab tables
    $tables = ['lab_test_requests', 'lab_test_items', 'lab_test_types', 'lab_results', 'lab_samples'];
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
            $countResult = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $countResult->fetch(PDO::FETCH_ASSOC)['count'];
            echo "  - Records: $count\n";
        } else {
            echo "✗ Table '$table' does NOT exist\n";
        }
    }

    // Check lab test types
    echo "\nChecking lab test types...\n";
    $result = $pdo->query("SELECT * FROM lab_test_types LIMIT 5");
    $testTypes = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($testTypes as $test) {
        echo "  - " . $test['test_name'] . " (ID: " . $test['test_type_id'] . ")\n";
    }

    // Check if we have any lab requests for patient 3
    echo "\nChecking lab requests for patient 3...\n";
    $result = $pdo->query("SELECT * FROM lab_test_requests WHERE patient_id = 3");
    $requests = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($requests) . " lab requests\n";

    foreach ($requests as $request) {
        echo "  - Request ID: " . $request['request_id'] . "\n";
    }

    // Check if we have any lab test items for patient 3
    echo "\nChecking lab test items for patient 3...\n";
    $result = $pdo->query("
        SELECT ti.*, lr.patient_id, lt.test_name
        FROM lab_test_items ti
        JOIN lab_test_requests lr ON ti.request_id = lr.request_id
        LEFT JOIN lab_test_types lt ON ti.test_type_id = lt.test_type_id
        WHERE lr.patient_id = 3
    ");
    $items = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($items) . " lab test items\n";

    foreach ($items as $item) {
        echo "  - Item ID: " . $item['test_item_id'] . ", Test: " . ($item['test_name'] ?? 'Unknown') . ", Status: " . $item['status'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
