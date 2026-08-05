<?php

namespace App\Http\Controllers;

use App\Http\Resources\RadiologyRequestResource;
use App\Models\Patient;
use App\Models\RadiologyRequest;
use App\Services\ActivityLogger;
use App\Support\PatientId;
use App\Support\Permissions;
use App\Traits\HasBulkActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RadiologyController extends Controller
{
    use HasBulkActions;

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = RadiologyRequest::with(['patient.user', 'doctor.user', 'requestedBy', 'assignedTo', 'verifiedBy']);

        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->patient_id);
            }
        } elseif ($user && $user->role === 'doctor') {
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

        return Inertia::render('Radiology/Index', [
            'requests' => RadiologyRequestResource::collection($query->latest()->paginate(15)),
            'filters' => (object) $request->only(['search', 'status', 'quick_filter']),
        ]);
    }

    public function create(Request $request)
    {
        $patientId = $request->query('patient_id');
        $consultationId = $request->query('consultation_id');

        $patient = $patientId ? Patient::with('user')->find($patientId) : null;

        return Inertia::render('Radiology/Create', [
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => PatientId::fromPatient($patient) ?: null,
            'consultation_id' => $consultationId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'consultation_id' => 'nullable|exists:consultations,consultation_id',
            'scan_type' => 'required|string',
            'priority' => 'required|string',
            'clinical_indication' => 'nullable|string',
            'scan_details' => 'nullable|string',
        ]);

        $radRequest = RadiologyRequest::create([
            'request_number' => 'RAD-'.strtoupper(substr(uniqid(), -6)),
            'patient_id' => $validated['patient_id'],
            'consultation_id' => $validated['consultation_id'] ?? null,
            'scan_type' => $validated['scan_type'],
            'priority' => $validated['priority'],
            'clinical_indication' => $validated['clinical_indication'] ?? null,
            'scan_details' => $validated['scan_details'] ?? null,
            'status' => 'pending',
            'requested_by' => Auth::id(),
            'doctor_id' => Auth::user()->role === 'doctor' ? Auth::user()->staff?->staff_id : null,
        ]);

        ActivityLogger::log(
            'radiology',
            'New radiology scan requested: '.$radRequest->scan_type,
            ['request_id' => $radRequest->request_id],
            Auth::user(),
            $radRequest,
            [1] // Notify Admin
        );

        return redirect()->route('radiology.index')->with('success', 'Radiology scan request created successfully.');
    }

    public function edit($id)
    {
        $radRequest = RadiologyRequest::with('patient.user')->findOrFail($id);

        if ($radRequest->status !== 'pending') {
            return redirect()->route('radiology.show', $radRequest->request_id)
                ->with('error', 'Only pending radiology requests can be edited.');
        }

        return Inertia::render('Radiology/Edit', [
            'radRequest' => $radRequest,
            'preselected_patient_id' => $radRequest->patient_id,
            'preselected_patient_label' => PatientId::fromPatient($radRequest->patient) ?: null,
        ]);
    }

    public function update(Request $request, $id)
    {
        $radRequest = RadiologyRequest::findOrFail($id);

        if ($radRequest->status !== 'pending') {
            return back()->with('error', 'Only pending radiology requests can be updated.');
        }

        $validated = $request->validate([
            'scan_type' => 'required|string',
            'priority' => 'required|string',
            'clinical_indication' => 'nullable|string',
            'scan_details' => 'nullable|string',
        ]);

        $radRequest->update($validated);

        ActivityLogger::log(
            'radiology',
            'Radiology request updated: '.$radRequest->scan_type,
            ['request_id' => $radRequest->request_id],
            Auth::user(),
            $radRequest,
            [1]
        );

        return redirect()->route('radiology.show', $radRequest->request_id)
            ->with('success', 'Radiology scan request updated successfully.');
    }

    public function show($id)
    {
        $request = RadiologyRequest::with(['patient.user', 'doctor.user', 'requestedBy', 'assignedTo', 'verifiedBy', 'consultation'])
            ->findOrFail($id);

        $this->requireStaffOrOwnPatient(
            $request->patient_id,
            Permissions::MANAGE_LAB
        );

        return Inertia::render('Radiology/Show', [
            'request' => RadiologyRequestResource::make($request),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,pending_verification,verified,completed,cancelled',
            'findings' => 'nullable|string',
            'impression' => 'nullable|string',
            'scan_details' => 'nullable|string',
        ]);

        $radRequest = RadiologyRequest::findOrFail($id);

        $updateData = [
            'status' => $validated['status'],
        ];

        if (array_key_exists('findings', $validated)) {
            $updateData['findings'] = $validated['findings'];
        }
        if (array_key_exists('impression', $validated)) {
            $updateData['impression'] = $validated['impression'];
        }
        if (array_key_exists('scan_details', $validated)) {
            $updateData['scan_details'] = $validated['scan_details'];
        }

        // Technician submits results → pending_verification
        if ($validated['status'] === 'pending_verification') {
            $updateData['assigned_to'] = Auth::id();
        }

        // Senior / Doctor verifies results → verified (also marks completed)
        if ($validated['status'] === 'verified') {
            $updateData['verified_by'] = Auth::id();
            $updateData['verified_at'] = now();
            $updateData['completed_at'] = now();
        }

        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now();
            $updateData['assigned_to'] = Auth::id();
        }

        if ($validated['status'] === 'processing') {
            $updateData['assigned_to'] = Auth::id();
        }

        $radRequest->update($updateData);

        ActivityLogger::log(
            'radiology',
            'Radiology request '.($validated['status'] === 'verified' ? 'results verified' : ($validated['status'] === 'pending_verification' ? 'awaiting verification' : "updated to {$validated['status']}")),
            ['request_id' => $radRequest->request_id, 'status' => $validated['status']],
            Auth::user(),
            $radRequest,
            [$radRequest->requested_by, $radRequest->patient->user_id, 1]
        );

        return redirect()->back()->with('success', 'Radiology request status updated to '.$validated['status']);
    }

    public function destroy($id)
    {
        $radRequest = RadiologyRequest::findOrFail($id);

        if ($radRequest->status !== 'pending') {
            return back()->with('error', 'Only pending radiology requests can be removed.');
        }

        $radRequest->delete();

        ActivityLogger::log(
            'radiology',
            'Radiology request removed: '.$radRequest->scan_type,
            ['request_id' => $radRequest->request_id],
            Auth::user(),
            null,
            [1]
        );

        return back()->with('success', 'Radiology request removed successfully.');
    }

    /**
     * Handle bulk actions on radiology requests.
     */
    protected function bulkActionMap(): array
    {
        return [
            'complete' => function (array $ids, int $count) {
                $updated = $this->bulkProcessWithLog(
                    RadiologyRequest::class, 'request_id', $ids,
                    fn ($item) => ! in_array($item->status, ['completed', 'cancelled']),
                    fn ($item) => ['status' => 'completed', 'completed_at' => now(), 'assigned_to' => Auth::id()],
                    'radiology', 'Radiology request',
                    fn ($item) => [$item->requested_by, $item->patient->user_id, 1]
                );

                return redirect()->back()->with('success', "{$updated} radiology request(s) completed.");
            },
            'cancel' => function (array $ids, int $count) {
                $updated = $this->bulkProcessWithLog(
                    RadiologyRequest::class, 'request_id', $ids,
                    fn ($item) => ! in_array($item->status, ['completed', 'cancelled']),
                    fn ($item) => ['status' => 'cancelled'],
                    'radiology', 'Radiology request',
                    fn ($item) => [$item->requested_by, $item->patient->user_id, 1]
                );

                return redirect()->back()->with('success', "{$updated} radiology request(s) cancelled.");
            },
            'delete' => function (array $ids, int $count) {
                $deleted = $this->bulkDelete(RadiologyRequest::class, 'request_id', $ids, 'status', 'completed');

                return redirect()->back()->with('success', "{$deleted} radiology request(s) deleted.");
            },
        ];
    }
}
