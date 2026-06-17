<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PrescriptionResource;
use App\Http\Resources\VitalResource;
use App\Models\Invoice;
use App\Models\Prescription;
use App\Models\Vital;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoidAuditController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'prescriptions');
        if (! in_array($type, ['prescriptions', 'vitals', 'invoices'], true)) {
            $type = 'prescriptions';
        }

        $search = $request->get('search');

        $prescriptions = null;
        $vitals = null;
        $invoices = null;

        if ($type === 'prescriptions') {
            $query = Prescription::withoutGlobalScope('not_voided')
                ->where('is_voided', true)
                ->with(['patient.user', 'doctor', 'voidedBy', 'items.medication'])
                ->latest('voided_at');

            if ($search) {
                $query->searchByPatientName($search);
            }

            $prescriptions = PrescriptionResource::collection($query->paginate(15)->withQueryString());
        }

        if ($type === 'vitals') {
            $query = Vital::withoutGlobalScope('not_voided')
                ->where('is_voided', true)
                ->with(['patient.user', 'recordedBy', 'voidedBy'])
                ->latest('voided_at');

            if ($search) {
                $query->whereHas('patient.user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            }

            $vitals = VitalResource::collection($query->paginate(15)->withQueryString());
        }

        if ($type === 'invoices') {
            $query = Invoice::withoutGlobalScope('not_voided')
                ->where('is_voided', true)
                ->with(['patient.user', 'voidedBy'])
                ->latest('voided_at');

            if ($search) {
                $query->searchByPatientOrNumber($search);
            }

            $invoices = InvoiceResource::collection($query->paginate(15)->withQueryString());
        }

        return Inertia::render('Admin/VoidAudit/Index', [
            'type' => $type,
            'prescriptions' => $prescriptions,
            'vitals' => $vitals,
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'type']),
            'counts' => [
                'prescriptions' => Prescription::withoutGlobalScope('not_voided')->where('is_voided', true)->count(),
                'vitals' => Vital::withoutGlobalScope('not_voided')->where('is_voided', true)->count(),
                'invoices' => Invoice::withoutGlobalScope('not_voided')->where('is_voided', true)->count(),
            ],
        ]);
    }
}
