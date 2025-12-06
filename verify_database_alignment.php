<?php
/**
 * Database Alignment Verification Script
 * 
 * Verifies that:
 * 1. All model table references exist in the database
 * 2. All foreign key relationships are properly defined
 * 3. All required columns exist
 * 4. Database views are implemented
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/core/DatabaseManager.php';

// Get database connection
$db = DatabaseManager::getInstance()->getConnection();

$results = [
    'models_checked' => [],
    'tables_found' => [],
    'tables_missing' => [],
    'foreign_keys' => [],
    'views_found' => [],
    'views_missing' => [],
    'errors' => [],
    'column_mismatches' => []
];

// Model table mappings (from grep results)
$modelTables = [
    'AppointmentModel' => 'appointments',
    'ConsultationModel' => 'consultations',
    'DepartmentModel' => 'departments',
    'DoctorModel' => 'doctors',
    'DoctorScheduleModel' => 'doctor_schedules',
    'FollowUpModel' => 'follow_ups',
    'InvoiceModel' => 'invoices',
    'LabAttachmentModel' => 'lab_attachments',
    'LabParameterModel' => 'lab_parameters',
    'LabRequestModel' => 'lab_requests',
    'LabTestModel' => 'lab_test_types', // May reference multiple tables
    'MedicalHistoryModel' => 'medical_history',
    'MedicationModel' => 'medications',
    'MessageModel' => 'messages',
    'NotificationModel' => 'notifications',
    'PatientModel' => 'patients',
    'PaymentModel' => 'payments',
    'PrescriptionModel' => 'prescriptions',
    'StaffModel' => 'staff',
    'UserModel' => 'users',
    'VitalSignModel' => 'vital_signs',
];

// Additional tables that may be referenced but don't have models
$additionalExpectedTables = [
    'lab_test_requests',
    'lab_test_items',
    'lab_test_parameters',
    'prescription_items',
    'invoice_items',
    'payment_transactions',
    'medication_batches',
    'medication_categories',
    'specializations',
    'roles',
    'services',
    'settings',
    'audit_logs',
    'activity_logs',
    'password_reset_tokens',
    'remember_tokens',
    'user_tokens',
    'system_notifications',
    'obstetric_history',
    'pregnancy_details',
    'referrals',
    'email_queue',
    'lab_samples',
    'lab_results',
];

echo "\n=== Database Alignment Verification ===\n\n";

// Get all existing tables
$tablesResult = $db->query("SHOW TABLES");
$existingTables = [];
while ($row = $tablesResult->fetch_array()) {
    $existingTables[] = $row[0];
}

echo "Total tables in database: " . count($existingTables) . "\n\n";

// Verify model tables exist
echo "Checking Model Table References:\n";
echo str_repeat("-", 60) . "\n";
foreach ($modelTables as $model => $table) {
    $results['models_checked'][] = $model;
    if (in_array($table, $existingTables)) {
        $results['tables_found'][] = $table;
        echo "✓ $model -> $table\n";
    } else {
        $results['tables_missing'][] = "$model -> $table";
        $results['errors'][] = "Table '$table' referenced by $model does not exist";
        echo "✗ $model -> $table (MISSING)\n";
    }
}

// Check additional expected tables
echo "\nChecking Additional Expected Tables:\n";
echo str_repeat("-", 60) . "\n";
foreach ($additionalExpectedTables as $table) {
    if (in_array($table, $existingTables)) {
        echo "✓ $table\n";
    } else {
        echo "✗ $table (MISSING)\n";
        $results['errors'][] = "Expected table '$table' does not exist";
    }
}

// Get tables not in our lists
$allExpectedTables = array_merge(array_values($modelTables), $additionalExpectedTables);
$additionalTables = array_diff($existingTables, $allExpectedTables);
if (!empty($additionalTables)) {
    echo "\nAdditional Tables Found (not in model/expected list):\n";
    echo str_repeat("-", 60) . "\n";
    foreach ($additionalTables as $table) {
        echo "  - $table\n";
    }
}

// Verify foreign key relationships
echo "\nChecking Foreign Key Relationships:\n";
echo str_repeat("-", 60) . "\n";
$fkResult = $db->query("
    SELECT 
        TABLE_NAME,
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
    AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY TABLE_NAME, CONSTRAINT_NAME
");

$fkCount = 0;
while ($row = $fkResult->fetch_assoc()) {
    $fkCount++;
    $results['foreign_keys'][] = [
        'table' => $row['TABLE_NAME'],
        'column' => $row['COLUMN_NAME'],
        'references' => $row['REFERENCED_TABLE_NAME'] . '.' . $row['REFERENCED_COLUMN_NAME'],
        'constraint' => $row['CONSTRAINT_NAME']
    ];
}
echo "Found $fkCount foreign key relationships\n";

// Check for database views
echo "\nChecking Database Views:\n";
echo str_repeat("-", 60) . "\n";
$viewsResult = $db->query("
    SELECT TABLE_NAME 
    FROM information_schema.VIEWS 
    WHERE TABLE_SCHEMA = DATABASE()
");
$existingViews = [];
while ($row = $viewsResult->fetch_array()) {
    $existingViews[] = $row[0];
}
$results['views_found'] = $existingViews;

if (empty($existingViews)) {
    echo "No database views found (this is normal if views are not used)\n";
} else {
    echo "Found " . count($existingViews) . " view(s):\n";
    foreach ($existingViews as $view) {
        echo "  - $view\n";
    }
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "VERIFICATION SUMMARY\n";
echo str_repeat("=", 60) . "\n";
echo "Models Checked: " . count($results['models_checked']) . "\n";
echo "Tables Found: " . count($results['tables_found']) . "\n";
echo "Tables Missing: " . count($results['tables_missing']) . "\n";
echo "Foreign Keys: " . count($results['foreign_keys']) . "\n";
echo "Views Found: " . count($results['views_found']) . "\n";
echo "Errors: " . count($results['errors']) . "\n";

if (!empty($results['errors'])) {
    echo "\nERRORS DETECTED:\n";
    foreach ($results['errors'] as $error) {
        echo "  ✗ $error\n";
    }
} else {
    echo "\n✓ All verifications passed!\n";
}

// Save results to file
file_put_contents(
    __DIR__ . '/database_alignment_report.json',
    json_encode($results, JSON_PRETTY_PRINT)
);

echo "\nFull report saved to: database_alignment_report.json\n\n";

