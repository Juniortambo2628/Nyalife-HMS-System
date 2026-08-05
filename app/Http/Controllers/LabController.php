<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLabRequestStatusRequest;
use App\Http\Resources\LabTestRequestResource;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Patient;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\Permissions;
use App\Traits\HasBulkActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LabController extends Controller
{
    use HasBulkActions;

    /**
     * Base query scoped to the authenticated user's lab access.
     */
    private function scopedRequestsQuery(?User $user)
    {
        $query = LabTestRequest::query();

        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->patient_id);
            }
        } elseif ($user && $user->role === 'doctor') {
            $query->where('requested_by', $user->user_id);
        }

        return $query;
    }

    private function labRequestStats(?User $user): array
    {
        $base = $this->scopedRequestsQuery($user);

        return [
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'processing' => (clone $base)->where('status', 'processing')->count(),
            'completed' => (clone $base)->whereIn('status', ['verified', 'completed'])->count(),
            'urgent' => (clone $base)->where('priority', 'urgent')->whereIn('status', ['pending', 'processing'])->count(),
        ];
    }

    public function requests(Request $request)
    {
        $user = Auth::user();
        $query = $this->scopedRequestsQuery($user)->with([
            'patient.user',
            'doctor.user',
            'testType',
            'assignedTo',
            'verifiedBy',
        ]);

        if ($request->has('consultation_id')) {
            $query->where('consultation_id', $request->consultation_id);
        }

        if ($request->has('quick_filter') && $request->quick_filter) {
            switch ($request->quick_filter) {
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'processing':
                    $query->where('status', 'processing');
                    break;
                case 'pending_verification':
                    $query->where('status', 'pending_verification');
                    break;
                case 'verified':
                    $query->whereIn('status', ['verified', 'completed']);
                    break;
                case 'completed':
                    $query->where('status', 'completed');
                    break;
                case 'urgent':
                    $query->where('priority', 'urgent')->whereIn('status', ['pending', 'processing']);
                    break;
            }
        }

        $query = $query->searchByPatientName($request->search)
            ->status($request->status);

        return Inertia::render('Lab/Index', [
            'requests' => LabTestRequestResource::collection($query->latest()->paginate(15)->withQueryString()),
            'filters' => (object) $request->only(['search', 'status', 'quick_filter']),
            'stats' => $this->labRequestStats($user),
        ]);
    }

    public function tests(Request $request)
    {
        $query = LabTestType::labTests();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('test_name', 'like', '%'.$request->search.'%')
                    ->orWhere('category', 'like', '%'.$request->search.'%');
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

        $stats = [
            'total' => LabTestType::count(),
            'active' => LabTestType::where('is_active', true)->count(),
            'inactive' => LabTestType::where('is_active', false)->count(),
        ];

        return Inertia::render('Lab/TestsCatalog', [
            'tests' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction']),
            'categories' => LabTestType::LAB_CATEGORIES,
            'stats' => $stats,
        ]);
    }

    public function manage(Request $request)
    {
        return redirect()->route('lab.tests', $request->query());
    }

    public function results(Request $request)
    {
        $user = Auth::user();
        $query = LabTestRequest::with(['patient.user', 'testType', 'doctor.user'])
            ->whereIn('status', ['verified', 'completed']);

        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            abort_unless($patient, 403);
            $query->where('patient_id', $patient->patient_id);
        } elseif ($user && $user->role === 'doctor') {
            $query->where('requested_by', $user->user_id);
        }

        $resultsQuery = clone $query;
        $stats = [
            'total' => $resultsQuery->count(),
            'today' => (clone $resultsQuery)->whereDate('completed_at', today())->count(),
            'this_week' => (clone $resultsQuery)->where('completed_at', '>=', now()->startOfWeek())->count(),
        ];

        $results = $query
            ->searchByPatientName($request->search)
            ->when($request->request_number, fn ($q) => $q->where('request_number', 'like', '%'.$request->request_number.'%'))
            ->latest('completed_at')
            ->latest('request_id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('LabResults/Index', [
            'results' => LabTestRequestResource::collection($results),
            'filters' => $request->only(['search', 'request_number']),
            'stats' => $stats,
        ]);
    }

    public function resultShow($id)
    {
        $labRequest = LabTestRequest::with(['patient.user', 'testType', 'doctor.user', 'verifiedBy'])
            ->findOrFail($id);

        $this->authorizeLabResultAccess($labRequest);
        abort_unless(in_array($labRequest->status, ['verified', 'completed']), 404, 'Results not yet available.');

        return Inertia::render('LabResults/Show', [
            'request' => LabTestRequestResource::make($labRequest),
        ]);
    }

    public function resultDownload($id)
    {
        $labRequest = LabTestRequest::findOrFail($id);
        $this->authorizeLabResultAccess($labRequest);
        abort_unless(in_array($labRequest->status, ['verified', 'completed']), 404);

        return redirect()->route('lab.print', $labRequest->request_id);
    }

    private function authorizeLabResultAccess(LabTestRequest $labRequest): void
    {
        $user = Auth::user();

        if ($user?->hasAnyPermission([
            Permissions::MANAGE_LAB,
            Permissions::MANAGE_PATIENTS,
            Permissions::MANAGE_APPOINTMENTS,
        ])) {
            return;
        }

        if ($user?->can(Permissions::MANAGE_CONSULTATIONS)) {
            abort_unless(
                $labRequest->requested_by === $user->user_id
                || ($labRequest->doctor && $labRequest->doctor->user_id === $user->user_id),
                403
            );

            return;
        }

        $this->requireStaffOrOwnPatient($labRequest->patient_id);

        if ($this->isPatientPortalUser()) {
            abort_unless(in_array($labRequest->status, ['verified', 'completed'], true), 403);
        }
    }

    public function show($id)
    {
        $request = LabTestRequest::with([
            'patient.user',
            'doctor.user',
            'testType',
            'consultation',
            'requestedBy',
            'assignedTo',
            'verifiedBy',
        ])->findOrFail($id);

        $this->requireStaffOrOwnPatient(
            $request->patient_id,
            Permissions::MANAGE_LAB,
            Permissions::MANAGE_PATIENTS,
            Permissions::MANAGE_APPOINTMENTS
        );

        return Inertia::render('Lab/Show', [
            'request' => LabTestRequestResource::make($request),
        ]);
    }

    public function updateStatus(UpdateLabRequestStatusRequest $request, $id)
    {
        $validated = $request->validated();

        $labRequest = LabTestRequest::findOrFail($id);

        $updateData = [
            'status' => $validated['status'],
            'results' => $validated['results'] ?? $labRequest->results,
        ];

        // Lab technician submits results → pending_verification
        if ($validated['status'] === 'pending_verification') {
            $updateData['assigned_to'] = Auth::id();
        }

        // Senior / Doctor verifies results → verified (also marks completed)
        if ($validated['status'] === 'verified') {
            $updateData['verified_by'] = Auth::id();
            $updateData['verified_at'] = now();
            $updateData['completed_at'] = now();
        }

        // Legacy direct-complete path
        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now();
            $updateData['assigned_to'] = Auth::id();
        }

        if ($validated['status'] === 'processing') {
            $updateData['assigned_to'] = Auth::id();
        }

        $labRequest->update($updateData);

        ActivityLogger::log(
            'lab',
            'Lab request'.($validated['status'] === 'verified' ? 'results verified' : ($validated['status'] === 'pending_verification' ? 'awaiting verification' : "updated to {$validated['status']}")),
            ['request_id' => $labRequest->request_id, 'status' => $validated['status']],
            Auth::user(),
            $labRequest,
            [$labRequest->requested_by, $labRequest->patient->user_id, 1]
        );

        return redirect()->back()->with('success', 'Lab request status updated to '.$validated['status']);
    }

    public function print($id)
    {
        $request = LabTestRequest::with(['patient.user', 'doctor.user', 'testType'])
            ->findOrFail($id);

        return Inertia::render('Lab/Print', [
            'request' => LabTestRequestResource::make($request),
            'clinic_name' => 'Nyalife Women\'s Clinic',
            'clinic_address' => 'Nairobi, Kenya',
            'clinic_phone' => '+254 700 000 000',
        ]);
    }

    /**
     * Handle bulk actions on lab requests.
     */
    protected function bulkActionMap(): array
    {
        return [
            'complete' => function (array $ids, int $count) {
                $updated = $this->bulkProcessWithLog(
                    LabTestRequest::class, 'request_id', $ids,
                    fn ($item) => !in_array($item->status, ['completed', 'cancelled']),
                    fn ($item) => ['status' => 'completed', 'completed_at' => now(), 'assigned_to' => Auth::id()],
                    'lab', 'Lab request',
                    fn ($item) => [$item->requested_by, $item->patient->user_id, 1]
                );

                return redirect()->back()->with('success', "{$updated} lab request(s) completed.");
            },
            'cancel' => function (array $ids, int $count) {
                $updated = $this->bulkProcessWithLog(
                    LabTestRequest::class, 'request_id', $ids,
                    fn ($item) => !in_array($item->status, ['completed', 'cancelled']),
                    fn ($item) => ['status' => 'cancelled'],
                    'lab', 'Lab request',
                    fn ($item) => [$item->requested_by, $item->patient->user_id, 1]
                );

                return redirect()->back()->with('success', "{$updated} lab request(s) cancelled.");
            },
            'delete' => function (array $ids, int $count) {
                $deleted = $this->bulkDelete(LabTestRequest::class, 'request_id', $ids, 'status', 'completed');

                return redirect()->back()->with('success', "{$deleted} lab request(s) deleted.");
            },
        ];
    }
}
