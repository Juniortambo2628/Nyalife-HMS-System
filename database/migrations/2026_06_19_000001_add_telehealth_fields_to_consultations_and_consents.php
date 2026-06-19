<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            if (!Schema::hasColumn('consultations', 'consultation_type')) {
                $table->string('consultation_type', 20)->default('in_person')->after('consultation_status');
            }
            if (!Schema::hasColumn('consultations', 'meeting_link')) {
                $table->string('meeting_link', 500)->nullable()->after('consultation_type');
            }
            if (!Schema::hasColumn('consultations', 'meeting_platform')) {
                $table->string('meeting_platform', 50)->nullable()->after('meeting_link');
            }
        });

        Schema::table('telehealth_consents', function (Blueprint $table) {
            if (!Schema::hasColumn('telehealth_consents', 'appointment_id')) {
                $table->unsignedInteger('appointment_id')->nullable()->after('patient_id');
                $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('telehealth_consents', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['consultation_type', 'meeting_link', 'meeting_platform']);
        });
    }
};
