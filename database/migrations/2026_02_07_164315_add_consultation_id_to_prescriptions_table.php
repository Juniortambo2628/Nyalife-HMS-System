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
        Schema::table('prescriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('prescriptions', 'consultation_id')) {
                $table->unsignedBigInteger('consultation_id')->nullable()->after('appointment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip for SQLite (testing) - dropping columns with indexes causes issues
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('prescriptions', 'consultation_id')) {
                $table->dropColumn('consultation_id');
            }
        });
    }
};
