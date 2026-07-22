<?php

use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogPublicController;
use App\Http\Controllers\CheckGuestDataController;
use App\Http\Controllers\CMSController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorBlockOutController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LabSampleController;
use App\Http\Controllers\LabTestRequestController;
use App\Http\Controllers\LabTestTypeController;
use App\Http\Controllers\MailTemplateController;
use App\Http\Controllers\MedicalProcedureController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RadiologyController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TelehealthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VitalController;
use App\Http\Controllers\VoidAuditController;
use App\Models\Blog;
use App\Models\ServiceTab;
use App\Models\Setting;
use App\Support\Permissions;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'blogs' => Blog::with('author')->where('is_published', true)->latest()->get(),
        'cms' => Setting::all()->pluck('value', 'key'),
        'serviceTabs' => ServiceTab::orderBy('sort_order')->get(),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/blogs', [BlogPublicController::class, 'index'])->name('blogs.public.index');
Route::get('/blogs/{slug}', [BlogPublicController::class, 'show'])->name('blogs.public.show');

// Google Authentication
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::get('/auth/google/complete-profile', [GoogleController::class, 'completeProfileView'])->name('auth.google.complete-profile');
Route::post('/auth/google/complete-profile', [GoogleController::class, 'storeProfile'])->middleware('throttle:5,1')->name('auth.google.store-profile');

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

Route::post('/contact', [ContactMessageController::class, 'store'])->middleware('throttle:10,1')->name('contact.store');
Route::post('/guest-appointment', [AppointmentController::class, 'storeGuest'])->middleware('throttle:5,1')->name('appointments.guest.store');
Route::get('/guest-appointments/confirmation', [AppointmentController::class, 'guestConfirmation'])->name('guest-appointments.confirmation');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,1')->name('newsletter.subscribe');
Route::post('/check-guest-data', [CheckGuestDataController::class, 'check'])->middleware('throttle:10,1')->name('guest.check');

// Telehealth
Route::get('/telehealth', [TelehealthController::class, 'index'])->name('telehealth.index');
Route::post('/telehealth/consent', [TelehealthController::class, 'store'])->middleware('throttle:5,1')->name('telehealth.store');
Route::get('/telehealth/meeting/{meetingId}', [TelehealthController::class, 'meetingRoom'])->middleware('auth')->name('telehealth.meeting');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{role}', [DashboardController::class, 'index'])->name('dashboard.role');

    // Appointments (staff or patient portal)
    Route::middleware('role_or_permission:'.Permissions::staffOrPatient(Permissions::MANAGE_APPOINTMENTS))->group(function () {
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
        Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->whereNumber('id')->name('appointments.show');
    });
    Route::middleware('permission:'.Permissions::MANAGE_APPOINTMENTS)->group(function () {
        Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::post('/appointments/bulk-action', [AppointmentController::class, 'bulkAction'])->name('appointments.bulk-action');
        Route::match(['put', 'patch'], '/appointments/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
        Route::post('/appointments/{id}/check-in', [AppointmentController::class, 'checkIn'])->name('appointments.check-in');
        Route::post('/appointments/{id}/confirm-telehealth-payment', [AppointmentController::class, 'confirmTelehealthPayment'])->name('appointments.confirm-telehealth-payment');
        Route::get('/doctors/search', [AppointmentController::class, 'searchDoctorsAjax'])->name('doctors.search');
        Route::get('/doctor-block-outs', [DoctorBlockOutController::class, 'index'])->name('doctor-block-outs.index');
        Route::post('/doctor-block-outs', [DoctorBlockOutController::class, 'store'])->name('doctor-block-outs.store');
        Route::delete('/doctor-block-outs/{id}', [DoctorBlockOutController::class, 'destroy'])->name('doctor-block-outs.destroy');
        Route::post('/doctor-block-outs/bulk-delete', [DoctorBlockOutController::class, 'bulkDelete'])->name('doctor-block-outs.bulk-delete');
    });

    // Patients (staff only)
    Route::middleware('permission:'.Permissions::MANAGE_PATIENTS)->group(function () {
        Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
        Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
        Route::get('/patients/search', [PatientController::class, 'searchAjax'])->name('patients.search-ajax');
        Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
        Route::post('/patients/quick-store', [PatientController::class, 'quickStore'])->name('patients.quick-store');
        Route::post('/patients/import', [PatientController::class, 'import'])->name('patients.import');
        Route::get('/patients/export', [PatientController::class, 'export'])->name('patients.export');
        Route::get('/patients/print-cards', [PatientController::class, 'printCards'])->name('patients.print-cards');
        Route::post('/patients/bulk-action', [PatientController::class, 'bulkAction'])->name('patients.bulk-action');
        Route::get('/patients/{id}', [PatientController::class, 'show'])->name('patients.show');
        Route::put('/patients/{id}', [PatientController::class, 'update'])->name('patients.update');
        Route::get('/patients/{id}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    });
    Route::middleware('permission:'.Permissions::MANAGE_PHARMACY)->group(function () {
        Route::get('/medications/search', [PharmacyController::class, 'searchAjax'])->name('medications.search');
    });

    // Consultations
    Route::middleware('permission:'.Permissions::MANAGE_CONSULTATIONS)->group(function () {
        Route::get('/consultations/create', [ConsultationController::class, 'create'])->name('consultations.create');
        Route::post('/consultations', [ConsultationController::class, 'store'])->name('consultations.store');
    });

    Route::middleware('role_or_permission:'.Permissions::staffOrPatient(Permissions::MANAGE_CONSULTATIONS))->group(function () {
        Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');
        Route::get('/consultations/{consultation}', [ConsultationController::class, 'show'])->name('consultations.show');
        Route::get('/consultations/{id}/print', [ConsultationController::class, 'print'])->name('consultations.print');
    });

    Route::middleware('permission:'.Permissions::MANAGE_CONSULTATIONS)->group(function () {
        Route::get('/consultations/{consultation}/edit', [ConsultationController::class, 'edit'])->name('consultations.edit');
        Route::put('/consultations/{consultation}', [ConsultationController::class, 'update'])->name('consultations.update');
        Route::patch('/consultations/{consultation}', [ConsultationController::class, 'update']);
        Route::delete('/consultations/{consultation}', [ConsultationController::class, 'destroy'])->name('consultations.destroy');
        Route::post('/consultations/bulk-action', [ConsultationController::class, 'bulkAction'])->name('consultations.bulk-action');
    });

    // Vitals
    Route::middleware('permission:'.Permissions::MANAGE_VITALS)->group(function () {
        Route::get('/vitals', [VitalController::class, 'index'])->name('vitals.index');
        Route::get('/vitals/record', [VitalController::class, 'create'])->name('vitals.create');
        Route::post('/vitals', [VitalController::class, 'store'])->name('vitals.store');
        Route::get('/vitals/{vital}/edit', [VitalController::class, 'edit'])->name('vitals.edit');
        Route::put('/vitals/{vital}', [VitalController::class, 'update'])->name('vitals.update');
        Route::delete('/vitals/{vital}', [VitalController::class, 'destroy'])->name('vitals.destroy');
        Route::get('/patients/{id}/latest-vitals', [VitalController::class, 'latest'])->name('patients.latest-vitals');
    });

    // Prescriptions
    Route::middleware('role_or_permission:'.Permissions::staffOrPatient(Permissions::MANAGE_PRESCRIPTIONS))->group(function () {
        Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
        Route::get('/prescriptions/{id}', [PrescriptionController::class, 'show'])->name('prescriptions.show');
        Route::get('/prescriptions/{id}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');
    });
    Route::middleware('permission:'.Permissions::MANAGE_PRESCRIPTIONS)->group(function () {
        Route::get('/prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
        Route::post('/prescriptions/bulk-action', [PrescriptionController::class, 'bulkAction'])->name('prescriptions.bulk-action');
        Route::get('/prescriptions/{id}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
        Route::put('/prescriptions/{id}', [PrescriptionController::class, 'update'])->name('prescriptions.update');
        Route::delete('/prescriptions/{id}', [PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');
        Route::post('/prescriptions/{id}/dispense', [PrescriptionController::class, 'dispense'])->name('prescriptions.dispense');
    });

    // Lab
    Route::middleware('permission:'.Permissions::MANAGE_LAB)->group(function () {
        Route::get('/lab/requests', [LabController::class, 'requests'])->name('lab.index');
        Route::get('/lab/requests/create', [LabTestRequestController::class, 'create'])->name('lab.create');
        Route::post('/lab/requests', [LabTestRequestController::class, 'store'])->name('lab.store');
        Route::post('/lab/bulk-action', [LabController::class, 'bulkAction'])->name('lab.bulk-action');
        Route::delete('/lab/requests/{id}', [LabTestRequestController::class, 'destroy'])->name('lab.requests.destroy');
        Route::get('/lab/requests/{id}', [LabController::class, 'show'])->name('lab.show');
        Route::post('/lab/requests/{id}/status', [LabController::class, 'updateStatus'])->name('lab.update-status');
        Route::get('/lab/requests/{id}/print', [LabController::class, 'print'])->name('lab.print');
        Route::get('/lab/tests', [LabController::class, 'tests'])->name('lab.tests');
        Route::get('/lab/samples', [LabSampleController::class, 'index'])->name('lab.samples.index');
        Route::get('/lab/samples/register', [LabSampleController::class, 'register'])->name('lab.samples.register');
        Route::post('/lab/samples', [LabSampleController::class, 'store'])->name('lab.samples.store');
        Route::get('/lab/samples/{id}', [LabSampleController::class, 'show'])->name('lab.samples.show');
        Route::get('/radiology/requests', [RadiologyController::class, 'index'])->name('radiology.index');
        Route::get('/radiology/requests/create', [RadiologyController::class, 'create'])->name('radiology.create');
        Route::post('/radiology/requests', [RadiologyController::class, 'store'])->name('radiology.store');
        Route::post('/radiology/bulk-action', [RadiologyController::class, 'bulkAction'])->name('radiology.bulk-action');
        Route::get('/radiology/requests/{id}', [RadiologyController::class, 'show'])->name('radiology.show');
        Route::get('/radiology/requests/{id}/edit', [RadiologyController::class, 'edit'])->name('radiology.edit');
        Route::put('/radiology/requests/{id}', [RadiologyController::class, 'update'])->name('radiology.update');
        Route::post('/radiology/requests/{id}/status', [RadiologyController::class, 'updateStatus'])->name('radiology.update-status');
        Route::delete('/radiology/requests/{id}', [RadiologyController::class, 'destroy'])->name('radiology.destroy');
    });
    Route::middleware('role_or_permission:'.Permissions::staffOrPatient(Permissions::MANAGE_LAB))->group(function () {
        Route::get('/lab-results', [LabController::class, 'results'])->name('lab.results');
        Route::get('/lab-results/{id}', [LabController::class, 'resultShow'])->name('lab.results.show');
        Route::get('/lab-results/{id}/download', [LabController::class, 'resultDownload'])->name('lab.results.download');
    });
    Route::middleware('permission:'.Permissions::MANAGE_LAB_CATALOG)->group(function () {
        Route::get('/lab-tests/manage', [LabController::class, 'manage'])->name('lab.manage');
        Route::resource('lab-tests', LabTestTypeController::class)->names([
            'index' => 'lab-tests.index',
            'create' => 'lab-tests.create',
            'store' => 'lab-tests.store',
            'edit' => 'lab-tests.edit',
            'update' => 'lab-tests.update',
            'destroy' => 'lab-tests.destroy',
        ]);
    });

    // Pharmacy
    Route::middleware('permission:'.Permissions::MANAGE_PHARMACY)->group(function () {
        Route::get('/pharmacy/inventory', [PharmacyController::class, 'inventory'])->name('pharmacy.inventory');
        Route::post('/pharmacy/inventory/update-stock', [PharmacyController::class, 'updateStock'])->name('pharmacy.inventory.update-stock');
        Route::get('/pharmacy/medicines', [PharmacyController::class, 'medicines'])->name('pharmacy.medicines');
        Route::post('/pharmacy/medicines', [PharmacyController::class, 'storeMedicine'])->name('pharmacy.medicines.store');
        Route::put('/pharmacy/medicines/{id}', [PharmacyController::class, 'updateMedicine'])->name('pharmacy.medicines.update');
        Route::delete('/pharmacy/medicines/{id}', [PharmacyController::class, 'destroyMedicine'])->name('pharmacy.medicines.destroy');
        Route::get('/pharmacy/purchase-orders', [PharmacyController::class, 'poIndex'])->name('pharmacy.po');
        Route::post('/pharmacy/purchase-orders', [PharmacyController::class, 'storePO'])->name('pharmacy.po.store');
        Route::put('/pharmacy/purchase-orders/{id}/status', [PharmacyController::class, 'updatePOStatus'])->name('pharmacy.po.update-status');
    });

    // Invoices
    Route::middleware('role_or_permission:'.Permissions::staffOrPatient(Permissions::MANAGE_INVOICES))->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->whereNumber('id')->name('invoices.show');
        Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->whereNumber('id')->name('invoices.print');
        Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf'])->whereNumber('id')->name('invoices.pdf');
    });
    Route::middleware('permission:'.Permissions::MANAGE_INVOICES)->group(function () {
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::post('/invoices/bulk-action', [InvoiceController::class, 'bulkAction'])->name('invoices.bulk-action');
        Route::get('/invoices/export/csv', [InvoiceController::class, 'exportCsv'])->name('invoices.export.csv');
        Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    // Payments
    Route::middleware('permission:'.Permissions::MANAGE_PAYMENTS)->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/export/csv', [PaymentController::class, 'exportCsv'])->name('payments.export.csv');
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{id}/complete', [PaymentController::class, 'complete'])->name('payments.complete');
        Route::get('/payments/{id}/print', [PaymentController::class, 'print'])->name('payments.print');
    });

    // Follow-ups
    Route::middleware('permission:'.Permissions::MANAGE_FOLLOW_UPS)->group(function () {
        Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
        Route::get('/follow-ups/upcoming', [FollowUpController::class, 'upcoming'])->name('follow-ups.upcoming');
        Route::get('/follow-ups/create', [FollowUpController::class, 'create'])->name('follow-ups.create');
        Route::post('/follow-ups', [FollowUpController::class, 'store'])->name('follow-ups.store');
        Route::get('/follow-ups/{id}/edit', [FollowUpController::class, 'edit'])->name('follow-ups.edit');
        Route::put('/follow-ups/{id}', [FollowUpController::class, 'update'])->name('follow-ups.update');
        Route::delete('/follow-ups/{id}', [FollowUpController::class, 'destroy'])->name('follow-ups.destroy');
        Route::post('/follow-ups/{id}/status', [FollowUpController::class, 'updateStatus'])->name('follow-ups.update-status');
        Route::get('/follow-ups/{id}', [FollowUpController::class, 'show'])->name('follow-ups.show');
    });

    // Departments
    Route::middleware('permission:'.Permissions::MANAGE_DEPARTMENTS)->group(function () {
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{id}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
        Route::post('/departments/{id}/toggle', [DepartmentController::class, 'toggle'])->name('departments.toggle');
        Route::get('/departments/{id}', [DepartmentController::class, 'show'])->name('departments.show');
    });
    // Users
    Route::middleware('permission:'.Permissions::MANAGE_USERS)->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    // Reports
    Route::middleware('permission:'.Permissions::VIEW_REPORTS)->group(function () {
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/financial', [ReportsController::class, 'financial'])->name('reports.financial');
        Route::get('/reports/appointments', [ReportsController::class, 'appointments'])->name('reports.appointments');
        Route::get('/reports/patients', [ReportsController::class, 'patients'])->name('reports.patients');
        Route::get('/reports/laboratory', [ReportsController::class, 'laboratory'])->name('reports.laboratory');
        Route::get('/reports/pharmacy', [ReportsController::class, 'pharmacy'])->name('reports.pharmacy');
        Route::get('/reports/export', [ReportsController::class, 'exportCsv'])->name('reports.export');
        Route::get('/admin/void-audit', [VoidAuditController::class, 'index'])->name('admin.void-audit.index');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Messages
    Route::middleware('permission:'.Permissions::SEND_MESSAGES)->group(function () {
        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/entities', [MessageController::class, 'getEntities'])->name('messages.entities');
        Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
        Route::post('/messages/users/{userId}/mark-read', [MessageController::class, 'markAllRead'])->name('messages.mark-all-read');
        Route::post('/messages/users/{userId}/archive', [MessageController::class, 'archiveConversation'])->name('messages.archive');
        Route::post('/messages/users/{userId}/unarchive', [MessageController::class, 'unarchiveConversation'])->name('messages.unarchive');
        Route::delete('/messages/users/{userId}', [MessageController::class, 'destroyConversation'])->name('messages.destroy-conversation');
        Route::post('/messages/{id}/read', [MessageController::class, 'markRead'])->name('messages.mark-read');
        Route::delete('/messages/{id}', [MessageController::class, 'destroy'])->name('messages.destroy');
    });

    Route::middleware('permission:'.Permissions::MANAGE_SETTINGS)->group(function () {
        Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    });

    Route::middleware('permission:'.Permissions::MANAGE_SYSTEM)->group(function () {
        Route::get('/admin/messages', [ContactMessageController::class, 'index'])->name('admin.messages.index');
        Route::get('/admin/messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('admin.messages.show');
        Route::post('/admin/messages/{contactMessage}/read', [ContactMessageController::class, 'markAsRead'])->name('admin.messages.read');
        Route::post('/admin/messages/{contactMessage}/reply', [ContactMessageController::class, 'reply'])->name('admin.messages.reply');
        Route::delete('/admin/messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('admin.messages.destroy');
        Route::get('/admin/blogs', [BlogController::class, 'manage'])->name('blog.manage');
        Route::get('/admin/blogs/create', [BlogController::class, 'create'])->name('blog.create');
        Route::post('/admin/blogs', [BlogController::class, 'store'])->name('blog.store');
        Route::get('/admin/blogs/{id}/edit', [BlogController::class, 'edit'])->name('blog.edit');
        Route::post('/admin/blogs/{id}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('/admin/blogs/{id}', [BlogController::class, 'destroy'])->name('blog.destroy');
        Route::get('/admin/cms', [CMSController::class, 'index'])->name('cms.index');
        Route::post('/admin/cms', [CMSController::class, 'update'])->name('cms.update');
        Route::post('/admin/cms/service-tabs', [CMSController::class, 'updateServiceTabs'])->name('cms.service-tabs.update');
        Route::get('/admin/medical-procedures', [MedicalProcedureController::class, 'index'])->name('medical-procedures.index');
        Route::post('/admin/medical-procedures', [MedicalProcedureController::class, 'store'])->name('medical-procedures.store');
        Route::put('/admin/medical-procedures/{id}', [MedicalProcedureController::class, 'update'])->name('medical-procedures.update');
        Route::delete('/admin/medical-procedures/{id}', [MedicalProcedureController::class, 'destroy'])->name('medical-procedures.destroy');
        Route::post('/admin/medical-procedures/{id}/toggle', [MedicalProcedureController::class, 'toggle'])->name('medical-procedures.toggle');
        Route::get('/admin/telehealth-consents', [TelehealthController::class, 'adminIndex'])->name('telehealth.admin.index');
        Route::get('/admin/telehealth-consents/{id}', [TelehealthController::class, 'show'])->name('telehealth.admin.show');
        Route::post('/admin/telehealth-consents/{id}/doctor-sign', [TelehealthController::class, 'signDoctor'])->name('telehealth.admin.sign-doctor');
        Route::get('/admin/api-tokens', [ApiTokenController::class, 'index'])->name('admin.api-tokens.index');
        Route::post('/admin/api-tokens', [ApiTokenController::class, 'store'])->name('admin.api-tokens.store');
        Route::delete('/admin/api-tokens/{id}', [ApiTokenController::class, 'destroy'])->name('admin.api-tokens.destroy');
        Route::get('/admin/mail-templates', [MailTemplateController::class, 'index'])->name('mail-templates.index');
        Route::get('/admin/mail-templates/{id}/edit', [MailTemplateController::class, 'edit'])->name('mail-templates.edit');
        Route::post('/admin/mail-templates/{id}', [MailTemplateController::class, 'update'])->name('mail-templates.update');
    });

    Route::middleware('permission:'.Permissions::MANAGE_INSURANCE)->group(function () {
        Route::get('/admin/insurances', [InsuranceController::class, 'index'])->name('insurances.index');
        Route::get('/admin/insurances/create', [InsuranceController::class, 'create'])->name('insurances.create');
        Route::post('/admin/insurances', [InsuranceController::class, 'store'])->name('insurances.store');
        Route::get('/admin/insurances/{id}/edit', [InsuranceController::class, 'edit'])->name('insurances.edit');
        Route::post('/admin/insurances/{id}', [InsuranceController::class, 'update'])->name('insurances.update');
        Route::delete('/admin/insurances/{id}', [InsuranceController::class, 'destroy'])->name('insurances.destroy');
        Route::post('/admin/insurances/{id}/toggle', [InsuranceController::class, 'toggle'])->name('insurances.toggle');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/image', [ProfileController::class, 'updateImage'])->name('profile.image.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
