<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drops pre-Laravel tables that have no Eloquent model and zero application references.
 * Only drops when the table exists AND has zero rows (safe default).
 *
 * Before running on production with populated legacy tables, export data:
 *   php scripts/export-legacy-tables.php
 */
return new class extends Migration
{
    private array $candidates = [
        'audit_logs',
        'services',
        'specializations',
        'user_tokens',
        'lab_test_items',
        'lab_test_parameters',
        'lab_parameters',
        'email_queue',
        'medication_categories',
        'phinxlog',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->candidates as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (DB::table($table)->count() === 0) {
                Schema::dropIfExists($table);
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Restore from backup or storage/legacy-exports/ if needed.
    }
};
