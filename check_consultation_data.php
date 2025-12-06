<?php
/**
 * Check consultation data
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking consultations data for patient 3...\n";

    $result = $pdo->query("SELECT * FROM consultations WHERE patient_id = 3");
    $consultation = $result->fetch(PDO::FETCH_ASSOC);

    if ($consultation) {
        echo "Consultation data:\n";
        foreach ($consultation as $key => $value) {
            echo "  $key: $value\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
