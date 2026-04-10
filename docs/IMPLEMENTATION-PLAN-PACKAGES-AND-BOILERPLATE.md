# Implementation Plan: Packages & Boilerplate Reduction

This plan implements the recommendations from the MVC audit: Form Requests, Spatie Permission, Query scopes / Query Builder, API Resources, PDF/Excel, and Spatie Settings. Implement in phases so each can be tested before moving on.

---

## Phase 0: Prerequisites (done once)

- PHP 8.2+, Composer, Node already in use.
- Ensure `docs/MVC-AUDIT-AND-PACKAGE-RECOMMENDATIONS.md` is read for context.

---

## Phase 1: Form Requests

**Goal:** Replace inline `$request->validate([...])` with Form Request classes so validation is reusable and controllers stay thin.

### 1.1 Create Form Request classes

Run for each flow you want to migrate first (suggested order):

```bash
php artisan make:request StorePatientRequest
php artisan make:request UpdatePatientRequest
php artisan make:request StoreAppointmentRequest
php artisan make:request UpdateAppointmentRequest
php artisan make:request StoreInvoiceRequest
php artisan make:request UpdateInvoiceRequest
php artisan make:request StoreConsultationRequest
php artisan make:request UpdateConsultationRequest
php artisan make:request StorePrescriptionRequest
php artisan make:request StoreUserRequest
php artisan make:request UpdateUserRequest
php artisan make:request StoreContactMessageRequest
php artisan make:request StoreBlogRequest
php artisan make:request UpdateBlogRequest
php artisan make:request StoreLabTestTypeRequest
php artisan make:request UpdateLabTestTypeRequest
php artisan make:request StoreMessageRequest
php artisan make:request UpdateLabRequestStatusRequest
php artisan make:request StoreVitalRequest
php artisan make:request StoreMedicineRequest
php artisan make:request UpdateMedicineRequest
php artisan make:request UpdatePharmacyStockRequest
php artisan make:request StoreGuestAppointmentRequest
php artisan make:request CheckGuestDataRequest
```

### 1.2 Implement rules in each Request

- Move the current `$request->validate([...])` rules from the controller into the Form Request’s `rules()` method.
- In `authorize()` return `true` for now (or add policy checks later when Phase 2 is done).
- Optionally add `attributes()` for friendlier validation messages.

### 1.3 Type-hint and use in controllers

Example for `PatientController`:

```php
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;

public function store(StorePatientRequest $request) {
    $validated = $request->validated();
    // ... use $validated
}

public function update(UpdatePatientRequest $request, $id) {
    $validated = $request->validated();
    // ...
}
```

- Replace every corresponding `$request->validate([...])` with the appropriate Form Request type-hint and `$request->validated()`.

### 1.4 Wire up

- Ensure failed validation still returns 422 and Inertia receives errors (Laravel + Inertia handle this by default when using Form Requests).
- Test one module end-to-end (e.g. Patients: create, update), then roll out to the rest.

---

## Phase 2: Spatie Laravel Permission

**Goal:** Replace manual role checks with roles and permissions and use middleware/policies.

### 2.1 Install and publish

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 2.2 Migrate from custom roles

- Your app has a `roles` table and `User.role_id` / `roleRelation` / `role` attribute.
- Options:
  - **A (recommended):** Keep existing `roles` and `role_id` for now; add Spatie’s `model_has_roles` (and optionally permissions) and sync existing role names into Spatie’s `roles` table via a one-off command or migration. Then use Spatie’s `HasRoles` trait and `$user->hasRole('doctor')` etc., and gradually phase out direct `role_id` checks.
  - **B:** Full migration: create a migration that copies `roles` into Spatie’s tables, adds `model_has_roles` for each user, then switch `User` to use `HasRoles` and remove `role_id` / `roleRelation` over time.

### 2.3 Configure User model

```php
// app/Models/User.php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable {
    use HasRoles;
    // keep or deprecate roleRelation/role_id as per 2.2
}
```

### 2.4 Define roles and permissions

- In a seeder or migration: create roles (`admin`, `doctor`, `nurse`, `patient`, `lab_technician`, `pharmacist`, `receptionist`) and assign permissions (e.g. `manage-patients`, `manage-appointments`, `view-reports`, `manage-invoices`).
- Assign roles to users (via Spatie’s APIs or your existing data).

### 2.5 Replace manual checks

- **Middleware:** Create route groups or named middleware using `role:doctor`, `role:admin`, etc., and apply to routes in `web.php` (e.g. `/admin/*` for admin, lab routes for lab_technician).
- **Controllers:** Replace `if ($user->role === 'doctor')` and `in_array($role, [...])` with `$request->user()->can('permission')` or `$request->user()->hasRole('doctor')`.
- **Policies (optional):** Create policies for key models (e.g. `AppointmentPolicy`, `PatientPolicy`) and use `$this->authorize('update', $appointment)` in controllers.

### 2.6 Frontend (optional)

- Share a minimal set of permissions/roles to the frontend (e.g. via Inertia shared data or a small API) so the UI can show/hide menu items or buttons. Don’t rely on frontend for security; keep authorization on the server.

---

## Phase 3: Query scopes and optional Spatie Query Builder

**Goal:** Centralize search/filter logic and reduce duplication in controllers.

### 3.1 Add local query scopes on models

- **Patient / User (for patient name search):**  
  On `Patient`: `scopeSearchByUserName($query, $search)` that does `$query->whereHas('user', fn($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));`
- **Appointment:** `scopeForDoctor($query, $staffId)`, `scopeForPatient($query, $patientId)`, `scopePending($query)`, etc.
- **LabTestRequest:** `scopeSearchByPatientName($query, $search)`, `scopeStatus($query, $status)`.
- **Consultation:** `scopeSearchByPatientOrDiagnosis($query, $search)`, `scopeForDoctor($query, $doctorId)`.
- **Invoice:** `scopeSearchByPatientOrNumber($query, $search)`.
- **Prescription:** `scopeSearchByPatientName($query, $search)`.
- **Blog:** `scopeSearch($query, $search)` (title/content).
- **Medication (Pharmacy):** `scopeSearchByNameOrType($query, $search)`.

Use the same relation/column logic you already have in controllers, but move it into the model.

### 3.2 Use scopes in controllers

Example:

```php
// LabController@requests
$query = LabTestRequest::with([...])
    ->searchByPatientName($request->search)
    ->when($request->status, fn($q) => $q->where('status', $request->status));
return Inertia::render('Lab/Index', [
    'requests' => $query->latest()->paginate(15),
    ...
]);
```

Repeat for InvoiceController, PrescriptionController, ConsultationController, PatientController, UserController, BlogController, PharmacyController as applicable.

### 3.3 (Optional) Spatie Query Builder

- Install: `composer require spatie/laravel-query-builder`.
- Use for list endpoints that need flexible filtering/sorting via query params (e.g. `?filter[status]=pending&sort=-created_at`).  
- In controller:  
  `QueryBuilder::for(LabTestRequest::class)->allowedFilters(['status'])->allowedSorts(['created_at'])->...`  
- This is optional; local scopes alone already reduce boilerplate.

---

## Phase 4: API Resources for Inertia

**Goal:** Consistent JSON shapes for Inertia props and control over relations to avoid N+1.

### 4.1 Create API Resources

```bash
php artisan make:resource PatientResource
php artisan make:resource PatientCollection
php artisan make:resource AppointmentResource
php artisan make:resource ConsultationResource
php artisan make:resource InvoiceResource
php artisan make:resource PrescriptionResource
php artisan make:resource UserResource
php artisan make:resource LabTestRequestResource
# add more as needed: VitalResource, BlogResource, etc.
```

### 4.2 Define toArray in each Resource

- Include only the fields the frontend needs; load relations in the controller and pass them to the resource (e.g. `PatientResource::make($patient->load('user'))`).
- Use conditional attributes if different roles get different data.

### 4.3 Use in controllers

- Replace raw model or ad-hoc arrays with resources.  
  Example:  
  `return Inertia::render('Patients/Show', ['patient' => new PatientResource($patient->load('user'))]);`  
- For lists: `PatientResource::collection($patients)` or use a Collection resource that wraps pagination.

### 4.4 Ensure N+1 is avoided

- Always `->load(...)` or `->with(...)` the relations needed by the resource before passing the model to the resource.

---

## Phase 5: PDF and Excel

**Goal:** Add PDF export (e.g. invoices, prescriptions) and Excel export (e.g. reports, lists).

### 5.1 PDF

- Install one of:
  - `composer require barryvdh/laravel-dompdf`, or  
  - `composer require spatie/laravel-pdf`
- Create a view (Blade) for the invoice/prescription/lab result layout.
- In controller (e.g. `InvoiceController@downloadPdf`): generate PDF from the view and return as download.
- Add route: e.g. `GET /invoices/{id}/pdf` (protected by auth and optionally permission).

### 5.2 Excel

- Install: `composer require maatwebsite/laravel-excel`
- Create Export classes: e.g. `php artisan make:export AppointmentsExport --model=Appointment`, and optionally `InvoicesExport`, `PatientsExport`, `LabRequestsExport`.
- In each Export, define `headings()` and `map($row)` (or use FromQuery with column mapping).
- In controller (e.g. `ReportsController`): return `Excel::download(new AppointmentsExport, 'appointments.xlsx');`
- Add routes and menu links for “Export to Excel” where appropriate.

---

## Phase 6: Spatie Laravel Settings

**Goal:** Replace ad-hoc key/value usage (e.g. `Setting`, `ServiceTab`) with a typed settings layer if you want caching and structure.

### 6.1 Install and publish

```bash
composer require spatie/laravel-settings
php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
php artisan migrate
```

### 6.2 Create settings classes

- One (or more) settings class, e.g. `App\Settings\GeneralSettings` with properties like `site_name`, `contact_email`, etc., and optionally `App\Settings\CmsSettings` for service tabs or feature flags.
- Use casts and default values; Spatie will store them in the DB and cache.

### 6.3 Replace usage in app

- Where you currently use `Setting::all()->pluck('value', 'key')` or `ServiceTab::orderBy('sort_order')->get()`, switch to injecting and using the new settings classes (e.g. `GeneralSettings::get('site_name')` or a DTO returned by the settings class). You can keep the existing `settings` and `service_tabs` tables and migrate data into Spatie’s table, or keep both during transition.

### 6.4 CMS controller

- Update `CMSController` to read/write through the new settings classes instead of raw `Setting`/`ServiceTab` if you fully migrate; otherwise wire only new settings to Spatie and leave existing tables as-is until you’re ready.

---

## Implementation order (suggested)

| Order | Phase | Reason |
|-------|--------|--------|
| 1 | Form Requests | Low risk, immediate clarity and reuse. |
| 2 | Spatie Permission | Unlocks middleware and policies; do before heavy policy use. |
| 3 | Query scopes | Reduces controller duplication; independent of 4. |
| 4 | API Resources | Improves Inertia props; do after scopes so list queries are clean. |
| 5 | PDF/Excel | Feature add; can be done in parallel with 3–4. |
| 6 | Spatie Settings | Optional; do when you’re ready to refactor CMS/settings. |

---

## Wiring checklist

- **Form Requests:** All create/update/store actions type-hint the correct Request class and use `$request->validated()`.
- **Spatie Permission:** User uses `HasRoles`; routes use `role:` or permission middleware; controllers use `can()`/`hasRole()` instead of raw role checks.
- **Query scopes:** Every list that filters by search/status uses a model scope instead of inline `whereHas`/`where`.
- **API Resources:** All Inertia responses that pass models use a Resource (or collection) and eager load relations.
- **PDF:** At least one export (e.g. invoice) implemented and linked from UI.
- **Excel:** At least one report (e.g. appointments or revenue) exported via Laravel Excel and linked from Reports.
- **Settings:** If Phase 6 is done, CMS and any global config use Spatie settings and migrations are run.

---

## Notes

- Run tests after each phase (`php artisan test`).
- Keep the existing `Role` and `role_id` until Spatie is fully adopted and data is migrated.
- The legacy_backup folder is excluded from Intelephense via `.vscode/settings.json` so Hack (`.hh`) files are not reported as PHP errors.
