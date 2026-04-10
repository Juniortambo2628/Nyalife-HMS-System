<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Prescription::with(['patient.user', 'items']);
        
        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->patient_id);
            }
        } elseif ($user && $user->role === 'doctor') {
             $query->where('prescribed_by', $user->user_id);
        }

        $prescriptions = $query
            ->searchByPatientName($request->search)
            ->status($request->status)
            ->latest()
            ->paginate(15);

        return Inertia::render('Prescriptions/Index', [
            'prescriptions' => PrescriptionResource::collection($prescriptions),
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function create(Request $request)
    {
        $patientId = $request->query('patient_id');
        $consultationId = $request->query('consultation_id');
        $patient = $patientId ? Patient::with('user')->find($patientId) : null;

        return Inertia::render('Prescriptions/Create', [
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => $patient ? ($patient->user->first_name . ' ' . $patient->user->last_name) : null,
            'consultation_id' => $consultationId
        ]);
    }

    public function store(StorePrescriptionRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // 1) Create the Prescription record
            $prescription = Prescription::create([
                'patient_id' => $validated['patient_id'],
                'consultation_id' => $validated['consultation_id'] ?? null,
                'prescribed_by' => Auth::id(),
                'prescription_date' => $validated['prescription_date'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'prescription_number' => 'RX-' . strtoupper(uniqid())
            ]);

            // 2) Create prescription items + deduct stock + collect invoice line items
            $invoiceItems = [];
            foreach ($validated['items'] as $item) {
                $prescription->items()->create([
                    'medication_id' => $item['medication_id'] ?? null,
                    'dosage' => $item['dosage'],
                    'frequency' => $item['frequency'],
                    'duration' => $item['duration']
                ]);

                // If medication_id is set, deduct stock and prepare invoice line
                if (!empty($item['medication_id'])) {
                    $medication = Medication::find($item['medication_id']);
                    if ($medication) {
                        // Calculate quantity needed (duration days x frequency per day, default 1)
                        $freqNum = $this->parseFrequencyToDaily($item['frequency'] ?? '');
                        $durationDays = (int) ($item['duration'] ?? 1);
                        $quantityNeeded = max(1, $freqNum * $durationDays);

                        // Deduct stock (floor at zero)
                        $deduction = min($quantityNeeded, $medication->stock_quantity);
                        if ($deduction > 0) {
                            $medication->decrement('stock_quantity', $deduction);
                        }

                        // Prepare invoice line item
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

            // 3) Auto-generate Invoice if there are billable items
            if (count($invoiceItems) > 0) {
                $totalAmount = array_sum(array_column($invoiceItems, 'total_price'));

                $invoice = Invoice::create([
                    'patient_id' => $validated['patient_id'],
                    'consultation_id' => $validated['consultation_id'] ?? null,
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

            DB::commit();

            return redirect()->route('prescriptions.index')->with('success', 'Prescription created successfully. Invoice auto-generated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process prescription: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $prescription = Prescription::with(['patient.user', 'items.medication', 'doctor'])->findOrFail($id);
        return Inertia::render('Prescriptions/Show', [
            'prescription' => PrescriptionResource::make($prescription)
        ]);
    }

    /**
     * Parse a frequency string like "3 times daily", "twice daily", "BD", "TDS" into a numeric daily count.
     */
    private function parseFrequencyToDaily(string $frequency): int
    {
        $freq = strtolower(trim($frequency));

        // Common abbreviations
        $map = [
            'od' => 1, 'once daily' => 1, 'daily' => 1,
            'bd' => 2, 'bid' => 2, 'twice daily' => 2, '2 times daily' => 2,
            'tds' => 3, 'tid' => 3, 'three times daily' => 3, '3 times daily' => 3,
            'qds' => 4, 'qid' => 4, 'four times daily' => 4, '4 times daily' => 4,
            'stat' => 1, 'prn' => 1, 'as needed' => 1,
        ];

        if (isset($map[$freq])) {
            return $map[$freq];
        }

        // Try to extract number
        if (preg_match('/(\d+)/', $freq, $matches)) {
            return (int) $matches[1];
        }

        return 1; // Default
    }
}
