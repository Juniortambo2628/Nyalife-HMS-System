# Auth Hardening Plan — Spatie + Sanctum

**Goal:** Extend permission enforcement to all core HMS modules and enable bearer-token API access for external clients.

---

## Phase A — Expanded Spatie permissions

| Permission | Scope |
|------------|--------|
| `manage-patients` | Patient registry CRUD (staff) |
| `view-own-records` | Patient portal — own appointments, consultations, etc. |
| `manage-appointments` | Appointment scheduling (staff) |
| `manage-consultations` | Consultation workflow (staff write) |
| `manage-prescriptions` | Prescriptions (staff) |
| `manage-invoices` | Billing (staff) |
| `manage-lab` | Lab requests, results, samples |
| `manage-lab-catalog` | Lab test type CRUD (admin) |
| `manage-pharmacy` | Inventory, medicines, POs |
| `manage-vitals` | Vitals recording |
| `manage-users` | User administration |
| `manage-system` | CMS, blogs, insurance, mail templates, contact messages, procedures, API tokens |
| `send-messages` | Internal staff messaging |
| *(existing)* | payments, follow-ups, departments, reports, settings |

Shared routes (staff **or** patient) use middleware:  
`role_or_permission:manage-{module}|view-own-records`

Mutating actions (create/edit/delete) additionally call `requirePermission()` in controllers.

---

## Phase B — Route middleware

All authenticated routes in `routes/web.php` grouped by permission. Patient portal routes accept `view-own-records`.

---

## Phase C — Controller ownership checks

`AuthorizesClinicalAccess` trait on base `Controller`:

- `requireStaffOrOwnPatient($patientId, ...$staffPermissions)` — used in show/print actions
- Keeps existing data-scoping in index methods

---

## Phase D — Frontend gating

`AuthenticatedLayout` sidebar items tagged with `permission` keys; filtered via shared `auth.permissions`.

---

## Phase E — Sanctum API tokens

1. Publish Sanctum migration + config
2. `HasApiTokens` on `User` model
3. Admin UI at `/admin/api-tokens` to create/revoke tokens
4. API routes use `auth:sanctum` (session cookie **or** `Authorization: Bearer {token}`)
5. Token abilities mirror the user's Spatie permissions at creation time

---

## Phase F — API expansion

| Endpoint | Permission |
|----------|------------|
| `GET /api/v1/appointments` | `manage-appointments` |
| *(existing v1 routes)* | unchanged |

---

## Deployment

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionsSeeder
```

Re-run the permissions seeder after any permission list change.

---

## Status

- [x] Phase A — Permission definitions + seeder
- [x] Phase B — Route middleware
- [x] Phase C — Clinical access trait
- [x] Phase D — Sidebar permission gating
- [x] Phase E — Sanctum tokens + admin UI
- [x] Phase F — Appointments API endpoint
