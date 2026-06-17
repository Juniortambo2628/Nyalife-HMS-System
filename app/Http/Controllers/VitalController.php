<?php

namespace App\Http\Controllers;

use App\Http\Resources\VitalResource;
use App\Http\Requests\StoreVitalRequest;
use App\Models\Vital;
use App\Models\Patient;
use App\Support\PatientId;
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

        $latestHeight = $patientId ? Vital::where('patient_id', $patientId)
            ->whereNotNull('height')
            ->latest('measured_at')
            ->value('height') : null;

        return Inertia::render('Vitals/Record', [
            'preselected_patient_id' => $patientId,
            'preselected_patient_label' => PatientId::fromPatient($patient) ?: null,
            'latest_height' => $latestHeight,
        ]);
    }

    public function store(StoreVitalRequest $request)
    {
        $validated = $request->validated();
        $priority = $request->input('priority', 'normal');
        
        // Calculate BMI if both height (cm) and weight (kg) are provided
        $bmi = null;
        if (!empty($validated['weight']) && !empty($validated['height'])) {
            $heightInMeters = $validated['height'] / 100;
            if ($heightInMeters > 0) {
                $bmi = round($validated['weight'] / ($heightInMeters * $heightInMeters), 2);
            }
        }

        Vital::create(array_merge($validated, [
            'bmi' => $bmi,
            'priority' => $priority,
            'measured_at' => $validated['measured_at'] ?? now(),
            'recorded_by' => Auth::id(),
        ]));

        $role = Auth::user()->role;
        return redirect()->route('dashboard', ['role' => $role])
                         ->with('success', 'Patient vitals recorded successfully. Patient is ready for doctor consultation.');
    }

    public function edit(Vital $vital)
    {
        $vital->load('patient.user');

        return Inertia::render('Vitals/Edit', [
            'vital' => VitalResource::make($vital),
        ]);
    }

    public function update(StoreVitalRequest $request, Vital $vital)
    {
        $validated = $request->validated();
        $priority = $request->input('priority', 'normal');
        
        // Calculate BMI if both height (cm) and weight (kg) are provided
        $bmi = null;
        if (!empty($validated['weight']) && !empty($validated['height'])) {
            $heightInMeters = $validated['height'] / 100;
            if ($heightInMeters > 0) {
                $bmi = round($validated['weight'] / ($heightInMeters * $heightInMeters), 2);
            }
        }

        $vital->update(array_merge($validated, [
            'bmi' => $bmi,
            'priority' => $priority,
        ]));

        return redirect()->route('patients.show', $vital->patient_id)
                         ->with('success', 'Patient vitals updated successfully.');
    }

    public function destroy(Request $request, Vital $vital)
    {
        $request->validate([
            'void_reason' => 'required|string|max:255'
        ]);

        $vital->update([
            'is_voided' => true,
            'void_reason' => $request->void_reason,
            'voided_by' => Auth::id(),
            'voided_at' => now(),
        ]);

        return back()->with('success', 'Vitals record has been voided.');
    }

    public function latest($patientId)
    {
        $vital = Vital::where('patient_id', $patientId)
            ->latest('measured_at')
            ->first();

        return response()->json($vital);
    }
}
