# DNS & SUBDOMAIN SETUP - Klubportal Multi-Tenant
===================================================

## DOMAIN STRUKTUR

### Haupt-Domain
- **klubportal.deinedomain.com** → Central/Landlord (Verwaltung)

### Tenant Subdomains
- **nkprigorjem.deinedomain.com** → NK Prigorje Markuševec
- **club2.deinedomain.com** → Zweiter Verein (Beispiel)
- **club3.deinedomain.com** → Dritter Verein (Beispiel)

## 1. DNS EINTRÄGE (bei Domain Provider)

### A-Records (bei CloudFlare, Hetzner, etc.)
```
Type    Name            Value               TTL     Proxy
A       klubportal      YOUR_SERVER_IP      Auto    Proxied (Orange)
A       *.klubportal    YOUR_SERVER_IP      Auto    Proxied (Orange)
```

ODER einzelne Subdomains:
```
Type    Name                Value               TTL
A       klubportal          YOUR_SERVER_IP      3600
A       nkprigorjem         YOUR_SERVER_IP      3600
A       club2               YOUR_SERVER_IP      3600
```

### CNAME Records (Alternative)
```
Type    Name            Value                       TTL
CNAME   nkprigorjem     klubportal.deinedomain.com  3600
CNAME   club2           klubportal.deinedomain.com  3600
```

## 2. NGINX CONFIGURATION (Empfohlen)

### /etc/nginx/sites-available/klubportal.conf
```nginx
# Hauptdomain - Central/Landlord
server {
    listen 80;
    listen 443 ssl http2;
    server_name klubportal.deinedomain.com;
    root /var/www/klubportal/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/klubportal.deinedomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/klubportal.deinedomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
}

# Wildcard für alle Tenant Subdomains
server {
    listen 80;
    listen 443 ssl http2;
    server_name *.deinedomain.com;
    root /var/www/klubportal/public;

    # SSL Wildcard Certificate
    ssl_certificate /etc/letsencrypt/live/deinedomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/deinedomain.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# HTTP zu HTTPS Redirect
server {
    listen 80;
    server_name klubportal.deinedomain.com *.deinedomain.com;
    return 301 https://$host$request_uri;
}
```

### Nginx aktivieren
```bash
sudo ln -s /etc/nginx/sites-available/klubportal.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 3. APACHE CONFIGURATION (Alternative)

### /etc/apache2/sites-available/klubportal.conf
```apache
<VirtualHost *:80>
    ServerName klubportal.deinedomain.com
    ServerAlias *.deinedomain.com
    DocumentRoot /var/www/klubportal/public

    <Directory /var/www/klubportal/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/klubportal-error.log
    CustomLog ${APACHE_LOG_DIR}/klubportal-access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName klubportal.deinedomain.com
    ServerAlias *.deinedomain.com
    DocumentRoot /var/www/klubportal/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/klubportal.deinedomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/klubportal.deinedomain.com/privkey.pem

    <Directory /var/www/klubportal/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/klubportal-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/klubportal-ssl-access.log combined
</VirtualHost>
```

### Apache aktivieren
```bash
sudo a2enmod rewrite ssl
sudo a2ensite klubportal.conf
sudo systemctl reload apache2
```

## 4. SSL ZERTIFIKAT (Let's Encrypt - KOSTENLOS)

### Installation Certbot
```bash
sudo apt install certbot python3-certbot-nginx  # für Nginx
# ODER
sudo apt install certbot python3-certbot-apache  # für Apache
```

### Wildcard Zertifikat (für *.deinedomain.com)
```bash
sudo certbot certonly --manual --preferred-challenges dns \
  -d deinedomain.com -d *.deinedomain.com

# Folge den Anweisungen und erstelle DNS TXT Records:
# _acme-challenge.deinedomain.com TXT "VERIFICATION_STRING"
```

### Einzelne Zertifikate
```bash
# Nginx
sudo certbot --nginx -d klubportal.deinedomain.com -d nkprigorjem.deinedomain.com

# Apache
sudo certbot --apache -d klubportal.deinedomain.com -d nkprigorjem.deinedomain.com
```

### Auto-Renewal
```bash
# Test renewal
sudo certbot renew --dry-run

# Cron Job (automatisch bei Installation)
sudo crontab -e
# Füge hinzu:
0 3 * * * certbot renew --quiet --post-hook "systemctl reload nginx"
```

## 5. LARAVEL TENANT CONFIGURATION

### config/tenancy.php
```php
'central_domains' => [
    'klubportal.deinedomain.com',  // Central/Landlord domain
],

'exempt_domains' => [
    'klubportal.deinedomain.com',
],
```

### Tenant in Datenbank erstellen
```php
// Via Tinker oder Migration
$tenant = Tenant::create([
    'id' => 'nkprigorjem',
    'name' => 'NK Prigorje Markuševec',
]);

$tenant->domains()->create([
    'domain' => 'nkprigorjem.deinedomain.com',
]);
```

## 6. .htaccess OPTIMIERUNGEN (Laravel public/.htaccess)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable Directory Browsing
Options -Indexes

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript
</IfModule>

# Cache Static Files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

## 7. CLOUDFLARE SETUP (Optional - CDN & DDoS Protection)

1. **Domain zu CloudFlare hinzufügen**
   - cloudflare.com → Add Site
   - Domain eingeben: deinedomain.com
   - Nameserver bei deinem Provider ändern zu CloudFlare NS

2. **DNS Records in CloudFlare**
   ```
   Type    Name            Value               Proxy
   A       klubportal      YOUR_SERVER_IP      ✓ Proxied (Orange)
   A       nkprigorjem     YOUR_SERVER_IP      ✓ Proxied
   A       *               YOUR_SERVER_IP      ✓ Proxied (Wildcard)
   ```

3. **SSL/TLS Settings**
   - SSL/TLS → Full (strict)
   - Edge Certificates → Always Use HTTPS: ON
   - Automatic HTTPS Rewrites: ON

4. **Performance**
   - Speed → Optimization
   - Auto Minify: CSS, JS, HTML
   - Brotli: ON
   - Rocket Loader: ON (optional)

5. **Security**
   - Firewall Rules
   - Rate Limiting
   - DDoS Protection (automatisch)

## 8. TESTING

```bash
# DNS prüfen
nslookup klubportal.deinedomain.com
nslookup nkprigorjem.deinedomain.com

# SSL testen
https://www.ssllabs.com/ssltest/

# Performance testen
https://gtmetrix.com
https://pagespeed.web.dev
```

## 9. TENANT DOMAINS VERWALTEN

### Via Filament Admin Panel
1. Login als Super Admin
2. Tenants → Edit
3. Domains → Add Domain
4. Domain: "nkprigorjem.deinedomain.com"
5. Save

### Via Tinker
```php
php artisan tinker

$tenant = Tenant::find('nkprigorjem');
$tenant->domains()->create(['domain' => 'nkprigorjem.deinedomain.com']);
```

### Via Migration
```php
// database/seeders/TenantDomainsSeeder.php
$tenants = [
    'nkprigorjem' => 'nkprigorjem.deinedomain.com',
    'club2' => 'club2.deinedomain.com',
];

foreach ($tenants as $tenantId => $domain) {
    $tenant = Tenant::find($tenantId);
    if ($tenant) {
        $tenant->domains()->updateOrCreate(['domain' => $domain]);
    }
}
```

## ZUSAMMENFASSUNG

1. ✅ DNS A-Records für alle Subdomains erstellen
2. ✅ Nginx/Apache für Wildcard Subdomains konfigurieren
3. ✅ SSL Zertifikat installieren (Let's Encrypt Wildcard)
4. ✅ Laravel .env mit Production Settings
5. ✅ Tenant Domains in Datenbank eintragen
6. ✅ Testen: https://nkprigorjem.deinedomain.com

**Jeder Tenant bekommt seine eigene Subdomain und eigene Datenbank!**
