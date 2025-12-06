<?php
/**
 * Update pain level for vitals
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Updating pain_level for vital_id = 2...\n";

    $pdo->query("UPDATE vital_signs SET pain_level = 2 WHERE vital_id = 2");
    echo "Pain level updated to 2\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
