<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        if (! Schema::hasTable('lab_test_requests')) {
            return;
        }

        $priority = collect(DB::select("SHOW COLUMNS FROM lab_test_requests WHERE Field = 'priority'"))->first();
        if ($priority && str_contains(strtolower($priority->Type ?? ''), 'varchar(20)')) {
            return;
        }

        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->string('priority', 20)->default('normal')->change();
            $table->string('status', 20)->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->string('priority', 10)->default('normal')->change();
        });
    }
};
