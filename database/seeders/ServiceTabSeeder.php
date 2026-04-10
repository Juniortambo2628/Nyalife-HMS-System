<?php

namespace Database\Seeders;

use App\Models\ServiceTab;
use Illuminate\Database\Seeder;

class ServiceTabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tabs = [
            [
                'title' => 'Obstetrics Care',
                'icon' => 'fa-baby',
                'content_title' => 'Comprehensive Pregnancy Care',
                'content_lead' => 'Nyalife HMS offers complete care for expectant mothers, ensuring a healthy and joyful journey from conception to postpartum.',
                'content_body' => "Our obstetrics services include regular prenatal check-ups, advanced ultrasound screenings, genetic counseling, and childbirth education classes. We focus on personalized care plans for every mother, including specialized support for high-risk pregnancies.\n\n- Personalised Prenatal Care\n- Advanced Ultrasound & Diagnostics\n- High-Risk Pregnancy Management\n- Postnatal Support & Counseling",
                'image_path' => '/assets/img/service-tabs/doctor-1.jpg',
                'sort_order' => 1,
            ],
            [
                'title' => 'Gynecology Services',
                'icon' => 'fa-venus',
                'content_title' => 'Dedicated Women\'s Health Services',
                'content_lead' => 'From routine check-ups to complex procedures, our gynecology services are designed to support women at every stage of life.',
                'content_body' => "We provide a full spectrum of gynecological care, including routine examinations, pap smears, HPV testing, contraception management, and management of conditions like endometriosis, PCOS, and menopausal symptoms. Our approach is holistic, focusing on both physical and emotional well-being.\n\n- Routine GYN Exams & Screenings\n- Contraception & Family Planning\n- Menopause Management\n- Treatment for Gynecological Conditions",
                'image_path' => '/assets/img/service-tabs/hospital-machine.JPG',
                'sort_order' => 2,
            ],
            [
                'title' => 'Lab Services',
                'icon' => 'fa-microscope',
                'content_title' => 'Accurate & Timely Diagnostics',
                'content_lead' => 'Our advanced laboratory provides precise and rapid diagnostic testing, crucial for effective treatment planning and patient management.',
                'content_body' => "Equipped with cutting-edge technology, our lab conducts a wide range of tests including blood work, urinalysis, hormone level tests, and specialized genetic screenings. We ensure quick turnaround times for results, enabling prompt medical decisions.\n\n- Extensive Blood & Urine Testing\n- Hormone Level Assessments\n- Genetic & Prenatal Screenings\n- Fast & Reliable Results",
                'image_path' => '/assets/img/service-tabs/Laboratory-services.JPG',
                'sort_order' => 3,
            ],
            [
                'title' => 'Pharmacy',
                'icon' => 'fa-pills',
                'content_title' => 'Convenient & Expert Pharmacy',
                'content_lead' => 'Our in-house pharmacy provides easy access to medications and expert pharmaceutical advice, supporting your health journey.',
                'content_body' => "We stock a comprehensive range of prescribed and over-the-counter medications relevant to women's health. Our pharmacists offer personalized counseling on medication use, potential side effects, and drug interactions, ensuring optimal therapeutic outcomes.\n\n- Wide Range of Medications\n- Expert Medication Counseling\n- Prescription Fulfillment\n- Convenient On-Site Access",
                'image_path' => '/assets/img/service-tabs/treatment.JPG',
                'sort_order' => 4,
            ],
        ];

        foreach ($tabs as $tab) {
            ServiceTab::updateOrCreate(['title' => $tab['title']], $tab);
        }
    }
}
