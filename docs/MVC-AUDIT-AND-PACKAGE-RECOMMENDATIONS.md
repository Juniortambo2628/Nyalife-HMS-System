# Nyalife HMS – MVC Pipeline Audit & Package Recommendations

**Date:** February 2026  
**Scope:** Routes, controllers, views (Inertia/React pages), models; gaps and package replacement opportunities.

---

## 1. MVC Pipeline Summary

| Layer   | Count | Notes |
|--------|-------|--------|
| Routes | `web.php` + `auth.php` | All authenticated admin/doctor/nurse etc. under `auth` + `verified` |
| Controllers | 32 files | 30 used by routes; 2 orphan (see below) |
| Views (Pages) | 74 JSX under `resources/js/Pages/` | Inertia + React (no Blade pages except `app.blade.php`) |
| Models | 22 | Used by controllers; no orphan models identified |

---

## 2. Issues Found and Fixes Applied

### 2.1 Missing controller method (fixed)

- **Route:** `POST /lab/requests/{id}/status` → `LabController@updateStatus`
- **Issue:** `LabController` did not define `updateStatus()`; request would hit a 500 / method missing.
- **Fix:** Implemented `updateStatus()` in `LabController`: validates `status` (pending/completed/cancelled), updates `LabTestRequest`, sets `processed_at` / `processed_by` when completed, redirects back with success.

### 2.2 View path mismatch – Consultations Edit (fixed)

- **Controller:** `ConsultationController@edit` rendered `'Consultations/Edit'`.
- **Issue:** Page file lives at `Consultation/Edit.jsx` (singular), so Inertia would resolve to `Consultations/Edit.jsx` (plural) and fail.
- **Fix:** Controller now renders `'Consultation/Edit'` so the existing `Consultation/Edit.jsx` is used.

### 2.3 Empty destroy action (fixed)

- **Controller:** `ConsultationController@destroy`
- **Issue:** Method was empty (`//`), so delete route did nothing.
- **Fix:** Implemented `destroy($id)`: finds consultation, deletes, redirects to `consultations.index` with success message.

---

## 3. Remaining Gaps and Recommendations

### 3.1 Orphan controllers (no routes)

| Controller | Rendered views | Recommendation |
|------------|----------------|----------------|
| **MedicationController** | `Inventory/Index`, `Inventory/Show` | Either add routes (e.g. `/inventory`, `/inventory/{id}`) if inventory is a separate UX, or remove controller and use Pharmacy inventory/medicines routes only. |
| **LabTestRequestController** | `Lab/Index`, `Lab/Create`, `Lab/Show` | Lab flows use `LabController` (requests, tests, manage, results, show). This controller is redundant; consider removing or folding any unique logic into `LabController` and deleting the rest. |

### 3.2 Unused controller method

- **BlogController::index()**  
  Renders `Welcome` with blogs. No route points to it; only `blog.manage` (BlogController@manage) is used.  
  **Recommendation:** Remove `index()` or repurpose it (e.g. redirect to `blog.manage`) to avoid dead code.

### 3.3 Invoice destroy route

- **Routes:** Invoices have index, create, store, show, update but **no delete/destroy** route.
- **Recommendation:** If business rules allow deleting/voiding invoices, add a destroy route and implement `InvoiceController@destroy` (and consider soft deletes or status “void” instead of hard delete).

### 3.4 Role/permission checks

- **Current:** Role is used in `DashboardController`, `AppointmentController`, `MessageController`, etc. via `$user->role` and ad‑hoc `in_array($role, [...])` checks. No central authorization.
- **Recommendation:** Introduce **Laravel Policy** or **Spatie Laravel Permission** so permissions are defined in one place and reused in controllers and (optionally) in the frontend.

---

## 4. Package / Framework Replacements to Reduce Boilerplate

### 4.1 Form Requests (validation)

- **Current:** Inline `$request->validate([...])` in almost every controller (Invoice, User, Prescription, Pharmacy, Consultation, Appointment, Vital, Patient, ContactMessage, Blog, LabTestType, Message, etc.).
- **Recommendation:** Use **Laravel Form Request** classes (`php artisan make:request StorePatientRequest`, etc.). Benefits: reusable rules, authorize(), cleaner controllers, consistent error responses for Inertia.

### 4.2 Roles and permissions

- **Current:** Custom `Role` model and `roleRelation` on `User`; role names checked manually in controllers.
- **Recommendation:** **spatie/laravel-permission**. Gives roles + permissions, middleware (`role:doctor`), `@can` in Blade and `$user->can()` in controllers, and scales better than ad‑hoc role checks.

### 4.3 Search and filtering

- **Current:** Repeated `whereHas('patient.user', fn($q) => $q->where('first_name', 'like', ...))` (and similar) across Invoice, Prescription, Consultation, Lab, Patient, User, Appointment, Blog, Pharmacy, Medication controllers.
- **Recommendation:**  
  - **Laravel Query Scopes** on models (e.g. `Patient::scopeSearchByName()`, `LabTestRequest::scopeFilterByStatus()`) to centralize logic.  
  - Optional: **spatie/laravel-query-builder** for filter/sort/embed query params and less controller code.

### 4.4 API resources (JSON shape)

- **Current:** Controllers often pass raw Eloquent models or hand‑built arrays to Inertia; some relations loaded ad hoc; risk of N+1 and inconsistent shapes.
- **Recommendation:** **Laravel API Resources** (`php artisan make:resource PatientResource`) for key entities (Patient, Appointment, Consultation, Invoice, etc.) so Inertia props are consistent and relations are controlled in one place.

### 4.5 Reports and exports

- **Current:** `ReportsController` only returns simple counts; no PDF/Excel export seen in the audited code.
- **Recommendation:**  
  - **barryvdh/laravel-dompdf** or **spatie/laravel-pdf** for PDFs (invoices, prescriptions, lab results).  
  - **maatwebsite/laravel-excel** for Excel reports (appointments, revenue, lab requests).

### 4.6 Notifications

- **Current:** Laravel `Notifiable` and database notifications; custom `NotificationController` for index/mark read/destroy.
- **Recommendation:** Keep current approach; optional: **Laravel Echo + broadcasting** for real‑time notifications if needed later.

### 4.7 Dashboard and charts

- **Current:** `DashboardController` builds stats and “performance” arrays manually per role; no dedicated chart library referenced.
- **Recommendation:** Keep server-side data; consider a small chart library on the frontend (e.g. Chart.js or Recharts) if not already in use. Optional: **ConsoleTVs/Charts** if you prefer server-side chart config.

### 4.8 Settings / CMS

- **Current:** `Setting` model and `ServiceTab`; `CMSController` for site settings and service tabs.
- **Recommendation:** For more complex settings (tabs, feature flags), **spatie/laravel-settings** can replace ad‑hoc key/value usage and add caching/typing.

---

## 5. Quick Reference – Routes vs Controllers vs Views

- **Auth:** Breeze routes and controllers; all render Inertia pages under `Auth/` (Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword).
- **Dashboard:** `DashboardController@index` → `Dashboard` or role-specific `Dashboard/Admin`, `Dashboard/Doctor`, etc.
- **Consultations:** Resource; Index/Create/View/Edit use `Consultations/*` and `Consultation/Edit` (and `Consultation/Form`).
- **Lab:** `LabController` → Lab/Index, Lab/Tests, Lab/Manage, Lab/Show; `LabResults/Index`; `LabTestTypeController` → Lab/Tests/Index, Lab/Tests/Form.
- **Pharmacy:** `PharmacyController` → Pharmacy/Inventory, Pharmacy/Medicines.
- **Prescriptions, Invoices, Patients, Appointments, Users, ContactMessages, Blog, CMS, Reports, Notifications, Messages:** Controllers and Inertia page names align with route names; no missing view detected for used routes.

---

## 6. Suggested Next Steps

1. **Remove or wire orphan controllers:** Decide on MedicationController and LabTestRequestController; add routes or delete and consolidate into Pharmacy/Lab.
2. **Add `LabController@updateStatus`** – already implemented.
3. **Fix Consultation edit view path** – already fixed to `Consultation/Edit`.
4. **Implement `ConsultationController@destroy`** – already implemented.
5. **Introduce Form Requests** for 2–3 main flows (e.g. Patient, Appointment, Invoice) as a pattern for the rest.
6. **Add Spatie Permission** and replace manual role checks with middleware/policies.
7. **Add invoice destroy** (and/or void flow) if required by business.
8. **Optionally** introduce API Resources and query scopes/search for the busiest modules (Patients, Appointments, Lab, Invoices).

This audit and the applied fixes should resolve the missing route/controller/view issues and give a clear path to reducing boilerplate and extending the system with packages.
