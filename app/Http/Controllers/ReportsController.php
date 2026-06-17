<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Http\Resources\LabTestRequestResource;
use App\Http\Resources\PrescriptionResource;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\LabTestRequest;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportsController extends Controller
{
    private function parseDateRange(Request $request): array
    {
        $from = $request->input('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->input('to', Carbon::now()->toDateString());

        return [
            'from' => $from,
            'to' => $to,
            'from_dt' => Carbon::parse($from)->startOfDay(),
            'to_dt' => Carbon::parse($to)->endOfDay(),
        ];
    }

    public function index(Request $request)
    {
        $range = $this->parseDateRange($request);
        $from = $range['from'];
        $to = $range['to'];

        $stats = (object) [
            'total_patients' => Patient::count(),
            'new_patients' => Patient::whereBetween('created_at', [$range['from_dt'], $range['to_dt']])->count(),
            'total_appointments' => Appointment::count(),
            'period_appointments' => Appointment::whereBetween('appointment_date', [$from, $to])->count(),
            'total_consultations' => Consultation::count(),
            'period_consultations' => Consultation::whereBetween('consultation_date', [$range['from_dt'], $range['to_dt']])->count(),
            'total_staff' => Staff::count(),
            'total_revenue' => Invoice::where('status', 'paid')->sum('total_amount'),
            'period_revenue' => Invoice::where('status', 'paid')->whereBetween('invoice_date', [$from, $to])->sum('total_amount'),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'pending_amount' => Invoice::where('status', 'pending')->sum('total_amount'),
            'total_prescriptions' => Prescription::count(),
            'period_prescriptions' => Prescription::whereBetween('created_at', [$range['from_dt'], $range['to_dt']])->count(),
            'total_lab_requests' => LabTestRequest::count(),
            'period_lab_requests' => LabTestRequest::whereBetween('created_at', [$range['from_dt'], $range['to_dt']])->count(),
            'period_payments' => Payment::whereBetween('payment_date', [$range['from_dt'], $range['to_dt']])->sum('amount'),
        ];

        $appointmentsByStatus = Appointment::select('status', DB::raw('count(*) as count'))
            ->whereBetween('appointment_date', [$from, $to])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $revenueTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenueTrend[] = [
                'month' => $month->format('M Y'),
                'revenue' => Invoice::where('status', 'paid')
                    ->whereYear('invoice_date', $month->year)
                    ->whereMonth('invoice_date', $month->month)
                    ->sum('total_amount'),
                'count' => Invoice::whereYear('invoice_date', $month->year)
                    ->whereMonth('invoice_date', $month->month)
                    ->count(),
            ];
        }

        $patientTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $patientTrend[] = [
                'month' => $month->format('M Y'),
                'count' => Patient::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }

        $topDiagnoses = Consultation::select('diagnosis', DB::raw('count(*) as count'))
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '!=', '')
            ->groupBy('diagnosis')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        $recentInvoices = Invoice::with('patient.user')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('Reports/Index', [
            'stats' => $stats,
            'appointmentsByStatus' => $appointmentsByStatus,
            'revenueTrend' => $revenueTrend,
            'patientTrend' => $patientTrend,
            'topDiagnoses' => $topDiagnoses,
            'recentInvoices' => $recentInvoices,
            'filters' => ['from' => $from, 'to' => $to],
        ]);
    }

    public function financial(Request $request)
    {
        $range = $this->parseDateRange($request);

        $invoices = Invoice::with('patient.user')
            ->whereBetween('invoice_date', [$range['from'], $range['to']])
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_invoiced' => Invoice::whereBetween('invoice_date', [$range['from'], $range['to']])->sum('total_amount'),
            'paid_amount' => Invoice::where('status', 'paid')->whereBetween('invoice_date', [$range['from'], $range['to']])->sum('total_amount'),
            'pending_amount' => Invoice::where('status', 'pending')->whereBetween('invoice_date', [$range['from'], $range['to']])->sum('total_amount'),
            'invoice_count' => Invoice::whereBetween('invoice_date', [$range['from'], $range['to']])->count(),
            'payments_received' => Payment::whereBetween('payment_date', [$range['from_dt'], $range['to_dt']])->sum('amount'),
            'payment_count' => Payment::whereBetween('payment_date', [$range['from_dt'], $range['to_dt']])->count(),
        ];

        return Inertia::render('Reports/Financial', [
            'invoices' => InvoiceResource::collection($invoices),
            'stats' => $stats,
            'filters' => ['from' => $range['from'], 'to' => $range['to']],
        ]);
    }

    public function appointments(Request $request)
    {
        $range = $this->parseDateRange($request);

        $appointments = Appointment::with(['patient.user', 'doctor.user'])
            ->whereBetween('appointment_date', [$range['from'], $range['to']])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('appointment_date')
            ->paginate(20)
            ->withQueryString();

        $byStatus = Appointment::select('status', DB::raw('count(*) as count'))
            ->whereBetween('appointment_date', [$range['from'], $range['to']])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return Inertia::render('Reports/Appointments', [
            'appointments' => $appointments,
            'stats' => [
                'total' => array_sum($byStatus),
                'by_status' => $byStatus,
            ],
            'filters' => $request->only(['from', 'to', 'status']) + ['from' => $range['from'], 'to' => $range['to']],
        ]);
    }

    public function patients(Request $request)
    {
        $range = $this->parseDateRange($request);

        $patients = Patient::with('user')
            ->whereBetween('created_at', [$range['from_dt'], $range['to_dt']])
            ->when($request->gender, fn ($q) => $q->where('gender', $request->gender))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $byGender = Patient::select('gender', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$range['from_dt'], $range['to_dt']])
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        return Inertia::render('Reports/Patients', [
            'patients' => $patients,
            'stats' => [
                'total' => array_sum($byGender),
                'by_gender' => $byGender,
            ],
            'filters' => $request->only(['from', 'to', 'gender']) + ['from' => $range['from'], 'to' => $range['to']],
        ]);
    }

    public function laboratory(Request $request)
    {
        $range = $this->parseDateRange($request);

        $requests = LabTestRequest::with(['patient.user', 'testType'])
            ->whereBetween('created_at', [$range['from_dt'], $range['to_dt']])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $byStatus = LabTestRequest::select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$range['from_dt'], $range['to_dt']])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return Inertia::render('Reports/Laboratory', [
            'requests' => LabTestRequestResource::collection($requests),
            'stats' => [
                'total' => array_sum($byStatus),
                'by_status' => $byStatus,
            ],
            'filters' => $request->only(['from', 'to', 'status']) + ['from' => $range['from'], 'to' => $range['to']],
        ]);
    }

    public function pharmacy(Request $request)
    {
        $range = $this->parseDateRange($request);

        $prescriptions = Prescription::with(['patient.user', 'items'])
            ->whereBetween('prescription_date', [$range['from'], $range['to']])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('prescription_date')
            ->paginate(20)
            ->withQueryString();

        $byStatus = Prescription::select('status', DB::raw('count(*) as count'))
            ->whereBetween('prescription_date', [$range['from'], $range['to']])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return Inertia::render('Reports/Pharmacy', [
            'prescriptions' => PrescriptionResource::collection($prescriptions),
            'stats' => [
                'total' => array_sum($byStatus),
                'by_status' => $byStatus,
            ],
            'filters' => $request->only(['from', 'to', 'status']) + ['from' => $range['from'], 'to' => $range['to']],
        ]);
    }

    public function exportCsv(Request $request)
    {
        $type = $request->input('type', 'financial');
        $range = $this->parseDateRange($request);
        $from = $range['from'];
        $to = $range['to'];

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$type}_report_{$from}_to_{$to}.csv\"",
        ];

        $callback = function () use ($type, $from, $to, $range) {
            $handle = fopen('php://output', 'w');

            if ($type === 'financial') {
                fputcsv($handle, ['Invoice #', 'Patient', 'Date', 'Amount', 'Status', 'Payment Method']);
                Invoice::with('patient.user')
                    ->whereBetween('invoice_date', [$from, $to])
                    ->orderBy('invoice_date', 'desc')
                    ->chunk(200, function ($invoices) use ($handle) {
                        foreach ($invoices as $inv) {
                            fputcsv($handle, [
                                $inv->invoice_number,
                                ($inv->patient->user->first_name ?? '') . ' ' . ($inv->patient->user->last_name ?? ''),
                                $inv->invoice_date,
                                $inv->total_amount,
                                $inv->status,
                                $inv->payment_method ?? 'N/A',
                            ]);
                        }
                    });
            } elseif ($type === 'patients') {
                fputcsv($handle, ['Patient ID', 'Name', 'Gender', 'DOB', 'Phone', 'Email', 'Blood Group', 'Registered']);
                Patient::with('user')
                    ->whereBetween('created_at', [$range['from_dt'], $range['to_dt']])
                    ->chunk(200, function ($patients) use ($handle) {
                        foreach ($patients as $p) {
                            fputcsv($handle, [
                                'PAT-' . $p->patient_id,
                                ($p->user->first_name ?? '') . ' ' . ($p->user->last_name ?? ''),
                                $p->gender ?? 'N/A',
                                $p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : 'N/A',
                                $p->user->phone ?? 'N/A',
                                $p->user->email ?? 'N/A',
                                $p->blood_group ?? 'N/A',
                                $p->created_at->format('Y-m-d'),
                            ]);
                        }
                    });
            } elseif ($type === 'appointments') {
                fputcsv($handle, ['Appointment ID', 'Patient', 'Doctor', 'Date', 'Time', 'Type', 'Status']);
                Appointment::with(['patient.user', 'doctor.user'])
                    ->whereBetween('appointment_date', [$from, $to])
                    ->orderBy('appointment_date', 'desc')
                    ->chunk(200, function ($appointments) use ($handle) {
                        foreach ($appointments as $a) {
                            fputcsv($handle, [
                                $a->appointment_id,
                                ($a->patient->user->first_name ?? '') . ' ' . ($a->patient->user->last_name ?? ''),
                                ($a->doctor->user->first_name ?? '') . ' ' . ($a->doctor->user->last_name ?? ''),
                                $a->appointment_date,
                                $a->appointment_time,
                                $a->appointment_type ?? 'N/A',
                                $a->status,
                            ]);
                        }
                    });
            } elseif ($type === 'consultations') {
                fputcsv($handle, ['Consultation ID', 'Patient', 'Doctor', 'Date', 'Diagnosis', 'Status']);
                Consultation::with(['patient.user', 'doctor.user'])
                    ->whereBetween('consultation_date', [$range['from_dt'], $range['to_dt']])
                    ->orderBy('consultation_date', 'desc')
                    ->chunk(200, function ($consults) use ($handle) {
                        foreach ($consults as $c) {
                            fputcsv($handle, [
                                $c->consultation_id,
                                ($c->patient->user->first_name ?? '') . ' ' . ($c->patient->user->last_name ?? ''),
                                ($c->doctor->user->first_name ?? '') . ' ' . ($c->doctor->user->last_name ?? ''),
                                $c->consultation_date?->format('Y-m-d'),
                                $c->diagnosis ?? 'Pending',
                                $c->consultation_status ?? 'N/A',
                            ]);
                        }
                    });
            } elseif ($type === 'laboratory') {
                fputcsv($handle, ['Request ID', 'Patient', 'Test', 'Request Date', 'Status', 'Priority']);
                LabTestRequest::with(['patient.user', 'testType'])
                    ->whereBetween('created_at', [$range['from_dt'], $range['to_dt']])
                    ->orderBy('created_at', 'desc')
                    ->chunk(200, function ($rows) use ($handle) {
                        foreach ($rows as $r) {
                            fputcsv($handle, [
                                $r->request_id,
                                ($r->patient->user->first_name ?? '') . ' ' . ($r->patient->user->last_name ?? ''),
                                $r->testType->test_name ?? 'N/A',
                                $r->request_date ?? $r->created_at?->format('Y-m-d'),
                                $r->status,
                                $r->priority ?? 'routine',
                            ]);
                        }
                    });
            } elseif ($type === 'pharmacy') {
                fputcsv($handle, ['Prescription ID', 'Patient', 'Date', 'Status', 'Medications']);
                Prescription::with(['patient.user', 'items'])
                    ->whereBetween('prescription_date', [$from, $to])
                    ->orderBy('prescription_date', 'desc')
                    ->chunk(200, function ($rows) use ($handle) {
                        foreach ($rows as $p) {
                            fputcsv($handle, [
                                $p->prescription_id,
                                ($p->patient->user->first_name ?? '') . ' ' . ($p->patient->user->last_name ?? ''),
                                $p->prescription_date,
                                $p->status,
                                $p->items->pluck('medicine_name')->join('; '),
                            ]);
                        }
                    });
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
