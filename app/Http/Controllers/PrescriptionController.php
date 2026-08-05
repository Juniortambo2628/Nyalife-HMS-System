<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Requests\VoidRequest;
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
use App\Services\ActivityLogger;
use App\Support\PatientId;
use App\Support\Permissions;
use App\Traits\HasBulkActions;

class PrescriptionController extends Controller
{
    use HasBulkActions;
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Prescription::with(['patient.user', 'doctor', 'items.medication']);
        
        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->patient_id);
            }
        } elseif ($user && $user->role === 'doctor') {
             $query->where('prescribed_by', $user->user_id);
        }

        if ($request->has('consultation_id')) {
            $query->where('consultation_id', $request->consultation_id);
        }

        if ($request->has('quick_filter') && $request->quick_filter) {
            switch ($request->quick_filter) {
                case 'today':
                    $query->whereDate('prescription_date', today());
                    break;
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'dispensed':
                    $query->where('status', 'dispensed');
                    break;
            }
        }

        $prescriptions = $query
            ->searchByPatientName($request->search)
            ->status($request->status)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Prescription::count(),
            'pending' => Prescription::where('status', 'pending')->count(),
            'dispensed' => Prescription::where('status', 'dispensed')->count(),
            'today' => Prescription::whereDate('prescription_date', today())->count(),
        ];

        return Inertia::render('Prescriptions/Index', [
            'prescriptions' => PrescriptionResource::collection($prescriptions),
            'filters' => $request->only(['search', 'status', 'quick_filter']),
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        $patientId = $request->query('patient_id');
        $consultationId = $request->query('consultation_id');
        $patient = $patientId ? Patient::with('user')->find($patientId) : null;

        return Inertia::render('Prescriptions/Create', [
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => PatientId::fromPatient($patient) ?: null,
            'consultation_id' => $consultationId
        ]);
    }

    public function store(StorePrescriptionRequest $request)
    {
        try {
            \App\Services\PrescriptionService::create($request->validated());
            return redirect()->route('prescriptions.index')->with('success', 'Prescription created successfully. Invoice auto-generated.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to process prescription: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $prescription = Prescription::with(['patient.user', 'items.medication', 'doctor'])->findOrFail($id);

        $this->requireStaffOrOwnPatient(
            $prescription->patient_id,
            Permissions::MANAGE_PRESCRIPTIONS,
            Permissions::MANAGE_PHARMACY
        );

        return Inertia::render('Prescriptions/Show', [
            'prescription' => PrescriptionResource::make($prescription)
        ]);
    }

    public function print($id)
    {
        $prescription = Prescription::with(['patient.user', 'items.medication', 'doctor'])->findOrFail($id);

        $this->requireStaffOrOwnPatient(
            $prescription->patient_id,
            Permissions::MANAGE_PRESCRIPTIONS,
            Permissions::MANAGE_PHARMACY
        );

        $settings = \App\Models\Setting::clinicContactSettings();

        return Inertia::render('Prescriptions/Print', [
            'prescription' => PrescriptionResource::make($prescription),
            'clinic_settings' => $settings,
        ]);
    }

    public function dispense(Request $request, $id)
    {
        $prescription = Prescription::findOrFail($id);
        
        if ($prescription->status !== 'pending') {
            return back()->withErrors(['error' => 'Prescription is already dispensed or cancelled.']);
        }

        \App\Services\PrescriptionService::dispense($prescription);

        return back()->with('success', 'Prescription marked as dispensed.');
    }

    public function edit($id)
    {
        $prescription = Prescription::with(['patient.user', 'items.medication', 'doctor'])->findOrFail($id);

        if ($prescription->status !== 'pending') {
            return back()->with('error', 'Only pending prescriptions can be edited.');
        }

        return Inertia::render('Prescriptions/Edit', [
            'prescription' => PrescriptionResource::make($prescription),
            'preselected_patient_id' => $prescription->patient_id,
            'preselected_patient_label' => PatientId::fromPatient($prescription->patient) ?: null,
        ]);
    }

    public function update(Request $request, $id)
    {
        $prescription = Prescription::findOrFail($id);

        if ($prescription->status !== 'pending') {
            return back()->with('error', 'Only pending prescriptions can be updated.');
        }

        $validated = $request->validate([
            'prescription_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medication_id' => 'nullable|exists:medications,medication_id',
            'items.*.dosage' => 'required|string',
            'items.*.frequency' => 'required|string',
            'items.*.duration' => 'required|string',
        ]);

        try {
            \App\Services\PrescriptionService::update($prescription, $validated);

            return redirect()->route('prescriptions.show', $prescription->prescription_id)
                ->with('success', 'Prescription updated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update prescription: ' . $e->getMessage()]);
        }
    }

    public function destroy(VoidRequest $request, $id)
    {
        $validated = $request->validated();

        $prescription = Prescription::findOrFail($id);

        $prescription->update([
            'is_voided' => true,
            'void_reason' => $validated['void_reason'],
            'voided_by' => Auth::id(),
            'voided_at' => now(),
        ]);

        ActivityLogger::log(
            'pharmacy',
            "Prescription {$prescription->prescription_number} voided: {$validated['void_reason']}",
            ['prescription_id' => $prescription->prescription_id],
            Auth::user(),
            $prescription,
            [$prescription->patient->user_id, 1]
        );

        return redirect()->route('prescriptions.index')->with('success', 'Prescription has been voided.');
    }

    /**
     * Handle bulk actions on prescriptions.
     */
    protected function bulkActionMap(): array
    {
        return [
            'dispense' => function (array $ids, int $count) {
                $updated = $this->bulkProcessWithLog(
                    Prescription::class, 'prescription_id', $ids,
                    fn ($item) => $item->status !== 'dispensed' && ! $item->is_voided,
                    fn ($item) => ['status' => 'dispensed', 'dispensed_by' => Auth::id(), 'dispensed_at' => now()],
                    'pharmacy', 'Prescription',
                    fn ($item) => [$item->patient->user_id, 1]
                );
                return redirect()->back()->with('success', "{$updated} prescription(s) dispensed.");
            },
            'void' => function (array $ids, int $count) {
                $updated = $this->bulkProcessWithLog(
                    Prescription::class, 'prescription_id', $ids,
                    fn ($item) => ! $item->is_voided && $item->status !== 'dispensed',
                    fn ($item) => ['is_voided' => true, 'void_reason' => 'Bulk voided via toolbar', 'voided_by' => Auth::id(), 'voided_at' => now()],
                    'pharmacy', 'Prescription',
                    fn ($item) => [$item->patient->user_id, 1]
                );
                return redirect()->back()->with('success', "{$updated} prescription(s) voided.");
            },
            'delete' => function (array $ids, int $count) {
                $deleted = $this->bulkDelete(Prescription::class, 'prescription_id', $ids, 'status', 'dispensed');
                return redirect()->back()->with('success', "{$deleted} prescription(s) deleted.");
            },
        ];
    }

}
