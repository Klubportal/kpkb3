# Deployment Guide - Production Checklist

**Schritt-für-Schritt Anleitung für Production Deployment**

---

## ✅ Pre-Deployment Checklist

### 1. Environment & Dependencies

```bash
# Update dependencies
composer update --no-dev
npm install --production

# Check PHP version
php -v  # Should be 8.2+

# Check MySQL
mysql --version  # Should be 8.0+
```

### 2. Configuration

```bash
# Copy environment
cp .env.example .env.production

# Generate key
php artisan key:generate

# Update .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com  # Or your provider
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@club.de"
MAIL_FROM_NAME="Club Management"

# VAPID keys for Push
VAPID_SUBJECT="mailto:admin@club.de"
VAPID_PUBLIC_KEY=your_public_key
VAPID_PRIVATE_KEY=your_private_key

# Queue
QUEUE_CONNECTION=database
CACHE_DRIVER=redis  # Recommended
SESSION_DRIVER=cookie

# PWA
APP_PROTOCOL=https  # Never HTTP!
```

### 3. Database Setup

```bash
# Production database
CREATE DATABASE kp_club_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kp_club'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON kp_club_production.* TO 'kp_club'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Migrations & Seeders

```bash
# Run migrations
php artisan migrate --force

# Run tenant migrations (for each tenant)
php artisan migrate --path=database/migrations/tenant --force

# Optional: seed initial data
php artisan db:seed --force
```

---

## 🔒 Security Configuration

### 1. SSL/HTTPS

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Generate certificate
sudo certbot certonly --nginx -d club.de -d www.club.de

# Auto-renewal
sudo systemctl enable certbot.timer
```

### 2. Firewall

```bash
# UFW Firewall (Ubuntu)
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

### 3. File Permissions

```bash
# Web directory
sudo chown -R www-data:www-data /var/www/kp_club
sudo chmod -R 755 /var/www/kp_club
sudo chmod -R 775 /var/www/kp_club/storage
sudo chmod -R 775 /var/www/kp_club/bootstrap/cache
```

### 4. Database Security

```bash
# Remote access disabled
# Bind to localhost only in my.cnf
bind-address = 127.0.0.1

# Strong passwords
MYSQL_ROOT_PASSWORD=very_strong_root_password
MYSQL_PASSWORD=very_strong_user_password
```

---

## 🚀 Web Server Setup

### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name club.de www.club.de;

    # SSL certificates
    ssl_certificate /etc/letsencrypt/live/club.de/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/club.de/privkey.pem;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    root /var/www/kp_club/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
    }

    # Service worker
    location /service-worker.js {
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        add_header Expires "0";
    }

    # PWA manifest
    location /manifest.json {
        add_header Content-Type "application/manifest+json";
    }
}

# HTTP redirect
server {
    listen 80;
    listen [::]:80;
    server_name club.de www.club.de;
    return 301 https://$server_name$request_uri;
}
```

### Apache Configuration

```apache
<VirtualHost *:443>
    ServerName club.de
    ServerAlias www.club.de
    DocumentRoot /var/www/kp_club/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/club.de/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/club.de/privkey.pem

    <Directory /var/www/kp_club/public>
        Allow from all
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^ index.php [QSA,L]
        </IfModule>
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
</VirtualHost>

# HTTP redirect
<VirtualHost *:80>
    ServerName club.de
    ServerAlias www.club.de
    Redirect / https://club.de/
</VirtualHost>
```

---

## 🔄 Services Setup

### 1. Queue Worker (Systemd)

```ini
# /etc/systemd/system/laravel-queue.service

[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/kp_club
ExecStart=/usr/bin/php artisan queue:work --queue=default,high --timeout=60 --sleep=3 --tries=3
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
sudo systemctl enable laravel-queue
sudo systemctl start laravel-queue
sudo systemctl status laravel-queue
```

### 2. Scheduler (Crontab)

```bash
# Edit crontab
sudo crontab -e -u www-data

# Add line (runs every minute)
* * * * * php /var/www/kp_club/artisan schedule:run >> /var/log/laravel-scheduler.log 2>&1
```

### 3. PHP-FPM

```bash
# Start PHP-FPM
sudo systemctl start php8.2-fpm
sudo systemctl enable php8.2-fpm
sudo systemctl status php8.2-fpm
```

---

## 📊 Monitoring & Logging

### 1. Log Rotation

```bash
# /etc/logrotate.d/laravel

/var/www/kp_club/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.2-fpm > /dev/null 2>&1 || true
    endscript
}
```

### 2. System Monitoring

```bash
# Monitor queue status
watch -n 5 'php artisan queue:failed'

# Monitor logs in real-time
tail -f storage/logs/laravel.log
tail -f storage/logs/queue.log

# Check disk space
df -h
du -sh storage/

# Monitor processes
ps aux | grep "queue:work"
ps aux | grep "php-fpm"
```

### 3. Backup Strategy

```bash
# Daily backup script
#!/bin/bash

BACKUP_DIR="/backups/kp_club"
DATE=$(date +%Y-%m-%d_%H-%M-%S)

# Database backup
mysqldump -u kp_club -p${DB_PASSWORD} kp_club_production | gzip > ${BACKUP_DIR}/db_${DATE}.sql.gz

# File backup
tar -czf ${BACKUP_DIR}/files_${DATE}.tar.gz /var/www/kp_club --exclude=node_modules --exclude=vendor

# Keep only last 30 days
find ${BACKUP_DIR} -type f -mtime +30 -delete

echo "Backup completed: ${DATE}"
```

Add to crontab:
```bash
0 2 * * * /usr/local/bin/backup-kp-club.sh >> /var/log/backup.log 2>&1
```

---

## 🧪 Health Checks

### 1. Application Health

```bash
curl https://club.de/api/health
```

Response should be:
```json
{
    "status": "healthy",
    "database": "connected",
    "queue": "working",
    "timestamp": "2025-10-23T10:00:00Z"
}
```

### 2. Queue Status

```bash
php artisan queue:work --verbose

# Should show:
# Processing: App\Jobs\SendMassEmailJob
# Failed: 0
```

### 3. Scheduled Jobs

```bash
php artisan schedule:list

# Should show:
# app:command      *  *  *  *  *  /usr/bin/php artisan command
```

---

## 🚀 Deployment Script

```bash
#!/bin/bash

set -e

REPO="/var/www/kp_club"
BRANCH="main"

echo "Starting deployment..."

# Pull latest code
cd $REPO
git fetch origin
git reset --hard origin/$BRANCH

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Database migrations
php artisan migrate --force

# Build assets
npm run build

# Set permissions
chown -R www-data:www-data $REPO
chmod -R 775 storage bootstrap/cache

# Restart services
systemctl restart php8.2-fpm
systemctl restart laravel-queue

echo "Deployment completed successfully!"
echo "Timestamp: $(date)"

# Send notification
echo "Deployment successful" | mail -s "KP Club Deployment" admin@club.de
```

Usage:
```bash
sudo chmod +x /usr/local/bin/deploy-kp-club.sh
sudo /usr/local/bin/deploy-kp-club.sh
```

---

## 🔧 Troubleshooting

### Queue jobs not processing

```bash
# Check queue is running
systemctl status laravel-queue

# Restart queue
systemctl restart laravel-queue

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Flush all jobs
php artisan queue:flush
```

### High memory usage

```bash
# Check memory limits
php -i | grep memory_limit

# Increase if needed (update php.ini)
memory_limit = 512M  # or more

# Restart PHP-FPM
systemctl restart php8.2-fpm
```

### Slow database queries

```bash
# Enable query logging
DB_LOG_QUERIES=true

# Monitor slow queries
tail -f storage/logs/laravel.log | grep "slow"

# Check indexes
SHOW INDEX FROM table_name;
ANALYZE TABLE table_name;
```

---

## 📈 Performance Optimization

### 1. Caching

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),
'redis' => [
    'client' => 'phpredis',
    'connection' => 'cache',
]

// Use caching
Cache::remember('key', 3600, function() {
    return expensive_operation();
});
```

### 2. Database Optimization

```sql
-- Add indexes
CREATE INDEX idx_tenant_id ON users(tenant_id);
CREATE INDEX idx_created_at ON messages(created_at);
CREATE INDEX idx_user_id_created_at ON notifications(user_id, created_at);

-- Analyze tables
ANALYZE TABLE users;
ANALYZE TABLE messages;

-- Check table size
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS `Size in MB` 
FROM information_schema.TABLES 
WHERE table_schema = 'kp_club_production';
```

### 3. CDN Setup (Optional)

```bash
# CloudFlare or similar
# Point DNS to CDN
# Enable caching rules for static assets
# Configure purge on deploy
```

---

## 📞 Monitoring & Alerts

### Uptime Monitoring

Services like Uptime Robot, Pingdom:
```
Health check URL: https://club.de/api/health
Check interval: 5 minutes
Alert on failure: YES
```

### Error Tracking

```php
// Sentry integration
'sentry' => env('SENTRY_LARAVEL_DSN'),

// Or Rollbar, Bugsnag, etc
```

---

## ✅ Post-Deployment

```bash
# 1. Test all endpoints
curl https://club.de/api/push-notifications
curl https://club.de/api/messages
curl https://club.de/api/email-templates

# 2. Test Push Notifications
curl -X POST https://club.de/api/push-subscriptions/register

# 3. Test Email
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('admin@club.de'); });

# 4. Monitor logs
tail -f storage/logs/laravel.log

# 5. Check queue
php artisan queue:work

# 6. Run health check
curl https://club.de/api/health
```

---

**Status:** ✅ Ready for Production
**Version:** 1.0
**Last Updated:** 2025-10-23
