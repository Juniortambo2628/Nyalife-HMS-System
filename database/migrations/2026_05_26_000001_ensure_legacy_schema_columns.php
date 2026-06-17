<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent column ensures for legacy tables that pre-date Laravel migrations.
 * Safe on production DBs where columns already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'gender')) {
                    $table->string('gender', 20)->nullable()->after('phone');
                }
                if (! Schema::hasColumn('users', 'date_of_birth')) {
                    $table->date('date_of_birth')->nullable()->after('gender');
                }
                if (! Schema::hasColumn('users', 'address')) {
                    $table->text('address')->nullable()->after('date_of_birth');
                }
            });
        }

        if (Schema::hasTable('lab_test_requests') && ! Schema::hasColumn('lab_test_requests', 'sample_collected_by')) {
            Schema::table('lab_test_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('sample_collected_by')->nullable()->after('status');
            });
        }

        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                if (! Schema::hasColumn('prescriptions', 'dispensed_by')) {
                    $table->unsignedBigInteger('dispensed_by')->nullable();
                }
                if (! Schema::hasColumn('prescriptions', 'dispensed_at')) {
                    $table->timestamp('dispensed_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('staff') && ! Schema::hasColumn('staff', 'department_id')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('department');
            });
        }

        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (! Schema::hasColumn('patients', 'patient_number')) {
                    $table->string('patient_number', 50)->nullable();
                }
                if (! Schema::hasColumn('patients', 'blood_group')) {
                    $table->string('blood_group', 10)->nullable();
                }
                if (! Schema::hasColumn('patients', 'height')) {
                    $table->decimal('height', 5, 2)->nullable();
                }
                if (! Schema::hasColumn('patients', 'weight')) {
                    $table->decimal('weight', 5, 2)->nullable();
                }
                if (! Schema::hasColumn('patients', 'allergies')) {
                    $table->text('allergies')->nullable();
                }
                if (! Schema::hasColumn('patients', 'chronic_diseases')) {
                    $table->text('chronic_diseases')->nullable();
                }
                if (! Schema::hasColumn('patients', 'marital_status')) {
                    $table->string('marital_status', 30)->nullable();
                }
                if (! Schema::hasColumn('patients', 'occupation')) {
                    $table->string('occupation', 100)->nullable();
                }
                if (! Schema::hasColumn('patients', 'insurance_provider')) {
                    $table->string('insurance_provider', 100)->nullable();
                }
                if (! Schema::hasColumn('patients', 'insurance_number')) {
                    $table->string('insurance_number', 100)->nullable();
                }
                if (! Schema::hasColumn('patients', 'insurance_expiry')) {
                    $table->date('insurance_expiry')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty — do not drop legacy columns on rollback.
    }
};
