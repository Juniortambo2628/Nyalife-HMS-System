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
        Schema::create('telehealth_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('patient_id')->nullable();
            $table->string('patient_name');
            $table->string('patient_email');
            $table->string('patient_phone');
            $table->string('doctor_name')->nullable();
            $table->longText('patient_signature_path')->nullable(); // Base64 signature path or text representation
            $table->boolean('verbal_consent_obtained')->default(false);
            $table->longText('doctor_signature_path')->nullable(); // doctor base64 signature path if counter-signed
            $table->timestamp('signed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('patient_id')->on('patients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telehealth_consents');
    }
};
