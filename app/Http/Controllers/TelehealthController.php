<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\TelehealthConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TelehealthController extends Controller
{
    /**
     * Public page rendering the step-by-step telehealth guide & interactive consent form.
     */
    public function index()
    {
        return Inertia::render('Telehealth/Index', [
            'isLoggedIn' => Auth::check(),
            'user' => Auth::user(),
        ]);
    }

    /**
     * Jitsi Meeting Room page.
     */
    public function meetingRoom($meetingId)
    {
        $user = Auth::user();

        $appointment = Appointment::where('notes', 'like', "%Meeting Link: %{$meetingId}%")
            ->where(function ($q) use ($user) {
                $q->where('patient_id', function ($subq) use ($user) {
                    $subq->select('patient_id')->from('patients')->where('user_id', $user->user_id);
                })
                    ->orWhere('doctor_id', function ($subq) use ($user) {
                        $subq->select('staff_id')->from('staff')->where('user_id', $user->user_id);
                    })
                    ->orWhere('created_by', $user->user_id);
            })
            ->first();

        if (! $appointment) {
            abort(403, 'You do not have access to this meeting room.');
        }

        $jitsiDomain = config('services.jitsi.domain', 'meet.jit.si');

        return Inertia::render('Telehealth/MeetingRoom', [
            'meetingId' => $meetingId,
            'jitsiDomain' => $jitsiDomain,
            'user' => $user,
        ]);
    }

    /**
     * Public/Patient submission of consent form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_email' => 'required|email|max:255',
            'patient_phone' => 'required|string|max:20',
            'doctor_name' => 'nullable|string|max:255',
            'patient_signature' => 'required|string', // Base64 data URI
            'verbal_consent' => 'nullable|boolean',
        ]);

        // Attempt to match patient by email or phone
        $patient = Patient::whereHas('user', function ($query) use ($validated) {
            $query->where('email', $validated['patient_email'])
                ->orWhere('phone', $validated['patient_phone']);
        })->first();

        TelehealthConsent::create([
            'patient_id' => $patient ? $patient->patient_id : null,
            'patient_name' => $validated['patient_name'],
            'patient_email' => $validated['patient_email'],
            'patient_phone' => $validated['patient_phone'],
            'doctor_name' => $validated['doctor_name'],
            'patient_signature_path' => $validated['patient_signature'],
            'verbal_consent_obtained' => $validated['verbal_consent'] ?? false,
            'signed_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Telehealth Consent Form signed and submitted successfully.');
    }

    /**
     * Admin view listing all signed consents.
     */
    public function adminIndex(Request $request)
    {
        $consents = TelehealthConsent::with('patient.user')
            ->orderBy('signed_at', 'desc')
            ->paginate(15);

        return Inertia::render('Telehealth/AdminIndex', [
            'consents' => $consents,
        ]);
    }

    /**
     * View specific consent sheet details.
     */
    public function show($id)
    {
        $consent = TelehealthConsent::with('patient.user')->findOrFail($id);

        return Inertia::render('Telehealth/Show', [
            'consent' => $consent,
            'doctors' => Staff::whereHas('user.roleRelation', function ($query) {
                $query->where('role_name', 'doctor');
            })->with('user')->get()->map(function ($s) {
                return [
                    'value' => 'Dr. '.($s->user->last_name ?? 'Unknown'),
                    'label' => 'Dr. '.($s->user->first_name.' '.$s->user->last_name),
                ];
            }),
        ]);
    }

    /**
     * Allows attending doctor or staff to log verbal consent or counter-sign/verify.
     */
    public function signDoctor(Request $request, $id)
    {
        $consent = TelehealthConsent::findOrFail($id);

        $validated = $request->validate([
            'doctor_signature' => 'nullable|string', // Base64 data URI
            'verbal_consent_obtained' => 'required|boolean',
            'doctor_name' => 'required|string|max:255',
        ]);

        $consent->update([
            'doctor_name' => $validated['doctor_name'],
            'doctor_signature_path' => $validated['doctor_signature'],
            'verbal_consent_obtained' => $validated['verbal_consent_obtained'],
        ]);

        return redirect()->back()->with('success', 'Telehealth Consent counter-signed / verified successfully.');
    }
}
