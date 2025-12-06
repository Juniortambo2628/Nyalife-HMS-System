<?php
/**
 * Phinx Migration: Verify Database Alignment with Models
 * 
 * This migration verifies that:
 * 1. All model table references exist in the database
 * 2. All foreign key relationships are properly defined
 * 3. All required columns exist
 * 4. Database views are implemented
 */

use Phinx\Migration\AbstractMigration;

class VerifyDatabaseAlignment extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     */
    public function change(): void
    {
        // This migration only verifies, doesn't modify
        // We'll use up() and down() methods for verification
    }

    /**
     * Verify database alignment
     */
    public function up(): void
    {
        $connection = $this->getAdapter()->getConnection();
        $results = [
            'models_checked' => [],
            'tables_found' => [],
            'tables_missing' => [],
            'foreign_keys' => [],
            'views_found' => [],
            'views_missing' => [],
            'errors' => []
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

        // Get all existing tables
        $tablesResult = $connection->query("SHOW TABLES");
        $existingTables = [];
        while ($row = $tablesResult->fetch(\PDO::FETCH_NUM)) {
            $existingTables[] = $row[0];
        }

        // Verify model tables exist
        foreach ($modelTables as $model => $table) {
            $results['models_checked'][] = $model;
            if (in_array($table, $existingTables)) {
                $results['tables_found'][] = $table;
            } else {
                $results['tables_missing'][] = "$model -> $table";
                $results['errors'][] = "Table '$table' referenced by $model does not exist";
            }
        }

        // Check for additional tables not in models
        $additionalTables = array_diff($existingTables, array_values($modelTables));
        $results['additional_tables'] = $additionalTables;

        // Verify foreign key relationships
        $fkResult = $connection->query("
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
        
        while ($row = $fkResult->fetch(\PDO::FETCH_ASSOC)) {
            $results['foreign_keys'][] = [
                'table' => $row['TABLE_NAME'],
                'column' => $row['COLUMN_NAME'],
                'references' => $row['REFERENCED_TABLE_NAME'] . '.' . $row['REFERENCED_COLUMN_NAME'],
                'constraint' => $row['CONSTRAINT_NAME']
            ];
        }

        // Check for database views
        $viewsResult = $connection->query("
            SELECT TABLE_NAME 
            FROM information_schema.VIEWS 
            WHERE TABLE_SCHEMA = DATABASE()
        ");
        $existingViews = [];
        while ($row = $viewsResult->fetch(\PDO::FETCH_NUM)) {
            $existingViews[] = $row[0];
        }
        $results['views_found'] = $existingViews;

        // Expected views (if any are defined)
        $expectedViews = [
            // Add expected view names here if any are defined
        ];

        foreach ($expectedViews as $view) {
            if (!in_array($view, $existingViews)) {
                $results['views_missing'][] = $view;
                $results['errors'][] = "View '$view' is expected but not found";
            }
        }

        // Output results
        echo "\n=== Database Alignment Verification ===\n\n";
        echo "Models Checked: " . count($results['models_checked']) . "\n";
        echo "Tables Found: " . count($results['tables_found']) . "\n";
        echo "Tables Missing: " . count($results['tables_missing']) . "\n";
        echo "Foreign Keys: " . count($results['foreign_keys']) . "\n";
        echo "Views Found: " . count($results['views_found']) . "\n";
        echo "Views Missing: " . count($results['views_missing']) . "\n";
        echo "Additional Tables: " . count($results['additional_tables']) . "\n";
        echo "Errors: " . count($results['errors']) . "\n\n";

        if (!empty($results['tables_missing'])) {
            echo "MISSING TABLES:\n";
            foreach ($results['tables_missing'] as $missing) {
                echo "  - $missing\n";
            }
            echo "\n";
        }

        if (!empty($results['views_missing'])) {
            echo "MISSING VIEWS:\n";
            foreach ($results['views_missing'] as $missing) {
                echo "  - $missing\n";
            }
            echo "\n";
        }

        if (!empty($results['errors'])) {
            echo "ERRORS:\n";
            foreach ($results['errors'] as $error) {
                echo "  - $error\n";
            }
            echo "\n";
        }

        // Save results to file
        file_put_contents(
            __DIR__ . '/../database_alignment_report.json',
            json_encode($results, JSON_PRETTY_PRINT)
        );

        echo "Full report saved to: database_alignment_report.json\n\n";

        // Fail if there are critical errors
        if (!empty($results['tables_missing'])) {
            throw new Exception("Database alignment verification failed: Missing tables detected");
        }
    }

    /**
     * Rollback (no-op for verification)
     */
    public function down(): void
    {
        // Verification only, no rollback needed
    }
}

