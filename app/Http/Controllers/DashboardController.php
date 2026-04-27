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
        
        $recent_activity = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($activity) {
                $module = $activity->getExtraProperty('module') ?? 'general';
                $icons = [
                    'appointments' => 'fa-calendar-check',
                    'consultations' => 'fa-stethoscope',
                    'lab' => 'fa-vials',
                    'pharmacy' => 'fa-pills',
                    'billing' => 'fa-file-invoice-dollar',
                    'general' => 'fa-bell'
                ];
                $colors = [
                    'appointments' => 'primary',
                    'consultations' => 'info',
                    'lab' => 'warning',
                    'pharmacy' => 'danger',
                    'billing' => 'success',
                    'general' => 'secondary'
                ];

                return [
                    'id' => $activity->id,
                    'type' => $module,
                    'title' => $activity->description,
                    'user' => $activity->causer ? ($activity->causer->first_name . ' ' . $activity->causer->last_name) : 'System',
                    'time' => $activity->created_at->diffForHumans(),
                    'icon' => $icons[$module] ?? 'fa-bell',
                    'color' => $colors[$module] ?? 'primary'
                ];
            });

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
            $todayAppointments = Appointment::where('doctor_id', $staff->staff_id)
                ->whereDate('appointment_date', today())
                ->with('patient.user')
                ->get();

            $walkInVitals = \App\Models\Vital::whereDate('measured_at', today())
                ->whereDoesntHave('patient.appointments', function($q) use ($staff) {
                    $q->whereDate('appointment_date', today())
                      ->where('doctor_id', $staff->staff_id);
                })
                ->with('patient.user')
                ->latest('measured_at')
                ->get()
                ->unique('patient_id');

            $walkIns = $walkInVitals->map(function($vital) {
                return [
                    'appointment_id' => null,
                    'patient_id' => $vital->patient_id,
                    'patient' => $vital->patient,
                    'appointment_time' => Carbon::parse($vital->measured_at)->format('H:i:s'),
                    'appointment_type' => 'walk-in',
                    'status' => 'arrived'
                ];
            });

            $stats['today_appointments'] = collect($todayAppointments)->concat($walkIns)->sortBy('appointment_time')->values();
                
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
        $walkinVitalsCount = \App\Models\Vital::whereDate('measured_at', today())
            ->whereDoesntHave('patient.appointments', function($q) {
                $q->whereDate('appointment_date', today());
            })->distinct('patient_id')->count('patient_id');

        $appointmentsTodayCount = Appointment::whereDate('appointment_date', today())
            ->whereIn('status', ['scheduled', 'pending', 'confirmed', 'arrived'])
            ->count();

        $stats = [
            'triage_queue' => $appointmentsTodayCount + $walkinVitalsCount,
            
            'checked_in_patients' => Appointment::whereDate('appointment_date', today())
                ->whereIn('status', ['confirmed', 'arrived'])
                ->count() + $walkinVitalsCount,
                
            'upcoming_appointments' => collect(Appointment::whereDate('appointment_date', today())
                ->whereIn('status', ['scheduled', 'pending', 'confirmed', 'arrived'])
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->limit(10)
                ->with(['patient.user', 'doctor.user'])
                ->get())
                ->concat(
                    \App\Models\Vital::whereDate('measured_at', today())
                        ->whereDoesntHave('patient.appointments', function($q) {
                            $q->whereDate('appointment_date', today());
                        })
                        ->with('patient.user')
                        ->latest('measured_at')
                        ->get()
                        ->unique('patient_id')
                        ->map(function($vital) {
                            return [
                                'appointment_id' => null,
                                'patient_id' => $vital->patient_id,
                                'patient' => $vital->patient,
                                'appointment_time' => Carbon::parse($vital->measured_at)->format('H:i:s'),
                                'appointment_type' => 'walk-in',
                                'status' => 'vitals_recorded',
                                'doctor' => ['user' => ['last_name' => 'Pending Assignment']]
                            ];
                        })
                )
                ->sortBy('appointment_time')
                ->values()
                ->take(10)
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
            'pending_requests' => LabTestRequest::whereIn('status', ['pending', 'processing'])->count(),
            'completed_today' => LabTestRequest::where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'recent_requests' => LabTestRequest::with(['patient.user', 'testType'])
                ->whereIn('status', ['pending', 'processing'])
                ->orderByRaw("FIELD(status, 'processing', 'pending')")
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
        $recentActivity = collect();
        $patient = Patient::where('user_id', $user->user_id)->first();
        
        if ($patient) {
            // 1. Appointments
            $stats['my_appointments'] = Appointment::where('patient_id', $patient->patient_id)
                ->where('appointment_date', '>=', today())
                ->orderBy('appointment_date')
                ->with('doctor.user')
                ->get();
                
            // 2. Prescriptions
            $stats['my_prescriptions'] = Prescription::where('patient_id', $patient->patient_id)
                ->orderBy('created_at', 'desc')
                ->with(['doctor', 'items.medication'])
                ->get();

            // 3. Historical Consultations
            $stats['my_consultations'] = Consultation::where('patient_id', $patient->patient_id)
                ->whereIn('consultation_status', ['closed', 'completed'])
                ->with(['doctor.user'])
                ->orderBy('consultation_date', 'desc')
                ->get();

            // 4. Historical Labs
            $stats['my_labs'] = LabTestRequest::where('patient_id', $patient->patient_id)
                ->where('status', 'completed')
                ->with(['testType', 'assignedTo'])
                ->orderBy('completed_at', 'desc')
                ->get();

            // 5. Dynamic Invoice Cost (only computing completed/procured items)
            $invoices = Invoice::where('patient_id', $patient->patient_id)
                ->where('status', '!=', 'paid')
                ->with('items')
                ->get();

            $actualCost = 0;
            $recommendedCost = 0;

            foreach ($invoices as $invoice) {
                $recommendedCost += $invoice->total_amount;
                foreach ($invoice->items as $item) {
                    if ($item->item_type === 'lab_test') {
                        $isCompleted = LabTestRequest::where('consultation_id', $invoice->consultation_id)
                            ->where('test_type_id', $item->item_id_ref)
                            ->where('status', 'completed')
                            ->exists();
                        if ($isCompleted) $actualCost += $item->total_price;
                    } elseif ($item->item_type === 'medication') {
                        $isDispensed = \App\Models\PrescriptionItem::whereHas('prescription', function($q) use ($invoice) {
                                $q->where('consultation_id', $invoice->consultation_id)
                                  ->where('status', 'dispensed');
                            })
                            ->where('medication_id', $item->item_id_ref)
                            ->exists();
                        if ($isDispensed) $actualCost += $item->total_price;
                    } else {
                        // Services, Procedures, Consultations are generally considered done if billed
                        $actualCost += $item->total_price;
                    }
                }
            }
            $stats['dynamic_billing'] = [
                'actual_cost' => $actualCost,
                'recommended_cost' => $recommendedCost,
                'pending_invoices_count' => $invoices->count()
            ];

            // 6. Recent Activity Feed (Developments)
            // Get newest labs
            $recentLabs = $stats['my_labs']->take(2)->map(function($lab) {
                return [
                    'type' => 'lab',
                    'title' => 'Laboratory Result Ready',
                    'subtitle' => ($lab->testType->test_name ?? 'Test'),
                    'time' => $lab->completed_at ? Carbon::parse($lab->completed_at)->diffForHumans() : 'Recently',
                    'icon' => 'fa-flask',
                    'color' => 'success',
                    'url' => route('lab.show', $lab->request_id),
                    'btnText' => 'View Report',
                    'date' => $lab->completed_at
                ];
            });
            
            // Get newest consultations
            $recentConsultations = $stats['my_consultations']->take(2)->map(function($c) {
                return [
                    'type' => 'consultation',
                    'title' => 'Consultation Concluded',
                    'subtitle' => 'Dr. ' . ($c->doctor->user->last_name ?? ''),
                    'time' => $c->updated_at ? $c->updated_at->diffForHumans() : 'Recently',
                    'icon' => 'fa-stethoscope',
                    'color' => 'primary',
                    'url' => route('consultations.show', $c->consultation_id),
                    'btnText' => 'Review Record',
                    'date' => $c->updated_at
                ];
            });

            $recentActivity = $recentLabs->concat($recentConsultations)
                ->sortByDesc('date')
                ->values()
                ->take(4);
        }

        return Inertia::render('Dashboard/Patient', [
            'user' => $user,
            'role' => 'patient',
            'stats' => $stats,
            'recentActivity' => $recentActivity
        ]);
    }
}
