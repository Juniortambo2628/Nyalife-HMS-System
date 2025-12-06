<?php
/**
 * Update lab test item with proper result data
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Getting first parameter ID...\n";

    $result = $pdo->query("SELECT parameter_id FROM lab_test_parameters WHERE test_id = 1 LIMIT 1");
    $param = $result->fetch(PDO::FETCH_ASSOC);

    if ($param) {
        $parameterId = $param['parameter_id'];
        echo "Parameter ID: $parameterId\n";

        echo "Updating lab test item with result data...\n";
        $pdo->query("UPDATE lab_test_items SET
            parameter_id = $parameterId,
            result_value = '7.2',
            normal_range = '4.5-11.0',
            units = 'x10^9/L',
            result_interpretation = 'Normal'
            WHERE test_item_id = 1");

        echo "Lab test item updated successfully!\n";
    } else {
        echo "No parameter found\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
