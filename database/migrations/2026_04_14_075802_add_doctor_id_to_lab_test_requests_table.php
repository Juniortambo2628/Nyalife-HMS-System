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
        if (! Schema::hasTable('lab_test_requests') || Schema::hasColumn('lab_test_requests', 'doctor_id')) {
            return;
        }

        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->integer('doctor_id')->nullable()->after('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->dropColumn('doctor_id');
        });
    }
};
