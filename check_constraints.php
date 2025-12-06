<?php
/**
 * Check foreign key constraints for lab_test_items
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking foreign key constraints...\n";

    $result = $pdo->query("SHOW CREATE TABLE lab_test_items");
    $createTable = $result->fetch(PDO::FETCH_ASSOC);
    echo $createTable['Create Table'];

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
