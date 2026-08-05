<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * invoices.doctor_id was constrained to legacy `doctors` table.
 * App uses `staff` for physicians; drop obsolete FK so legacy doctors table can be removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Skip for SQLite (testing)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (! Schema::hasTable('invoices')) {
            return;
        }

        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'invoices'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME = 'fk_invoice_doctor'
        ");

        if ($foreignKeys !== []) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign('fk_invoice_doctor');
            });
        }

        if (Schema::hasTable('doctors') && DB::table('doctors')->count() === 0) {
            Schema::dropIfExists('doctors');
        }
    }

    public function down(): void
    {
        // Legacy FK not restored — use database backup if rollback required.
    }
};
