<?php
/**
 * Update consultation status
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Updating consultation status...\n";

    $pdo->query("UPDATE consultations SET consultation_status = 'completed' WHERE consultation_id = 10");
    echo "Consultation status updated to completed\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
