<?php

namespace Database\Seeders;

use App\Models\LabTestType;
use App\Models\Medication;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LabAndMedicationSeeder extends Seeder
{
    /**
     * Seed medications and lab/service tests from the clinic's Excel price list.
     * File: Documents-from-clinic/Nyalife Medicine and Service Price List and Expiry Dates.xlsx
     *
     * Excel Structure:
     *   Sheet "Medicines Price List" — header at row 4:  B=Name, C=Type, D=Description, E=Price
     *   Sheet "Service Price list"  — header at row 3:  A=Code, B=Name, C=Description, D=Insurance Price, E=Cash Price
     *   Sheet "Office Supplies List" — skipped (not clinical)
     *
     * Uses updateOrCreate so it can be re-run safely without duplicating records.
     */
    public function run(): void
    {
        $filePath = base_path('Documents-from-clinic/Nyalife Medicine and Service Price List and Expiry Dates.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("Excel file not found at: {$filePath}");
            $this->command->info("Falling back to hardcoded lab templates only...");
            $this->seedLabTemplates();
            return;
        }

        $this->command->info('Loading Excel file...');
        $spreadsheet = IOFactory::load($filePath);

        // ── Sheet 1: Medicines ───────────────────────────────────────
        $this->processMedicinesSheet($spreadsheet);

        // ── Sheet 2: Services (consultations, lab tests, procedures) ─
        $this->processServicesSheet($spreadsheet);

        // ── Always seed lab result templates ─────────────────────────
        $this->seedLabTemplates();

        $this->command->info('');
        $this->command->info('✅  Seeding complete!');
        $this->command->info('   Medications:    ' . Medication::count());
        $this->command->info('   Lab Test Types: ' . LabTestType::count());
    }

    // ─── MEDICINES SHEET ────────────────────────────────────────────

    private function processMedicinesSheet($spreadsheet): void
    {
        $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName('Medicines Price List'));
        if ($sheetIndex === null) {
            $this->command->warn('Sheet "Medicines Price List" not found, skipping.');
            return;
        }

        $sheet = $spreadsheet->getSheet($sheetIndex);
        $rows  = $sheet->toArray(null, true, true, true);
        $count = 0;

        // Data starts at row 5 (row 4 is header row)
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex < 5) continue;

            $name = trim($row['B'] ?? '');
            if (empty($name)) continue;

            $type        = trim($row['C'] ?? '');
            $description = trim($row['D'] ?? '');
            $priceRaw    = trim($row['E'] ?? '0');

            // Parse price: "$100 per pack" -> 100, "Ksh 500" -> 500
            $price = $this->parsePrice($priceRaw);

            // Extract strength from the type column if it contains mg/ml etc.
            $strength = '';
            if (preg_match('/(\d+\s*(mg|ml|mcg|g|iu|%|mmol))/i', $type, $m)) {
                $strength = $m[1];
            }

            Medication::updateOrCreate(
                ['medication_name' => $name],
                [
                    'medication_type' => $description ?: 'General',
                    'description'     => $type,
                    'strength'        => $strength,
                    'unit'            => 'pack',
                    'price_per_unit'  => $price,
                    'stock_quantity'  => 0,
                ]
            );
            $count++;
        }

        $this->command->info("  → Imported {$count} medications from 'Medicines Price List'");
    }

    // ─── SERVICES SHEET ─────────────────────────────────────────────

    private function processServicesSheet($spreadsheet): void
    {
        $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName('Service Price list '));
        if ($sheetIndex === null) {
            // Try without trailing space
            $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName('Service Price list'));
        }
        if ($sheetIndex === null) {
            $this->command->warn('Sheet "Service Price list" not found, skipping.');
            return;
        }

        $sheet = $spreadsheet->getSheet($sheetIndex);
        $rows  = $sheet->toArray(null, true, true, true);
        $count = 0;

        // Lab/investigation keywords to classify as LabTestType
        $labKeywords = [
            'test', 'screening', 'culture', 'scan', 'x-ray', 'xray', 'ultrasound',
            'urinalysis', 'blood', 'hiv', 'hepatitis', 'serology', 'cbc', 'fbc',
            'rbs', 'fbs', 'hba1c', 'lipid', 'liver', 'renal', 'thyroid', 'psa',
            'malaria', 'widal', 'stool', 'pregnancy', 'pap', 'vdrl', 'rpr',
            'lab', 'investigation', 'biopsy', 'swab', 'microscopy', 'electrolyte',
            'grouping', 'cross', 'haemoglobin', 'hemoglobin', 'creatinine', 'urea',
            'glucose', 'cholesterol', 'ecg', 'eeg', 'ct scan', 'mri',
        ];

        // Data starts at row 4 (row 3 is header row)
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex < 4) continue;

            $serviceCode   = trim($row['A'] ?? '');
            $serviceName   = trim($row['B'] ?? '');
            $description   = trim($row['C'] ?? '');
            $insuranceRaw  = trim($row['D'] ?? '0');
            $cashRaw       = trim($row['E'] ?? '0');

            if (empty($serviceName)) continue;

            // Skip disclaimer/note rows (not real services)
            if (strlen($serviceName) > 120) continue;
            if (preg_match('/^(NOTE|DISCLAIMER|N\/B|NB:)/i', $serviceName)) continue;

            $insurancePrice = $this->parsePrice($insuranceRaw);
            $cashPrice      = $this->parsePrice($cashRaw);
            $price          = $cashPrice > 0 ? $cashPrice : $insurancePrice;

            // Build a full label for matching
            $fullLabel = strtolower($serviceName . ' ' . $description);

            // Determine if this is a lab/investigation or a general service
            $isLab = false;
            foreach ($labKeywords as $kw) {
                if (str_contains($fullLabel, $kw)) {
                    $isLab = true;
                    break;
                }
            }

            // Determine category from service code prefix or name
            $category = $this->categorizeService($serviceCode, $serviceName, $description);

            if ($isLab) {
                $displayName = $description ? "{$serviceName} - {$description}" : $serviceName;
                $displayName = mb_substr($displayName, 0, 191);
                LabTestType::updateOrCreate(
                    ['test_name' => $displayName],
                    [
                        'category'    => $category,
                        'price'       => $price,
                        'is_active'   => true,
                        'description' => "Code: {$serviceCode}. Insurance: Ksh " . number_format($insurancePrice) . ", Cash: Ksh " . number_format($cashPrice),
                    ]
                );
            } else {
                // Store non-lab services as LabTestType too (for billing catalog)
                $displayName = $description ? "{$serviceName} - {$description}" : $serviceName;
                $displayName = mb_substr($displayName, 0, 191);
                LabTestType::updateOrCreate(
                    ['test_name' => $displayName],
                    [
                        'category'    => $category,
                        'price'       => $price,
                        'is_active'   => true,
                        'description' => "Code: {$serviceCode}. Insurance: Ksh " . number_format($insurancePrice) . ", Cash: Ksh " . number_format($cashPrice),
                    ]
                );
            }
            $count++;
        }

        $this->command->info("  → Imported {$count} services from 'Service Price list'");
    }

    // ─── LAB RESULT TEMPLATES ───────────────────────────────────────

    private function seedLabTemplates(): void
    {
        $templates = [
            'Urinalysis' => [
                ['label' => 'Colour', 'normalRange' => 'Yellow', 'unit' => ''],
                ['label' => 'Appearance', 'normalRange' => 'Clear', 'unit' => ''],
                ['label' => 'pH', 'normalRange' => '4.5-8.0', 'unit' => ''],
                ['label' => 'Specific Gravity', 'normalRange' => '1.005-1.030', 'unit' => ''],
                ['label' => 'Protein', 'normalRange' => 'Negative', 'unit' => ''],
                ['label' => 'Glucose', 'normalRange' => 'Negative', 'unit' => ''],
                ['label' => 'Ketones', 'normalRange' => 'Negative', 'unit' => ''],
                ['label' => 'Blood', 'normalRange' => 'Negative', 'unit' => ''],
                ['label' => 'Bilirubin', 'normalRange' => 'Negative', 'unit' => ''],
                ['label' => 'Urobilinogen', 'normalRange' => 'Normal', 'unit' => ''],
                ['label' => 'Leukocytes', 'normalRange' => 'Negative', 'unit' => ''],
                ['label' => 'Nitrites', 'normalRange' => 'Negative', 'unit' => ''],
                ['label' => 'Pus Cells', 'normalRange' => '0-5', 'unit' => '/hpf'],
                ['label' => 'RBCs', 'normalRange' => '0-2', 'unit' => '/hpf'],
                ['label' => 'Epithelial Cells', 'normalRange' => 'Few', 'unit' => '/hpf'],
                ['label' => 'Casts', 'normalRange' => 'None', 'unit' => ''],
                ['label' => 'Crystals', 'normalRange' => 'None', 'unit' => ''],
                ['label' => 'Bacteria', 'normalRange' => 'None', 'unit' => ''],
            ],
            'Full Blood Count' => [
                ['label' => 'WBC', 'normalRange' => '4.0-11.0', 'unit' => '×10³/µL'],
                ['label' => 'RBC', 'normalRange' => '4.5-5.5 (M) / 4.0-5.0 (F)', 'unit' => '×10⁶/µL'],
                ['label' => 'Haemoglobin', 'normalRange' => '13.5-17.5 (M) / 12.0-16.0 (F)', 'unit' => 'g/dL'],
                ['label' => 'Haematocrit (PCV)', 'normalRange' => '40-54 (M) / 36-48 (F)', 'unit' => '%'],
                ['label' => 'MCV', 'normalRange' => '80-100', 'unit' => 'fL'],
                ['label' => 'MCH', 'normalRange' => '27-33', 'unit' => 'pg'],
                ['label' => 'MCHC', 'normalRange' => '32-36', 'unit' => 'g/dL'],
                ['label' => 'Platelets', 'normalRange' => '150-400', 'unit' => '×10³/µL'],
                ['label' => 'Neutrophils', 'normalRange' => '40-70', 'unit' => '%'],
                ['label' => 'Lymphocytes', 'normalRange' => '20-40', 'unit' => '%'],
                ['label' => 'Monocytes', 'normalRange' => '2-8', 'unit' => '%'],
                ['label' => 'Eosinophils', 'normalRange' => '1-4', 'unit' => '%'],
                ['label' => 'Basophils', 'normalRange' => '0-1', 'unit' => '%'],
                ['label' => 'ESR', 'normalRange' => '0-20 (M) / 0-30 (F)', 'unit' => 'mm/hr'],
            ],
            'Renal Function Tests' => [
                ['label' => 'Urea', 'normalRange' => '2.5-6.4', 'unit' => 'mmol/L'],
                ['label' => 'Creatinine', 'normalRange' => '62-106 (M) / 44-80 (F)', 'unit' => 'µmol/L'],
                ['label' => 'Sodium', 'normalRange' => '135-145', 'unit' => 'mmol/L'],
                ['label' => 'Potassium', 'normalRange' => '3.5-5.0', 'unit' => 'mmol/L'],
                ['label' => 'Chloride', 'normalRange' => '98-106', 'unit' => 'mmol/L'],
                ['label' => 'eGFR', 'normalRange' => '>90', 'unit' => 'mL/min/1.73m²'],
            ],
            'Liver Function Tests' => [
                ['label' => 'Total Bilirubin', 'normalRange' => '3-21', 'unit' => 'µmol/L'],
                ['label' => 'Direct Bilirubin', 'normalRange' => '0-5', 'unit' => 'µmol/L'],
                ['label' => 'ALT (SGPT)', 'normalRange' => '7-56', 'unit' => 'U/L'],
                ['label' => 'AST (SGOT)', 'normalRange' => '10-40', 'unit' => 'U/L'],
                ['label' => 'ALP', 'normalRange' => '44-147', 'unit' => 'U/L'],
                ['label' => 'GGT', 'normalRange' => '9-48', 'unit' => 'U/L'],
                ['label' => 'Total Protein', 'normalRange' => '64-83', 'unit' => 'g/L'],
                ['label' => 'Albumin', 'normalRange' => '35-52', 'unit' => 'g/L'],
            ],
            'Lipid Profile' => [
                ['label' => 'Total Cholesterol', 'normalRange' => '<5.2', 'unit' => 'mmol/L'],
                ['label' => 'HDL Cholesterol', 'normalRange' => '>1.0 (M) / >1.2 (F)', 'unit' => 'mmol/L'],
                ['label' => 'LDL Cholesterol', 'normalRange' => '<3.4', 'unit' => 'mmol/L'],
                ['label' => 'Triglycerides', 'normalRange' => '<1.7', 'unit' => 'mmol/L'],
                ['label' => 'VLDL', 'normalRange' => '0.1-0.8', 'unit' => 'mmol/L'],
            ],
            'Random Blood Sugar' => [
                ['label' => 'Blood Glucose (Random)', 'normalRange' => '3.9-7.8', 'unit' => 'mmol/L'],
            ],
            'Fasting Blood Sugar' => [
                ['label' => 'Blood Glucose (Fasting)', 'normalRange' => '3.9-5.6', 'unit' => 'mmol/L'],
            ],
            'HbA1c' => [
                ['label' => 'HbA1c', 'normalRange' => '<5.7% (Normal) / 5.7-6.4% (Pre-diabetic)', 'unit' => '%'],
            ],
            'Blood Grouping & Rh' => [
                ['label' => 'ABO Group', 'normalRange' => '', 'unit' => ''],
                ['label' => 'Rh Factor', 'normalRange' => '', 'unit' => ''],
            ],
            'HIV Test' => [
                ['label' => 'HIV 1/2 Antibodies', 'normalRange' => 'Non-Reactive', 'unit' => ''],
            ],
            'VDRL/RPR' => [
                ['label' => 'VDRL/RPR', 'normalRange' => 'Non-Reactive', 'unit' => ''],
            ],
            'Hepatitis B Surface Antigen (HBsAg)' => [
                ['label' => 'HBsAg', 'normalRange' => 'Negative', 'unit' => ''],
            ],
            'Pregnancy Test (urine)' => [
                ['label' => 'hCG (Urine)', 'normalRange' => '', 'unit' => ''],
            ],
            'Stool Analysis' => [
                ['label' => 'Colour', 'normalRange' => 'Brown', 'unit' => ''],
                ['label' => 'Consistency', 'normalRange' => 'Formed', 'unit' => ''],
                ['label' => 'Ova', 'normalRange' => 'None Seen', 'unit' => ''],
                ['label' => 'Cysts', 'normalRange' => 'None Seen', 'unit' => ''],
                ['label' => 'Occult Blood', 'normalRange' => 'Negative', 'unit' => ''],
                ['label' => 'Pus Cells', 'normalRange' => 'None', 'unit' => '/hpf'],
                ['label' => 'RBCs', 'normalRange' => 'None', 'unit' => '/hpf'],
                ['label' => 'Bacteria', 'normalRange' => 'Normal Flora', 'unit' => ''],
            ],
            'Widal Test' => [
                ['label' => 'Salmonella typhi O', 'normalRange' => '<1:80', 'unit' => ''],
                ['label' => 'Salmonella typhi H', 'normalRange' => '<1:80', 'unit' => ''],
                ['label' => 'Salmonella paratyphi AH', 'normalRange' => '<1:80', 'unit' => ''],
                ['label' => 'Salmonella paratyphi BH', 'normalRange' => '<1:80', 'unit' => ''],
            ],
            'Malaria Test (BS for MPS)' => [
                ['label' => 'Malaria Parasites', 'normalRange' => 'Not Seen', 'unit' => ''],
                ['label' => 'Species', 'normalRange' => '', 'unit' => ''],
                ['label' => 'Parasite Density', 'normalRange' => '', 'unit' => '/µL'],
            ],
            'Thyroid Function Tests' => [
                ['label' => 'TSH', 'normalRange' => '0.4-4.0', 'unit' => 'mIU/L'],
                ['label' => 'Free T4', 'normalRange' => '12-22', 'unit' => 'pmol/L'],
                ['label' => 'Free T3', 'normalRange' => '3.1-6.8', 'unit' => 'pmol/L'],
            ],
            'Prostatic Specific Antigen (PSA)' => [
                ['label' => 'Total PSA', 'normalRange' => '0-4.0', 'unit' => 'ng/mL'],
            ],
        ];

        $count = 0;
        foreach ($templates as $testName => $template) {
            // Update existing test types with their template
            $updated = LabTestType::where('test_name', 'like', "%{$testName}%")
                ->update(['template' => json_encode($template)]);

            // Also create the test type if it doesn't exist yet
            LabTestType::firstOrCreate(
                ['test_name' => $testName],
                [
                    'category'    => 'Laboratory',
                    'price'       => 0,
                    'is_active'   => true,
                    'template'    => $template,
                    'description' => '',
                ]
            );
            $count++;
        }

        $this->command->info("  → Seeded {$count} lab result templates");
    }

    // ─── Helpers ────────────────────────────────────────────────────

    /**
     * Parse a textual price like "$100 per pack", "Ksh 4,000.00", "3000" into a float.
     */
    private function parsePrice(string $raw): float
    {
        if (empty($raw)) return 0;
        // Remove currency symbols, "Ksh", "$", "per pack", commas
        $clean = preg_replace('/[^0-9.]/', '', $raw);
        return (float) $clean;
    }

    /**
     * Categorize a service row into a human-friendly category based on its code/name.
     */
    private function categorizeService(string $code, string $name, string $desc): string
    {
        $fullText = strtolower("{$name} {$desc}");

        if (str_contains($fullText, 'consult'))  return 'Consultation';
        if (str_contains($fullText, 'scan') || str_contains($fullText, 'ultrasound')) return 'Imaging';
        if (str_contains($fullText, 'x-ray') || str_contains($fullText, 'xray'))      return 'Imaging';
        if (str_contains($fullText, 'procedure') || str_contains($fullText, 'surgical')) return 'Procedure';
        if (str_contains($fullText, 'lab') || str_contains($fullText, 'test'))          return 'Laboratory';
        if (str_contains($fullText, 'screen') || str_contains($fullText, 'hiv'))        return 'Screening';
        if (str_contains($fullText, 'immuniz') || str_contains($fullText, 'vaccine'))   return 'Immunization';
        if (str_contains($fullText, 'antenatal') || str_contains($fullText, 'anc'))     return 'Antenatal';
        if (str_contains($fullText, 'delivery') || str_contains($fullText, 'birth'))    return 'Delivery';
        if (str_contains($fullText, 'family planning') || str_contains($fullText, 'contracepti')) return 'Family Planning';

        return 'General Services';
    }
}
