# Deployment guide: GitHub → Webspace

This repository is prepared for two deployment modes via GitHub Actions:

- SSH deployment (recommended): Upload files via SCP and run Composer/Artisan on the server
- FTP/SFTP deployment (fallback): Build vendor in CI and upload files to shared hosting

Both workflows trigger on push to `main` and can be triggered manually.

## 1) Prerequisites

- PHP 8.2 on server with required extensions (mbstring, intl, gd, zip, pdo_mysql)
- MySQL 8.x (or compatible)
- Redis server (recommended) if using CACHE_STORE=redis (Predis client is configured)
- Composer installed on server (for SSH mode), or use SFTP mode which uploads `vendor/`
- Document root points to `public/` (configure in hosting control panel or via subdomain)

## 2) Create repository and connect

1. Create a new GitHub repository (private or public)
2. Locally: add remote and push

```
# Run in project root
# PowerShell

git remote add origin https://github.com/<your-org>/<your-repo>.git
git branch -M main
git push -u origin main
```

## 3) Configure environment on server

- Copy `.env.production.example` to server as `.env` and set real values (APP_KEY, DB_*, MAIL_*, etc.)
- On first deploy (SSH mode), run `php artisan key:generate --force` on server
- Ensure `storage/` and `bootstrap/cache/` are writable by the web server user
- Point the web root to `public/`

## 4) SSH deployment (recommended)

Workflow file: `.github/workflows/deploy-ssh.yml`

Set the following GitHub Secrets (Settings → Secrets and variables → Actions):

- `SSH_HOST` – your server host (e.g. ssh.your-host.tld)
- `SSH_PORT` – optional, default `22`
- `SSH_USERNAME` – SSH user
- `SSH_PRIVATE_KEY` – contents of your private key (PEM)
- `SSH_TARGET_DIR` – absolute directory on server (e.g. `/var/www/your-app`)

What it does:
- Uploads repository files (excludes vendor, node_modules, logs, tests, .git)
- Runs on server:
  - `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader`
  - `php artisan storage:link`
  - `php artisan migrate --force`
  - `php artisan optimize` and caches

Note: If Composer isn’t available on the server, either install it or use SFTP deployment.

## 5) FTP/SFTP deployment (shared hosting)

Workflow file: `.github/workflows/deploy-sftp.yml`

Set the following GitHub Secrets:

- `FTP_SERVER` – hostname
- `FTP_PORT` – optional, default `21` (or use `22` for SFTP)
- `FTP_PROTOCOL` – `ftp`, `ftps`, or `sftp` (default `ftps`)
- `FTP_USERNAME` – FTP/SFTP username
- `FTP_PASSWORD` – FTP password (or use `FTP_SSH_KEY` for SFTP key)
- `FTP_SERVER_DIR` – target directory (document root should be `public/`)

What it does:
- Installs Composer dependencies in CI (so `vendor/` is included)
- Optionally builds frontend assets if a lockfile is detected
- Uploads the project with excludes for dev-only directories

Notes:
- Ensure your `.env` is already present on the server (the workflow does not upload secrets).
- Make sure your host’s document root points to `public/`; adjust `FTP_SERVER_DIR` accordingly.

## 6) Post-deploy checklist

- Run one-time setup if needed:
  - `php artisan key:generate --force` (SSH mode)
  - `php artisan storage:link` (SSH mode; for SFTP mode, the `public/storage` symlink might need to be created via SSH panel or host support)
- Verify app URL and SSL (APP_URL in `.env`)
- Verify queue workers and schedulers (if applicable)

## 7) Redis note

This project is configured to use Predis. Ensure a Redis-compatible server is reachable at `127.0.0.1:6379` or change `.env`:

- Temporarily set `CACHE_STORE=array` for shared hosting without Redis
- Or set `REDIS_HOST`, `REDIS_PORT`, and keep `REDIS_CLIENT=predis`

## 8) Troubleshooting

- 500 error after deploy: clear caches on server (SSH):
  - `php artisan optimize:clear`
- Wrong document root: ensure your hosting points to `public/`
- Missing extensions: check `php -m` on server and enable required extensions
- No SSH: use SFTP workflow; confirm that `vendor/` was uploaded and `public/build` assets exist

Happy shipping!
