<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\NewsletterSubscriber;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Setting;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $this->requirePermission(Permissions::MANAGE_SETTINGS);

        $stats = [
            'users' => User::count(),
            'patients' => Patient::count(),
            'appointments_today' => Appointment::whereDate('appointment_date', today())->count(),
            'consultations' => Consultation::count(),
            'invoices_pending' => Invoice::where('status', 'pending')->count(),
            'prescriptions_pending' => Prescription::where('status', 'pending')->count(),
            'newsletter_subscribers' => NewsletterSubscriber::count(),
        ];

        $settings = Setting::orderBy('group')->orderBy('key')->get(['key', 'value', 'label', 'group']);

        return Inertia::render('Settings/Admin', [
            'stats' => $stats,
            'settings' => $settings,
        ]);
    }
}
