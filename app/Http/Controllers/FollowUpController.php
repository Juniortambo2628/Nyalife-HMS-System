<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFollowUpRequest;
use App\Http\Requests\UpdateFollowUpRequest;
use App\Http\Resources\ConsultationResource;
use App\Http\Resources\FollowUpResource;
use App\Models\Consultation;
use App\Models\FollowUp;
use App\Services\ActivityLogger;
use App\Support\Permissions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FollowUpController extends Controller
{
    private function authorizeStaff(): void
    {
        $this->requirePermission(Permissions::MANAGE_FOLLOW_UPS);
    }

    public function index(Request $request)
    {
        $this->authorizeStaff();

        $query = FollowUp::with(['patient.user', 'consultation.doctor.user', 'createdBy'])
            ->filteredQuery($request);

        if ($request->view === 'upcoming') {
            $query->upcoming()->orderBy('follow_up_date');
        } else {
            $query->latest('follow_up_date');
        }

        $followUps = $query->paginate(15)->withQueryString();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $stats = [
            'scheduled_month' => FollowUp::where('status', 'scheduled')
                ->whereBetween('follow_up_date', [$monthStart, $monthEnd])
                ->count(),
            'completed_month' => FollowUp::where('status', 'completed')
                ->whereBetween('follow_up_date', [$monthStart, $monthEnd])
                ->count(),
            'upcoming_week' => FollowUp::upcoming()
                ->whereDate('follow_up_date', '<=', Carbon::today()->addDays(7))
                ->count(),
        ];

        return Inertia::render('FollowUps/Index', [
            'followUps' => FollowUpResource::collection($followUps),
            'filters' => $request->only(['search', 'status', 'type', 'patient_id', 'date_from', 'date_to', 'view']),
            'stats' => $stats,
            'followUpTypes' => FollowUp::TYPES,
        ]);
    }

    public function upcoming(Request $request)
    {
        $request->merge(['view' => 'upcoming', 'status' => $request->status ?: 'scheduled']);

        return $this->index($request);
    }

    public function create(Request $request)
    {
        $this->authorizeStaff();

        $consultation = null;
        if ($request->consultation_id) {
            $consultation = Consultation::with(['patient.user', 'doctor.user'])->findOrFail($request->consultation_id);
        }

        $recentConsultations = Consultation::with(['patient.user'])
            ->latest('consultation_date')
            ->limit(100)
            ->get();

        return Inertia::render('FollowUps/Create', [
            'consultation' => $consultation ? ConsultationResource::make($consultation) : null,
            'recentConsultations' => ConsultationResource::collection($recentConsultations),
            'followUpTypes' => FollowUp::TYPES,
            'preselected_consultation_id' => $request->consultation_id,
            'default_reason' => $consultation?->follow_up_instructions,
        ]);
    }

    public function store(StoreFollowUpRequest $request)
    {
        $validated = $request->validated();

        $followUp = FollowUp::create([
            ...$validated,
            'follow_up_type' => $validated['follow_up_type'] ?? 'general',
            'status' => $validated['status'] ?? 'scheduled',
            'created_by' => Auth::id(),
        ]);

        $followUp->load(['patient.user', 'consultation']);

        ActivityLogger::log(
            'clinical',
            'Follow-up scheduled for '.($followUp->patient->user->first_name ?? 'patient').' on '.$followUp->follow_up_date->format('Y-m-d'),
            ['follow_up_id' => $followUp->follow_up_id],
            Auth::user(),
            $followUp,
            [$followUp->patient->user_id ?? null, 1]
        );

        return redirect()->route('follow-ups.show', $followUp->follow_up_id)
            ->with('success', 'Follow-up scheduled successfully.');
    }

    public function show($id)
    {
        $this->authorizeStaff();

        $followUp = FollowUp::with(['patient.user', 'consultation.doctor.user', 'createdBy'])
            ->findOrFail($id);

        return Inertia::render('FollowUps/Show', [
            'followUp' => FollowUpResource::make($followUp),
            'followUpTypes' => FollowUp::TYPES,
        ]);
    }

    public function edit($id)
    {
        $this->authorizeStaff();

        $followUp = FollowUp::with(['patient.user', 'consultation'])->findOrFail($id);

        return Inertia::render('FollowUps/Edit', [
            'followUp' => FollowUpResource::make($followUp),
            'followUpTypes' => FollowUp::TYPES,
        ]);
    }

    public function update(UpdateFollowUpRequest $request, $id)
    {
        $followUp = FollowUp::findOrFail($id);
        $followUp->update($request->validated());

        ActivityLogger::log(
            'clinical',
            'Follow-up #'.$followUp->follow_up_id.' updated',
            ['follow_up_id' => $followUp->follow_up_id, 'status' => $followUp->status],
            Auth::user(),
            $followUp
        );

        return redirect()->route('follow-ups.show', $followUp->follow_up_id)
            ->with('success', 'Follow-up updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(FollowUp::STATUSES)),
        ]);

        $followUp = FollowUp::findOrFail($id);
        $followUp->update(['status' => $validated['status']]);

        ActivityLogger::log(
            'clinical',
            'Follow-up #'.$followUp->follow_up_id.' marked as '.$validated['status'],
            ['follow_up_id' => $followUp->follow_up_id],
            Auth::user(),
            $followUp
        );

        return back()->with('success', 'Follow-up status updated.');
    }

    public function destroy($id)
    {
        $this->authorizeStaff();

        $followUp = FollowUp::with('patient.user')->findOrFail($id);

        if ($followUp->status === 'completed') {
            return back()->with('error', 'Completed follow-ups cannot be deleted.');
        }

        ActivityLogger::log(
            'clinical',
            'Follow-up #'.$followUp->follow_up_id.' removed',
            ['follow_up_id' => $followUp->follow_up_id],
            Auth::user(),
            null
        );

        $followUp->delete();

        return redirect()->route('follow-ups.index')->with('success', 'Follow-up removed.');
    }
}
