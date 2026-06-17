<?php

/**
 * Export legacy table data to CSV before Phase 4 cleanup.
 *
 * Usage:
 *   php scripts/export-legacy-tables.php
 *   php scripts/export-legacy-tables.php audit_logs medication_categories
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$defaultTables = [
    'audit_logs',
    'phinxlog',
    'services',
    'specializations',
    'user_tokens',
    'lab_test_items',
    'lab_test_parameters',
    'lab_parameters',
    'email_queue',
    'medication_categories',
];

$tables = $argv[1] ?? null
    ? array_slice($argv, 1)
    : $defaultTables;

$exportDir = storage_path('legacy-exports/' . date('Y-m-d_His'));

if (! is_dir($exportDir) && ! mkdir($exportDir, 0755, true) && ! is_dir($exportDir)) {
    fwrite(STDERR, "Failed to create export directory: {$exportDir}\n");
    exit(1);
}

echo "Exporting to {$exportDir}\n\n";

foreach ($tables as $table) {
    if (! Schema::hasTable($table)) {
        echo "{$table}: SKIP (table not found)\n";
        continue;
    }

    $rows = DB::table($table)->get();
    $count = $rows->count();
    $path = "{$exportDir}/{$table}.csv";

    $handle = fopen($path, 'w');
    if ($handle === false) {
        echo "{$table}: ERR (cannot write {$path})\n";
        continue;
    }

    if ($count === 0) {
        fclose($handle);
        echo "{$table}: 0 rows (empty CSV created)\n";
        continue;
    }

    $columns = array_keys((array) $rows->first());
    fputcsv($handle, $columns);

    foreach ($rows as $row) {
        fputcsv($handle, array_map(fn ($v) => is_scalar($v) || $v === null ? $v : json_encode($v), (array) $row));
    }

    fclose($handle);
    echo "{$table}: {$count} rows → {$path}\n";
}

echo "\nDone.\n";
