<?php
/**
 * Check parameter tables structure
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking lab_parameters table...\n";
    $result = $pdo->query("DESCRIBE lab_parameters");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }

    echo "\nChecking lab_test_parameters table...\n";
    $result = $pdo->query("DESCRIBE lab_test_parameters");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }

    echo "\nChecking what parameters exist in lab_parameters...\n";
    $result = $pdo->query("SELECT * FROM lab_parameters LIMIT 5");
    $params = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($params as $param) {
        echo "  - ID: " . $param['parameter_id'] . ", Name: " . $param['parameter_name'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
