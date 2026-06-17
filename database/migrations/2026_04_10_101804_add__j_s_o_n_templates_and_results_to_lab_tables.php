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
        if (Schema::hasTable('lab_test_types') && ! Schema::hasColumn('lab_test_types', 'template')) {
            Schema::table('lab_test_types', function (Blueprint $table) {
                $table->json('template')->nullable()->after('units');
            });
        }

        if (Schema::hasTable('lab_test_requests') && ! Schema::hasColumn('lab_test_requests', 'results')) {
            Schema::table('lab_test_requests', function (Blueprint $table) {
                $table->json('results')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lab_test_types') && Schema::hasColumn('lab_test_types', 'template')) {
            Schema::table('lab_test_types', function (Blueprint $table) {
                $table->dropColumn('template');
            });
        }

        if (Schema::hasTable('lab_test_requests') && Schema::hasColumn('lab_test_requests', 'results')) {
            Schema::table('lab_test_requests', function (Blueprint $table) {
                $table->dropColumn('results');
            });
        }
    }
};
