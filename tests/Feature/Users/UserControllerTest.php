<?php

namespace Tests\Feature\Users;

use App\Models\Department;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'doctor', 'nurse', 'receptionist', 'patient'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->admin = User::factory()->create([
            'role_id' => Role::where('role_name', 'admin')->first()->role_id,
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');
        $this->admin->givePermissionTo(Permissions::MANAGE_USERS);
    }

    public function test_store_creates_user_with_staff(): void
    {
        $department = Department::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane.doe@example.com',
                'role' => 'doctor',
                'department_id' => $department->department_id,
                'password' => 'securepass123',
                'password_confirmation' => 'securepass123',
            ])
            ->assertRedirect();

        $user = User::where('email', 'jane.doe@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue((bool) $user->is_active);

        // Non-patient should have Staff record
        $staff = Staff::where('user_id', $user->user_id)->first();
        $this->assertNotNull($staff);
        $this->assertNotNull($staff->employee_id);
    }

    public function test_store_patient_does_not_create_staff(): void
    {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'first_name' => 'John',
                'last_name' => 'Patient',
                'email' => 'john.patient@example.com',
                'role' => 'patient',
                'password' => 'securepass123',
                'password_confirmation' => 'securepass123',
            ])
            ->assertRedirect();

        $user = User::where('email', 'john.patient@example.com')->first();
        $this->assertNotNull($user);

        $staff = Staff::where('user_id', $user->user_id)->first();
        $this->assertNull($staff);
    }

    public function test_store_generates_username_if_not_provided(): void
    {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'first_name' => 'Auto',
                'last_name' => 'Gen',
                'email' => 'auto.gen@example.com',
                'role' => 'nurse',
                'password' => 'securepass123',
                'password_confirmation' => 'securepass123',
            ])
            ->assertRedirect();

        $user = User::where('email', 'auto.gen@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->username);
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
            ])
            ->assertSessionHasErrors(['first_name', 'last_name', 'email']);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'first_name' => 'Dupe',
                'last_name' => 'User',
                'email' => 'duplicate@example.com',
                'role' => 'nurse',
                'password' => 'securepass123',
                'password_confirmation' => 'securepass123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_update_modifies_user(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'email' => 'old.name@example.com',
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->user_id), [
                'first_name' => 'New',
                'last_name' => 'Name',
                'email' => 'new.name@example.com',
                'role' => 'doctor',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('New', $user->first_name);
        $this->assertSame('new.name@example.com', $user->email);
    }

    public function test_update_role_change_creates_staff(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('role_name', 'patient')->first()->role_id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->user_id), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => 'nurse',
            ])
            ->assertRedirect();

        $staff = Staff::where('user_id', $user->user_id)->first();
        $this->assertNotNull($staff);
    }

    public function test_update_role_change_to_patient_deletes_staff(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
        ]);
        Staff::factory()->create(['user_id' => $user->user_id]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->user_id), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => 'patient',
            ])
            ->assertRedirect();

        $staff = Staff::where('user_id', $user->user_id)->first();
        $this->assertNull($staff);
    }

    public function test_destroy_removes_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $user->user_id))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'user_id' => $user->user_id,
        ]);
    }

    public function test_bulk_deactivate_excludes_self(): void
    {
        $user1 = User::factory()->create(['is_active' => true]);
        $user2 = User::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)
            ->post(route('users.bulk-action'), [
                'action' => 'deactivate',
                'ids' => [$this->admin->user_id, $user1->user_id, $user2->user_id],
            ])
            ->assertRedirect();

        $this->admin->refresh();
        $user1->refresh();
        $user2->refresh();

        $this->assertTrue((bool) $this->admin->is_active); // Self not deactivated
        $this->assertFalse((bool) $user1->is_active);
        $this->assertFalse((bool) $user2->is_active);
    }

    public function test_bulk_delete_excludes_self(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('users.bulk-action'), [
                'action' => 'delete',
                'ids' => [$this->admin->user_id, $user->user_id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['user_id' => $this->admin->user_id]); // Self not deleted
        $this->assertDatabaseMissing('users', ['user_id' => $user->user_id]);
    }

    public function test_unauthorized_user_cannot_manage_users(): void
    {
        $nurse = User::factory()->create([
            'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            'is_active' => true,
        ]);
        $nurse->assignRole('nurse');

        $this->actingAs($nurse)
            ->get(route('users.index'))
            ->assertForbidden();
    }
}
