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
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role.
     */
    public function index(Request $request, $role = null)
    {
        $user = Auth::user();
        
        if (!$role) {
            $role = $user->role ?? 'patient'; 
        }

        $stats = [];
        $recent_activity = [];
        
        // Base stats for Admin
        if ($role === 'admin') {
            $stats['total_users'] = User::where('is_active', 1)->count();
            $stats['active_patients'] = Patient::count();
            $stats['pending_appointments'] = Appointment::where('status', 'pending')->count();
            $stats['today_appointments'] = Appointment::whereDate('appointment_date', today())->count();
            
            // Real Activity Stream
            $recent_patients = User::where('role_id', 7) // Patient role
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

            // Performance Data (Weekly Chart)
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
        }
        
        // Fetch stats based on other roles
        switch ($role) {
            case 'doctor':
                $staff = Staff::where('user_id', $user->user_id)->first();
                if ($staff) {
                     $stats['today_appointments'] = Appointment::where('doctor_id', $staff->staff_id)
                        ->whereDate('appointment_date', today())
                        ->with('patient.user')
                        ->get();
                     $stats['pending_appointments'] = Appointment::where('doctor_id', $staff->staff_id)
                        ->where('status', 'pending')
                        ->count();
                }
                break;
                
            case 'nurse':
                 $stats['checked_in_patients'] = Appointment::whereDate('appointment_date', today())
                        ->whereIn('status', ['confirmed', 'arrived'])
                        ->count();
                 $stats['upcoming_appointments'] = Appointment::where('appointment_date', '>', today())
                        ->orderBy('appointment_date')
                        ->limit(5)
                        ->with(['patient.user', 'doctor.user'])
                        ->get();
                break;
                
             case 'lab_technician':
                 $stats['pending_requests'] = LabTestRequest::where('status', 'pending')->count();
                 $stats['completed_today'] = LabTestRequest::where('status', 'completed')
                        ->whereDate('completed_at', today())
                        ->count();
                 $stats['recent_requests'] = LabTestRequest::with(['patient.user', 'testType'])
                        ->where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
                 break;
                 
             case 'pharmacist':
                 $stats['pending_prescriptions'] = Prescription::where('status', 'pending')->count();
                 $stats['recent_prescriptions'] = Prescription::with(['patient.user', 'doctor'])
                        ->where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
                 break;

             case 'patient':
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
                 break;
        }

        $dashboardData = [
            'user' => $user,
            'role' => $role,
            'stats' => $stats,
            'recentActivity' => $recent_activity
        ];

        switch ($role) {
            case 'admin':
                return Inertia::render('Dashboard/Admin', $dashboardData);
            case 'doctor':
                return Inertia::render('Dashboard/Doctor', $dashboardData);
            case 'nurse':
                return Inertia::render('Dashboard/Nurse', $dashboardData);
            case 'lab_technician':
                return Inertia::render('Dashboard/LabTechnician', $dashboardData);
            case 'pharmacist':
                return Inertia::render('Dashboard/Pharmacist', $dashboardData);
            case 'patient':
                return Inertia::render('Dashboard/Patient', $dashboardData);
            default:
                return Inertia::render('Dashboard', $dashboardData);
        }
    }
}
