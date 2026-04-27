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
            if (Schema::hasColumn('lab_test_requests', 'test_id') && !Schema::hasColumn('lab_test_requests', 'test_type_id')) {
                $table->renameColumn('test_id', 'test_type_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_test_requests', function (Blueprint $table) {
            if (Schema::hasColumn('lab_test_requests', 'test_type_id')) {
                $table->renameColumn('test_type_id', 'test_id');
            }
        });
    }
};
