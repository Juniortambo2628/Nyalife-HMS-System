<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CMSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Hero Section
            ['key' => 'hero_title', 'value' => 'Nyalife Hospital Management System', 'type' => 'text', 'group' => 'hero', 'label' => 'Hero Title'],
            ['key' => 'hero_subtitle', 'value' => 'Specialized Obstetrics & Gynecology Care', 'type' => 'text', 'group' => 'hero', 'label' => 'Hero Subtitle'],
            ['key' => 'hero_bg_1', 'value' => '/assets/img/hero/h1_hero.png', 'type' => 'image', 'group' => 'hero', 'label' => 'Hero Background 1'],
            
            // About Section
            ['key' => 'about_title', 'value' => 'Comprehensive Women\'s Healthcare', 'type' => 'text', 'group' => 'about', 'label' => 'About Section Title'],
            ['key' => 'about_description', 'value' => 'Nyalife Women\'s Clinic is a specialized healthcare facility dedicated to providing comprehensive obstetrics and gynecology services to women at every stage of life.', 'type' => 'textarea', 'group' => 'about', 'label' => 'About Description'],
            ['key' => 'about_image', 'value' => '/assets/img/service-tabs/nyalife-1.JPG', 'type' => 'image', 'group' => 'about', 'label' => 'About Image'],
            
            // Services Section
            ['key' => 'services_title', 'value' => 'Our Specialized Services', 'type' => 'text', 'group' => 'services', 'label' => 'Services Title'],
            
            // Contact Info
            ['key' => 'contact_address', 'value' => 'JemPark Complex building suite A5 in Sabaki', 'type' => 'text', 'group' => 'contact', 'label' => 'Physical Address'],
            ['key' => 'contact_phone', 'value' => '+254746516514', 'type' => 'text', 'group' => 'contact', 'label' => 'Phone Number'],
            ['key' => 'contact_email', 'value' => 'info@nyalifewomensclinic.com', 'type' => 'text', 'group' => 'contact', 'label' => 'Contact Email'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
