<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('service_tabs')->updateOrInsert(
            ['title' => 'Ultrasound Services'],
            [
                'icon' => 'fa-wave-square',
                'content_title' => 'Ultrasound & Imaging Services',
                'content_lead' => 'Clear, timely ultrasound scans to support pregnancy care, gynecology reviews, and treatment planning.',
                'content_body' => "Our ultrasound services support antenatal, pelvic, obstetric, and gynecological assessments in a calm clinic setting.\n\n- Obstetric and pregnancy scans\n- Pelvic and gynecological ultrasound\n- Growth and wellbeing checks\n- Doctor-guided interpretation and follow-up",
                'image_path' => '/assets/img/service-tabs/hospital-machine.JPG',
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('service_tabs')->updateOrInsert(
            ['title' => 'Pediatric Clinic'],
            [
                'icon' => 'fa-child',
                'content_title' => 'Pediatric Clinic',
                'content_lead' => 'Child health reviews, growth monitoring, and family-friendly pediatric care every Monday.',
                'content_body' => "Our pediatric clinic runs on Mondays and provides practical child health support for families.\n\n- Monday pediatric clinic\n- Growth and development monitoring\n- Childhood illness review\n- Immunization and wellness guidance",
                'image_path' => '/assets/img/service-tabs/doctor-1.jpg',
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('service_tabs')
            ->whereIn('title', ['Ultrasound Services', 'Pediatric Clinic'])
            ->delete();
    }
};
