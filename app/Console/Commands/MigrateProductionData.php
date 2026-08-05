<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateProductionData extends Command
{
    protected $signature = 'production:migrate-data {--fresh : Drop and recreate tables first}';

    protected $description = 'Migrate production data from legacy DB (nyalifew_legacy) to new schema';

    private $legacy;

    public function handle()
    {
        if ($this->option('fresh')) {
            $this->warn('This will drop all tables and re-migrate!');
            if (! $this->confirm('Continue?')) {
                return 1;
            }
            $this->call('migrate:fresh', ['--force' => true]);
        }

        // Configure legacy DB connection dynamically
        config(['database.connections.legacy' => [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => 'nyalifew_legacy',
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        try {
            $this->legacy = DB::connection('legacy');
            $this->legacy->getPdo();
            $this->info('Connected to legacy database (nyalifew_legacy)');
        } catch (\Exception $e) {
            $this->error('Cannot connect to legacy database: '.$e->getMessage());
            $this->error("Create 'nyalifew_legacy' database and import production_database_15_7_26.sql first");

            return 1;
        }

        // Check if legacy tables have data
        $userCount = $this->legacy->table('users')->count();
        $this->info("Found {$userCount} users in legacy database");

        if ($userCount === 0) {
            $this->error('Legacy database appears empty. Import data first.');

            return 1;
        }

        $this->info('Starting data migration...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET UNIQUE_CHECKS=0');

        $tables = [
            'users',
            'roles',
            'permissions',
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
            'departments',
            'staff',
            'patients',
            'appointments',
            'consultations',
            'prescriptions',
            'prescription_items',
            'medications',
            'medication_batches',
            'invoices',
            'invoice_items',
            'payments',
            'lab_test_types',
            'lab_test_requests',
            'lab_samples',
            'vital_signs',
            'follow_ups',
            'radiology_requests',
            'insurances',
            'medical_procedures',
            'telehealth_consents',
            'pharmacy_purchase_orders',
            'mail_templates',
            'doctor_block_outs',
            'contact_messages',
            'messages',
            'newsletter_subscribers',
            'settings',
            'service_tabs',
            'blogs',
            'activity_log',
            'personal_access_tokens',
        ];

        $totalInserted = 0;

        foreach ($tables as $table) {
            try {
                // Check if table exists in legacy
                $legacyExists = $this->legacy->getSchemaBuilder()->hasTable($table);
                $localExists = DB::getSchemaBuilder()->hasTable($table);

                if (! $legacyExists) {
                    $this->line("  SKIP {$table} - not in legacy DB");

                    continue;
                }

                if (! $localExists) {
                    $this->line("  SKIP {$table} - not in local DB");

                    continue;
                }

                // Get legacy columns
                $legacyColumns = $this->legacy->getSchemaBuilder()->getColumnListing($table);
                $localColumns = DB::getSchemaBuilder()->getColumnListing($table);

                // Only use columns that exist in both
                $commonColumns = array_intersect($legacyColumns, $localColumns);

                if (empty($commonColumns)) {
                    $this->line("  SKIP {$table} - no common columns");

                    continue;
                }

                // Get count
                $count = $this->legacy->table($table)->count();

                if ($count === 0) {
                    $this->line("  SKIP {$table} - 0 rows");

                    continue;
                }

                // Transfer in batches
                $columns = implode(', ', array_map(fn ($c) => "`{$c}`", $commonColumns));
                $batchSize = 500;
                $offset = 0;
                $inserted = 0;

                $this->line("  Migrating {$table} ({$count} rows)...");

                while (true) {
                    $rows = $this->legacy->select("SELECT {$columns} FROM `{$table}` ORDER BY 1 LIMIT {$batchSize} OFFSET {$offset}");

                    if (empty($rows)) {
                        break;
                    }

                    // Build bulk insert
                    $values = [];
                    $bindings = [];

                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $rowValues = [];
                        foreach ($commonColumns as $col) {
                            $rowValues[] = '?';
                            $bindings[] = $rowArray[$col] ?? null;
                        }
                        $values[] = '('.implode(', ', $rowValues).')';
                    }

                    if (! empty($values)) {
                        DB::statement("INSERT IGNORE INTO `{$table}` ({$columns}) VALUES ".implode(', ', $values), $bindings);
                        $inserted += count($rows);
                    }

                    $offset += $batchSize;

                    if (count($rows) < $batchSize) {
                        break;
                    }
                }

                $totalInserted += $inserted;
                $this->line("  OK {$table}: {$inserted} rows");

            } catch (\Exception $e) {
                $this->error("  ERROR {$table}: ".$e->getMessage());
            }
        }

        DB::statement('SET UNIQUE_CHECKS=1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->newLine();
        $this->info("Migration complete! Total rows transferred: {$totalInserted}");

        // Show summary
        $this->newLine();
        $this->table(['Table', 'Count'], $this->getCounts($tables));

        return 0;
    }

    private function getCounts(array $tables): array
    {
        $results = [];
        foreach ($tables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $count = DB::table($table)->count();
                    $results[] = [$table, $count];
                }
            } catch (\Exception $e) {
                $results[] = [$table, 'error'];
            }
        }

        return $results;
    }
}
