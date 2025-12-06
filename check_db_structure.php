<?php
/**
 * Check database structure for lab tables
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Lab-related tables:\n";

    $result = $pdo->query("SHOW TABLES LIKE '%lab%'");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $tableName = array_values($row)[0];
        echo "  - $tableName\n";
    }

    echo "\nChecking lab_test_requests structure:\n";
    $result = $pdo->query("DESCRIBE lab_test_requests");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
