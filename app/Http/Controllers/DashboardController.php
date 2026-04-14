<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\LabTestRequest;
use App\Models\Prescription;
use App\Models\Staff;
use App\Models\Invoice;
use App\Models\Consultation;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request, $role = null)
    {
        $user = Auth::user();
        if (!$role) {
            $role = $user->role ?? 'patient'; 
        }

        return match ($role) {
            'admin' => $this->adminDashboard($user),
            'doctor' => $this->doctorDashboard($user),
            'nurse' => $this->nurseDashboard($user),
            'lab_technician' => $this->labTechnicianDashboard($user),
            'pharmacist' => $this->pharmacistDashboard($user),
            'patient' => $this->patientDashboard($user),
            default => Inertia::render('Dashboard', [
                'user' => $user,
                'role' => $role,
                'stats' => [],
                'recentActivity' => []
            ]),
        };
    }

    private function adminDashboard($user)
    {
        $stats = [
            'total_users' => User::where('is_active', 1)->count(),
            'active_patients' => Patient::count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'today_appointments' => Appointment::whereDate('appointment_date', today())->count()
        ];
        
        $recent_patients = User::where('role_id', 7)
            ->latest()
            ->limit(2)
            ->get()
            ->map(fn($u) => [
                'type' => 'patient',
                'title' => "New Patient: {$u->first_name} {$u->last_name}",
                'time' => $u->created_at->diffForHumans(),
                'icon' => 'fa-user-plus',
                'color' => 'primary'
            ]);

        $recent_invoices = Invoice::with('patient.user')
            ->latest()
            ->limit(2)
            ->get()
            ->map(fn($i) => [
                'type' => 'invoice',
                'title' => "Invoice #INV-{$i->invoice_id} for " . ($i->patient->user->last_name ?? 'Patient'),
                'time' => $i->created_at->diffForHumans(),
                'icon' => 'fa-file-invoice-dollar',
                'color' => 'success'
            ]);

        $recent_activity = collect($recent_patients)
            ->merge($recent_invoices)
            ->filter()
            ->sortByDesc('time')
            ->values()
            ->all();

        $performance = [];
        $labels = [];
        for($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('D');
            $performance[] = Appointment::whereDate('appointment_date', $date)->count();
        }

        $stats['performance'] = [
            'labels' => $labels,
            'data' => $performance
        ];

        return Inertia::render('Dashboard/Admin', [
            'user' => $user,
            'role' => 'admin',
            'stats' => $stats,
            'recentActivity' => $recent_activity
        ]);
    }

    private function doctorDashboard($user)
    {
        $stats = [];
        $staff = Staff::where('user_id', $user->user_id)->first();
        
        if ($staff) {
            $stats['today_appointments'] = Appointment::where('doctor_id', $staff->staff_id)
                ->whereDate('appointment_date', today())
                ->with('patient.user')
                ->get();
                
            $stats['pending_appointments'] = Appointment::where('doctor_id', $staff->staff_id)
                ->where('status', 'pending')
                ->count();
                
            $stats['completed_this_week'] = Consultation::where('doctor_id', $staff->staff_id)
                ->where('consultation_status', 'completed')
                ->where('consultation_date', '>=', now()->startOfWeek())
                ->count();

            $stats['in_progress_consultations'] = Consultation::where('doctor_id', $staff->staff_id)
                ->whereIn('consultation_status', ['pending', 'in_progress'])
                ->whereDoesntHave('labTestRequests', function($q) {
                    $q->where('status', 'pending');
                })
                ->with('patient.user')
                ->latest('updated_at')
                ->take(5)
                ->get();

            $stats['pending_lab_consultations'] = Consultation::where('doctor_id', $staff->staff_id)
                ->whereHas('labTestRequests', function($q) {
                    $q->where('status', 'pending');
                })
                ->with('patient.user')
                ->latest('updated_at')
                ->take(5)
                ->get();

            $stats['released_labs'] = Consultation::where('doctor_id', $staff->staff_id)
                ->where('consultation_status', '!=', 'completed')
                ->whereHas('labTestRequests', function($q) {
                    $q->where('status', 'completed')
                        ->where('completed_at', '>=', now()->subDays(2));
                })
                ->with('patient.user')
                ->latest('updated_at')
                ->take(5)
                ->get();
        }

        return Inertia::render('Dashboard/Doctor', [
            'user' => $user,
            'role' => 'doctor',
            'stats' => $stats,
            'recentActivity' => []
        ]);
    }

    private function nurseDashboard($user)
    {
        $stats = [
            'triage_queue' => Appointment::whereDate('appointment_date', today())
                ->where('status', 'arrived')
                ->whereDoesntHave('consultations')
                ->count() + 
                Consultation::whereDate('consultation_date', today())
                ->where('consultation_status', 'pending')
                ->where('is_walk_in', true)
                ->count(),
            
            'checked_in_patients' => Appointment::whereDate('appointment_date', today())
                ->whereIn('status', ['confirmed', 'arrived'])
                ->count(),
                
            'upcoming_appointments' => Appointment::whereDate('appointment_date', '>=', today())
                ->whereIn('status', ['pending', 'confirmed', 'arrived'])
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->limit(10)
                ->with(['patient.user', 'doctor.user'])
                ->get()
        ];

        return Inertia::render('Dashboard/Nurse', [
            'user' => $user,
            'role' => 'nurse',
            'stats' => $stats,
            'recentActivity' => []
        ]);
    }

    private function labTechnicianDashboard($user)
    {
        $stats = [
            'pending_requests' => LabTestRequest::where('status', 'pending')->count(),
            'completed_today' => LabTestRequest::where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'recent_requests' => LabTestRequest::with(['patient.user', 'testType'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return Inertia::render('Dashboard/LabTechnician', [
            'user' => $user,
            'role' => 'lab_technician',
            'stats' => $stats,
            'recentActivity' => []
        ]);
    }

    private function pharmacistDashboard($user)
    {
        $stats = [
            'pending_prescriptions' => Prescription::where('status', 'pending')->count(),
            'recent_prescriptions' => Prescription::with(['patient.user', 'doctor'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return Inertia::render('Dashboard/Pharmacist', [
            'user' => $user,
            'role' => 'pharmacist',
            'stats' => $stats,
            'recentActivity' => []
        ]);
    }

    private function patientDashboard($user)
    {
        $stats = [];
        $patient = Patient::where('user_id', $user->user_id)->first();
        
        if ($patient) {
            $stats['my_appointments'] = Appointment::where('patient_id', $patient->patient_id)
                ->where('appointment_date', '>=', today())
                ->orderBy('appointment_date')
                ->with('doctor.user')
                ->get();
                
            $stats['my_prescriptions'] = Prescription::where('patient_id', $patient->patient_id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return Inertia::render('Dashboard/Patient', [
            'user' => $user,
            'role' => 'patient',
            'stats' => $stats,
            'recentActivity' => []
        ]);
    }
}
