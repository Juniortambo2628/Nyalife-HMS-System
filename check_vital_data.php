<?php
/**
 * Check vital signs data
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking vital signs data for vital_id = 2...\n";

    $result = $pdo->query("SELECT * FROM vital_signs WHERE vital_id = 2");
    $vital = $result->fetch(PDO::FETCH_ASSOC);

    if ($vital) {
        echo "Current vital signs data:\n";
        foreach ($vital as $key => $value) {
            echo "  $key: $value\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
