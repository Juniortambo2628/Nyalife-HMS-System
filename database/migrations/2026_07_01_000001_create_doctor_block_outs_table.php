<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_block_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('staff', 'staff_id')->cascadeOnDelete();
            $table->date('block_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'block_date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_block_outs');
    }
};
