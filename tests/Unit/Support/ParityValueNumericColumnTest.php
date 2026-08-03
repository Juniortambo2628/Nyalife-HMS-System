<?php

namespace Tests\Unit\Support;

use App\Support\ParityValue;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tests ParityValue::normaliseForColumn() against a legacy INT column.
 *
 * Uses DatabaseMigrations because the tests ALTER TABLE (DDL), which
 * auto-commits in MySQL and would break RefreshDatabase's transaction
 * wrapping for subsequent tests.
 */
class ParityValueNumericColumnTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        ParityValue::flushCache();
        // Shrink to INT to simulate a legacy production schema.
        DB::statement('ALTER TABLE consultations MODIFY parity INT NULL');
        ParityValue::flushCache();
    }

    protected function tearDown(): void
    {
        // Restore varchar(50) so other test classes see the default schema.
        DB::statement('ALTER TABLE consultations MODIFY parity VARCHAR(50) NULL');
        ParityValue::flushCache();
        parent::tearDown();
    }

    public function test_returns_int_when_column_is_numeric(): void
    {
        $this->assertSame(2, ParityValue::normaliseForColumn('2'));
        $this->assertSame(0, ParityValue::normaliseForColumn('0'));
        $this->assertSame(10, ParityValue::normaliseForColumn('10'));
    }

    public function test_extracts_leading_integer_from_free_form(): void
    {
        $this->assertSame(0, ParityValue::normaliseForColumn('Para 0+0'));
        $this->assertSame(2, ParityValue::normaliseForColumn('2+0'));
        $this->assertSame(2, ParityValue::normaliseForColumn('G2P1+0'));
        $this->assertSame(1, ParityValue::normaliseForColumn('G1P0'));
        $this->assertSame(1, ParityValue::normaliseForColumn('Para 1'));
    }

    public function test_returns_null_when_no_digits_in_input(): void
    {
        $this->assertNull(ParityValue::normaliseForColumn('primi'));
        $this->assertNull(ParityValue::normaliseForColumn('G'));
    }

    public function test_handles_negative_integers(): void
    {
        $this->assertSame(-1, ParityValue::normaliseForColumn('-1'));
    }
}
