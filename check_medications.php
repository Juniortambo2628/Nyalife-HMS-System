<?php
/**
 * Check medications in database
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking medications...\n";

    $result = $pdo->query("SELECT medication_id, medication_name FROM medications LIMIT 5");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['medication_name'] . " (ID: " . $row['medication_id'] . ")\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
