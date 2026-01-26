# ownCloud Guest Plugin - Installationsanleitung
## PHP 8.4 Kompatible Version

**Version:** 1.0.0  
**Release Datum:** Januar 2025  
**Modified by:** BW-Tech GmbH

---

## Inhaltsverzeichnis

1. [Systemvoraussetzungen](#systemvoraussetzungen)
2. [Schnellstart-Installation](#schnellstart-installation)
3. [Detaillierte Installation](#detaillierte-installation)
4. [Konfiguration](#konfiguration)
5. [Verifikation](#verifikation)
6. [Troubleshooting](#troubleshooting)
7. [Migration](#migration)

---

## Systemvoraussetzungen

### Anforderungen

#### Server
- **Betriebssystem:** Ubuntu 20.04+ / Debian 11+ / CentOS 8+ / RHEL 8+
- **Webserver:** Apache 2.4+ oder nginx 1.18+
- **Datenbank:** MySQL/MariaDB 10.5+, PostgreSQL 12+, oder SQLite 3+
- **RAM:** Mindestens 2 GB (4 GB empfohlen)
- **Speicher:** Mindestens 10 GB freier Speicherplatz

#### Software
- **PHP:** 8.4 oder höher
- **Composer:** 2.x
- **Git:** 2.x
- **ownCloud Core:** 10.16.0.0 (PHP 8.4 kompatibel)

### PHP Extensions

Die folgenden PHP Extensions müssen installiert sein:

```bash
# Core Extensions
php8.4-bcmath
php8.4-bz2
php8.4-ctype
php8.4-curl
php8.4-dom
php8.4-fileinfo
php8.4-gd
php8.4-gmp
php8.4-iconv
php8.4-intl
php8.4-json
php8.4-mbstring
php8.4-openssl
php8.4-pcntl
php8.4-posix
php8.4-smbclient
php8.4-sqlite3
php8.4-xml
php8.4-zip
php8.4-zlib

# Optional aber empfohlen
php8.4-redis      # Für Caching
php8.4-imagick    # Für Bildbearbeitung
php8.4-apcu       # Für Performance
```

### Installation der PHP Extensions

#### Ubuntu/Debian
```bash
sudo apt update
sudo apt install -y php8.4 php8.4-{bcmath,bz2,ctype,curl,dom,fileinfo,gd,gmp,iconv,intl,json,mbstring,openssl,pcntl,posix,smbclient,sqlite3,xml,zip,zlib}
```

#### CentOS/RHEL
```bash
sudo dnf install -y php84-php84-{bcmath,bz2,ctype,curl,dom,fileinfo,gd,gmp,iconv,intl,json,mbstring,openssl,pcntl,posix,smbclient,sqlite3,xml,zip,zlib}
```

### PHP-Version prüfen

```bash
php -v
```

Erwartete Ausgabe:
```
PHP 8.4.x (cli) (built: ...)
Copyright (c) The PHP Group
Zend Engine v4.0.x, Copyright (c) Zend Technologies
```

---

## Schnellstart-Installation

### Installation in 5 Minuten

```bash
# 1. In das ownCloud Apps-Verzeichnis wechseln
cd /var/www/html/owncloud/apps

# 2. Altes Guests-Plugin sichern (falls vorhanden)
sudo mv guests guests_backup_$(date +%Y%m%d_%H%M%S)

# 3. PHP 8.4 Version klonen
sudo git clone --branch php8.4-migration https://github.com/GrossLukas/guest-php84.git guests

# 4. Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/html/owncloud/apps/guests

# 5. App aktivieren
cd /var/www/html/owncloud
sudo -u www-data php occ app:enable guests

# 6. Installation verifizieren
sudo -u www-data php occ app:list | grep guests
```

Das war's! Das Plugin ist jetzt installiert und bereit zur Verwendung.

---

## Detaillierte Installation

### Schritt 1: Vorbereitung

#### Backup erstellen
Bevor Sie beginnen, erstellen Sie unbedingt ein Backup:

```bash
# Backup der ownCloud Installation
cd /var/www/html/owncloud
sudo tar -czf backup_$(date +%Y%m%d_%H%M%S).tar.gz data config apps

# Backup des alten Guests-Plugins (falls vorhanden)
cd apps
if [ -d "guests" ]; then
    sudo mv guests guests_backup_$(date +%Y%m%d_%H%M%S)
fi
```

#### PHP-Version prüfen
Stellen Sie sicher, dass PHP 8.4 installiert ist:

```bash
php -v | grep "PHP 8.4"
```

Wenn nicht, installieren Sie PHP 8.4:

```bash
# Ubuntu/Debian
sudo apt install -y php8.4

# PHP 8.4 als Standard setzen
sudo update-alternatives --set php /usr/bin/php8.4
sudo update-alternatives --set phar /usr/bin/phar8.4
```

### Schritt 2: Installation

#### Methode 1: Git Clone (Empfohlen)

```bash
# In das ownCloud Apps-Verzeichnis wechseln
cd /var/www/html/owncloud/apps

# Repository klonen
sudo git clone --branch php8.4-migration https://github.com/GrossLukas/guest-php84.git guests

# Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/html/owncloud/apps/guests
```

#### Methode 2: Manuelles Download

```bash
# In das ownCloud Apps-Verzeichnis wechseln
cd /var/www/html/owncloud/apps

# ZIP-Archiv herunterladen
sudo wget https://github.com/GrossLukas/guest-php84/archive/refs/heads/php8.4-migration.zip

# Archiv entpacken
sudo unzip php8.4-migration.zip

# Verzeichnis umbenennen
sudo mv guest-php84-php8.4-migration guests

# ZIP-Datei löschen
sudo rm php8.4-migration.zip

# Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/html/owncloud/apps/guests
```

#### Methode 3: Composer

```bash
# In das ownCloud Apps-Verzeichnis wechseln
cd /var/www/html/owncloud/apps

# composer.json erstellen
sudo tee composer.json << 'EOF'
{
  "require": {
    "grosslukas/guest-php84": "dev-php8.4-migration"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/GrossLukas/guest-php84.git"
    }
  ]
}
EOF

# Installieren
sudo -u www-data composer install

# Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/html/owncloud/apps/guests
```

### Schritt 3: Composer Dependencies installieren

```bash
# In das Plugin-Verzeichnis wechseln
cd /var/www/html/owncloud/apps/guests

# Dependencies installieren
sudo -u www-data composer install --no-dev

# Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/html/owncloud/apps/guests
```

### Schritt 4: App aktivieren

```bash
# In das ownCloud Verzeichnis wechseln
cd /var/www/html/owncloud

# App aktivieren
sudo -u www-data php occ app:enable guests

# Erfolgsprüfung
sudo -u www-data php occ app:list | grep guests
```

Erwartete Ausgabe:
```
  - guests: 1.0.0 (enabled)
```

### Schritt 5: Webserver neu starten

```bash
# Apache
sudo systemctl restart apache2

# nginx und PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm
```

---

## Konfiguration

### Grundkonfiguration

Die Grundkonfiguration erfolgt über die ownCloud Admin-Oberfläche:

1. Als Admin in ownCloud einloggen
2. Navigieren Sie zu: **Einstellungen** → **Administration** → **Teilen**
3. Konfigurieren Sie die Gast-Plugin Einstellungen

### Gast-Plugin Einstellungen

#### Gast-Gruppe
Standardmäßig werden Gäste in die virtuelle Gruppe `guest_app` eingetragen.

#### Whitelist-Konfiguration
Die Whitelist bestimmt, welche Apps Gäste verwenden dürfen. Standardmäßig sind folgende Apps freigegeben:

```json
[
  "files",
  "gallery",
  "files_pdfviewer",
  "files_trashbin"
]
```

**WICHTIG:** Überprüfen Sie die Whitelist sorgfältig! Nur Apps in der Whitelist sind für Gäste zugänglich.

#### Email-Einstellungen
Das Plugin benötigt funktionierende Email-Einstellungen:

```bash
# SMTP-Modus prüfen
sudo -u www-data php occ config:system:get mail_smtpmode

# Wenn nicht konfiguriert:
sudo -u www-data php occ config:system:set mail_smtpmode --value="smtp"
sudo -u www-data php occ config:system:set mail_smtpsecure --value="tls"
sudo -u www-data php occ config:system:set mail_smtphost --value="smtp.example.com"
sudo -u www-data php occ config:system:set mail_smtpport --value="587"

# SMTP-Authentifizierung (falls erforderlich)
sudo -u www-data php occ config:system:set mail_smtpauth --value="1"
sudo -u www-data php occ config:system:set mail_smtpname --value="noreply@yourdomain.com"
sudo -u www-data php occ config:system:set mail_smtppassword --value="yourpassword"
```

### Erweiterte Konfiguration

#### config.php Einstellungen

Sie können zusätzliche Einstellungen in der `config.php` hinzufügen:

```php
<?php
// /var/www/html/owncloud/config/config.php

// Gast-Plugin spezifische Einstellungen
'app.mail.accounts.default' => [
    'email' => 'noreply@yourdomain.com',
    'name' => 'Your Company Name',
],

// Passwort-Policy
'password_policy' => [
    'minLength' => 12,
    'enforceUpperLowerCase' => true,
    'enforceNumericCharacters' => true,
    'enforceSpecialCharacters' => true,
],

// Session-Timeout (in Sekunden)
'session_lifetime' => 1800,

// Force HTTPS (empfohlen)
'overwrite.cli.url' => 'https://yourdomain.com',
'force_ssl' => true,
```

---

## Verifikation

### Grundlegende Verifikation

#### 1. App Status prüfen
```bash
sudo -u www-data php occ app:list | grep guests
```

Erwartete Ausgabe:
```
  - guests: 1.0.0 (enabled)
```

#### 2. PHP-Version prüfen
```bash
php -v
```

Erwartete Ausgabe:
```
PHP 8.4.x (cli)
```

#### 3. Dateiberechtigungen prüfen
```bash
ls -la /var/www/html/owncloud/apps/guests
```

Alle Dateien sollten dem Benutzer `www-data` gehören:
```
drwxr-xr-x  10 www-data www-data  4096 ...
-rw-r--r--   1 www-data www-data  1234 ...
```

### Funktionale Tests

#### Test 1: Gast-Konto erstellen

1. Melden Sie sich als Admin in ownCloud an
2. Navigieren Sie zu einer Datei oder einem Ordner
3. Klicken Sie auf "Teilen"
4. Geben Sie eine Email-Adresse ein (z.B. test@example.com)
5. Klicken Sie auf "Teilen"

**Erwartetes Ergebnis:**
- Gast wird erstellt
- Einladungs-Email wird gesendet
- Kein Fehler erscheint

#### Test 2: Gast-Registrierung

1. Öffnen Sie die Einladungs-Email
2. Klicken Sie auf den Aktivierungs-Link
3. Wählen Sie ein Passwort
4. Klicken Sie auf "Konto aktivieren"

**Erwartetes Ergebnis:**
- Gast-Konto wird aktiviert
- Sie werden zum Login weitergeleitet
- Sie können sich mit den Gast-Credentials einloggen

#### Test 3: Gast-Login

1. Melden Sie sich mit den Gast-Credentials an
2. Prüfen Sie, ob nur freigegebene Dateien sichtbar sind

**Erwartetes Ergebnis:**
- Erfolgreicher Login
- Nur freigegebene Dateien/Ordner sichtbar
- Zugriff auf alle freigegebenen Inhalte möglich

### Unit-Tests

Führen Sie die Unit-Tests aus, um sicherzustellen, dass alles korrekt funktioniert:

```bash
# In das Plugin-Verzeichnis wechseln
cd /var/www/html/owncloud/apps/guests

# Unit-Tests ausführen
./vendor/bin/phpunit

# Erwartete Ausgabe:
# Tests: 24, Assertions: 53, Warnings: 0.
```

### Log-Verifikation

Prüfen Sie die Logs auf Fehler:

```bash
# ownCloud Log
tail -f /var/www/html/owncloud/data/owncloud.log

# Apache Error Log
tail -f /var/log/apache2/error.log

# PHP-FPM Log
tail -f /var/log/php8.4-fpm.log
```

Es sollten keine PHP-Fehler oder Warnungen bezüglich des Guests-Plugins erscheinen.

---

## Troubleshooting

### Häufige Probleme

#### Problem 1: App lässt sich nicht aktivieren

**Fehlermeldung:**
```
Exception: Database error when running migration
```

**Lösung:**
```bash
# ownCloud Cache leeren
sudo -u www-data php occ maintenance:repair

# Datenbank-Migration ausführen
sudo -u www-data php occ upgrade

# App erneut aktivieren
sudo -u www-data php occ app:enable guests
```

#### Problem 2: Gast-Login lädt unendlich

**Symptom:** Die Login-Seite lädt endlos

**Lösung:**
```bash
# Browser-Cache leeren

# ownCloud Cache leeren
sudo -u www-data php occ maintenance:repair

# PHP-FPM Cache leeren
sudo systemctl restart php8.4-fpm

# Webserver neu starten
sudo systemctl restart apache2  # oder nginx
```

#### Problem 3: WebDAV 403 Forbidden

**Symptom:** Gast kann per WebDAV nicht zugreifen

**Lösung:**
```bash
# App Status prüfen
sudo -u www-data php occ app:list | grep guests

# App neu aktivieren
sudo -u www-data php occ app:disable guests
sudo -u www-data php occ app:enable guests

# Cache leeren
sudo -u www-data php occ files:scan --all
```

#### Problem 4: Email wird nicht gesendet

**Symptom:** Gast erhält keine Einladungs-Email

**Lösung:**
```bash
# Email-Konfiguration prüfen
sudo -u www-data php occ config:system:get mail_smtpmode

# Test-Email senden
echo "Test" | mail -s "Test Email" your@email.com

# Wenn nicht funktioniert, Email-Konfiguration anpassen:
sudo -u www-data php occ config:system:set mail_smtpmode --value="smtp"
sudo -u www-data php occ config:system:set mail_smtphost --value="smtp.example.com"
sudo -u www-data php occ config:system:set mail_smtpport --value="587"
```

#### Problem 5: HTTP 422 bei mehreren Shares

**Symptom:** Fehler beim Teilen eines zweiten Ordners mit demselben Gast

**Lösung:**
```bash
# Plugin aktualisieren
cd /var/www/html/owncloud/apps/guests
sudo git pull origin php8.4-migration

# Browser-Cache leeren
sudo systemctl restart php8.4-fpm
sudo systemctl restart apache2
```

### Debug-Modus aktivieren

Wenn Probleme auftreten, aktivieren Sie den Debug-Modus:

```bash
# Debug-Modus in config.php aktivieren
sudo -u www-data php occ config:system:set debug --value="true"

# Log-Level erhöhen
sudo -u www-data php occ config:system:set loglevel --value="2"

# Logs überwachen
tail -f /var/www/html/owncloud/data/owncloud.log
```

### PHP-Fehler prüfen

Prüfen Sie die PHP-Fehler-Logs:

```bash
# Apache
tail -f /var/log/apache2/error.log

# nginx
tail -f /var/log/nginx/error.log

# PHP-FPM
tail -f /var/log/php8.4-fpm.log
```

### Support kontaktieren

Wenn Sie das Problem nicht lösen können:

1. Sammeln Sie folgende Informationen:
   - ownCloud Version: `php -v`
   - PHP Version: `php -v`
   - Plugin Version: Check `appinfo/info.xml`
   - Error Logs: `/var/www/html/owncloud/data/owncloud.log`

2. Erstellen Sie ein Issue auf GitHub:
   https://github.com/GrossLukas/guest-php84/issues

3. Oder kontaktieren Sie den Support:
   Email: support@bw-tech.de

---

## Migration

### Von PHP 7.4 zu PHP 8.4

#### Wichtige Hinweise
1. **Backup erstellen:** Vor der Migration unbedingt ein vollständiges Backup erstellen!
2. **Reihenfolge beachten:** Erst ownCloud Core, dann PHP, dann Plugin aktualisieren
3. **Cache leeren:** Nicht vergessen, alle Caches zu leeren
4. **Testing:** Testen Sie die Migration in einer Staging-Umgebung zuerst

#### Migrationsschritte

##### Schritt 1: Backup erstellen

```bash
# Backup der ownCloud Installation
cd /var/www/html/owncloud
sudo tar -czf backup_$(date +%Y%m%d_%H%M%S).tar.gz data config apps

# Backup der Datenbank
mysqldump -u root -p owncloud > backup_db_$(date +%Y%m%d_%H%M%S).sql
```

##### Schritt 2: ownCloud Core aktualisieren

```bash
# ownCloud Core auf PHP 8.4 Version aktualisieren
cd /var/www/html/owncloud

# PHP 8.4 Version klonen
sudo git clone --branch php8.4 https://github.com/GrossLukas/owncloud-core.git core_php84

# Dateien kopieren
sudo cp -r core_php84/* .
sudo rm -rf core_php84

# Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/html/owncloud

# Datenbank-Migration
sudo -u www-data php occ upgrade
```

##### Schritt 3: PHP-Version ändern

```bash
# PHP 8.4 installieren (falls noch nicht geschehen)
sudo apt install -y php8.4 php8.4-*

# PHP 8.4 als Standard setzen
sudo update-alternatives --set php /usr/bin/php8.4

# PHP-FPM neu starten
sudo systemctl restart php8.4-fpm
```

##### Schritt 4: Altes Guests-Plugin deaktivieren

```bash
# App deaktivieren
cd /var/www/html/owncloud
sudo -u www-data php occ app:disable guests

# Backup des alten Plugins
cd apps
sudo mv guests guests_backup_php74_$(date +%Y%m%d_%H%M%S)
```

##### Schritt 5: Neues Guests-Plugin installieren

```bash
# PHP 8.4 Version klonen
sudo git clone --branch php8.4-migration https://github.com/GrossLukas/guest-php84.git guests

# Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/html/owncloud/apps/guests

# Dependencies installieren
cd guests
sudo -u www-data composer install --no-dev
```

##### Schritt 6: App aktivieren

```bash
# App aktivieren
cd /var/www/html/owncloud
sudo -u www-data php occ app:enable guests

# Status prüfen
sudo -u www-data php occ app:list | grep guests
```

##### Schritt 7: Cache leeren und Testen

```bash
# Cache leeren
sudo -u www-data php occ maintenance:repair
sudo -u www-data php occ files:scan --all

# Webserver neu starten
sudo systemctl restart apache2  # oder nginx
sudo systemctl restart php8.4-fpm

# Unit-Tests ausführen
cd /var/www/html/owncloud/apps/guests
./vendor/bin/phpunit
```

### Daten-Kompatibilität

#### Gast-Konto-Daten
- **Werden beibehalten:** Alle existierenden Gast-Konten funktionieren weiterhin
- **Keine Migration erforderlich:** Die Datenbankstruktur ist identisch
- **Passwort-Hashes:** Werden weiterhin unterstützt

#### Share-Daten
- **Werden beibehalten:** Alle existierenden Shares sind weiterhin gültig
- **Keine Änderungen:** Die Share-Tabelle wurde nicht modifiziert
- **Zugriff bleibt erhalten:** Gäste können weiterhin auf freigegebene Inhalte zugreifen

#### Konfiguration
- **Wird übernommen:** Die bestehende Konfiguration wird übernommen
- **Keine Anpassungen nötig:** Die Konfigurationsdateien sind kompatibel
- **Whitelist-Einstellungen:** Werden übernommen

### Rollback

Wenn bei der Migration etwas schiefgeht, können Sie zurückrollen:

```bash
# ownCloud Verzeichnis wiederherstellen
cd /var/www/html
sudo rm -rf owncloud
sudo tar -xzf backup_YYYYMMDD_HHMMSS.tar.gz

# Datenbank wiederherstellen
mysql -u root -p owncloud < backup_db_YYYYMMDD_HHMMSS.sql

# Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/html/owncloud

# Webserver neu starten
sudo systemctl restart apache2  # oder nginx
sudo systemctl restart php7.4-fpm
```

---

## Zusammenfassung

Die Installation des ownCloud Guest Plugins (PHP 8.4 Version) ist unkompliziert und dauert nur wenige Minuten. Folgen Sie einfach der Schnellstart-Installation oder der detaillierten Installationsanleitung.

### Wichtige Punkte

1. **PHP 8.4 erforderlich:** Stellen Sie sicher, dass PHP 8.4 installiert ist
2. **Backup erstellen:** Erstellen Sie vor der Installation ein Backup
3. **Berechtigungen:** Setzen Sie die richtigen Berechtigungen (www-data)
4. **Email-Konfiguration:** Konfigurieren Sie SMTP für Einladungs-Emails
5. **Whitelist prüfen:** Überprüfen Sie die Whitelist-Konfiguration
6. **Testing:** Testen Sie die Installation gründlich

### Support

Bei Problemen oder Fragen:
- **GitHub Issues:** https://github.com/GrossLukas/guest-php84/issues
- **Email:** support@bw-tech.de
- **Forum:** https://central.owncloud.org

---

**Ende der Installationsanleitung**