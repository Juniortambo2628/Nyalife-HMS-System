<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use Database\Seeders\RolePermissionsSeeder;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Create legacy patient role
        Role::firstOrCreate(['role_name' => 'patient']);

        // Seed Spatie roles and permissions
        $this->seed(SyncSpatieRolesSeeder::class);
        $this->seed(RolePermissionsSeeder::class);

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'phone' => '0712345678',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        if ($response->getStatusCode() === 500) {
            $exception = $response->exception;
            if ($exception) {
                dump($exception->getMessage());
                dump($exception->getTraceAsString());
            }
        }
        $this->assertEquals(302, $response->getStatusCode());
    }
}
