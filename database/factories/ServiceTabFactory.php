<?php

namespace Database\Factories;

use App\Models\ServiceTab;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceTabFactory extends Factory
{
    protected $model = ServiceTab::class;

    public function definition(): array
    {
        $services = [
            ['Obstetrics Care', 'fa-baby', 'Comprehensive Obstetrics', 'Prenatal to Postnatal', 'Full spectrum pregnancy care from conception through delivery and beyond.', 'services/obstetrics.jpg'],
            ['Gynecology Services', 'fa-venus', 'Expert Gynecology', 'Specialized Women\'s Health', 'Diagnosis and treatment of gynecological conditions.', 'services/gynecology.jpg'],
            ['Laboratory Services', 'fa-microscope', 'Modern Laboratory', 'Accurate Diagnostics', 'State-of-the-art lab equipment for precise test results.', 'services/lab.jpg'],
            ['Pharmacy', 'fa-pills', 'In-House Pharmacy', 'Convenient Medication Access', 'Licensed pharmacy with a wide range of medications.', 'services/pharmacy.jpg'],
            ['Family Planning', 'fa-baby-carriage', 'Family Planning', 'Reproductive Choices', 'Comprehensive family planning and contraceptive counseling.', 'services/family-planning.jpg'],
            ['Antenatal Care', 'fa-heartbeat', 'Antenatal Services', 'Healthy Pregnancy Journey', 'Regular checkups, ultrasounds, and prenatal education.', 'services/antenatal.jpg'],
            ['Delivery Services', 'fa-hospital', 'Safe Delivery', 'Maternity Ward', 'Normal and C-section deliveries with expert care.', 'services/delivery.jpg'],
            ['Postnatal Care', 'fa-user-nurse', 'Postnatal Support', 'Mother & Baby Care', 'Post-delivery care for mother and newborn.', 'services/postnatal.jpg'],
        ];

        $service = $this->faker->randomElement($services);

        return [
            'title' => $service[0],
            'icon' => $service[1],
            'content_title' => $service[2],
            'content_lead' => $service[3],
            'content_body' => $service[4],
            'image_path' => $this->faker->optional()->imageUrl(400, 300),
            'sort_order' => $this->faker->numberBetween(1, 20),
        ];
    }
}