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

        return Inertia::render('Consultations/Index', [
            'consultations' => ConsultationResource::collection($consultations),
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
        if ($appointmentId) {
            $appointment = Appointment::with('patient.user')->find($appointmentId);
            $patientId = $appointment->patient_id;
        }

        return Inertia::render('Consultations/Create', [
            'appointment_id' => $appointmentId,
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => $appointment ? ($appointment->patient->user->first_name . ' ' . $appointment->patient->user->last_name) : null,
             // Link doctors to users for the dropdown
            'doctors' => Staff::with('user')->get()->map(function($s) {
                 return [
                    'value' => $s->staff_id,
                    'label' => 'Dr. ' . ($s->user->last_name ?? 'Unknown')
                 ];
            }),
            'appointment' => $appointment
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

            // Update appointment status if linked
            if (!empty($data['appointment_id'])) {
                Appointment::where('appointment_id', $data['appointment_id'])
                    ->update(['status' => 'completed']);
            }

            DB::commit();

            if ($data['consultation_status'] === 'in_progress') {
                return redirect()->route('consultations.edit', $consultation->consultation_id)
                    ->with('success', 'Consultation saved progressively. You can now request labs.');
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
            'doctors' => Staff::with('user')->get()->map(function($s) {
                 return [
                    'value' => $s->staff_id,
                    'label' => 'Dr. ' . ($s->user->last_name ?? 'Unknown')
                 ];
            }),
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
}
