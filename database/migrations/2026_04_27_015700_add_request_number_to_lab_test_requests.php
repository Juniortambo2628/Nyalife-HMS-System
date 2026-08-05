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
        Schema::table('lab_test_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('lab_test_requests', 'request_number')) {
                $table->string('request_number')->nullable()->after('request_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip for SQLite (testing) - SQLite can't drop column with index
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->dropColumn('request_number');
        });
    }
};
