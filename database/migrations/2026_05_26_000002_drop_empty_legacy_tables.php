<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drops pre-Laravel tables that have zero rows and no Eloquent model.
 * Skips any table that does not exist or has rows > 0.
 */
return new class extends Migration
{
    private array $candidates = [
        'lab_requests',
        'lab_results',
        'lab_attachments',
        'medical_history',
        'doctor_schedules',
        // 'doctors' — kept: invoices.fk_invoice_doctor still references this table (see 2026_05_26_000003)
        'payment_transactions',
        'activity_logs',
        'referrals',
        'system_notifications',
        'remember_tokens',
        'obstetric_history',
        'pregnancy_details',
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
        // Tables not recreated — restore from database backup if needed.
    }
};
