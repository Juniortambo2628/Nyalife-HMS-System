<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\LabSample;
use App\Models\Vital;
use App\Models\FollowUp;
use App\Models\RadiologyRequest;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Role;
use App\Models\MedicalProcedure;
use Carbon\Carbon;

class ImportLegacyData extends Command
{
    protected $signature = 'legacy:import {--dry-run : Show what would be imported without saving}';
    protected $description = 'Import legacy data from old database into new schema';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info("🔍 DRY RUN MODE - No data will be saved");
        }

        // Connect to legacy database
        config(['database.connections.legacy' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => 'nyalife_legacy',  // Your legacy DB name
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]]);

        try {
            $legacyDb = DB::connection('legacy');
            $this->info("✅ Connected to legacy database");
        } catch (\Exception $e) {
            $this->error("❌ Cannot connect to legacy database: " . $e->getMessage());
            $this->error("Make sure 'nyalife_legacy' database exists and is accessible");
            return 1;
        }

        $this->info("� Importing data from legacy database...");

        $stats = [
            'users' => 0,
            'patients' => 0,
            'appointments' => 0,
            'consultations' => 0,
            'prescriptions' => 0,
            'invoices' => 0,
            'invoice_items' => 0,
            'payments' => 0,
            'lab_requests' => 0,
            'vitals' => 0,
            'follow_ups' => 0,
            'radiology' => 0,
        ];

        // Import in dependency order
        $this->importUsers($legacyDb, $dryRun, $stats);
        $this->importPatients($legacyDb, $dryRun, $stats);
        $this->importAppointments($legacyDb, $dryRun, $stats);
        $this->importConsultations($legacyDb, $dryRun, $stats);
        $this->importPrescriptions($legacyDb, $dryRun, $stats);
        $this->importInvoices($legacyDb, $dryRun, $stats);
        $this->importPayments($legacyDb, $dryRun, $stats);
        $this->importLabRequests($legacyDb, $dryRun, $stats);
        $this->importVitals($legacyDb, $dryRun, $stats);
        $this->importFollowUps($legacyDb, $dryRun, $stats);

        $this->newLine();
        $this->info("📊 Import Summary:");
        $this->table(['Entity', 'Imported'], collect($stats)->map(fn($v, $k) => [$k, $v])->toArray());
        
        if ($dryRun) {
            $this->warn("⚠️ Dry run complete - no data was saved. Run without --dry-run to execute.");
        } else {
            $this->info("✅ Import complete!");
        }
        
        return 0;
    }

    private function importUsers($legacyDb, $dryRun, &$stats)
    {
        $this->info("👤 Importing users...");
        
        $legacyUsers = $legacyDb->table('users')->where('role_id', '!=', 7)->get(); // Skip patients (role_id=7)
        
        foreach ($legacyUsers as $user) {
            if (User::where('user_id', $user->user_id)->exists()) continue;
            
            $data = [
                'user_id' => $user->user_id,
                'username' => $user->username ?? strtolower($user->first_name . '.' . $user->last_name),
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'password' => $user->password ?: Hash::make('password'),
                'role_id' => $user->role_id ?? 6, // default to patient
                'is_active' => $user->is_active ?? 1,
                'status' => $user->status ?? 'active',
                'gender' => $this->normalizeGender($user->gender),
                'date_of_birth' => $this->parseDate($user->date_of_birth),
                'address' => $user->address,
                'profile_image' => $user->profile_image,
                'last_login' => $user->last_login,
                'email_verified_at' => $user->email_verified_at ?? now(),
                'remember_token' => $user->remember_token,
            ];

            if (!$dryRun) {
                User::create($data);
            }
            $stats['users']++;
        }
        $this->info("  ✅ Imported {$stats['users']} users");
    }

    private function importPatients($legacyDb, $dryRun, &$stats)
    {
        $this->info("🏥 Importing patients...");
        
        $legacyPatients = $legacyDb->table('patients')->get();
        
        foreach ($legacyPatients as $patient) {
            if (Patient::where('patient_id', $patient->patient_id)->exists()) continue;

            // Find or create user
            $user = User::where('user_id', $patient->user_id)->first();
            if (!$user) {
                $user = User::create([
                    'user_id' => $patient->user_id,
                    'username' => strtolower(str_replace(' ', '', $patient->first_name ?? 'patient') . '.' . $patient->last_name ?? 'patient' . $patient->patient_id),
                    'email' => $patient->email ?? "patient{$patient->patient_id}@example.com",
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'phone' => $patient->phone,
                    'password' => Hash::make('password'),
                    'role_id' => Role::where('role_name', 'patient')->first()?->role_id ?? 7,
                    'is_active' => true,
                    'status' => 'active',
                ]);
            }

            Patient::create([
                'patient_id' => $patient->patient_id,
                'user_id' => $user->user_id,
                'patient_number' => 'PAT-' . date('Ymd') . '-' . str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT),
                'date_of_birth' => $this->parseDate($patient->date_of_birth),
                'gender' => $this->normalizeGender($patient->gender),
                'address' => $patient->address,
                'blood_group' => $patient->blood_group,
                'height' => $patient->height,
                'weight' => $patient->weight,
                'allergies' => $patient->allergies,
                'chronic_diseases' => $patient->chronic_diseases,
                'emergency_name' => $patient->emergency_name,
                'emergency_contact' => $patient->emergency_contact,
                'marital_status' => $patient->marital_status,
                'occupation' => $patient->occupation,
                'insurance_provider' => $patient->insurance_provider,
                'insurance_id' => $patient->insurance_id,
                'insurance_number' => $patient->insurance_number,
                'insurance_expiry' => $this->parseDate($patient->insurance_expiry),
            ]);
            $stats['patients']++;
        }
        $this->info("  ✅ Imported {$stats['patients']} patients");
    }

    private function importAppointments($legacyDb, $dryRun, &$stats)
    {
        $legacyAppts = $legacyDb->table('appointments')->get();
        
        foreach ($legacyAppts as $appt) {
            if (Appointment::where('appointment_id', $appt->appointment_id)->exists()) continue;
            
            Appointment::create([
                'appointment_id' => $appt->appointment_id,
                'patient_id' => $appt->patient_id,
                'doctor_id' => $appt->doctor_id, // This is staff.id in old DB
                'appointment_date' => $this->parseDate($appt->appointment_date),
                'appointment_time' => $appt->appointment_time,
                'end_time' => $appt->end_time,
                'appointment_type' => $appt->appointment_type ?? 'general',
                'status' => $appt->status ?? 'scheduled',
                'reason' => $appt->reason,
                'notes' => $appt->notes,
                'created_by' => $appt->created_by,
            ]);
            $stats['appointments']++;
        }
        $this->info("  ✅ Imported {$stats['appointments']} appointments");
    }

    private function importConsultations($legacyDb, $dryRun, &$stats)
    {
        $legacyConsults = $legacyDb->table('consultations')->get();
        
        foreach ($legacyConsults as $consult) {
            if (Consultation::where('consultation_id', $consult->consultation_id)->exists()) continue;
            
            Consultation::create([
                'consultation_id' => $consult->consultation_id,
                'patient_id' => $consult->patient_id,
                'doctor_id' => $consult->doctor_id,
                'appointment_id' => $consult->appointment_id,
                'consultation_date' => $this->parseDate($consult->consultation_date),
                'consultation_status' => $consult->consultation_status ?? 'in_progress',
                'consultation_type' => $consult->consultation_type ?? 'in_person',
                'is_walk_in' => $consult->is_walk_in ?? false,
                'priority' => $consult->priority ?? 'normal',
                'chief_complaint' => $consult->chief_complaint,
                'history_present_illness' => $consult->history_present_illness,
                'past_medical_history' => $consult->past_medical_history,
                'family_history' => $consult->family_history,
                'social_history' => $consult->social_history,
                'obstetric_history' => $consult->obstetric_history,
                'gynecological_history' => $consult->gynecological_history,
                'menstrual_history' => $this->parseJson($consult->menstrual_history),
                'contraceptive_history' => $consult->contraceptive_history,
                'sexual_history' => $consult->sexual_history,
                'review_of_systems' => $consult->review_of_systems,
                'vital_signs' => $this->parseJson($consult->vital_signs),
                'physical_examination' => $consult->physical_examination,
                'general_examination' => $consult->general_examination,
                'systems_examination' => $consult->systems_examination,
                'diagnosis' => $consult->diagnosis,
                'diagnosis_confidence' => $consult->diagnosis_confidence,
                'differential_diagnosis' => $consult->differential_diagnosis,
                'diagnostic_plan' => $consult->diagnostic_plan,
                'treatment_plan' => $consult->treatment_plan,
                'follow_up_instructions' => $consult->follow_up_instructions,
                'notes' => $consult->notes,
                'clinical_summary' => $consult->clinical_summary,
                'parity' => $consult->parity,
                'current_pregnancy' => $consult->current_pregnancy,
                'past_obstetric' => $this->parseJson($consult->past_obstetric),
                'surgical_history' => $consult->surgical_history,
                'cervical_screening' => $consult->cervical_screening,
                'created_by' => $consult->created_by,
            ]);
            $stats['consultations']++;
        }
    }

    private function importPrescriptions($legacyDb, $dryRun, &$stats)
    {
        $legacyRx = $legacyDb->table('prescriptions')->get();
        
        foreach ($legacyRx as $rx) {
            if (Prescription::where('prescription_id', $rx->prescription_id)->exists()) continue;
            
            Prescription::create([
                'prescription_id' => $rx->prescription_id,
                'patient_id' => $rx->patient_id,
                'prescribed_by' => $rx->prescribed_by,
                'appointment_id' => $rx->appointment_id,
                'consultation_id' => $rx->consultation_id,
                'prescription_date' => $this->parseDate($rx->prescription_date),
                'prescription_number' => $rx->prescription_number ?? 'RX-' . strtoupper(substr(uniqid(), -6)),
                'status' => $rx->status ?? 'pending',
                'is_voided' => $rx->is_voided ?? false,
                'void_reason' => $rx->void_reason,
                'voided_by' => $rx->voided_by,
                'voided_at' => $rx->voided_at,
                'notes' => $rx->notes,
                'dispensed_by' => $rx->dispensed_by,
                'dispensed_at' => $rx->dispensed_at,
            ]);
            $stats['prescriptions']++;
        }
    }

    private function importInvoices($legacyDb, $dryRun, &$stats)
    {
        $legacyInvoices = $legacyDb->table('invoices')->get();
        
        foreach ($legacyInvoices as $inv) {
            if (Invoice::where('invoice_id', $inv->invoice_id)->exists()) continue;
            
            Invoice::create([
                'invoice_id' => $inv->invoice_id,
                'patient_id' => $inv->patient_id,
                'consultation_id' => $inv->consultation_id,
                'doctor_id' => $inv->doctor_id,
                'invoice_number' => $inv->invoice_number ?? 'INV-' . strtoupper(substr(uniqid(), -6)),
                'invoice_date' => $this->parseDate($inv->invoice_date),
                'due_date' => $this->parseDate($inv->due_date),
                'total_amount' => $inv->total_amount ?? 0,
                'discount' => $inv->discount ?? 0,
                'tax' => $inv->tax ?? 0,
                'status' => $inv->status ?? 'pending',
                'payment_method' => $inv->payment_method,
                'notes' => $inv->notes,
                'created_by' => $inv->created_by,
                'insurance_claim_id' => $inv->insurance_claim_id,
                'insurance_coverage' => $inv->insurance_coverage,
                'patient_responsibility' => $inv->patient_responsibility,
                'is_voided' => $inv->is_voided ?? false,
                'void_reason' => $inv->void_reason,
                'voided_by' => $inv->voided_by,
                'voided_at' => $inv->voided_at,
            ]);
            $stats['invoices']++;
        }
    }

    private function importPayments($legacyDb, $dryRun, &$stats)
    {
        $legacyPayments = $legacyDb->table('payments')->get();
        
        foreach ($legacyPayments as $pay) {
            if (Payment::where('payment_id', $pay->payment_id)->exists()) continue;
            
            Payment::create([
                'payment_id' => $pay->payment_id,
                'invoice_id' => $pay->invoice_id,
                'amount' => $pay->amount,
                'payment_method' => $pay->payment_method,
                'payment_date' => $this->parseDate($pay->payment_date),
                'transaction_reference' => $pay->transaction_reference,
                'payment_status' => $pay->payment_status ?? 'completed',
                'status' => $pay->status ?? 'completed',
                'notes' => $pay->notes,
                'received_by' => $pay->received_by,
            ]);
            $stats['payments']++;
        }
    }

    private function importLabRequests($legacyDb, $dryRun, &$stats)
    {
        $legacyLabs = $legacyDb->table('lab_test_requests')->get();
        
        foreach ($legacyLabs as $lab) {
            if (LabTestRequest::where('request_id', $lab->request_id)->exists()) continue;
            
            LabTestRequest::create([
                'request_id' => $lab->request_id,
                'request_number' => $lab->request_number,
                'patient_id' => $lab->patient_id,
                'doctor_id' => $lab->doctor_id,
                'test_type_id' => $lab->test_type_id,
                'priority' => $lab->priority ?? 'normal',
                'requested_by' => $lab->requested_by,
                'status' => $lab->status ?? 'pending',
                'results' => $this->parseJson($lab->results),
                'request_date' => $this->parseDate($lab->request_date),
                'completed_at' => $lab->completed_at,
                'assigned_to' => $lab->assigned_to,
                'sample_collected_by' => $lab->sample_collected_by,
                'verified_by' => $lab->verified_by,
                'verified_at' => $lab->verified_at,
                'notes' => $lab->notes,
                'consultation_id' => $lab->consultation_id,
                'appointment_id' => $lab->appointment_id,
            ]);
            $stats['lab_requests']++;
        }
    }

    private function importVitals($legacyDb, $dryRun, &$stats)
    {
        $legacyVitals = $legacyDb->table('vital_signs')->get();
        
        foreach ($legacyVitals as $vital) {
            if (Vital::where('vital_id', $vital->vital_id)->exists()) continue;
            
            Vital::create([
                'vital_id' => $vital->vital_id,
                'patient_id' => $vital->patient_id,
                'consultation_id' => $vital->consultation_id,
                'blood_pressure' => $vital->blood_pressure,
                'heart_rate' => $vital->heart_rate,
                'respiratory_rate' => $vital->respiratory_rate,
                'temperature' => $vital->temperature,
                'weight' => $vital->weight,
                'height' => $vital->height,
                'bmi' => $vital->bmi,
                'pain_level' => $vital->pain_level,
                'oxygen_saturation' => $vital->oxygen_saturation,
                'priority' => $vital->priority ?? 'normal',
                'notes' => $vital->notes,
                'measured_at' => $this->parseDate($vital->measured_at),
                'recorded_by' => $vital->recorded_by,
                'is_voided' => $vital->is_voided ?? false,
                'void_reason' => $vital->void_reason,
                'voided_by' => $vital->voided_by,
                'voided_at' => $vital->voided_at,
            ]);
            $stats['vitals']++;
        }
    }

    private function importFollowUps($legacyDb, $dryRun, &$stats)
    {
        $legacyFollowUps = $legacyDb->table('follow_ups')->get();
        
        foreach ($legacyFollowUps as $fu) {
            if (FollowUp::where('follow_up_id', $fu->follow_up_id)->exists()) continue;
            
            FollowUp::create([
                'follow_up_id' => $fu->follow_up_id,
                'patient_id' => $fu->patient_id,
                'consultation_id' => $fu->consultation_id,
                'follow_up_date' => $this->parseDate($fu->follow_up_date),
                'follow_up_type' => $fu->follow_up_type ?? 'general',
                'reason' => $fu->reason,
                'status' => $fu->status ?? 'scheduled',
                'notes' => $fu->notes,
                'created_by' => $fu->created_by,
            ]);
            $stats['follow_ups']++;
        }
    }

    private function importRadiology($legacyDb, $dryRun, &$stats)
    {
        $legacyRadio = $legacyDb->table('radiology_requests')->get();
        
        foreach ($legacyRadio as $radio) {
            if (RadiologyRequest::where('request_id', $radio->request_id)->exists()) continue;
            
            RadiologyRequest::create([
                'request_id' => $radio->request_id,
                'request_number' => $radio->request_number,
                'patient_id' => $radio->patient_id,
                'doctor_id' => $radio->doctor_id,
                'scan_type' => $radio->scan_type,
                'clinical_indication' => $radio->clinical_indication,
                'scan_details' => $radio->scan_details,
                'findings' => $radio->findings,
                'impression' => $radio->impression,
                'priority' => $radio->priority ?? 'routine',
                'status' => $radio->status ?? 'pending',
                'requested_by' => $radio->requested_by,
                'assigned_to' => $radio->assigned_to,
                'verified_by' => $radio->verified_by,
                'verified_at' => $radio->verified_at,
                'completed_at' => $radio->completed_at,
                'consultation_id' => $radio->consultation_id,
                'appointment_id' => $radio->appointment_id,
            ]);
            $stats['radiology']++;
        }
    }

    private function normalizeGender($gender)
    {
        $g = strtolower(trim($gender ?? ''));
        if (in_array($g, ['male', 'm'])) return 'male';
        if (in_array($g, ['female', 'f'])) return 'female';
        return 'other';
    }

    private function parseDate($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return null;
        }
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseJson($str)
    {
        if (empty($str) || $str === 'null' || $str === 'NULL') return null;
        $decoded = json_decode($str, true);
        return $decoded ?? null;
    }
}