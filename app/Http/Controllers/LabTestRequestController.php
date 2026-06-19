<?php

namespace App\Http\Controllers;

use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\ActivityLogger;
use App\Support\PatientId;

class LabTestRequestController extends Controller
{
    public function create(Request $request)
    {
        $patientId = $request->query('patient_id');
        $consultationId = $request->query('consultation_id');
        
        $patient = $patientId ? Patient::with('user')->find($patientId) : null;

        return Inertia::render('Lab/Create', [
            'testTypes' => LabTestType::labTests()->active()->get(),
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => PatientId::fromPatient($patient) ?: null,
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

        $labRequest = LabTestRequest::create([
            'patient_id' => $validated['patient_id'],
            'consultation_id' => $validated['consultation_id'] ?? null,
            'test_type_id' => $validated['test_type_id'],
            'requested_by' => Auth::id(),
            'request_date' => now(),
            'status' => 'pending',
            'priority' => $validated['priority'],
            'notes' => $validated['notes']
        ]);

        ActivityLogger::log(
            'lab',
            "New lab test requested: " . ($labRequest->testType->test_name ?? 'Test'),
            ['request_id' => $labRequest->request_id],
            Auth::user(),
            $labRequest,
            [1] // Notify Admin and maybe add Lab Tech group later
        );

        return redirect()->route('lab.index')->with('success', 'Lab test request created successfully.');
    }

    public function destroy($id)
    {
        $labRequest = LabTestRequest::findOrFail($id);
        
        if ($labRequest->status !== 'pending') {
            return back()->with('error', 'Only pending lab requests can be removed.');
        }

        $labRequest->delete();

        ActivityLogger::log(
            'lab',
            "Lab test request removed: " . ($labRequest->testType->test_name ?? 'Test'),
            ['request_id' => $labRequest->request_id],
            Auth::user(),
            null,
            [1]
        );

        return back()->with('success', 'Lab test request removed successfully.');
    }
}
