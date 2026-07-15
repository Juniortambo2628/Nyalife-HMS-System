<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\MedicalProcedure;
use Illuminate\Support\Facades\Auth;

class ConsultationInvoiceService
{
    public static function createForConsultation(array $data, int $consultationId): Invoice
    {
        $invoice = Invoice::create([
            'patient_id' => $data['patient_id'],
            'consultation_id' => $consultationId,
            'invoice_number' => 'INV-' . strtoupper(substr(uniqid(), -6)),
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'unpaid',
            'total_amount' => 0,
            'created_by' => Auth::id(),
        ]);

        $totalAmount = 0;

        $totalAmount += self::addConsultationFee($invoice);
        $totalAmount += self::addProcedures($invoice, $data['requested_procedures'] ?? []);
        $totalAmount += self::addLabTests($invoice, $data, $consultationId);
        $totalAmount += self::addServiceItems($invoice, $data['requested_service_items'] ?? []);

        $invoice->update(['total_amount' => $totalAmount]);

        return $invoice;
    }

    public static function addNewItemsToExisting(Invoice $invoice, array $data, int $consultationId): void
    {
        $existingLabTypeIds = LabTestRequest::where('consultation_id', $consultationId)
            ->pluck('test_type_id')->filter()->values()->toArray();
        $existingProcedureIds = InvoiceItem::where('invoice_id', $invoice->invoice_id)
            ->where('item_type', 'procedure')->pluck('item_id_ref')->toArray();
        $existingServiceTypeIds = InvoiceItem::where('invoice_id', $invoice->invoice_id)
            ->where('item_type', 'service')->pluck('item_id_ref')->toArray();

        if (!empty($data['requested_labs'])) {
            foreach ($data['requested_labs'] as $lab) {
                $labTypeId = $lab['test_type_id'] ?? $lab['lab_test_type_id'] ?? null;
                if ($labTypeId && in_array($labTypeId, $existingLabTypeIds)) {
                    continue;
                }

                $labType = $labTypeId ? LabTestType::find($labTypeId) : null;
                $fee = $labType ? $labType->price : ($lab['price'] ?? 0);

                InvoiceItem::create([
                    'invoice_id' => $invoice->invoice_id,
                    'item_type' => 'lab_test',
                    'item_id_ref' => $labTypeId,
                    'description' => 'Lab: ' . ($labType->test_name ?? $lab['test_name'] ?? 'Diagnostics'),
                    'quantity' => 1,
                    'unit_price' => $fee,
                    'total_price' => $fee,
                ]);
                $invoice->increment('total_amount', $fee);

                LabTestRequest::create([
                    'request_number' => 'LAB-' . strtoupper(substr(uniqid(), -6)),
                    'consultation_id' => $consultationId,
                    'patient_id' => $data['patient_id'],
                    'requested_by' => Auth::id(),
                    'test_type_id' => $labTypeId,
                    'status' => 'pending',
                    'request_date' => now(),
                    'notes' => 'Auto-requested via consultation',
                    'priority' => $data['priority'] ?? 'routine',
                ]);
            }
        }

        if (!empty($data['requested_service_items'])) {
            foreach ($data['requested_service_items'] as $svc) {
                $svcTypeId = $svc['test_type_id'] ?? null;
                if ($svcTypeId && in_array($svcTypeId, $existingServiceTypeIds)) {
                    continue;
                }

                $svcType = $svcTypeId ? LabTestType::find($svcTypeId) : null;
                $fee = $svcType ? $svcType->price : ($svc['price'] ?? 0);

                InvoiceItem::create([
                    'invoice_id' => $invoice->invoice_id,
                    'item_type' => 'service',
                    'item_id_ref' => $svcTypeId,
                    'description' => $svcType->test_name ?? $svc['test_name'] ?? 'Service',
                    'quantity' => 1,
                    'unit_price' => $fee,
                    'total_price' => $fee,
                ]);
                $invoice->increment('total_amount', $fee);
            }
        }

        if (!empty($data['requested_procedures'])) {
            foreach ($data['requested_procedures'] as $proc) {
                $procId = $proc['procedure_id'] ?? null;
                if ($procId && in_array($procId, $existingProcedureIds)) {
                    continue;
                }

                $fee = $proc['standard_fee'] ?? 0;
                InvoiceItem::create([
                    'invoice_id' => $invoice->invoice_id,
                    'item_type' => 'procedure',
                    'item_id_ref' => $procId,
                    'description' => $proc['name'] ?? 'Procedure',
                    'quantity' => 1,
                    'unit_price' => $fee,
                    'total_price' => $fee,
                ]);
                $invoice->increment('total_amount', $fee);
            }
        }
    }

    private static function addConsultationFee(Invoice $invoice): float
    {
        $baseFee = MedicalProcedure::where('category', 'consultation')->first();
        if (!$baseFee) {
            return 0;
        }

        InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id,
            'item_type' => 'consultation',
            'item_id_ref' => $baseFee->procedure_id,
            'description' => 'Doctor Consultation: ' . $baseFee->name,
            'quantity' => 1,
            'unit_price' => $baseFee->standard_fee,
            'total_price' => $baseFee->standard_fee,
        ]);

        return $baseFee->standard_fee;
    }

    private static function addProcedures(Invoice $invoice, array $procedures): float
    {
        $total = 0;
        foreach ($procedures as $proc) {
            $fee = $proc['standard_fee'] ?? 0;
            InvoiceItem::create([
                'invoice_id' => $invoice->invoice_id,
                'item_type' => 'procedure',
                'item_id_ref' => $proc['procedure_id'] ?? null,
                'description' => $proc['name'] ?? 'Procedure',
                'quantity' => 1,
                'unit_price' => $fee,
                'total_price' => $fee,
            ]);
            $total += $fee;
        }
        return $total;
    }

    private static function addLabTests(Invoice $invoice, array $data, int $consultationId): float
    {
        $total = 0;
        if (empty($data['requested_labs'])) {
            return 0;
        }

        foreach ($data['requested_labs'] as $lab) {
            $labTypeId = $lab['test_type_id'] ?? $lab['lab_test_type_id'] ?? null;
            $labType = $labTypeId ? LabTestType::find($labTypeId) : null;
            $fee = $labType ? $labType->price : ($lab['price'] ?? 0);

            InvoiceItem::create([
                'invoice_id' => $invoice->invoice_id,
                'item_type' => 'lab_test',
                'item_id_ref' => $labTypeId,
                'description' => 'Lab: ' . ($labType->test_name ?? $lab['test_name'] ?? 'Diagnostics'),
                'quantity' => 1,
                'unit_price' => $fee,
                'total_price' => $fee,
            ]);
            $total += $fee;

            LabTestRequest::create([
                'request_number' => 'LAB-' . strtoupper(substr(uniqid(), -6)),
                'consultation_id' => $consultationId,
                'patient_id' => $data['patient_id'],
                'requested_by' => Auth::id(),
                'test_type_id' => $labTypeId,
                'status' => 'pending',
                'request_date' => now(),
                'notes' => 'Auto-requested via consultation',
                'priority' => $data['priority'] ?? 'routine',
            ]);
        }

        return $total;
    }

    private static function addServiceItems(Invoice $invoice, array $services): float
    {
        $total = 0;
        foreach ($services as $svc) {
            $svcTypeId = $svc['test_type_id'] ?? null;
            $svcType = $svcTypeId ? LabTestType::find($svcTypeId) : null;
            $fee = $svcType ? $svcType->price : ($svc['price'] ?? 0);

            InvoiceItem::create([
                'invoice_id' => $invoice->invoice_id,
                'item_type' => 'service',
                'item_id_ref' => $svcTypeId,
                'description' => $svcType->test_name ?? $svc['test_name'] ?? 'Service',
                'quantity' => 1,
                'unit_price' => $fee,
                'total_price' => $fee,
            ]);
            $total += $fee;
        }
        return $total;
    }
}
