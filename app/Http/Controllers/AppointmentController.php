<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\StoreGuestAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AppointmentQueryService;
use App\Services\TelehealthNotificationService;
use App\Support\PatientId;
use App\Support\Permissions;
use App\Traits\HasBulkActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    use HasBulkActions;

    /**
     * Search doctors via AJAX.
     */
    public function searchDoctorsAjax(Request $request)
    {
        $search = $request->query('q');

        $doctors = Staff::whereHas('user', function ($q) use ($search) {
            $q->whereHas('roleRelation', function ($r) {
                $r->where('role_name', 'doctor');
            });

            if ($search) {
                $q->where(function ($sq) use ($search) {
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
                    'label' => "Dr. {$doctor->user->first_name} {$doctor->user->last_name} ({$doctor->specialization})",
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
        Appointment::releaseExpiredTelehealthHolds();

        // 1. Check if user exists
        $user = User::where('email', $validated['email'])->first();

        // 2. If not, create user
        if (! $user) {
            $password = Str::random(10); // Generate random password
            $username = 'guest_'.time().'_'.Str::random(4);

            $user = User::create([
                'first_name' => explode(' ', $validated['name'])[0],
                'last_name' => explode(' ', $validated['name'], 2)[1] ?? '',
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'username' => $username,
                'password' => Hash::make($password),
                'role' => 'patient', // Assuming 'patient' role exists
                'role_id' => Role::idFromName('patient'),
                'is_active' => true,
                'status' => 'provisional',
            ]);

            // Create Patient record
            Patient::create([
                'user_id' => $user->user_id,
                'patient_number' => Patient::generateNumber($user->user_id),
            ]);

            // Send guest credentials email
            TelehealthNotificationService::sendGuestCredentials($user->email, $validated['name'], $password);

            // Assign Spatie patient role
            $user->assignRole('patient');
        }

        // 3. Get Patient ID
        $patient = Patient::where('user_id', $user->user_id)->first();

        if (! $patient) {
            $patient = Patient::create([
                'user_id' => $user->user_id,
                'patient_number' => Patient::generateNumber($user->user_id),
            ]);
        }

        // 4. Create Appointment
        // Assign to a default doctor or rotate? For now, pick the first available doctor or leave null if constraints allow.
        // Looking at schema, doctor_id might be required. Let's find a default doctor or making it nullable if DB allows.
        // Assuming strict schema, let's pick the first doctor.
        $doctor = Staff::whereHas('user', function ($q) {
            $q->whereHas('roleRelation', function ($r) {
                $r->where('role_name', 'doctor');
            });
        })->first();

        $appointment_type = ($validated['type'] ?? '') === 'telehealth' ? 'telehealth' : 'consultation';

        if ($appointment_type === 'telehealth' && Appointment::where('doctor_id', $doctor?->staff_id)
            ->whereDate('appointment_date', $validated['date'])
            ->where('appointment_time', $validated['time'])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists()) {
            return back()->withErrors(['time' => 'That telehealth time is no longer available. Please choose another time.']);
        }

        $appointment = Appointment::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor ? $doctor->staff_id : 1, // Fallback to 1 if no doctor found (risky but needed)
            'appointment_date' => $validated['date'],
            'appointment_time' => $validated['time'],
            'appointment_type' => $appointment_type,
            'reason' => $validated['reason'],
            'status' => 'pending', // Guest appointments start as pending
            'created_by' => $user->user_id, // Self-created
            'telehealth_payment_amount' => $appointment_type === 'telehealth' ? 4000 : null,
            'telehealth_payment_expires_at' => $appointment_type === 'telehealth' ? now()->addMinutes(15) : null,
            'telehealth_payment_token' => $appointment_type === 'telehealth' ? Str::random(64) : null,
        ]);

        // Send payment notification for telehealth guest appointments
        if ($appointment_type === 'telehealth' && $user->email) {
            TelehealthNotificationService::sendPaymentNotification($appointment, $user->email);
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

        if (! $appointmentId) {
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
                'patient_name' => trim(($appointment->patient->user->first_name ?? '').' '.($appointment->patient->user->last_name ?? '')),
                'patient_email' => $appointment->patient->user->email ?? null,
                'patient_phone' => $appointment->patient->user->phone ?? null,
                'telehealth_payment_amount' => $appointment->telehealth_payment_amount,
                'telehealth_payment_expires_at' => $appointment->telehealth_payment_expires_at?->toIso8601String(),
                'telehealth_payment_url' => $appointment->telehealth_payment_token
                    ? route('telehealth.payment', $appointment->telehealth_payment_token)
                    : null,
            ],
        ]);
    }

    public function telehealthPayment(string $token)
    {
        $appointment = Appointment::with('patient.user')->where('telehealth_payment_token', $token)->firstOrFail();
        Appointment::releaseExpiredTelehealthHolds();
        abort_if($appointment->status === 'cancelled', 410, 'This payment hold has expired.');

        return Inertia::render('Appointments/TelehealthPayment', [
            'appointment' => AppointmentResource::make($appointment),
            'payment_token' => $token,
        ]);
    }

    public function submitTelehealthPayment(Request $request, string $token)
    {
        $appointment = Appointment::where('telehealth_payment_token', $token)->firstOrFail();
        Appointment::releaseExpiredTelehealthHolds();
        abort_if($appointment->status === 'cancelled', 410, 'This payment hold has expired.');

        $validated = $request->validate([
            'transaction_reference' => 'required|string|max:100',
            'receipt' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);
        $path = $request->file('receipt')?->store('telehealth-receipts', 'private');
        $appointment->update([
            'telehealth_payment_reference' => $validated['transaction_reference'],
            'telehealth_payment_receipt_path' => $path,
            'telehealth_payment_submitted_at' => now(),
        ]);

        return back()->with('success', 'Payment proof submitted. The clinic will confirm your appointment after review.');
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
            'preselected_doctor_label' => $doctor ? ('Dr. '.$doctor->user->first_name.' '.$doctor->user->last_name) : null,
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

        if (($validated['appointment_type'] ?? '') === Appointment::TYPE_TELEHEALTH) {
            $validated['status'] = 'pending';
            $validated['telehealth_payment_amount'] = 4000;
            $validated['telehealth_payment_expires_at'] = now()->addMinutes(15);
            $validated['telehealth_payment_token'] = Str::random(64);
        }

        $appointment = Appointment::create($validated);

        // Send payment notification for telehealth appointments
        if (($validated['appointment_type'] ?? '') === Appointment::TYPE_TELEHEALTH) {
            $patientEmail = $appointment->patient->user->email ?? null;
            if ($patientEmail) {
                TelehealthNotificationService::sendPaymentNotification($appointment, $patientEmail);
            }
        }

        ActivityLogger::log(
            'appointments',
            'New appointment scheduled for '.($appointment->patient->user->full_name ?? 'Patient'),
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

        // Patients may only view their own appointments
        $this->requireStaffOrOwnPatient(
            $appointment->patient_id,
            Permissions::MANAGE_APPOINTMENTS
        );

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
     * Check in a patient and auto-create consultation.
     */
    public function checkIn($id)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user'])->findOrFail($id);
        $appointment->update(['status' => 'arrived']);
        PatientQueue::enqueue($appointment->patient_id, $appointment->appointment_id, Auth::id());

        $consultation = Consultation::create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->appointment_id,
            'consultation_date' => now(),
            'consultation_status' => 'in_progress',
            'diagnosis' => null,
            'complaint' => $appointment->reason_for_visit ?? null,
            'created_by' => Auth::id(),
        ]);

        ActivityLogger::log(
            'consultations',
            'Consultation auto-created for '.($appointment->patient->user->full_name ?? 'Patient').' (check-in)',
            ['consultation_id' => $consultation->consultation_id, 'appointment_id' => $appointment->appointment_id],
            Auth::user(),
            $consultation,
            [$appointment->doctor->user_id, 1]
        );

        return redirect()->route('consultations.edit', $consultation->consultation_id)
            ->with('success', 'Patient checked in and consultation started. Please complete the consultation details.');
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
                'title' => $apt->patient->user->first_name.' '.$apt->patient->user->last_name,
                'start' => $apt->appointment_date.' '.$apt->appointment_time,
                'status' => $apt->status,
            ];
        });

        return Inertia::render('Appointments/Calendar', [
            'appointments' => $appointments,
        ]);
    }

    /**
     * Confirm telehealth payment and generate meeting link.
     */
    public function confirmTelehealthPayment(Request $request, $id)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user'])->findOrFail($id);

        if ($appointment->appointment_type !== 'telehealth') {
            return redirect()->back()->with('error', 'This appointment is not a telehealth consultation.');
        }

        if ($appointment->status === 'cancelled') {
            return redirect()->back()->with('error', 'This appointment has been cancelled.');
        }

        if ($appointment->telehealth_payment_token && ! $appointment->telehealth_payment_reference) {
            return redirect()->back()->with('error', 'The patient has not submitted an M-Pesa transaction code yet.');
        }

        $link = TelehealthNotificationService::confirmPaymentAndSendInvite($appointment);
        $appointment->update(['telehealth_payment_approved_at' => now()]);

        ActivityLogger::log(
            'appointments',
            "Telehealth payment confirmed for appointment #{$appointment->appointment_id}. Meeting link sent.",
            ['appointment_id' => $appointment->appointment_id, 'meeting_link' => $link],
            Auth::user(),
            $appointment,
            [1]
        );

        return redirect()->back()->with('success', 'Payment confirmed. Meeting link has been sent to the patient.');
    }

    /**
     * Handle bulk actions on appointments.
     */
    protected function bulkActionMap(): array
    {
        return [
            'confirm' => function (array $ids, int $count) {
                Appointment::whereIn('appointment_id', $ids)->update(['status' => 'confirmed']);

                return redirect()->back()->with('success', "{$count} appointment(s) confirmed.");
            },
            'cancel' => function (array $ids, int $count) {
                Appointment::whereIn('appointment_id', $ids)->update(['status' => 'cancelled']);

                return redirect()->back()->with('success', "{$count} appointment(s) cancelled.");
            },
            'delete' => function (array $ids, int $count) {
                Appointment::whereIn('appointment_id', $ids)->delete();

                return redirect()->back()->with('success', "{$count} appointment(s) deleted.");
            },
        ];
    }
}
