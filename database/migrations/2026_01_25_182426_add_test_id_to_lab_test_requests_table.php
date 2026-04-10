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
            if (!Schema::hasColumn('lab_test_requests', 'test_id')) {
                $table->integer('test_id')->after('consultation_id')->nullable();
                $table->index('test_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_test_requests', function (Blueprint $table) {
            //
        });
    }
};
