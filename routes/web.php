<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Support\Permissions;
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
Route::get('/guest-appointments/confirmation', [App\Http\Controllers\AppointmentController::class, 'guestConfirmation'])->name('guest-appointments.confirmation');
Route::post('/newsletter/subscribe', [App\Http\Controllers\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/check-guest-data', [App\Http\Controllers\CheckGuestDataController::class, 'check'])->name('guest.check');

// Telehealth
Route::get('/telehealth', [App\Http\Controllers\TelehealthController::class, 'index'])->name('telehealth.index');
Route::post('/telehealth/consent', [App\Http\Controllers\TelehealthController::class, 'store'])->name('telehealth.store');
Route::get('/telehealth/meeting/{meetingId}', [App\Http\Controllers\TelehealthController::class, 'meetingRoom'])->name('telehealth.meeting');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{role}', [DashboardController::class, 'index'])->name('dashboard.role');
    
    // Emergency Triage
    Route::middleware('permission:' . Permissions::MANAGE_CONSULTATIONS)->group(function () {
        Route::get('/emergency-triage', [\App\Http\Controllers\EmergencyTriageController::class, 'create'])->name('emergency-triage.create');
    });
    
    // Appointments (staff or patient portal)
    Route::middleware('role_or_permission:' . Permissions::staffOrPatient(Permissions::MANAGE_APPOINTMENTS))->group(function () {
        Route::get('/appointments', [\App\Http\Controllers\AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/calendar', [\App\Http\Controllers\AppointmentController::class, 'calendar'])->name('appointments.calendar');
        Route::get('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'show'])->whereNumber('id')->name('appointments.show');
    });
    Route::middleware('permission:' . Permissions::MANAGE_APPOINTMENTS)->group(function () {
        Route::get('/appointments/create', [\App\Http\Controllers\AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [\App\Http\Controllers\AppointmentController::class, 'store'])->name('appointments.store');
        Route::post('/appointments/bulk-action', [\App\Http\Controllers\AppointmentController::class, 'bulkAction'])->name('appointments.bulk-action');
        Route::match(['put', 'patch'], '/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'update'])->name('appointments.update');
        Route::delete('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'destroy'])->name('appointments.destroy');
        Route::post('/appointments/{id}/check-in', [\App\Http\Controllers\AppointmentController::class, 'checkIn'])->name('appointments.check-in');
        Route::get('/doctors/search', [\App\Http\Controllers\AppointmentController::class, 'searchDoctorsAjax'])->name('doctors.search');
    });
    
    // Patients (staff only)
    Route::middleware('permission:' . Permissions::MANAGE_PATIENTS)->group(function () {
        Route::get('/patients', [\App\Http\Controllers\PatientController::class, 'index'])->name('patients.index');
        Route::get('/patients/create', [\App\Http\Controllers\PatientController::class, 'create'])->name('patients.create');
        Route::get('/patients/search', [\App\Http\Controllers\PatientController::class, 'searchAjax'])->name('patients.search-ajax');
        Route::post('/patients', [\App\Http\Controllers\PatientController::class, 'store'])->name('patients.store');
        Route::post('/patients/quick-store', [\App\Http\Controllers\PatientController::class, 'quickStore'])->name('patients.quick-store');
        Route::post('/patients/import', [\App\Http\Controllers\PatientController::class, 'import'])->name('patients.import');
        Route::get('/patients/export', [\App\Http\Controllers\PatientController::class, 'export'])->name('patients.export');
        Route::get('/patients/print-cards', [\App\Http\Controllers\PatientController::class, 'printCards'])->name('patients.print-cards');
        Route::post('/patients/bulk-action', [\App\Http\Controllers\PatientController::class, 'bulkAction'])->name('patients.bulk-action');
        Route::get('/patients/{id}', [\App\Http\Controllers\PatientController::class, 'show'])->name('patients.show');
        Route::put('/patients/{id}', [\App\Http\Controllers\PatientController::class, 'update'])->name('patients.update');
        Route::get('/patients/{id}/edit', [\App\Http\Controllers\PatientController::class, 'edit'])->name('patients.edit');
    });
    Route::get('/medications/search', [\App\Http\Controllers\PharmacyController::class, 'searchAjax'])->name('medications.search');
    
    // Consultations
    Route::middleware('permission:' . Permissions::MANAGE_CONSULTATIONS)->group(function () {
        Route::get('/consultations/create', [\App\Http\Controllers\ConsultationController::class, 'create'])->name('consultations.create');
        Route::post('/consultations', [\App\Http\Controllers\ConsultationController::class, 'store'])->name('consultations.store');
    });

    Route::middleware('role_or_permission:' . Permissions::staffOrPatient(Permissions::MANAGE_CONSULTATIONS))->group(function () {
        Route::get('/consultations', [\App\Http\Controllers\ConsultationController::class, 'index'])->name('consultations.index');
        Route::get('/consultations/{consultation}', [\App\Http\Controllers\ConsultationController::class, 'show'])->name('consultations.show');
        Route::get('/consultations/{id}/print', [\App\Http\Controllers\ConsultationController::class, 'print'])->name('consultations.print');
    });

    Route::middleware('permission:' . Permissions::MANAGE_CONSULTATIONS)->group(function () {
        Route::get('/consultations/{consultation}/edit', [\App\Http\Controllers\ConsultationController::class, 'edit'])->name('consultations.edit');
        Route::put('/consultations/{consultation}', [\App\Http\Controllers\ConsultationController::class, 'update'])->name('consultations.update');
        Route::patch('/consultations/{consultation}', [\App\Http\Controllers\ConsultationController::class, 'update']);
        Route::delete('/consultations/{consultation}', [\App\Http\Controllers\ConsultationController::class, 'destroy'])->name('consultations.destroy');
        Route::post('/consultations/bulk-action', [\App\Http\Controllers\ConsultationController::class, 'bulkAction'])->name('consultations.bulk-action');
    });
    
    // Vitals
    Route::middleware('permission:' . Permissions::MANAGE_VITALS)->group(function () {
        Route::get('/vitals', [\App\Http\Controllers\VitalController::class, 'index'])->name('vitals.index');
        Route::get('/vitals/record', [\App\Http\Controllers\VitalController::class, 'create'])->name('vitals.create');
        Route::post('/vitals', [\App\Http\Controllers\VitalController::class, 'store'])->name('vitals.store');
        Route::get('/vitals/{vital}/edit', [\App\Http\Controllers\VitalController::class, 'edit'])->name('vitals.edit');
        Route::put('/vitals/{vital}', [\App\Http\Controllers\VitalController::class, 'update'])->name('vitals.update');
        Route::delete('/vitals/{vital}', [\App\Http\Controllers\VitalController::class, 'destroy'])->name('vitals.destroy');
        Route::get('/patients/{id}/latest-vitals', [\App\Http\Controllers\VitalController::class, 'latest'])->name('patients.latest-vitals');
    });

    // Prescriptions
    Route::middleware('role_or_permission:' . Permissions::staffOrPatient(Permissions::MANAGE_PRESCRIPTIONS))->group(function () {
        Route::get('/prescriptions', [\App\Http\Controllers\PrescriptionController::class, 'index'])->name('prescriptions.index');
        Route::get('/prescriptions/{id}', [\App\Http\Controllers\PrescriptionController::class, 'show'])->name('prescriptions.show');
        Route::get('/prescriptions/{id}/print', [\App\Http\Controllers\PrescriptionController::class, 'print'])->name('prescriptions.print');
    });
    Route::middleware('permission:' . Permissions::MANAGE_PRESCRIPTIONS)->group(function () {
        Route::get('/prescriptions/create', [\App\Http\Controllers\PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::post('/prescriptions', [\App\Http\Controllers\PrescriptionController::class, 'store'])->name('prescriptions.store');
        Route::post('/prescriptions/bulk-action', [\App\Http\Controllers\PrescriptionController::class, 'bulkAction'])->name('prescriptions.bulk-action');
        Route::get('/prescriptions/{id}/edit', [\App\Http\Controllers\PrescriptionController::class, 'edit'])->name('prescriptions.edit');
        Route::put('/prescriptions/{id}', [\App\Http\Controllers\PrescriptionController::class, 'update'])->name('prescriptions.update');
        Route::delete('/prescriptions/{id}', [\App\Http\Controllers\PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');
        Route::post('/prescriptions/{id}/dispense', [\App\Http\Controllers\PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
    });
    
    // Lab
    Route::middleware('permission:' . Permissions::MANAGE_LAB)->group(function () {
        Route::get('/lab/requests', [\App\Http\Controllers\LabController::class, 'requests'])->name('lab.index');
        Route::get('/lab/requests/create', [\App\Http\Controllers\LabTestRequestController::class, 'create'])->name('lab.create');
        Route::post('/lab/requests', [\App\Http\Controllers\LabTestRequestController::class, 'store'])->name('lab.store');
        Route::post('/lab/bulk-action', [\App\Http\Controllers\LabController::class, 'bulkAction'])->name('lab.bulk-action');
        Route::delete('/lab/requests/{id}', [\App\Http\Controllers\LabTestRequestController::class, 'destroy'])->name('lab.requests.destroy');
        Route::get('/lab/requests/{id}', [\App\Http\Controllers\LabController::class, 'show'])->name('lab.show');
        Route::post('/lab/requests/{id}/status', [\App\Http\Controllers\LabController::class, 'updateStatus'])->name('lab.update-status');
        Route::get('/lab/requests/{id}/print', [\App\Http\Controllers\LabController::class, 'print'])->name('lab.print');
        Route::get('/lab/tests', [\App\Http\Controllers\LabController::class, 'tests'])->name('lab.tests');
        Route::get('/lab/samples', [\App\Http\Controllers\LabSampleController::class, 'index'])->name('lab.samples.index');
        Route::get('/lab/samples/register', [\App\Http\Controllers\LabSampleController::class, 'register'])->name('lab.samples.register');
        Route::post('/lab/samples', [\App\Http\Controllers\LabSampleController::class, 'store'])->name('lab.samples.store');
        Route::get('/lab/samples/{id}', [\App\Http\Controllers\LabSampleController::class, 'show'])->name('lab.samples.show');
        Route::get('/radiology/requests', [\App\Http\Controllers\RadiologyController::class, 'index'])->name('radiology.index');
        Route::get('/radiology/requests/create', [\App\Http\Controllers\RadiologyController::class, 'create'])->name('radiology.create');
        Route::post('/radiology/requests', [\App\Http\Controllers\RadiologyController::class, 'store'])->name('radiology.store');
        Route::post('/radiology/bulk-action', [\App\Http\Controllers\RadiologyController::class, 'bulkAction'])->name('radiology.bulk-action');
        Route::get('/radiology/requests/{id}', [\App\Http\Controllers\RadiologyController::class, 'show'])->name('radiology.show');
        Route::get('/radiology/requests/{id}/edit', [\App\Http\Controllers\RadiologyController::class, 'edit'])->name('radiology.edit');
        Route::put('/radiology/requests/{id}', [\App\Http\Controllers\RadiologyController::class, 'update'])->name('radiology.update');
        Route::post('/radiology/requests/{id}/status', [\App\Http\Controllers\RadiologyController::class, 'updateStatus'])->name('radiology.update-status');
        Route::delete('/radiology/requests/{id}', [\App\Http\Controllers\RadiologyController::class, 'destroy'])->name('radiology.destroy');
    });
    Route::middleware('role_or_permission:' . Permissions::staffOrPatient(Permissions::MANAGE_LAB))->group(function () {
        Route::get('/lab-results', [\App\Http\Controllers\LabController::class, 'results'])->name('lab.results');
        Route::get('/lab-results/{id}', [\App\Http\Controllers\LabController::class, 'resultShow'])->name('lab.results.show');
        Route::get('/lab-results/{id}/download', [\App\Http\Controllers\LabController::class, 'resultDownload'])->name('lab.results.download');
    });
    Route::middleware('permission:' . Permissions::MANAGE_LAB_CATALOG)->group(function () {
        Route::get('/lab-tests/manage', [\App\Http\Controllers\LabController::class, 'manage'])->name('lab.manage');
        Route::resource('lab-tests', \App\Http\Controllers\LabTestTypeController::class)->names([
            'index' => 'lab-tests.index',
            'create' => 'lab-tests.create',
            'store' => 'lab-tests.store',
            'edit' => 'lab-tests.edit',
            'update' => 'lab-tests.update',
            'destroy' => 'lab-tests.destroy',
        ]);
    });

    // Pharmacy
    Route::middleware('permission:' . Permissions::MANAGE_PHARMACY)->group(function () {
        Route::get('/pharmacy/inventory', [\App\Http\Controllers\PharmacyController::class, 'inventory'])->name('pharmacy.inventory');
        Route::post('/pharmacy/inventory/update-stock', [\App\Http\Controllers\PharmacyController::class, 'updateStock'])->name('pharmacy.inventory.update-stock');
        Route::get('/pharmacy/medicines', [\App\Http\Controllers\PharmacyController::class, 'medicines'])->name('pharmacy.medicines');
        Route::post('/pharmacy/medicines', [\App\Http\Controllers\PharmacyController::class, 'storeMedicine'])->name('pharmacy.medicines.store');
        Route::put('/pharmacy/medicines/{id}', [\App\Http\Controllers\PharmacyController::class, 'updateMedicine'])->name('pharmacy.medicines.update');
        Route::delete('/pharmacy/medicines/{id}', [\App\Http\Controllers\PharmacyController::class, 'destroyMedicine'])->name('pharmacy.medicines.destroy');
        Route::get('/pharmacy/purchase-orders', [\App\Http\Controllers\PharmacyController::class, 'poIndex'])->name('pharmacy.po');
        Route::post('/pharmacy/purchase-orders', [\App\Http\Controllers\PharmacyController::class, 'storePO'])->name('pharmacy.po.store');
        Route::put('/pharmacy/purchase-orders/{id}/status', [\App\Http\Controllers\PharmacyController::class, 'updatePOStatus'])->name('pharmacy.po.update-status');
    });
    
    // Invoices
    Route::middleware('role_or_permission:' . Permissions::staffOrPatient(Permissions::MANAGE_INVOICES))->group(function () {
        Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{id}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('/invoices/{id}/pdf', [\App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    });
    Route::middleware('permission:' . Permissions::MANAGE_INVOICES)->group(function () {
        Route::get('/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
        Route::post('/invoices/bulk-action', [\App\Http\Controllers\InvoiceController::class, 'bulkAction'])->name('invoices.bulk-action');
        Route::get('/invoices/export/csv', [\App\Http\Controllers\InvoiceController::class, 'exportCsv'])->name('invoices.export.csv');
        Route::put('/invoices/{id}', [\App\Http\Controllers\InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{id}', [\App\Http\Controllers\InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    // Payments
    Route::middleware('permission:' . Permissions::MANAGE_PAYMENTS)->group(function () {
        Route::get('/payments', [\App\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [\App\Http\Controllers\PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/export/csv', [\App\Http\Controllers\PaymentController::class, 'exportCsv'])->name('payments.export.csv');
        Route::get('/payments/{id}', [\App\Http\Controllers\PaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{id}/complete', [\App\Http\Controllers\PaymentController::class, 'complete'])->name('payments.complete');
        Route::get('/payments/{id}/print', [\App\Http\Controllers\PaymentController::class, 'print'])->name('payments.print');
    });

    // Follow-ups
    Route::middleware('permission:' . Permissions::MANAGE_FOLLOW_UPS)->group(function () {
        Route::get('/follow-ups', [\App\Http\Controllers\FollowUpController::class, 'index'])->name('follow-ups.index');
        Route::get('/follow-ups/upcoming', [\App\Http\Controllers\FollowUpController::class, 'upcoming'])->name('follow-ups.upcoming');
        Route::get('/follow-ups/create', [\App\Http\Controllers\FollowUpController::class, 'create'])->name('follow-ups.create');
        Route::post('/follow-ups', [\App\Http\Controllers\FollowUpController::class, 'store'])->name('follow-ups.store');
        Route::get('/follow-ups/{id}/edit', [\App\Http\Controllers\FollowUpController::class, 'edit'])->name('follow-ups.edit');
        Route::put('/follow-ups/{id}', [\App\Http\Controllers\FollowUpController::class, 'update'])->name('follow-ups.update');
        Route::delete('/follow-ups/{id}', [\App\Http\Controllers\FollowUpController::class, 'destroy'])->name('follow-ups.destroy');
        Route::post('/follow-ups/{id}/status', [\App\Http\Controllers\FollowUpController::class, 'updateStatus'])->name('follow-ups.update-status');
        Route::get('/follow-ups/{id}', [\App\Http\Controllers\FollowUpController::class, 'show'])->name('follow-ups.show');
    });

    // Departments
    Route::middleware('permission:' . Permissions::MANAGE_DEPARTMENTS)->group(function () {
        Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [\App\Http\Controllers\DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departments', [\App\Http\Controllers\DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{id}/edit', [\App\Http\Controllers\DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{id}', [\App\Http\Controllers\DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{id}', [\App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.destroy');
        Route::post('/departments/{id}/toggle', [\App\Http\Controllers\DepartmentController::class, 'toggle'])->name('departments.toggle');
        Route::get('/departments/{id}', [\App\Http\Controllers\DepartmentController::class, 'show'])->name('departments.show');
    });
    // Users
    Route::middleware('permission:' . Permissions::MANAGE_USERS)->group(function () {
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::post('/users/bulk-action', [\App\Http\Controllers\UserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::get('/users/{id}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
        Route::get('/users/{id}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    });
    // Reports
    Route::middleware('permission:' . Permissions::VIEW_REPORTS)->group(function () {
        Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/financial', [\App\Http\Controllers\ReportsController::class, 'financial'])->name('reports.financial');
        Route::get('/reports/appointments', [\App\Http\Controllers\ReportsController::class, 'appointments'])->name('reports.appointments');
        Route::get('/reports/patients', [\App\Http\Controllers\ReportsController::class, 'patients'])->name('reports.patients');
        Route::get('/reports/laboratory', [\App\Http\Controllers\ReportsController::class, 'laboratory'])->name('reports.laboratory');
        Route::get('/reports/pharmacy', [\App\Http\Controllers\ReportsController::class, 'pharmacy'])->name('reports.pharmacy');
        Route::get('/reports/export', [\App\Http\Controllers\ReportsController::class, 'exportCsv'])->name('reports.export');
        Route::get('/admin/void-audit', [\App\Http\Controllers\VoidAuditController::class, 'index'])->name('admin.void-audit.index');
    });

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Messages
    Route::middleware('permission:' . Permissions::SEND_MESSAGES)->group(function () {
        Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/entities', [\App\Http\Controllers\MessageController::class, 'getEntities'])->name('messages.entities');
        Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
        Route::post('/messages/users/{userId}/mark-read', [\App\Http\Controllers\MessageController::class, 'markAllRead'])->name('messages.mark-all-read');
        Route::post('/messages/users/{userId}/archive', [\App\Http\Controllers\MessageController::class, 'archiveConversation'])->name('messages.archive');
        Route::post('/messages/users/{userId}/unarchive', [\App\Http\Controllers\MessageController::class, 'unarchiveConversation'])->name('messages.unarchive');
        Route::delete('/messages/users/{userId}', [\App\Http\Controllers\MessageController::class, 'destroyConversation'])->name('messages.destroy-conversation');
        Route::post('/messages/{id}/read', [\App\Http\Controllers\MessageController::class, 'markRead'])->name('messages.mark-read');
        Route::delete('/messages/{id}', [\App\Http\Controllers\MessageController::class, 'destroy'])->name('messages.destroy');
    });

    Route::middleware('permission:' . Permissions::MANAGE_SETTINGS)->group(function () {
        Route::get('/admin/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('admin.settings.index');
    });

    Route::middleware('permission:' . Permissions::MANAGE_SYSTEM)->group(function () {
        Route::get('/admin/messages', [\App\Http\Controllers\ContactMessageController::class, 'index'])->name('admin.messages.index');
        Route::get('/admin/messages/{contactMessage}', [\App\Http\Controllers\ContactMessageController::class, 'show'])->name('admin.messages.show');
        Route::post('/admin/messages/{contactMessage}/read', [\App\Http\Controllers\ContactMessageController::class, 'markAsRead'])->name('admin.messages.read');
        Route::post('/admin/messages/{contactMessage}/reply', [\App\Http\Controllers\ContactMessageController::class, 'reply'])->name('admin.messages.reply');
        Route::delete('/admin/messages/{contactMessage}', [\App\Http\Controllers\ContactMessageController::class, 'destroy'])->name('admin.messages.destroy');
        Route::get('/admin/blogs', [\App\Http\Controllers\BlogController::class, 'manage'])->name('blog.manage');
        Route::get('/admin/blogs/create', [\App\Http\Controllers\BlogController::class, 'create'])->name('blog.create');
        Route::post('/admin/blogs', [\App\Http\Controllers\BlogController::class, 'store'])->name('blog.store');
        Route::get('/admin/blogs/{id}/edit', [\App\Http\Controllers\BlogController::class, 'edit'])->name('blog.edit');
        Route::post('/admin/blogs/{id}', [\App\Http\Controllers\BlogController::class, 'update'])->name('blog.update');
        Route::delete('/admin/blogs/{id}', [\App\Http\Controllers\BlogController::class, 'destroy'])->name('blog.destroy');
        Route::get('/admin/cms', [\App\Http\Controllers\CMSController::class, 'index'])->name('cms.index');
        Route::post('/admin/cms', [\App\Http\Controllers\CMSController::class, 'update'])->name('cms.update');
        Route::post('/admin/cms/service-tabs', [\App\Http\Controllers\CMSController::class, 'updateServiceTabs'])->name('cms.service-tabs.update');
        Route::get('/admin/medical-procedures', [\App\Http\Controllers\MedicalProcedureController::class, 'index'])->name('medical-procedures.index');
        Route::post('/admin/medical-procedures', [\App\Http\Controllers\MedicalProcedureController::class, 'store'])->name('medical-procedures.store');
        Route::put('/admin/medical-procedures/{id}', [\App\Http\Controllers\MedicalProcedureController::class, 'update'])->name('medical-procedures.update');
        Route::delete('/admin/medical-procedures/{id}', [\App\Http\Controllers\MedicalProcedureController::class, 'destroy'])->name('medical-procedures.destroy');
        Route::post('/admin/medical-procedures/{id}/toggle', [\App\Http\Controllers\MedicalProcedureController::class, 'toggle'])->name('medical-procedures.toggle');
        Route::get('/admin/telehealth-consents', [App\Http\Controllers\TelehealthController::class, 'adminIndex'])->name('telehealth.admin.index');
        Route::get('/admin/telehealth-consents/{id}', [App\Http\Controllers\TelehealthController::class, 'show'])->name('telehealth.admin.show');
        Route::post('/admin/telehealth-consents/{id}/doctor-sign', [App\Http\Controllers\TelehealthController::class, 'signDoctor'])->name('telehealth.admin.sign-doctor');
        Route::get('/admin/api-tokens', [\App\Http\Controllers\ApiTokenController::class, 'index'])->name('admin.api-tokens.index');
        Route::post('/admin/api-tokens', [\App\Http\Controllers\ApiTokenController::class, 'store'])->name('admin.api-tokens.store');
        Route::delete('/admin/api-tokens/{id}', [\App\Http\Controllers\ApiTokenController::class, 'destroy'])->name('admin.api-tokens.destroy');
        Route::get('/admin/mail-templates', [\App\Http\Controllers\MailTemplateController::class, 'index'])->name('mail-templates.index');
        Route::get('/admin/mail-templates/{id}/edit', [\App\Http\Controllers\MailTemplateController::class, 'edit'])->name('mail-templates.edit');
        Route::post('/admin/mail-templates/{id}', [\App\Http\Controllers\MailTemplateController::class, 'update'])->name('mail-templates.update');
    });

    Route::middleware('permission:' . Permissions::MANAGE_INSURANCE)->group(function () {
        Route::get('/admin/insurances', [\App\Http\Controllers\InsuranceController::class, 'index'])->name('insurances.index');
        Route::get('/admin/insurances/create', [\App\Http\Controllers\InsuranceController::class, 'create'])->name('insurances.create');
        Route::post('/admin/insurances', [\App\Http\Controllers\InsuranceController::class, 'store'])->name('insurances.store');
        Route::get('/admin/insurances/{id}/edit', [\App\Http\Controllers\InsuranceController::class, 'edit'])->name('insurances.edit');
        Route::post('/admin/insurances/{id}', [\App\Http\Controllers\InsuranceController::class, 'update'])->name('insurances.update');
        Route::delete('/admin/insurances/{id}', [\App\Http\Controllers\InsuranceController::class, 'destroy'])->name('insurances.destroy');
        Route::post('/admin/insurances/{id}/toggle', [\App\Http\Controllers\InsuranceController::class, 'toggle'])->name('insurances.toggle');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/image', [ProfileController::class, 'updateImage'])->name('profile.image.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
