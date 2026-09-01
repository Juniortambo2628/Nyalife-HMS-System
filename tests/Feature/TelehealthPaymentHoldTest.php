<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionsSeeder;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TelehealthPaymentHoldTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_submit_mpesa_proof_for_an_active_telehealth_hold(): void
    {
        foreach (['doctor', 'patient'] as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);
        Storage::fake('private');

        $doctor = User::factory()->create(['role_id' => Role::where('role_name', 'doctor')->value('role_id')]);
        $doctor->assignRole('doctor');
        $staff = Staff::factory()->create(['user_id' => $doctor->user_id]);
        $appointment = Appointment::factory()->create([
            'doctor_id' => $staff->staff_id,
            'appointment_type' => 'telehealth',
            'status' => 'pending',
            'telehealth_payment_amount' => 4000,
            'telehealth_payment_token' => str()->random(64),
            'telehealth_payment_expires_at' => now()->addMinutes(15),
        ]);

        $this->post(route('telehealth.payment.submit', $appointment->telehealth_payment_token), [
            'transaction_reference' => 'QWE123RTY9',
            'receipt' => UploadedFile::fake()->image('mpesa-receipt.png'),
        ])->assertRedirect();

        $appointment->refresh();
        $this->assertSame('QWE123RTY9', $appointment->telehealth_payment_reference);
        $this->assertNotNull($appointment->telehealth_payment_submitted_at);
        Storage::disk('private')->assertExists($appointment->telehealth_payment_receipt_path);
    }

    public function test_staff_telehealth_booking_creates_pending_payment_hold(): void
    {
        Mail::fake();
        foreach (['admin', 'receptionist', 'patient'] as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);
        $this->seed(RolePermissionsSeeder::class);

        $patient = \App\Models\Patient::factory()->create();
        $doctor = Staff::factory()->create();
        $staffUser = User::factory()->create(['role_id' => Role::where('role_name', 'receptionist')->value('role_id')]);
        $staffUser->assignRole('receptionist');

        $this->actingAs($staffUser)->post(route('appointments.store'), [
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->staff_id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:00',
            'appointment_type' => Appointment::TYPE_TELEHEALTH,
            'reason' => 'Online consultation',
        ])->assertRedirect();

        $appointment = Appointment::latest('appointment_id')->first();

        $this->assertSame('pending', $appointment->status);
        $this->assertSame('4000.00', $appointment->telehealth_payment_amount);
        $this->assertNotNull($appointment->telehealth_payment_token);
        $this->assertTrue($appointment->telehealth_payment_expires_at->between(
            now()->addMinutes(14),
            now()->addMinutes(16)
        ));
    }
}
