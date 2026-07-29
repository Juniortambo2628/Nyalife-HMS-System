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
        $active = Prescription::factory()->create(['is_voided' => false]);
        $voided = Prescription::factory()->create(['is_voided' => true]);

        $nonVoidedResults = Prescription::withVoided()->where('prescription_id', $active->prescription_id)->get();
        $this->assertCount(1, $nonVoidedResults);
        $this->assertTrue((bool) $nonVoidedResults->first()->is_voided === false);

        $voidedCheck = Prescription::withVoided()->where('prescription_id', $voided->prescription_id)->first();
        $this->assertNotNull($voidedCheck);
        $this->assertTrue((bool) $voidedCheck->is_voided);

        $activeVisible = Prescription::where('prescription_id', $active->prescription_id)->first();
        $this->assertNotNull($activeVisible);

        $voidedHidden = Prescription::where('prescription_id', $voided->prescription_id)->first();
        $this->assertNull($voidedHidden, 'Voided prescription should be excluded by global scope');
    }

    public function test_prescription_search_by_patient_name(): void
    {
        $patient = Patient::factory()->create([
            'user_id' => User::factory()->create(['first_name' => 'Robert', 'last_name' => 'Johnson'])->user_id,
        ]);
        $prescription = Prescription::factory()->create(['patient_id' => $patient->patient_id]);

        $results = Prescription::searchByPatientName('Robert')->get();
        $this->assertTrue($results->contains('prescription_id', $prescription->prescription_id));

        $results = Prescription::searchByPatientName('Johnson')->get();
        $this->assertTrue($results->contains('prescription_id', $prescription->prescription_id));
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
