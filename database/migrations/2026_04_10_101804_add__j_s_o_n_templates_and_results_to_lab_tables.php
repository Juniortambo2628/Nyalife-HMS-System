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
        Schema::table('lab_test_types', function (Blueprint $table) {
            $table->json('template')->nullable()->after('units');
        });

        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->json('results')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_test_types', function (Blueprint $table) {
            $table->dropColumn('template');
        });

        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->dropColumn('results');
        });
    }
};
