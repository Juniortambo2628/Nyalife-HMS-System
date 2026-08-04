<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_has_fillable_attributes(): void
    {
        $dept = Department::create([
            'department_name' => 'Obstetrics',
            'description' => 'Pregnancy and childbirth care',
            'is_active' => true,
            'code' => 'OBG',
            'type' => 'clinical',
        ]);

        $this->assertSame('Obstetrics', $dept->department_name);
        $this->assertSame('OBG', $dept->code);
        $this->assertTrue((bool) $dept->is_active);
    }

    public function test_department_has_many_staff(): void
    {
        $dept = Department::create([
            'department_name' => 'Pharmacy Unique '.uniqid(),
            'code' => 'PH'.substr(uniqid(), -4),
        ]);

        Staff::factory()->create(['department_id' => $dept->department_id]);
        Staff::factory()->create(['department_id' => $dept->department_id]);

        $this->assertCount(2, $dept->staffMembers);
    }

    public function test_department_active_inactive_toggle(): void
    {
        $dept = Department::create([
            'department_name' => 'Radiology',
            'is_active' => true,
        ]);

        $dept->update(['is_active' => false]);
        $dept->refresh();
        $this->assertFalse((bool) $dept->is_active);

        $dept->update(['is_active' => true]);
        $dept->refresh();
        $this->assertTrue((bool) $dept->is_active);
    }
}
