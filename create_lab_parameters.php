<?php
/**
 * Create lab test parameters for Complete Blood Count
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating lab test parameters...\n";

    // Create some basic CBC parameters
    $parameters = [
        ['test_id' => 1, 'parameter_name' => 'White Blood Cell Count', 'reference_range' => '4.5-11.0', 'unit' => 'x10^9/L'],
        ['test_id' => 1, 'parameter_name' => 'Red Blood Cell Count', 'reference_range' => '4.5-5.9', 'unit' => 'x10^12/L'],
        ['test_id' => 1, 'parameter_name' => 'Hemoglobin', 'reference_range' => '13.5-17.5', 'unit' => 'g/dL'],
        ['test_id' => 1, 'parameter_name' => 'Hematocrit', 'reference_range' => '41-53', 'unit' => '%'],
        ['test_id' => 1, 'parameter_name' => 'Platelet Count', 'reference_range' => '150-450', 'unit' => 'x10^9/L']
    ];

    foreach ($parameters as $param) {
        $pdo->query("INSERT INTO lab_test_parameters (test_id, parameter_name, reference_range, unit, created_at) VALUES (" . $param['test_id'] . ", '" . $param['parameter_name'] . "', '" . $param['reference_range'] . "', '" . $param['unit'] . "', NOW())");
        echo "  - Created parameter: " . $param['parameter_name'] . "\n";
    }

    echo "Lab test parameters created successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
