# Nyalife HMS — REST API (v1)

Base URL: `/api/v1` (authenticated routes require a logged-in session cookie or Sanctum token).

Public endpoints are under `/api/appointments/...`.

## Authentication

Authenticated routes accept either:

1. **Session cookie** — same-origin browser requests (SPA)
2. **Bearer token** — `Authorization: Bearer {token}` from [Admin → API Tokens](/admin/api-tokens)

Create tokens at `/admin/api-tokens` (requires `manage-system`). Token abilities mirror your permissions at creation time.

## Public Endpoints

### Available appointment slots

`GET /api/appointments/available-slots`

Query parameters:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `date` | date (Y-m-d) | Yes | Appointment date (today or later) |
| `doctor_id` | integer | No | Staff ID; defaults to first active doctor |

Response:

```json
{
  "doctor_id": 1,
  "date": "2026-05-26",
  "data": [
    { "time": "08:00", "label": "8:00 AM", "available": true },
    { "time": "08:30", "label": "8:30 AM", "available": true }
  ]
}
```

Slots are 30-minute intervals from 08:00–17:00, excluding times already booked (non-cancelled appointments).

## Authenticated Endpoints

All require authentication via Sanctum (session or bearer token) and the permission noted.

### Appointments — `manage-appointments` or `view-own-records`

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/appointments` | Paginated list (`status`, `date`, `per_page`) |

### Departments — `view-departments`

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/departments` | Paginated list (`search`, `type`, `active_only`, `per_page`) |
| GET | `/api/v1/departments/{id}` | Single department |

### Payments — `manage-payments`

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/payments` | Paginated list (`search`, `status`, `method`, `invoice_id`, date filters) |
| GET | `/api/v1/payments/{id}` | Single payment with invoice |

### Follow-ups — `manage-follow-ups`

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/follow-ups` | Paginated list |
| GET | `/api/v1/follow-ups/upcoming` | Upcoming follow-ups (`limit`, `patient_id`) |
| GET | `/api/v1/follow-ups/{id}` | Single follow-up |

## Permissions Setup

After migration, seed roles and permissions:

```bash
php artisan db:seed --class=SyncSpatieRolesSeeder
php artisan db:seed --class=RolePermissionsSeeder
```

Permission names are defined in `app/Support/Permissions.php`.

## Web Route Middleware

These web modules also enforce Spatie permissions via route middleware:

- `/payments/*` → `manage-payments`
- `/follow-ups/*` → `manage-follow-ups`
- `/departments/*` → `manage-departments`
- `/reports/*` → `view-reports`
- `/admin/settings` → `manage-settings`

Frontend menu items are filtered using `auth.permissions` shared via Inertia.
