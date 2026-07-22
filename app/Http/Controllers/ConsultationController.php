<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Http\Resources\ConsultationResource;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabTestType;
use App\Models\MedicalProcedure;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vital;
use App\Services\ActivityLogger;
use App\Services\ConsultationInvoiceService;
use App\Support\PatientId;
use App\Support\Permissions;
use App\Traits\HasBulkActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ConsultationController extends Controller
{
    use HasBulkActions;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Consultation::with([
            'patient.user',
            'doctor.user',
            'prescriptions',
            'labTestRequests.testType',
        ]);

        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->patient_id);
            }
        } elseif ($user && $user->role === 'doctor') {
            $staff = Staff::where('user_id', $user->user_id)->first();
            if ($staff) {
                $query->where('doctor_id', $staff->staff_id);
            }
        }

        if ($request->has('quick_filter') && $request->quick_filter) {
            switch ($request->quick_filter) {
                case 'in_progress':
                    $query->where('consultation_status', 'in_progress');
                    break;
                case 'completed':
                    $query->where('consultation_status', 'completed');
                    break;
                case 'walk_in':
                    $query->where('is_walk_in', true);
                    break;
            }
        }

        $consultations = $query
            ->searchByPatientOrDiagnosis($request->search)
            ->forDoctor($request->doctor_id)
            ->when($request->patient_id, fn ($q) => $q->where('patient_id', $request->patient_id))
            ->orderBy('consultation_date', 'desc')
            ->paginate(15);

        $activeDrafts = Consultation::with(['patient.user', 'doctor.user'])
            ->where('consultation_status', 'in_progress')
            ->when($user && $user->role === 'doctor', function ($q) use ($user) {
                $staff = Staff::where('user_id', $user->user_id)->first();

                return $staff ? $q->where('doctor_id', $staff->staff_id) : $q;
            })
            ->when($user && $user->role === 'patient', function ($q) use ($user) {
                $patient = Patient::where('user_id', $user->user_id)->first();

                return $patient ? $q->where('patient_id', $patient->patient_id) : $q;
            })
            ->latest()
            ->get();

        $stats = [
            'total' => Consultation::count(),
            'in_progress' => Consultation::where('consultation_status', 'in_progress')->count(),
            'completed' => Consultation::where('consultation_status', 'completed')->count(),
            'today' => Consultation::whereDate('consultation_date', today())->count(),
        ];

        return Inertia::render('Consultations/Index', [
            'consultations' => ConsultationResource::collection($consultations),
            'drafts' => ConsultationResource::collection($this->getActiveDrafts()),
            'filters' => $request->only(['search', 'doctor_id', 'patient_id', 'quick_filter']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $appointmentId = $request->query('appointment_id');
        $patientId = $request->query('patient_id');

        Log::info('Consultation Create Request', [
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
            'url' => $request->fullUrl(),
        ]);

        // Auto-redirect if an active draft already exists for this patient/appointment to prevent duplication
        if ($appointmentId || $patientId) {
            $existingDraft = Consultation::where('consultation_status', 'in_progress')
                ->when($appointmentId, function ($q) use ($appointmentId) {
                    return $q->where('appointment_id', $appointmentId);
                })
                ->when($patientId && ! $appointmentId, function ($q) use ($patientId) {
                    return $q->where('patient_id', $patientId);
                })
                ->first();

            if ($existingDraft) {
                return redirect()->route('consultations.edit', $existingDraft->consultation_id)
                    ->with('info', 'Redirected to resume active assessment draft.');
            }
        }

        $appointment = null;
        $patient = null;
        $doctorId = null;

        if ($appointmentId) {
            $appointment = Appointment::with('patient.user')->find($appointmentId);
            if ($appointment) {
                $patient = $appointment->patient;
                $patientId = $appointment->patient_id;
                $doctorId = $appointment->doctor_id;
            }
        } elseif ($patientId) {
            $patient = Patient::with('user')->find($patientId);
        }

        // If no doctor from appointment, and current user is a doctor, prefill with current user
        if (! $doctorId && Auth::user()->role === 'doctor') {
            $staff = Staff::where('user_id', Auth::id())->first();
            if ($staff) {
                $doctorId = $staff->staff_id;
            }
        }

        $latestVitals = null;
        $latestHeight = null;
        $historyPrefill = null;
        $patientClinical = null;

        if ($patientId) {
            $latestVitals = Vital::where('patient_id', $patientId)
                ->where('measured_at', '>=', now()->subHours(24))
                ->latest('measured_at')
                ->first();

            // For returning patients, get height from the most recent record ever
            $latestHeight = Vital::where('patient_id', $patientId)
                ->whereNotNull('height')
                ->latest('measured_at')
                ->value('height');

            $previousConsultation = Consultation::latestHistoryForPatient((int) $patientId);
            if ($previousConsultation) {
                $historyPrefill = $previousConsultation->toHistoryPrefill();
                $historyPrefill['source_consultation_id'] = $previousConsultation->consultation_id;
                $historyPrefill['source_consultation_date'] = $previousConsultation->consultation_date?->format('Y-m-d');
            }

            if ($patient) {
                $patientClinical = [
                    'allergies' => $patient->allergies,
                    'chronic_diseases' => $patient->chronic_diseases,
                ];
            }
        }

        return Inertia::render('Consultations/Create', [
            'appointment_id' => $appointmentId,
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => PatientId::fromPatient($patient) ?: null,
            'preselected_patient_gender' => $patient ? $patient->user->gender : null,
            'preselected_doctor_id' => $doctorId,
            'latest_height' => $latestHeight,
            'priority' => $request->query('priority', 'normal'),
            // Link doctors to users for the dropdown
            'doctors' => Staff::whereHas('user.roleRelation', function ($query) {
                $query->where('role_name', 'doctor');
            })->with('user')->get()->map(function ($s) {
                return [
                    'value' => $s->staff_id,
                    'label' => 'Dr. '.($s->user->last_name ?? 'Unknown'),
                ];
            }),
            'drafts' => ConsultationResource::collection($this->getActiveDrafts()),
            'appointment' => $appointment,
            'medical_procedures' => MedicalProcedure::where('is_active', true)->orderBy('name')->get(),
            'lab_test_types' => LabTestType::labTests()->active()->orderBy('category')->orderBy('test_name')->get(),
            'procedure_services' => LabTestType::services()->active()->orderBy('category')->orderBy('test_name')->get(),
            'latest_vitals' => $latestVitals,
            'history_prefill' => $historyPrefill,
            'patient_clinical' => $patientClinical,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsultationRequest $request)
    {
        Log::info('Consultation Store Attempt', [
            'data' => $request->all(),
            'user' => Auth::id(),
        ]);

        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Ensure non-null values for text fields that might be NOT NULL in DB
            $data['diagnosis'] = $data['diagnosis'] ?? '';
            $data['treatment_plan'] = $data['treatment_plan'] ?? '';
            $data['follow_up_instructions'] = $data['follow_up_instructions'] ?? '';
            $data['notes'] = $data['notes'] ?? '';

            // Map 'status' to 'consultation_status' if specific name used in legacy
            $data['consultation_status'] = $data['status'];
            $data['created_by'] = Auth::id();

            // Handle walk-in logic
            $data['is_walk_in'] = $request->boolean('is_walk_in');
            if ($data['is_walk_in']) {
                $data['appointment_id'] = null;
            }

            $consultation = Consultation::create($data);

            ConsultationInvoiceService::createForConsultation($data, $consultation->consultation_id);

            // Update appointment status if linked
            if (! empty($data['appointment_id'])) {
                Appointment::where('appointment_id', $data['appointment_id'])
                    ->update(['status' => 'completed']);
            }

            DB::commit();

            ActivityLogger::log(
                'consultations',
                'Consultation '.($data['consultation_status'] === 'in_progress' ? 'started' : 'concluded').' for '.($consultation->patient->user->full_name ?? 'Patient'),
                ['consultation_id' => $consultation->consultation_id, 'status' => $data['consultation_status']],
                Auth::user(),
                $consultation,
                [$consultation->patient->user_id, 1]
            );

            if ($data['consultation_status'] === 'in_progress') {
                return redirect()->route('consultations.edit', $consultation->consultation_id)
                    ->with('success', 'Consultation saved progressively. Labs requested and invoice generated.');
            }

            return redirect()->route('dashboard')->with('success', 'Consultation completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Consultation store failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['password']),
                'user_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => 'Failed to create consultation: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $consultation = Consultation::with([
            'patient.user',
            'doctor.user',
            'appointment',
            'prescriptions.items.medication',
            'labTestRequests.testType',
            'labTestRequests.assignedTo',
            'invoices.items',
            'invoices.payments',
            'followUps',
        ])->findOrFail($id);

        $this->requireStaffOrOwnPatient(
            $consultation->patient_id,
            Permissions::MANAGE_CONSULTATIONS
        );

        return Inertia::render('Consultations/View', [
            'consultation' => ConsultationResource::make($consultation),
        ]);
    }

    public function print($id)
    {
        $consultation = Consultation::with(['patient.user', 'doctor.user'])
            ->findOrFail($id);

        $this->requireStaffOrOwnPatient(
            $consultation->patient_id,
            Permissions::MANAGE_CONSULTATIONS
        );

        $settings = Setting::clinicContactSettings();

        return Inertia::render('Consultations/Print', [
            'consultation' => ConsultationResource::make($consultation),
            'clinic_settings' => $settings,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = Auth::user();
        if ($user && in_array($user->role, ['nurse', 'receptionist', 'lab_technician', 'patient'])) {
            abort(403, 'Unauthorized editing of consultation records.');
        }

        $consultation = Consultation::with([
            'patient.user',
            'labTestRequests.testType',
            'labTestRequests.assignedTo',
            'prescriptions.items.medication',
            'invoices.items',
        ])->findOrFail($id);

        return Inertia::render('Consultations/Edit', [
            'consultation' => ConsultationResource::make($consultation),
            'patients' => Patient::with('user')->get()->map(function ($p) {
                return [
                    'value' => $p->patient_id,
                    'label' => $p->user->first_name.' '.$p->user->last_name,
                ];
            }),
            'doctors' => Staff::whereHas('user.roleRelation', function ($query) {
                $query->where('role_name', 'doctor');
            })->with('user')->get()->map(function ($s) {
                return [
                    'value' => $s->staff_id,
                    'label' => 'Dr. '.($s->user->last_name ?? 'Unknown'),
                ];
            }),
            'drafts' => ConsultationResource::collection($this->getActiveDrafts()),
            'medical_procedures' => MedicalProcedure::where('is_active', true)->orderBy('name')->get(),
            'medications' => Medication::orderBy('medication_name')->get(),
            'lab_test_types' => LabTestType::labTests()->active()->orderBy('category')->orderBy('test_name')->get(),
            'procedure_services' => LabTestType::services()->active()->orderBy('category')->orderBy('test_name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConsultationRequest $request, $id)
    {
        $user = Auth::user();
        if ($user && in_array($user->role, ['nurse', 'receptionist', 'lab_technician', 'patient'])) {
            abort(403, 'Unauthorized editing of consultation records.');
        }

        $consultation = Consultation::findOrFail($id);
        $data = $request->validated();

        // Ensure non-null values for text fields that might be NOT NULL in DB
        $data['diagnosis'] = $data['diagnosis'] ?? '';
        $data['treatment_plan'] = $data['treatment_plan'] ?? '';
        $data['follow_up_instructions'] = $data['follow_up_instructions'] ?? '';
        $data['notes'] = $data['notes'] ?? '';

        // Map status to consultation_status
        if (isset($data['status'])) {
            $data['consultation_status'] = $data['status'];
        }

        DB::beginTransaction();
        try {
            $consultation->update($data);

            // Process any NEW items added during this edit session
            $invoice = Invoice::withoutGlobalScope('not_voided')
                ->where('consultation_id', $consultation->consultation_id)
                ->where('is_voided', false)
                ->first();

            if ($invoice) {
                ConsultationInvoiceService::addNewItemsToExisting(
                    $invoice,
                    $data,
                    $consultation->consultation_id
                );
            }

            // Create new prescriptions
            if (! empty($data['requested_prescriptions'])) {
                $prescription = Prescription::create([
                    'consultation_id' => $consultation->consultation_id,
                    'patient_id' => $consultation->patient_id,
                    'prescribed_by' => Auth::id(),
                    'prescription_date' => now(),
                    'status' => 'pending',
                    'notes' => 'Prescribed during consultation edit',
                ]);

                foreach ($data['requested_prescriptions'] as $rx) {
                    $medId = $rx['medication_id'] ?? null;
                    $medication = $medId ? Medication::find($medId) : null;

                    PrescriptionItem::create([
                        'prescription_id' => $prescription->prescription_id,
                        'medication_id' => $medId,
                        'dosage' => $rx['dosage'] ?? '',
                        'frequency' => $rx['frequency'] ?? '',
                        'quantity' => 1,
                        'duration' => $rx['duration'] ?? '',
                        'instructions' => $rx['instructions'] ?? '',
                    ]);

                    if ($invoice && $medication) {
                        InvoiceItem::create([
                            'invoice_id' => $invoice->invoice_id,
                            'item_type' => 'medication',
                            'item_id_ref' => $medId,
                            'description' => 'Rx: '.$medication->medication_name.' '.($medication->strength ?? ''),
                            'quantity' => 1,
                            'unit_price' => $medication->price_per_unit ?? 0,
                            'total_price' => $medication->price_per_unit ?? 0,
                        ]);
                        $invoice->increment('total_amount', $medication->price_per_unit ?? 0);
                    }
                }
            }

            $status = $data['consultation_status'] ?? $consultation->consultation_status;

            $patientUser = $consultation->patient->user ?? null;
            $patientUserId = $patientUser->user_id ?? null;
            ActivityLogger::log(
                'consultations',
                'Consultation '.($status === 'in_progress' ? 'updated' : 'concluded').' for '.($patientUser->full_name ?? 'Patient'),
                ['consultation_id' => $consultation->consultation_id, 'status' => $status],
                Auth::user(),
                $consultation,
                [$patientUserId, 1]
            );

            DB::commit();

            if ($status === 'in_progress') {
                return redirect()->back()->with('success', 'Progress saved successfully.');
            }

            return redirect()->route('dashboard')->with('success', 'Consultation concluded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Consultation update failed', [
                'consultation_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['password']),
                'user_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => 'Failed to update consultation: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $consultation = Consultation::findOrFail($id);
        $consultation->delete();

        return redirect()->route('consultations.index')->with('success', 'Consultation deleted successfully.');
    }

    /**
     * Get active in-progress drafts for the current user context.
     */
    private function getActiveDrafts()
    {
        $user = Auth::user();

        return Consultation::with(['patient.user', 'doctor.user'])
            ->where('consultation_status', 'in_progress')
            ->when($user && $user->role === 'doctor', function ($q) use ($user) {
                $staff = Staff::where('user_id', $user->user_id)->first();

                return $staff ? $q->where('doctor_id', $staff->staff_id) : $q;
            })
            ->when($user && $user->role === 'patient', function ($q) use ($user) {
                $patient = Patient::where('user_id', $user->user_id)->first();

                return $patient ? $q->where('patient_id', $patient->patient_id) : $q;
            })
            ->latest()
            ->get();
    }

    /**
     * Handle bulk actions on consultations.
     */
    protected function bulkActionMap(): array
    {
        return [
            'mark_complete' => function (array $ids, int $count) {
                Consultation::whereIn('consultation_id', $ids)->update(['consultation_status' => 'completed']);

                return redirect()->back()->with('success', "{$count} consultation(s) marked as complete.");
            },
            'delete' => function (array $ids, int $count) {
                Consultation::whereIn('consultation_id', $ids)->delete();

                return redirect()->back()->with('success', "{$count} consultation(s) deleted.");
            },
            'export' => function (array $ids, int $count) {
                return redirect()->back()->with('success', "{$count} records flagged for export.");
            },
        ];
    }
}
