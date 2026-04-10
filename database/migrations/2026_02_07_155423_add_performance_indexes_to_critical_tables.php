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
        Schema::table('users', function (Blueprint $table) {
            $table->index(['first_name', 'last_name'], 'idx_users_name');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index('status', 'idx_appointments_status');
            $table->index('appointment_date', 'idx_appointments_date');
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->index('consultation_status', 'idx_consultations_status');
            $table->index('consultation_date', 'idx_consultations_date');
        });

        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->index('status', 'idx_lab_requests_status');
            $table->index('priority', 'idx_lab_requests_priority');
            $table->index('request_date', 'idx_lab_requests_date');
        });

        Schema::table('vital_signs', function (Blueprint $table) {
            $table->index('measured_at', 'idx_vitals_measured_at');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->index('status', 'idx_prescriptions_status');
            $table->index('prescription_date', 'idx_prescriptions_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_name');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_appointments_status');
            $table->dropIndex('idx_appointments_date');
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->dropIndex('idx_consultations_status');
            $table->dropIndex('idx_consultations_date');
        });

        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->dropIndex('idx_lab_requests_status');
            $table->dropIndex('idx_lab_requests_priority');
            $table->dropIndex('idx_lab_requests_date');
        });

        Schema::table('vital_signs', function (Blueprint $table) {
            $table->dropIndex('idx_vitals_measured_at');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropIndex('idx_prescriptions_status');
            $table->dropIndex('idx_prescriptions_date');
        });
    }
};
