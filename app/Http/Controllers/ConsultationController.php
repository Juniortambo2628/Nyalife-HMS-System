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

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Consultation::with(['patient.user', 'doctor.user', 'prescriptions', 'labTestRequests']);

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

        return Inertia::render('Consultations/Index', [
            'consultations' => ConsultationResource::collection($consultations),
            'drafts' => ConsultationResource::collection($this->getActiveDrafts()),
            'filters' => $request->only(['search', 'doctor_id', 'patient_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $appointmentId = $request->query('appointment_id');
        $patientId = $request->query('patient_id');
        
        $appointment = null;
        $patient = null;
        if ($appointmentId) {
            $appointment = Appointment::with('patient.user')->find($appointmentId);
            $patient = $appointment->patient;
            $patientId = $appointment->patient_id;
        } elseif ($patientId) {
            $patient = Patient::with('user')->find($patientId);
        }

        return Inertia::render('Consultations/Create', [
            'appointment_id' => $appointmentId,
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => $patient ? ($patient->user->first_name . ' ' . $patient->user->last_name) : null,
            'preselected_patient_gender' => $patient ? $patient->user->gender : null,
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
            'lab_test_types' => \App\Models\LabTestType::whereIn('category', [
                'Hematology', 'Chemistry', 'Biochemistry', 'Microbiology', 
                'Parasitology', 'Pathology', 'Reproductive', 'Serology', 'Laboratory'
            ])->where('is_active', true)->orderBy('category')->orderBy('test_name')->get(),
            'procedure_services' => \App\Models\LabTestType::whereIn('category', [
                'Procedure', 'Imaging', 'General Services', 'Delivery',
                'Consultation', 'Antenatal', 'Family Planning', 'Immunization'
            ])->where('is_active', true)->orderBy('category')->orderBy('test_name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsultationRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $data = $request->all();
            
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
                     'item_id' => $baseFee->procedure_id,
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
                        'item_id' => $proc['procedure_id'] ?? null,
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
                        'item_id' => $labTypeId,
                        'description' => 'Lab: ' . ($labType->test_name ?? $lab['test_name'] ?? 'Diagnostics'),
                        'quantity' => 1,
                        'unit_price' => $fee,
                        'total_price' => $fee
                    ]);
                    $totalAmount += $fee;
                    
                    \App\Models\LabTestRequest::create([
                        'consultation_id' => $consultation->consultation_id,
                        'patient_id' => $data['patient_id'],
                        'requested_by' => Auth::id(),
                        'test_id' => $labTypeId,
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
                        'item_id' => $svcTypeId,
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
        $user = Auth::user();
        
        // Block receptionists from clinical details
        if ($user && $user->role === 'receptionist') {
            abort(403, 'Unauthorized access to clinical details.');
        }

        $consultation = Consultation::with(['patient.user', 'doctor.user', 'appointment'])
            ->findOrFail($id);

        // Ownership check for patients
        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            if (!$patient || $consultation->patient_id !== $patient->patient_id) {
                abort(403, 'You are not authorized to view this consultation.');
            }
        }
            
        return Inertia::render('Consultations/View', [
            'consultation' => ConsultationResource::make($consultation)
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

        $consultation = Consultation::findOrFail($id);
        
        return Inertia::render('Consultation/Edit', [
            'consultation' => $consultation,
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
        $validated = $request->validated();
        $consultation->update($validated);

        $status = $validated['status'] ?? $validated['consultation_status'] ?? $request->status;
        if ($status === 'in_progress') {
            return redirect()->back()->with('success', 'Progress saved successfully.');
        }

        return redirect()->route('dashboard')->with('success', 'Consultation concluded successfully.');
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
}
