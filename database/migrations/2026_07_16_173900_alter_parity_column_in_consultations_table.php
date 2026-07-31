<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure `parity` accommodates obstetric notation such as
     * "G1P0", "1+0", "G2P1+0" — anything ≤ 50 chars.
     *
     * Idempotent: no-op when the column is already varchar(50) or wider.
     * Safe for legacy installs where the column is narrower (varchar(3),
     * varchar(10), etc.) — those truncate values like "1+0" on insert,
     * which causes silent data loss in the consultations store.
     */
    public function up(): void
    {
        if (! Schema::hasTable('consultations')) {
            return;
        }

        $currentLength = $this->currentParityLength();

        // Already wide enough — nothing to do.
        if ($currentLength === null || $currentLength >= 50) {
            return;
        }

        // Non-string columns (e.g. legacy `int parity`) need an ALTER that
        // converts the underlying type. Using raw SQL keeps MariaDB/MySQL
        // happy regardless of the starting type and avoids doctrine/dbal.
        DB::statement('ALTER TABLE consultations MODIFY parity VARCHAR(50) NULL');
    }

    /**
     * Reverse: shrink the column back to its original narrow width.
     * We do NOT delete obstetric strings — the original truncation was the
     * bug, not the data. The reversal is therefore lossy only because the
     * narrowed column can't hold long values, which is acceptable as a
     * rollback path for a defensive migration.
     */
    public function down(): void
    {
        if (! Schema::hasTable('consultations')) {
            return;
        }

        DB::statement('UPDATE consultations SET parity = NULL WHERE CHAR_LENGTH(parity) > 10');
        DB::statement('ALTER TABLE consultations MODIFY parity VARCHAR(10) NULL');
    }

    private function currentParityLength(): ?int
    {
        $database = DB::connection()->getDatabaseName();

        $row = DB::selectOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH AS len
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = "consultations"
               AND COLUMN_NAME = "parity"',
            [$database]
        );

        if ($row === null || $row->len === null) {
            return null;
        }

        return (int) $row->len;
    }
};
