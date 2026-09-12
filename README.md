# VR Junior College — PHP CMS

Production custom CMS (pure PHP 8+, MySQL, no Laravel) for [https://vrjuniorcollege.com/](https://vrjuniorcollege.com/). The approved frontend design is unchanged; every public URL is clean, extension-less, and routed through `public/index.php`.

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `json`, `openssl`, `curl`, `zip`
- MySQL 8+ / MariaDB 10.5+
- Apache with `mod_rewrite` (cPanel-friendly) or `php -S` for local

## Install

1. Point the domain document root at `public/` (or keep the project root and use the root `.htaccess` that forwards into `public/`).
2. Create an empty MySQL database, then edit:
   - `config/database.php` — host, name, user, password
   - `config/app.php` — `url` must be `https://vrjuniorcollege.com` in production, plus a long random `key` and `cron_key`
3. Run **either**:
   ```bash
   php database/install.php
   ```
   **or** visit `/install/` once (delete `storage/installed.lock` only if you intend to reinstall).
4. Sign in at `/admin/login/`
   - Email: `admin@vrjuniorcollege.com`
   - Password: `ChangeMe_VRJ2026` — change this immediately
5. Optional: `ln -sfn ../assets public/assets` if the symlink is missing after deploy.

## Cron

Hit once an hour (scheduled publish, backups, cleanup):

```
https://vrjuniorcollege.com/cron/{CRON_KEY}/
```

or `php cron.php` from the project root.

## Google Sheets

1. Enable the Sheets API on a Google Cloud project.
2. Create a service account, download the JSON key.
3. Share the target spreadsheet with the service account `client_email`.
4. Upload the JSON in **Settings → Google Sheets**.
5. On each form, toggle **Sync to Google Sheet** and paste the spreadsheet ID + tab name.

Local submissions are always stored even if Sheets is down.

## Documentation

- `docs/SETUP.md` — hosting, mail, cron, BASE_URL
- `docs/CPT.md` — how staff create a new Custom Post Type (AI Program walkthrough)
- `docs/URLS.md` — slug rules, reserved routes, 301s for indexed WordPress URLs

## Local preview

```bash
APP_URL=http://127.0.0.1:8080 php database/install.php
APP_URL=http://127.0.0.1:8080 php -S 127.0.0.1:8080 -t public public/router.php
```
