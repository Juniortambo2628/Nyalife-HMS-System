<?php
/**
 * Check prescription status data
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking prescription data structure...\n";

    $result = $pdo->query("SELECT * FROM prescriptions WHERE patient_id = 3");
    $prescription = $result->fetch(PDO::FETCH_ASSOC);

    if ($prescription) {
        echo "Prescription data:\n";
        foreach ($prescription as $key => $value) {
            echo "  $key: $value\n";
        }
    } else {
        echo "No prescription found for patient 3\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
