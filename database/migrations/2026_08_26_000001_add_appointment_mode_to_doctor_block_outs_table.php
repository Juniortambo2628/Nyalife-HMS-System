<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_block_outs', function (Blueprint $table) {
            $table->string('appointment_mode', 20)->default('all')->after('end_time');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_block_outs', function (Blueprint $table) {
            $table->dropColumn('appointment_mode');
        });
    }
};
