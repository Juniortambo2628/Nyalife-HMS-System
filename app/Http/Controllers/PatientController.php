<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuickStorePatientRequest;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use App\Support\PatientId;
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
            ->when($request->status, function($q, $status) {
                if ($status === 'male') return $q->where('gender', 'male');
                if ($status === 'female') return $q->where('gender', 'female');
                if ($status === 'recent') return $q->where('created_at', '>=', now()->subDays(7));
                return $q;
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        
        return Inertia::render('Patients/Index', [
            'patients' => PatientResource::collection($patients),
            'filters' => $request->only(['search', 'status']),
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

        // Handle optional email - remove spaces to comply with RFC 2822
        $safeFirstName = str_replace(' ', '', $validated['first_name']);
        $safeLastName = str_replace(' ', '', $validated['last_name']);
        $email = $validated['email'] ?? strtolower($safeFirstName . '.' . $safeLastName . '.' . rand(1000, 9999) . '@nyalife-hms.com');

        // Create user account
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $email,
            'phone' => $validated['phone'],
            'username' => strtolower($safeFirstName . '.' . $safeLastName . '.' . rand(1000, 9999)),
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
            'height' => $validated['height'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
            'chronic_diseases' => $validated['chronic_diseases'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'insurance_provider' => $validated['insurance_provider'] ?? null,
            'insurance_id' => $validated['insurance_id'] ?? null,
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
            'appointments' => fn ($q) => $q->with('doctor.user')->latest('appointment_date'),
            'vitals' => fn ($q) => $q->latest('measured_at'),
        ];

        if ($user && in_array($user->role, ['doctor', 'admin', 'nurse'])) {
            $with['consultations'] = fn ($q) => $q->with('doctor.user')->latest('consultation_date');
        }

        if ($user && in_array($user->role, ['doctor', 'admin'])) {
            $with['prescriptions'] = fn ($q) => $q->with('items.medication')->latest('prescription_date');
        }

        $patient = Patient::with($with)->findOrFail($id);

        $clinicalSummary = null;
        if ($user && in_array($user->role, ['doctor', 'admin', 'nurse'])) {
            $latestConsultation = Consultation::latestHistoryForPatient((int) $id);
            if ($latestConsultation) {
                $clinicalSummary = $latestConsultation->toClinicalSummary();
            }
        }
        
        return Inertia::render('Patients/Show', [
            'patient' => PatientResource::make($patient),
            'clinical_summary' => $clinicalSummary,
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
            'height',
            'weight',
            'allergies',
            'chronic_diseases',
            'marital_status',
            'occupation',
            'insurance_provider',
            'insurance_id',
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

        // Remove spaces to comply with RFC 2822
        $safeFirstName = str_replace(' ', '', $validated['first_name']);
        $safeLastName = str_replace(' ', '', $validated['last_name']);
        $email = $validated['email'] ?? strtolower($safeFirstName . '.' . $safeLastName . '.' . time() . '@nyalife.com');

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $email,
            'phone' => $validated['phone'],
            'username' => strtolower($safeFirstName . '.' . $safeLastName . '.' . time()),
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
            'select_label' => PatientId::fromPatient($patient),
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
                'label' => PatientId::fromPatient($p),
                'id' => $p->patient_id
            ];
        }));
    }

    /**
     * Import patients from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return redirect()->back()->with('error', 'Unable to open uploaded CSV file.');
        }

        $header = fgetcsv($handle, 1000, ',');
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file is empty or malformed.');
        }

        // Clean headers (remove BOM/spaces/lowercase)
        $header = array_map(function($h) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x7F-\x9F\xEF\xBB\xBF]/', '', $h)));
        }, $header);

        $requiredHeaders = ['first_name', 'last_name', 'email', 'phone', 'gender', 'date_of_birth'];
        $missing = array_diff($requiredHeaders, $header);

        if (count($missing) > 0) {
            fclose($handle);
            return redirect()->back()->with('error', 'Missing required CSV headers: ' . implode(', ', $missing) . '. Please ensure headers are present.');
        }

        $importedCount = 0;
        $skippedCount = 0;
        $roleId = \App\Models\Role::where('role_name', 'patient')->first()->role_id ?? 7;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) < count($header)) {
                    continue; // Skip malformed rows
                }

                $data = array_combine($header, array_slice($row, 0, count($header)));

                // Check if user already exists
                $existingUser = User::where('email', trim($data['email']))
                    ->orWhere('phone', trim($data['phone']))
                    ->first();

                if ($existingUser) {
                    $skippedCount++;
                    continue;
                }

                $safeFirstName = str_replace(' ', '', $data['first_name']);
                $safeLastName = str_replace(' ', '', $data['last_name']);
                $username = strtolower($safeFirstName . '.' . $safeLastName . '.' . rand(1000, 9999));

                $user = User::create([
                    'first_name' => trim($data['first_name']),
                    'last_name' => trim($data['last_name']),
                    'email' => trim($data['email']),
                    'phone' => trim($data['phone']),
                    'username' => $username,
                    'password' => Hash::make('password123'), // Default temporary password
                    'role_id' => $roleId,
                    'is_active' => true,
                    'gender' => trim(strtolower($data['gender'])),
                    'date_of_birth' => trim($data['date_of_birth']),
                    'address' => isset($data['address']) ? trim($data['address']) : null,
                ]);

                Patient::create([
                    'user_id' => $user->user_id,
                    'date_of_birth' => trim($data['date_of_birth']),
                    'gender' => trim(strtolower($data['gender'])),
                    'address' => isset($data['address']) ? trim($data['address']) : null,
                    'blood_group' => isset($data['blood_group']) ? trim($data['blood_group']) : null,
                    'emergency_name' => isset($data['emergency_name']) ? trim($data['emergency_name']) : null,
                    'emergency_contact' => isset($data['emergency_contact']) ? trim($data['emergency_contact']) : null,
                    'patient_number' => 'PAT-' . date('Ymd') . '-' . str_pad($user->user_id, 4, '0', STR_PAD_LEFT),
                ]);

                $importedCount++;
            }
            
            fclose($handle);
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            fclose($handle);
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Patient CSV Import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', "CSV Import completed. Imported: {$importedCount}, Skipped (Duplicates): {$skippedCount}.");
    }

    /**
     * Export selected patients as a CSV file.
     */
    public function export(Request $request)
    {
        $ids = $request->ids ? explode(',', $request->ids) : [];

        $query = Patient::with('user');
        if (!empty($ids)) {
            $query->whereIn('patient_id', $ids);
        }
        $patients = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="patients-export-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($patients) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Gender', 'Date of Birth', 'Blood Group', 'Address', 'Registered']);
            foreach ($patients as $p) {
                fputcsv($handle, [
                    'PAT-' . $p->patient_id,
                    $p->user->first_name ?? '',
                    $p->user->last_name ?? '',
                    $p->user->email ?? '',
                    $p->user->phone ?? '',
                    $p->gender ?? '',
                    $p->date_of_birth ?? '',
                    $p->blood_group ?? '',
                    $p->address ?? '',
                    $p->created_at?->format('Y-m-d') ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display printable ID cards for selected patients.
     */
    public function printCards(Request $request)
    {
        $ids = $request->ids ? explode(',', $request->ids) : [];

        $query = Patient::with('user');
        if (!empty($ids)) {
            $query->whereIn('patient_id', $ids);
        }
        $patients = $query->get();

        return Inertia::render('Patients/PrintCards', [
            'patients' => $patients,
        ]);
    }

    /**
     * Handle bulk actions on patients.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:export,print_cards',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
        ]);

        // Client-side print/export is handled in the browser; this is a fallback.
        return redirect()->back()->with('success', count($validated['ids']) . ' patient records processed.');
    }
}
