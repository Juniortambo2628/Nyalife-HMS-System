<?php

namespace App\Http\Controllers;

use App\Models\PatientQueue;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PatientQueueController extends Controller
{
    public function index()
    {
        $this->requirePermission(Permissions::MANAGE_QUEUE);
        return Inertia::render('Queue/Index', ['queue' => PatientQueue::with('patient.user')->where('queue_date', PatientQueue::clinicQueueDate())->orderBy('queue_number')->get()]);
    }

    public function store(Request $request)
    {
        $this->requirePermission(Permissions::MANAGE_QUEUE);
        $data = $request->validate(['patient_id' => 'required|exists:patients,patient_id', 'appointment_id' => 'nullable|exists:appointments,appointment_id']);
        PatientQueue::enqueue((int) $data['patient_id'], $data['appointment_id'] ?? null, Auth::id());
        return back()->with('success', 'Patient added to today\'s queue.');
    }

    public function updateStatus(Request $request, PatientQueue $queue)
    {
        $this->requirePermission(Permissions::MANAGE_QUEUE);
        $data = $request->validate(['status' => 'required|in:waiting,triage,with_doctor,completed,cancelled']);
        $queue->update($data + ($data['status'] === 'completed' ? ['completed_at' => now()] : []) + (in_array($data['status'], ['triage', 'with_doctor']) ? ['called_at' => now()] : []));
        return back()->with('success', 'Queue status updated.');
    }
}
