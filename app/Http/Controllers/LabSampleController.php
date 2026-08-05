<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLabSampleRequest;
use App\Http\Resources\LabSampleResource;
use App\Http\Resources\LabTestRequestResource;
use App\Models\LabSample;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LabSampleController extends Controller
{
    private function authorizeLabStaff(): void
    {
        abort_unless(in_array(Auth::user()?->role, ['admin', 'lab_technician', 'nurse', 'doctor'], true), 403);
    }

    public function register(Request $request)
    {
        $this->authorizeLabStaff();

        $prefillRequest = null;
        if ($request->lab_request_id) {
            $prefillRequest = LabTestRequest::with(['patient.user', 'testType'])
                ->find($request->lab_request_id);
        }

        $pendingRequests = LabTestRequest::with(['patient.user', 'testType'])
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('Lab/Samples/Register', [
            'prefillRequest' => $prefillRequest ? LabTestRequestResource::make($prefillRequest) : null,
            'pendingRequests' => LabTestRequestResource::collection($pendingRequests),
            'testTypes' => LabTestType::where('is_active', true)->orderBy('test_name')->get(),
            'sampleTypes' => LabSample::SAMPLE_TYPES,
            'preselected_lab_request_id' => $request->lab_request_id,
        ]);
    }

    public function store(StoreLabSampleRequest $request)
    {
        $validated = $request->validated();
        $now = now();

        $sample = LabSample::create([
            'sample_id' => 'SMP-'.strtoupper(substr(uniqid(), -8)),
            'patient_id' => $validated['patient_id'],
            'test_type_id' => $validated['test_type_id'],
            'sample_type' => $validated['sample_type'],
            'collected_date' => $validated['collected_date'],
            'collected_by' => Auth::id(),
            'collected_at' => $validated['collected_at'] ?? $now,
            'status' => 'registered',
            'notes' => $validated['notes'] ?? null,
            'urgent' => $validated['urgent'] ?? false,
        ]);

        if (! empty($validated['lab_request_id'])) {
            $labRequest = LabTestRequest::find($validated['lab_request_id']);
            if ($labRequest && in_array($labRequest->status, ['pending', 'processing'], true)) {
                $labRequest->update([
                    'status' => 'processing',
                    'sample_collected_by' => Auth::id(),
                ]);
            }
        }

        ActivityLogger::log(
            'lab',
            "Sample {$sample->sample_id} registered",
            ['sample_id' => $sample->id, 'lab_sample_code' => $sample->sample_id],
            Auth::user(),
            $sample,
            [1]
        );

        return redirect()->route('lab.samples.show', $sample->id)
            ->with('success', 'Sample registered successfully.');
    }

    public function show($id)
    {
        $this->authorizeLabStaff();

        $sample = LabSample::with(['patient.user', 'testType', 'collectedByUser', 'completedByUser'])
            ->findOrFail($id);

        return Inertia::render('Lab/Samples/Show', [
            'sample' => LabSampleResource::make($sample),
        ]);
    }

    public function index(Request $request)
    {
        $this->authorizeLabStaff();

        $samples = LabSample::with(['patient.user', 'testType', 'collectedByUser'])
            ->filterSearch($request->search)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('collected_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => LabSample::count(),
            'registered' => LabSample::where('status', 'registered')->count(),
            'completed' => LabSample::where('status', 'completed')->count(),
        ];

        return Inertia::render('Lab/Samples/Index', [
            'samples' => LabSampleResource::collection($samples),
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
            'sampleStatuses' => LabSample::STATUSES,
        ]);
    }
}
