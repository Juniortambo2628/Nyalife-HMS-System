<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates core HMS tables for fresh installs where legacy schema pre-dates Laravel migrations.
 * Skips entirely when patients table already exists (production / restored dumps).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patients')) {
            return;
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('role_id');
            $table->string('role_name', 50)->unique();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->increments('department_id');
            $table->string('department_name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('code', 10)->nullable();
            $table->string('type', 30)->default('clinical');
            $table->string('head_name', 100)->nullable();
            $table->string('head_position', 100)->nullable();
            $table->string('head_image')->nullable();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->increments('patient_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('patient_number', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_diseases')->nullable();
            $table->string('emergency_name', 100)->nullable();
            $table->string('emergency_contact', 50)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('insurance_provider', 100)->nullable();
            $table->string('insurance_id', 100)->nullable();
            $table->string('insurance_number', 100)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->increments('staff_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('employee_id', 50)->nullable();
            $table->string('specialization', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->string('position', 100)->nullable();
            $table->string('license_number', 100)->nullable();
            $table->string('qualification')->nullable();
            $table->date('join_date')->nullable();
            $table->string('emergency_contact', 50)->nullable();
            $table->string('emergency_name', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('appointment_id');
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('appointment_type', 50)->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->increments('consultation_id');
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->unsignedBigInteger('appointment_id')->nullable()->index();
            $table->dateTime('consultation_date')->nullable();
            $table->string('consultation_status', 20)->default('in_progress');
            $table->boolean('is_walk_in')->default(false);
            $table->string('priority', 20)->nullable();
            $table->text('chief_complaint')->nullable();
            $table->text('history_present_illness')->nullable();
            $table->text('past_medical_history')->nullable();
            $table->text('family_history')->nullable();
            $table->text('social_history')->nullable();
            $table->text('obstetric_history')->nullable();
            $table->text('gynecological_history')->nullable();
            $table->json('menstrual_history')->nullable();
            $table->text('contraceptive_history')->nullable();
            $table->text('sexual_history')->nullable();
            $table->text('review_of_systems')->nullable();
            $table->json('vital_signs')->nullable();
            $table->text('physical_examination')->nullable();
            $table->text('general_examination')->nullable();
            $table->text('systems_examination')->nullable();
            $table->text('diagnosis')->nullable();
            $table->string('diagnosis_confidence', 30)->nullable();
            $table->text('differential_diagnosis')->nullable();
            $table->text('diagnostic_plan')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->text('notes')->nullable();
            $table->text('clinical_summary')->nullable();
            $table->string('parity', 50)->nullable();
            $table->text('current_pregnancy')->nullable();
            $table->json('past_obstetric')->nullable();
            $table->text('surgical_history')->nullable();
            $table->text('cervical_screening')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('medications', function (Blueprint $table) {
            $table->increments('medication_id');
            $table->string('medication_name');
            $table->string('medication_type', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('strength', 50)->nullable();
            $table->string('unit', 30)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->decimal('price_per_unit', 10, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        Schema::create('medication_batches', function (Blueprint $table) {
            $table->increments('batch_id');
            $table->unsignedBigInteger('medication_id')->index();
            $table->string('batch_number', 50);
            $table->integer('quantity')->default(0);
            $table->date('expiry_date')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('lab_test_types', function (Blueprint $table) {
            $table->increments('test_type_id');
            $table->string('test_name');
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('normal_range')->nullable();
            $table->string('units', 50)->nullable();
            $table->json('template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lab_test_requests', function (Blueprint $table) {
            $table->increments('request_id');
            $table->string('request_number', 50)->nullable()->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->unsignedBigInteger('test_type_id')->nullable()->index();
            $table->string('priority', 20)->default('normal');
            $table->unsignedBigInteger('requested_by')->nullable()->index();
            $table->string('status', 20)->default('pending');
            $table->json('results')->nullable();
            $table->dateTime('request_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('sample_collected_by')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('consultation_id')->nullable()->index();
            $table->unsignedBigInteger('appointment_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('lab_samples', function (Blueprint $table) {
            $table->id();
            $table->string('sample_id', 50)->nullable()->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('test_type_id')->nullable()->index();
            $table->string('sample_type', 30)->default('blood');
            $table->date('collected_date')->nullable();
            $table->unsignedBigInteger('collected_by')->nullable();
            $table->dateTime('collected_at')->nullable();
            $table->string('status', 30)->default('registered');
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('urgent')->default(false);
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->increments('prescription_id');
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('prescribed_by')->nullable()->index();
            $table->unsignedBigInteger('appointment_id')->nullable()->index();
            $table->unsignedBigInteger('consultation_id')->nullable()->index();
            $table->date('prescription_date')->nullable();
            $table->string('prescription_number', 50)->nullable();
            $table->string('status', 20)->default('pending');
            $table->boolean('is_voided')->default(false);
            $table->string('void_reason')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('dispensed_by')->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->increments('item_id');
            $table->unsignedBigInteger('prescription_id')->index();
            $table->unsignedBigInteger('medication_id')->nullable()->index();
            $table->string('item_type', 30)->nullable();
            $table->string('dosage', 100)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->integer('quantity')->default(1);
            $table->string('duration', 100)->nullable();
            $table->text('instructions')->nullable();
            $table->string('status', 20)->nullable();
            $table->unsignedBigInteger('dispensed_by')->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('invoice_id');
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('consultation_id')->nullable()->index();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->string('invoice_number', 50)->nullable()->index();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('payment_method', 30)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('insurance_claim_id', 100)->nullable();
            $table->decimal('insurance_coverage', 12, 2)->nullable();
            $table->decimal('patient_responsibility', 12, 2)->nullable();
            $table->boolean('is_voided')->default(false);
            $table->string('void_reason')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->increments('item_id');
            $table->unsignedBigInteger('invoice_id')->index();
            $table->string('item_type', 30)->nullable();
            $table->unsignedBigInteger('item_id_ref')->nullable();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->unsignedBigInteger('invoice_id')->index();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30);
            $table->dateTime('payment_date');
            $table->string('transaction_reference', 100)->nullable();
            $table->string('payment_status', 20)->default('completed');
            $table->string('status', 20)->default('completed');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamps();
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->increments('follow_up_id');
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('consultation_id')->nullable()->index();
            $table->date('follow_up_date');
            $table->string('follow_up_type', 50)->default('general');
            $table->text('reason');
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->increments('vital_id');
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('consultation_id')->nullable()->index();
            $table->string('blood_pressure', 20)->nullable();
            $table->integer('heart_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->integer('pain_level')->nullable();
            $table->integer('oxygen_saturation')->nullable();
            $table->string('priority', 20)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('measured_at')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->boolean('is_voided')->default(false);
            $table->string('void_reason')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('patients')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        foreach ([
            'vital_signs', 'follow_ups', 'payments', 'invoice_items', 'invoices',
            'prescription_items', 'prescriptions', 'lab_samples', 'lab_test_requests',
            'lab_test_types', 'medication_batches', 'medications', 'consultations',
            'appointments', 'staff', 'patients', 'departments', 'roles',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }
};
