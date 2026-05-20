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
        Schema::create('radiology_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('request_number')->unique();
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('staff', 'staff_id')->onDelete('set null');
            $table->string('scan_type'); // e.g. Pelvic Ultrasound, Obstetric Scan, Chest X-Ray
            $table->text('clinical_indication')->nullable();
            $table->text('scan_details')->nullable();
            $table->text('findings')->nullable();
            $table->text('impression')->nullable();
            $table->string('priority')->default('routine'); // routine, urgent, emergency
            $table->string('status')->default('pending'); // pending, processing, pending_verification, verified, completed, cancelled
            $table->foreignId('requested_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->foreignId('verified_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('consultation_id')->nullable()->constrained('consultations', 'consultation_id')->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments', 'appointment_id')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_requests');
    }
};
