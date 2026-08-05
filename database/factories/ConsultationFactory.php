<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Staff::factory(),
            'appointment_id' => Appointment::factory(),
            'consultation_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
            'consultation_status' => $this->faker->randomElement(['in_progress', 'completed', 'cancelled']),
            'consultation_type' => $this->faker->randomElement(['in_person', 'telehealth']),
            'meeting_link' => $this->faker->optional()->url(),
            'meeting_platform' => $this->faker->optional()->randomElement(['zoom', 'teams', 'google_meet']),
            'is_walk_in' => $this->faker->boolean(10),
            'priority' => $this->faker->randomElement(['normal', 'emergency']),
            'chief_complaint' => $this->faker->sentence(),
            'history_present_illness' => $this->faker->optional()->paragraph(),
            'past_medical_history' => $this->faker->optional()->paragraph(),
            'family_history' => $this->faker->optional()->paragraph(),
            'social_history' => $this->faker->optional()->paragraph(),
            'obstetric_history' => $this->faker->optional()->paragraph(),
            'gynecological_history' => $this->faker->optional()->paragraph(),
            'menstrual_history' => $this->faker->optional()->randomElement([
                null,
                json_encode(['last_period' => '2024-01-15', 'cycle_length' => 28, 'duration' => 5]),
                json_encode(['last_period' => '2024-02-10', 'cycle_length' => 30, 'duration' => 4]),
            ]),
            'cervical_screening' => $this->faker->optional()->paragraph(),
            'contraceptive_history' => $this->faker->optional()->paragraph(),
            'sexual_history' => $this->faker->optional()->paragraph(),
            'review_of_systems' => $this->faker->optional()->paragraph(),
            'vital_signs' => $this->faker->optional()->randomElement([
                null,
                json_encode(['bp' => '120/80', 'hr' => 72, 'temp' => 37.0, 'rr' => 16, 'spo2' => 99]),
                json_encode(['bp' => '130/85', 'hr' => 78, 'temp' => 37.2, 'rr' => 18, 'spo2' => 98]),
            ]),
            'physical_examination' => $this->faker->optional()->paragraph(),
            'general_examination' => $this->faker->optional()->paragraph(),
            'systems_examination' => $this->faker->optional()->paragraph(),
            'diagnosis' => $this->faker->sentence(),
            'diagnosis_confidence' => $this->faker->randomElement(['high', 'moderate', 'low']),
            'differential_diagnosis' => $this->faker->optional()->paragraph(),
            'diagnostic_plan' => $this->faker->optional()->paragraph(),
            'treatment_plan' => $this->faker->optional()->paragraph(),
            'follow_up_instructions' => $this->faker->optional()->paragraph(),
            'notes' => $this->faker->optional()->paragraph(),
            'clinical_summary' => $this->faker->optional()->paragraph(),
            'parity' => $this->faker->optional()->randomElement(['G1P0', 'G2P1', 'G3P2', 'G4P3', 'G5P4']),
            'current_pregnancy' => $this->faker->optional()->paragraph(),
            'past_obstetric' => $this->faker->optional()->randomElement([
                null,
                json_encode(['previous_cs' => 1, 'vaginal_deliveries' => 2]),
                json_encode(['previous_cs' => 0, 'vaginal_deliveries' => 3]),
            ]),
            'surgical_history' => $this->faker->optional()->paragraph(),
            'created_by' => User::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['consultation_status' => 'completed']);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => ['consultation_status' => 'in_progress']);
    }

    public function telehealth(): static
    {
        return $this->state(fn (array $attributes) => [
            'consultation_type' => 'telehealth',
            'meeting_link' => $this->faker->url(),
            'meeting_platform' => $this->faker->randomElement(['zoom', 'teams', 'google_meet']),
        ]);
    }

    public function walkIn(): static
    {
        return $this->state(fn (array $attributes) => ['is_walk_in' => true]);
    }

    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => ['priority' => 'emergency']);
    }
}
