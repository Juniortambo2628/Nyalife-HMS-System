<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLabRequestStatusRequest;
use App\Http\Resources\LabTestRequestResource;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LabController extends Controller
{
    public function requests(Request $request)
    {
        $user = Auth::user();
        $query = LabTestRequest::with(['patient.user', 'doctor.user', 'testType']);

        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->patient_id);
            }
        } elseif ($user && $user->role === 'doctor') {
            // Lab requests are tracked by the user who requested them
            $query->where('requested_by', $user->user_id);
        }

        $query = $query->searchByPatientName($request->search)
            ->status($request->status);

        return Inertia::render('Lab/Index', [
            'requests' => LabTestRequestResource::collection($query->latest()->paginate(15)),
            'filters' => (object) $request->only(['search', 'status']),
            'auth' => [
                'user' => Auth::user()
            ]
        ]);
    }

    public function tests()
    {
        $labCategories = [
            'Hematology', 'Chemistry', 'Reproductive', 'Serology', 
            'Microbiology', 'Pathology', 'Parasitology', 'Biochemistry', 'Laboratory'
        ];

        return Inertia::render('Lab/Tests', [
            'tests' => LabTestType::whereIn('category', $labCategories)
                ->where('is_active', true)
                ->orderBy('test_name')
                ->get()
        ]);
    }

    public function manage()
    {
        return Inertia::render('Lab/Manage');
    }

    public function results()
    {
        return Inertia::render('LabResults/Index');
    }

    public function show($id)
    {
        $request = LabTestRequest::with(['patient.user', 'doctor.user', 'testType'])
            ->findOrFail($id);

        return Inertia::render('Lab/Show', [
            'request' => LabTestRequestResource::make($request)
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
            'results' => 'nullable|array'
        ]);

        $labRequest = LabTestRequest::findOrFail($id);
        $labRequest->update([
            'status' => $validated['status'],
            'results' => $validated['results'] ?? $labRequest->results,
            'processed_at' => $validated['status'] === 'completed' ? now() : null,
            'processed_by' => $validated['status'] === 'completed' ? Auth::id() : $labRequest->processed_by,
        ]);

        return redirect()->back()->with('success', 'Lab request status updated.');
    }
}
