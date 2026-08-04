<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create(['user_id' => $user->user_id]);

        $this->assertNotNull($staff->user);
        $this->assertSame($user->user_id, $staff->user->user_id);
    }

    public function test_staff_belongs_to_department(): void
    {
        $department = Department::factory()->create();
        $staff = Staff::factory()->create(['department_id' => $department->department_id]);

        $this->assertNotNull($staff->departmentRelation);
        $this->assertSame($department->department_id, $staff->departmentRelation->department_id);
    }

    public function test_staff_has_many_appointments(): void
    {
        $staff = Staff::factory()->create();

        $this->assertInstanceOf(HasMany::class, $staff->appointments());
    }

    public function test_staff_fillable_attributes(): void
    {
        $staff = Staff::factory()->create([
            'employee_id' => 'EMP-001',
            'join_date' => '2024-01-15',
        ]);

        $this->assertSame('EMP-001', $staff->employee_id);
        $this->assertSame('2024-01-15', $staff->join_date);
    }
}
