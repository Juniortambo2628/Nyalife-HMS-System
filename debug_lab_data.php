<?php
/**
 * Debug lab data structure
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking lab test item details...\n";

    $result = $pdo->query("SELECT * FROM lab_test_items WHERE test_item_id = 1");
    $item = $result->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        echo "Current lab test item data:\n";
        foreach ($item as $key => $value) {
            echo "  $key: $value\n";
        }
    }

    echo "\nChecking lab test types for reference ranges...\n";
    $result = $pdo->query("SELECT * FROM lab_test_types WHERE test_type_id = 1");
    $testType = $result->fetch(PDO::FETCH_ASSOC);

    if ($testType) {
        echo "Test type data:\n";
        foreach ($testType as $key => $value) {
            echo "  $key: $value\n";
        }
    }

    echo "\nChecking lab test parameters...\n";
    $result = $pdo->query("SELECT * FROM lab_test_parameters WHERE test_id = 1");
    $parameters = $result->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($parameters) . " parameters\n";
    foreach ($parameters as $param) {
        echo "  - " . $param['parameter_name'] . ": " . $param['reference_range'] . " " . $param['unit'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
