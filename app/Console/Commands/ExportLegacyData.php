<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportLegacyData extends Command
{
    protected $signature = 'legacy:export {output-file=legacy-data-new-schema.sql} {--dry-run : Show what would be exported without saving}';

    protected $description = 'Export legacy data as SQL file matching new schema';

    public function handle()
    {
        $outputFile = $this->argument('output-file');
        $dryRun = $this->option('dry-run');

        $this->info('📤 Exporting legacy data as SQL matching new schema...');

        // Connect to legacy database
        config(['database.connections.legacy' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => 'nyalife_legacy',
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        try {
            $legacyDb = DB::connection('legacy');
            $this->info('✅ Connected to legacy database');
        } catch (\Exception $e) {
            $this->error('❌ Cannot connect to legacy database: '.$e->getMessage());
            $this->error("Make sure 'nyalife_legacy' database exists and is accessible");

            return 1;
        }

        $sql = $this->generateSql();

        if ($dryRun) {
            $this->info('🔍 DRY RUN - First 1000 chars of SQL:');
            $this->line(substr($sql, 0, 1000).'...');

            return 0;
        }

        file_put_contents($outputFile, $sql);
        $this->info("✅ Exported to: {$outputFile}");
        $this->info('📏 File size: '.number_format(strlen($sql) / 1024, 2).' KB');

        return 0;
    }

    private function generateSql()
    {
        $legacyDb = DB::connection('legacy');
        $sql = "-- Legacy data export for new schema\n";
        $sql .= '-- Generated: '.now()->toDateTimeString()."\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $sql .= $this->exportUsers($legacyDb);
        $sql .= $this->exportPatients($legacyDb);
        $sql .= $this->exportDepartments($legacyDb);
        $sql .= $this->exportStaff($legacyDb);
        $sql .= $this->exportAppointments($legacyDb);
        $sql .= $this->exportConsultations($legacyDb);
        $sql .= $this->exportPrescriptions($legacyDb);
        $sql .= $this->exportPrescriptionItems($legacyDb);
        $sql .= $this->exportInvoices($legacyDb);
        $sql .= $this->exportInvoiceItems($legacyDb);
        $sql .= $this->exportPayments($legacyDb);
        $sql .= $this->exportLabTestTypes($legacyDb);
        $sql .= $this->exportLabRequests($legacyDb);
        $sql .= $this->exportLabSamples($legacyDb);
        $sql .= $this->exportVitals($legacyDb);
        $sql .= $this->exportFollowUps($legacyDb);
        $sql .= $this->exportRadiology($legacyDb);
        $sql .= $this->exportTelehealth($legacyDb);
        $sql .= $this->exportPharmacy($legacyDb);
        $sql .= $this->exportMedicalProcedures($legacyDb);
        $sql .= $this->exportInsurance($legacyDb);
        $sql .= $this->exportOtherTables($legacyDb);

        $sql .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    private function exportUsers($legacyDb)
    {
        $users = $legacyDb->table('users')
            ->where('role_id', '!=', 7)
            ->orderBy('user_id')
            ->get();

        if ($users->isEmpty()) {
            return "-- No users to export\n\n";
        }

        $sql = "-- Users (staff)\n";
        $sql .= "INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role_id`, `first_name`, `last_name`, `phone`, `gender`, `date_of_birth`, `address`, `is_active`, `status`, `last_login`, `profile_image`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($users as $user) {
            $password = $user->password ?: '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
            $rows[] = "({$user->user_id}, ".$this->escape($user->username).', '.$this->escape($user->email).', '.$this->escape($user->password ?: '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi').", {$user->role_id}, ".$this->escape($user->first_name).', '.$this->escape($user->last_name).', '.$this->escape($user->phone).', '.$this->escape($this->normalizeGender($user->gender)).', '.$this->escape($this->formatDate($user->date_of_birth)).', '.$this->escape($user->address).", {$user->is_active}, ".$this->escape($user->status ?? 'active').', '.$this->escape($user->last_login).', '.$this->escape($user->profile_image).', '.$this->escape($user->email_verified_at).', '.$this->escape($user->remember_token).', '.$this->escape($user->created_at).', '.$this->escape($user->updated_at).')';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportPatients()
    {
        $legacyDb = DB::connection('legacy');
        $patients = $legacyDb->table('patients')->orderBy('patient_id')->get();

        if ($patients->isEmpty()) {
            return "-- No patients to export\n\n";
        }

        $sql = "-- Patients\n";
        $sql .= "INSERT INTO `patients` (`patient_id`, `user_id`, `patient_number`, `date_of_birth`, `gender`, `address`, `blood_group`, `height`, `weight`, `allergies`, `chronic_diseases`, `emergency_name`, `emergency_contact`, `marital_status`, `occupation`, `insurance_provider`, `insurance_id`, `insurance_number`, `insurance_expiry`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($patients as $p) {
            $userId = $p->user_id;
            $patientNumber = 'PAT-'.date('Ymd').'-'.str_pad($p->patient_id, 4, '0', STR_PAD_LEFT);

            $rows[] = "({$p->patient_id}, {$userId}, ".$this->escape($patientNumber).', '.$this->escape($this->formatDate($p->date_of_birth)).', '.$this->escape($this->normalizeGender($p->gender)).', '.$this->escape($p->address).', '.$this->escape($p->blood_group).', '.$this->escape($p->height).', '.$this->escape($p->weight).', '.$this->escape($p->allergies).', '.$this->escape($p->chronic_diseases).', '.$this->escape($p->emergency_name).', '.$this->escape($p->emergency_contact).', '.$this->escape($p->marital_status).', '.$this->escape($p->occupation).', '.$this->escape($p->insurance_provider).', '.$this->escape($p->insurance_id).', '.$this->escape($p->insurance_number).', '.$this->escape($this->formatDate($p->insurance_expiry)).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportDepartments()
    {
        $legacyDb = DB::connection('legacy');
        $departments = $legacyDb->table('departments')->orderBy('department_id')->get();

        if ($departments->isEmpty()) {
            return "-- No departments to export\n\n";
        }

        $sql = "-- Departments\n";
        $sql .= "INSERT INTO `departments` (`department_id`, `department_name`, `description`, `is_active`, `code`, `type`, `head_name`, `head_position`, `head_image`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($departments as $d) {
            $rows[] = "({$d->department_id}, ".$this->escape($d->department_name).', '.$this->escape($d->description).', '.($d->is_active ?? 1).', '.$this->escape($d->code).', '.$this->escape($d->type ?? 'clinical').', '.$this->escape($d->head_name).', '.$this->escape($d->head_position).', '.$this->escape($d->head_image).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportStaff()
    {
        $legacyDb = DB::connection('legacy');
        $staff = $legacyDb->table('staff')->orderBy('staff_id')->get();

        if ($staff->isEmpty()) {
            return "-- No staff to export\n\n";
        }

        $sql = "-- Staff\n";
        $sql .= "INSERT INTO `staff` (`staff_id`, `user_id`, `employee_id`, `specialization`, `department`, `department_id`, `position`, `license_number`, `qualification`, `join_date`, `emergency_contact`, `emergency_name`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($staff as $s) {
            $rows[] = "({$s->staff_id}, {$s->user_id}, ".$this->escape($s->employee_id).', '.$this->escape($s->specialization).', '.$this->escape($s->department).', '.($s->department_id ?? 'NULL').', '.$this->escape($s->position).', '.$this->escape($s->license_number).', '.$this->escape($s->qualification).', '.$this->escape($this->formatDate($s->join_date)).', '.$this->escape($s->emergency_contact).', '.$this->escape($s->emergency_name).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportAppointments()
    {
        $legacyDb = DB::connection('legacy');
        $appts = $legacyDb->table('appointments')->orderBy('appointment_id')->get();

        if ($appts->isEmpty()) {
            return "-- No appointments to export\n\n";
        }

        $sql = "-- Appointments\n";
        $sql .= "INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `end_time`, `appointment_type`, `status`, `reason`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($appts as $a) {
            $rows[] = "({$a->appointment_id}, {$a->patient_id}, {$a->doctor_id}, ".$this->escape($this->formatDate($a->appointment_date)).', '.$this->escape($a->appointment_time).', '.$this->escape($a->end_time).', '.$this->escape($a->appointment_type ?? 'general').', '.$this->escape($a->status ?? 'scheduled').', '.$this->escape($a->reason).', '.$this->escape($a->notes).", {$a->created_by}, ".$this->escape($a->created_at).', '.$this->escape($a->updated_at).')';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportConsultations()
    {
        $legacyDb = DB::connection('legacy');
        $consults = $legacyDb->table('consultations')->orderBy('consultation_id')->get();

        if ($consults->isEmpty()) {
            return "-- No consultations to export\n\n";
        }

        $sql = "-- Consultations\n";
        $sql .= "INSERT INTO `consultations` (`consultation_id`, `patient_id`, `doctor_id`, `appointment_id`, `consultation_date`, `consultation_status`, `consultation_type`, `meeting_link`, `meeting_platform`, `is_walk_in`, `priority`, `chief_complaint`, `history_present_illness`, `past_medical_history`, `family_history`, `social_history`, `obstetric_history`, `gynecological_history`, `menstrual_history`, `cervical_screening`, `contraceptive_history`, `sexual_history`, `review_of_systems`, `vital_signs`, `physical_examination`, `general_examination`, `systems_examination`, `diagnosis`, `diagnosis_confidence`, `differential_diagnosis`, `diagnostic_plan`, `treatment_plan`, `follow_up_instructions`, `notes`, `clinical_summary`, `parity`, `current_pregnancy`, `past_obstetric`, `surgical_history`, `cervical_screening`, `created_by`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($consults as $c) {
            $rows[] = "({$c->consultation_id}, {$c->patient_id}, {$c->doctor_id}, ".($c->appointment_id ? "{$c->appointment_id}" : 'NULL').', '.$this->escape($c->consultation_date).', '.$this->escape($c->consultation_status ?? 'in_progress').', '.$this->escape($c->consultation_type ?? 'in_person').', '.$this->escape($c->meeting_link).', '.$this->escape($c->meeting_platform).', '.($c->is_walk_in ? 1 : 0).', '.$this->escape($c->priority ?? 'normal').', '.$this->escape($c->chief_complaint).', '.$this->escape($c->history_present_illness).', '.$this->escape($c->past_medical_history).', '.$this->escape($c->family_history).', '.$this->escape($c->social_history).', '.$this->escape($c->obstetric_history).', '.$this->escape($c->gynecological_history).', '.$this->escape(json_encode($c->menstrual_history ?? null)).', '.$this->escape($c->cervical_screening).', '.$this->escape($c->contraceptive_history).', '.$this->escape($c->sexual_history).', '.$this->escape($c->review_of_systems).', '.$this->escape(json_encode($c->vital_signs ?? null)).', '.$this->escape($c->physical_examination).', '.$this->escape($c->general_examination).', '.$this->escape($c->systems_examination).', '.$this->escape($c->diagnosis).', '.$this->escape($c->diagnosis_confidence).', '.$this->escape($c->differential_diagnosis).', '.$this->escape($c->diagnostic_plan).', '.$this->escape($c->treatment_plan).', '.$this->escape($c->follow_up_instructions).', '.$this->escape($c->notes).', '.$this->escape($c->clinical_summary).', '.$this->escape($c->parity).', '.$this->escape($c->current_pregnancy).', '.$this->escape(json_encode($c->past_obstetric ?? null)).', '.$this->escape($c->surgical_history).', '.$this->escape($c->cervical_screening).", {$c->created_by}, NOW(), NOW())";
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportPrescriptions()
    {
        $legacyDb = DB::connection('legacy');
        $rx = $legacyDb->table('prescriptions')->orderBy('prescription_id')->get();

        if ($rx->isEmpty()) {
            return "-- No prescriptions to export\n\n";
        }

        $sql = "-- Prescriptions\n";
        $sql .= "INSERT INTO `prescriptions` (`prescription_id`, `patient_id`, `prescribed_by`, `appointment_id`, `consultation_id`, `prescription_date`, `prescription_number`, `status`, `is_voided`, `void_reason`, `voided_by`, `voided_at`, `notes`, `dispensed_by`, `dispensed_at`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($rx as $r) {
            $rows[] = "({$r->prescription_id}, {$r->patient_id}, {$r->prescribed_by}, ".($r->appointment_id ? "{$r->appointment_id}" : 'NULL').', '.($r->consultation_id ? "{$r->consultation_id}" : 'NULL').', '.$this->escape($this->formatDate($r->prescription_date)).', '.$this->escape($r->prescription_number ?? 'RX-'.strtoupper(substr(uniqid(), -6))).', '.$this->escape($r->status ?? 'pending').', '.($r->is_voided ? 1 : 0).', '.$this->escape($r->void_reason).', '.($r->voided_by ?? 'NULL').', '.$this->escape($r->voided_at).', '.$this->escape($r->notes).', '.($r->dispensed_by ?? 'NULL').', '.$this->escape($r->dispensed_at).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportPrescriptionItems()
    {
        $legacyDb = DB::connection('legacy');
        $items = $legacyDb->table('prescription_items')->orderBy('item_id')->get();

        if ($items->isEmpty()) {
            return "-- No prescription items to export\n\n";
        }

        $sql = "-- Prescription Items\n";
        $sql .= "INSERT INTO `prescription_items` (`item_id`, `prescription_id`, `medication_id`, `dosage`, `frequency`, `quantity`, `duration`, `instructions`, `status`, `dispensed_by`, `dispensed_at`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($items as $i) {
            $rows[] = "({$i->item_id}, {$i->prescription_id}, ".($i->medication_id ?? 'NULL').', '.$this->escape($i->dosage).', '.$this->escape($i->frequency).", {$i->quantity}, ".$this->escape($i->duration).', '.$this->escape($i->instructions).', '.$this->escape($i->status ?? 'pending').', '.($i->dispensed_by ?? 'NULL').', '.$this->escape($i->dispensed_at).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportInvoices()
    {
        $legacyDb = DB::connection('legacy');
        $invoices = $legacyDb->table('invoices')->orderBy('invoice_id')->get();

        if ($invoices->isEmpty()) {
            return "-- No invoices to export\n\n";
        }

        $sql = "-- Invoices\n";
        $sql .= "INSERT INTO `invoices` (`invoice_id`, `patient_id`, `consultation_id`, `doctor_id`, `invoice_number`, `invoice_date`, `due_date`, `total_amount`, `discount`, `tax`, `status`, `payment_method`, `notes`, `created_by`, `insurance_claim_id`, `insurance_coverage`, `patient_responsibility`, `is_voided`, `void_reason`, `voided_by`, `voided_at`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($invoices as $inv) {
            $rows[] = "({$inv->invoice_id}, {$inv->patient_id}, ".($inv->consultation_id ?? 'NULL').', '.($inv->doctor_id ?? 'NULL').', '.$this->escape($inv->invoice_number ?? 'INV-'.strtoupper(substr(uniqid(), -6))).', '.$this->escape($this->formatDate($inv->invoice_date)).', '.$this->escape($this->formatDate($inv->due_date)).", {$inv->total_amount}, {$inv->discount}, {$inv->tax}, ".$this->escape($inv->status ?? 'pending').', '.$this->escape($inv->payment_method).', '.$this->escape($inv->notes).", {$inv->created_by}, ".$this->escape($inv->insurance_claim_id).', '.$this->escape($inv->insurance_coverage).', '.$this->escape($inv->patient_responsibility).', '.($inv->is_voided ? 1 : 0).', '.$this->escape($inv->void_reason).', '.($inv->voided_by ?? 'NULL').', '.$this->escape($inv->voided_at).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportInvoiceItems()
    {
        $legacyDb = DB::connection('legacy');
        $items = $legacyDb->table('invoice_items')->orderBy('item_id')->get();

        if ($items->isEmpty()) {
            return "-- No invoice items to export\n\n";
        }

        $sql = "-- Invoice Items\n";
        $sql .= "INSERT INTO `invoice_items` (`item_id`, `invoice_id`, `item_type`, `item_id_ref`, `description`, `quantity`, `unit_price`, `total_price`, `discount`, `tax`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($items as $i) {
            $rows[] = "({$i->item_id}, {$i->invoice_id}, ".$this->escape($i->item_type).', '.($i->item_id_ref ?? 'NULL').', '.$this->escape($i->description).", {$i->quantity}, {$i->unit_price}, {$i->total_price}, {$i->discount}, {$i->tax}, NOW(), NOW())";
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportPayments()
    {
        $legacyDb = DB::connection('legacy');
        $payments = $legacyDb->table('payments')->orderBy('payment_id')->get();

        if ($payments->isEmpty()) {
            return "-- No payments to export\n\n";
        }

        $sql = "-- Payments\n";
        $sql .= "INSERT INTO `payments` (`payment_id`, `invoice_id`, `amount`, `payment_method`, `payment_date`, `transaction_reference`, `payment_status`, `status`, `notes`, `received_by`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($payments as $p) {
            $rows[] = "({$p->payment_id}, {$p->invoice_id}, {$p->amount}, ".$this->escape($p->payment_method).', '.$this->escape($p->payment_date).', '.$this->escape($p->transaction_reference).', '.$this->escape($p->payment_status ?? 'completed').', '.$this->escape($p->status ?? 'completed').', '.$this->escape($p->notes).", {$p->received_by}, NOW(), NOW())";
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportLabTestTypes()
    {
        $legacyDb = DB::connection('legacy');
        $types = $legacyDb->table('lab_test_types')->orderBy('test_type_id')->get();

        if ($types->isEmpty()) {
            return "-- No lab test types to export\n\n";
        }

        $sql = "-- Lab Test Types\n";
        $sql .= "INSERT INTO `lab_test_types` (`test_type_id`, `test_name`, `description`, `category`, `price`, `normal_range`, `units`, `template`, `is_active`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($types as $t) {
            $rows[] = "({$t->test_type_id}, ".$this->escape($t->test_name).', '.$this->escape($t->description).', '.$this->escape($t->category).", {$t->price}, ".$this->escape($t->normal_range).', '.$this->escape($t->units).', '.$this->escape($t->template).', '.($t->is_active ? 1 : 0).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportLabRequests()
    {
        $legacyDb = DB::connection('legacy');
        $requests = $legacyDb->table('lab_test_requests')->orderBy('request_id')->get();

        if ($requests->isEmpty()) {
            return "-- No lab requests to export\n\n";
        }

        $sql = "-- Lab Test Requests\n";
        $sql .= "INSERT INTO `lab_test_requests` (`request_id`, `request_number`, `patient_id`, `doctor_id`, `test_type_id`, `priority`, `requested_by`, `status`, `results`, `request_date`, `completed_at`, `assigned_to`, `sample_collected_by`, `verified_by`, `verified_at`, `notes`, `consultation_id`, `appointment_id`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($requests as $r) {
            $rows[] = "({$r->request_id}, ".$this->escape($r->request_number).", {$r->patient_id}, {$r->doctor_id}, {$r->test_type_id}, ".$this->escape($r->priority ?? 'normal').", {$r->requested_by}, ".$this->escape($r->status ?? 'pending').', '.$this->escape(json_encode($r->results ?? null)).', '.$this->escape($r->request_date).', '.$this->escape($r->completed_at).', '.($r->assigned_to ?? 'NULL').', '.($r->sample_collected_by ?? 'NULL').', '.($r->verified_by ?? 'NULL').', '.$this->escape($r->verified_at).', '.$this->escape($r->notes).', '.($r->consultation_id ?? 'NULL').', '.($r->appointment_id ?? 'NULL').', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportLabSamples()
    {
        $legacyDb = DB::connection('legacy');
        $samples = $legacyDb->table('lab_samples')->orderBy('id')->get();

        if ($samples->isEmpty()) {
            return "-- No lab samples to export\n\n";
        }

        $sql = "-- Lab Samples\n";
        $sql .= "INSERT INTO `lab_samples` (`id`, `sample_id`, `patient_id`, `test_type_id`, `sample_type`, `collected_date`, `collected_by`, `collected_at`, `status`, `completed_by`, `completed_at`, `notes`, `urgent`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($samples as $s) {
            $rows[] = "({$s->id}, ".$this->escape($s->sample_id).", {$s->patient_id}, ".($s->test_type_id ?? 'NULL').', '.$this->escape($s->sample_type ?? 'blood').', '.$this->escape($this->formatDate($s->collected_date)).', '.($s->collected_by ?? 'NULL').', '.$this->escape($s->collected_at).', '.$this->escape($s->status ?? 'registered').', '.($s->completed_by ?? 'NULL').', '.$this->escape($s->completed_at).', '.$this->escape($s->notes).', '.($s->urgent ? 1 : 0).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportVitals()
    {
        $legacyDb = DB::connection('legacy');
        $vitals = $legacyDb->table('vital_signs')->orderBy('vital_id')->get();

        if ($vitals->isEmpty()) {
            return "-- No vitals to export\n\n";
        }

        $sql = "-- Vital Signs\n";
        $sql .= "INSERT INTO `vital_signs` (`vital_id`, `patient_id`, `consultation_id`, `blood_pressure`, `heart_rate`, `respiratory_rate`, `temperature`, `weight`, `height`, `bmi`, `pain_level`, `oxygen_saturation`, `priority`, `notes`, `measured_at`, `recorded_by`, `is_voided`, `void_reason`, `voided_by`, `voided_at`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($vitals as $v) {
            $rows[] = "({$v->vital_id}, {$v->patient_id}, ".($v->consultation_id ?? 'NULL').', '.$this->escape($v->blood_pressure).", {$v->heart_rate}, {$v->respiratory_rate}, {$v->temperature}, {$v->weight}, {$v->height}, {$v->bmi}, ".($v->pain_level ?? 'NULL').", {$v->oxygen_saturation}, ".$this->escape($v->priority ?? 'normal').', '.$this->escape($v->notes).', '.$this->escape($v->measured_at).", {$v->recorded_by}, ".($v->is_voided ? 1 : 0).', '.$this->escape($v->void_reason).', '.($v->voided_by ?? 'NULL').', '.$this->escape($v->voided_at).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportFollowUps()
    {
        $legacyDb = DB::connection('legacy');
        $followUps = $legacyDb->table('follow_ups')->orderBy('follow_up_id')->get();

        if ($followUps->isEmpty()) {
            return "-- No follow-ups to export\n\n";
        }

        $sql = "-- Follow-ups\n";
        $sql .= "INSERT INTO `follow_ups` (`follow_up_id`, `patient_id`, `consultation_id`, `follow_up_date`, `follow_up_type`, `reason`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($followUps as $f) {
            $rows[] = "({$f->follow_up_id}, {$f->patient_id}, ".($f->consultation_id ?? 'NULL').', '.$this->escape($this->formatDate($f->follow_up_date)).', '.$this->escape($f->follow_up_type ?? 'general').', '.$this->escape($f->reason).', '.$this->escape($f->status ?? 'scheduled').', '.$this->escape($f->notes).", {$f->created_by}, NOW(), NOW())";
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportRadiology()
    {
        $legacyDb = DB::connection('legacy');
        $radio = $legacyDb->table('radiology_requests')->orderBy('request_id')->get();

        if ($radio->isEmpty()) {
            return "-- No radiology requests to export\n\n";
        }

        $sql = "-- Radiology Requests\n";
        $sql .= "INSERT INTO `radiology_requests` (`request_id`, `request_number`, `patient_id`, `doctor_id`, `scan_type`, `clinical_indication`, `scan_details`, `findings`, `impression`, `priority`, `status`, `requested_by`, `assigned_to`, `verified_by`, `verified_at`, `completed_at`, `consultation_id`, `appointment_id`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($radio as $r) {
            $rows[] = "({$r->request_id}, ".$this->escape($r->request_number).", {$r->patient_id}, {$r->doctor_id}, ".$this->escape($r->scan_type).', '.$this->escape($r->clinical_indication).', '.$this->escape($r->scan_details).', '.$this->escape($r->findings).', '.$this->escape($r->impression).', '.$this->escape($r->priority ?? 'routine').', '.$this->escape($r->status ?? 'pending').", {$r->requested_by}, ".($r->assigned_to ?? 'NULL').', '.($r->verified_by ?? 'NULL').', '.$this->escape($r->verified_at).', '.$this->escape($r->completed_at).', '.($r->consultation_id ?? 'NULL').', '.($r->appointment_id ?? 'NULL').', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportTelehealth()
    {
        $legacyDb = DB::connection('legacy');
        $tele = $legacyDb->table('telehealth_consents')->orderBy('id')->get();

        if ($tele->isEmpty()) {
            return "-- No telehealth consents to export\n\n";
        }

        $sql = "-- Telehealth Consents\n";
        $sql .= "INSERT INTO `telehealth_consents` (`id`, `patient_id`, `appointment_id`, `patient_name`, `patient_email`, `patient_phone`, `doctor_name`, `patient_signature_path`, `verbal_consent_obtained`, `doctor_signature_path`, `signed_at`, `ip_address`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($tele as $t) {
            $rows[] = "({$t->id}, {$t->patient_id}, ".($t->appointment_id ?? 'NULL').', '.$this->escape($t->patient_name).', '.$this->escape($t->patient_email).', '.$this->escape($t->patient_phone).', '.$this->escape($t->doctor_name).', '.$this->escape($t->patient_signature_path).', '.($t->verbal_consent_obtained ? 1 : 0).', '.$this->escape($t->doctor_signature_path).', '.$this->escape($t->signed_at).', '.$this->escape($t->ip_address).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportPharmacy()
    {
        $legacyDb = DB::connection('legacy');
        $orders = $legacyDb->table('pharmacy_purchase_orders')->orderBy('id')->get();

        if ($orders->isEmpty()) {
            return "-- No pharmacy orders to export\n\n";
        }

        $sql = "-- Pharmacy Purchase Orders\n";
        $sql .= "INSERT INTO `pharmacy_purchase_orders` (`id`, `order_number`, `medication_id`, `medication_name`, `quantity`, `supplier_name`, `estimated_cost`, `status`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($orders as $o) {
            $rows[] = "({$o->id}, ".$this->escape($o->order_number).', '.($o->medication_id ?? 'NULL').', '.$this->escape($o->medication_name).", {$o->quantity}, ".$this->escape($o->supplier_name).", {$o->estimated_cost}, ".$this->escape($o->status ?? 'pending').', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportMedicalProcedures()
    {
        $legacyDb = DB::connection('legacy');
        $procedures = $legacyDb->table('medical_procedures')->orderBy('procedure_id')->get();

        if ($procedures->isEmpty()) {
            return "-- No medical procedures to export\n\n";
        }

        $sql = "-- Medical Procedures\n";
        $sql .= "INSERT INTO `medical_procedures` (`procedure_id`, `name`, `description`, `category`, `standard_fee`, `is_active`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($procedures as $p) {
            $rows[] = "({$p->procedure_id}, ".$this->escape($p->name).', '.$this->escape($p->description).', '.$this->escape($p->category).", {$p->standard_fee}, ".($p->is_active ? 1 : 0).', NOW(), NOW())';
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportInsurance()
    {
        $legacyDb = DB::connection('legacy');
        $insurance = $legacyDb->table('insurances')->orderBy('insurance_id')->get();

        if ($insurance->isEmpty()) {
            return "-- No insurance to export\n\n";
        }

        $sql = "-- Insurance\n";
        $sql .= "INSERT INTO `insurances` (`insurance_id`, `name`, `logo_path`, `link`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES\n";

        $rows = [];
        foreach ($insurance as $i) {
            $rows[] = "({$i->insurance_id}, ".$this->escape($i->name).', '.$this->escape($i->logo_path).', '.$this->escape($i->link).', '.($i->is_active ? 1 : 0).", {$i->sort_order}, NOW(), NOW())";
        }

        return $sql.implode(",\n", $rows).";\n\n";
    }

    private function exportOtherTables()
    {
        $legacyDb = DB::connection('legacy');
        $sql = "-- Other Tables\n";

        // Settings
        $settings = $legacyDb->table('settings')->get();
        if (! $settings->isEmpty()) {
            $sql .= "-- Settings\n";
            $sql .= "INSERT INTO `settings` (`key`, `value`, `type`, `group`, `label`, `created_at`, `updated_at`) VALUES\n";
            $rows = [];
            foreach ($settings as $s) {
                $rows[] = '('.$this->escape($s->key).', '.$this->escape($s->value).', '.$this->escape($s->type ?? 'text').', '.$this->escape($s->group ?? 'general').', '.$this->escape($s->label).', NOW(), NOW())';
            }
            $sql .= implode(",\n", $rows).";\n\n";
        }

        // Service Tabs
        $tabs = $legacyDb->table('service_tabs')->get();
        if (! $tabs->isEmpty()) {
            $sql .= "-- Service Tabs\n";
            $sql .= "INSERT INTO `service_tabs` (`title`, `icon`, `content_title`, `content_lead`, `content_body`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES\n";
            $rows = [];
            foreach ($tabs as $t) {
                $rows[] = '('.$this->escape($t->title).', '.$this->escape($t->icon).', '.$this->escape($t->content_title).', '.$this->escape($t->content_lead).', '.$this->escape($t->content_body).', '.$this->escape($t->image_path).", {$t->sort_order}, NOW(), NOW())";
            }
            $sql .= implode(",\n", $rows).";\n\n";
        }

        // Blogs
        $blogs = $legacyDb->table('blogs')->get();
        if (! $blogs->isEmpty()) {
            $sql .= "-- Blogs\n";
            $sql .= "INSERT INTO `blogs` (`title`, `slug`, `excerpt`, `content`, `image_path`, `author_id`, `tags`, `is_published`, `published_at`, `created_at`, `updated_at`) VALUES\n";
            $rows = [];
            foreach ($blogs as $b) {
                $rows[] = '('.$this->escape($b->title).', '.$this->escape($b->slug).', '.$this->escape($b->excerpt).', '.$this->escape($b->content).', '.$this->escape($b->image_path).", {$b->author_id}, ".$this->escape($b->tags).', '.($b->is_published ? 1 : 0).', '.$this->escape($b->published_at).', NOW(), NOW())';
            }
            $sql .= implode(",\n", $rows).";\n\n";
        }

        // Contact Messages
        $contacts = $legacyDb->table('contact_messages')->get();
        if (! $contacts->isEmpty()) {
            $sql .= "-- Contact Messages\n";
            $sql .= "INSERT INTO `contact_messages` (`name`, `email`, `message`, `status`, `read_at`, `reply`, `replied_at`, `replied_by`, `created_at`, `updated_at`) VALUES\n";
            $rows = [];
            foreach ($contacts as $c) {
                $rows[] = '('.$this->escape($c->name).', '.$this->escape($c->email).', '.$this->escape($c->message).', '.$this->escape($c->status ?? 'pending').', '.$this->escape($c->read_at).', '.$this->escape($c->reply).', '.$this->escape($c->replied_at).', '.($c->replied_by ?? 'NULL').', NOW(), NOW())';
            }
            $sql .= implode(",\n", $rows).";\n\n";
        }

        // Messages
        $messages = $legacyDb->table('messages')->get();
        if (! $messages->isEmpty()) {
            $sql .= "-- Messages\n";
            $sql .= "INSERT INTO `messages` (`sender_id`, `receiver_id`, `content`, `metadata`, `read_at`, `sender_archived_at`, `receiver_archived_at`, `created_at`, `updated_at`) VALUES\n";
            $rows = [];
            foreach ($messages as $m) {
                $rows[] = "({$m->sender_id}, {$m->receiver_id}, ".$this->escape($m->content).', '.$this->escape($m->metadata).', '.$this->escape($m->read_at).', '.$this->escape($m->sender_archived_at).', '.$this->escape($m->receiver_archived_at).', NOW(), NOW())';
            }
            $sql .= implode(",\n", $rows).";\n\n";
        }

        // Newsletter Subscribers
        $subs = $legacyDb->table('newsletter_subscribers')->get();
        if (! $subs->isEmpty()) {
            $sql .= "-- Newsletter Subscribers\n";
            $sql .= "INSERT INTO `newsletter_subscribers` (`email`, `name`, `subscribed_at`, `created_at`, `updated_at`) VALUES\n";
            $rows = [];
            foreach ($subs as $s) {
                $rows[] = '('.$this->escape($s->email).', '.$this->escape($s->name).', '.$this->escape($s->subscribed_at).', NOW(), NOW())';
            }
            $sql .= implode(",\n", $rows).";\n\n";
        }

        // Mail Templates
        $templates = $legacyDb->table('mail_templates')->get();
        if (! $templates->isEmpty()) {
            $sql .= "-- Mail Templates\n";
            $sql .= "INSERT INTO `mail_templates` (`mailable`, `subject`, `html_template`, `text_template`, `created_at`, `updated_at`) VALUES\n";
            $rows = [];
            foreach ($templates as $t) {
                $rows[] = '('.$this->escape($t->mailable).', '.$this->escape($t->subject).', '.$this->escape($t->html_template).', '.$this->escape($t->text_template).', NOW(), NOW())';
            }
            $sql .= implode(",\n", $rows).";\n\n";
        }

        // Doctor Block Outs
        $blocks = $legacyDb->table('doctor_block_outs')->get();
        if (! $blocks->isEmpty()) {
            $sql .= "-- Doctor Block Outs\n";
            $sql .= "INSERT INTO `doctor_block_outs` (`id`, `doctor_id`, `block_date`, `start_time`, `end_time`, `reason`, `created_at`, `updated_at`) VALUES\n";
            $rows = [];
            foreach ($blocks as $b) {
                $rows[] = "({$b->id}, {$b->doctor_id}, ".$this->escape($this->formatDate($b->block_date)).', '.$this->escape($b->start_time).', '.$this->escape($b->end_time).', '.$this->escape($b->reason).', NOW(), NOW())';
            }
            $sql .= implode(",\n", $rows).";\n\n";
        }

        return $sql;
    }

    // Helper methods
    private function escape($value)
    {
        if ($value === null || $value === '' || $value === 'null') {
            return 'NULL';
        }
        if (is_numeric($value)) {
            return $value;
        }
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return "'".str_replace("'", "''", (string) $value)."'";
    }

    private function normalizeGender($gender)
    {
        $g = strtolower(trim($gender ?? ''));
        if (in_array($g, ['male', 'm'])) {
            return 'male';
        }
        if (in_array($g, ['female', 'f'])) {
            return 'female';
        }

        return 'other';
    }

    private function formatDate($date)
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
}
