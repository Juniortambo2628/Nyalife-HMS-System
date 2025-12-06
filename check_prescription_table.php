<?php
/**
 * Check prescriptions table structure
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking prescriptions table structure...\n";

    $result = $pdo->query("DESCRIBE prescriptions");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")";
        if ($row['Default'] !== null) {
            echo " DEFAULT: " . $row['Default'];
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
