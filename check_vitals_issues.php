<?php
/**
 * Check vitals and patient data issues
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking current vital signs data for patient 3...\n";

    $result = $pdo->query("SELECT * FROM vital_signs WHERE patient_id = 3");
    $vital = $result->fetch(PDO::FETCH_ASSOC);

    if ($vital) {
        echo "Current vital signs data:\n";
        foreach ($vital as $key => $value) {
            echo "  $key: $value\n";
        }
    }

    echo "\nChecking patient data for ID 3...\n";
    $result = $pdo->query("SELECT p.*, u.first_name, u.last_name, u.gender, u.date_of_birth FROM patients p LEFT JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = 3");
    $patient = $result->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        echo "Patient data with user info:\n";
        foreach ($patient as $key => $value) {
            echo "  $key: $value\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
