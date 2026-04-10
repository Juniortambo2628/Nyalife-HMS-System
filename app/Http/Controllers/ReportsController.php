<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index', [
            'stats' => (object) [
                'total_patients' => \App\Models\Patient::count(),
                'total_appointments' => \App\Models\Appointment::count(),
                'total_staff' => \App\Models\Staff::count(),
                'total_revenue' => \App\Models\Invoice::where('status', 'paid')->sum('total_amount'),
            ]
        ]);
    }
}
