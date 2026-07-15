<?php

namespace Database\Factories;

use App\Models\MedicalProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalProcedureFactory extends Factory
{
    protected $model = MedicalProcedure::class;

    public function definition(): array
    {
        $procedures = [
            ['General Consultation', 'consultation', 3000],
            ['Obstetrics Ultrasound (1st Trimester)', 'imaging', 3500],
            ['Obstetrics Ultrasound (2nd/3rd Trimester)', 'imaging', 4500],
            ['Normal Delivery Package', 'maternity', 45000],
            ['Caesarean Section (C-Section)', 'surgery', 120000],
            ['Pap Smear', 'lab', 1500],
            ['Fetal Heart Monitoring (CTG)', 'monitoring', 2000],
            ['Postnatal Nursing Care (Per Day)', 'nursing', 5000],
            ['Antenatal Profile', 'lab', 8000],
            ['Glucose Tolerance Test (OGTT)', 'lab', 2500],
            ['Group B Strep Screening', 'lab', 3000],
            ['Cervical Cerclage', 'surgery', 35000],
            ['External Cephalic Version', 'procedure', 15000],
            ['Induction of Labor', 'maternity', 25000],
            ['Manual Vacuum Aspiration (MVA)', 'procedure', 15000],
            ['Dilation & Curettage (D&C)', 'surgery', 30000],
            ['Hysteroscopy', 'surgery', 45000],
            ['Laparoscopy (Diagnostic)', 'surgery', 60000],
            ['Laparoscopy (Operative)', 'surgery', 120000],
            ['Colposcopy', 'procedure', 10000],
            ['LEEP Procedure', 'surgery', 35000],
            ['Endometrial Biopsy', 'procedure', 8000],
            ['IUD Insertion', 'procedure', 3000],
            ['IUD Removal', 'procedure', 2000],
            ['Implant Insertion', 'procedure', 5000],
            ['Implant Removal', 'procedure', 3000],
        ];

        $proc = $this->faker->randomElement($procedures);

        return [
            'name' => $proc[0],
            'description' => $this->faker->optional()->paragraph(),
            'category' => $proc[1],
            'standard_fee' => $proc[2] + $this->faker->randomFloat(2, -500, 500),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}