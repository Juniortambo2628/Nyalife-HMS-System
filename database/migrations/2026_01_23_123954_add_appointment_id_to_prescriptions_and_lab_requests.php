<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('prescriptions', 'appointment_id')) {
                    $table->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
                }
            });
        }

        if (Schema::hasTable('lab_test_requests')) {
            Schema::table('lab_test_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('lab_test_requests', 'appointment_id')) {
                    $table->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal logic skipped for safety in existing tables
    }
};
