# Implementation Checklist – Packages & Boilerplate

**Reference:** [IMPLEMENTATION-PLAN-PACKAGES-AND-BOILERPLATE.md](./IMPLEMENTATION-PLAN-PACKAGES-AND-BOILERPLATE.md)

Track progress for each phase. Check off items as they are completed.

---

## Phase 1: Form Requests

- [x] **1.1** Create all Form Request classes (Store/Update per flow)
- [x] **1.2** Implement `rules()` and `authorize()` in each Request
- [x] **1.3** Wire controllers: type-hint Request, use `$request->validated()`
- [ ] **1.4** Verify validation (422 + Inertia errors) and test one module

**Flows:** Patient, Appointment, Invoice, Consultation, Prescription, User, ContactMessage, Blog, LabTestType, Message, Lab status, Vital, Medicine, Pharmacy stock, Guest appointment, Check guest data

---

## Phase 2: Spatie Laravel Permission

- [x] **2.1** Install and publish (config + migration created; run `php artisan migrate`)
- [x] **2.2** Migrate from custom roles (SyncSpatieRolesSeeder syncs app roles → Spatie)
- [x] **2.3** Add `HasRoles` to User model
- [x] **2.4** Define roles and permissions (seeder: `php artisan db:seed --class=SyncSpatieRolesSeeder`)
- [x] **2.5** New users get Spatie role in UserController::store; use `$user->hasRole('role')` where needed
- [ ] **2.6** (Optional) Share permissions to frontend for UI

**Note:** Spatie uses table `spatie_roles` to avoid conflict with existing `roles`. After migrate, run the seeder once to sync existing users.

---

## Phase 3: Query scopes

- [x] **3.1** Add scopes on Patient, LabTestRequest, Consultation, Invoice, Prescription, Blog, Medication, Appointment, User
- [x] **3.2** Use scopes in all list controllers (Lab, Invoice, Prescription, Consultation, Patient, User, BlogPublic, Pharmacy)
- [ ] **3.3** (Optional) Install and use Spatie Query Builder for filter/sort params

---

## Phase 4: API Resources for Inertia

- [x] **4.1** Create Resource (and optional Collection) classes for main entities
- [x] **4.2** Define `toArray()` in each Resource (User, Patient, Invoice, Consultation, Prescription, Appointment, LabTestRequest, Staff)
- [x] **4.3** Use Resources in controllers for Inertia responses (Patient, Invoice, Prescription, Consultation, User, Lab, Appointment, LabTestRequest)
- [x] **4.4** Eager load relations to avoid N+1 (controllers already use `with()`)
- [x] **4.5** `JsonResource::withoutWrapping()` in AppServiceProvider for Inertia payload shape

---

## Phase 5: PDF and Excel

- [ ] **5.1** Install PDF package; create Blade view; add route and controller action (e.g. invoice PDF)
- [ ] **5.2** Install Laravel Excel; create Export classes; add export routes and Reports UI links

---

## Phase 6: Spatie Laravel Settings

- [ ] **6.1** Install and publish Spatie Settings; migrate
- [ ] **6.2** Create settings class(es) (e.g. GeneralSettings, CmsSettings)
- [ ] **6.3** Replace Setting/ServiceTab usage with settings classes
- [ ] **6.4** Update CMSController to use new settings

---

## Wiring checklist (final)

- [ ] All create/update/store actions use Form Requests and `validated()`
- [ ] User uses HasRoles; routes use role/permission middleware; controllers use `can()`/`hasRole()`
- [ ] List endpoints use model scopes for search/filter
- [ ] Inertia responses use API Resources with eager loading
- [ ] At least one PDF export and one Excel export implemented and linked
- [ ] (If Phase 6 done) CMS uses Spatie settings
