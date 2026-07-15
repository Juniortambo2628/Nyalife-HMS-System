<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        $column = collect(DB::select("SHOW COLUMNS FROM appointments WHERE Field = 'status'"))->first();
        if (! $column || ! str_contains(strtolower($column->Type ?? ''), 'enum')) {
            return;
        }

        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show', 'pending') DEFAULT 'scheduled'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip for SQLite (testing)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled'");
    }
};
