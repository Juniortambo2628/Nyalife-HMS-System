<?php

namespace Tests\Unit\Support;

use App\Support\ParityValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests ParityValue::normaliseForColumn() against the default varchar(50)
 * schema created by the migrations — no DDL needed.
 */
class ParityValueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ParityValue::flushCache();
    }

    public function test_returns_null_for_null_input(): void
    {
        $this->assertNull(ParityValue::normaliseForColumn(null));
    }

    public function test_returns_null_for_empty_string(): void
    {
        $this->assertNull(ParityValue::normaliseForColumn(''));
        $this->assertNull(ParityValue::normaliseForColumn('   '));
    }

    public function test_returns_trimmed_substring_for_string_column(): void
    {
        // consultations.parity is varchar(50) after migrations.
        $this->assertSame('2+0', ParityValue::normaliseForColumn('  2+0  '));
        $this->assertSame('G1P0', ParityValue::normaliseForColumn('G1P0'));
        $this->assertSame('Para 0+0', ParityValue::normaliseForColumn('Para 0+0'));
    }

    public function test_truncates_long_strings_to_fifty_characters(): void
    {
        $long = str_repeat('G1P0+', 30); // 150 chars
        $result = ParityValue::normaliseForColumn($long);
        $this->assertIsString($result);
        $this->assertLessThanOrEqual(50, mb_strlen($result));
    }

    public function test_returns_null_for_missing_column(): void
    {
        $this->assertNull(ParityValue::normaliseForColumn('2+0', 'no_such_table', 'no_such_col'));
    }

    public function test_column_type_caches_across_calls(): void
    {
        $a = ParityValue::normaliseForColumn('2+0');
        $b = ParityValue::normaliseForColumn('G1P0');
        $this->assertSame('2+0', $a);
        $this->assertSame('G1P0', $b);
    }
}
