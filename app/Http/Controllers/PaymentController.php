<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\PaymentService;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    private function authorizeStaff(): void
    {
        $this->requirePermission(Permissions::MANAGE_PAYMENTS);
    }

    public function index(Request $request)
    {
        $this->authorizeStaff();

        $payments = Payment::with(['invoice.patient.user', 'receivedBy'])
            ->filteredQuery($request)
            ->latest('payment_date')
            ->paginate(15);

        $stats = [
            'total_collected' => (float) Payment::completed()
                ->when($request->date_from, fn ($q) => $q->whereDate('payment_date', '>=', $request->date_from))
                ->when($request->date_to, fn ($q) => $q->whereDate('payment_date', '<=', $request->date_to))
                ->sum('amount'),
            'payment_count' => Payment::completed()->count(),
            'pending_count' => Payment::where('payment_status', 'pending')->count(),
        ];

        return Inertia::render('Payments/Index', [
            'payments' => PaymentResource::collection($payments),
            'filters' => $request->only(['search', 'status', 'method', 'invoice_id', 'date_from', 'date_to']),
            'stats' => $stats,
            'paymentMethods' => Payment::METHODS,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeStaff();

        $invoice = null;
        if ($request->invoice_id) {
            $invoice = Invoice::with(['patient.user', 'items', 'payments'])
                ->findOrFail($request->invoice_id);
        }

        $pendingInvoices = Invoice::with(['patient.user', 'payments'])
            ->whereIn('status', ['pending', 'partially_paid', 'overdue'])
            ->latest()
            ->limit(50)
            ->get();

        return Inertia::render('Payments/Create', [
            'invoice' => $invoice ? InvoiceResource::make($invoice) : null,
            'pendingInvoices' => InvoiceResource::collection($pendingInvoices),
            'paymentMethods' => Payment::METHODS,
            'preselected_invoice_id' => $request->invoice_id,
        ]);
    }

    public function store(StorePaymentRequest $request)
    {
        $validated = $request->validated();
        $status = $validated['payment_status'] ?? 'completed';

        $payment = DB::transaction(function () use ($validated, $status) {
            $payment = Payment::create([
                'invoice_id' => $validated['invoice_id'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'payment_status' => $status,
                'status' => in_array($status, ['pending', 'completed', 'failed'], true) ? $status : 'completed',
                'notes' => $validated['notes'] ?? null,
                'received_by' => Auth::id(),
            ]);

            if ($status === 'completed') {
                PaymentService::syncInvoiceStatus($payment->invoice);
            }

            return $payment;
        });

        $payment->load(['invoice.patient.user', 'receivedBy']);

        ActivityLogger::log(
            'billing',
            'Payment of Ksh '.number_format((float) $payment->amount, 2).' recorded for invoice #'.($payment->invoice->invoice_number ?? $payment->invoice_id),
            ['payment_id' => $payment->payment_id, 'invoice_id' => $payment->invoice_id],
            Auth::user(),
            $payment,
            [$payment->invoice->patient->user_id ?? null, 1]
        );

        return redirect()->route('payments.show', $payment->payment_id)
            ->with('success', 'Payment recorded successfully.');
    }

    public function show($id)
    {
        $this->authorizeStaff();

        $payment = Payment::with(['invoice.patient.user', 'invoice.items', 'receivedBy'])
            ->findOrFail($id);

        $settings = Setting::whereIn('key', [
            'contact_address',
            'contact_email',
            'contact_phone',
        ])->pluck('value', 'key');

        return Inertia::render('Payments/Show', [
            'payment' => PaymentResource::make($payment),
            'clinic_settings' => $settings,
        ]);
    }

    public function complete($id)
    {
        $this->authorizeStaff();

        $payment = Payment::with('invoice')->findOrFail($id);

        if ($payment->payment_status === 'completed') {
            return back()->with('error', 'Payment is already completed.');
        }

        $remaining = PaymentService::remainingBalance($payment->invoice);
        if ((float) $payment->amount > $remaining) {
            return back()->with('error', 'Payment amount exceeds remaining invoice balance.');
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'payment_status' => 'completed',
                'status' => 'completed',
            ]);
            PaymentService::syncInvoiceStatus($payment->invoice);
        });

        ActivityLogger::log(
            'billing',
            'Payment #'.$payment->payment_id.' marked as completed',
            ['payment_id' => $payment->payment_id],
            Auth::user(),
            $payment
        );

        return back()->with('success', 'Payment marked as completed.');
    }

    public function print($id)
    {
        $this->authorizeStaff();

        $payment = Payment::with(['invoice.patient.user', 'receivedBy'])
            ->findOrFail($id);

        $settings = Setting::whereIn('key', [
            'contact_address',
            'contact_email',
            'contact_phone',
        ])->pluck('value', 'key');

        return Inertia::render('Payments/Print', [
            'payment' => PaymentResource::make($payment),
            'clinic_settings' => $settings,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorizeStaff();

        $payments = Payment::with(['invoice.patient.user', 'receivedBy'])
            ->filteredQuery($request)
            ->latest('payment_date')
            ->get();

        $filename = 'payments-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Payment ID', 'Invoice #', 'Patient', 'Amount', 'Method', 'Status', 'Date', 'Reference', 'Received By']);

            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->payment_id,
                    $payment->invoice?->invoice_number,
                    trim(($payment->invoice?->patient?->user?->first_name ?? '').' '.($payment->invoice?->patient?->user?->last_name ?? '')),
                    $payment->amount,
                    Payment::METHODS[$payment->payment_method] ?? $payment->payment_method,
                    $payment->payment_status,
                    $payment->payment_date?->format('Y-m-d H:i'),
                    $payment->transaction_reference,
                    trim(($payment->receivedBy?->first_name ?? '').' '.($payment->receivedBy?->last_name ?? '')),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
