<?php
/**
 * Database Schema Analyzer
 * Extracts current database schema structure
 */

// Load .env file manually for CLI execution
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$name] = $value;
        putenv(sprintf('%s=%s', $name, $value));
    }
}

require_once __DIR__ . '/config/database.php';

$conn = connectDB();

echo "=== Nyalife HMS Database Schema Analysis ===\n\n";

// Get all tables
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "Total Tables: " . count($tables) . "\n\n";
echo "Tables:\n" . implode("\n", $tables) . "\n\n";

// Get detailed schema for each table
echo "\n=== Detailed Schema ===\n\n";

foreach ($tables as $table) {
    echo "TABLE: $table\n";
    echo str_repeat("-", 80) . "\n";
    
    $result = $conn->query("DESCRIBE $table");
    while ($column = $result->fetch_assoc()) {
        printf("  %-30s %-20s %s\n", 
            $column['Field'], 
            $column['Type'], 
            ($column['Key'] ? "KEY:{$column['Key']}" : "") . 
            ($column['Null'] === 'NO' ? ' NOT NULL' : ' NULL') .
            ($column['Default'] !== null ? " DEFAULT:{$column['Default']}" : "")
        );
    }
    echo "\n";
}

$conn->close();
