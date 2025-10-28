# DEPLOYMENT CHECKLIST - Klubportal Production
================================================

## 1. ENVIRONMENT CONFIGURATION

### .env Production Settings
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://klubportal.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=klubportal_landlord
DB_USERNAME=klubportal_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Tenant Database
TENANCY_DATABASE_AUTO_CREATE=true
TENANCY_DATABASE_AUTO_DELETE=false

# Cache & Session (Redis empfohlen)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Comet API
COMET_API_BASE_URL=https://api-hns.analyticom.de
COMET_API_USERNAME=your-username
COMET_API_PASSWORD=your-password
```

## 2. OPTIMIZATION COMMANDS

```bash
# Production Optimierungen
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# Assets kompilieren
npm ci --production
npm run build
```

## 3. FILE PERMISSIONS (Linux/Ubuntu)

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/klubportal
sudo chown -R www-data:www-data /var/www/klubportal/storage
sudo chown -R www-data:www-data /var/www/klubportal/bootstrap/cache

# Set permissions
sudo find /var/www/klubportal -type f -exec chmod 644 {} \;
sudo find /var/www/klubportal -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/klubportal/storage
sudo chmod -R 775 /var/www/klubportal/bootstrap/cache
```

## 4. SECURITY

- [ ] APP_KEY generieren: `php artisan key:generate`
- [ ] .env aus Git ausschließen (bereits in .gitignore)
- [ ] Starke Datenbank Passwörter verwenden
- [ ] SSL Zertifikat installieren (Let's Encrypt)
- [ ] HTTPS erzwingen in .htaccess
- [ ] Rate Limiting aktivieren
- [ ] CORS richtig konfigurieren

## 5. CRON JOBS (für Laravel Scheduler)

```bash
# Füge in crontab hinzu:
* * * * * cd /var/www/klubportal && php artisan schedule:run >> /dev/null 2>&1
```

## 6. SUPERVISOR (für Queue Workers)

```bash
sudo apt install supervisor

# /etc/supervisor/conf.d/klubportal-worker.conf
[program:klubportal-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/klubportal/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/klubportal/storage/logs/worker.log
stopwaitsecs=3600

# Supervisor neu laden
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start klubportal-worker:*
```

## 7. WICHTIGE FILES FÜR DEPLOYMENT

- [ ] composer.json & composer.lock
- [ ] package.json & package-lock.json
- [ ] .env.example (mit production settings)
- [ ] database/migrations/*
- [ ] public/.htaccess
- [ ] storage/ Verzeichnis (mit korrekten Permissions)
- [ ] bootstrap/cache/ (beschreibbar)

## 8. DEPLOYMENT SCRIPT

Erstelle deploy.sh für automatisches Deployment:
```bash
#!/bin/bash
cd /var/www/klubportal
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
sudo supervisorctl restart klubportal-worker:*
```

## 9. MONITORING & LOGS

```bash
# Log Rotation
sudo nano /etc/logrotate.d/klubportal

/var/www/klubportal/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}

# Monitoring Tools
- Laravel Telescope (bereits installiert - nur für staging!)
- Laravel Horizon (für Redis Queues)
- Sentry.io (Error Tracking)
```

## 10. BACKUP AUTOMATION

```bash
# Daily Backup Script
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=/var/backups/klubportal/$TIMESTAMP

# Database Backup
mysqldump -u user -ppassword klubportal_landlord > $BACKUP_DIR/central.sql
mysqldump -u user -ppassword tenant_nkprigorjem > $BACKUP_DIR/tenant.sql

# Files Backup
tar -czf $BACKUP_DIR/files.tar.gz /var/www/klubportal/storage/app

# Remove old backups (keep last 30 days)
find /var/backups/klubportal -type d -mtime +30 -exec rm -rf {} +
```

## 11. PERFORMANCE TUNING

### PHP-FPM (php.ini)
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
```

### MySQL (my.cnf)
```ini
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
max_connections = 200
query_cache_size = 0
query_cache_type = 0
```

## 12. TESTING

- [ ] Alle URLs testen (Haupt-Domain + Subdomains)
- [ ] Login testen
- [ ] Tenant-Switching testen
- [ ] File Uploads testen
- [ ] Email Versand testen
- [ ] Comet API Sync testen
- [ ] Backup & Restore testen
- [ ] Performance testen (Google PageSpeed)
- [ ] SSL Zertifikat prüfen
- [ ] Mobile Responsiveness testen
