<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Medication;
use App\Models\MedicationBatch;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vital;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoEnvironmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Nyalife demo environment seed ===');

        $this->call([
            AdminUserSeeder::class,
            TestUsersSeeder::class,
            SyncSpatieRolesSeeder::class,
            RolePermissionsSeeder::class,
            CMSSettingsSeeder::class,
            ServiceTabSeeder::class,
            BlogSeeder::class,
            MailTemplateSeeder::class,
        ]);

        $admin = User::where('username', 'admin')->first();
        if (! $admin) {
            $this->command->error('Admin user missing after foundation seed.');

            return;
        }

        $departments = $this->seedDepartments();
        $staff = $this->seedStaffProfiles($departments);
        $this->seedCatalog();
        $patients = $this->seedPatients();
        $this->seedClinicalData($admin, $staff, $patients);

        $this->call(SyncSpatieRolesSeeder::class);

        $this->command->newLine();
        $this->command->info('Demo seed complete.');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Patients', Patient::count()],
                ['Appointments', Appointment::count()],
                ['Consultations', Consultation::count()],
                ['Lab requests', LabTestRequest::count()],
                ['Prescriptions', Prescription::count()],
                ['Invoices', Invoice::count()],
                ['Payments', Payment::count()],
                ['Follow-ups', FollowUp::count()],
                ['Vitals', Vital::count()],
            ]
        );
        $this->command->newLine();
        $this->command->info('Login with email or username (password: password):');
        $this->command->table(
            ['Email', 'Username', 'Role'],
            [
                ['admin@nyalife.com', 'admin', 'admin'],
                ['doctor@nyalife.com', 'doctor', 'doctor'],
                ['nurse@nyalife.com', 'nurse', 'nurse'],
                ['labtech@nyalife.com', 'labtech', 'lab technician'],
                ['pharmacist@nyalife.com', 'pharmacist', 'pharmacist'],
                ['receptionist@nyalife.com', 'receptionist', 'receptionist'],
                ['patient@nyalife.com', 'patient', 'patient portal'],
            ]
        );
    }

    private function seedDepartments(): array
    {
        $defs = [
            ['department_name' => 'Obstetrics & Gynecology', 'code' => 'OBGYN', 'type' => 'clinical', 'description' => 'Antenatal, gynecology, and women\'s health services.'],
            ['department_name' => 'Laboratory', 'code' => 'LAB', 'type' => 'support', 'description' => 'Diagnostic and pathology services.'],
            ['department_name' => 'Pharmacy', 'code' => 'PHARM', 'type' => 'support', 'description' => 'Outpatient dispensing and inventory.'],
            ['department_name' => 'Front Desk', 'code' => 'RECEP', 'type' => 'administrative', 'description' => 'Appointments, billing, and patient intake.'],
        ];

        $departments = [];
        foreach ($defs as $def) {
            $departments[$def['code']] = Department::updateOrCreate(
                ['code' => $def['code']],
                $def + ['is_active' => true]
            );
        }

        return $departments;
    }

    private function seedStaffProfiles(array $departments): array
    {
        $map = [
            'doctor' => [
                'specialization' => 'Obstetrician & Gynecologist',
                'department' => 'Obstetrics & Gynecology',
                'department_id' => $departments['OBGYN']->department_id,
                'position' => 'Consultant',
                'license_number' => 'KMPDC-DR-1024',
            ],
            'nurse' => [
                'specialization' => 'Registered Nurse',
                'department' => 'Obstetrics & Gynecology',
                'department_id' => $departments['OBGYN']->department_id,
                'position' => 'Senior Nurse',
                'license_number' => 'NCK-RN-5581',
            ],
            'labtech' => [
                'specialization' => 'Medical Laboratory Technologist',
                'department' => 'Laboratory',
                'department_id' => $departments['LAB']->department_id,
                'position' => 'Lab Technologist',
                'license_number' => 'KMLTTB-3390',
            ],
            'pharmacist' => [
                'specialization' => 'Clinical Pharmacist',
                'department' => 'Pharmacy',
                'department_id' => $departments['PHARM']->department_id,
                'position' => 'Pharmacist',
                'license_number' => 'PPB-PH-7712',
            ],
            'receptionist' => [
                'specialization' => 'Patient Services',
                'department' => 'Front Desk',
                'department_id' => $departments['RECEP']->department_id,
                'position' => 'Receptionist',
            ],
        ];

        $staff = [];
        foreach ($map as $username => $profile) {
            $user = User::where('username', $username)->first();
            if (! $user) {
                continue;
            }

            $staff[$username] = Staff::updateOrCreate(
                ['user_id' => $user->user_id],
                $profile + [
                    'employee_id' => strtoupper($username) . '-001',
                    'join_date' => now()->subYears(2)->toDateString(),
                ]
            );
        }

        return $staff;
    }

    private function seedCatalog(): void
    {
        $tests = [
            ['test_name' => 'Full Blood Count', 'category' => 'Hematology', 'price' => 1500, 'normal_range' => 'See report', 'units' => 'various'],
            ['test_name' => 'Obstetric Ultrasound', 'category' => 'Imaging', 'price' => 3500, 'normal_range' => 'N/A', 'units' => 'N/A'],
            ['test_name' => 'Urinalysis', 'category' => 'Urinalysis', 'price' => 600, 'normal_range' => 'Normal', 'units' => 'N/A'],
            ['test_name' => 'Blood Group & Rhesus', 'category' => 'Immunology', 'price' => 800, 'normal_range' => 'Patient specific', 'units' => 'N/A'],
            ['test_name' => 'HIV Rapid Test', 'category' => 'Serology', 'price' => 500, 'normal_range' => 'Non-reactive', 'units' => 'N/A'],
            ['test_name' => 'Random Blood Sugar', 'category' => 'Biochemistry', 'price' => 400, 'normal_range' => '3.9–5.5 mmol/L', 'units' => 'mmol/L'],
            ['test_name' => 'Pregnancy Test (hCG)', 'category' => 'Serology', 'price' => 350, 'normal_range' => 'Negative/Positive', 'units' => 'N/A'],
        ];

        foreach ($tests as $test) {
            LabTestType::updateOrCreate(
                ['test_name' => $test['test_name']],
                $test + ['is_active' => true, 'description' => $test['test_name'] . ' — standard clinic panel.']
            );
        }

        $meds = [
            ['medication_name' => 'Folic Acid', 'medication_type' => 'Tablet', 'strength' => '5mg', 'unit' => 'tablets', 'stock_quantity' => 500, 'price_per_unit' => 5],
            ['medication_name' => 'Paracetamol', 'medication_type' => 'Tablet', 'strength' => '500mg', 'unit' => 'tablets', 'stock_quantity' => 800, 'price_per_unit' => 3],
            ['medication_name' => 'Amoxicillin', 'medication_type' => 'Capsule', 'strength' => '500mg', 'unit' => 'capsules', 'stock_quantity' => 400, 'price_per_unit' => 12],
            ['medication_name' => 'Iron Supplements', 'medication_type' => 'Tablet', 'strength' => '200mg', 'unit' => 'tablets', 'stock_quantity' => 350, 'price_per_unit' => 8],
            ['medication_name' => 'Prenatal Vitamins', 'medication_type' => 'Tablet', 'strength' => 'Combo', 'unit' => 'tablets', 'stock_quantity' => 300, 'price_per_unit' => 15],
        ];

        foreach ($meds as $med) {
            $medication = Medication::updateOrCreate(
                ['medication_name' => $med['medication_name']],
                $med + ['description' => 'Clinic formulary item.']
            );

            MedicationBatch::updateOrCreate(
                ['medication_id' => $medication->medication_id, 'batch_number' => 'DEMO-' . strtoupper(Str::slug($med['medication_name'], ''))],
                [
                    'quantity' => $med['stock_quantity'],
                    'expiry_date' => now()->addMonths(18)->toDateString(),
                    'manufacturing_date' => now()->subMonths(3)->toDateString(),
                ]
            );
        }
    }

    private function seedPatients(): array
    {
        $patientRoleId = \DB::table('roles')->where('role_name', 'patient')->value('role_id');

        $defs = [
            ['username' => 'patient', 'first_name' => 'Wanjiru', 'last_name' => 'Kamau', 'email' => 'patient@nyalife.com', 'phone' => '0712345678', 'gender' => 'female', 'dob' => '1994-03-12', 'blood_group' => 'O+'],
            ['username' => 'grace.wanjiku', 'first_name' => 'Grace', 'last_name' => 'Wanjiku', 'email' => 'grace.wanjiku@demo.nyalife.com', 'phone' => '0722111222', 'gender' => 'female', 'dob' => '1990-07-21', 'blood_group' => 'A+'],
            ['username' => 'faith.akinyi', 'first_name' => 'Faith', 'last_name' => 'Akinyi', 'email' => 'faith.akinyi@demo.nyalife.com', 'phone' => '0733222333', 'gender' => 'female', 'dob' => '1988-11-05', 'blood_group' => 'B+'],
            ['username' => 'mercy.njeri', 'first_name' => 'Mercy', 'last_name' => 'Njeri', 'email' => 'mercy.njeri@demo.nyalife.com', 'phone' => '0744333444', 'gender' => 'female', 'dob' => '1996-01-18', 'blood_group' => 'AB+'],
            ['username' => 'linda.chemutai', 'first_name' => 'Linda', 'last_name' => 'Chemutai', 'email' => 'linda.chemutai@demo.nyalife.com', 'phone' => '0755444555', 'gender' => 'female', 'dob' => '1992-09-30', 'blood_group' => 'O-'],
            ['username' => 'jane.smith', 'first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane.smith@nyalife.com', 'phone' => '0722345678', 'gender' => 'female', 'dob' => '1985-11-20', 'blood_group' => 'A-'],
            ['username' => 'alice.brown', 'first_name' => 'Alice', 'last_name' => 'Brown', 'email' => 'alice.brown@nyalife.com', 'phone' => '0732345678', 'gender' => 'female', 'dob' => '1995-02-10', 'blood_group' => 'B-'],
            ['username' => 'john.doe', 'first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john.doe@nyalife.com', 'phone' => '0712345670', 'gender' => 'male', 'dob' => '1990-05-15', 'blood_group' => 'O+'],
        ];

        $patients = [];
        foreach ($defs as $def) {
            $user = User::updateOrCreate(
                ['username' => $def['username']],
                [
                    'first_name' => $def['first_name'],
                    'last_name' => $def['last_name'],
                    'email' => $def['email'],
                    'phone' => $def['phone'],
                    'gender' => $def['gender'],
                    'date_of_birth' => $def['dob'],
                    'password' => Hash::make('password'),
                    'role_id' => $patientRoleId,
                    'is_active' => true,
                ]
            );

            $patients[] = Patient::updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'patient_number' => Patient::generateNumber($user->user_id),
                    'gender' => $def['gender'],
                    'date_of_birth' => $def['dob'],
                    'blood_group' => $def['blood_group'],
                    'address' => 'Athi River, Machakos County',
                    'marital_status' => 'married',
                    'occupation' => 'Professional',
                    'emergency_name' => 'Emergency Contact',
                    'emergency_contact' => '0700000000',
                ]
            );
        }

        return $patients;
    }

    private function seedClinicalData(User $admin, array $staff, array $patients): void
    {
        $doctor = $staff['doctor'] ?? Staff::first();
        $labTechUser = User::where('username', 'labtech')->first();
        $nurseUser = User::where('username', 'nurse')->first();

        if (! $doctor) {
            $this->command->warn('No doctor staff profile — skipping clinical demo data.');

            return;
        }

        $testTypes = LabTestType::where('is_active', true)->get();
        $medications = Medication::all();

        $appointmentPlans = [
            ['offset' => 0, 'time' => '09:00', 'status' => 'confirmed', 'type' => 'consultation', 'reason' => 'Antenatal clinic visit — 28 weeks'],
            ['offset' => 0, 'time' => '10:30', 'status' => 'arrived', 'type' => 'consultation', 'reason' => 'Pelvic pain review'],
            ['offset' => 0, 'time' => '14:00', 'status' => 'scheduled', 'type' => 'followup', 'reason' => 'Postpartum follow-up'],
            ['offset' => 1, 'time' => '09:30', 'status' => 'scheduled', 'type' => 'consultation', 'reason' => 'Family planning counselling'],
            ['offset' => 2, 'time' => '11:00', 'status' => 'scheduled', 'type' => 'procedure', 'reason' => 'Cervical screening'],
            ['offset' => -1, 'time' => '10:00', 'status' => 'completed', 'type' => 'consultation', 'reason' => 'Routine ANC review'],
            ['offset' => -2, 'time' => '15:00', 'status' => 'completed', 'type' => 'consultation', 'reason' => 'Urinary tract symptoms'],
            ['offset' => -3, 'time' => '08:30', 'status' => 'completed', 'type' => 'consultation', 'reason' => 'First trimester scan booking'],
            ['offset' => -5, 'time' => '13:00', 'status' => 'cancelled', 'type' => 'consultation', 'reason' => 'Patient rescheduled'],
            ['offset' => -7, 'time' => '16:00', 'status' => 'no_show', 'type' => 'followup', 'reason' => 'Missed review appointment'],
        ];

        $consultations = [];
        $appointments = [];

        foreach ($appointmentPlans as $index => $plan) {
            $patient = $patients[$index % count($patients)];
            $date = now()->addDays($plan['offset'])->toDateString();

            $appointment = Appointment::updateOrCreate(
                [
                    'patient_id' => $patient->patient_id,
                    'appointment_date' => $date,
                    'appointment_time' => $plan['time'] . ':00',
                ],
                [
                    'doctor_id' => $doctor->staff_id,
                    'end_time' => Carbon::parse($plan['time'])->addMinutes(30)->format('H:i:s'),
                    'appointment_type' => $plan['type'],
                    'status' => $plan['status'],
                    'reason' => $plan['reason'],
                    'created_by' => $admin->user_id,
                ]
            );

            $appointments[] = $appointment;

            if ($plan['status'] === 'completed') {
                $consultations[] = Consultation::updateOrCreate(
                    ['appointment_id' => $appointment->appointment_id],
                    [
                        'patient_id' => $patient->patient_id,
                        'doctor_id' => $doctor->staff_id,
                        'consultation_date' => $date . ' ' . $plan['time'] . ':00',
                        'consultation_status' => 'closed',
                        'chief_complaint' => $plan['reason'],
                        'diagnosis' => $this->demoDiagnosis($index),
                        'treatment_plan' => 'Continue prescribed care, return if symptoms worsen.',
                        'follow_up_instructions' => 'Review in 2 weeks or as advised.',
                        'created_by' => $admin->user_id,
                    ]
                );
            }
        }

        $labPlans = [
            ['status' => 'pending', 'priority' => 'urgent'],
            ['status' => 'pending', 'priority' => 'normal'],
            ['status' => 'processing', 'priority' => 'normal'],
            ['status' => 'completed', 'priority' => 'normal'],
            ['status' => 'completed', 'priority' => 'urgent'],
            ['status' => 'pending_verification', 'priority' => 'normal'],
        ];

        foreach ($labPlans as $i => $plan) {
            $patient = $patients[$i % count($patients)];
            $consultation = $consultations[$i % max(count($consultations), 1)] ?? null;
            $testType = $testTypes[$i % max($testTypes->count(), 1)];

            LabTestRequest::updateOrCreate(
                ['request_number' => 'LAB-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'patient_id' => $patient->patient_id,
                    'doctor_id' => $doctor->staff_id,
                    'test_type_id' => $testType?->test_type_id,
                    'priority' => $plan['priority'],
                    'requested_by' => $doctor->user_id,
                    'status' => $plan['status'],
                    'request_date' => now()->subDays($i)->setTime(9, 0),
                    'completed_at' => in_array($plan['status'], ['completed', 'pending_verification'], true) ? now()->subDays($i - 1)->setTime(15, 0) : null,
                    'consultation_id' => $consultation?->consultation_id,
                    'appointment_id' => $appointments[$i % count($appointments)]->appointment_id ?? null,
                    'assigned_to' => $labTechUser?->user_id,
                    'notes' => 'Demo lab request for UI testing.',
                    'results' => $plan['status'] === 'completed' ? ['summary' => 'Within expected range.'] : null,
                ]
            );
        }

        foreach (array_slice($consultations, 0, 4) as $i => $consultation) {
            $med = $medications[$i % max($medications->count(), 1)];
            $appointment = Appointment::find($consultation->appointment_id);

            $prescription = Prescription::updateOrCreate(
                ['prescription_number' => 'RX-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'patient_id' => $consultation->patient_id,
                    'prescribed_by' => $doctor->user_id,
                    'appointment_id' => $consultation->appointment_id,
                    'consultation_id' => $consultation->consultation_id,
                    'prescription_date' => Carbon::parse($consultation->consultation_date)->toDateString(),
                    'status' => $i < 2 ? 'dispensed' : 'pending',
                    'dispensed_by' => $i < 2 ? User::where('username', 'pharmacist')->value('user_id') : null,
                    'dispensed_at' => $i < 2 ? now()->subDays(2) : null,
                    'notes' => 'Demo prescription.',
                ]
            );

            if ($med) {
                PrescriptionItem::updateOrCreate(
                    ['prescription_id' => $prescription->prescription_id, 'medication_id' => $med->medication_id],
                    [
                        'dosage' => '1 tablet',
                        'frequency' => 'Twice daily',
                        'quantity' => 14,
                        'duration' => '7 days',
                        'instructions' => 'Take after meals.',
                        'status' => $prescription->status,
                    ]
                );
            }

            $amount = 2500 + ($i * 750);
            $invoice = Invoice::updateOrCreate(
                ['invoice_number' => 'INV-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'patient_id' => $consultation->patient_id,
                    'consultation_id' => $consultation->consultation_id,
                    'doctor_id' => $doctor->staff_id,
                    'invoice_date' => Carbon::parse($consultation->consultation_date)->toDateString(),
                    'due_date' => Carbon::parse($consultation->consultation_date)->addDays(14)->toDateString(),
                    'total_amount' => $amount,
                    'tax' => round($amount * 0.16, 2),
                    'status' => $i % 2 === 0 ? 'paid' : 'pending',
                    'payment_method' => $i % 2 === 0 ? 'cash' : null,
                    'created_by' => $admin->user_id,
                ]
            );

            InvoiceItem::updateOrCreate(
                ['invoice_id' => $invoice->invoice_id, 'description' => 'Consultation & care package'],
                [
                    'item_type' => 'service',
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                ]
            );

            if ($invoice->status === 'paid') {
                Payment::updateOrCreate(
                    ['transaction_reference' => 'PAY-DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                    [
                        'invoice_id' => $invoice->invoice_id,
                        'amount' => $amount,
                        'payment_method' => 'cash',
                        'payment_date' => Carbon::parse($consultation->consultation_date)->addHours(2),
                        'payment_status' => 'completed',
                        'status' => 'completed',
                        'received_by' => User::where('username', 'receptionist')->value('user_id'),
                    ]
                );
            }
        }

        foreach (array_slice($patients, 0, 4) as $i => $patient) {
            $consultation = $consultations[$i % max(count($consultations), 1)] ?? null;

            FollowUp::updateOrCreate(
                [
                    'patient_id' => $patient->patient_id,
                    'follow_up_date' => now()->addDays(7 + $i)->toDateString(),
                ],
                [
                    'consultation_id' => $consultation?->consultation_id,
                    'follow_up_type' => ['general', 'lab_results', 'medication_review'][$i % 3],
                    'reason' => 'Review progress and lab outcomes.',
                    'status' => $i === 0 ? 'scheduled' : 'scheduled',
                    'notes' => 'Demo follow-up appointment.',
                    'created_by' => $doctor->user_id,
                ]
            );

            Vital::updateOrCreate(
                [
                    'patient_id' => $patient->patient_id,
                    'measured_at' => now()->subDays($i)->setTime(8, 30),
                ],
                [
                    'blood_pressure' => '118/76',
                    'heart_rate' => 78 + $i,
                    'respiratory_rate' => 18,
                    'temperature' => 36.6 + ($i * 0.1),
                    'weight' => 62 + $i,
                    'height' => 165,
                    'oxygen_saturation' => 98,
                    'priority' => 'normal',
                    'recorded_by' => $nurseUser?->user_id,
                    'notes' => 'Triage vitals — demo record.',
                ]
            );
        }
    }

    private function demoDiagnosis(int $index): string
    {
        $options = [
            'Normal intrauterine pregnancy — 28 weeks',
            'Uncomplicated urinary tract infection',
            'Iron deficiency anemia — mild',
            'Threatened preterm labor — ruled out',
            'Pelvic inflammatory disease — resolving',
        ];

        return $options[$index % count($options)];
    }
}
