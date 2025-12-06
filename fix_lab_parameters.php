<?php
/**
 * Fix lab parameters and update lab test item
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking lab_parameters content...\n";

    $result = $pdo->query("SELECT * FROM lab_parameters WHERE test_type_id = 1");
    $params = $result->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($params) . " parameters in lab_parameters for test_type_id = 1\n";

    // If no parameters exist, create some
    if (count($params) == 0) {
        echo "Creating parameters in lab_parameters...\n";
        $pdo->query("INSERT INTO lab_parameters (test_type_id, parameter_name, normal_range, units) VALUES (1, 'White Blood Cell Count', '4.5-11.0', 'x10^9/L')");
        $pdo->query("INSERT INTO lab_parameters (test_type_id, parameter_name, normal_range, units) VALUES (1, 'Red Blood Cell Count', '4.5-5.9', 'x10^12/L')");
        $pdo->query("INSERT INTO lab_parameters (test_type_id, parameter_name, normal_range, units) VALUES (1, 'Hemoglobin', '13.5-17.5', 'g/dL')");
        echo "Parameters created!\n";
    }

    // Get the first parameter ID
    $result = $pdo->query("SELECT parameter_id FROM lab_parameters WHERE test_type_id = 1 LIMIT 1");
    $param = $result->fetch(PDO::FETCH_ASSOC);

    if ($param) {
        $parameterId = $param['parameter_id'];
        echo "Using parameter ID: $parameterId\n";

        echo "Updating lab test item...\n";
        $pdo->query("UPDATE lab_test_items SET
            parameter_id = $parameterId,
            result_value = '7.2',
            normal_range = '4.5-11.0',
            units = 'x10^9/L',
            result_interpretation = 'Normal'
            WHERE test_item_id = 1");

        echo "Lab test item updated successfully!\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
