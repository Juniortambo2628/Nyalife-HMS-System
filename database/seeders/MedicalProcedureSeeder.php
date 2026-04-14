<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicalProcedureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $procedures = [
            ['name' => 'General Consultation', 'description' => 'Initial assessment by general practitioner focus on obstetric/gynecological health.', 'category' => 'consultation', 'standard_fee' => 3000.00, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Obstetrics Ultrasound (1st Trimester)', 'description' => 'Early viability and dating scan.', 'category' => 'imaging', 'standard_fee' => 3500.00, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Normal Delivery Package', 'description' => 'Uncomplicated vaginal delivery including hospital stay.', 'category' => 'maternity', 'standard_fee' => 45000.00, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Caesarean Section (C-Section)', 'description' => 'Surgical delivery procedure.', 'category' => 'surgery', 'standard_fee' => 120000.00, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pap Smear', 'description' => 'Cervical cancer screening test.', 'category' => 'lab', 'standard_fee' => 1500.00, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Fetal Heart Monitoring (CTG)', 'description' => 'Electronic monitoring of fetal heart rate.', 'category' => 'monitoring', 'standard_fee' => 2000.00, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Postnatal Nursing Care (Per Day)', 'description' => 'Standard daily inpatient nursing charges post-delivery.', 'category' => 'nursing', 'standard_fee' => 5000.00, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('medical_procedures')->insert($procedures);
    }
}
