<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip for SQLite (testing)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (! Schema::hasTable('appointments')) {
            return;
        }

        $column = collect(DB::select("SHOW COLUMNS FROM appointments WHERE Field = 'appointment_type'"))->first();
        if ($column && strtolower($column->Type ?? '') !== 'varchar(50)') {
            DB::statement('ALTER TABLE appointments MODIFY COLUMN appointment_type VARCHAR(50) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting back to enum could cause data loss if there are 'telehealth' or other custom string values.
        // It's safer to leave it as VARCHAR.
    }
};
