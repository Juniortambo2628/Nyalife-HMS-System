<?php

namespace App\Http\Controllers;

use App\Http\Resources\LabTestRequestResource;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LabTestRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LabTestRequest::with(['patient.user', 'testType', 'doctor']);

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('patient.user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(15);

        return Inertia::render('Lab/Index', [
            'requests' => LabTestRequestResource::collection($requests),
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function create(Request $request)
    {
        $patientId = $request->query('patient_id');
        $consultationId = $request->query('consultation_id');
        
        $patient = $patientId ? Patient::with('user')->find($patientId) : null;

        return Inertia::render('Lab/Create', [
            'testTypes' => LabTestType::where('is_active', true)->get(),
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => $patient ? ($patient->user->first_name . ' ' . $patient->user->last_name) : null,
            'consultation_id' => $consultationId
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'consultation_id' => 'nullable|exists:consultations,consultation_id',
            'test_type_id' => 'required|exists:lab_test_types,test_type_id',
            'priority' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        LabTestRequest::create([
            'patient_id' => $validated['patient_id'],
            'consultation_id' => $validated['consultation_id'] ?? null,
            'test_type_id' => $validated['test_type_id'],
            'requested_by' => Auth::id(),
            'request_date' => now(),
            'status' => 'pending',
            'priority' => $validated['priority'],
            'notes' => $validated['notes']
        ]);

        return redirect()->route('lab.index')->with('success', 'Lab test request created successfully.');
    }

    public function show($id)
    {
        $request = LabTestRequest::with(['patient.user', 'testType', 'doctor', 'labTechnician.user'])->findOrFail($id);
        return Inertia::render('Lab/Show', [
            'request' => LabTestRequestResource::make($request)
        ]);
    }
}
