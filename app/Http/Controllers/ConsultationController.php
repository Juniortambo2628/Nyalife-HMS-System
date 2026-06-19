<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Http\Resources\ConsultationResource;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;
use App\Support\PatientId;
use App\Support\Permissions;

class ConsultationController extends Controller
{
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
            ->when($user && $user->role === 'doctor', function($q) use ($user) {
                $staff = Staff::where('user_id', $user->user_id)->first();
                return $staff ? $q->where('doctor_id', $staff->staff_id) : $q;
            })
            ->when($user && $user->role === 'patient', function($q) use ($user) {
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
        
        \Illuminate\Support\Facades\Log::info('Consultation Create Request', [
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
            'url' => $request->fullUrl()
        ]);

        // Auto-redirect if an active draft already exists for this patient/appointment to prevent duplication
        if ($appointmentId || $patientId) {
            $existingDraft = Consultation::where('consultation_status', 'in_progress')
                ->when($appointmentId, function($q) use ($appointmentId) {
                    return $q->where('appointment_id', $appointmentId);
                })
                ->when($patientId && !$appointmentId, function($q) use ($patientId) {
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
        if (!$doctorId && Auth::user()->role === 'doctor') {
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
            $latestVitals = \App\Models\Vital::where('patient_id', $patientId)
                ->where('measured_at', '>=', now()->subHours(24))
                ->latest('measured_at')
                ->first();
            
            // For returning patients, get height from the most recent record ever
            $latestHeight = \App\Models\Vital::where('patient_id', $patientId)
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
            'doctors' => Staff::whereHas('user.roleRelation', function($query) {
                $query->where('role_name', 'doctor');
            })->with('user')->get()->map(function($s) {
                 return [
                    'value' => $s->staff_id,
                    'label' => 'Dr. ' . ($s->user->last_name ?? 'Unknown')
                 ];
            }),
            'drafts' => ConsultationResource::collection($this->getActiveDrafts()),
            'appointment' => $appointment,
            'medical_procedures' => \App\Models\MedicalProcedure::where('is_active', true)->orderBy('name')->get(),
            'lab_test_types' => \App\Models\LabTestType::labTests()->active()->orderBy('category')->orderBy('test_name')->get(),
            'procedure_services' => \App\Models\LabTestType::services()->active()->orderBy('category')->orderBy('test_name')->get(),
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
        \Illuminate\Support\Facades\Log::info('Consultation Store Attempt', [
            'data' => $request->all(),
            'user' => Auth::id()
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

            // PHASE 3: AUTOMATED INVOICING ENGINE
            $invoice = \App\Models\Invoice::create([
                'patient_id' => $data['patient_id'],
                'consultation_id' => $consultation->consultation_id,
                'invoice_number' => 'INV-' . strtoupper(substr(uniqid(), -6)),
                'invoice_date' => now(),
                'due_date' => now()->addDays(7), // Default 7-day due date
                'status' => 'unpaid',
                'total_amount' => 0, 
                'created_by' => Auth::id()
            ]);

            $totalAmount = 0;

            // 1. Base Consultation Fee Subtotal
            $baseFee = \App\Models\MedicalProcedure::where('category', 'consultation')->first();
            if ($baseFee) {
                 \App\Models\InvoiceItem::create([
                     'invoice_id' => $invoice->invoice_id,
                     'item_type' => 'consultation',
                     'item_id_ref' => $baseFee->procedure_id,
                     'description' => 'Doctor Consultation: ' . $baseFee->name,
                     'quantity' => 1,
                     'unit_price' => $baseFee->standard_fee,
                     'total_price' => $baseFee->standard_fee
                 ]);
                 $totalAmount += $baseFee->standard_fee;
            }

            // 2. Aggregate Requested Surgeries & Procedures
            if (!empty($data['requested_procedures'])) {
                foreach ($data['requested_procedures'] as $proc) {
                    $fee = isset($proc['standard_fee']) ? $proc['standard_fee'] : 0;
                    \App\Models\InvoiceItem::create([
                        'invoice_id' => $invoice->invoice_id,
                        'item_type' => 'procedure',
                        'item_id_ref' => $proc['procedure_id'] ?? null,
                        'description' => $proc['name'] ?? 'Procedure',
                        'quantity' => 1,
                        'unit_price' => $fee,
                        'total_price' => $fee
                    ]);
                    $totalAmount += $fee;
                }
            }

            // 3. Aggregate Requested Labs & Farm out Orders to Laboratory
            if (!empty($data['requested_labs'])) {
                foreach ($data['requested_labs'] as $lab) {
                    $labTypeId = $lab['test_type_id'] ?? $lab['lab_test_type_id'] ?? null;
                    $labType = $labTypeId ? \App\Models\LabTestType::find($labTypeId) : null;
                    $fee = $labType ? $labType->price : (isset($lab['price']) ? $lab['price'] : 0);
                    
                    \App\Models\InvoiceItem::create([
                        'invoice_id' => $invoice->invoice_id,
                        'item_type' => 'lab_test',
                        'item_id_ref' => $labTypeId,
                        'description' => 'Lab: ' . ($labType->test_name ?? $lab['test_name'] ?? 'Diagnostics'),
                        'quantity' => 1,
                        'unit_price' => $fee,
                        'total_price' => $fee
                    ]);
                    $totalAmount += $fee;
                    
                    \App\Models\LabTestRequest::create([
                        'request_number' => 'LAB-' . strtoupper(substr(uniqid(), -6)),
                        'consultation_id' => $consultation->consultation_id,
                        'patient_id' => $data['patient_id'],
                        'requested_by' => Auth::id(),
                        'test_type_id' => $labTypeId,
                        'status' => 'pending',
                        'request_date' => now(),
                        'notes' => 'Auto-requested via consultation',
                        'priority' => $data['priority'] ?? 'routine'
                    ]);
                }
            }

            // 4. Aggregate Requested Service/Procedure Items from LabTestType
            if (!empty($data['requested_service_items'])) {
                foreach ($data['requested_service_items'] as $svc) {
                    $svcTypeId = $svc['test_type_id'] ?? null;
                    $svcType = $svcTypeId ? \App\Models\LabTestType::find($svcTypeId) : null;
                    $fee = $svcType ? $svcType->price : (isset($svc['price']) ? $svc['price'] : 0);
                    
                    \App\Models\InvoiceItem::create([
                        'invoice_id' => $invoice->invoice_id,
                        'item_type' => 'service',
                        'item_id_ref' => $svcTypeId,
                        'description' => ($svcType->test_name ?? $svc['test_name'] ?? 'Service'),
                        'quantity' => 1,
                        'unit_price' => $fee,
                        'total_price' => $fee
                    ]);
                    $totalAmount += $fee;
                }
            }

            $invoice->update(['total_amount' => $totalAmount]);

            // Update appointment status if linked
            if (!empty($data['appointment_id'])) {
                Appointment::where('appointment_id', $data['appointment_id'])
                    ->update(['status' => 'completed']);
            }

            DB::commit();

            ActivityLogger::log(
                'consultations',
                "Consultation " . ($data['consultation_status'] === 'in_progress' ? 'started' : 'concluded') . " for " . ($consultation->patient->user->full_name ?? 'Patient'),
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
            return back()->withErrors(['error' => 'Failed to create consultation: ' . $e->getMessage()]);
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
            'consultation' => ConsultationResource::make($consultation)
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

        $settings = \App\Models\Setting::whereIn('key', [
            'contact_address', 'contact_email', 'contact_phone',
        ])->pluck('value', 'key');

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
            'invoices.items'
        ])->findOrFail($id);
        
        return Inertia::render('Consultations/Edit', [
            'consultation' => ConsultationResource::make($consultation),
             'patients' => Patient::with('user')->get()->map(function($p) {
                return [
                    'value' => $p->patient_id,
                    'label' => $p->user->first_name . ' ' . $p->user->last_name
                ];
            }),
            'doctors' => Staff::whereHas('user.roleRelation', function($query) {
                $query->where('role_name', 'doctor');
            })->with('user')->get()->map(function($s) {
                 return [
                    'value' => $s->staff_id,
                    'label' => 'Dr. ' . ($s->user->last_name ?? 'Unknown')
                 ];
            }),
            'drafts' => ConsultationResource::collection($this->getActiveDrafts()),
            'medical_procedures' => \App\Models\MedicalProcedure::where('is_active', true)->orderBy('name')->get(),
            'medications' => \App\Models\Medication::orderBy('medication_name')->get(),
            'lab_test_types' => \App\Models\LabTestType::labTests()->active()->orderBy('category')->orderBy('test_name')->get(),
            'procedure_services' => \App\Models\LabTestType::services()->active()->orderBy('category')->orderBy('test_name')->get(),
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
            $patient_id = $consultation->patient_id;
            $invoice = \App\Models\Invoice::withoutGlobalScope('not_voided')
                ->where('consultation_id', $consultation->consultation_id)
                ->where('is_voided', false)
                ->first();

            // Track existing items to avoid duplicates
            $existingLabTypeIds = \App\Models\LabTestRequest::where('consultation_id', $consultation->consultation_id)
                ->pluck('test_type_id')->filter()->values()->toArray();
            $existingProcedureIds = \App\Models\InvoiceItem::where('invoice_id', optional($invoice)->invoice_id)
                ->where('item_type', 'procedure')->pluck('item_id_ref')->toArray();

            // Create new lab requests
            if (!empty($data['requested_labs'])) {
                foreach ($data['requested_labs'] as $lab) {
                    $labTypeId = $lab['test_type_id'] ?? null;
                    if (!$labTypeId || in_array($labTypeId, $existingLabTypeIds)) {
                        continue;
                    }
                    $labType = \App\Models\LabTestType::find($labTypeId);

                    \App\Models\LabTestRequest::create([
                        'request_number' => 'LAB-' . strtoupper(substr(uniqid(), -6)),
                        'consultation_id' => $consultation->consultation_id,
                        'patient_id' => $patient_id,
                        'requested_by' => Auth::id(),
                        'test_type_id' => $labTypeId,
                        'status' => 'pending',
                        'request_date' => now(),
                        'notes' => 'Requested during consultation edit',
                        'priority' => $data['priority'] ?? 'routine'
                    ]);

                    if ($invoice && $labType) {
                        \App\Models\InvoiceItem::create([
                            'invoice_id' => $invoice->invoice_id,
                            'item_type' => 'lab_test',
                            'item_id_ref' => $labTypeId,
                            'description' => 'Lab: ' . $labType->test_name,
                            'quantity' => 1,
                            'unit_price' => $labType->price ?? 0,
                            'total_price' => $labType->price ?? 0,
                        ]);
                        $invoice->increment('total_amount', $labType->price ?? 0);
                    }
                }
            }

            // Create new service item requests
            if (!empty($data['requested_service_items'])) {
                $existingServiceTypeIds = \App\Models\InvoiceItem::where('invoice_id', optional($invoice)->invoice_id)
                    ->where('item_type', 'service')->pluck('item_id_ref')->toArray();

                foreach ($data['requested_service_items'] as $svc) {
                    $svcTypeId = $svc['test_type_id'] ?? null;
                    if (!$svcTypeId || in_array($svcTypeId, $existingServiceTypeIds)) {
                        continue;
                    }
                    $svcType = \App\Models\LabTestType::find($svcTypeId);

                    if ($invoice && $svcType) {
                        \App\Models\InvoiceItem::create([
                            'invoice_id' => $invoice->invoice_id,
                            'item_type' => 'service',
                            'item_id_ref' => $svcTypeId,
                            'description' => $svcType->test_name ?? 'Service',
                            'quantity' => 1,
                            'unit_price' => $svcType->price ?? 0,
                            'total_price' => $svcType->price ?? 0,
                        ]);
                        $invoice->increment('total_amount', $svcType->price ?? 0);
                    }
                }
            }

            // Create new procedure requests
            if (!empty($data['requested_procedures'])) {
                foreach ($data['requested_procedures'] as $proc) {
                    $procId = $proc['procedure_id'] ?? null;
                    if (!$procId || in_array($procId, $existingProcedureIds)) {
                        continue;
                    }
                    $procedure = \App\Models\MedicalProcedure::find($procId);

                    if ($invoice && $procedure) {
                        \App\Models\InvoiceItem::create([
                            'invoice_id' => $invoice->invoice_id,
                            'item_type' => 'procedure',
                            'item_id_ref' => $procId,
                            'description' => $procedure->name,
                            'quantity' => 1,
                            'unit_price' => $procedure->standard_fee ?? 0,
                            'total_price' => $procedure->standard_fee ?? 0,
                        ]);
                        $invoice->increment('total_amount', $procedure->standard_fee ?? 0);
                    }
                }
            }

            // Create new prescriptions
            if (!empty($data['requested_prescriptions'])) {
                $prescription = \App\Models\Prescription::create([
                    'consultation_id' => $consultation->consultation_id,
                    'patient_id' => $patient_id,
                    'prescribed_by' => Auth::id(),
                    'prescription_date' => now(),
                    'status' => 'pending',
                    'notes' => 'Prescribed during consultation edit',
                ]);

                foreach ($data['requested_prescriptions'] as $rx) {
                    $medId = $rx['medication_id'] ?? null;
                    $medication = $medId ? \App\Models\Medication::find($medId) : null;

                    \App\Models\PrescriptionItem::create([
                        'prescription_id' => $prescription->prescription_id,
                        'medication_id' => $medId,
                        'dosage' => $rx['dosage'] ?? '',
                        'frequency' => $rx['frequency'] ?? '',
                        'quantity' => 1,
                        'duration' => $rx['duration'] ?? '',
                        'instructions' => $rx['instructions'] ?? '',
                    ]);

                    if ($invoice && $medication) {
                        \App\Models\InvoiceItem::create([
                            'invoice_id' => $invoice->invoice_id,
                            'item_type' => 'medication',
                            'item_id_ref' => $medId,
                            'description' => 'Rx: ' . $medication->medication_name . ' ' . ($medication->strength ?? ''),
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
                "Consultation " . ($status === 'in_progress' ? 'updated' : 'concluded') . " for " . ($patientUser->full_name ?? 'Patient'),
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
            return back()->withErrors(['error' => 'Failed to update consultation: ' . $e->getMessage()]);
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
            ->when($user && $user->role === 'doctor', function($q) use ($user) {
                $staff = Staff::where('user_id', $user->user_id)->first();
                return $staff ? $q->where('doctor_id', $staff->staff_id) : $q;
            })
            ->when($user && $user->role === 'patient', function($q) use ($user) {
                $patient = Patient::where('user_id', $user->user_id)->first();
                return $patient ? $q->where('patient_id', $patient->patient_id) : $q;
            })
            ->latest()
            ->get();
    }

    /**
     * Handle bulk actions on consultations.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:mark_complete,delete,export',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
        ]);

        $ids    = $validated['ids'];
        $action = $validated['action'];
        $count  = count($ids);

        switch ($action) {
            case 'mark_complete':
                Consultation::whereIn('consultation_id', $ids)
                    ->update(['consultation_status' => 'completed']);
                return redirect()->back()->with('success', "{$count} consultation(s) marked as complete.");

            case 'delete':
                Consultation::whereIn('consultation_id', $ids)->delete();
                return redirect()->back()->with('success', "{$count} consultation(s) deleted.");

            case 'export':
                // Handled client-side; backend fallback
                return redirect()->back()->with('success', "{$count} records flagged for export.");
        }

        return redirect()->back()->with('error', 'Unknown bulk action.');
    }
}
