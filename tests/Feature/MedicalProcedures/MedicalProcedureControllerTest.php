<?php

namespace Tests\Feature\MedicalProcedures;

use App\Models\MedicalProcedure;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalProcedureControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->admin = User::factory()->create([
            'role_id' => Role::where('role_name', 'admin')->first()->role_id,
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');
        $this->admin->givePermissionTo(Permissions::MANAGE_SYSTEM);
    }

    public function test_store_creates_medical_procedure(): void
    {
        $this->actingAs($this->admin)
            ->post(route('medical-procedures.store'), [
                'name' => 'Cesarean Section',
                'description' => 'Surgical delivery of a baby',
                'category' => 'Surgical',
                'standard_fee' => 150000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('medical_procedures', [
            'name' => 'Cesarean Section',
            'category' => 'Surgical',
            'standard_fee' => 150000,
            'is_active' => true,
        ]);
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('medical-procedures.store'), [
                'name' => '',
                'category' => '',
                'standard_fee' => '',
            ])
            ->assertSessionHasErrors(['name', 'category', 'standard_fee']);
    }

    public function test_store_rejects_duplicate_name(): void
    {
        MedicalProcedure::factory()->create(['name' => 'IUD Insertion']);

        $this->actingAs($this->admin)
            ->post(route('medical-procedures.store'), [
                'name' => 'IUD Insertion',
                'category' => 'Contraception',
                'standard_fee' => 5000,
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_update_modifies_procedure(): void
    {
        $proc = MedicalProcedure::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin)
            ->put(route('medical-procedures.update', $proc->procedure_id), [
                'name' => 'New Name',
                'category' => 'Updated',
                'standard_fee' => 10000,
            ])
            ->assertRedirect();

        $proc->refresh();
        $this->assertSame('New Name', $proc->name);
        $this->assertSame('Updated', $proc->category);
    }

    public function test_toggle_procedure_active_status(): void
    {
        $proc = MedicalProcedure::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)
            ->post(route('medical-procedures.toggle', $proc->procedure_id))
            ->assertRedirect();

        $proc->refresh();
        $this->assertFalse((bool) $proc->is_active);
    }

    public function test_toggle_from_inactive_to_active(): void
    {
        $proc = MedicalProcedure::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin)
            ->post(route('medical-procedures.toggle', $proc->procedure_id))
            ->assertRedirect();

        $proc->refresh();
        $this->assertTrue((bool) $proc->is_active);
    }

    public function test_destroy_removes_procedure(): void
    {
        $proc = MedicalProcedure::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('medical-procedures.destroy', $proc->procedure_id))
            ->assertRedirect();

        $this->assertDatabaseMissing('medical_procedures', [
            'procedure_id' => $proc->procedure_id,
        ]);
    }
}
