<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PatientRegistrationService
{
    public static function register(array $data): array
    {
        $safeFirstName = str_replace(' ', '', $data['first_name']);
        $safeLastName = str_replace(' ', '', $data['last_name']);
        $email = $data['email'] ?? strtolower($safeFirstName . '.' . $safeLastName . '.' . rand(1000, 9999) . '@nyalife-hms.com');
        $username = strtolower($safeFirstName . '.' . $safeLastName . '.' . rand(1000, 9999));

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $email,
            'phone' => $data['phone'],
            'username' => $username,
            'password' => Hash::make(Str::random(12)),
            'role_id' => Role::idFromName('patient'),
            'is_active' => true,
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'address' => $data['address'] ?? null,
        ]);

        $patient = Patient::create([
            'user_id' => $user->user_id,
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'address' => $data['address'] ?? null,
            'blood_group' => $data['blood_group'] ?? null,
            'height' => $data['height'] ?? null,
            'weight' => $data['weight'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'chronic_diseases' => $data['chronic_diseases'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'insurance_provider' => $data['insurance_provider'] ?? null,
            'insurance_id' => $data['insurance_id'] ?? null,
            'emergency_name' => $data['emergency_name'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'patient_number' => Patient::generateNumber($user->user_id),
        ]);

        return ['user' => $user, 'patient' => $patient];
    }

    public static function quickRegister(array $data): array
    {
        $safeFirstName = str_replace(' ', '', $data['first_name']);
        $safeLastName = str_replace(' ', '', $data['last_name']);
        $email = $data['email'] ?? strtolower($safeFirstName . '.' . $safeLastName . '.' . time() . '@nyalife.com');

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $email,
            'phone' => $data['phone'],
            'username' => strtolower($safeFirstName . '.' . $safeLastName . '.' . time()),
            'password' => Hash::make(Str::random(12)),
            'role_id' => Role::idFromName('patient'),
            'is_active' => true,
        ]);

        $patient = Patient::create([
            'user_id' => $user->user_id,
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'emergency_name' => $data['emergency_name'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'blood_group' => $data['blood_group'] ?? null,
            'patient_number' => Patient::generateNumber($user->user_id),
        ]);

        return ['user' => $user, 'patient' => $patient];
    }
}
