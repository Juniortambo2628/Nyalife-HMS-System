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
        Schema::table('appointments', function (Blueprint $table) {
            $table->time('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip for SQLite (testing) - SQLite can't change column to NOT NULL
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        Schema::table('appointments', function (Blueprint $table) {
            $table->time('end_time')->nullable(false)->change();
        });
    }
};
