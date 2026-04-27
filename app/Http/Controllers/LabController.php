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
use App\Services\ActivityLogger;

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

        if ($request->has('consultation_id')) {
            $query->where('consultation_id', $request->consultation_id);
        }

        if ($request->has('quick_filter') && $request->quick_filter) {
            switch ($request->quick_filter) {
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'completed':
                    $query->where('status', 'completed');
                    break;
                case 'urgent':
                    $query->where('priority', 'urgent')->where('status', 'pending');
                    break;
            }
        }

        $query = $query->searchByPatientName($request->search)
            ->status($request->status);

        return Inertia::render('Lab/Index', [
            'requests' => LabTestRequestResource::collection($query->latest()->paginate(15)),
            'filters' => (object) $request->only(['search', 'status', 'quick_filter']),
            'auth' => [
                'user' => Auth::user()
            ]
        ]);
    }

    public function tests(Request $request)
    {
        $query = LabTestType::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('test_name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'test_name');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSorts = ['test_name', 'category', 'price', 'is_active'];
        
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('test_name', 'asc');
        }

        $labCategories = [
            'Hematology', 'Chemistry', 'Reproductive', 'Serology', 
            'Microbiology', 'Pathology', 'Parasitology', 'Biochemistry', 'Toxicology', 'General'
        ];

        return Inertia::render('Lab/TestsCatalog', [
            'tests' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction']),
            'categories' => $labCategories,
            'auth' => [
                'user' => Auth::user()
            ]
        ]);
    }

    public function manage(Request $request)
    {
        $query = LabTestType::query();

        if ($request->has('search') && $request->search) {
            $query->where('test_name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Lab/Tests/Index', [
            'tests' => $query->latest()->get(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function results()
    {
        return Inertia::render('LabResults/Index');
    }

    public function show($id)
    {
        $request = LabTestRequest::with(['patient.user', 'doctor.user', 'testType', 'consultation'])
            ->findOrFail($id);

        return Inertia::render('Lab/Show', [
            'request' => LabTestRequestResource::make($request)
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'results' => 'nullable|array'
        ]);

        $labRequest = LabTestRequest::findOrFail($id);
        
        $updateData = [
            'status' => $validated['status'],
            'results' => $validated['results'] ?? $labRequest->results,
        ];

        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now();
            $updateData['assigned_to'] = Auth::id(); // Or processed_by if that's the column
        }

        if ($validated['status'] === 'processing') {
            $updateData['assigned_to'] = Auth::id();
        }

        $labRequest->update($updateData);

        ActivityLogger::log(
            'lab',
            "Lab request " . ($validated['status'] === 'completed' ? 'results ready' : "updated to {$validated['status']}"),
            ['request_id' => $labRequest->request_id, 'status' => $validated['status']],
            Auth::user(),
            $labRequest,
            [$labRequest->requested_by, $labRequest->patient->user_id, 1] // Notify Doctor, Patient, and Admin
        );

        return redirect()->back()->with('success', 'Lab request status updated to ' . $validated['status']);
    }

    public function print($id)
    {
        $request = LabTestRequest::with(['patient.user', 'doctor.user', 'testType'])
            ->findOrFail($id);

        return Inertia::render('Lab/Print', [
            'request' => LabTestRequestResource::make($request),
            'clinic_name' => 'Nyalife Women\'s Clinic',
            'clinic_address' => 'Nairobi, Kenya',
            'clinic_phone' => '+254 700 000 000'
        ]);
    }
}
