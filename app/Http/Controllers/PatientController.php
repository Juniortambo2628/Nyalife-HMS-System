<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuickStorePatientRequest;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Patient::with(['user', 'appointments' => function ($q) {
            $q->latest()->limit(5);
        }]);

        if ($user && in_array($user->role, ['doctor', 'admin'])) {
            $query->with(['consultations' => function ($q) {
                $q->latest()->limit(5);
            }]);
        }

        if ($user && $user->role === 'patient') {
            $query->where('user_id', $user->user_id);
        }

        $patients = $query->searchByUserName($request->search)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return Inertia::render('Patients/Index', [
            'patients' => PatientResource::collection($patients),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        return Inertia::render('Patients/Create');
    }

    /**
     * Store a newly created patient.
     */
    public function store(StorePatientRequest $request)
    {
        $validated = $request->validated();

        // Handle optional email
        $email = $validated['email'] ?? strtolower($validated['first_name'] . '.' . $validated['last_name'] . '.' . rand(1000, 9999) . '@nyalife-hms.com');

        // Create user account
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $email,
            'phone' => $validated['phone'],
            'username' => strtolower($validated['first_name'] . '.' . $validated['last_name'] . '.' . rand(1000, 9999)),
            'password' => Hash::make('password123'), // Default password
            'role_id' => \App\Models\Role::where('role_name', 'patient')->first()->role_id ?? 7,
            'is_active' => true,
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'address' => $validated['address'] ?? null,
        ]);
        
        // Create patient record
        Patient::create([
            'user_id' => $user->user_id,
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'address' => $validated['address'] ?? null,
            'blood_group' => $validated['blood_group'] ?? null,
            'emergency_name' => $validated['emergency_name'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'patient_number' => 'PAT-' . date('Ymd') . '-' . str_pad($user->user_id, 4, '0', STR_PAD_LEFT),
        ]);
        
        return redirect()->route('patients.index')
                         ->with('success', 'Patient registered successfully.');
    }

    /**
     * Display the specified patient.
     */
    public function show($id)
    {
        $user = Auth::user();

        $with = [
            'user', 
            'appointments.doctor.user',
            'vitals',
        ];

        if ($user && in_array($user->role, ['doctor', 'admin'])) {
            $with[] = 'consultations.doctor.user';
            $with[] = 'prescriptions.items';
        }

        $patient = Patient::with($with)->findOrFail($id);
        
        return Inertia::render('Patients/Show', [
            'patient' => PatientResource::make($patient),
        ]);
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit($id)
    {
        $patient = Patient::with('user')->findOrFail($id);
        return Inertia::render('Patients/Edit', [
            'patient' => PatientResource::make($patient),
        ]);
    }

    /**
     * Update the specified patient.
     */
    public function update(UpdatePatientRequest $request, $id)
    {
        $patient = Patient::with('user')->findOrFail($id);
        $validated = $request->validated();

        // Update user
        $userData = $request->only([
            'first_name',
            'last_name',
            'phone',
            'email',
            'address',
            'gender',
            'date_of_birth',
        ]);
        $patient->user->update($userData);

        // Update patient
        $patientData = $request->only([
            'address',
            'gender',
            'date_of_birth',
            'blood_group',
            'emergency_name',
            'emergency_contact',
        ]);
        $patient->update($patientData);
        
        return redirect()->route('patients.show', $id)->with('success', 'Patient updated successfully.');
    }

    /**
     * Store a newly created patient (Quick Create version).
     */
    public function quickStore(QuickStorePatientRequest $request)
    {
        $validated = $request->validated();

        $email = $validated['email'] ?? strtolower($validated['first_name'] . '.' . $validated['last_name'] . '.' . time() . '@nyalife.com');

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $email,
            'phone' => $validated['phone'],
            'username' => strtolower($validated['first_name'] . '.' . $validated['last_name'] . '.' . time()),
            'password' => Hash::make('password123'),
            'role_id' => \App\Models\Role::where('role_name', 'patient')->first()->role_id ?? 7,
            'is_active' => true,
        ]);
        
        // Create patient record
        $patient = Patient::create([
            'user_id' => $user->user_id,
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'emergency_name' => $validated['emergency_name'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'blood_group' => $validated['blood_group'] ?? null,
            'patient_number' => 'PAT-' . date('Ymd') . '-' . str_pad($user->user_id, 4, '0', STR_PAD_LEFT),
        ]);
        
        return response()->json([
            'success' => true,
            'patient_id' => $patient->patient_id,
            'full_name' => $user->first_name . ' ' . $user->last_name,
            'gender' => $patient->gender,
            'message' => 'Patient created successfully.'
        ]);
    }

    /**
     * Search patients for AJAX selects.
     */
    public function searchAjax(Request $request)
    {
        $search = $request->query('q');
        
        $patients = Patient::with('user')
            ->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->orWhere('patient_id', 'like', "%{$search}%")
            ->limit(20)
            ->get();
            
        return response()->json($patients->map(function($p) {
            return [
                'value' => $p->patient_id,
                'label' => $p->user->first_name . ' ' . $p->user->last_name . ' (PAT-' . $p->patient_id . ')',
                'id' => $p->patient_id
            ];
        }));
    }
}
