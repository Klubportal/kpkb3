#!/bin/bash
# Quick Server Setup Script für Hetzner
# Ausführen als root: bash quick-setup.sh

set -e

echo "╔════════════════════════════════════════════════════════╗"
echo "║  Hetzner Server Setup für KPKB3                       ║"
echo "╚════════════════════════════════════════════════════════╝"

# 1) System aktualisieren
echo "📦 System aktualisieren..."
apt update && apt -y upgrade

# 2) Pakete installieren
echo "📦 Basis-Pakete installieren..."
apt -y install git unzip curl ufw

echo "📦 PHP 8.2 installieren..."
apt -y install php8.2 php8.2-fpm php8.2-cli php8.2-mbstring php8.2-intl \
               php8.2-zip php8.2-gd php8.2-curl php8.2-xml php8.2-mysql

echo "📦 Nginx, MariaDB, Redis installieren..."
apt -y install nginx mariadb-server redis-server

# 3) Services starten
echo "🚀 Services starten..."
systemctl enable php8.2-fpm nginx mariadb redis-server
systemctl start php8.2-fpm nginx mariadb redis-server

# 4) Composer
echo "📦 Composer installieren..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# 5) Firewall
echo "🔒 Firewall konfigurieren..."
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

# 6) App-Verzeichnis
echo "📁 App-Verzeichnis anlegen..."
mkdir -p /var/www/kpkb3
chown -R www-data:www-data /var/www/kpkb3

echo ""
echo "✅ Basis-Setup abgeschlossen!"
echo ""
echo "Nächste Schritte:"
echo "  1. Datenbank anlegen: siehe HETZNER_DEPLOYMENT.md Schritt 2"
echo "  2. Nginx konfigurieren: siehe deployment/nginx-kpkb3.conf"
echo "  3. Code deployen: git clone + composer install"
echo ""
