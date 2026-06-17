# Nyalife HMS — Codebase Cleanup & DRY Unification Plan

**Goal:** One source of truth per concern, no duplicate implementations, smaller git footprint, schema aligned with models.

**Status:** Phase 1 executed (see git diff). Phases 2–4 are planned; do not skip testing between phases.

---

## Executive summary

| Area | Problem | SSOT target | Phase |
|------|---------|-------------|-------|
| CSS | 20+ legacy files + duplicate sidebar variants | `resources/css/nyalife-core.css` via Vite | **1 done** |
| Build output | `public/build` committed → massive git churn | Source in `resources/js`; build on deploy | **1 done** |
| Components | 6 unused Laravel Breeze components | Dashboard design system components | **1 done** |
| Profile | Dead `UpdateProfileInformationForm.jsx` | `UpdatePersonalInformationForm.jsx` | **1 done** |
| Lab validation | `UpdateLabRequestStatusRequest` unused | Wired in `LabController::updateStatus` | **1 done** |
| Models | `MedicationCategory` — zero references | Removed (table kept; see DB phase) | **1 done** |
| Formatting | 30+ inline date/currency formatters | `Utils/dateUtils.js`, `Utils/formatUtils.js` | 2 |
| Lab UI | Two catalog pages/routes | `Lab/TestsCatalog.jsx` + single manage route | 2 |
| Auth | `$user->role` vs Spatie `Permissions::*` vs trait | `Permissions.php` + middleware + `AuthorizesClinicalAccess` | 3 |
| API/Web | Duplicate query logic in 4 controller pairs | Model scopes / query services | 3 |
| Database | Legacy tables pre-exist; 53 ALTER-only migrations | Baseline schema migration + idempotent ensures | **1 partial** |
| DB legacy | 22 orphan tables from pre-Laravel app | Drop empty (migration); archive populated (manual) | **1 partial** |

---

## Phase 1 — Safe cleanup (no behavior change) ✅

### Frontend archived → `deprecated/` (gitignored)

- `resources/css/legacy/` (20 files)
- `resources/css/sidebar-legacy.css`, `nyalife-sidebar.css`
- `public/assets/css/` (11 files — not referenced; images under `public/assets/img` kept)
- Breeze components: `ApplicationLogo`, `NavLink`, `ResponsiveNavLink`, `Dropdown`, `Checkbox`, `TextArea`
- `Pages/Profile/Partials/UpdateProfileInformationForm.jsx`
- `app/Models/MedicationCategory.php`

### Git

- `/public/build` re-enabled for shared hosting (no npm on production)
- `/deprecated/` gitignored — local archive only
- Run once: `git rm -r --cached public/build` to stop tracking build artifacts

### Backend

- `LabController::updateStatus` uses `UpdateLabRequestStatusRequest`
- `scripts/audit-legacy-tables.php` — row-count audit for legacy tables

### Database migrations added

- `2026_05_26_000001_ensure_legacy_schema_columns.php` — idempotent column ensures for prod + fresh installs
- `2026_05_26_000003_fix_invoice_doctor_foreign_key.php` — drops `invoices.fk_invoice_doctor` + empty `doctors` table

**Applied on local DB:** 14 empty legacy tables removed; `phinxlog` kept (1 row); populated tables listed in Phase 4 below.

---

### Phase 2 — DRY frontend ✅

1. **Formatting helpers** — All pages migrated to `formatUtils` / `dateUtils` (including `formatDateLong`, `formatTime`).
2. **Lab catalog unification** — `lab.tests` (TestsCatalog) is SSOT; `lab-tests.index` and `lab.manage` redirect; `Lab/Tests/Index.jsx` archived.
3. **TestsCatalog** — Removed duplicate inline `.shadow-hover` rule; uses `formatNumber` for prices.
4. **Dashboard fallback** — Uses `RoleDashboardShell` + `QuickActionCard`.
5. **MedicalProcedures** — Removed local `formatCurrency` duplicate.

---

## Phase 3 — DRY backend & auth (medium risk) ✅

1. **Permissions single path** ✅
   - Form Requests use `$this->user()?->can(Permissions::…)` instead of `$user->role`
   - `Api/AppointmentController` uses `AppointmentQueryService` + permission-based scoping
   - `requireStaffOrOwnPatient()` on Prescription, Invoice, Lab, Radiology show/print

2. **Query services** ✅
   - `Payment::scopeFilteredQuery(Request $request)`
   - `FollowUp::scopeFilteredQuery(Request $request)`
   - `Department::scopeFilteredQuery(Request $request)`
   - `AppointmentQueryService` for web + API appointment lists

3. **Route hygiene** ✅
   - `/api/insurances` and `/api/context-switching` moved from `web.php` to `api.php`

4. **Inline validation → Form Requests** — deferred (LabTestRequest, Insurance, Radiology, Pharmacy)

5. **Long-term:** deprecate legacy `roles` table + `users.role_id` after full Spatie migration

---

## Phase 4 — Database deep clean (high risk — backup first) ✅ partial

### Baseline for fresh installs ✅

- **`2026_01_01_000000_bootstrap_legacy_core_schema.php`** — creates 18 core HMS tables when `patients` is missing
- **`database/schema/README.md`** — fresh-install runbook
- ALTER migrations hardened with `hasTable` / `hasColumn` / enum-skip guards for idempotent fresh installs

### Legacy table cleanup ✅

- **`2026_05_26_000004_drop_populated_legacy_tables.php`** — drops legacy tables with **zero rows** only
- **`scripts/export-legacy-tables.php`** — CSV export to `storage/legacy-exports/` before manual drops

### Populated legacy tables — still manual

Run export first, then drop/truncate manually if needed:

| Table | Rows (prod audit) | Notes |
|-------|-------------------|-------|
| `audit_logs` | 255 | Export CSV, no app references |
| `medication_categories` | 12 | Model removed |
| `services` | 7 | Superseded by `service_tabs` |
| `specializations` | 8 | Superseded by `staff.specialization` |
| `email_queue` | 3 | Verify no cron uses it |
| `lab_test_items/parameters` | 1–5 | Old lab schema |
| `user_tokens` | 1 | Legacy auth |
| `phinxlog` | 1 | Safe after export |

### Fresh install commands

```bash
php artisan migrate
php artisan db:seed --class=TestUsersSeeder
php artisan db:seed --class=SyncSpatieRolesSeeder
php artisan db:seed --class=RolePermissionsSeeder
```

---

## Verification checklist

After each phase:

```bash
php artisan migrate
php artisan route:list --columns=method,uri,name
npm run build
php artisan test   # if tests exist
```

Manual smoke: login (each role), appointment show, patient show, lab request show, profile save, payment record, consultation create.

---

## Commands reference

```bash
# Audit legacy table row counts
php scripts/audit-legacy-tables.php

# Stop tracking build output (once)
git rm -r --cached public/build

# Deploy build (required after clone if build not committed)
npm ci && npm run build
```

---

## What NOT to change without explicit approval

- Dropping `audit_logs`, `email_queue`, or any table with rows > 0 without export
- Merging `AuthLayout` and `GuestLayout` (different UX contracts)
- Removing legacy `roles` / `users.role_id` (auth still dual-path)
- Squashing applied migrations on production DB
