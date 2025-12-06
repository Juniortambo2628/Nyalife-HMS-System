<?php
/**
 * Create doctor_schedules table
 * 
 * This script creates the missing doctor_schedules table
 */

require_once __DIR__ . '/config/database.php';

try {
    $conn = connectDB();
    
    echo "Creating doctor_schedules table...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `doctor_schedules` (
      `schedule_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `doctor_id` INT UNSIGNED NOT NULL COMMENT 'References staff.staff_id',
      `day_of_week` TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday, 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday',
      `start_time` TIME NOT NULL COMMENT 'Start time for this day',
      `end_time` TIME NOT NULL COMMENT 'End time for this day',
      `appointment_duration` SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Duration in minutes',
      `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`schedule_id`),
      INDEX `idx_doctor_id` (`doctor_id`),
      INDEX `idx_day_of_week` (`day_of_week`),
      INDEX `idx_is_active` (`is_active`),
      INDEX `idx_doctor_day_active` (`doctor_id`, `day_of_week`, `is_active`),
      CONSTRAINT `fk_doctor_schedules_staff` 
        FOREIGN KEY (`doctor_id`) 
        REFERENCES `staff` (`staff_id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "✓ Table 'doctor_schedules' created successfully!\n";
        
        // Verify the table was created
        $result = $conn->query("SHOW TABLES LIKE 'doctor_schedules'");
        if ($result && $result->num_rows > 0) {
            echo "✓ Table verification: doctor_schedules exists\n";
            
            // Show table structure
            $result = $conn->query("DESCRIBE doctor_schedules");
            if ($result) {
                echo "\nTable structure:\n";
                echo str_repeat("-", 60) . "\n";
                while ($row = $result->fetch_assoc()) {
                    printf("%-20s %-20s %-10s\n", 
                        $row['Field'], 
                        $row['Type'], 
                        $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL'
                    );
                }
            }
        }
    } else {
        echo "✗ Error creating table: " . $conn->error . "\n";
        exit(1);
    }
    
    $conn->close();
    echo "\n✓ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

