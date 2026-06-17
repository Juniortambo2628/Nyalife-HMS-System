# Nyalife HMS — Migration Implementation Plan

**Created:** May 2026  
**Reference:** Migration audit (legacy `legacy_backup/` vs Laravel/Inertia app)  
**Tracking:** Check off items as completed; phases are ordered by dependency and risk.

---

## Progress Overview

| Phase | Scope | Status | Est. effort |
|-------|--------|--------|-------------|
| **0** | Fix broken wiring | Complete | 1–2 days |
| **1** | Payments module | Complete | 3–5 days |
| **2** | Follow-ups module | Complete | 2–3 days |
| **3** | Departments module | Complete | 1–2 days |
| **4** | Lab results + samples | Complete | 3–4 days |
| **5** | Clinical history integration | Complete | 1–2 days |
| **6** | Reports expansion | Complete | 2–3 days |
| **7** | Print views & PDF | Complete | 1–2 days |
| **8** | Messages, settings, public polish | Complete | 2–3 days |
| **9** | Auth hardening + API | Complete | 3–5 days |

---

## Phase 0 — Fix Broken Wiring

**Goal:** No 500s from missing methods; no debug routes in production; remove dead MVC artifacts.

- [x] Implement `MessageController::markRead($id)`
- [x] Remove `GET /auth/google/check-config` debug route
- [x] Remove `GET /add-pregnancy-test` ad-hoc seeder route
- [x] Delete unrouted `MedicationController` + `Inventory/*` pages
- [x] Remove duplicate `LabTestRequestController@index` and `@show`
- [x] Delete orphan pages: `Lab/Manage.jsx`, `Lab/Requests.jsx`, `Lab/Tests.jsx`
- [x] Remove dead `BlogController::index()` if unused

**Acceptance:** All registered routes resolve to existing controller methods; no orphan controllers/pages referenced only by dead code.

---

## Phase 1 — Payments Module

**Goal:** Legacy parity for payment records separate from invoices.

- [x] `Payment` model mapped to existing `payments` table
- [x] Form Request: `StorePaymentRequest`
- [x] `PaymentController`: index, create, store, show, complete, print, exportCsv
- [x] Inertia pages: `Payments/Index`, `Create`, `Show`, `Print`
- [x] Routes under `/payments`
- [x] Wire `Invoices/Show` — record payment modal + payment history
- [x] Sidebar: admin + receptionist
- [x] CSV export for payments and invoices
- [ ] Server-rendered PDF (deferred to Phase 7; print views use client print)
- [x] `PaymentResource` for Inertia payloads

**Acceptance:** Staff can record partial/full payments against invoices; payment list and receipt view work; exports generate files.

---

## Phase 2 — Follow-ups Module

**Goal:** Dedicated follow-up scheduling (table `follow_ups` already exists).

- [x] `FollowUp` model
- [x] Form Requests: store/update
- [x] `FollowUpController`: CRUD, upcoming list, status update, stats
- [x] Pages: `FollowUps/Index`, `Create`, `Show`, `Edit`
- [x] Routes + sidebar (admin, doctor, nurse)
- [x] Link from `Consultations/View` — schedule follow-up + list scheduled items
- [ ] Optional: auto-create appointment from follow-up (deferred)

**Acceptance:** Follow-ups CRUD works; upcoming view shows scheduled items; linked to patient + consultation.

---

## Phase 3 — Departments Module

**Goal:** Admin CRUD for `departments` table.

- [x] `Department` model
- [x] `DepartmentController`: CRUD + activate/deactivate toggle
- [x] Pages: `Departments/Index`, `Form`, `Show`
- [x] Routes + admin sidebar
- [x] Wire staff profile department field to `departments` table (dropdown on profile)

**Acceptance:** Departments manageable; active departments available in staff forms.

---

## Phase 4 — Lab Results Portal + Samples

**Goal:** Replace stub `/lab-results` and sample registration workflow.

- [x] Implement `LabController::results()` with patient-scoped query
- [x] Flesh out `LabResults/Index`, add `LabResults/Show`
- [x] Download/print route for patient results
- [x] Sample workflow: register sample, view sample (`LabSample` model)
- [ ] Optional: `LabAttachment` model for file uploads (deferred)
- [x] Patient sidebar → `route('lab.results')`

**Acceptance:** Patients see completed lab results; lab tech can register samples; results downloadable.

---

## Phase 5 — Clinical History Integration (replaces separate medical_history module)

**Goal:** Carry forward consultation history and surface patient clinical alerts without duplicating the legacy `medical_history` table.

- [x] Prefill new consultations from the patient's most recent completed consultation
- [x] Surface `allergies` and `chronic_diseases` on consultation create form
- [x] Clinical alerts card on `Patients/Show`
- [x] Clinical Background tab on `Patients/Show` (aggregated from latest consultation)
- [ ] ~~Separate `medical_history` CRUD~~ — skipped (redundant with consultation workflow)

**Acceptance:** Returning patients get history prefilled; allergies/chronic conditions visible on patient record and new consultations.

---

## Phase 6 — Reports Expansion

**Goal:** Typed reports matching legacy sub-pages.

- [x] `ReportsController`: financial, appointments, patients, laboratory, pharmacy
- [x] UI: sub-routes under `/reports/*` with shared nav
- [x] Per-report CSV export (financial, patients, appointments, consultations, laboratory, pharmacy)
- [x] Date range filters consistent with `Reports/Index`

**Acceptance:** Each report type shows filtered data and exports.

---

## Phase 7 — Print Views & PDF

**Goal:** Server-side and dedicated print routes.

- [x] `GET /consultations/{id}/print` → `Consultations/Print.jsx`
- [x] Dedicated prescription print route → `Prescriptions/Print.jsx`
- [x] Invoice print route + server-rendered PDF (Blade + dompdf)
- [x] Legacy print styles incorporated into print views

**Acceptance:** Consultation and invoice printable via route; PDF download works.

---

## Phase 8 — Messages, Settings, Public Polish

**Goal:** Close smaller legacy gaps.

- [x] Messages: compose, search, archive, delete
- [x] Guest appointment confirmation page (`/guest-appointments/confirmation`)
- [x] Newsletter `POST /newsletter/subscribe` + Welcome form
- [x] Admin settings panel (system stats) — CMS remains for landing page edits
- [ ] Social redirect routes (optional)

**Acceptance:** Messaging UX matches legacy core flows; public booking has confirmation step.

---

## Phase 9 — Authorization & API

**Goal:** Production-ready access control; optional REST API.

- [x] Replace ad-hoc `$user->role` checks with Spatie middleware/policies on new modules
- [x] Share permissions to frontend for UI gating (checklist 2.6)
- [x] Create `routes/api.php` if external clients needed
- [x] Port high-value legacy API routes (departments, payments, follow-ups, available-slots)

**Acceptance:** Role middleware on sensitive routes; API documented if implemented.

---

## Post-migration — Auth hardening (optional)

See [`docs/AUTH-HARDENING.md`](AUTH-HARDENING.md) for full details.

- [x] Core module permissions (patients, appointments, consultations, lab, pharmacy, etc.)
- [x] Patient portal `view-own-records` + ownership checks
- [x] Sanctum bearer tokens + admin UI at `/admin/api-tokens`
- [x] Sidebar filtered by `auth.permissions`

---

## Dependencies

```
Phase 0 ──► all other phases
Phase 1 ──► Phase 6 (financial reports), Phase 7 (invoice PDF)
Phase 2 ──► Phase 9 (follow-ups API)
Phase 3 ──► Phase 9 (departments API)
Phase 4 ──► independent after Phase 0
Phase 5 ──► independent after Phase 0
```

---

## Conventions (follow existing codebase)

- Models: legacy PK names (`payment_id`, `follow_up_id`, `department_id`)
- Controllers → `Inertia::render` + API Resources where established
- Form Requests for all store/update actions
- Activity logging via `ActivityLogger` for clinical/financial mutations
- Sidebar updates in `AuthenticatedLayout.jsx`
- Void pattern for deletions where audit trail required (invoices, prescriptions, vitals)
