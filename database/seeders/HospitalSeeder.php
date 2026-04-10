<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Medication;
use App\Models\MedicationBatch;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Ensure an admin/creator user exists (ID 1)
        $admin = User::find(1);
        if (!$admin) {
            $admin = User::create([
                'user_id' => 1,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@nyalife.com',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role_id' => 1,
                'is_active' => true,
            ]);
        }

        // 1. Seed Patients
        $patientsData = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john.doe@nyalife.com', 'phone' => '0712345678', 'dob' => '1990-05-15', 'gender' => 'male'],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane.smith@nyalife.com', 'phone' => '0722345678', 'dob' => '1985-11-20', 'gender' => 'female'],
            ['first_name' => 'Alice', 'last_name' => 'Brown', 'email' => 'alice.brown@nyalife.com', 'phone' => '0732345678', 'dob' => '1995-02-10', 'gender' => 'female'],
        ];

        $patients = [];
        foreach ($patientsData as $data) {
            $user = User::updateOrCreate(
                ['username' => strtolower($data['first_name'] . '.' . $data['last_name'])],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'date_of_birth' => $data['dob'],
                    'gender' => $data['gender'],
                    'password' => Hash::make('password123'),
                    'role_id' => 7, // Patient
                    'is_active' => true,
                ]
            );

            $patients[] = Patient::updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'patient_number' => 'PAT-' . date('Ymd') . '-' . str_pad($user->user_id, 4, '0', STR_PAD_LEFT),
                ]
            );
        }

        // 2. Doctors
        if (Staff::count() < 1) {
            $docUser = User::updateOrCreate(
                ['username' => 'dr.wilson'],
                [
                    'first_name' => 'Dr. James',
                    'last_name' => 'Wilson',
                    'email' => 'james.wilson@nyalife.com',
                    'password' => Hash::make('password123'),
                    'role_id' => 2, // Doctor
                    'is_active' => true,
                    'gender' => 'male',
                    'date_of_birth' => '1980-01-01'
                ]
            );
            Staff::updateOrCreate(
                ['user_id' => $docUser->user_id],
                [
                    'specialization' => 'General Physician',
                    'department' => 'OPD'
                ]
            );
        }
        
        $doctors = Staff::all();

        // 3. Lab Test Types
        $testTypes = [
            ['test_name' => 'Full Blood Count', 'price' => 1500, 'normal_range' => 'See report', 'category' => 'Hematology', 'units' => 'various'],
            ['test_name' => 'Malaria Parasites', 'price' => 500, 'normal_range' => 'Negative', 'category' => 'Parasitology', 'units' => 'N/A'],
            ['test_name' => 'Blood Sugar', 'price' => 800, 'normal_range' => '3.9 - 5.5 mmol/L', 'category' => 'Biochemistry', 'units' => 'mmol/L'],
        ];

        foreach ($testTypes as $type) {
            LabTestType::updateOrCreate(['test_name' => $type['test_name']], $type + ['is_active' => true]);
        }
        $createdTestTypes = LabTestType::all();

        // 4. Medications
        if (Medication::count() < 3) {
            $meds = [
                ['medication_name' => 'Paracetamol', 'medication_type' => 'Tablet', 'strength' => '500mg', 'unit' => 'Pills'],
                ['medication_name' => 'Amoxicillin', 'medication_type' => 'Capsule', 'strength' => '250mg', 'unit' => 'Pills'],
                ['medication_name' => 'Cough Syrup', 'medication_type' => 'Syrup', 'strength' => '100ml', 'unit' => 'Bottles'],
            ];

            foreach ($meds as $med) {
                $m = Medication::updateOrCreate(['medication_name' => $med['medication_name']], $med + ['stock_quantity' => 100]);

                if ($m->wasRecentlyCreated || MedicationBatch::where('medication_id', $m->medication_id)->count() == 0) {
                    MedicationBatch::updateOrCreate(
                        ['medication_id' => $m->medication_id, 'batch_number' => 'BATCH-A1'],
                        [
                            'quantity' => 100,
                            'expiry_date' => now()->addYears(2),
                            'manufacturing_date' => now()->subMonths(6),
                            'created_by' => $admin->user_id,
                        ]
                    );
                }
            }
        }

        // 5. Appointments & Consultations
        foreach ($patients as $index => $patient) {
            $doctor = $doctors->random();
            
            $apt = Appointment::firstOrCreate(
                ['patient_id' => $patient->patient_id, 'appointment_date' => now()->subDays($index + 1)->toDateString()],
                [
                    'doctor_id' => $doctor->staff_id,
                    'appointment_time' => '10:00:00',
                    'end_time' => '10:30:00',
                    'status' => 'completed',
                    'reason' => 'Routine Checkup',
                    'appointment_type' => 'routine_checkup',
                    'created_by' => $admin->user_id
                ]
            );

            Consultation::firstOrCreate(
                ['appointment_id' => $apt->appointment_id],
                [
                    'patient_id' => $patient->patient_id,
                    'doctor_id' => $doctor->staff_id,
                    'consultation_date' => $apt->appointment_date . ' 10:30:00',
                    'consultation_status' => 'closed', // Fixed: closed instead of completed
                    'chief_complaint' => 'Patient complaining of persistent cough.',
                    'diagnosis' => 'Upper Respiratory Track Infection',
                    'treatment_plan' => 'Prescribed Amoxicillin and Cough Syrup.',
                    'created_by' => $admin->user_id
                ]
            );
        }

        // 6. Notifications
        $currentUser = User::where('username', 'dr.wilson')->first() ?: User::find(2) ?: User::first();
        if ($currentUser) {
            for ($i = 1; $i <= 5; $i++) {
                DB::table('notifications')->insert([
                    'id' => Str::uuid(),
                    'type' => 'App\Notifications\GeneralNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $currentUser->user_id,
                    'data' => json_encode([
                        'title' => 'System Update #' . $i . ' (' . Str::random(3) . ')',
                        'message' => 'The hospital management system has been updated to v2.3.',
                        'icon' => 'fa-info-circle'
                    ]),
                    'read_at' => null,
                    'created_at' => now()->subHours($i),
                    'updated_at' => now()->subHours($i),
                ]);
            }
        }

        // 7. Messages
        $otherUser = User::where('user_id', '!=', $currentUser->user_id)->first();
        if ($currentUser && $otherUser) {
            for ($i = 1; $i <= 3; $i++) {
                Message::create([
                    'sender_id' => $otherUser->user_id,
                    'receiver_id' => $currentUser->user_id,
                    'content' => "Test message #$i (" . Str::random(3) . ") regarding patient follow-up.",
                    'read_at' => null,
                ]);
            }
        }
    }
}
