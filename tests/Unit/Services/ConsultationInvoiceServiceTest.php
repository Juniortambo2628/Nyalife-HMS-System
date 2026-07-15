<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\MedicalProcedure;
use App\Models\LabTestRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConsultationInvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_for_consultation_creates_invoice(): void
    {
        $this->seed(\Database\Seeders\RolePermissionsSeeder::class);

        $patient = Patient::factory()->create();
        $consultation = Consultation::factory()->create(['patient_id' => $patient->patient_id]);
        $procedure = MedicalProcedure::factory()->create(['standard_fee' => 5000, 'name' => 'Test Procedure']);
        $labRequest = LabTestRequest::factory()->create(['patient_id' => $patient->patient_id]);
        $labRequest->testType()->update(['price' => 3000, 'test_name' => 'Test Lab']);

        $data = [
            'patient_id' => $patient->patient_id,
            'requested_procedures' => [
                ['procedure_id' => $procedure->procedure_id, 'name' => $procedure->name, 'standard_fee' => 5000],
            ],
            'requested_labs' => [
                ['test_type_id' => $labRequest->testType->test_type_id, 'test_name' => 'Test Lab', 'price' => 3000],
            ],
        ];

        $invoice = \App\Services\ConsultationInvoiceService::createForConsultation($data, $consultation->consultation_id);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($patient->patient_id, $invoice->patient_id);
        $this->assertEquals($consultation->consultation_id, $invoice->consultation_id);
        $this->assertCount(2, $invoice->items);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    public function test_add_new_items_to_existing(): void
    {
        $this->seed(\Database\Seeders\RolePermissionsSeeder::class);

        $patient = Patient::factory()->create();
        $invoice = Invoice::factory()->create(['patient_id' => $patient->patient_id, 'status' => 'pending']);
        $procedure = MedicalProcedure::factory()->create(['standard_fee' => 5000, 'name' => 'Test Procedure']);
        $consultation = Consultation::factory()->create(['patient_id' => $patient->patient_id]);

        $data = [
            'requested_procedures' => [
                ['procedure_id' => $procedure->procedure_id, 'name' => $procedure->name, 'standard_fee' => 5000],
            ],
        ];

        \App\Services\ConsultationInvoiceService::addNewItemsToExisting($invoice, $data, $consultation->consultation_id);

        $invoice->refresh();
        $this->assertCount(1, $invoice->items);
        $this->assertStringContainsString($procedure->name, $invoice->items->first()->description);
    }
}