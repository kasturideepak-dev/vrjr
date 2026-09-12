# Production setup

## 1. Files

Upload the project. Set the domain document root to `public/`. If cPanel can only point at the project folder, the root `.htaccess` forwards into `public/`.

## 2. Config

Edit `config/app.php`:

- `url` → `https://vrjuniorcollege.com` (no trailing slash). This is `BASE_URL`. Canonical tags, the sitemap, OG URLs, and reset emails all use it.
- `key` → a long random string (used for app secrets).
- `cron_key` → a long random string for the cron URL.
- `debug` → `0` in production (`APP_DEBUG=0`).
- `env` → `production`.

Edit `config/database.php` with the MySQL user created in cPanel.

## 3. Database

```bash
php database/install.php
```

Or open `https://vrjuniorcollege.com/install.php` once. A lock file is written to `storage/installed.lock`.

Default super admin:

- Email: `admin@vrjuniorcollege.com`
- Password: `ChangeMe_VRJ2026`

Change the password immediately. Enable email OTP on the user if you want 2FA.

## 4. Permissions

`storage/`, `public/uploads/`, and `storage/secrets/` must be writable by PHP (0755/0775). `config/`, `app/`, `database/`, `views/`, and `storage/secrets/` are blocked from HTTP via `.htaccess`. PHP is disabled inside `public/uploads/`.

## 5. HTTPS, cache, gzip

`public/.htaccess` forces HTTPS (except localhost), enables gzip, and sets cache headers for assets.

## 6. Cron

Every 15–60 minutes:

```
wget -q -O - https://vrjuniorcollege.com/cron/YOUR_CRON_KEY/
```

This publishes scheduled content, runs automatic backups if enabled, and purges expired tokens.

## 7. Mail

Settings → SMTP. For Gmail, use an App Password on port 587 / TLS. Form notifications and password resets use this.

## 8. Google Sheets

See README. Failures never drop the local lead; they are flagged on the submission row.

## 9. After launch

1. Change the admin password.
2. Confirm GTM `GTM-M7W444LT` in Settings.
3. Open **Redirects** and confirm the seeded WordPress URLs.
4. Request indexing of `/sitemap.xml` in Search Console.
5. Take a manual backup from **Backups**.
