<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\ActivityLogger;

class InvoiceController extends Controller
{
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

        return Inertia::render('Invoices/Index', [
            'invoices' => InvoiceResource::collection($invoices),
            'filters' => $request->only(['search', 'status', 'quick_filter'])
        ]);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['patient.user', 'items', 'consultation'])->findOrFail($id);
        
        $settings = \App\Models\Setting::whereIn('key', [
            'contact_address', 
            'contact_email', 
            'contact_phone',
            'tax_rate'
        ])->pluck('value', 'key');

        return Inertia::render('Invoices/Show', [
            'invoice' => InvoiceResource::make($invoice),
            'clinic_settings' => $settings
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
            'consultation_fee' => 1500, // Default consultation fee, can be made dynamic later
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
}
