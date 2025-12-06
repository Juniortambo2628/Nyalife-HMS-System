<?php
/**
 * Database Structure Checker
 * 
 * This script checks the database structure and compares it with the code files
 * to identify any mismatches, unknown columns, or missing columns.
 */

// Load environment variables
function loadEnv($file) {
    if (!file_exists($file)) {
        return false;
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                $value = $matches[1];
            }
            
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
    return true;
}

// Load environment variables from env.production
loadEnv('env.production');

// Override with local development credentials for testing
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
$_ENV['DB_NAME'] = 'nyalifew_hms_prod';

// Include database configuration
require_once 'config/database.php';

try {
    // Create database connection using the existing function
    $conn = connectDB();
    
    echo "=== DATABASE STRUCTURE CHECK ===\n\n";
    
    // Check users table structure
    echo "1. CHECKING USERS TABLE STRUCTURE:\n";
    echo "================================\n";
    
    $stmt = $conn->query("DESCRIBE users");
    $usersColumns = [];
    while ($row = $stmt->fetch_assoc()) {
        $usersColumns[] = $row;
    }
    
    echo "Database columns in 'users' table:\n";
    foreach ($usersColumns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - Default: {$column['Default']}\n";
    }
    
    echo "\n";
    
    // Check what columns our code is trying to use
    echo "2. CODE ANALYSIS - COLUMNS USED IN CODE:\n";
    echo "========================================\n";
    
    // Expected columns based on our code analysis
    $expectedColumns = [
        'user_id', 'role_id', 'username', 'password', 'email', 'phone',
        'first_name', 'last_name', 'date_of_birth', 'gender', 'address',
        'city', 'state', 'country', 'postal_code', 'is_active', 'last_login',
        'profile_image', 'created_at', 'updated_at'
    ];
    
    echo "Expected columns from code analysis:\n";
    foreach ($expectedColumns as $column) {
        echo "- $column\n";
    }
    
    echo "\n";
    
    // Check for mismatches
    echo "3. MISMATCH ANALYSIS:\n";
    echo "=====================\n";
    
    $dbColumns = array_column($usersColumns, 'Field');
    $missingInDB = array_diff($expectedColumns, $dbColumns);
    $extraInDB = array_diff($dbColumns, $expectedColumns);
    $unknownInCode = array_intersect($dbColumns, ['status']); // Known problematic columns
    
    if (!empty($missingInDB)) {
        echo "❌ MISSING IN DATABASE (code expects but DB doesn't have):\n";
        foreach ($missingInDB as $column) {
            echo "   - $column\n";
        }
        echo "\n";
    }
    
    if (!empty($extraInDB)) {
        echo "⚠️  EXTRA IN DATABASE (DB has but code doesn't use):\n";
        foreach ($extraInDB as $column) {
            echo "   - $column\n";
        }
        echo "\n";
    }
    
    if (!empty($unknownInCode)) {
        echo "❌ UNKNOWN COLUMNS IN CODE (code tries to use non-existent columns):\n";
        foreach ($unknownInCode as $column) {
            echo "   - $column\n";
        }
        echo "\n";
    }
    
    if (empty($missingInDB) && empty($extraInDB) && empty($unknownInCode)) {
        echo "✅ No mismatches found! Database structure matches code expectations.\n\n";
    }
    
    // Check other important tables
    echo "4. CHECKING OTHER IMPORTANT TABLES:\n";
    echo "==================================\n";
    
    $importantTables = ['patients', 'appointments', 'staff', 'roles'];
    
    foreach ($importantTables as $table) {
        echo "\nTable: $table\n";
        echo "---------\n";
        
        try {
            $stmt = $conn->query("DESCRIBE $table");
            $columns = [];
            while ($row = $stmt->fetch_assoc()) {
                $columns[] = $row;
            }
            
            foreach ($columns as $column) {
                echo "- {$column['Field']} ({$column['Type']})\n";
            }
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
    
    // Check for any SQL errors in recent logs
    echo "\n5. CHECKING FOR RECENT DATABASE ERRORS:\n";
    echo "======================================\n";
    
    $logFiles = glob('logs/*.log');
    $recentErrors = [];
    
    foreach ($logFiles as $logFile) {
        if (filemtime($logFile) > time() - 86400) { // Last 24 hours
            $content = file_get_contents($logFile);
            if (preg_match_all('/Unknown column.*in.*field list/i', $content, $matches)) {
                $recentErrors[] = basename($logFile) . ": " . count($matches[0]) . " unknown column errors";
            }
        }
    }
    
    if (!empty($recentErrors)) {
        echo "❌ Recent database errors found:\n";
        foreach ($recentErrors as $error) {
            echo "   - $error\n";
        }
    } else {
        echo "✅ No recent database errors found.\n";
    }
    
    $conn->close();
    echo "\n=== CHECK COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in config/database.php\n";
}
?> 