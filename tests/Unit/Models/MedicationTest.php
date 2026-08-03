<?php

namespace Tests\Unit\Models;

use App\Models\Medication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_medication_has_fillable_attributes(): void
    {
        $med = Medication::factory()->create();
        $this->assertNotEmpty($med->medication_name);
        $this->assertNotEmpty($med->medication_type);
        $this->assertIsNumeric($med->stock_quantity);
        $this->assertIsNumeric($med->price_per_unit);
    }

    public function test_search_by_name_finds_match(): void
    {
        Medication::factory()->create(['medication_name' => 'Amoxicillin 500mg']);
        Medication::factory()->create(['medication_name' => 'Paracetamol 500mg']);

        $results = Medication::searchByNameOrType('Amoxicillin')->get();
        $this->assertCount(1, $results);
        $this->assertSame('Amoxicillin 500mg', $results->first()->medication_name);
    }

    public function test_search_by_type_finds_match(): void
    {
        Medication::factory()->create(['medication_type' => 'suppository_unique_xyz']);
        Medication::factory()->create(['medication_type' => 'syrup']);

        $results = Medication::searchByNameOrType('suppository_unique_xyz')->get();
        $this->assertCount(1, $results);
        $this->assertSame('suppository_unique_xyz', $results->first()->medication_type);
    }

    public function test_search_by_description_finds_match(): void
    {
        Medication::factory()->create(['description' => 'Antibiotic for respiratory infections']);
        Medication::factory()->create(['description' => 'Pain reliever']);

        $results = Medication::searchByNameOrType('antibiotic')->get();
        $this->assertCount(1, $results);
    }

    public function test_search_by_strength_finds_match(): void
    {
        Medication::factory()->create(['strength' => '750mg_unique_xyz']);
        Medication::factory()->create(['strength' => '100mg']);

        $results = Medication::searchByNameOrType('750mg_unique_xyz')->get();
        $this->assertCount(1, $results);
    }

    public function test_search_empty_string_returns_all(): void
    {
        $before = Medication::count();
        Medication::factory()->count(2)->create();

        $results = Medication::searchByNameOrType('')->get();
        $this->assertSame($before + 2, $results->count());
    }

    public function test_search_no_match_returns_empty(): void
    {
        Medication::factory()->create(['medication_name' => 'Ibuprofen']);

        $results = Medication::searchByNameOrType('NonexistentDrugXYZ')->get();
        $this->assertCount(0, $results);
    }

    public function test_low_stock_factory_state(): void
    {
        $med = Medication::factory()->lowStock()->create();
        $this->assertLessThanOrEqual(10, $med->stock_quantity);
        $this->assertGreaterThanOrEqual(0, $med->stock_quantity);
    }

    public function test_expired_factory_state(): void
    {
        $med = Medication::factory()->expired()->create();
        $this->assertLessThan(now()->format('Y-m-d'), $med->expiry_date);
    }

    public function test_stock_decrement(): void
    {
        $med = Medication::factory()->create(['stock_quantity' => 50]);
        $med->decrement('stock_quantity', 10);
        $med->refresh();
        $this->assertSame(40, $med->stock_quantity);
    }

    public function test_stock_increment(): void
    {
        $med = Medication::factory()->create(['stock_quantity' => 50]);
        $med->increment('stock_quantity', 20);
        $med->refresh();
        $this->assertSame(70, $med->stock_quantity);
    }

    public function test_primary_key_is_medication_id(): void
    {
        $med = Medication::factory()->create();
        $this->assertNotNull($med->medication_id);
        $this->assertSame($med->getKey(), $med->medication_id);
    }

    public function test_search_is_case_insensitive(): void
    {
        Medication::factory()->create(['medication_name' => 'ZopicloneUniqueXYZ']);

        $lower = Medication::searchByNameOrType('zopicloneuniquexyz')->count();
        $upper = Medication::searchByNameOrType('ZOPICLONEUNIQUEXYZ')->count();
        $mixed = Medication::searchByNameOrType('ZopicloneUniqueXYZ')->count();

        $this->assertSame(1, $lower);
        $this->assertSame(1, $upper);
        $this->assertSame(1, $mixed);
    }
}
