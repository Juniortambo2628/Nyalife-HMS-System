<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('patient_queues', function (Blueprint $table) { $table->id(); $table->unsignedBigInteger('patient_id'); $table->unsignedInteger('appointment_id')->nullable(); $table->date('queue_date')->index(); $table->unsignedInteger('queue_number'); $table->string('visit_type', 10); $table->string('status', 20)->default('waiting'); $table->unsignedBigInteger('checked_in_by')->nullable(); $table->dateTime('called_at')->nullable(); $table->dateTime('completed_at')->nullable(); $table->timestamps(); $table->unique(['queue_date', 'queue_number']); }); } public function down(): void { Schema::dropIfExists('patient_queues'); } };
