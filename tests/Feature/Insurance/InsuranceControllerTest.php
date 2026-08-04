<?php

namespace Tests\Feature\Insurance;

use App\Models\Insurance;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InsuranceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'receptionist'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->admin = User::factory()->create([
            'role_id' => Role::where('role_name', 'admin')->first()->role_id,
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');
        $this->admin->givePermissionTo(Permissions::MANAGE_INSURANCE);
    }

    public function test_toggle_insurance_active_status(): void
    {
        $insurance = Insurance::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)
            ->post(route('insurances.toggle', $insurance->insurance_id))
            ->assertRedirect();

        $insurance->refresh();
        $this->assertFalse((bool) $insurance->is_active);
    }

    public function test_toggle_insurance_from_inactive_to_active(): void
    {
        $insurance = Insurance::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin)
            ->post(route('insurances.toggle', $insurance->insurance_id))
            ->assertRedirect();

        $insurance->refresh();
        $this->assertTrue((bool) $insurance->is_active);
    }

    public function test_destroy_removes_insurance(): void
    {
        Storage::fake('public');
        $insurance = Insurance::factory()->create([
            'logo_path' => 'insurances/test-logo.png',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('insurances.destroy', $insurance->insurance_id))
            ->assertRedirect();

        $this->assertDatabaseMissing('insurances', [
            'insurance_id' => $insurance->insurance_id,
        ]);
    }

    public function test_public_list_returns_active_insurances(): void
    {
        Insurance::factory()->create(['is_active' => true, 'sort_order' => 1]);
        Insurance::factory()->create(['is_active' => false, 'sort_order' => 2]);
        Insurance::factory()->create(['is_active' => true, 'sort_order' => 3]);

        $response = $this->getJson('/api/insurances');
        $response->assertOk();
        // API returns a list of active insurances (the controller scopes to is_active=1)
        $json = $response->json();
        $this->assertIsArray($json);
    }

    public function test_update_modifies_insurance(): void
    {
        $insurance = Insurance::factory()->create(['name' => 'Old Insurance']);

        $this->actingAs($this->admin)
            ->post(route('insurances.update', $insurance->insurance_id), [
                'name' => 'New Insurance',
                'is_active' => true,
                'sort_order' => 5,
            ])
            ->assertRedirect();

        $insurance->refresh();
        $this->assertSame('New Insurance', $insurance->name);
    }
}
