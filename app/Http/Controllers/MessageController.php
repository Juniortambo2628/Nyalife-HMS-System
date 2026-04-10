<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\LabTestRequest;
use App\Models\Medication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all messages where user is sender or receiver
        $messages = Message::with(['sender', 'receiver'])
            ->where('sender_id', $user->user_id)
            ->orWhere('receiver_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get list of users with unread counts
        $users = User::where('user_id', '!=', $user->user_id)
            ->where('is_active', true)
            ->get(['user_id', 'first_name', 'last_name', 'username'])
            ->map(function($u) use ($user) {
                $u['unread_count'] = Message::where('sender_id', $u->user_id)
                    ->where('receiver_id', $user->user_id)
                    ->whereNull('read_at')
                    ->count();
                return $u;
            });

        return Inertia::render('Messages/Index', [
            'messages' => $messages,
            'users' => $users
        ]);
    }

    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'content' => $validated['content'],
            'metadata' => $validated['metadata'] ?? null
        ]);

        return redirect()->back()->with('success', 'Message sent.');
    }

    public function markAllRead($userId)
    {
        Message::where('sender_id', $userId)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return redirect()->back();
    }

    public function getEntities()
    {
        $user = Auth::user();
        $role = $user->role;
        $entities = [];

        // Patients are referenceable by everyone except maybe other patients? 
        // For simplicity, all staff can see patients.
        if ($role !== 'patient') {
            $entities['patients'] = Patient::with('user')->get()->map(function($p) {
                return [
                    'id' => $p->patient_id,
                    'label' => $p->user->first_name . ' ' . $p->user->last_name . ' (' . $p->patient_number . ')',
                    'type' => 'patient'
                ];
            });
        }

        // Appointments
        if (in_array($role, ['admin', 'doctor', 'nurse', 'receptionist'])) {
            $entities['appointments'] = Appointment::with('patient.user')->get()->map(function($a) {
                return [
                    'id' => $a->appointment_id,
                    'label' => 'Apt #' . $a->appointment_id . ': ' . $a->patient->user->first_name . ' (' . $a->appointment_date . ')',
                    'type' => 'appointment'
                ];
            });
        }

        // Consultations
        if (in_array($role, ['admin', 'doctor', 'nurse'])) {
            $entities['consultations'] = Consultation::with('patient.user')->get()->map(function($c) {
                return [
                    'id' => $c->consultation_id,
                    'label' => 'Consultation #' . $c->consultation_id . ' - ' . $c->patient->user->first_name,
                    'type' => 'consultation'
                ];
            });
        }

        // Lab Requests
        if (in_array($role, ['admin', 'doctor', 'nurse', 'lab_technician'])) {
            $entities['lab_requests'] = LabTestRequest::with(['patient.user', 'testType'])->get()->map(function($l) {
                return [
                    'id' => $l->request_id,
                    'label' => 'Lab #' . $l->request_id . ' - ' . ($l->testType->test_name ?? 'Test') . ' (' . $l->patient->user->first_name . ')',
                    'type' => 'lab_request'
                ];
            });
        }

        // Pharmacy/Medications
        if (in_array($role, ['admin', 'doctor', 'nurse', 'pharmacist'])) {
            $entities['medications'] = Medication::get()->map(function($m) {
                return [
                    'id' => $m->medication_id,
                    'label' => $m->medication_name . ' (' . $m->strength . ')',
                    'type' => 'medication'
                ];
            });
        }

        return response()->json($entities);
    }
}
