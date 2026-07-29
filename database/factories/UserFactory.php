<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username' => fake()->userName() . '_' . uniqid(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('07########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'role_id' => static::patientRoleId(),
            'is_active' => true,
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    protected static function patientRoleId(): int
    {
        return Role::query()->firstOrCreate(
            ['role_name' => 'patient'],
        )->role_id;
    }
}
