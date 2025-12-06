Nyalife HMS - Production Deployment (cPanel/Shared Hosting)

1) Prepare env.production
   - Edit `env.production` with your server DB credentials and domain.
   - Keep `APP_ENV=production` and `APP_DEBUG=false`.

2) Upload Files
   - Upload entire project folder contents to your cPanel document root (e.g., `public_html/nyalife/`).
   - Ensure `.htaccess` is present in the upload directory (for routing).

3) File/Folder Permissions
   - Folders: 755 (uploads, logs, reports)
   - Files: 644
   - The app auto-creates required folders; if not, create and set 755:
     uploads/, uploads/patients/, uploads/documents/, uploads/temp/, storage/logs/, reports/

4) Database
   - In phpMyAdmin, create the database and user.
   - Import `database/schema/nyalife_hms_clean.sql` (provided).

5) Configuration Loading
   - The app auto-detects environment and loads `.env` if present, else `env.production`.
   - Composer is optional on cPanel; autoload gracefully skips vendor if missing.

6) Common Issues
   - White screen: set `APP_DEBUG=true` temporarily in `env.production` to inspect errors, then revert.
   - 404 routing: confirm `.htaccess` uploaded and `AllowOverride All` enabled, or use cPanel “Optimize Website” to enable compression.


