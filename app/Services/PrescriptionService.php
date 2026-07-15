<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medication;
use App\Models\Prescription;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrescriptionService
{
    public static function create(array $data): Prescription
    {
        return DB::transaction(function () use ($data) {
            $prescription = Prescription::create([
                'patient_id' => $data['patient_id'],
                'consultation_id' => $data['consultation_id'] ?? null,
                'prescribed_by' => Auth::id(),
                'prescription_date' => $data['prescription_date'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'prescription_number' => 'RX-' . strtoupper(uniqid()),
            ]);

            $invoiceItems = [];

            foreach ($data['items'] as $item) {
                $prescription->items()->create([
                    'medication_id' => $item['medication_id'] ?? null,
                    'dosage' => $item['dosage'],
                    'frequency' => $item['frequency'],
                    'duration' => $item['duration'],
                ]);

                if (!empty($item['medication_id'])) {
                    $medication = Medication::find($item['medication_id']);
                    if ($medication) {
                        $freqNum = self::parseFrequencyToDaily($item['frequency'] ?? '');
                        $durationDays = (int) ($item['duration'] ?? 1);
                        $quantityNeeded = max(1, $freqNum * $durationDays);

                        $deduction = min($quantityNeeded, $medication->stock_quantity);
                        if ($deduction > 0) {
                            $medication->decrement('stock_quantity', $deduction);
                        }

                        $invoiceItems[] = [
                            'item_type' => 'medication',
                            'item_id_ref' => $medication->medication_id,
                            'description' => "{$medication->medication_name} ({$medication->strength} {$medication->unit})",
                            'quantity' => $quantityNeeded,
                            'unit_price' => $medication->price_per_unit,
                            'total_price' => $quantityNeeded * $medication->price_per_unit,
                        ];
                    }
                }
            }

            if (count($invoiceItems) > 0) {
                $totalAmount = array_sum(array_column($invoiceItems, 'total_price'));

                $invoice = Invoice::create([
                    'patient_id' => $data['patient_id'],
                    'consultation_id' => $data['consultation_id'] ?? null,
                    'invoice_number' => 'INV-' . strtoupper(uniqid()),
                    'invoice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'created_by' => Auth::id(),
                    'notes' => "Auto-generated from prescription {$prescription->prescription_number}",
                ]);

                foreach ($invoiceItems as $lineItem) {
                    InvoiceItem::create(array_merge($lineItem, [
                        'invoice_id' => $invoice->invoice_id,
                    ]));
                }
            }

            ActivityLogger::log(
                'pharmacy',
                "New prescription created for " . ($prescription->patient->user->full_name ?? 'Patient'),
                ['prescription_id' => $prescription->prescription_id],
                Auth::user(),
                $prescription,
                [$prescription->patient->user_id, 1]
            );

            return $prescription;
        });
    }

    public static function update(Prescription $prescription, array $data): Prescription
    {
        return DB::transaction(function () use ($prescription, $data) {
            $prescription->update([
                'prescription_date' => $data['prescription_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $prescription->items()->delete();

            foreach ($data['items'] as $item) {
                $prescription->items()->create([
                    'medication_id' => $item['medication_id'] ?? null,
                    'dosage' => $item['dosage'],
                    'frequency' => $item['frequency'],
                    'duration' => $item['duration'],
                ]);
            }

            ActivityLogger::log(
                'pharmacy',
                "Prescription {$prescription->prescription_number} updated",
                ['prescription_id' => $prescription->prescription_id],
                Auth::user(),
                $prescription,
                [$prescription->patient->user_id, 1]
            );

            return $prescription;
        });
    }

    public static function dispense(Prescription $prescription): Prescription
    {
        $prescription->update([
            'status' => 'dispensed',
            'dispensed_by' => Auth::id(),
            'dispensed_at' => now(),
        ]);

        $rxLabel = $prescription->prescription_number
            ?? ('RX-' . str_pad((string) $prescription->prescription_id, 6, '0', STR_PAD_LEFT));

        ActivityLogger::log(
            'pharmacy',
            "Prescription {$rxLabel} dispensed",
            ['prescription_id' => $prescription->prescription_id],
            Auth::user(),
            $prescription,
            [$prescription->patient->user_id, 1]
        );

        return $prescription;
    }

    public static function parseFrequencyToDaily(string $frequency): int
    {
        $frequency = strtolower(trim($frequency));

        $map = [
            'once daily' => 1, 'once a day' => 1, 'daily' => 1, 'qd' => 1, 'od' => 1,
            'twice daily' => 2, 'twice a day' => 2, 'bid' => 2, 'bd' => 2,
            'three times daily' => 3, 'three times a day' => 3, 'tid' => 3, 'tds' => 3,
            'four times daily' => 4, 'four times a day' => 4, 'qid' => 4, 'qds' => 4,
            'every 4 hours' => 6, 'every 6 hours' => 4, 'every 8 hours' => 3, 'every 12 hours' => 2,
            'at bedtime' => 1, 'hs' => 1, 'prn' => 1,
        ];

        if (isset($map[$frequency])) {
            return $map[$frequency];
        }

        if (preg_match('/(\d+)\s*times?\s*(?:a\s*)?day/i', $frequency, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/every\s+(\d+)\s*hours?/i', $frequency, $m)) {
            $hours = (int) $m[1];
            return $hours > 0 ? (int) ceil(24 / $hours) : 1;
        }

        return 1;
    }
}
