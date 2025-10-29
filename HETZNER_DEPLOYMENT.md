# Hetzner Server Deployment - Schritt für Schritt

## Server-Zugangsdaten

```
IPv4:     46.224.7.207
IPv6:     2a01:4f8:c17:89e3::/64
User:     root
Password: NKsnEqcrjVCPMcxE34cN
```

## 1) Erstes Login & Basis-Setup

Von deinem lokalen Rechner (PowerShell):

```powershell
ssh root@46.224.7.207
# Passwort: NKsnEqcrjVCPMcxE34cN
```

**Auf dem Server** alle Befehle als root ausführen:

```bash
# System aktualisieren
apt update && apt -y upgrade

# Basis-Tools
apt -y install git unzip curl ufw

# PHP 8.2 + Extensions
apt -y install php8.2 php8.2-fpm php8.2-cli php8.2-mbstring php8.2-intl php8.2-zip php8.2-gd php8.2-curl php8.2-xml php8.2-mysql

# Webserver, DB, Redis
apt -y install nginx mariadb-server redis-server

# Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Services starten
systemctl enable php8.2-fpm nginx mariadb redis-server
systemctl start php8.2-fpm nginx mariadb redis-server

# Firewall (zusätzlich zu Hetzner Firewall)
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
```

## 2) Datenbank einrichten

```bash
mysql -uroot
```

In MySQL:
```sql
CREATE DATABASE kpkb3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kpkb3_user'@'localhost' IDENTIFIED BY 'VeryStr0ngP@ssw0rd!2025';
GRANT ALL PRIVILEGES ON kpkb3.* TO 'kpkb3_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 3) App-Verzeichnis + Nginx

```bash
# Verzeichnis anlegen
mkdir -p /var/www/kpkb3
chown -R www-data:www-data /var/www/kpkb3

# Nginx vHost erstellen
nano /etc/nginx/sites-available/kpkb3
```

**Inhalt** (Domain später anpassen):
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 46.224.7.207 example.com *.example.com;

    root /var/www/kpkb3/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
    }
}
```

Aktivieren:
```bash
ln -s /etc/nginx/sites-available/kpkb3 /etc/nginx/sites-enabled/kpkb3
nginx -t && systemctl reload nginx
```

## 4) Code manuell deployen (erste Installation)

```bash
cd /var/www/kpkb3

# Repository clonen (privat? GitHub Token nutzen)
git clone https://github.com/Klubportal/kpkb3.git .

# Dependencies installieren
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# .env erstellen
nano .env
```

**.env Inhalt** (anpassen!):
```
APP_NAME="Klubportal"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://46.224.7.207

APP_LOCALE=de
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=central
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kpkb3
DB_USERNAME=kpkb3_user
DB_PASSWORD=VeryStr0ngP@ssw0rd!2025

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

SANCTUM_STATEFUL_DOMAINS=46.224.7.207
VITE_APP_NAME="${APP_NAME}"
```

**Laravel initialisieren**:
```bash
php artisan key:generate --force
php artisan storage:link
php artisan migrate --force
php create-tenant-databases.php
php artisan tenants:migrate --force
php artisan optimize

# Assets bauen (falls Node.js installiert)
apt -y install nodejs npm
npm ci --omit=dev
npm run build
```

**Rechte setzen**:
```bash
chown -R www-data:www-data /var/www/kpkb3
find /var/www/kpkb3 -type f -exec chmod 644 {} \;
find /var/www/kpkb3 -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/kpkb3/storage /var/www/kpkb3/bootstrap/cache
```

## 5) Testen

Browser öffnen: http://46.224.7.207

Logs checken:
```bash
tail -f /var/www/kpkb3/storage/logs/laravel.log
```

Bei 500er:
```bash
php artisan optimize:clear
systemctl restart php8.2-fpm nginx
```

## 6) SSL + Domain (sobald DNS zeigt)

DNS:
- A-Record: @ → 46.224.7.207
- A-Record: www → 46.224.7.207
- A-Record: * → 46.224.7.207

SSL:
```bash
apt -y install certbot python3-certbot-nginx
certbot --nginx -d example.com -d www.example.com

# Für Wildcard (*.example.com):
certbot certonly --manual --preferred-challenges=dns -d example.com -d *.example.com
# Dann DNS TXT-Record setzen wie angezeigt
```

Nginx anpassen:
```bash
nano /etc/nginx/sites-available/kpkb3
# server_name auf echte Domain ändern: example.com *.example.com
nginx -t && systemctl reload nginx
```

.env anpassen:
```bash
nano /var/www/kpkb3/.env
# APP_URL=https://example.com
# SESSION_SECURE_COOKIE=true
# SANCTUM_STATEFUL_DOMAINS=example.com
php artisan config:clear
```

## 7) Background Jobs

**Scheduler** (Laravel Cron):
```bash
crontab -u www-data -e
```
Zeile einfügen:
```
* * * * * php /var/www/kpkb3/artisan schedule:run >> /var/log/laravel-scheduler.log 2>&1
```

**Queue Worker** (Systemd):
```bash
nano /etc/systemd/system/kpkb3-queue.service
```
Inhalt:
```ini
[Unit]
Description=Laravel Queue Worker (kpkb3)
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/kpkb3
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Aktivieren:
```bash
systemctl daemon-reload
systemctl enable kpkb3-queue
systemctl start kpkb3-queue
systemctl status kpkb3-queue
```

## 8) GitHub Actions (automatisches Deployment)

GitHub-Repo → Settings → Secrets and variables → Actions → New secret:

- **SSH_HOST**: `46.224.7.207`
- **SSH_PORT**: `22`
- **SSH_USERNAME**: `root`
- **SSH_PRIVATE_KEY**: (SSH-Key generieren und hier einfügen, oder Passwort-basiert ist unsicher)
- **SSH_TARGET_DIR**: `/var/www/kpkb3`

Workflow ist bereits da: `.github/workflows/deploy-ssh.yml`

Push auf `main` triggert automatisch:
- Upload via SCP
- `composer install`
- `php artisan migrate --force`
- `php artisan optimize`

## 9) SSH absichern (empfohlen)

Deploy-User anlegen:
```bash
adduser deploy
usermod -aG sudo deploy
mkdir -p /home/deploy/.ssh
nano /home/deploy/.ssh/authorized_keys
# Public Key einfügen
chown -R deploy:deploy /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
chmod 600 /home/deploy/.ssh/authorized_keys
```

SSH härten:
```bash
nano /etc/ssh/sshd_config
```
Ändern:
```
PermitRootLogin no
PasswordAuthentication no
```
```bash
systemctl restart ssh
```

Ab dann einloggen als:
```powershell
ssh deploy@46.224.7.207
```

## 10) Backup Script (optional)

```bash
nano /usr/local/bin/backup-kpkb3.sh
```
Inhalt:
```bash
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/kpkb3"
mkdir -p $BACKUP_DIR

mysqldump -u kpkb3_user -p'VeryStr0ngP@ssw0rd!2025' kpkb3 | gzip > $BACKUP_DIR/db_$TIMESTAMP.sql.gz
tar -czf $BACKUP_DIR/files_$TIMESTAMP.tar.gz /var/www/kpkb3/storage/app

find $BACKUP_DIR -type f -mtime +30 -delete
echo "Backup completed: $TIMESTAMP"
```

Ausführbar machen:
```bash
chmod +x /usr/local/bin/backup-kpkb3.sh
```

Cron (täglich 2 Uhr):
```bash
crontab -e
```
```
0 2 * * * /usr/local/bin/backup-kpkb3.sh >> /var/log/backup-kpkb3.log 2>&1
```

---

## Quick Commands Reference

```bash
# Logs
tail -f /var/www/kpkb3/storage/logs/laravel.log

# Cache leeren
php artisan optimize:clear

# Services neu starten
systemctl restart php8.2-fpm nginx

# Queue Status
systemctl status kpkb3-queue

# Migrations
php artisan migrate --force
php artisan tenants:migrate --force

# Code Update (manuell)
cd /var/www/kpkb3
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci --omit=dev && npm run build
php artisan migrate --force
php artisan optimize
systemctl restart kpkb3-queue
```

---

**Fertig!** 🚀 App läuft auf http://46.224.7.207 (später https://deine-domain.com)
