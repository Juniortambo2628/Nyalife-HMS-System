<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lab_test_requests')) {
            return;
        }

        Schema::table('lab_test_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('lab_test_requests', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('assigned_to');
            }
            if (! Schema::hasColumn('lab_test_requests', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_test_requests', function (Blueprint $table) {
            $table->dropColumn(['verified_by', 'verified_at']);
        });
    }
};
