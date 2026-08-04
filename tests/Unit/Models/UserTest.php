<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['admin', 'doctor', 'nurse', 'pharmacist', 'patient'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);
    }

    public function test_user_has_role_attribute(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
        ]);

        $this->assertSame('doctor', $user->role);
    }

    public function test_user_role_relation(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
        ]);

        $this->assertNotNull($user->roleRelation);
        $this->assertSame('nurse', $user->roleRelation->role_name);
    }

    public function test_admin_inherits_all_permissions(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('role_name', 'admin')->first()->role_id,
        ]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->hasPermissionTo('manage-patients'));
        $this->assertTrue($admin->hasPermissionTo('manage-lab'));
        $this->assertTrue($admin->hasPermissionTo('manage-pharmacy'));
        $this->assertTrue($admin->hasPermissionTo('manage-payments'));
    }

    public function test_user_is_active_toggle(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->assertTrue((bool) $user->is_active);

        $user->update(['is_active' => false]);
        $this->assertFalse((bool) $user->is_active);
    }

    public function test_user_full_name_accessor(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertSame('John Doe', $user->full_name);
    }

    public function test_user_unique_username(): void
    {
        $user1 = User::factory()->create(['username' => 'johndoe']);
        $user2 = User::factory()->create(['username' => 'janedoe']);

        $this->assertNotSame($user1->username, $user2->username);
    }

    public function test_user_belongs_to_role(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('role_name', 'pharmacist')->first()->role_id,
        ]);

        $this->assertNotNull($user->roleRelation);
        $this->assertSame('pharmacist', $user->roleRelation->role_name);
    }
}
