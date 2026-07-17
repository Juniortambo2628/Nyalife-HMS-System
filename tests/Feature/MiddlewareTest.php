<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Consultation;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Prescription;
use App\Models\LabTestRequest;
use App\Models\Vital;
use App\Models\FollowUp;
use App\Models\Insurance;
use App\Models\Role;
use App\Models\Message;
use App\Models\ContactMessage;
use App\Models\Blog;
use App\Models\DoctorBlockOut;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
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
    // AUTH MIDDLEWARE
    // =========================================================================

    public function test_guest_redirected_from_protected_route(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/appointments',
            '/patients',
            '/consultations',
            '/vitals',
            '/prescriptions',
            '/lab/requests',
            '/pharmacy/inventory',
            '/invoices',
            '/payments',
            '/follow-ups',
            '/departments',
            '/users',
            '/reports',
            '/messages',
            '/notifications',
            '/admin/settings',
            '/admin/insurances',
            '/admin/blogs',
            '/admin/cms',
            '/profile',
        ];

        foreach ($protectedRoutes as $route) {
            $this->get($route)->assertRedirect('/login', "Route {$route} should redirect guests");
        }
    }

    // =========================================================================
    // MANAGE_PATIENTS PERMISSION
    // =========================================================================

    public function test_admin_can_manage_patients(): void
    {
        $this->actingAs($this->adminUser)->get('/patients')->assertOk();
    }

    public function test_doctor_can_manage_patients(): void
    {
        $this->actingAs($this->doctorUser)->get('/patients')->assertOk();
    }

    public function test_nurse_can_manage_patients(): void
    {
        $this->actingAs($this->nurseUser)->get('/patients')->assertOk();
    }

    public function test_receptionist_can_manage_patients(): void
    {
        $this->actingAs($this->receptionistUser)->get('/patients')->assertOk();
    }

    public function test_lab_tech_cannot_manage_patients(): void
    {
        $this->actingAs($this->labTechUser)->get('/patients')->assertStatus(403);
    }

    public function test_pharmacist_cannot_manage_patients(): void
    {
        $this->actingAs($this->pharmacistUser)->get('/patients')->assertStatus(403);
    }

    public function test_patient_cannot_manage_patients(): void
    {
        $this->actingAs($this->patientUser)->get('/patients')->assertStatus(403);
    }

    // =========================================================================
    // MANAGE_CONSULTATIONS PERMISSION
    // =========================================================================

    public function test_admin_can_manage_consultations(): void
    {
        $this->actingAs($this->adminUser)->get('/consultations')->assertOk();
    }

    public function test_doctor_can_manage_consultations(): void
    {
        $this->actingAs($this->doctorUser)->get('/consultations')->assertOk();
    }

    public function test_nurse_can_manage_consultations(): void
    {
        $this->actingAs($this->nurseUser)->get('/consultations')->assertOk();
    }

    public function test_receptionist_cannot_manage_consultations(): void
    {
        $this->actingAs($this->receptionistUser)->get('/consultations')->assertStatus(403);
    }

    // =========================================================================
    // MANAGE_VITALS PERMISSION
    // =========================================================================

    public function test_admin_can_manage_vitals(): void
    {
        $this->actingAs($this->adminUser)->get('/vitals')->assertOk();
    }

    public function test_nurse_can_manage_vitals(): void
    {
        $this->actingAs($this->nurseUser)->get('/vitals')->assertOk();
    }

    public function test_doctor_can_manage_vitals(): void
    {
        $this->actingAs($this->doctorUser)->get('/vitals')->assertOk();
    }

    public function test_receptionist_cannot_manage_vitals(): void
    {
        $this->actingAs($this->receptionistUser)->get('/vitals')->assertStatus(403);
    }

    // =========================================================================
    // MANAGE_PRESCRIPTIONS PERMISSION
    // =========================================================================

    public function test_admin_can_manage_prescriptions(): void
    {
        $this->actingAs($this->adminUser)->get('/prescriptions')->assertOk();
    }

    public function test_doctor_can_manage_prescriptions(): void
    {
        $this->actingAs($this->doctorUser)->get('/prescriptions')->assertOk();
    }

    public function test_pharmacist_can_manage_prescriptions(): void
    {
        $this->actingAs($this->pharmacistUser)->get('/prescriptions')->assertOk();
    }

    public function test_lab_tech_cannot_manage_prescriptions(): void
    {
        $this->actingAs($this->labTechUser)->get('/prescriptions')->assertStatus(403);
    }

    // =========================================================================
    // MANAGE_LAB PERMISSION
    // =========================================================================

    public function test_admin_can_manage_lab(): void
    {
        $this->actingAs($this->adminUser)->get('/lab/requests')->assertOk();
    }

    public function test_doctor_can_manage_lab(): void
    {
        $this->actingAs($this->doctorUser)->get('/lab/requests')->assertOk();
    }

    public function test_lab_tech_can_manage_lab(): void
    {
        $this->actingAs($this->labTechUser)->get('/lab/requests')->assertOk();
    }

    public function test_nurse_can_manage_lab(): void
    {
        $this->actingAs($this->nurseUser)->get('/lab/requests')->assertOk();
    }

    public function test_pharmacist_cannot_manage_lab(): void
    {
        $this->actingAs($this->pharmacistUser)->get('/lab/requests')->assertStatus(403);
    }

    // =========================================================================
    // MANAGE_INVOICES PERMISSION
    // =========================================================================

    public function test_admin_can_manage_invoices(): void
    {
        $this->actingAs($this->adminUser)->get('/invoices')->assertOk();
    }

    public function test_receptionist_can_manage_invoices(): void
    {
        $this->actingAs($this->receptionistUser)->get('/invoices')->assertOk();
    }

    public function test_doctor_cannot_manage_invoices(): void
    {
        $this->actingAs($this->doctorUser)->get('/invoices')->assertStatus(403);
    }

    public function test_nurse_cannot_manage_invoices(): void
    {
        $this->actingAs($this->nurseUser)->get('/invoices')->assertStatus(403);
    }

    // =========================================================================
    // MANAGE_PAYMENTS PERMISSION
    // =========================================================================

    public function test_admin_can_manage_payments(): void
    {
        $this->actingAs($this->adminUser)->get('/payments')->assertOk();
    }

    public function test_receptionist_can_manage_payments(): void
    {
        $this->actingAs($this->receptionistUser)->get('/payments')->assertOk();
    }

    public function test_doctor_can_manage_payments(): void
    {
        $this->actingAs($this->doctorUser)->get('/payments')->assertOk();
    }

    public function test_nurse_cannot_manage_payments(): void
    {
        $this->actingAs($this->nurseUser)->get('/payments')->assertStatus(403);
    }

    // =========================================================================
    // MANAGE_PHARMACY PERMISSION
    // =========================================================================

    public function test_admin_can_manage_pharmacy(): void
    {
        $this->actingAs($this->adminUser)->get('/pharmacy/inventory')->assertOk();
    }

    public function test_pharmacist_can_manage_pharmacy(): void
    {
        $this->actingAs($this->pharmacistUser)->get('/pharmacy/inventory')->assertOk();
    }

    public function test_doctor_cannot_manage_pharmacy(): void
    {
        $this->actingAs($this->doctorUser)->get('/pharmacy/inventory')->assertStatus(403);
    }

    // =========================================================================
    // MANAGE_USERS PERMISSION
    // =========================================================================

    public function test_admin_can_manage_users(): void
    {
        $this->actingAs($this->adminUser)->get('/users')->assertOk();
    }

    public function test_doctor_cannot_manage_users(): void
    {
        $this->actingAs($this->doctorUser)->get('/users')->assertStatus(403);
    }

    public function test_nurse_cannot_manage_users(): void
    {
        $this->actingAs($this->nurseUser)->get('/users')->assertStatus(403);
    }

    // =========================================================================
    // VIEW_REPORTS PERMISSION
    // =========================================================================

    public function test_admin_can_view_reports(): void
    {
        $this->actingAs($this->adminUser)->get('/reports')->assertOk();
    }

    public function test_doctor_can_view_reports(): void
    {
        $this->actingAs($this->doctorUser)->get('/reports')->assertOk();
    }

    public function test_receptionist_can_view_reports(): void
    {
        $this->actingAs($this->receptionistUser)->get('/reports')->assertOk();
    }

    public function test_nurse_cannot_view_reports(): void
    {
        $this->actingAs($this->nurseUser)->get('/reports')->assertStatus(403);
    }

    public function test_lab_tech_cannot_view_reports(): void
    {
        $this->actingAs($this->labTechUser)->get('/reports')->assertStatus(403);
    }

    // =========================================================================
    // SEND_MESSAGES PERMISSION
    // =========================================================================

    public function test_admin_can_send_messages(): void
    {
        $this->actingAs($this->adminUser)->get('/messages')->assertOk();
    }

    public function test_doctor_can_send_messages(): void
    {
        $this->actingAs($this->doctorUser)->get('/messages')->assertOk();
    }

    public function test_nurse_can_send_messages(): void
    {
        $this->actingAs($this->nurseUser)->get('/messages')->assertOk();
    }

    public function test_receptionist_can_send_messages(): void
    {
        $this->actingAs($this->receptionistUser)->get('/messages')->assertOk();
    }

    public function test_lab_tech_can_send_messages(): void
    {
        $this->actingAs($this->labTechUser)->get('/messages')->assertOk();
    }

    public function test_pharmacist_can_send_messages(): void
    {
        $this->actingAs($this->pharmacistUser)->get('/messages')->assertOk();
    }

    // =========================================================================
    // MANAGE_INSURANCE PERMISSION
    // =========================================================================

    public function test_admin_can_manage_insurance(): void
    {
        $this->actingAs($this->adminUser)->get('/admin/insurances')->assertOk();
    }

    public function test_receptionist_can_manage_insurance(): void
    {
        $this->actingAs($this->receptionistUser)->get('/admin/insurances')->assertOk();
    }

    public function test_doctor_cannot_manage_insurance(): void
    {
        $this->actingAs($this->doctorUser)->get('/admin/insurances')->assertStatus(403);
    }

    // =========================================================================
    // MANAGE_SYSTEM PERMISSION
    // =========================================================================

    public function test_admin_can_manage_system(): void
    {
        $this->actingAs($this->adminUser)->get('/admin/settings')->assertOk();
    }

    public function test_doctor_cannot_manage_system(): void
    {
        $this->actingAs($this->doctorUser)->get('/admin/settings')->assertStatus(403);
    }

    public function test_admin_can_manage_blogs(): void
    {
        $this->actingAs($this->adminUser)->get('/admin/blogs')->assertOk();
    }

    public function test_admin_can_manage_cms(): void
    {
        $this->actingAs($this->adminUser)->get('/admin/cms')->assertOk();
    }

    public function test_admin_can_manage_medical_procedures(): void
    {
        $this->actingAs($this->adminUser)->get('/admin/medical-procedures')->assertOk();
    }

    // =========================================================================
    // VIEW_DEPARTMENTS PERMISSION
    // =========================================================================

    public function test_admin_can_view_departments(): void
    {
        $this->actingAs($this->adminUser)->get('/departments')->assertOk();
    }

    public function test_doctor_can_view_departments(): void
    {
        $this->actingAs($this->doctorUser)->get('/departments')->assertOk();
    }

    public function test_nurse_can_view_departments(): void
    {
        $this->actingAs($this->nurseUser)->get('/departments')->assertOk();
    }

    public function test_pharmacist_can_view_departments(): void
    {
        $this->actingAs($this->pharmacistUser)->get('/departments')->assertOk();
    }

    // =========================================================================
    // MANAGE_FOLLOW_UPS PERMISSION
    // =========================================================================

    public function test_admin_can_manage_follow_ups(): void
    {
        $this->actingAs($this->adminUser)->get('/follow-ups')->assertOk();
    }

    public function test_doctor_can_manage_follow_ups(): void
    {
        $this->actingAs($this->doctorUser)->get('/follow-ups')->assertOk();
    }

    public function test_nurse_can_manage_follow_ups(): void
    {
        $this->actingAs($this->nurseUser)->get('/follow-ups')->assertOk();
    }

    public function test_receptionist_cannot_manage_follow_ups(): void
    {
        $this->actingAs($this->receptionistUser)->get('/follow-ups')->assertStatus(403);
    }

    // =========================================================================
    // PROFILE — auth-only (not permission-gated)
    // =========================================================================

    public function test_authenticated_user_can_access_profile(): void
    {
        $this->actingAs($this->adminUser)->get('/profile')->assertOk();
    }

    public function test_patient_can_access_profile(): void
    {
        $this->actingAs($this->patientUser)->get('/profile')->assertOk();
    }

    // =========================================================================
    // RATE LIMITING (contact form)
    // =========================================================================

    public function test_contact_form_rate_limit(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->post('/contact', [
                'name' => 'Test',
                'email' => 'test@test.com',
                'subject' => 'Test',
                'message' => 'Hello',
            ]);
        }
        $this->post('/contact', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'subject' => 'Test',
            'message' => 'Hello',
        ])->assertStatus(429);
    }
}
