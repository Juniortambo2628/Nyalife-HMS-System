<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVitalRequest;
use App\Models\Vital;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VitalController extends Controller
{
    public function index()
    {
        $appointments = \App\Models\Appointment::with(['patient.user'])
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time')
            ->get();

        return Inertia::render('Vitals/Index', [
            'appointments' => $appointments
        ]);
    }

    public function create(Request $request)
    {
        $patientId = $request->query('patient_id');
        $patient = $patientId ? Patient::with('user')->find($patientId) : null;

        return Inertia::render('Vitals/Record', [
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => $patient ? ($patient->user->first_name . ' ' . $patient->user->last_name) : null,
        ]);
    }

    public function store(StoreVitalRequest $request)
    {
        $validated = $request->validated();
        Vital::create(array_merge($validated, [
            'measured_at' => now(),
            'recorded_by' => Auth::id(),
        ]));

        return redirect()->route('dashboard', 'nurse')
                         ->with('success', 'Vital signs recorded successfully.');
    }
}
