<?php

namespace App\Support;

/**
 * Helpers for coercing free-form obstetric values into whatever shape the
 * underlying DB column can store. The `consultations.parity` column
 * drifted across versions:
 *   - legacy installs: integer column (e.g. `int parity`).
 *   - newer installs: `varchar(50)` after the alter-parity migration.
 *
 * Doctors paste notes-form ("Para 0+0", "G1P0+1", "2+0", "0+0+1") which
 * may be too wide for an integer column and may not be parseable as a
 * number at all. Without normalisation MySQL throws
 * `1366 Incorrect integer value`, the transaction rolls back, and the
 * consultation never persists. That was the production incident on
 * 2026-08-01 for patient 44 ("Para 0+0").
 *
 * `normaliseForColumn()` inspects the live column type and returns a
 * value that is guaranteed to insert cleanly: integer when the column
 * is numeric (parsing digits from the input, falling back to NULL),
 * substring-trimmed string when the column is text, or NULL when the
 * column is missing entirely.
 */
class ParityValue
{
    /**
     * @return int|string|null
     */
    public static function normaliseForColumn(?string $rawValue, string $table = 'consultations', string $column = 'parity')
    {
        if ($rawValue === null) {
            return null;
        }

        $trimmed = trim($rawValue);
        if ($trimmed === '') {
            return null;
        }

        $columnType = self::resolveColumnType($table, $column);

        if ($columnType === null) {
            return null;
        }

        // Numeric column (int, bigint, tinyint, etc.).
        if (self::isNumericType($columnType)) {
            // Pull the leading integer out of the free-form value.
            // "Para 0+0" → 0, "G2P1+0" → 2, "2+0" → 2, "10" → 10.
            if (preg_match('/-?\d+/', $trimmed, $m) === 1) {
                return (int) $m[0];
            }

            // No digits at all — drop the value rather than crash the
            // transaction. The doctor can re-enter it once the column is
            // widened by the alter-parity migration.
            return null;
        }

        // String column (varchar, text, char). Trim to a defensive 50-char
        // ceiling — matches the width the alter-parity migration enforces.
        return mb_substr($trimmed, 0, 50);
    }

    private static array $typeCache = [];

    /**
     * Clear the column-type cache. Call after DDL that changes column types
     * (e.g. ALTER TABLE ... MODIFY) to force a fresh INFORMATION_SCHEMA lookup.
     */
    public static function flushCache(): void
    {
        self::$typeCache = [];
    }

    private static function resolveColumnType(string $table, string $column): ?string
    {
        $key = $table.'.'.$column;
        if (array_key_exists($key, self::$typeCache)) {
            return self::$typeCache[$key];
        }

        $database = \DB::connection()->getDatabaseName();
        $row = \DB::selectOne(
            'SELECT DATA_TYPE AS type
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            [$database, $table, $column]
        );

        return self::$typeCache[$key] = $row?->type;
    }

    private static function isNumericType(string $type): bool
    {
        return in_array(strtolower($type), [
            'tinyint',
            'smallint',
            'mediumint',
            'int',
            'integer',
            'bigint',
            'decimal',
            'numeric',
            'float',
            'double',
            'real',
            'bit',
        ], true);
    }
}
