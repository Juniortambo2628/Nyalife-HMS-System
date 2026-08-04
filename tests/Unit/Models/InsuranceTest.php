<?php

namespace Tests\Unit\Models;

use App\Models\Insurance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InsuranceTest extends TestCase
{
    use RefreshDatabase;

    public function test_insurance_has_fillable_attributes(): void
    {
        $insurance = Insurance::create([
            'name' => 'NHIF',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame('NHIF', $insurance->name);
        $this->assertTrue((bool) $insurance->is_active);
        $this->assertSame(1, $insurance->sort_order);
    }

    public function test_insurance_toggle(): void
    {
        $insurance = Insurance::create([
            'name' => 'AAR',
            'is_active' => true,
        ]);

        $insurance->update(['is_active' => false]);
        $insurance->refresh();
        $this->assertFalse((bool) $insurance->is_active);

        $insurance->update(['is_active' => true]);
        $insurance->refresh();
        $this->assertTrue((bool) $insurance->is_active);
    }

    public function test_insurance_logo_path_nullable(): void
    {
        $insurance = Insurance::create([
            'name' => 'Jubilee',
            'is_active' => true,
        ]);

        $this->assertNull($insurance->logo_path);
    }

    public function test_insurance_active_scope(): void
    {
        $uniqueName = 'Active '.uniqid();
        Insurance::create(['name' => $uniqueName, 'is_active' => true]);

        $active = Insurance::where('name', $uniqueName)->where('is_active', true)->count();
        $this->assertSame(1, $active);
    }
}
