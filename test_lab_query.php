<?php
/**
 * Test lab results query
 */

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Testing lab results query...\n";

    $sql = "SELECT
                ti.*,
                lt.test_name,
                COALESCE(CONCAT(ud.first_name, ' ', ud.last_name), CONCAT(up.first_name, ' ', up.last_name)) as doctor_name,
                COALESCE(ti.sample_reported_at, ti.performed_at, lr.completed_at, lr.request_date) as result_dt,
                DATE_FORMAT(COALESCE(ti.sample_reported_at, ti.performed_at, lr.completed_at, lr.request_date), '%M %e, %Y') as formatted_date
            FROM lab_test_items ti
            JOIN lab_test_requests lr ON ti.request_id = lr.request_id
            LEFT JOIN lab_test_types lt ON ti.test_type_id = lt.test_type_id
            LEFT JOIN users ud ON ti.verified_by = ud.user_id
            LEFT JOIN users up ON ti.performed_by = up.user_id
            WHERE lr.patient_id = 3 AND ti.status = 'completed'
            ORDER BY result_dt DESC";

    $result = $pdo->query($sql);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($rows) . " lab results\n";
    foreach ($rows as $row) {
        echo "  - " . ($row['test_name'] ?? 'Unknown') . " on " . ($row['formatted_date'] ?? 'Unknown date') . "\n";
        echo "    Status: " . $row['status'] . "\n";
        echo "    Doctor: " . ($row['doctor_name'] ?? 'Unknown') . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
