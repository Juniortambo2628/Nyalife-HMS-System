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
        if (! Schema::hasTable('consultations')) {
            return;
        }

        $column = collect(DB::select("SHOW COLUMNS FROM consultations WHERE Field = 'consultation_status'"))->first();
        if ($column && str_contains(strtolower($column->Type ?? ''), 'varchar(20)')) {
            return;
        }

        Schema::table('consultations', function (Blueprint $table) {
            $table->string('consultation_status', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('consultation_status', 10)->change();
        });
    }
};
