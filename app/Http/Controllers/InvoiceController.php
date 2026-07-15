<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\VoidRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\ActivityLogger;
use App\Support\Permissions;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\HasBulkActions;

class InvoiceController extends Controller
{
    use HasBulkActions;
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Invoice::with(['patient.user']);

        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->patient_id);
            }
        }

        if ($request->has('quick_filter') && $request->quick_filter) {
            switch ($request->quick_filter) {
                case 'unpaid':
                    $query->where('status', 'unpaid');
                    break;
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'overdue':
                    $query->where('status', 'unpaid')->whereDate('due_date', '<', today());
                    break;
            }
        }

        $invoices = $query
            ->searchByPatientOrNumber($request->search)
            ->status($request->status)
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Invoice::count(),
            'unpaid' => Invoice::where('status', 'unpaid')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'total_amount' => Invoice::sum('total_amount'),
        ];

        return Inertia::render('Invoices/Index', [
            'invoices' => InvoiceResource::collection($invoices),
            'filters' => $request->only(['search', 'status', 'quick_filter']),
            'stats' => $stats,
        ]);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['patient.user', 'items', 'consultation', 'payments.receivedBy'])->findOrFail($id);

        $this->requireStaffOrOwnPatient(
            $invoice->patient_id,
            Permissions::MANAGE_INVOICES,
            Permissions::MANAGE_PAYMENTS
        );
        
        $settings = \App\Models\Setting::clinicInvoiceSettings();

        return Inertia::render('Invoices/Show', [
            'invoice' => InvoiceResource::make($invoice),
            'clinic_settings' => $settings,
            'paymentMethods' => \App\Models\Payment::METHODS,
        ]);
    }
    public function create(Request $request)
    {
        $consultation_id = $request->query('consultation_id');
        $patient_id = $request->query('patient_id');

        $consultation = null;
        if ($consultation_id) {
            $consultation = Consultation::with('patient.user')->find($consultation_id);
            $patient_id = $consultation->patient_id;
        }

        return Inertia::render('Invoices/Create', [
            'patient_id' => $patient_id,
            'consultation_id' => $consultation_id,
            'consultation' => $consultation,
            'consultation_fee' => \App\Models\Setting::where('key', 'consultation_fee')->value('value') ?: 1500,
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $validated = $request->validated();

        // Calculate totals
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Create Invoice
            $invoice = Invoice::create([
                'patient_id' => $validated['patient_id'],
                'consultation_id' => $validated['consultation_id'] ?? null,
                'invoice_number' => 'INV-' . strtoupper(uniqid()), // Simple generator, can be improved
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'created_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create Invoice Items
            foreach ($request->items as $item) {
                \App\Models\InvoiceItem::create([
                    'invoice_id' => $invoice->invoice_id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            ActivityLogger::log(
                'billing',
                "New invoice #{$invoice->invoice_number} created for " . ($invoice->patient->user->full_name ?? 'Patient'),
                ['invoice_id' => $invoice->invoice_id, 'amount' => $totalAmount],
                Auth::user(),
                $invoice,
                [$invoice->patient->user_id, 1]
            );

            return redirect()->route('invoices.show', $invoice->invoice_id)
                ->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateInvoiceRequest $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $validated = $request->validated();

        if (! empty($validated)) {
            $invoice->update([
                'status' => $validated['status'] ?? $invoice->status,
                'payment_method' => $validated['payment_method'] ?? $invoice->payment_method,
            ]);

            ActivityLogger::log(
                'billing',
                "Invoice #{$invoice->invoice_number} marked as {$invoice->status}",
                ['invoice_id' => $invoice->invoice_id, 'status' => $invoice->status],
                Auth::user(),
                $invoice,
                [$invoice->patient->user_id, 1]
            );

            return back()->with('success', 'Invoice status updated.');
        }

        return back()->with('error', 'No valid updates provided.');
    }

    public function destroy(VoidRequest $request, $id)
    {
        $validated = $request->validated();

        $invoice = Invoice::with('patient.user')->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoices cannot be voided.');
        }

        $invoice->update([
            'is_voided' => true,
            'void_reason' => $validated['void_reason'],
            'voided_by' => Auth::id(),
            'voided_at' => now(),
        ]);

        ActivityLogger::log(
            'billing',
            "Invoice #{$invoice->invoice_number} voided: {$validated['void_reason']}",
            ['invoice_id' => $invoice->invoice_id, 'amount' => $invoice->total_amount],
            Auth::user(),
            $invoice,
            [$invoice->patient->user_id, 1]
        );

        return redirect()->route('invoices.index')->with('success', 'Invoice has been voided.');
    }

    /**
     * Handle bulk actions on invoices.
     */
    protected function bulkActionMap(): array
    {
        return [
            'void' => function (array $ids, int $count) {
                $updated = $this->bulkProcessWithLog(
                    Invoice::class, 'invoice_id', $ids,
                    fn ($item) => $item->status !== 'paid' && ! $item->is_voided,
                    fn ($item) => ['is_voided' => true, 'void_reason' => 'Bulk voided via toolbar', 'voided_by' => Auth::id(), 'voided_at' => now()],
                    'billing', 'Invoice',
                    fn ($item) => [$item->patient->user_id, 1]
                );
                return redirect()->back()->with('success', "{$updated} invoice(s) voided.");
            },
            'delete' => function (array $ids, int $count) {
                $deleted = $this->bulkDelete(Invoice::class, 'invoice_id', $ids, 'status', 'paid');
                return redirect()->back()->with('success', "{$deleted} unpaid invoice(s) deleted.");
            },
        ];
    }

    public function exportCsv(Request $request)
    {
        abort_unless(in_array(Auth::user()?->role, ['admin', 'receptionist', 'doctor'], true), 403);

        $user = Auth::user();
        $query = Invoice::with(['patient.user']);

        if ($user && $user->role === 'patient') {
            $patient = Patient::where('user_id', $user->user_id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->patient_id);
            }
        }

        $invoices = $query
            ->searchByPatientOrNumber($request->search)
            ->status($request->status)
            ->latest()
            ->get();

        $filename = 'invoices-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($invoices) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice #', 'Patient', 'Date', 'Due Date', 'Amount', 'Status', 'Payment Method']);

            foreach ($invoices as $invoice) {
                fputcsv($handle, [
                    $invoice->invoice_number,
                    trim(($invoice->patient?->user?->first_name ?? '') . ' ' . ($invoice->patient?->user?->last_name ?? '')),
                    $invoice->invoice_date,
                    $invoice->due_date,
                    $invoice->total_amount,
                    $invoice->status,
                    $invoice->payment_method,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function print($id)
    {
        $invoice = Invoice::with(['patient.user', 'items', 'payments'])->findOrFail($id);

        $this->requireStaffOrOwnPatient(
            $invoice->patient_id,
            Permissions::MANAGE_INVOICES,
            Permissions::MANAGE_PAYMENTS
        );

        $settings = \App\Models\Setting::clinicInvoiceSettings();

        return Inertia::render('Invoices/Print', [
            'invoice' => InvoiceResource::make($invoice),
            'clinic_settings' => $settings,
        ]);
    }

    public function downloadPdf($id)
    {
        $invoice = Invoice::with(['patient.user', 'items', 'payments'])->findOrFail($id);

        $this->requireStaffOrOwnPatient(
            $invoice->patient_id,
            Permissions::MANAGE_INVOICES,
            Permissions::MANAGE_PAYMENTS
        );

        $settings = \App\Models\Setting::clinicInvoiceSettings();

        $subtotal = $invoice->items->sum('total_price');
        $taxRate = (float) ($settings['tax_rate'] ?? 0);
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount - (float) ($invoice->discount ?? 0);
        $amountPaid = $invoice->payments->where('payment_status', 'completed')->sum('amount');
        $balanceDue = max(0, $total - $amountPaid);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'clinic' => $settings,
            'subtotal' => $subtotal,
            'taxRate' => $taxRate,
            'taxAmount' => $taxAmount,
            'total' => $total,
            'amountPaid' => $amountPaid,
            'balanceDue' => $balanceDue,
        ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}

