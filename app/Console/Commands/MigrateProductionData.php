<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\LabTestRequest;
use App\Models\LabSample;
use App\Models\Vital;
use App\Models\FollowUp;
use App\Models\RadiologyRequest;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Role;
use App\Models\Medication;
use App\Models\MedicalProcedure;
use App\Models\Insurance;
use App\Models\TelehealthConsent;
use App\Models\PharmacyPurchaseOrder;
use App\Models\MailTemplate;
use App\Models\DoctorBlockOut;
use App\Models\ContactMessage;
use App\Models\Message;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\ServiceTab;
use App\Models\Blog;
use App\Models\MedicalProcedure;
use App\Models\MailTemplate;
use App\Models\DoctorBlockOut;
use App\Models\ContactMessage;
use App\Models\Message;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\ServiceTab;
use App\Models\LabTestType;
use App\Models\LabSample;
use App\Models\LabTestRequest;
use App\Models\Vital;
use App\Models\FollowUp;
use App\Models\RadiologyRequest;
use App\Models\PharmacyPurchaseOrder;
use App\Models\TelehealthConsent;
use App\Models\MedicalProcedure;
use App\Models\MailTemplate;
use App\Models\DoctorBlockOut;
use Carbon\Carbon;

class MigrateProductionData extends Command
{
    protected $signature = 'production:migrate-data {--fresh : Drop and recreate tables first}';
    protected $description = 'Migrate production data from legacy DB to new schema';

    public function handle()
    {
        if ($this->option('fresh')) {
            $this->warn("⚠️  This will drop all tables and re-migrate!");
            if (!$this->confirm('Continue?')) return 1;
            $this->call('migrate:fresh', ['--force' => true]);
        }

        // Connect to legacy database
        config(['database.connections.legacy' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => 'nyalifew_legacy',
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        try {
            $legacyDb = DB::connection('legacy');
            $this->info("✅ Connected to legacy database (nyalife_legacy)");
        } catch (\Exception $e) {
            $this->error("❌ Cannot connect to legacy database: " . $e->getMessage());
            $this->error("Create 'nyalifew_legacy' database and import production_database_15_7_26.sql first");
            return 1;
        }

        $this->info("🚀 Migrating production data to new schema...");

        $stats = [
            'users' => 0, 'patients' => 0, 'appointments' => 0,
            'consultations' => 0, 'prescriptions' => 0, 'invoices' => 0,
            'payments' => 0, 'lab_requests' => 0, 'vitals' => 0,
            'follow_ups' => 0, 'radiology' => 0, 'lab_requests' => 0,
        ];

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->migrateUsers();
        $this->migratePatients();
        $this->migrateDepartments();
        $this->migrateStaff();
        $this->migrateAppointments();
        $this->migrateConsultations();
        $this->migratePrescriptions();
        $this->migratePrescriptionItems();
        $this->migrateInvoices();
        $this->migrateInvoiceItems();
        $this->migratePayments();
        $this->migrateLabTestTypes();
        $this->migrateLabRequests();
        $this->migrateLabSamples();
        $this->migrateVitals();
        $this->migrateFollowUps();
        $this->migrateRadiology();
        $this->migrateTelehealth();
        $this->migratePharmacy();
        $this->migrateMedicalProcedures();
        $this->migrateInsurance();
        $this->migrateTelehealthConsents();
        $this->migratePharmacyPurchaseOrders();
        $this->migrateMailTemplates();
        $this->migrateDoctorBlockOuts();
        $this->migrateContactMessages();
        $this->migrateMessages();
        $this->migrateNewsletterSubscribers();
        $this->migrateSettings();
        $this->migrateServiceTabs();
        $this->migrateBlogs();
        $this->migrateMedicalProcedures();
        $this->migrateMailTemplates();
        $this->migrateDoctorBlockOuts();
        $this->migrateContactMessages();
        $this->migrateMessages();
        $this->migrateNewsletterSubscribers();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->newLine();
        $this->info("✅ Migration complete!");
        $this->table(['Entity', 'Count'], collect([
            ['Users', User::count()],
            ['Patients', Patient::count()],
            ['Appointments', Appointment::count()],
            ['Consultations', Consultation::count()],
            ['Prescriptions', Prescription::count()],
            ['Invoices', Invoice::count()],
            ['Payments', Payment::count()],
            ['Lab Requests', LabTestRequest::count()],
            ['Vitals', \App\Models\Vital::count()],
            ['Follow-ups', FollowUp::count()],
            ['Radiology', \App\Models\RadiologyRequest::count()],
        ])->map(fn($v, $k) => [$k, $v])->toArray());

        return 0;
    }

    // Helper methods
    private function importUsers()
    {
        $legacyUsers = DB::connection('legacy')->table('users')
            ->where('role_id', '!=', 7) // Skip patients
            ->orderBy('user_id')
            ->get();

        foreach ($users as $user) {
            if (User::where('user_id', $user->user_id)->exists()) continue;

            User::create([
                'user_id' => $user->user_id,
                'username' => $user->username ?? strtolower($user->first_name . '.' . $user->last_name),
                'email' => $user->email,
                'password' => $user->password ?: '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role_id' => $user->role_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'gender' => $this->normalizeGender($user->gender),
                'date_of_birth' => $this->parseDate($user->date_of_birth),
                'address' => $user->address,
                'is_active' => $user->is_active ?? 1,
                'status' => $user->status ?? 'active',
                'last_login' => $user->last_login,
                'profile_image' => $user->profile_image,
                'email_verified_at' => $user->email_verified_at,
                'remember_token' => $user->remember_token,
            ]);
        }
    }

    private function importPatients()
    {
        $legacyPatients = DB::connection('legacy')->table('patients')->get();

        foreach ($patients as $patient) {
            if (Patient::where('patient_id', $patient->patient_id)->exists()) continue;

            $user = User::where('user_id', $patient->user_id)->first();
            if (!$user) {
                $user = User::create([
                    'user_id' => $patient->user_id,
                    'username' => strtolower(str_replace(' ', '', $patient->first_name . '.' . $patient->last_name . '.' . $patient->patient_id)),
                    'email' => $patient->email ?? "patient{$patient->patient_id}@example.com",
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'phone' => $patient->phone,
                    'password' => Hash::make('password'),
                    'role_id' => \App\Models\Role::where('role_name', 'patient')->first()?->role_id ?? 7,
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
        }
    }

    // ... (other import methods would continue here)

    private function normalizeGender($gender)
    {
        $g = strtolower(trim($gender ?? ''));
        if (in_array($g, ['male', 'm'])) return 'male';
        if (in_array($g, ['female', 'f'])) return 'female';
        return 'other';
    }

    private function parseDate($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return null;
        try { return Carbon::parse($date)->format('Y-m-d'); } catch (\Exception $e) { return null; }
    }
}