<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Consultation;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Medication;
use App\Models\Prescription;
use App\Models\LabTestType;
use App\Models\LabTestRequest;
use App\Models\Vital;
use App\Models\Insurance;
use App\Models\FollowUp;
use App\Models\Role;
use App\Models\MedicalProcedure;
use App\Models\RadiologyRequest;
use App\Models\Message;
use App\Models\DoctorBlockOut;
use App\Models\ContactMessage;
use App\Models\Blog;
use App\Models\Setting;
use App\Models\MailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $labTechUser;
    protected User $pharmacistUser;
    protected User $patientUser;
    protected Patient $patient;
    protected Staff $doctor;
    protected Staff $nurse;
    protected Department $department;
    protected Medication $medication;
    protected LabTestType $labTestType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        $adminRoleId = Role::where('role_name', 'admin')->first()->role_id;
        $doctorRoleId = Role::where('role_name', 'doctor')->first()->role_id;
        $nurseRoleId = Role::where('role_name', 'nurse')->first()->role_id;
        $receptionistRoleId = Role::where('role_name', 'receptionist')->first()->role_id;
        $labTechRoleId = Role::where('role_name', 'lab_technician')->first()->role_id;
        $pharmacistRoleId = Role::where('role_name', 'pharmacist')->first()->role_id;
        $patientRoleId = Role::where('role_name', 'patient')->first()->role_id;

        $this->adminUser = User::factory()->create(['role_id' => $adminRoleId]);
        $this->adminUser->assignRole('admin');

        $this->doctorUser = User::factory()->create(['role_id' => $doctorRoleId]);
        $this->doctorUser->assignRole('doctor');

        $this->nurseUser = User::factory()->create(['role_id' => $nurseRoleId]);
        $this->nurseUser->assignRole('nurse');

        $this->receptionistUser = User::factory()->create(['role_id' => $receptionistRoleId]);
        $this->receptionistUser->assignRole('receptionist');

        $this->labTechUser = User::factory()->create(['role_id' => $labTechRoleId]);
        $this->labTechUser->assignRole('lab_technician');

        $this->pharmacistUser = User::factory()->create(['role_id' => $pharmacistRoleId]);
        $this->pharmacistUser->assignRole('pharmacist');

        $patientUser = User::factory()->create(['role_id' => $patientRoleId, 'gender' => 'female']);
        $patientUser->assignRole('patient');
        $this->patientUser = $patientUser;
        $this->patient = Patient::factory()->create(['user_id' => $patientUser->user_id]);

        $this->department = Department::factory()->create();
        $this->doctor = Staff::factory()->create([
            'user_id' => $this->doctorUser->user_id,
            'department_id' => $this->department->department_id,
        ]);
        $this->nurse = Staff::factory()->create([
            'user_id' => $this->nurseUser->user_id,
            'department_id' => $this->department->department_id,
        ]);

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
    // PUBLIC ROUTES — should return 200 without authentication
    // =========================================================================

    public function test_public_home_page(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_public_blogs_page(): void
    {
        $this->get('/blogs')->assertOk();
    }

    public function test_public_privacy_policy(): void
    {
        $this->get('/privacy-policy')->assertOk();
    }

    public function test_public_cookie_policy(): void
    {
        $this->get('/cookie-policy')->assertOk();
    }

    public function test_public_terms_of_service(): void
    {
        $this->get('/terms-of-service')->assertOk();
    }

    public function test_public_contact_form_submit(): void
    {
        $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test',
            'message' => 'Hello from test',
        ])->assertOk();
    }

    public function test_public_guest_appointment_submit(): void
    {
        $this->post('/guest-appointment', [
            'first_name' => 'Guest',
            'last_name' => 'User',
            'email' => 'guest@test.com',
            'phone' => '+254700000000',
            'date' => now()->addDays(3)->format('Y-m-d'),
            'time' => '10:00',
            'department_id' => $this->department->department_id,
        ])->assertOk();
    }

    public function test_public_newsletter_subscribe(): void
    {
        $this->post('/newsletter/subscribe', [
            'email' => 'subscriber@test.com',
        ])->assertOk();
    }

    public function test_public_check_guest_data(): void
    {
        $this->post('/check-guest-data', [
            'email' => 'guest@test.com',
        ])->assertOk();
    }

    public function test_public_telehealth_page(): void
    {
        $this->get('/telehealth')->assertOk();
    }

    // =========================================================================
    // AUTH ROUTES — guest should see, authenticated should redirect
    // =========================================================================

    public function test_login_page(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_login_patient_page(): void
    {
        $this->get('/login/patient')->assertOk();
    }

    public function test_login_staff_page(): void
    {
        $this->get('/login/staff')->assertOk();
    }

    public function test_register_page(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_forgot_password_page(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function test_authenticated_user_redirected_from_login(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/login')
            ->assertRedirect();
    }

    // =========================================================================
    // DASHBOARD ROUTES
    // =========================================================================

    public function test_admin_dashboard(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_doctor_dashboard(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_nurse_dashboard(): void
    {
        $this->actingAs($this->nurseUser)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_receptionist_dashboard(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_lab_technician_dashboard(): void
    {
        $this->actingAs($this->labTechUser)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_pharmacist_dashboard(): void
    {
        $this->actingAs($this->pharmacistUser)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_patient_dashboard(): void
    {
        $this->actingAs($this->patientUser)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_unauthenticated_dashboard_redirects(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    // =========================================================================
    // APPOINTMENT ROUTES
    // =========================================================================

    public function test_appointments_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/appointments')
            ->assertOk();
    }

    public function test_appointments_calendar(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/appointments/calendar')
            ->assertOk();
    }

    public function test_appointments_create(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/appointments/create')
            ->assertOk();
    }

    public function test_appointments_show(): void
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
        ]);

        $this->actingAs($this->adminUser)
            ->get("/appointments/{$appointment->appointment_id}")
            ->assertOk();
    }

    public function test_appointments_store(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/appointments', [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'department_id' => $this->department->department_id,
                'appointment_date' => now()->addDays(3)->format('Y-m-d'),
                'appointment_time' => '10:00',
                'appointment_type' => 'consultation',
                'reason' => 'Test appointment',
            ])
            ->assertRedirect();
    }

    public function test_appointments_update(): void
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
        ]);

        $this->actingAs($this->adminUser)
            ->put("/appointments/{$appointment->appointment_id}", [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'department_id' => $this->department->department_id,
                'appointment_date' => now()->addDays(5)->format('Y-m-d'),
                'appointment_time' => '11:00',
                'appointment_type' => 'consultation',
                'reason' => 'Updated reason',
            ])
            ->assertRedirect();
    }

    public function test_appointments_destroy(): void
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
        ]);

        $this->actingAs($this->adminUser)
            ->delete("/appointments/{$appointment->appointment_id}")
            ->assertRedirect();
    }

    public function test_appointments_check_in(): void
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($this->adminUser)
            ->post("/appointments/{$appointment->appointment_id}/check-in")
            ->assertRedirect();
    }

    public function test_doctor_block_outs_index(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/doctor-block-outs')
            ->assertOk();
    }

    public function test_doctor_block_outs_store(): void
    {
        $this->actingAs($this->doctorUser)
            ->post('/doctor-block-outs', [
                'block_date' => now()->addDays(10)->format('Y-m-d'),
                'reason' => 'Personal day off',
            ])
            ->assertRedirect();
    }

    public function test_doctors_search(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/doctors/search?q=doctor')
            ->assertOk();
    }

    // =========================================================================
    // PATIENT ROUTES
    // =========================================================================

    public function test_patients_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/patients')
            ->assertOk();
    }

    public function test_patients_create(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/patients/create')
            ->assertOk();
    }

    public function test_patients_show(): void
    {
        $this->actingAs($this->adminUser)
            ->get("/patients/{$this->patient->patient_id}")
            ->assertOk();
    }

    public function test_patients_search_ajax(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/patients/search?q=test')
            ->assertOk();
    }

    public function test_patients_quick_store(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/patients/quick-store', [
                'first_name' => 'Quick',
                'last_name' => 'Patient',
                'phone' => '+254700999999',
                'gender' => 'male',
            ])
            ->assertRedirect();
    }

    public function test_patients_export(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/patients/export')
            ->assertOk();
    }

    public function test_patients_print_cards(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/patients/print-cards')
            ->assertOk();
    }

    public function test_patients_update(): void
    {
        $this->actingAs($this->adminUser)
            ->put("/patients/{$this->patient->patient_id}", [
                'first_name' => 'Updated',
                'last_name' => 'Patient',
                'phone' => '+254700111111',
                'date_of_birth' => '1995-06-15',
                'gender' => 'female',
                'address' => 'Updated Address',
            ])
            ->assertRedirect();
    }

    public function test_patient_cannot_be_accessed_by_unauthenticated(): void
    {
        $this->get('/patients')->assertRedirect('/login');
    }

    // =========================================================================
    // CONSULTATION ROUTES
    // =========================================================================

    public function test_consultations_index(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/consultations')
            ->assertOk();
    }

    public function test_consultations_create(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/consultations/create')
            ->assertOk();
    }

    public function test_consultations_show(): void
    {
        $consultation = Consultation::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
        ]);

        $this->actingAs($this->doctorUser)
            ->get("/consultations/{$consultation->consultation_id}")
            ->assertOk();
    }

    public function test_consultations_store(): void
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
        ]);

        $this->actingAs($this->doctorUser)
            ->post('/consultations', [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'appointment_id' => $appointment->appointment_id,
                'chief_complaint' => 'Test complaint',
                'notes' => 'Test notes',
                'status' => 'in_progress',
            ])
            ->assertRedirect();
    }

    public function test_consultations_print(): void
    {
        $consultation = Consultation::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
        ]);

        $this->actingAs($this->doctorUser)
            ->get("/consultations/{$consultation->consultation_id}/print")
            ->assertOk();
    }

    // =========================================================================
    // VITALS ROUTES
    // =========================================================================

    public function test_vitals_index(): void
    {
        $this->actingAs($this->nurseUser)
            ->get('/vitals')
            ->assertOk();
    }

    public function test_vitals_create(): void
    {
        $this->actingAs($this->nurseUser)
            ->get('/vitals/record')
            ->assertOk();
    }

    public function test_vitals_store(): void
    {
        $this->actingAs($this->nurseUser)
            ->post('/vitals', [
                'patient_id' => $this->patient->patient_id,
                'temperature' => 36.5,
                'blood_pressure_systolic' => 120,
                'blood_pressure_diastolic' => 80,
                'heart_rate' => 72,
                'respiratory_rate' => 16,
                'oxygen_saturation' => 98,
                'weight' => 65,
                'height' => 170,
            ])
            ->assertRedirect();
    }

    public function test_vitals_latest(): void
    {
        Vital::factory()->create([
            'patient_id' => $this->patient->patient_id,
        ]);

        $this->actingAs($this->nurseUser)
            ->get("/patients/{$this->patient->patient_id}/latest-vitals")
            ->assertOk();
    }

    // =========================================================================
    // PRESCRIPTION ROUTES
    // =========================================================================

    public function test_prescriptions_index(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/prescriptions')
            ->assertOk();
    }

    public function test_prescriptions_create(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/prescriptions/create')
            ->assertOk();
    }

    public function test_prescriptions_store(): void
    {
        $this->actingAs($this->doctorUser)
            ->post('/prescriptions', [
                'patient_id' => $this->patient->patient_id,
                'prescribed_by' => $this->doctorUser->user_id,
                'items' => [
                    [
                        'medication_id' => $this->medication->medication_id,
                        'dosage' => '500mg',
                        'frequency' => 'Twice daily',
                        'duration' => '7 days',
                        'instructions' => 'Take after meals',
                    ],
                ],
            ])
            ->assertRedirect();
    }

    public function test_prescriptions_show(): void
    {
        $prescription = Prescription::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'prescribed_by' => $this->doctorUser->user_id,
        ]);

        $this->actingAs($this->doctorUser)
            ->get("/prescriptions/{$prescription->prescription_id}")
            ->assertOk();
    }

    public function test_prescriptions_print(): void
    {
        $prescription = Prescription::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'prescribed_by' => $this->doctorUser->user_id,
        ]);

        $this->actingAs($this->doctorUser)
            ->get("/prescriptions/{$prescription->prescription_id}/print")
            ->assertOk();
    }

    // =========================================================================
    // LAB ROUTES
    // =========================================================================

    public function test_lab_requests_index(): void
    {
        $this->actingAs($this->labTechUser)
            ->get('/lab/requests')
            ->assertOk();
    }

    public function test_lab_requests_create(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/lab/requests/create')
            ->assertOk();
    }

    public function test_lab_tests_catalog(): void
    {
        $this->actingAs($this->labTechUser)
            ->get('/lab/tests')
            ->assertOk();
    }

    public function test_lab_samples_index(): void
    {
        $this->actingAs($this->labTechUser)
            ->get('/lab/samples')
            ->assertOk();
    }

    public function test_lab_results_index(): void
    {
        $this->actingAs($this->labTechUser)
            ->get('/lab-results')
            ->assertOk();
    }

    public function test_lab_test_type_index(): void
    {
        $this->actingAs($this->labTechUser)
            ->get('/lab-tests')
            ->assertOk();
    }

    public function test_lab_test_type_create(): void
    {
        $this->actingAs($this->labTechUser)
            ->get('/lab-tests/create')
            ->assertOk();
    }

    public function test_lab_test_type_store(): void
    {
        $this->actingAs($this->labTechUser)
            ->post('/lab-tests', [
                'test_name' => 'New Test Type',
                'category' => 'Hematology',
                'price' => 1500,
                'turnaround_time' => '24 hours',
            ])
            ->assertRedirect();
    }

    // =========================================================================
    // PHARMACY ROUTES
    // =========================================================================

    public function test_pharmacy_inventory(): void
    {
        $this->actingAs($this->pharmacistUser)
            ->get('/pharmacy/inventory')
            ->assertOk();
    }

    public function test_pharmacy_medicines(): void
    {
        $this->actingAs($this->pharmacistUser)
            ->get('/pharmacy/medicines')
            ->assertOk();
    }

    public function test_pharmacy_purchase_orders(): void
    {
        $this->actingAs($this->pharmacistUser)
            ->get('/pharmacy/purchase-orders')
            ->assertOk();
    }

    public function test_medications_search(): void
    {
        $this->actingAs($this->pharmacistUser)
            ->get('/medications/search?q=paracetamol')
            ->assertOk();
    }

    // =========================================================================
    // INVOICE ROUTES
    // =========================================================================

    public function test_invoices_index(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/invoices')
            ->assertOk();
    }

    public function test_invoices_create(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/invoices/create')
            ->assertOk();
    }

    public function test_invoices_store(): void
    {
        $this->actingAs($this->receptionistUser)
            ->post('/invoices', [
                'patient_id' => $this->patient->patient_id,
                'items' => [
                    [
                        'description' => 'Consultation Fee',
                        'quantity' => 1,
                        'unit_price' => 2000,
                    ],
                ],
            ])
            ->assertRedirect();
    }

    public function test_invoices_show(): void
    {
        $invoice = Invoice::factory()->create([
            'patient_id' => $this->patient->patient_id,
        ]);

        $this->actingAs($this->receptionistUser)
            ->get("/invoices/{$invoice->invoice_id}")
            ->assertOk();
    }

    public function test_invoices_export_csv(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/invoices/export/csv')
            ->assertOk();
    }

    // =========================================================================
    // PAYMENT ROUTES
    // =========================================================================

    public function test_payments_index(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/payments')
            ->assertOk();
    }

    public function test_payments_create(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/payments/create')
            ->assertOk();
    }

    public function test_payments_store(): void
    {
        $invoice = Invoice::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'total_amount' => 2000,
        ]);

        $this->actingAs($this->receptionistUser)
            ->post('/payments', [
                'invoice_id' => $invoice->invoice_id,
                'amount' => 2000,
                'payment_method' => 'cash',
            ])
            ->assertRedirect();
    }

    public function test_payments_export_csv(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/payments/export/csv')
            ->assertOk();
    }

    // =========================================================================
    // FOLLOW-UP ROUTES
    // =========================================================================

    public function test_follow_ups_index(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/follow-ups')
            ->assertOk();
    }

    public function test_follow_ups_upcoming(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/follow-ups/upcoming')
            ->assertOk();
    }

    public function test_follow_ups_create(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/follow-ups/create')
            ->assertOk();
    }

    public function test_follow_ups_store(): void
    {
        $this->actingAs($this->doctorUser)
            ->post('/follow-ups', [
                'patient_id' => $this->patient->patient_id,
                'doctor_id' => $this->doctor->staff_id,
                'follow_up_date' => now()->addWeek()->format('Y-m-d'),
                'reason' => 'Follow-up check',
            ])
            ->assertRedirect();
    }

    // =========================================================================
    // DEPARTMENT ROUTES
    // =========================================================================

    public function test_departments_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/departments')
            ->assertOk();
    }

    public function test_departments_create(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/departments/create')
            ->assertOk();
    }

    public function test_departments_store(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/departments', [
                'department_name' => 'New Department',
                'description' => 'A new department',
            ])
            ->assertRedirect();
    }

    public function test_departments_toggle(): void
    {
        $this->actingAs($this->adminUser)
            ->post("/departments/{$this->department->department_id}/toggle")
            ->assertRedirect();
    }

    // =========================================================================
    // USER MANAGEMENT ROUTES
    // =========================================================================

    public function test_users_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/users')
            ->assertOk();
    }

    public function test_users_create(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/users/create')
            ->assertOk();
    }

    public function test_users_show(): void
    {
        $this->actingAs($this->adminUser)
            ->get("/users/{$this->doctorUser->user_id}")
            ->assertOk();
    }

    public function test_users_store(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/users', [
                'first_name' => 'New',
                'last_name' => 'Staff',
                'email' => 'newstaff@test.com',
                'username' => 'newstaff',
                'phone' => '+254700888888',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            ])
            ->assertRedirect();
    }

    // =========================================================================
    // REPORTS ROUTES
    // =========================================================================

    public function test_reports_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/reports')
            ->assertOk();
    }

    public function test_reports_financial(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/reports/financial')
            ->assertOk();
    }

    public function test_reports_appointments(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/reports/appointments')
            ->assertOk();
    }

    public function test_reports_patients(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/reports/patients')
            ->assertOk();
    }

    public function test_reports_laboratory(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/reports/laboratory')
            ->assertOk();
    }

    public function test_reports_pharmacy(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/reports/pharmacy')
            ->assertOk();
    }

    public function test_reports_export(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/reports/export')
            ->assertOk();
    }

    // =========================================================================
    // NOTIFICATION ROUTES
    // =========================================================================

    public function test_notifications_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/notifications')
            ->assertOk();
    }

    public function test_notifications_mark_all_read(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/notifications/read-all')
            ->assertRedirect();
    }

    // =========================================================================
    // MESSAGE ROUTES
    // =========================================================================

    public function test_messages_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/messages')
            ->assertOk();
    }

    public function test_messages_entities(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/messages/entities')
            ->assertOk();
    }

    public function test_messages_store(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/messages', [
                'receiver_id' => $this->doctorUser->user_id,
                'content' => 'Hello from test',
            ])
            ->assertRedirect();
    }

    // =========================================================================
    // INSURANCE ROUTES
    // =========================================================================

    public function test_insurances_index(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/admin/insurances')
            ->assertOk();
    }

    public function test_insurances_create(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/admin/insurances/create')
            ->assertOk();
    }

    public function test_insurances_store(): void
    {
        $this->actingAs($this->receptionistUser)
            ->post('/admin/insurances', [
                'insurance_name' => 'Test Insurance',
                'insurance_company' => 'Test Corp',
                'coverage_percentage' => 80,
                'contact_phone' => '+254700777777',
            ])
            ->assertRedirect();
    }

    public function test_insurances_toggle(): void
    {
        $insurance = Insurance::factory()->create();

        $this->actingAs($this->receptionistUser)
            ->post("/admin/insurances/{$insurance->insurance_id}/toggle")
            ->assertRedirect();
    }

    public function test_insurances_public_list(): void
    {
        $this->get('/api/insurances')
            ->assertOk();
    }

    // =========================================================================
    // ADMIN / SYSTEM ROUTES
    // =========================================================================

    public function test_admin_settings(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/settings')
            ->assertOk();
    }

    public function test_admin_blogs_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/blogs')
            ->assertOk();
    }

    public function test_admin_blogs_create(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/blogs/create')
            ->assertOk();
    }

    public function test_admin_blogs_store(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/admin/blogs', [
                'title' => 'Test Blog',
                'content' => 'Test content for blog post',
                'status' => 'draft',
            ])
            ->assertRedirect();
    }

    public function test_admin_cms(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/cms')
            ->assertOk();
    }

    public function test_admin_medical_procedures_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/medical-procedures')
            ->assertOk();
    }

    public function test_admin_medical_procedures_store(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/admin/medical-procedures', [
                'procedure_name' => 'Test Procedure',
                'description' => 'A test procedure',
                'cost' => 5000,
            ])
            ->assertRedirect();
    }

    public function test_admin_api_tokens(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/api-tokens')
            ->assertOk();
    }

    public function test_admin_mail_templates(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/mail-templates')
            ->assertOk();
    }

    public function test_admin_void_audit(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/void-audit')
            ->assertOk();
    }

    // =========================================================================
    // CONTACT MESSAGES (ADMIN)
    // =========================================================================

    public function test_admin_contact_messages_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/messages')
            ->assertOk();
    }

    public function test_admin_contact_messages_store(): void
    {
        $contactMessage = ContactMessage::factory()->create();

        $this->actingAs($this->adminUser)
            ->get("/admin/messages/{$contactMessage->contact_message_id}")
            ->assertOk();
    }

    // =========================================================================
    // PROFILE ROUTES
    // =========================================================================

    public function test_profile_edit(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/profile')
            ->assertOk();
    }

    public function test_profile_update(): void
    {
        $this->actingAs($this->adminUser)
            ->patch('/profile', [
                'first_name' => 'Updated',
                'last_name' => 'Admin',
                'email' => $this->adminUser->email,
            ])
            ->assertRedirect();
    }

    public function test_profile_delete(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/profile')
            ->assertRedirect('/');
    }

    // =========================================================================
    // UNAUTHENTICATED ACCESS — all protected routes redirect to /login
    // =========================================================================

    public function test_unauthenticated_appointments(): void
    {
        $this->get('/appointments')->assertRedirect('/login');
    }

    public function test_unauthenticated_patients(): void
    {
        $this->get('/patients')->assertRedirect('/login');
    }

    public function test_unauthenticated_consultations(): void
    {
        $this->get('/consultations')->assertRedirect('/login');
    }

    public function test_unauthenticated_vitals(): void
    {
        $this->get('/vitals')->assertRedirect('/login');
    }

    public function test_unauthenticated_prescriptions(): void
    {
        $this->get('/prescriptions')->assertRedirect('/login');
    }

    public function test_unauthenticated_lab(): void
    {
        $this->get('/lab/requests')->assertRedirect('/login');
    }

    public function test_unauthenticated_pharmacy(): void
    {
        $this->get('/pharmacy/inventory')->assertRedirect('/login');
    }

    public function test_unauthenticated_invoices(): void
    {
        $this->get('/invoices')->assertRedirect('/login');
    }

    public function test_unauthenticated_payments(): void
    {
        $this->get('/payments')->assertRedirect('/login');
    }

    public function test_unauthenticated_follow_ups(): void
    {
        $this->get('/follow-ups')->assertRedirect('/login');
    }

    public function test_unauthenticated_departments(): void
    {
        $this->get('/departments')->assertRedirect('/login');
    }

    public function test_unauthenticated_users(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_unauthenticated_reports(): void
    {
        $this->get('/reports')->assertRedirect('/login');
    }

    public function test_unauthenticated_messages(): void
    {
        $this->get('/messages')->assertRedirect('/login');
    }

    public function test_unauthenticated_notifications(): void
    {
        $this->get('/notifications')->assertRedirect('/login');
    }

    public function test_unauthenticated_insurances(): void
    {
        $this->get('/admin/insurances')->assertRedirect('/login');
    }

    public function test_unauthenticated_admin_settings(): void
    {
        $this->get('/admin/settings')->assertRedirect('/login');
    }

    public function test_unauthenticated_admin_blogs(): void
    {
        $this->get('/admin/blogs')->assertRedirect('/login');
    }

    public function test_unauthenticated_admin_cms(): void
    {
        $this->get('/admin/cms')->assertRedirect('/login');
    }

    public function test_unauthenticated_profile(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    // =========================================================================
    // PERMISSION DENIED — wrong role for permission-gated routes
    // =========================================================================

    public function test_patient_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->patientUser)
            ->get('/admin/settings')
            ->assertStatus(403);
    }

    public function test_nurse_cannot_access_reports(): void
    {
        $this->actingAs($this->nurseUser)
            ->get('/reports')
            ->assertStatus(403);
    }

    public function test_lab_tech_cannot_access_prescriptions(): void
    {
        $this->actingAs($this->labTechUser)
            ->get('/prescriptions')
            ->assertStatus(403);
    }

    public function test_pharmacist_cannot_access_invoices(): void
    {
        $this->actingAs($this->pharmacistUser)
            ->get('/invoices')
            ->assertStatus(403);
    }

    public function test_receptionist_cannot_access_consultations(): void
    {
        $this->actingAs($this->receptionistUser)
            ->get('/consultations')
            ->assertStatus(403);
    }

    public function test_doctor_cannot_manage_users(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/users')
            ->assertStatus(403);
    }

    // =========================================================================
    // RADIOLOGY ROUTES
    // =========================================================================

    public function test_radiology_index(): void
    {
        $this->actingAs($this->labTechUser)
            ->get('/radiology/requests')
            ->assertOk();
    }

    public function test_radiology_create(): void
    {
        $this->actingAs($this->doctorUser)
            ->get('/radiology/requests/create')
            ->assertOk();
    }

    // =========================================================================
    // TELEMEDICINE
    // =========================================================================

    public function test_telehealth_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/telehealth-consents')
            ->assertOk();
    }
}
