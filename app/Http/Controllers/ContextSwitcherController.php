<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContextSwitcherController extends Controller
{
    public function getOptions(Request $request)
    {
        $url = $request->query('current_url', '');
        $subjects = [];
        $subjectType = '';

        // Intelligent Switching Logic
        if (str_contains($url, '/consultations')) {
            $subjectType = 'Patients with Consultations';
            // Get recent patients who have consultations
            $patients = Patient::whereHas('consultations')
                ->with(['user'])
                ->latest()
                ->take(5)
                ->get();
            
            foreach ($patients as $p) {
                $subjects[] = [
                    'id' => $p->patient_id,
                    'name' => $p->user->first_name . ' ' . $p->user->last_name,
                    'initials' => strtoupper(substr($p->user->first_name, 0, 1) . substr($p->user->last_name, 0, 1)),
                    'subtext' => 'Recent Clinical Visit',
                    'url' => route('consultations.index', ['patient_id' => $p->patient_id])
                ];
            }
        } elseif (str_contains($url, '/patients')) {
            $subjectType = 'Recent Patients';
            $patients = Patient::with(['user'])
                ->latest()
                ->take(5)
                ->get();
            
            foreach ($patients as $p) {
                $subjects[] = [
                    'id' => $p->patient_id,
                    'name' => $p->user->first_name . ' ' . $p->user->last_name,
                    'initials' => strtoupper(substr($p->user->first_name, 0, 1) . substr($p->user->last_name, 0, 1)),
                    'subtext' => 'Registered: ' . $p->created_at->format('M d, Y'),
                    'url' => route('patients.show', $p->patient_id)
                ];
            }
        } elseif (str_contains($url, '/appointments')) {
            $subjectType = 'Upcoming Patients';
            $appointments = Appointment::with(['patient.user'])
                ->where('appointment_date', '>=', now()->toDateString())
                ->where('status', 'scheduled')
                ->orderBy('appointment_date')
                ->take(5)
                ->get();

            foreach ($appointments as $a) {
                $p = $a->patient;
                if ($p) {
                    $subjects[] = [
                        'id' => $p->patient_id,
                        'name' => $p->user->first_name . ' ' . $p->user->last_name,
                        'initials' => strtoupper(substr($p->user->first_name, 0, 1) . substr($p->user->last_name, 0, 1)),
                        'subtext' => 'Appointment: ' . $a->appointment_date,
                        'url' => route('appointments.index', ['patient_id' => $p->patient_id])
                    ];
                }
            }
        }

        return response()->json([
            'subjects' => $subjects,
            'subject_type' => $subjectType
        ]);
    }
}
