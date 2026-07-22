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
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('doctor_id')->nullable();
            $table->string('scan_type'); // e.g. Pelvic Ultrasound, Obstetric Scan, Chest X-Ray
            $table->text('clinical_indication')->nullable();
            $table->text('scan_details')->nullable();
            $table->text('findings')->nullable();
            $table->text('impression')->nullable();
            $table->string('priority')->default('routine'); // routine, urgent, emergency
            $table->string('status')->default('pending'); // pending, processing, pending_verification, verified, completed, cancelled
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('consultation_id')->nullable();
            $table->unsignedInteger('appointment_id')->nullable();

            $table->foreign('patient_id')->references('patient_id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('staff_id')->on('staff')->onDelete('set null');
            $table->foreign('requested_by')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('consultation_id')->references('consultation_id')->on('consultations')->onDelete('set null');
            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->onDelete('set null');
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
