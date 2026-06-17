<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('prescriptions', 'prescription_number')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->string('prescription_number', 64)->nullable()->after('prescription_date');
            });
        }

        DB::table('prescriptions')
            ->whereNull('prescription_number')
            ->orderBy('prescription_id')
            ->pluck('prescription_id')
            ->each(function ($id) {
                DB::table('prescriptions')
                    ->where('prescription_id', $id)
                    ->update([
                        'prescription_number' => 'RX-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT),
                    ]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('prescriptions', 'prescription_number')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropColumn('prescription_number');
            });
        }
    }
};
