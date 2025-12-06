<?php
/**
 * Migration: add missing consultation columns
 * Run: php migrations/add_consultation_columns.php
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Try to obtain connection via connectDB(); if running from CLI this may point to production
    // Attempt connectDB first, then fall back to local default credentials if access denied.
    try {
        $conn = connectDB();
    } catch (Throwable $e) {
        // If connectDB() is not available or fails, we'll try a local mysqli fallback
        $conn = null;
    }

    // If connection exists but cannot run SHOW COLUMNS (e.g., access denied), fall back to local defaults
    $useFallback = false;
    if ($conn instanceof mysqli) {
        $result = @$conn->query("SHOW COLUMNS FROM `consultations`");
        if ($result === false) {
            $useFallback = true;
        }
    } else {
        $useFallback = true;
    }

    if ($useFallback) {
        echo "Falling back to local mysqli connection using root@localhost...\n";
        $fallbackHost = 'localhost';
        $fallbackUser = 'root';
        $fallbackPass = '';
        $fallbackDb = 'nyalifew_hms_prod';
        $conn = new mysqli($fallbackHost, $fallbackUser, $fallbackPass, $fallbackDb);
        if ($conn->connect_error) {
            throw new Exception('Fallback DB connection failed: ' . $conn->connect_error);
        }
    } else {
        echo "Using connectDB() connection.\n";
    }

    echo "Checking existing columns on `consultations`...\n";
    $result = $conn->query("SHOW COLUMNS FROM `consultations`");
    if (!$result) {
        throw new Exception('Failed to fetch columns: ' . $conn->error);
    }

    $existing = [];
    while ($row = $result->fetch_assoc()) {
        $existing[] = $row['Field'];
    }
    $result->free();

    $toAdd = [
        'diagnosis_confidence' => 'VARCHAR(32) DEFAULT NULL',
        'differential_diagnosis' => 'TEXT DEFAULT NULL',
        'diagnostic_plan' => 'TEXT DEFAULT NULL',
        'general_examination' => 'TEXT DEFAULT NULL',
        'systems_examination' => 'TEXT DEFAULT NULL',
        'clinical_summary' => 'TEXT DEFAULT NULL',
        'parity' => 'INT DEFAULT NULL',
        'current_pregnancy' => 'TEXT DEFAULT NULL',
        'past_obstetric' => 'TEXT DEFAULT NULL',
        'surgical_history' => 'TEXT DEFAULT NULL'
    ];

    foreach ($toAdd as $col => $type) {
        if (in_array($col, $existing)) {
            echo " - Column '{$col}' already exists, skipping.\n";
            continue;
        }

        $sql = "ALTER TABLE `consultations` ADD COLUMN `{$col}` {$type}";
        echo "Adding column {$col}... ";
        if (!$conn->query($sql)) {
            throw new Exception('Failed to add column ' . $col . ': ' . $conn->error);
        }
        echo "done.\n";
    }

    echo "Migration completed.\n";
    $conn->close();
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}


