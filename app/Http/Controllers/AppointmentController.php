<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\StoreGuestAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Services\ActivityLogger;
use App\Support\PatientId;
use App\Services\AppointmentQueryService;
use App\Mail\TelehealthInvitation;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    /**
     * Search doctors via AJAX.
     */
    public function searchDoctorsAjax(Request $request)
    {
        $search = $request->query('q');

        $doctors = Staff::whereHas('user', function($q) use ($search) {
                $q->whereHas('roleRelation', function($r) {
                    $r->where('role_name', 'doctor');
                });
                
                if ($search) {
                    $q->where(function($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('username', 'like', "%{$search}%");
                    });
                }
            })
            ->with('user')
            ->limit(20)
            ->get()
            ->map(function ($doctor) {
                return [
                    'value' => $doctor->staff_id,
                    'label' => "Dr. {$doctor->user->first_name} {$doctor->user->last_name} ({$doctor->specialization})"
                ];
            });

        return response()->json($doctors);
    }

    /**
     * Store a guest appointment.
     */
    public function storeGuest(StoreGuestAppointmentRequest $request)
    {
        $validated = $request->validated();

        // 1. Check if user exists
        $user = User::where('email', $validated['email'])->first();

        // 2. If not, create user
        if (!$user) {
            $password = Str::random(10); // Generate random password
            $username = 'guest_' . time() . '_' . Str::random(4);

            $user = User::create([
                'first_name' => explode(' ', $validated['name'])[0],
                'last_name' => explode(' ', $validated['name'], 2)[1] ?? '',
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'username' => $username,
                'password' => Hash::make($password),
                'role' => 'patient', // Assuming 'patient' role exists
                'role_id' => Role::where('role_name', 'patient')->first()->role_id ?? 6, // Fallback ID
                'is_active' => true,
                'status' => 'provisional',
            ]);

            // Create Patient record
            Patient::create([
                'user_id' => $user->user_id,
                'patient_number' => 'NYA' . date('Y') . str_pad($user->user_id, 4, '0', STR_PAD_LEFT),
            ]);
            
            // TODO: Send email with credentials
        }

        // 3. Get Patient ID
        $patient = Patient::where('user_id', $user->user_id)->first();
        
        if (!$patient) {
             $patient = Patient::create([
                'user_id' => $user->user_id,
                'patient_number' => 'NYA' . date('Y') . str_pad($user->user_id, 4, '0', STR_PAD_LEFT),
            ]);
        }

        // 4. Create Appointment
        // Assign to a default doctor or rotate? For now, pick the first available doctor or leave null if constraints allow.
        // Looking at schema, doctor_id might be required. Let's find a default doctor or making it nullable if DB allows.
        // Assuming strict schema, let's pick the first doctor.
        $doctor = Staff::whereHas('user', function($q) {
                $q->whereHas('roleRelation', function($r) {
                    $r->where('role_name', 'doctor');
                });
            })->first();

        $appointment_type = ($validated['type'] ?? '') === 'telehealth' ? 'telehealth' : 'consultation';

        $appointment = Appointment::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor ? $doctor->staff_id : 1, // Fallback to 1 if no doctor found (risky but needed)
            'appointment_date' => $validated['date'],
            'appointment_time' => $validated['time'],
            'appointment_type' => $appointment_type,
            'reason' => $validated['reason'],
            'status' => 'pending', // Guest appointments start as pending
            'created_by' => $user->user_id, // Self-created
        ]);

        // Generate Jitsi meeting link for telehealth guest appointments
        if ($appointment_type === 'telehealth') {
            $meetingId = 'nyalife-' . strtolower(\Illuminate\Support\Str::random(12));
            $appUrl = rtrim(config('app.url'), '/');
            $link = "{$appUrl}/telehealth/meeting/{$meetingId}";
            $appointment->notes = "Meeting Link: {$link}";
            $appointment->save();

            // Send telehealth invitation email
            try {
                if ($user->email) {
                    Mail::to($user->email)->send(new TelehealthInvitation([
                        'patient_name' => $validated['name'],
                        'meeting_link' => $link,
                        'appointment_date' => $validated['date'],
                        'appointment_time' => $validated['time'],
                        'doctor_name' => 'Clinic Physician',
                    ]));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Telehealth invitation email failed: ' . $e->getMessage());
            }
        }

        ActivityLogger::log(
            'appointments',
            "Guest appointment request from {$validated['name']}",
            ['appointment_id' => $appointment->appointment_id],
            $user,
            $appointment,
            [1]
        );

        return redirect()->route('guest-appointments.confirmation')
            ->with('guest_appointment_id', $appointment->appointment_id);
    }

    public function guestConfirmation()
    {
        $appointmentId = session('guest_appointment_id');

        if (!$appointmentId) {
            return redirect()->route('welcome');
        }

        $appointment = Appointment::with(['patient.user', 'doctor.user'])
            ->findOrFail($appointmentId);

        return Inertia::render('Appointments/GuestConfirmation', [
            'appointment' => [
                'appointment_id' => $appointment->appointment_id,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'appointment_type' => $appointment->appointment_type,
                'reason' => $appointment->reason,
                'status' => $appointment->status,
                'patient_name' => trim(($appointment->patient->user->first_name ?? '') . ' ' . ($appointment->patient->user->last_name ?? '')),
                'patient_email' => $appointment->patient->user->email ?? null,
                'patient_phone' => $appointment->patient->user->phone ?? null,
            ],
        ]);
    }

    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AppointmentQueryService::scopedFor($user, [
            'patient.user',
            'doctor.user',
            'consultations',
            'prescriptions',
            'labTestRequests.testType',
        ]);
        AppointmentQueryService::applyFilters($query, $request);

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Appointment::count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'scheduled' => Appointment::where('status', 'scheduled')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'today' => Appointment::whereDate('appointment_date', today())->count(),
        ];

        return Inertia::render('Appointments/Index', [
            'appointments' => AppointmentResource::collection($appointments),
            'filters' => $request->only(['status', 'date', 'doctor_id', 'patient_id', 'quick_filter', 'search']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create(Request $request)
    {
        $patientId = $request->query('patient_id');
        $doctorId = $request->query('doctor_id');
        $appointmentType = $request->query('type');

        $patient = $patientId ? Patient::with('user')->find($patientId) : null;
        $doctor = $doctorId ? Staff::with('user')->find($doctorId) : null;

        return Inertia::render('Appointments/Create', [
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => PatientId::fromPatient($patient) ?: null,
            'preselected_doctor_id' => $doctorId,
            'preselected_doctor_label' => $doctor ? ("Dr. " . $doctor->user->first_name . " " . $doctor->user->last_name) : null,
            'preselected_type' => $appointmentType,
        ]);
    }

    /**
     * Store a newly created appointment.
     */
    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();
        $validated['status'] = 'scheduled';
        $validated['created_by'] = Auth::id();
        
        $appointment = Appointment::create($validated);

        // Generate Jitsi meeting link for telehealth appointments
        if (($validated['appointment_type'] ?? '') === 'telehealth') {
            $meetingId = 'nyalife-' . strtolower(\Illuminate\Support\Str::random(12));
            $appUrl = rtrim(config('app.url'), '/');
            $link = "{$appUrl}/telehealth/meeting/{$meetingId}";
            $appointment->notes = ($appointment->notes ?? '') . "\nMeeting Link: {$link}";
            $appointment->save();

            // Send telehealth invitation email
            try {
                $patientEmail = $appointment->patient->user->email ?? null;
                if ($patientEmail) {
                    Mail::to($patientEmail)->send(new TelehealthInvitation([
                        'patient_name' => trim(($appointment->patient->user->first_name ?? '') . ' ' . ($appointment->patient->user->last_name ?? '')),
                        'meeting_link' => $link,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                        'doctor_name' => 'Clinic Physician',
                    ]));
                }
            } catch (\Exception $e) {
                // Log but don't break the flow if email fails
                \Illuminate\Support\Facades\Log::warning('Telehealth invitation email failed: ' . $e->getMessage());
            }
        }

        ActivityLogger::log(
            'appointments',
            "New appointment scheduled for " . ($appointment->patient->user->full_name ?? 'Patient'),
            ['appointment_id' => $appointment->appointment_id],
            Auth::user(),
            $appointment,
            [1] // Notify Admin (assuming ID 1 is admin)
        );
        
        return redirect()->route('appointments.index')
                         ->with('success', 'Appointment scheduled successfully.');
    }

    /**
     * Display the specified appointment.
     */
    public function show($id)
    {
        $appointment = Appointment::with([
            'patient.user',
            'doctor.user',
            'doctor.departmentRelation',
            'prescriptions.items.medication',
            'labTestRequests.testType',
            'consultations.doctor.user',
        ])->findOrFail($id);
        
        return Inertia::render('Appointments/Show', [
            'appointment' => AppointmentResource::make($appointment),
        ]);
    }

    /**
     * Update the specified appointment.
     */
    public function update(UpdateAppointmentRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $validated = $request->validated();
        $appointment->update($validated);

        ActivityLogger::log(
            'appointments',
            "Appointment #{$appointment->appointment_id} updated",
            ['changes' => $validated],
            Auth::user(),
            $appointment,
            [1]
        );
        
        return redirect()->back()->with('success', 'Appointment updated successfully.');
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        
        return redirect()->route('appointments.index')
                         ->with('success', 'Appointment deleted successfully.');
    }

    /**
     * Check in a patient (update status to arrived).
     */
    public function checkIn($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'arrived']);

        ActivityLogger::log(
            'appointments',
            "Patient " . ($appointment->patient->user->full_name ?? 'Patient') . " checked in",
            ['appointment_id' => $appointment->appointment_id],
            Auth::user(),
            $appointment,
            [$appointment->doctor->user_id, 1] // Notify Doctor and Admin
        );
        
        return redirect()->back()->with('success', 'Patient checked in successfully! You can now start the consultation.');
    }

    /**
     * Display calendar view.
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();
        $query = Appointment::with(['patient.user', 'doctor.user']);
        
        // Filter by role
        if ($user->role === 'doctor') {
            $staff = Staff::where('user_id', $user->user_id)->first();
            if ($staff) {
                $query->where('doctor_id', $staff->staff_id);
            }
        }
        
        $appointments = $query->get()->map(function ($apt) {
            return [
                'id' => $apt->appointment_id,
                'title' => $apt->patient->user->first_name . ' ' . $apt->patient->user->last_name,
                'start' => $apt->appointment_date . ' ' . $apt->appointment_time,
                'status' => $apt->status,
            ];
        });
        
        return Inertia::render('Appointments/Calendar', [
            'appointments' => $appointments,
        ]);
    }

    /**
     * Handle bulk actions on appointments.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:confirm,cancel,delete',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
        ]);

        $ids    = $validated['ids'];
        $action = $validated['action'];
        $count  = count($ids);

        switch ($action) {
            case 'confirm':
                Appointment::whereIn('appointment_id', $ids)
                    ->update(['status' => 'confirmed']);
                return redirect()->back()->with('success', "{$count} appointment(s) confirmed.");

            case 'cancel':
                Appointment::whereIn('appointment_id', $ids)
                    ->update(['status' => 'cancelled']);
                return redirect()->back()->with('success', "{$count} appointment(s) cancelled.");

            case 'delete':
                Appointment::whereIn('appointment_id', $ids)->delete();
                return redirect()->back()->with('success', "{$count} appointment(s) deleted.");
        }

        return redirect()->back()->with('error', 'Unknown bulk action.');
    }
}
