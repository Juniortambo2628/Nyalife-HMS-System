<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CMSSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Hero Section
            [
                'key' => 'hero_title',
                'value' => 'Nyalife Hospital Management System',
                'type' => 'text',
                'group' => 'hero',
                'label' => 'Hero Main Title'
            ],
            [
                'key' => 'hero_subtitle',
                'value' => 'Specialized Obstetrics & Gynecology Care',
                'type' => 'text',
                'group' => 'hero',
                'label' => 'Hero Subtitle'
            ],
            [
                'key' => 'hero_slide_1',
                'value' => '/assets/img/slider/slider-1.jpg',
                'type' => 'image',
                'group' => 'hero',
                'label' => 'Hero Slide 1 (Background)'
            ],
            [
                'key' => 'hero_slide_2',
                'value' => '/assets/img/slider/slider-2.jpg',
                'type' => 'image',
                'group' => 'hero',
                'label' => 'Hero Slide 2 (Background)'
            ],
            [
                'key' => 'hero_slide_3',
                'value' => '/assets/img/slider/slider-3.jpg',
                'type' => 'image',
                'group' => 'hero',
                'label' => 'Hero Slide 3 (Background)'
            ],
            [
                'key' => 'hero_slide_4',
                'value' => '/assets/img/slider/slider-4.jpg',
                'type' => 'image',
                'group' => 'hero',
                'label' => 'Hero Slide 4 (Background)'
            ],


            // About Section
            [
                'key' => 'about_title',
                'value' => 'About Nyalife Women\'s Clinic',
                'type' => 'text',
                'group' => 'about',
                'label' => 'About Section Title'
            ],
            [
                'key' => 'about_description',
                'value' => 'Nyalife Women\'s Clinic is a specialized healthcare facility dedicated to providing comprehensive obstetrics and gynecology services to women at every stage of life.',
                'type' => 'textarea',
                'group' => 'about',
                'label' => 'Detailed About Description'
            ],
            [
                'key' => 'about_image',
                'value' => '/assets/img/service-tabs/nyalife-1.JPG',
                'type' => 'image',
                'group' => 'about',
                'label' => 'About Featured Image'
            ],

            // Contact Info
            [
                'key' => 'contact_phone',
                'value' => '+254746516514',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Contact Phone Number'
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@nyalifewomensclinic.com',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Contact Email Address'
            ],
            [
                'key' => 'contact_address',
                'value' => 'JemPark Complex building suite A5 in Sabaki, About 500meters from Mlolongo in Athi River, Machakos',
                'type' => 'textarea',
                'group' => 'contact',
                'label' => 'Clinic Physical Address'
            ],
            [
                'key' => 'contact_bg_image',
                'value' => '/assets/img/slider/footer-bg1.jpg',
                'type' => 'image',
                'group' => 'contact',
                'label' => 'Contact Section Background'
            ],
            [
                'key' => 'contact_overlay_opacity',
                'value' => '0.85',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Contact Overlay Opacity (0.0 to 1.0)'
            ],
            [
                'key' => 'landing_page_order',
                'value' => 'hero,appointment,about,services,blog,contact',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Landing Page Section Order'
            ],
            [
                'key' => 'tax_rate',
                'value' => '16',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Standard Tax Rate (%)'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
