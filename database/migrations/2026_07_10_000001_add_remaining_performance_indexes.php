<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        // Skip for SQLite (testing) - check using PRAGMA
        if (DB::getDriverName() === 'sqlite') {
            $result = DB::select("PRAGMA index_list({$table})");
            foreach ($result as $index) {
                if ($index->name === $indexName) {
                    return true;
                }
            }
            return false;
        }
        
        $rows = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($rows) > 0;
    }

    public function up(): void
    {
        if (Schema::hasTable('radiology_requests')) {
            Schema::table('radiology_requests', function (Blueprint $table) {
                if (! $this->indexExists('radiology_requests', 'idx_radiology_status')) {
                    $table->index('status', 'idx_radiology_status');
                }
                if (! $this->indexExists('radiology_requests', 'idx_radiology_priority')) {
                    $table->index('priority', 'idx_radiology_priority');
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! $this->indexExists('payments', 'idx_payments_status')) {
                    $table->index('payment_status', 'idx_payments_status');
                }
                if (! $this->indexExists('payments', 'idx_payments_date')) {
                    $table->index('payment_date', 'idx_payments_date');
                }
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (! $this->indexExists('invoices', 'idx_invoices_status')) {
                    $table->index('status', 'idx_invoices_status');
                }
            });
        }

        if (Schema::hasTable('follow_ups')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                if (! $this->indexExists('follow_ups', 'idx_follow_ups_status')) {
                    $table->index('status', 'idx_follow_ups_status');
                }
                if (! $this->indexExists('follow_ups', 'idx_follow_ups_date')) {
                    $table->index('follow_up_date', 'idx_follow_ups_date');
                }
            });
        }

        if (Schema::hasTable('lab_samples')) {
            Schema::table('lab_samples', function (Blueprint $table) {
                if (! $this->indexExists('lab_samples', 'idx_lab_samples_status')) {
                    $table->index('status', 'idx_lab_samples_status');
                }
            });
        }

        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                if (! $this->indexExists('prescriptions', 'idx_prescriptions_dispensed_by')) {
                    $table->index('dispensed_by', 'idx_prescriptions_dispensed_by');
                }
            });
        }

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (! $this->indexExists('appointments', 'idx_appointments_created_by')) {
                    $table->index('created_by', 'idx_appointments_created_by');
                }
            });
        }

        if (Schema::hasTable('consultations')) {
            Schema::table('consultations', function (Blueprint $table) {
                if (! $this->indexExists('consultations', 'idx_consultations_created_by')) {
                    $table->index('created_by', 'idx_consultations_created_by');
                }
            });
        }
    }

    public function down(): void
    {
        $drop = function (string $table, string $index) {
            if ($this->indexExists($table, $index)) {
                DB::statement("DROP INDEX {$index} ON {$table}");
            }
        };

        $drop('radiology_requests', 'idx_radiology_status');
        $drop('radiology_requests', 'idx_radiology_priority');
        $drop('payments', 'idx_payments_status');
        $drop('payments', 'idx_payments_date');
        $drop('invoices', 'idx_invoices_status');
        $drop('follow_ups', 'idx_follow_ups_status');
        $drop('follow_ups', 'idx_follow_ups_date');
        $drop('lab_samples', 'idx_lab_samples_status');
        $drop('prescriptions', 'idx_prescriptions_dispensed_by');
        $drop('appointments', 'idx_appointments_created_by');
        $drop('consultations', 'idx_consultations_created_by');
    }
};
