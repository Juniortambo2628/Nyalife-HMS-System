<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionsSeeder;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $doctorUser;

    protected User $patientUser;

    protected Patient $patient;

    protected Staff $doctor;

    protected Department $department;

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

        $patientUser = User::factory()->create(['role_id' => $patientRoleId, 'gender' => 'female']);
        $patientUser->assignRole('patient');
        $this->patientUser = $patientUser;
        $this->patient = Patient::factory()->create(['user_id' => $patientUser->user_id]);

        $this->department = Department::factory()->create();
        $this->doctor = Staff::factory()->create([
            'user_id' => $this->doctorUser->user_id,
            'department_id' => $this->department->department_id,
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

    // =========================================================================
    // PUBLIC API ROUTES
    // =========================================================================

    public function test_public_available_appointment_slots(): void
    {
        $this->getJson('/api/appointments/available-slots?date='.now()->addDay()->format('Y-m-d'))
            ->assertOk();
    }

    public function test_public_insurance_list(): void
    {
        $this->getJson('/api/insurances')
            ->assertOk();
    }

    public function test_public_available_slots_throttle(): void
    {
        for ($i = 0; $i < 35; $i++) {
            $this->getJson('/api/appointments/available-slots?date='.now()->addDay()->format('Y-m-d'));
        }
        $this->getJson('/api/appointments/available-slots?date='.now()->addDay()->format('Y-m-d'))->assertStatus(429);
    }

    // =========================================================================
    // AUTHENTICATED API ROUTES (Sanctum)
    // =========================================================================

    public function test_unauthenticated_api_returns_401(): void
    {
        $this->getJson('/api/v1/appointments')->assertStatus(401);
        $this->getJson('/api/v1/departments')->assertStatus(401);
        $this->getJson('/api/v1/payments')->assertStatus(401);
        $this->getJson('/api/v1/follow-ups')->assertStatus(401);
    }

    public function test_api_appointments_index_as_admin(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/appointments')
            ->assertOk();
    }

    public function test_api_appointments_index_as_doctor(): void
    {
        Sanctum::actingAs($this->doctorUser);

        $this->getJson('/api/v1/appointments')
            ->assertOk();
    }

    public function test_api_appointments_index_as_patient(): void
    {
        Sanctum::actingAs($this->patientUser);

        $this->getJson('/api/v1/appointments')
            ->assertOk();
    }

    public function test_api_departments_index(): void
    {
        Department::factory()->create();

        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/departments')
            ->assertOk();
    }

    public function test_api_departments_show(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson("/api/v1/departments/{$this->department->department_id}")
            ->assertOk();
    }

    public function test_api_departments_show_not_found(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/departments/99999')
            ->assertStatus(404);
    }

    public function test_api_payments_index(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/payments')
            ->assertOk();
    }

    public function test_api_payments_show(): void
    {
        $invoice = Invoice::factory()->create(['patient_id' => $this->patient->patient_id]);
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'received_by' => $this->adminUser->user_id,
        ]);

        Sanctum::actingAs($this->adminUser);

        $this->getJson("/api/v1/payments/{$payment->payment_id}")
            ->assertOk();
    }

    public function test_api_payments_show_not_found(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/payments/99999')
            ->assertStatus(404);
    }

    public function test_api_follow_ups_index(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/follow-ups')
            ->assertOk();
    }

    public function test_api_follow_ups_upcoming(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/follow-ups/upcoming')
            ->assertOk();
    }

    public function test_api_follow_ups_show(): void
    {
        $followUp = FollowUp::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'created_by' => $this->doctor->user_id,
        ]);

        Sanctum::actingAs($this->adminUser);

        $this->getJson("/api/v1/follow-ups/{$followUp->follow_up_id}")
            ->assertOk();
    }

    public function test_api_follow_ups_show_not_found(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/follow-ups/99999')
            ->assertStatus(404);
    }

    public function test_context_switching_endpoint(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/context-switching')
            ->assertOk();
    }

    // =========================================================================
    // API PERMISSION TESTS
    // =========================================================================

    public function test_patient_cannot_access_doctor_only_api(): void
    {
        Sanctum::actingAs($this->patientUser);

        $this->getJson('/api/v1/payments')->assertStatus(403);
    }

    public function test_doctor_can_access_appointments_api(): void
    {
        Sanctum::actingAs($this->doctorUser);

        $this->getJson('/api/v1/appointments')->assertOk();
    }

    public function test_doctor_can_access_departments_api(): void
    {
        Sanctum::actingAs($this->doctorUser);

        $this->getJson('/api/v1/departments')->assertOk();
    }

    // =========================================================================
    // API RESPONSE FORMAT TESTS
    // =========================================================================

    public function test_api_departments_returns_json(): void
    {
        Department::factory()->create();

        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/departments')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_api_appointments_returns_json(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/appointments')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_api_payments_returns_json(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/payments')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_api_follow_ups_returns_json(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/follow-ups')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');
    }

    // =========================================================================
    // API EDGE CASES
    // =========================================================================

    public function test_api_nonexistent_route_returns_404(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/nonexistent')
            ->assertStatus(404);
    }

    public function test_api_appointments_with_date_filter(): void
    {
        Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
        ]);

        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/appointments?from='.now()->subDay()->format('Y-m-d'))
            ->assertOk();
    }

    public function test_api_appointments_with_status_filter(): void
    {
        Appointment::factory()->create([
            'patient_id' => $this->patient->patient_id,
            'doctor_id' => $this->doctor->staff_id,
            'status' => 'confirmed',
        ]);

        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/appointments?status=confirmed')
            ->assertOk();
    }
}
