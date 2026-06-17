<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

        return count($rows) > 0;
    }

    public function up(): void
    {
        if (Schema::hasTable('users') && ! $this->indexExists('users', 'idx_users_name')) {
            // Prefix index avoids utf8mb4 key-length limit on varchar(255) columns.
            DB::statement('ALTER TABLE users ADD INDEX idx_users_name (first_name(50), last_name(50))');
        }

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (! $this->indexExists('appointments', 'idx_appointments_status')) {
                    $table->index('status', 'idx_appointments_status');
                }
                if (! $this->indexExists('appointments', 'idx_appointments_date')) {
                    $table->index('appointment_date', 'idx_appointments_date');
                }
            });
        }

        if (Schema::hasTable('consultations')) {
            Schema::table('consultations', function (Blueprint $table) {
                if (! $this->indexExists('consultations', 'idx_consultations_status')) {
                    $table->index('consultation_status', 'idx_consultations_status');
                }
                if (! $this->indexExists('consultations', 'idx_consultations_date')) {
                    $table->index('consultation_date', 'idx_consultations_date');
                }
            });
        }

        if (Schema::hasTable('lab_test_requests')) {
            Schema::table('lab_test_requests', function (Blueprint $table) {
                if (! $this->indexExists('lab_test_requests', 'idx_lab_requests_status')) {
                    $table->index('status', 'idx_lab_requests_status');
                }
                if (! $this->indexExists('lab_test_requests', 'idx_lab_requests_priority')) {
                    $table->index('priority', 'idx_lab_requests_priority');
                }
                if (! $this->indexExists('lab_test_requests', 'idx_lab_requests_date')) {
                    $table->index('request_date', 'idx_lab_requests_date');
                }
            });
        }

        if (Schema::hasTable('vital_signs')) {
            Schema::table('vital_signs', function (Blueprint $table) {
                if (! $this->indexExists('vital_signs', 'idx_vitals_measured_at')) {
                    $table->index('measured_at', 'idx_vitals_measured_at');
                }
            });
        }

        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                if (! $this->indexExists('prescriptions', 'idx_prescriptions_status')) {
                    $table->index('status', 'idx_prescriptions_status');
                }
                if (! $this->indexExists('prescriptions', 'idx_prescriptions_date')) {
                    $table->index('prescription_date', 'idx_prescriptions_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && $this->indexExists('users', 'idx_users_name')) {
            DB::statement('ALTER TABLE users DROP INDEX idx_users_name');
        }

        Schema::table('appointments', function (Blueprint $table) {
            if ($this->indexExists('appointments', 'idx_appointments_status')) {
                $table->dropIndex('idx_appointments_status');
            }
            if ($this->indexExists('appointments', 'idx_appointments_date')) {
                $table->dropIndex('idx_appointments_date');
            }
        });

        Schema::table('consultations', function (Blueprint $table) {
            if ($this->indexExists('consultations', 'idx_consultations_status')) {
                $table->dropIndex('idx_consultations_status');
            }
            if ($this->indexExists('consultations', 'idx_consultations_date')) {
                $table->dropIndex('idx_consultations_date');
            }
        });

        Schema::table('lab_test_requests', function (Blueprint $table) {
            if ($this->indexExists('lab_test_requests', 'idx_lab_requests_status')) {
                $table->dropIndex('idx_lab_requests_status');
            }
            if ($this->indexExists('lab_test_requests', 'idx_lab_requests_priority')) {
                $table->dropIndex('idx_lab_requests_priority');
            }
            if ($this->indexExists('lab_test_requests', 'idx_lab_requests_date')) {
                $table->dropIndex('idx_lab_requests_date');
            }
        });

        Schema::table('vital_signs', function (Blueprint $table) {
            if ($this->indexExists('vital_signs', 'idx_vitals_measured_at')) {
                $table->dropIndex('idx_vitals_measured_at');
            }
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            if ($this->indexExists('prescriptions', 'idx_prescriptions_status')) {
                $table->dropIndex('idx_prescriptions_status');
            }
            if ($this->indexExists('prescriptions', 'idx_prescriptions_date')) {
                $table->dropIndex('idx_prescriptions_date');
            }
        });
    }
};
