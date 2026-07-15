<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
        });

        Schema::table('medication_batches', function (Blueprint $table) {
            if (Schema::hasColumn('medication_batches', 'supplier_id')) {
                $table->dropColumn('supplier_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->string('item_type', 50)->nullable()->after('medication_id');
        });

        Schema::table('medication_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->after('medication_id');
        });
    }
};
