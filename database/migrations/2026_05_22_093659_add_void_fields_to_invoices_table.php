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
        if (! Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'is_voided')) {
                $table->boolean('is_voided')->default(false)->after('status');
            }
            if (! Schema::hasColumn('invoices', 'void_reason')) {
                $table->string('void_reason')->nullable()->after('is_voided');
            }
            if (! Schema::hasColumn('invoices', 'voided_by')) {
                $table->integer('voided_by')->nullable()->after('void_reason');
            }
            if (! Schema::hasColumn('invoices', 'voided_at')) {
                $table->timestamp('voided_at')->nullable()->after('voided_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['is_voided', 'void_reason', 'voided_by', 'voided_at']);
        });
    }
};
