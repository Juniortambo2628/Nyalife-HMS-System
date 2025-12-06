<?php
/**
 * Fix prescription status
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Updating prescription status...\n";

    $pdo->query("UPDATE prescriptions SET status = 'pending' WHERE patient_id = 3 AND (status IS NULL OR status = '' OR status = 'active')");
    echo "Prescription status updated to pending\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
