<?php

namespace Database\Factories;

use App\Models\LabTestType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabTestTypeFactory extends Factory
{
    protected $model = LabTestType::class;

    public function definition(): array
    {
        $categories = [
            'Hematology' => ['CBC', 'ESR', 'Reticulocyte Count', 'Blood Film', 'Coagulation Profile'],
            'Biochemistry' => ['Renal Function', 'Liver Function', 'Lipid Profile', 'Glucose', 'HbA1c', 'Electrolytes', 'Calcium', 'Phosphate', 'Uric Acid'],
            'Microbiology' => ['Urine Culture', 'Blood Culture', 'Stool Culture', 'Throat Swab', 'Wound Swab', 'Sputum Culture'],
            'Serology' => ['HIV', 'Hepatitis B', 'Hepatitis C', 'Syphilis', 'Widal Test', 'ASOT', 'RA Factor', 'CRP'],
            'Immunology' => ['Immunoglobulins', 'Complement', 'Autoantibodies', 'Allergy Panel'],
            'Histopathology' => ['Biopsy', 'FNAC', 'Pap Smear', 'Frozen Section'],
            'Blood Transfusion' => ['Blood Grouping', 'Crossmatch', 'Antibody Screen', 'Direct Coombs'],
        ];

        $category = $this->faker->randomElement(array_keys($categories));
        $testNames = $categories[$category];
        $testName = $this->faker->randomElement($testNames);

        return [
            'test_name' => $testName,
            'description' => $this->faker->optional()->sentence(),
            'category' => $category,
            'price' => $this->faker->randomFloat(2, 200, 10000),
            'normal_range' => $this->faker->optional()->sentence(),
            'units' => $this->faker->optional()->randomElement(['g/dL', 'mmol/L', 'mg/dL', 'U/L', 'x10^9/L', '%', 'cells/µL', 'IU/mL']),
            'template' => $this->faker->optional()->randomElement([
                null,
                json_encode(['fields' => [['name' => 'Result', 'type' => 'text'], ['name' => 'Reference Range', 'type' => 'text']]]),
                json_encode(['fields' => [['name' => 'Value', 'type' => 'number'], ['name' => 'Unit', 'type' => 'select'], ['name' => 'Flag', 'type' => 'select']]]),
            ]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}