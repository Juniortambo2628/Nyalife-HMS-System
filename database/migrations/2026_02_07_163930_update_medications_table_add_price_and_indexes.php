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
        Schema::table('medications', function (Blueprint $table) {
            if (!Schema::hasColumn('medications', 'price_per_unit')) {
                $table->decimal('price_per_unit', 10, 2)->default(0.00)->after('unit');
            }
            $table->index('medication_name', 'idx_medications_name');
            $table->index('medication_type', 'idx_medications_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->dropIndex('idx_medications_name');
            $table->dropIndex('idx_medications_type');
            if (Schema::hasColumn('medications', 'price_per_unit')) {
                $table->dropColumn('price_per_unit');
            }
        });
    }
};
