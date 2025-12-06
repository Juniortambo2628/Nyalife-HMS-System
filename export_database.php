<?php
/**
 * Nyalife HMS - Database Export Script
 * 
 * This script exports your database schema and data for production deployment
 */

require_once 'includes/core/DatabaseManager.php';

echo "=== NYALIFE HMS DATABASE EXPORT ===\n\n";

try {
    $db = DatabaseManager::getInstance()->getConnection();
    
    if (!$db) {
        echo "❌ Database connection failed\n";
        exit(1);
    }
    
    echo "✅ Database connected successfully\n\n";
    
    // Get database name
    $result = $db->query("SELECT DATABASE() as db_name");
    $dbName = $result ? $result->fetch_assoc()['db_name'] : 'unknown';
    
    echo "🔍 Exporting database: $dbName\n\n";
    
    // Export directory
    $exportDir = __DIR__ . '/database_export';
    if (!is_dir($exportDir)) {
        mkdir($exportDir, 0755, true);
    }
    
    // 1. Export complete database (structure + data)
    echo "📦 EXPORTING COMPLETE DATABASE...\n";
    $completeFile = $exportDir . '/nyalife_hms_complete.sql';
    exportDatabase($db, $dbName, $completeFile, true);
    echo "✅ Complete export: $completeFile\n";
    
    // 2. Export structure only
    echo "\n🏗️ EXPORTING DATABASE STRUCTURE ONLY...\n";
    $structureFile = $exportDir . '/nyalife_hms_structure.sql';
    exportDatabase($db, $dbName, $structureFile, false);
    echo "✅ Structure export: $structureFile\n";
    
    // 3. Export specific tables
    echo "\n📋 EXPORTING SPECIFIC TABLES...\n";
    $tables = ['users', 'roles', 'staff', 'doctors', 'patients', 'departments', 'appointments'];
    foreach ($tables as $table) {
        $tableFile = $exportDir . "/table_{$table}.sql";
        exportTable($db, $table, $tableFile);
        echo "✅ Table export: $table -> $tableFile\n";
    }
    
    // 4. Create production setup script
    echo "\n⚙️ CREATING PRODUCTION SETUP SCRIPT...\n";
    createProductionSetupScript($exportDir, $dbName);
    echo "✅ Production setup script created\n";
    
    // 5. Create database info
    echo "\n📊 CREATING DATABASE INFORMATION...\n";
    createDatabaseInfo($db, $exportDir);
    echo "✅ Database information created\n";
    
    echo "\n=== DATABASE EXPORT COMPLETE! ===\n";
    echo "✅ Location: $exportDir\n";
    echo "✅ Files created:\n";
    echo "   - nyalife_hms_complete.sql (full backup)\n";
    echo "   - nyalife_hms_structure.sql (structure only)\n";
    echo "   - table_*.sql (individual tables)\n";
    echo "   - production_setup.sql (production setup)\n";
    echo "   - database_info.txt (database details)\n\n";
    
    echo "🚀 NEXT STEPS:\n";
    echo "1. Upload these SQL files to your production hosting\n";
    echo "2. Create a new database on your hosting\n";
    echo "3. Import the structure first (nyalife_hms_structure.sql)\n";
    echo "4. Import the data (nyalife_hms_complete.sql)\n";
    echo "5. Update database credentials in production config\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

/**
 * Export complete database
 */
function exportDatabase($db, $dbName, $filename, $includeData = true) {
    $tables = [];
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    $output = "-- Nyalife HMS Database Export\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- Database: $dbName\n\n";
    
    $output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
    
    foreach ($tables as $table) {
        // Table structure
        $result = $db->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch_array();
        $output .= "-- Table structure for table `$table`\n";
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $output .= $row[1] . ";\n\n";
        
        if ($includeData) {
            // Table data
            $result = $db->query("SELECT * FROM `$table`");
            if ($result && $result->num_rows > 0) {
                $output .= "-- Data for table `$table`\n";
                while ($row = $result->fetch_assoc()) {
                    $output .= "INSERT INTO `$table` VALUES (";
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . $db->real_escape_string($value) . "'";
                        }
                    }
                    $output .= implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }
    }
    
    $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    
    file_put_contents($filename, $output);
}

/**
 * Export specific table
 */
function exportTable($db, $table, $filename) {
    // Check if table exists
    $result = $db->query("SHOW TABLES LIKE '$table'");
    if (!$result || $result->num_rows == 0) {
        file_put_contents($filename, "-- Table '$table' does not exist\n");
        return;
    }
    
    $output = "-- Table: $table\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Table structure
    $result = $db->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch_array();
    $output .= "DROP TABLE IF EXISTS `$table`;\n";
    $output .= $row[1] . ";\n\n";
    
    // Table data
    $result = $db->query("SELECT * FROM `$table`");
    if ($result && $result->num_rows > 0) {
        $output .= "-- Data for table `$table`\n";
        while ($row = $result->fetch_assoc()) {
            $output .= "INSERT INTO `$table` VALUES (";
            $values = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . $db->real_escape_string($value) . "'";
                }
            }
            $output .= implode(', ', $values) . ");\n";
        }
    }
    
    file_put_contents($filename, $output);
}

/**
 * Create production setup script
 */
function createProductionSetupScript($exportDir, $dbName) {
    $script = "-- Nyalife HMS Production Database Setup\n";
    $script .= "-- Run this script on your production server\n\n";
    
    $script .= "-- 1. Create database\n";
    $script .= "CREATE DATABASE IF NOT EXISTS `nyalife_hms_production`;\n";
    $script .= "USE `nyalife_hms_production`;\n\n";
    
    $script .= "-- 2. Import structure and data\n";
    $script .= "-- Run: mysql -u username -p nyalife_hms_production < nyalife_hms_complete.sql\n\n";
    
    $script .= "-- 3. Create production user (optional)\n";
    $script .= "-- CREATE USER 'nyalife_user'@'localhost' IDENTIFIED BY 'strong_password';\n";
    $script .= "-- GRANT ALL PRIVILEGES ON nyalife_hms_production.* TO 'nyalife_user'@'localhost';\n";
    $script .= "-- FLUSH PRIVILEGES;\n\n";
    
    $script .= "-- 4. Verify tables\n";
    $script .= "SHOW TABLES;\n\n";
    
    $script .= "-- 5. Check user count\n";
    $script .= "SELECT COUNT(*) as total_users FROM users;\n";
    $script .= "SELECT COUNT(*) as total_patients FROM patients;\n";
    $script .= "SELECT COUNT(*) as total_staff FROM staff;\n";
    
    file_put_contents($exportDir . '/production_setup.sql', $script);
}

/**
 * Create database information file
 */
function createDatabaseInfo($db, $exportDir) {
    $info = "NYALIFE HMS DATABASE INFORMATION\n";
    $info .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Database info
    $result = $db->query("SELECT DATABASE() as db_name, VERSION() as version");
    $row = $result->fetch_assoc();
    $info .= "Database: " . $row['db_name'] . "\n";
    $info .= "MySQL Version: " . $row['version'] . "\n\n";
    
    // Table information
    $info .= "TABLES:\n";
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $table = $row[0];
        $countResult = $db->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $countResult ? $countResult->fetch_assoc()['count'] : 0;
        $info .= "- $table: $count records\n";
    }
    
    // Size information
    $info .= "\nDATABASE SIZE:\n";
    $result = $db->query("
        SELECT 
            table_schema AS 'Database',
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
        GROUP BY table_schema
    ");
    if ($result) {
        $row = $result->fetch_assoc();
        $info .= "Total Size: " . $row['Size (MB)'] . " MB\n";
    }
    
    file_put_contents($exportDir . '/database_info.txt', $info);
}
?>
