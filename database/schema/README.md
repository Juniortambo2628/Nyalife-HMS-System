# Database schema baseline

Nyalife HMS core clinical tables (`patients`, `appointments`, `lab_test_requests`, etc.) pre-date Laravel and were never created by checked-in migrations. Phase 4 adds a bootstrap path for **fresh installs**.

## Fresh install

```bash
php artisan migrate
php artisan db:seed --class=TestUsersSeeder
php artisan db:seed --class=SyncSpatieRolesSeeder
php artisan db:seed --class=RolePermissionsSeeder
# optional demo data:
php artisan db:seed --class=HospitalSeeder
```

### What runs

| Step | Migration / script | Purpose |
|------|-------------------|---------|
| 1 | `0001_01_01_*` | Laravel `users`, sessions, cache, jobs |
| 2 | **`2026_01_01_000000_bootstrap_legacy_core_schema`** | Creates 18 core HMS tables **only if `patients` does not exist** |
| 3 | `2026_01_23` … `2026_05_26` | Idempotent ALTER migrations (guarded where needed) |
| 4 | `2026_05_26_000002` | Drops **empty** orphan legacy tables |
| 5 | `2026_05_26_000004` | Drops **empty** populated legacy tables (see below) |

## Existing production / restored dumps

Bootstrap **skips automatically** when `patients` already exists. Run `php artisan migrate` as usual — only new ALTER migrations apply.

## Legacy table cleanup

### Empty-only auto-drop (migrations)

Phase 1 (`000002`) and Phase 4 (`000004`) drop legacy tables **only when row count = 0**.

### Populated legacy tables (manual review)

| Table | App usage | Before drop |
|-------|-----------|-------------|
| `audit_logs` | None | `php scripts/export-legacy-tables.php audit_logs` |
| `medication_categories` | Model removed | Export, then truncate or drop manually |
| `services`, `specializations` | Superseded by `service_tabs` / `staff.specialization` | Export if needed |
| `email_queue`, `user_tokens`, `lab_test_*` (old) | None | Export if needed |
| `phinxlog` | Phinx migrator remnant | Safe after export |

```bash
# Export all legacy candidates to storage/legacy-exports/{timestamp}/
php scripts/export-legacy-tables.php

# Audit row counts
php scripts/audit-legacy-tables.php
```

To drop a populated table after export, run SQL manually or truncate then re-run migrate (000004 only drops empty tables).

## Regenerating SQL from production

If you need a portable DDL snapshot:

```bash
mysqldump -u root -p --no-data nyalifew_hms_prod \
  roles patients staff departments appointments consultations \
  prescriptions prescription_items invoices invoice_items payments follow_ups \
  vital_signs lab_test_types lab_test_requests lab_samples \
  medications medication_batches > database/schema/legacy-core.sql
```

Commit `legacy-core.sql` only after reviewing against `2026_01_01_000000_bootstrap_legacy_core_schema.php`.

## Do not

- Squash or delete applied migrations on production
- Drop tables with rows > 0 without export
- Remove legacy `roles` / `users.role_id` until Spatie-only auth is complete
