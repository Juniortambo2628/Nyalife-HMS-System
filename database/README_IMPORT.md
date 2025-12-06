phpMyAdmin Import Instructions

1) Create database (utf8mb4, collation utf8mb4_unicode_ci).
2) Import `database/schema/nyalife_hms_clean.sql`.
3) (Optional) Create an admin user manually if needed via phpMyAdmin or application UI.
4) Update `env.production` with DB credentials and upload to server.

