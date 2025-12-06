<?php
/**
 * Check lab test item structure
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking lab test items structure...\n";

    $result = $pdo->query("SELECT * FROM lab_test_items WHERE request_id = 1");
    $item = $result->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        echo "Lab test item found:\n";
        foreach ($item as $key => $value) {
            echo "  $key: $value\n";
        }
    } else {
        echo "No lab test item found\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
