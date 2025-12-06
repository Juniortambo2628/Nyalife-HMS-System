<?php
/**
 * Update vitals with complete data including height, weight, BMI
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Updating vitals with height, weight, BMI...\n";

    // Update the existing vital signs record
    $pdo->query("UPDATE vital_signs SET
        height = 165.0,
        weight = 60.0,
        bmi = 22.0
        WHERE patient_id = 3 AND vital_id = 2");

    echo "Vitals updated successfully!\n";

    // Verify the update
    $result = $pdo->query("SELECT height, weight, bmi FROM vital_signs WHERE patient_id = 3");
    $vital = $result->fetch(PDO::FETCH_ASSOC);
    echo "Height: " . $vital['height'] . ", Weight: " . $vital['weight'] . ", BMI: " . $vital['bmi'] . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
