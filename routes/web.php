<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'blogs' => \App\Models\Blog::with('author')->where('is_published', true)->latest()->get(),
        'cms' => \App\Models\Setting::all()->pluck('value', 'key'),
        'serviceTabs' => \App\Models\ServiceTab::orderBy('sort_order')->get(),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/blogs', [\App\Http\Controllers\BlogPublicController::class, 'index'])->name('blogs.public.index');
Route::get('/blogs/{slug}', [\App\Http\Controllers\BlogPublicController::class, 'show'])->name('blogs.public.show');

// Google Authentication
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Temporary Debug Route - Verify Production .env
Route::get('/auth/google/check-config', function() {
    $calculated = route('auth.google.callback');
    if (app()->environment('production') && !str_starts_with($calculated, 'https')) {
        $calculated = str_replace('http://', 'https://', $calculated);
    }

    return [
        'GOOGLE_CLIENT_ID' => substr(env('GOOGLE_CLIENT_ID'), 0, 8) . '...',
        'GOOGLE_CLIENT_SECRET' => substr(env('GOOGLE_CLIENT_SECRET'), 0, 3) . '...',
        'GOOGLE_REDIRECT_URI_ENV' => env('GOOGLE_REDIRECT_URI'),
        'CALCULATED_REDIRECT_URI' => $calculated,
        'APP_URL' => env('APP_URL'),
        'SOCIALITE_GOOGLE_CONFIGURED' => config('services.google') ? 'YES' : 'NO',
        'APP_ENV' => app()->environment(),
    ];
});
Route::get('/auth/google/complete-profile', [\App\Http\Controllers\Auth\GoogleController::class, 'completeProfileView'])->name('auth.google.complete-profile');
Route::post('/auth/google/complete-profile', [\App\Http\Controllers\Auth\GoogleController::class, 'storeProfile'])->name('auth.google.store-profile');

// Legal Policies
Route::get('/privacy-policy', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacy-policy');

Route::get('/cookie-policy', function () {
    return Inertia::render('CookiePolicy');
})->name('cookie-policy');

Route::get('/terms-of-service', function () {
    return Inertia::render('TermsOfService');
})->name('terms-of-service');

Route::post('/contact', [App\Http\Controllers\ContactMessageController::class, 'store'])->name('contact.store');
Route::post('/guest-appointment', [App\Http\Controllers\AppointmentController::class, 'storeGuest'])->name('appointments.guest.store');
Route::post('/check-guest-data', [App\Http\Controllers\CheckGuestDataController::class, 'check'])->name('guest.check');

Route::middleware(['auth', 'verified'])->group(function () {
    // Admin Contact Messages
    Route::get('/admin/messages', [\App\Http\Controllers\ContactMessageController::class, 'index'])->name('admin.messages.index');
    Route::get('/admin/messages/{contactMessage}', [\App\Http\Controllers\ContactMessageController::class, 'show'])->name('admin.messages.show');
    Route::post('/admin/messages/{contactMessage}/read', [\App\Http\Controllers\ContactMessageController::class, 'markAsRead'])->name('admin.messages.read');
    Route::delete('/admin/messages/{contactMessage}', [\App\Http\Controllers\ContactMessageController::class, 'destroy'])->name('admin.messages.destroy');
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{role}', [DashboardController::class, 'index'])->name('dashboard.role');
    
    // Appointments
    Route::get('/appointments', [\App\Http\Controllers\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [\App\Http\Controllers\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [\App\Http\Controllers\AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/calendar', [\App\Http\Controllers\AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::get('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'show'])->name('appointments.show');
    Route::put('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::post('/appointments/{id}/check-in', [\App\Http\Controllers\AppointmentController::class, 'checkIn'])->name('appointments.check-in');
    
    // Patients
    Route::get('/patients', [\App\Http\Controllers\PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [\App\Http\Controllers\PatientController::class, 'create'])->name('patients.create');
    Route::get('/patients/search', [\App\Http\Controllers\PatientController::class, 'searchAjax'])->name('patients.search-ajax');
    Route::get('/medications/search', [\App\Http\Controllers\PharmacyController::class, 'searchAjax'])->name('medications.search');
    Route::get('/doctors/search', [\App\Http\Controllers\AppointmentController::class, 'searchDoctorsAjax'])->name('doctors.search');
    Route::post('/patients', [\App\Http\Controllers\PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{id}', [\App\Http\Controllers\PatientController::class, 'show'])->name('patients.show');
    Route::put('/patients/{id}', [\App\Http\Controllers\PatientController::class, 'update'])->name('patients.update');
    Route::post('/patients/quick-store', [\App\Http\Controllers\PatientController::class, 'quickStore'])->name('patients.quick-store');
    
    // Consultations
    Route::resource('consultations', \App\Http\Controllers\ConsultationController::class);
    
    // Vitals
    Route::get('/vitals', [\App\Http\Controllers\VitalController::class, 'index'])->name('vitals.index');
    Route::get('/vitals/record', [\App\Http\Controllers\VitalController::class, 'create'])->name('vitals.create');
    Route::post('/vitals', [\App\Http\Controllers\VitalController::class, 'store'])->name('vitals.store');
    
    // Prescriptions
    Route::get('/prescriptions', [\App\Http\Controllers\PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [\App\Http\Controllers\PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('/prescriptions/{id}', [\App\Http\Controllers\PrescriptionController::class, 'show'])->name('prescriptions.show');
    Route::post('/prescriptions', [\App\Http\Controllers\PrescriptionController::class, 'store'])->name('prescriptions.store');
    
    // Lab
    Route::get('/lab/requests', [\App\Http\Controllers\LabController::class, 'requests'])->name('lab.index');
    Route::get('/lab/requests/create', [\App\Http\Controllers\LabTestRequestController::class, 'create'])->name('lab.create');
    Route::post('/lab/requests', [\App\Http\Controllers\LabTestRequestController::class, 'store'])->name('lab.store');
    Route::get('/lab/requests/{id}', [\App\Http\Controllers\LabController::class, 'show'])->name('lab.show');
    Route::post('/lab/requests/{id}/status', [\App\Http\Controllers\LabController::class, 'updateStatus'])->name('lab.update-status');
    Route::get('/lab/tests', [\App\Http\Controllers\LabController::class, 'tests'])->name('lab.tests'); // Legacy catalog
    
    // Lab Admin (CRUD)
    Route::get('/lab-tests/manage', [\App\Http\Controllers\LabController::class, 'manage'])->name('lab.manage');
    
    Route::resource('lab-tests', \App\Http\Controllers\LabTestTypeController::class)->names([
        'index' => 'lab-tests.index',
        'create' => 'lab-tests.create',
        'store' => 'lab-tests.store',
        'edit' => 'lab-tests.edit',
        'update' => 'lab-tests.update',
        'destroy' => 'lab-tests.destroy',
    ]);

    Route::get('/lab-results', [\App\Http\Controllers\LabController::class, 'results'])->name('lab.results');
    
    // Pharmacy
    Route::get('/pharmacy/inventory', [\App\Http\Controllers\PharmacyController::class, 'inventory'])->name('pharmacy.inventory');
    Route::post('/pharmacy/inventory/update-stock', [\App\Http\Controllers\PharmacyController::class, 'updateStock'])->name('pharmacy.inventory.update-stock');
    Route::get('/pharmacy/medicines', [\App\Http\Controllers\PharmacyController::class, 'medicines'])->name('pharmacy.medicines');
    Route::post('/pharmacy/medicines', [\App\Http\Controllers\PharmacyController::class, 'storeMedicine'])->name('pharmacy.medicines.store');
    Route::put('/pharmacy/medicines/{id}', [\App\Http\Controllers\PharmacyController::class, 'updateMedicine'])->name('pharmacy.medicines.update');
    Route::delete('/pharmacy/medicines/{id}', [\App\Http\Controllers\PharmacyController::class, 'destroyMedicine'])->name('pharmacy.medicines.destroy');
    
    // Invoices
    Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{id}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::put('/invoices/{id}', [\App\Http\Controllers\InvoiceController::class, 'update'])->name('invoices.update');
    
    // Users (Admin)
    // Users (Admin)
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Messages
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/entities', [\App\Http\Controllers\MessageController::class, 'getEntities'])->name('messages.entities');
    Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::post('/messages/users/{userId}/mark-read', [\App\Http\Controllers\MessageController::class, 'markAllRead'])->name('messages.mark-all-read');
    Route::post('/messages/{id}/read', [\App\Http\Controllers\MessageController::class, 'markRead'])->name('messages.mark-read');

    // Blog Management (Admin)
    Route::get('/admin/blogs', [\App\Http\Controllers\BlogController::class, 'manage'])->name('blog.manage');
    Route::get('/admin/blogs/create', [\App\Http\Controllers\BlogController::class, 'create'])->name('blog.create');
    Route::post('/admin/blogs', [\App\Http\Controllers\BlogController::class, 'store'])->name('blog.store');
    Route::get('/admin/blogs/{id}/edit', [\App\Http\Controllers\BlogController::class, 'edit'])->name('blog.edit');
    Route::post('/admin/blogs/{id}', [\App\Http\Controllers\BlogController::class, 'update'])->name('blog.update');
    Route::delete('/admin/blogs/{id}', [\App\Http\Controllers\BlogController::class, 'destroy'])->name('blog.destroy');

    // CMS / Settings (Admin)
    Route::get('/admin/cms', [\App\Http\Controllers\CMSController::class, 'index'])->name('cms.index');
    Route::post('/admin/cms', [\App\Http\Controllers\CMSController::class, 'update'])->name('cms.update');
    Route::post('/admin/cms/service-tabs', [\App\Http\Controllers\CMSController::class, 'updateServiceTabs'])->name('cms.service-tabs.update');

    // Insurance Management
    Route::get('/admin/insurances', [\App\Http\Controllers\InsuranceController::class, 'index'])->name('insurances.index');
    Route::post('/admin/insurances', [\App\Http\Controllers\InsuranceController::class, 'store'])->name('insurances.store');
    Route::post('/admin/insurances/{id}', [\App\Http\Controllers\InsuranceController::class, 'update'])->name('insurances.update'); // POST for multipart/form-data compatibility
    Route::delete('/admin/insurances/{id}', [\App\Http\Controllers\InsuranceController::class, 'destroy'])->name('insurances.destroy');
    Route::post('/admin/insurances/{id}/toggle', [\App\Http\Controllers\InsuranceController::class, 'toggle'])->name('insurances.toggle');

    // Medical Procedures (Admin)
    Route::get('/admin/medical-procedures', [\App\Http\Controllers\MedicalProcedureController::class, 'index'])->name('medical-procedures.index');
    Route::post('/admin/medical-procedures', [\App\Http\Controllers\MedicalProcedureController::class, 'store'])->name('medical-procedures.store');
    Route::put('/admin/medical-procedures/{id}', [\App\Http\Controllers\MedicalProcedureController::class, 'update'])->name('medical-procedures.update');
    Route::delete('/admin/medical-procedures/{id}', [\App\Http\Controllers\MedicalProcedureController::class, 'destroy'])->name('medical-procedures.destroy');
    Route::post('/admin/medical-procedures/{id}/toggle', [\App\Http\Controllers\MedicalProcedureController::class, 'toggle'])->name('medical-procedures.toggle');
});

// Insurance Public API
Route::get('/api/insurances', [\App\Http\Controllers\InsuranceController::class, 'publicList'])->name('api.insurances.list');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
