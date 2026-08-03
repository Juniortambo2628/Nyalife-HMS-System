<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Consultation;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medication;
use App\Models\Prescription;
use App\Models\LabTestType;
use App\Models\LabTestRequest;
use App\Models\LabSample;
use App\Models\Vital;
use App\Models\Insurance;
use App\Models\FollowUp;
use App\Models\Role;
use App\Models\MedicalProcedure;
use App\Models\RadiologyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $doctorUser;
    protected Patient $patient;
    protected Staff $doctor;
    protected Department $department;
    protected Medication $medication;
    protected LabTestType $labTestType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();

        $adminRoleId = Role::where('role_name', 'admin')->first()->role_id;
        $doctorRoleId = Role::where('role_name', 'doctor')->first()->role_id;
        $patientRoleId = Role::where('role_name', 'patient')->first()->role_id;

        $this->adminUser = User::factory()->create(['role_id' => $adminRoleId]);
        $this->adminUser->assignRole('admin');

        $this->doctorUser = User::factory()->create(['role_id' => $doctorRoleId]);
        $this->doctorUser->assignRole('doctor');

        $this->department = Department::factory()->create();
        $this->doctor = Staff::factory()->create([
            'user_id' => $this->doctorUser->user_id,
            'department_id' => $this->department->department_id,
        ]);

        $patientUser = User::factory()->create([
            'role_id' => $patientRoleId,
            'gender' => 'female',
        ]);
        $patientUser->assignRole('patient');
        $this->patient = Patient::factory()->create(['user_id' => $patientUser->user_id]);

        $this->medication = Medication::factory()->create();
        $this->labTestType = LabTestType::factory()->create();
    }

    protected function seedRoles(): void
    {
        foreach (['admin', 'doctor', 'nurse', 'receptionist', 'lab_technician', 'pharmacist', 'patient'] as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }
        $this->seed(\Database\Seeders\SyncSpatieRolesSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionsSeeder::class);
    }

    // =========================================================================
    // PATIENT FORMS
    // =========================================================================

    public function test_patient_store_with_valid_data(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('patients.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'phone' => '+254700000001',
                'date_of_birth' => '1990-01-15',
                'gender' => 'female',
                'address' => '123 Nairobi St',
                'blood_group' => 'O+',
                'height' => 165,
                'weight' => 62.5,
                'allergies' => 'None',
                'chronic_diseases' => 'None',
                'marital_status' => 'single',
                'occupation' => 'Engineer',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['first_name' => 'Jane', 'last_name' => 'Doe']);
        $this->assertDatabaseHas('patients', ['gender' => 'female', 'blood_group' => 'O+']);
    }

    public function test_patient_store_with_long_strings(): void
    {
        $longText = str_repeat('A', 1000);

        $response = $this->actingAs($this->adminUser)
            ->post(route('patients.store'), [
                'first_name' => 'LongName',
                'last_name' => 'TestUser',
                'phone' => '+254700000002',
                'date_of_birth' => '1985-06-20',
                'gender' => 'male',
                'allergies' => $longText,
                'chronic_diseases' => $longText,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['first_name' => 'LongName', 'last_name' => 'TestUser']);
    }

    public function test_patient_store_with_special_characters(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('patients.store'), [
                'first_name' => "O'Brien",
                'last_name' => "Mwangi-Kamau",
                'phone' => '+254 712 345 678',
                'date_of_birth' => '1995-03-10',
                'gender' => 'other',
                'address' => "Kenyatta Ave, Block C #42, Apt. 3B (Nairobi)",
                'allergies' => 'Peanuts, Penicillin',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['first_name' => "O'Brien"]);
    }

    public function test_patient_update(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('patients.update', $this->patient->patient_id), [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'phone' => '+254700000003',
                'date_of_birth' => '1990-01-15',
                'gender' => 'female',
                'allergies' => 'Updated allergy data',
                'weight' => 70.5,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('patients', ['patient_id' => $this->patient->patient_id, 'weight' => 70.5]);
    }

    // =========================================================================
    // APPOINTMENT FORMS
    // =========================================================================

    public function test_appointment_store(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('appointments.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'appointment_date' => now()->addDays(1)->format('Y-m-d'),
                'appointment_time' => '10:00',
                'appointment_type' => 'general',
                'reason' => 'Annual checkup',
                'notes' => 'Patient prefers morning slot',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
            'status' => 'scheduled',
        ]);
    }

    public function test_appointment_store_with_all_types(): void
    {
        $countBefore = \DB::table('appointments')->count();
        foreach (['general', 'follow_up', 'telehealth', 'emergency'] as $type) {
            $response = $this->actingAs($this->doctorUser)
                ->post(route('appointments.store'), [
                    'patient_id' => $this->patient->patient_id,
                    'doctor_id' => $this->doctor->staff_id,
                    'appointment_date' => now()->addDays(2)->format('Y-m-d'),
                    'appointment_time' => '14:00',
                    'appointment_type' => $type,
                    'reason' => "Test appointment type: $type",
                ]);

            $response->assertRedirect();
        }

        $this->assertDatabaseCount('appointments', $countBefore + 4);
    }

    // =========================================================================
    // CONSULTATION FORMS
    // =========================================================================

    public function test_consultation_store_in_progress(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'status' => 'in_progress',
                'chief_complaint' => 'Persistent headache for 3 days',
                'parity' => '2+1',
                'diagnosis' => 'Tension-type headache',
                'treatment_plan' => 'Rest and analgesics',
                'notes' => 'Follow up in 1 week',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('consultations', [
            'patient_id' => $this->patient->patient_id,
            'consultation_status' => 'in_progress',
            'parity' => '2+1',
        ]);
    }

    public function test_consultation_store_completed(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'status' => 'completed',
                'chief_complaint' => 'Fever and cough',
                'diagnosis' => 'Upper respiratory tract infection',
                'treatment_plan' => 'Antibiotics and rest',
            ]);

        $response->assertRedirect();
    }

    public function test_consultation_store_with_long_text_fields(): void
    {
        $longText = str_repeat('This is a detailed medical history note. ', 50);

        $response = $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'status' => 'in_progress',
                'chief_complaint' => $longText,
                'history_present_illness' => $longText,
                'past_medical_history' => $longText,
                'family_history' => $longText,
                'social_history' => $longText,
                'obstetric_history' => $longText,
                'surgical_history' => $longText,
                'diagnosis' => $longText,
                'treatment_plan' => $longText,
                'notes' => $longText,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('consultations', [
            'patient_id' => $this->patient->patient_id,
        ]);
    }

    public function test_consultation_store_with_vital_signs(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'status' => 'in_progress',
                'chief_complaint' => 'Routine checkup',
                'vital_signs' => [
                    'temperature' => '36.8',
                    'blood_pressure' => '120/80',
                    'heart_rate' => '72',
                ],
                'diagnosis' => 'Normal',
            ]);

        $response->assertRedirect();
    }

    public function test_consultation_store_with_menstrual_history(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'status' => 'in_progress',
                'chief_complaint' => 'Menstrual irregularity',
                'menstrual_history' => [
                    'last_period_date' => '2026-07-01',
                    'regularity' => 'regular',
                    'flow_duration' => '5 days',
                    'dysmenorrhea' => 'mild',
                ],
                'parity' => '0+0',
                'diagnosis' => 'Dysmenorrhea',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('consultations', ['parity' => '0+0']);
    }

    public function test_consultation_store_various_parity_values(): void
    {
        foreach (['0+0', '1+0', '2+1', '3+2', '4+3+1', '10+5+2'] as $parity) {
            $response = $this->actingAs($this->doctorUser)
                ->post(route('consultations.store'), [
                    'patient_id' => $this->patient->patient_id,
                    'doctor_id' => $this->doctor->staff_id,
                    'consultation_date' => now()->format('Y-m-d H:i:s'),
                    'status' => 'in_progress',
                    'chief_complaint' => "Test parity: $parity",
                    'parity' => $parity,
                    'diagnosis' => 'Test',
                ]);

            $response->assertRedirect();
        }
    }

    public function test_consultation_store_walk_in(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'status' => 'in_progress',
                'is_walk_in' => true,
                'chief_complaint' => 'Walk-in patient',
                'diagnosis' => 'Common cold',
            ]);

        $response->assertRedirect();
    }

    public function test_consultation_store_with_lab_requests(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'status' => 'in_progress',
                'chief_complaint' => 'Lab tests needed',
                'requested_labs' => [$this->labTestType->test_type_id],
                'diagnosis' => 'Pending lab results',
            ]);

        $response->assertRedirect();
    }

    public function test_consultation_all_fields_max_data(): void
    {
        $longText = str_repeat('Detailed medical narrative. ', 50);

        $response = $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'status' => 'completed',
                'priority' => 'emergency',
                'is_walk_in' => false,
                'chief_complaint' => 'Severe abdominal pain with vomiting',
                'history_present_illness' => $longText,
                'past_medical_history' => $longText,
                'family_history' => $longText,
                'social_history' => $longText,
                'obstetric_history' => $longText,
                'gynecological_history' => $longText,
                'cervical_screening' => 'Normal - Last Pap 2025',
                'contraceptive_history' => 'Oral contraceptive pills',
                'sexual_history' => 'Monogamous relationship',
                'review_of_systems' => $longText,
                'general_examination' => 'Patient in acute pain, BP 140/90, HR 110',
                'systems_examination' => 'Abdomen: tender in RLQ, guarding positive',
                'diagnosis' => 'Acute appendicitis',
                'diagnosis_confidence' => 'high',
                'differential_diagnosis' => 'Ovarian cyst torsion; Renal colic',
                'diagnostic_plan' => 'CT abdomen, CBC, urinalysis',
                'treatment_plan' => 'Surgical consultation for appendectomy',
                'follow_up_instructions' => 'NPO, IV fluids, monitor vitals',
                'notes' => 'Patient consented for surgery',
                'clinical_summary' => $longText,
                'parity' => '3+2',
                'current_pregnancy' => 'N/A',
                'past_obstetric' => [
                    ['year' => '2020', 'place_of_birth' => 'Nairobi Hospital', 'duration' => '5 hours', 'mode_of_delivery' => 'normal', 'outcome' => 'healthy', 'sex' => 'male', 'weight' => '3.2kg', 'complications' => 'none'],
                    ['year' => '2023', 'place_of_birth' => 'Kenyatta Hospital', 'duration' => '8 hours', 'mode_of_delivery' => 'cesarean', 'outcome' => 'healthy', 'sex' => 'female', 'weight' => '3.5kg', 'complications' => 'none'],
                ],
                'surgical_history' => $longText,
                'vital_signs' => [
                    'temperature' => '37.8',
                    'blood_pressure' => '140/90',
                    'heart_rate' => '110',
                    'respiratory_rate' => '20',
                    'oxygen_saturation' => '97',
                ],
                'menstrual_history' => [
                    'last_period_date' => '2026-06-15',
                    'regularity' => 'regular',
                    'flow_duration' => '5 days',
                    'dysmenorrhea' => 'mild',
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('consultations', [
            'patient_id' => $this->patient->patient_id,
            'consultation_status' => 'completed',
            'parity' => '3+2',
        ]);
    }

    // =========================================================================
    // INVOICE FORMS
    // =========================================================================

    public function test_invoice_store(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('invoices.store'), [
                'patient_id' => $this->patient->patient_id,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'items' => [
                    ['description' => 'Consultation fee', 'quantity' => 1, 'unit_price' => 2000],
                    ['description' => 'Lab test - CBC', 'quantity' => 1, 'unit_price' => 1500],
                ],
                'notes' => 'Payment due within 30 days',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $this->patient->patient_id,
            'total_amount' => 3500,
        ]);
    }

    public function test_invoice_store_with_many_items(): void
    {
        $items = [];
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'description' => "Service item #$i",
                'quantity' => 1,
                'unit_price' => 1000,
            ];
        }

        $response = $this->actingAs($this->adminUser)
            ->post(route('invoices.store'), [
                'patient_id' => $this->patient->patient_id,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'items' => $items,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $this->patient->patient_id,
        ]);
    }

    // =========================================================================
    // PRESCRIPTION FORMS
    // =========================================================================

    public function test_prescription_store(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('prescriptions.store'), [
                'patient_id' => $this->patient->patient_id,
                'prescription_date' => now()->format('Y-m-d'),
                'items' => [
                    [
                        'medicine_name' => 'Paracetamol',
                        'dosage' => '500mg',
                        'frequency' => 'TDS',
                        'duration' => '7 days',
                    ],
                ],
                'notes' => 'Take after meals',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $this->patient->patient_id,
        ]);
    }

    public function test_prescription_store_with_multiple_items(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('prescriptions.store'), [
                'patient_id' => $this->patient->patient_id,
                'prescription_date' => now()->format('Y-m-d'),
                'items' => [
                    ['medicine_name' => 'Amoxicillin', 'dosage' => '500mg', 'frequency' => 'TDS', 'duration' => '7 days'],
                    ['medicine_name' => 'Metronidazole', 'dosage' => '400mg', 'frequency' => 'BD', 'duration' => '5 days'],
                    ['medicine_name' => 'Omeprazole', 'dosage' => '20mg', 'frequency' => 'OD', 'duration' => '14 days'],
                ],
                'notes' => 'Complete the full course',
            ]);

        $response->assertRedirect();
    }

    // =========================================================================
    // PAYMENT FORMS
    // =========================================================================

    public function test_payment_store(): void
    {
        $invoice = Invoice::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'total_amount' => 5000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->invoice_id,
                'amount' => 3000,
                'payment_method' => 'cash',
                'payment_date' => now()->format('Y-m-d'),
                'transaction_reference' => 'CASH-001',
                'notes' => 'Partial payment',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->invoice_id,
            'amount' => 3000,
        ]);
    }

    // =========================================================================
    // VITAL SIGNS
    // =========================================================================

    public function test_vital_store(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('vitals.store'), [
                'patient_id' => $this->patient->patient_id,
                'temperature' => 36.8,
                'blood_pressure_systolic' => 120,
                'blood_pressure_diastolic' => 80,
                'heart_rate' => 72,
                'respiratory_rate' => 16,
                'oxygen_saturation' => 98,
                'weight' => 68,
                'height' => 170,
                'notes' => 'Within normal range',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vital_signs', [
            'patient_id' => $this->patient->patient_id,
        ]);
    }

    // =========================================================================
    // LAB REQUESTS
    // =========================================================================

    public function test_lab_request_store(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('lab.store'), [
                'patient_id' => $this->patient->patient_id,
                'test_type_id' => $this->labTestType->test_type_id,
                'priority' => 'routine',
                'notes' => 'Fasting required',
            ]);

        $response->assertRedirect();
    }

    // =========================================================================
    // USER/STAFF FORMS
    // =========================================================================

    public function test_user_store(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('users.store'), [
                'first_name' => 'New',
                'last_name' => 'Doctor',
                'email' => 'newdoctor@test.com',
                'phone' => '+254700000099',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'doctor',
                'department_id' => $this->department->department_id,
                'gender' => 'male',
                'date_of_birth' => '1985-04-15',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'newdoctor@test.com']);
    }

    // =========================================================================
    // DEPARTMENT
    // =========================================================================

    public function test_department_store(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('departments.store'), [
                'department_name' => 'Radiology',
                'description' => 'Diagnostic imaging department',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', ['department_name' => 'Radiology']);
    }

    // =========================================================================
    // MESSAGES
    // =========================================================================

    public function test_message_store(): void
    {
        $nurseRoleId = Role::where('role_name', 'nurse')->first()->role_id;
        $receiver = User::factory()->create(['role_id' => $nurseRoleId]);

        $response = $this->actingAs($this->doctorUser)
            ->post(route('messages.store'), [
                'receiver_id' => $receiver->user_id,
                'content' => 'Hello, this is a test message.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->doctorUser->user_id,
            'receiver_id' => $receiver->user_id,
        ]);
    }

    public function test_message_store_with_long_content(): void
    {
        $nurseRoleId = Role::where('role_name', 'nurse')->first()->role_id;
        $receiver = User::factory()->create(['role_id' => $nurseRoleId]);

        $response = $this->actingAs($this->doctorUser)
            ->post(route('messages.store'), [
                'receiver_id' => $receiver->user_id,
                'content' => str_repeat('This is a detailed message about a patient case. ', 100),
            ]);

        $response->assertRedirect();
    }

    public function test_message_store_special_chars(): void
    {
        $nurseRoleId = Role::where('role_name', 'nurse')->first()->role_id;
        $receiver = User::factory()->create(['role_id' => $nurseRoleId]);

        $response = $this->actingAs($this->doctorUser)
            ->post(route('messages.store'), [
                'receiver_id' => $receiver->user_id,
                'content' => "Patient O'Brien needs \"urgent\" review: BP 120/80, HR <60.",
            ]);

        $response->assertRedirect();
    }

    // =========================================================================
    // DOCTOR BLOCK-OUT
    // =========================================================================

    public function test_doctor_block_out_store(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('doctor-block-outs.store'), [
                'doctor_id' => $this->doctor->staff_id,
                'block_date' => now()->addDays(5)->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '17:00',
                'reason' => 'Conference attendance',
            ]);

        $response->assertRedirect();
    }

    // =========================================================================
    // FOLLOW-UP
    // =========================================================================

    public function test_follow_up_store(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('follow-ups.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'follow_up_date' => now()->addDays(7)->format('Y-m-d'),
                'reason' => 'Post-treatment review',
                'notes' => 'Check blood pressure',
                'status' => 'pending',
            ]);

        $response->assertRedirect();
    }

    // =========================================================================
    // RADIOLOGY
    // =========================================================================

    public function test_radiology_store(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('radiology.store'), [
                'patient_id' => $this->patient->patient_id,
                'request_type' => 'X-Ray',
                'body_part' => 'Chest PA',
                'clinical_history' => 'Persistent cough for 2 weeks',
                'priority' => 'routine',
            ]);

        $response->assertRedirect();
    }

    // =========================================================================
    // INSURANCE
    // =========================================================================

    public function test_insurance_store_validation(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('insurances.store'), [
                'name' => 'NHIF',
                'is_active' => true,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['logo']);
    }

    // =========================================================================
    // MEDICAL PROCEDURES
    // =========================================================================

    public function test_medical_procedure_store(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('medical-procedures.store'), [
                'name' => 'Appendectomy',
                'description' => 'Surgical removal of the appendix',
                'category' => 'Surgery',
                'estimated_duration' => 90,
                'base_cost' => 85000,
                'is_active' => true,
            ]);

        $response->assertRedirect();
    }

    // =========================================================================
    // LAB TEST TYPES
    // =========================================================================

    public function test_lab_test_type_store(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('lab-tests.store'), [
                'test_name' => 'Full Blood Count',
                'description' => 'Complete blood count with differential',
                'category' => 'Haematology',
                'price' => 1500,
                'normal_range' => 'WBC 4.0-11.0, RBC 4.5-5.5',
                'units' => 'x10^9/L',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lab_test_types', ['test_name' => 'Full Blood Count']);
    }

    // =========================================================================
    // CONSULTATION UPDATE (parity fix was the original issue)
    // =========================================================================

    public function test_consultation_update_with_parity(): void
    {
        $consultation = Consultation::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
            'created_by' => $this->doctorUser->user_id,
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->put(route('consultations.update', $consultation->consultation_id), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => $consultation->consultation_date,
                'status' => 'completed',
                'chief_complaint' => 'Updated complaint',
                'parity' => '5+3',
                'diagnosis' => 'Updated diagnosis',
                'treatment_plan' => 'Updated treatment plan',
                'follow_up_instructions' => 'Updated follow-up',
                'notes' => 'Updated notes',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('consultations', [
            'consultation_id' => $consultation->consultation_id,
            'parity' => '5+3',
        ]);
    }

    // =========================================================================
    // VALIDATION (ensure 422 on bad data, NOT 500)
    // =========================================================================

    public function test_patient_store_validation_missing_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('patients.store'), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['first_name', 'last_name', 'phone', 'date_of_birth', 'gender']);
    }

    public function test_appointment_store_validation_missing_fields(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('appointments.store'), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['patient_id', 'doctor_id', 'appointment_date', 'appointment_time']);
    }

    public function test_invoice_store_validation_no_items(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('invoices.store'), [
                'patient_id' => $this->patient->patient_id,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'items' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['items']);
    }

    public function test_prescription_store_validation_no_items(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('prescriptions.store'), [
                'patient_id' => $this->patient->patient_id,
                'prescription_date' => now()->format('Y-m-d'),
                'items' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['items']);
    }

    public function test_null_vs_empty_string_handling(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'consultation_date' => now()->format('Y-m-d H:i:s'),
                'status' => 'in_progress',
                'chief_complaint' => 'Test',
                'family_history' => null,
                'social_history' => '',
                'obstetric_history' => null,
                'parity' => null,
                'past_obstetric' => null,
                'diagnosis' => 'Test diagnosis',
            ]);

        $response->assertRedirect();
    }
}
