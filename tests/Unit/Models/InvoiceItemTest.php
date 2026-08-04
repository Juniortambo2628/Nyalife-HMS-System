<?php

namespace Tests\Unit\Models;

use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_item_has_fillable_attributes(): void
    {
        $item = InvoiceItem::create([
            'invoice_id' => 1,
            'item_type' => 'medication',
            'description' => 'Paracetamol 500mg',
            'quantity' => 10,
            'unit_price' => 15.00,
            'total_price' => 150.00,
        ]);

        $this->assertSame('medication', $item->item_type);
        $this->assertSame('Paracetamol 500mg', $item->description);
        $this->assertSame(10, $item->quantity);
        $this->assertSame(150.00, (float) $item->total_price);
    }

    public function test_invoice_item_type_values(): void
    {
        $types = ['service', 'medication', 'lab', 'procedure', 'imaging'];

        foreach ($types as $type) {
            $item = InvoiceItem::create([
                'invoice_id' => 1,
                'item_type' => $type,
                'description' => "Test $type",
                'quantity' => 1,
                'unit_price' => 100,
                'total_price' => 100,
            ]);

            $this->assertSame($type, $item->item_type);
        }
    }

    public function test_invoice_item_decimal_calculations(): void
    {
        $item = InvoiceItem::create([
            'invoice_id' => 1,
            'item_type' => 'service',
            'description' => 'Consultation',
            'quantity' => 3,
            'unit_price' => 1500.50,
            'total_price' => 4501.50,
        ]);

        $this->assertSame(4501.50, (float) $item->total_price);
        $this->assertSame(1500.50, (float) $item->unit_price);
    }
}
