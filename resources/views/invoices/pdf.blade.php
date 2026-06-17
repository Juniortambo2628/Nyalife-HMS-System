<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 12px; margin-bottom: 20px; }
        .hospital-name { font-size: 20px; font-weight: bold; }
        .hospital-info { font-size: 11px; color: #666; }
        .invoice-header { width: 100%; margin-bottom: 20px; }
        .invoice-header td { vertical-align: top; width: 50%; }
        .invoice-number { font-size: 16px; font-weight: bold; margin-bottom: 6px; }
        .patient-name { font-size: 14px; font-weight: bold; text-align: right; }
        .patient-details { text-align: right; font-size: 11px; line-height: 1.5; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th, table.items td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        table.items th { background: #f8f9fa; }
        .totals { text-align: right; line-height: 1.8; }
        .total-amount { font-size: 16px; font-weight: bold; color: #20c997; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #777; border-top: 1px solid #eee; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="hospital-name">Nyalife Women's Clinic</div>
        <div class="hospital-info">{{ $clinic['contact_address'] ?? '' }}</div>
        <div class="hospital-info">{{ $clinic['contact_phone'] ?? '' }} | {{ $clinic['contact_email'] ?? '' }}</div>
    </div>

    <table class="invoice-header">
        <tr>
            <td>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div>Date: {{ $invoice->invoice_date }}</div>
                <div>Due: {{ $invoice->due_date ?? '—' }}</div>
                <div>Status: {{ strtoupper($invoice->status) }}</div>
            </td>
            <td>
                <div class="patient-name">{{ $invoice->patient->user->first_name ?? '' }} {{ $invoice->patient->user->last_name ?? '' }}</div>
                <div class="patient-details">
                    PAT-{{ $invoice->patient_id }}<br>
                    {{ $invoice->patient->user->phone ?? '' }}<br>
                    {{ $invoice->patient->user->email ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description ?? $item->item_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Ksh {{ number_format($item->unit_price, 2) }}</td>
                <td>Ksh {{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div>Subtotal: Ksh {{ number_format($subtotal, 2) }}</div>
        @if($taxRate > 0)
        <div>Tax ({{ $taxRate }}%): Ksh {{ number_format($taxAmount, 2) }}</div>
        @endif
        @if($invoice->discount > 0)
        <div>Discount: -Ksh {{ number_format($invoice->discount, 2) }}</div>
        @endif
        <div class="total-amount">Total: Ksh {{ number_format($total, 2) }}</div>
        @if($amountPaid > 0)
        <div>Paid: Ksh {{ number_format($amountPaid, 2) }}</div>
        @endif
        @if($balanceDue > 0)
        <div><strong>Balance Due: Ksh {{ number_format($balanceDue, 2) }}</strong></div>
        @endif
    </div>

    @if($invoice->notes)
    <p style="margin-top: 16px; color: #666;"><strong>Notes:</strong> {{ $invoice->notes }}</p>
    @endif

    <div class="footer">Thank you for choosing Nyalife Women's Clinic</div>
</body>
</html>
