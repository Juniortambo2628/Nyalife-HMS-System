<?php

namespace Tests\Feature\Workflows;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\MedicalProcedure;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vital;
use App\Services\PaymentService;
use Database\Seeders\RolePermissionsSeeder;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientClinicalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $doctorUser;

    protected User $nurseUser;

    protected User $receptionistUser;

    protected Staff $doctor;

    protected Department $department;

    protected MedicalProcedure $consultationProcedure;

    protected MedicalProcedure $additionalProcedure;

    protected LabTestType $labTestType;

    protected LabTestType $serviceItem;

    protected Medication $medication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();

        $adminRoleId = Role::where('role_name', 'admin')->first()->role_id;
        $doctorRoleId = Role::where('role_name', 'doctor')->first()->role_id;
        $nurseRoleId = Role::where('role_name', 'nurse')->first()->role_id;
        $receptionistRoleId = Role::where('role_name', 'receptionist')->first()->role_id;

        $this->adminUser = User::factory()->create(['role_id' => $adminRoleId]);
        $this->adminUser->assignRole('admin');

        $this->doctorUser = User::factory()->create(['role_id' => $doctorRoleId]);
        $this->doctorUser->assignRole('doctor');

        $this->nurseUser = User::factory()->create(['role_id' => $nurseRoleId]);
        $this->nurseUser->assignRole('nurse');

        $this->receptionistUser = User::factory()->create(['role_id' => $receptionistRoleId]);
        $this->receptionistUser->assignRole('receptionist');

        $this->department = Department::factory()->create();

        $this->doctor = Staff::factory()->create([
            'user_id' => $this->doctorUser->user_id,
            'department_id' => $this->department->department_id,
        ]);

        // A consultation medical procedure is required for auto-generated invoices.
        $this->consultationProcedure = MedicalProcedure::factory()->create([
            'name' => 'General Consultation',
            'category' => 'consultation',
            'standard_fee' => 3000,
            'is_active' => true,
        ]);

        // An additional procedure requested separately from the consultation fee.
        $this->additionalProcedure = MedicalProcedure::factory()->create([
            'name' => 'Wound Dressing',
            'category' => 'procedure',
            'standard_fee' => 1200,
            'is_active' => true,
        ]);

        // A lab test type (lab category) for requested_labs.
        $this->labTestType = LabTestType::factory()->create([
            'test_name' => 'Complete Blood Count',
            'category' => 'Hematology',
            'price' => 1500,
            'is_active' => true,
        ]);

        // A service item (service category) for requested_service_items.
        $this->serviceItem = LabTestType::factory()->create([
            'test_name' => 'Ultrasound Scan',
            'category' => 'Imaging',
            'price' => 3500,
            'is_active' => true,
        ]);

        $this->medication = Medication::factory()->create([
            'price_per_unit' => 250,
        ]);
    }

    protected function seedRoles(): void
    {
        foreach (['admin', 'doctor', 'nurse', 'receptionist', 'lab_technician', 'pharmacist', 'patient'] as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }

        $this->seed(SyncSpatieRolesSeeder::class);
        $this->seed(RolePermissionsSeeder::class);
    }

    /**
     * Complete patient clinical workflow:
     * 1. Patient registration
     * 2. Appointment booking
     * 3. Patient check-in
     * 4. Vitals recording
     * 5. Doctor consultation creation (with labs/services)
     * 6. Invoice generation from consultation
     * 7. Payment recording
     */
    public function test_full_patient_clinical_workflow(): void
    {
        // =====================================================================
        // 1. Patient registration (PatientController::store)
        // =====================================================================
        $registrationPayload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe.workflow@example.com',
            'phone' => '+254711222333',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'address' => '123 Test Lane, Nairobi',
            'blood_group' => 'O+',
            'height' => 165.00,
            'weight' => 68.00,
            'allergies' => 'Penicillin',
            'chronic_diseases' => 'Hypertension',
            'marital_status' => 'married',
            'occupation' => 'Teacher',
            'emergency_name' => 'John Doe',
            'emergency_contact' => '+254722333444',
        ];

        $this->actingAs($this->receptionistUser)
            ->post(route('patients.store'), $registrationPayload)
            ->assertRedirect(route('patients.index'));

        $patient = Patient::with('user')
            ->whereHas('user', fn ($q) => $q->where('email', 'jane.doe.workflow@example.com'))
            ->first();

        $this->assertNotNull($patient, 'Patient was not registered.');
        $this->assertEquals('Jane', $patient->user->first_name);
        $this->assertEquals('female', $patient->gender);
        $this->assertNotNull($patient->patient_number);

        // =====================================================================
        // 2. Appointment booking (AppointmentController::store)
        // =====================================================================
        $appointmentDate = now()->addDay()->format('Y-m-d');

        $this->actingAs($this->receptionistUser)
            ->post(route('appointments.store'), [
                'patient_id' => $patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'appointment_date' => $appointmentDate,
                'appointment_time' => '09:00',
                'appointment_type' => 'general',
                'reason' => 'Annual check-up and review',
            ])
            ->assertRedirect(route('appointments.index'));

        $appointment = Appointment::where('patient_id', $patient->patient_id)->first();
        $this->assertNotNull($appointment, 'Appointment was not created.');
        $this->assertEquals('scheduled', $appointment->status);
        $this->assertEquals($this->doctor->staff_id, $appointment->doctor_id);

        // =====================================================================
        // 3. Patient check-in (AppointmentController::checkIn)
        // =====================================================================
        $this->actingAs($this->receptionistUser)
            ->post(route('appointments.check-in', $appointment->appointment_id))
            ->assertRedirect();

        $appointment->refresh();
        $this->assertEquals('arrived', $appointment->status);

        // =====================================================================
        // 4. Vitals recorded by a nurse (VitalController::store)
        // =====================================================================
        $this->actingAs($this->nurseUser)
            ->post(route('vitals.store'), [
                'patient_id' => $patient->patient_id,
                'consultation_id' => null,
                'temperature' => 36.8,
                'blood_pressure' => '120/80',
                'heart_rate' => 72,
                'respiratory_rate' => 16,
                'weight' => 70.00,
                'height' => 175.00,
                'oxygen_saturation' => 98,
                'pain_level' => 1,
                'priority' => 'normal',
                'notes' => 'Patient appears well',
                'measured_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $vital = Vital::where('patient_id', $patient->patient_id)->first();
        $this->assertNotNull($vital, 'Vitals were not recorded.');
        $this->assertEquals('120/80', $vital->blood_pressure);
        $this->assertEqualsWithDelta(22.86, round((float) $vital->bmi, 2), 0.01);
        $this->assertEquals($this->nurseUser->user_id, $vital->recorded_by);

        // =====================================================================
        // 5. Doctor consultation creation (ConsultationController::store)
        //    with requested procedures, labs and services.
        // =====================================================================
        $consultationPayload = [
            'patient_id' => $patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
            'appointment_id' => $appointment->appointment_id,
            'consultation_date' => now()->format('Y-m-d H:i:s'),
            'chief_complaint' => 'Headache and mild fever',
            'history_present_illness' => 'Started two days ago',
            'status' => 'in_progress',
            'priority' => 'normal',
            'diagnosis' => 'Viral upper respiratory infection',
            'treatment_plan' => 'Rest, hydration and review in 3 days',
            'follow_up_instructions' => 'Return if symptoms worsen',
            'notes' => 'Patient vitals stable',
            'requested_procedures' => [$this->additionalProcedure->procedure_id],
            'requested_labs' => [$this->labTestType->test_type_id],
            'requested_service_items' => [$this->serviceItem->test_type_id],
        ];

        $this->actingAs($this->doctorUser)
            ->post(route('consultations.store'), $consultationPayload)
            ->assertRedirect();

        $consultation = Consultation::where('patient_id', $patient->patient_id)->first();
        $this->assertNotNull($consultation, 'Consultation was not created.');
        $this->assertEquals('in_progress', $consultation->consultation_status);
        $this->assertEquals($appointment->appointment_id, $consultation->appointment_id);

        // =====================================================================
        // 6. Invoice generation from consultation (ConsultationInvoiceService)
        // =====================================================================
        $invoice = Invoice::where('consultation_id', $consultation->consultation_id)->first();
        $this->assertNotNull($invoice, 'Invoice was not generated for the consultation.');
        $this->assertEquals($patient->patient_id, $invoice->patient_id);
        $this->assertEquals('pending', $invoice->status);

        $expectedTotal =
            (float) $this->consultationProcedure->standard_fee
            + (float) $this->additionalProcedure->standard_fee
            + (float) $this->labTestType->price
            + (float) $this->serviceItem->price;

        $this->assertEqualsWithDelta($expectedTotal, (float) $invoice->total_amount, 0.01);

        $invoiceItems = $invoice->items;
        $this->assertCount(4, $invoiceItems);
        $this->assertTrue($invoiceItems->contains('item_type', 'consultation'));
        $this->assertTrue($invoiceItems->contains('item_type', 'procedure'));
        $this->assertTrue($invoiceItems->contains('item_type', 'lab_test'));
        $this->assertTrue($invoiceItems->contains('item_type', 'service'));

        $labRequests = LabTestRequest::where('consultation_id', $consultation->consultation_id)->get();
        $this->assertCount(1, $labRequests);
        $this->assertEquals($this->labTestType->test_type_id, $labRequests->first()->test_type_id);
        $this->assertEquals('pending', $labRequests->first()->status);

        // =====================================================================
        // 5b. Doctor updates consultation to completed and adds prescriptions.
        //     Invoice should be updated with new prescription items.
        // =====================================================================
        $updatePayload = [
            'patient_id' => $patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
            'consultation_date' => $consultation->consultation_date->format('Y-m-d H:i:s'),
            'chief_complaint' => 'Headache and mild fever',
            'status' => 'completed',
            'priority' => 'normal',
            'diagnosis' => 'Viral upper respiratory infection - confirmed',
            'treatment_plan' => 'Rest, hydration, paracetamol and review in 3 days',
            'follow_up_instructions' => 'Return if symptoms worsen',
            'notes' => 'Patient vitals stable, medication prescribed',
            'requested_prescriptions' => [
                [
                    'medication_id' => $this->medication->medication_id,
                    'dosage' => '500mg',
                    'frequency' => 'Three times daily',
                    'duration' => '5 days',
                    'instructions' => 'Take after meals',
                ],
            ],
        ];

        $this->actingAs($this->doctorUser)
            ->put(route('consultations.update', $consultation->consultation_id), $updatePayload)
            ->assertRedirect();

        $consultation->refresh();
        $this->assertEquals('completed', $consultation->consultation_status);

        $invoice->refresh();
        $expectedTotalAfterPrescription = $expectedTotal + (float) $this->medication->price_per_unit;
        $this->assertEqualsWithDelta($expectedTotalAfterPrescription, (float) $invoice->total_amount, 0.01);
        $this->assertTrue($invoice->items->contains('item_type', 'medication'));

        // =====================================================================
        // 7. Payment recording (PaymentController::store)
        // =====================================================================
        $this->actingAs($this->receptionistUser)
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->invoice_id,
                'amount' => (float) $invoice->total_amount,
                'payment_method' => 'cash',
                'payment_date' => now()->format('Y-m-d'),
                'transaction_reference' => 'TXN-WORKFLOW-'.uniqid(),
                'payment_status' => 'completed',
                'notes' => 'Full settlement',
            ])
            ->assertRedirect();

        $payment = Payment::where('invoice_id', $invoice->invoice_id)->first();
        $this->assertNotNull($payment, 'Payment was not recorded.');
        $this->assertEquals('completed', $payment->payment_status);
        $this->assertEqualsWithDelta((float) $invoice->total_amount, (float) $payment->amount, 0.01);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_patient_quick_registration_workflow(): void
    {
        $this->actingAs($this->receptionistUser)
            ->postJson(route('patients.quick-store'), [
                'first_name' => 'Quick',
                'last_name' => 'Patient',
                'email' => 'quick.patient@example.com',
                'phone' => '+254733444555',
                'date_of_birth' => '1985-08-20',
                'gender' => 'male',
                'blood_group' => 'A+',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'quick.patient@example.com',
            'first_name' => 'Quick',
            'last_name' => 'Patient',
        ]);
    }

    public function test_payment_cannot_exceed_invoice_balance(): void
    {
        $patientUser = User::factory()->create([
            'role_id' => Role::where('role_name', 'patient')->first()->role_id,
        ]);
        $patientUser->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $patientUser->user_id]);

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->patient_id,
            'total_amount' => 5000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->receptionistUser)
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->invoice_id,
                'amount' => 6000,
                'payment_method' => 'cash',
                'payment_date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('amount');

        $this->assertNull(Payment::where('invoice_id', $invoice->invoice_id)->first());
    }

    public function test_partial_payment_updates_invoice_status(): void
    {
        $patientUser = User::factory()->create([
            'role_id' => Role::where('role_name', 'patient')->first()->role_id,
        ]);
        $patientUser->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $patientUser->user_id]);

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->patient_id,
            'total_amount' => 10000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->receptionistUser)
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->invoice_id,
                'amount' => 4000,
                'payment_method' => 'cash',
                'payment_date' => now()->format('Y-m-d'),
                'payment_status' => 'completed',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals('partially_paid', $invoice->status);
        $this->assertEqualsWithDelta(6000.00, (float) PaymentService::remainingBalance($invoice), 0.01);

        // Record the remaining payment.
        $this->actingAs($this->receptionistUser)
            ->post(route('payments.store'), [
                'invoice_id' => $invoice->invoice_id,
                'amount' => 6000,
                'payment_method' => 'mobile_payment',
                'payment_date' => now()->format('Y-m-d'),
                'payment_status' => 'completed',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEqualsWithDelta(0.00, (float) PaymentService::remainingBalance($invoice), 0.01);
    }
}
