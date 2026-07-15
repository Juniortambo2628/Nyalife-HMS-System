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
        
        if (! Schema::hasTable('invoices')) {
            return;
        }

        $column = collect(DB::select("SHOW COLUMNS FROM invoices WHERE Field = 'status'"))->first();
        if ($column && str_contains(strtolower($column->Type ?? ''), 'varchar(20)')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 10)->change();
        });
    }
};
