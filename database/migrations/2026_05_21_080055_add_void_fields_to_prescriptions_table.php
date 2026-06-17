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
        if (! Schema::hasTable('prescriptions')) {
            return;
        }

        Schema::table('prescriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('prescriptions', 'is_voided')) {
                $table->boolean('is_voided')->default(false)->after('status');
            }
            if (! Schema::hasColumn('prescriptions', 'void_reason')) {
                $table->string('void_reason')->nullable()->after('is_voided');
            }
            if (! Schema::hasColumn('prescriptions', 'voided_by')) {
                $table->integer('voided_by')->nullable()->after('void_reason');
            }
            if (! Schema::hasColumn('prescriptions', 'voided_at')) {
                $table->timestamp('voided_at')->nullable()->after('voided_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            //
        });
    }
};
