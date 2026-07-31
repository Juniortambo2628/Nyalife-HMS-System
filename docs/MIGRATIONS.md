# Production migration runbook

This repository deploys to cPanel shared hosting via FTP only — see
`.github/workflows/deploy.yml`. There is no `php artisan migrate` step in
the workflow because the CI runner cannot execute PHP against the
production database. **Schema changes shipped in a deploy are not active
on production until an operator runs the migration manually.**

## When a migration ships

Any change under `database/migrations/` requires this manual step after
the FTP deploy finishes:

1. Open **cPanel → Terminal** (or SSH into the host if your account has
   shell access enabled).
2. `cd /home1/nyalifew/nyalife_core`
3. `php artisan migrate --force`
4. Confirm the output lists the new migration(s) and ends with
   `Migrated:` or `No migrations found` if everything was already current.

## Why this matters

The deploy workflow uploads the migration file but never executes it.
Without this step:

- New tables / columns added by a deploy don't exist on production.
- Column-shape changes (widening, renames, type conversions) are
  committed to the codebase but never applied to the live schema.
- The application code can run against a schema that's older than what
  the tests assume.

In particular, after the 2026-07-31 deploy (`refactor(messages)...`),
the `consultations.parity` column migration ships but does not run.
The controller-level guard (`mb_substr($parity, 0, 50)` in
`ConsultationController::store`) prevents silent data loss in the
meantime, but the column still needs the `ALTER TABLE` to commit.

## Future work

A guarded HTTP-runnable migrator would be the right long-term fix:

1. Add a `MIGRATE_TOKEN` GitHub secret.
2. Upload a small `public_html/artisan-migrate.php` that:
   - checks the token,
   - loads the deployed `.env`,
   - calls `Artisan::call('migrate', ['--force' => true])`,
   - prints the output.
3. Add a `curl` step at the end of `deploy.yml` that hits
   `https://nyalifewomensclinic.net/artisan-migrate.php?key=$MIGRATE_TOKEN`.

That removes the manual step entirely. Until that's in place, run the
manual `php artisan migrate --force` after every deploy that touches
`database/migrations/`.
