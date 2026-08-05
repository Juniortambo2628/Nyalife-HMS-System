<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\TelehealthConsent;
use Illuminate\Database\Eloquent\Factories\Factory;

class TelehealthConsentFactory extends Factory
{
    protected $model = TelehealthConsent::class;

    public function definition(): array
    {
        $signedAt = $this->faker->boolean(50)
            ? $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s')
            : null;

        return [
            'patient_id' => Patient::factory(),
            'appointment_id' => Appointment::factory(),
            'patient_name' => $this->faker->name(),
            'patient_email' => $this->faker->safeEmail(),
            'patient_phone' => $this->faker->numerify('07########'),
            'doctor_name' => $this->faker->name(),
            'patient_signature_path' => $this->faker->boolean(50) ? $this->faker->imageUrl(400, 200) : null,
            'verbal_consent_obtained' => $this->faker->boolean(80),
            'doctor_signature_path' => $this->faker->boolean(50) ? $this->faker->imageUrl(400, 200) : null,
            'signed_at' => $signedAt,
            'ip_address' => $this->faker->optional(0.5)->ipv4(),
        ];
    }

    public function signed(): static
    {
        return $this->state(fn (array $attributes) => [
            'verbal_consent_obtained' => true,
            'signed_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
