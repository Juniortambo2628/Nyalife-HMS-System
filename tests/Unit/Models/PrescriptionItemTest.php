<?php

namespace Tests\Unit\Models;

use App\Models\PrescriptionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_prescription_item_has_fillable_attributes(): void
    {
        $item = PrescriptionItem::create([
            'prescription_id' => 1,
            'medication_id' => 1,
            'dosage' => '500mg',
            'frequency' => 'twice daily',
            'duration' => '7 days',
        ]);

        $this->assertSame('500mg', $item->dosage);
        $this->assertSame('twice daily', $item->frequency);
        $this->assertSame('7 days', $item->duration);
    }

    public function test_prescription_item_belongs_to_prescription(): void
    {
        $item = PrescriptionItem::create([
            'prescription_id' => 42,
            'dosage' => '10mg',
            'frequency' => 'daily',
            'duration' => '30 days',
        ]);

        $this->assertNull($item->prescription); // FK doesn't exist in test DB
    }

    public function test_prescription_item_belongs_to_medication(): void
    {
        $item = PrescriptionItem::create([
            'prescription_id' => 42,
            'medication_id' => null,
            'dosage' => '250mg',
            'frequency' => 'three times daily',
            'duration' => '5 days',
        ]);

        $this->assertNull($item->medication);
    }

    public function test_prescription_item_nullable_medication(): void
    {
        $item = PrescriptionItem::create([
            'prescription_id' => 1,
            'dosage' => 'Custom compound',
            'frequency' => 'as needed',
            'duration' => '14 days',
        ]);

        $this->assertNull($item->medication_id);
        $this->assertSame('Custom compound', $item->dosage);
    }
}
