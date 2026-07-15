<?php

namespace Tests\Unit\Models;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Patient;
use App\Models\User;
use App\Models\Consultation;
use App\Models\Medication;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_prescription_belongs_to_patient(): void
    {
        $prescription = Prescription::factory()->create();

        $this->assertInstanceOf(Patient::class, $prescription->patient);
        $this->assertEquals($prescription->patient_id, $prescription->patient->patient_id);
    }

    public function test_prescription_belongs_to_prescribed_by_user(): void
    {
        $prescription = Prescription::factory()->create();

        $this->assertInstanceOf(User::class, $prescription->doctor);
        $this->assertEquals($prescription->prescribed_by, $prescription->doctor->user_id);
    }

    public function test_prescription_belongs_to_consultation(): void
    {
        $prescription = Prescription::factory()->create();

        $this->assertInstanceOf(Consultation::class, $prescription->consultation);
        $this->assertEquals($prescription->consultation_id, $prescription->consultation->consultation_id);
    }

    public function test_prescription_has_many_items(): void
    {
        $prescription = Prescription::factory()->has(PrescriptionItem::factory()->count(3), 'items')->create();

        $this->assertCount(3, $prescription->items);
        $prescription->items->each(fn ($item) => $this->assertEquals($prescription->prescription_id, $item->prescription_id));
    }

    public function test_prescription_status_values(): void
    {
        $statuses = ['pending', 'dispensed', 'partially_dispensed', 'cancelled'];

        foreach ($statuses as $status) {
            $prescription = Prescription::factory()->create(['status' => $status]);
            $this->assertEquals($status, $prescription->status);
        }
    }

    public function test_prescription_void_fields_work(): void
    {
        $prescription = Prescription::factory()->voided()->create();

        $this->assertTrue($prescription->is_voided);
        $this->assertNotNull($prescription->void_reason);
        $this->assertNotNull($prescription->voided_by);
    }

    public function test_prescription_global_scope_excludes_voided_by_default(): void
    {
        Prescription::factory()->create(['is_voided' => false]);
        Prescription::factory()->create(['is_voided' => true]);

        $this->assertCount(1, Prescription::all());
        $this->assertCount(2, Prescription::withVoided()->get());
    }

    public function test_prescription_search_by_patient_name(): void
    {
        $patient = Patient::factory()->create([
            'user_id' => User::factory()->create(['first_name' => 'Robert', 'last_name' => 'Johnson'])->user_id,
        ]);
        $prescription = Prescription::factory()->create(['patient_id' => $patient->patient_id]);

        $results = Prescription::searchByPatientName('Robert')->get();
        $this->assertCount(1, $results);

        $results = Prescription::searchByPatientName('Johnson')->get();
        $this->assertCount(1, $results);
    }

    public function test_prescription_service_parse_frequency_to_daily(): void
    {
        $this->assertEquals(1, \App\Services\PrescriptionService::parseFrequencyToDaily('once daily'));
        $this->assertEquals(2, \App\Services\PrescriptionService::parseFrequencyToDaily('twice daily'));
        $this->assertEquals(3, \App\Services\PrescriptionService::parseFrequencyToDaily('three times daily'));
        $this->assertEquals(4, \App\Services\PrescriptionService::parseFrequencyToDaily('four times daily'));
        $this->assertEquals(4, \App\Services\PrescriptionService::parseFrequencyToDaily('every 6 hours'));
        $this->assertEquals(3, \App\Services\PrescriptionService::parseFrequencyToDaily('every 8 hours'));
        $this->assertEquals(1, \App\Services\PrescriptionService::parseFrequencyToDaily('at bedtime'));
        $this->assertEquals(1, \App\Services\PrescriptionService::parseFrequencyToDaily('as needed'));
        $this->assertEquals(1, \App\Services\PrescriptionService::parseFrequencyToDaily('unknown'));
    }
}