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
        Schema::table('lab_test_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_test_requests', 'priority')) {
                 // Check if we can use an 'after' column that definitely exists
                 $after = Schema::hasColumn('lab_test_requests', 'test_id') ? 'test_id' : 'patient_id';
                 $table->string('priority')->default('normal')->after($after);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
