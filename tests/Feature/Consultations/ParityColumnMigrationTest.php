<?php

namespace Tests\Feature\Consultations;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Schema-level tests for the parity column.
 *
 * Uses `DatabaseMigrations` (not `RefreshDatabase`) because the assertions
 * require `ALTER TABLE consultations MODIFY parity ...` which IMPLICITLY
 * COMMITS any open transaction in MySQL — that breaks `RefreshDatabase`'s
 * per-test transaction wrapping and leaks rows into subsequent tests.
 *
 * `DatabaseMigrations` is slower (re-runs migrations per test) but is the
 * only isolation strategy compatible with DDL inside the test body.
 */
class ParityColumnMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_alter_parity_migration_is_idempotent_on_wide_column(): void
    {
        // Schema created by DatabaseMigrations already has parity at varchar(50).
        // Running the migration must be a no-op.
        $migration = require __DIR__.'/../../../database/migrations/2026_07_16_173900_alter_parity_column_in_consultations_table.php';
        $migration->up();

        $row = DB::selectOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH AS len
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = "consultations"
               AND COLUMN_NAME = "parity"',
            [DB::connection()->getDatabaseName()]
        );
        $this->assertSame(50, (int) $row->len);
    }

    public function test_alter_parity_migration_widens_narrow_column(): void
    {
        // Simulate the legacy production schema: shrink the column to varchar(3)
        // BEFORE running the migration, then assert the migration widens it.
        DB::statement('ALTER TABLE consultations MODIFY parity VARCHAR(3) NULL');

        $migration = require __DIR__.'/../../../database/migrations/2026_07_16_173900_alter_parity_column_in_consultations_table.php';
        $migration->up();

        $row = DB::selectOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH AS len
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = "consultations"
               AND COLUMN_NAME = "parity"',
            [DB::connection()->getDatabaseName()]
        );
        $this->assertSame(50, (int) $row->len);
    }
}
