# Guests

Die App `guests` erweitert owncloud.online um Gastkonten: Wird im
Freigabedialog eine E-Mail-Adresse eingetragen, zu der es noch kein Konto
gibt, legt die App ein Gastkonto an und verschickt eine Einladung mit
Aktivierungslink. Gäste melden sich danach mit ihrer E-Mail-Adresse an und
arbeiten in den Inhalten, die mit ihnen geteilt wurden.

## Was die App tut

- **Einladung per E-Mail.** Beim Freigeben an eine unbekannte
  E-Mail-Adresse wird ein Konto angelegt, dessen Benutzername die klein
  geschriebene E-Mail-Adresse ist. Anzeigename und E-Mail-Adresse werden
  aus der Eingabe übernommen.
- **Aktivierung durch den Gast.** Die Einladung enthält einen Link der
  Form `/apps/guests/register/<e-mail>/<token>`. Dort vergibt der Gast
  sein Passwort; anschließend wird der Token verworfen und die Anmeldeseite
  aufgerufen. Ab diesem Zeitpunkt meldet sich der Gast mit E-Mail-Adresse
  und selbst gewähltem Passwort an.
- **Eigene virtuelle Gruppe.** Alle Gäste erscheinen in einer Gruppe, die
  nicht in der Datenbank angelegt wird (Vorgabe: `guest_app`). Die
  Mitgliedschaft ergibt sich aus der Benutzereinstellung `isGuest` mit dem
  Wert `1`; darüber lassen sich Gäste in der Benutzerverwaltung filtern.
- **Whitelist der erlaubten Apps.** Ist die Beschränkung aktiv, dürfen
  Gäste nur die freigegebenen Apps aufrufen. Nicht freigegebene Apps
  beantwortet die App mit HTTP 403 und dem Hinweis „Der Zugriff auf diese
  Ressource ist für Gastnutzer untersagt."; passende Einträge werden
  zusätzlich aus dem Navigationsmenü entfernt.
- **Sperrliste für Domains.** E-Mail-Adressen aus gesperrten Domains
  werden mit „Die Domain ist blockiert" abgewiesen.
- **Gäste laden keine Gäste ein.** Der Versuch endet mit „Ein
  Gast-Benutzer kann keinen anderen Gast-Benutzer erstellen.".
- **Freigabe wird zurückgenommen, wenn die Einladung scheitert.** Kann
  die E-Mail nicht zugestellt werden, löscht die App die soeben erzeugte
  Freigabe wieder und meldet den Fehler an den Freigebenden.

## Voraussetzungen

| Komponente     | Anforderung                                    |
|----------------|------------------------------------------------|
| PHP            | 8.4 oder neuer                                 |
| owncloud.online| 10.15 bis 11.99                                |
| Composer       | 2.x (nur für die Installation aus dem Quelltext)|
| E-Mail-Versand | im Server eingerichtet, sonst keine Einladungen |

## Installation

Der einfachere Weg ist die Installation über den Markt; dort wird die App
als fertiges Paket ausgeliefert. Aus dem Quelltext geht es so:

    cd /var/www/owncloud.online/apps
    git clone https://github.com/BWTECH-github/guests.git
    cd guests
    composer install --no-dev
    chown -R www-data:www-data .
    sudo -u www-data php8.4 ../../occ app:enable guests

Die App-Kennung bleibt `guests`, vorhandene Gastkonten und Einstellungen
bleiben bei einem Wechsel auf diesen Fork also erhalten.

## Einstellungen

Die App bringt eine eigene Fläche in der Administration mit; sie wird im
Abschnitt `sharing` unter der Überschrift „Guests" eingehängt. Dort werden
vier Werte gepflegt, die als Anwendungseinstellungen unter der Kennung
`guests` gespeichert werden:

| Schlüssel      | Vorgabe      | Bedeutung                             |
|----------------|--------------|---------------------------------------|
| `group`        | `guest_app`  | Name der virtuellen Gastgruppe. Das Feld heißt „Gruppenname" und darf nicht leer sein. |
| `usewhitelist` | `true`       | Schaltet die App-Beschränkung ein („Gastzugriff auf eine App-Whitelist beschränken"). |
| `whitelist`    | siehe unten  | Kommaliste der zusätzlich erlaubten App-Kennungen. |
| `blockdomains` | leer         | Kommaliste gesperrter E-Mail-Domains („Diese Domain ist für Gäste Einladungen blockiert"). |

Ein fester Teil der Whitelist ist im Programmcode hinterlegt und lässt sich
nicht abschalten:

    core, files, dav, federatedfilesharing, guests, encryption,
    files_primary_s3, files_antivirus, files_external,
    files_external_dropbox, files_external_ftp, files_ldap_home,
    files_onedrive, sharepoint, files_external_s3,
    windows_network_drive, admin_audit, firewall,
    ransomware_protection

Der Zugriff über die DAV-Schnittstelle und die Protokollierung durch
`admin_audit` bleiben damit unabhängig von der Einstellung erhalten.

Der änderbare Teil hat folgende Voreinstellung; die Schaltfläche
„Whitelist zurücksetzen" stellt genau diesen Stand wieder her:

    settings, avatar, files_trashbin, files_versions, files_sharing,
    files_texteditor, activity, firstrunwizard, gallery, notifications,
    password_policy, oauth2, files_pdfviewer, files_mediaviewer,
    richdocuments, onlyoffice, wopi, oco_selfservice, twofactor_totp,
    impersonate

Die Voreinstellung ist bewusst knapp gehalten. Prüfen Sie vor der
Freigabe weiterer Apps, ob Gäste diese wirklich benötigen.

Aus der Serverkonfiguration wertet die App zusätzlich `default_language`
aus: Ist der Wert gesetzt, wird die Einladungsmail in dieser Sprache
verfasst, sonst in der Sprache der Sitzung, aus der die Freigabe stammt.

Eigene occ-Befehle bringt die App nicht mit.

## Fehlersuche

| Symptom | Ursache | Abhilfe |
|---|---|---|
| Freigabe an eine E-Mail-Adresse bricht mit „Fehler beim Teilen" ab, im Protokoll steht „Failed to send reset email" | Der Server kann keine E-Mail versenden; die App nimmt die Freigabe daraufhin zurück | E-Mail-Versand im Server einrichten und erneut freigeben |
| Gast erhält „Der Zugriff auf diese Ressource ist für Gastnutzer untersagt." oder sieht Menüeinträge nicht | Die App steht nicht auf der Whitelist | App-Kennung in der Whitelist ergänzen oder die Beschränkung abschalten |
| „Ein Benutzername mit dieser E-Mail existiert bereits." | Zu der Adresse gibt es bereits ein Konto – auch ein bereits aktiviertes Gastkonto | Direkt an das vorhandene Konto freigeben, statt neu einzuladen |
| „Die Domain ist blockiert" | Die Domain der Adresse steht in `blockdomains` | Eintrag entfernen oder eine andere Adresse verwenden |
| Ein bereits aktivierter Gast bekommt bei weiteren Freigaben keine E-Mail | Die Einladung geht nur an Konten, deren Aktivierungstoken noch offen ist; beim Setzen des Passworts wird er gelöscht | Kein Fehler. Der Gast findet die Freigabe nach der Anmeldung in seiner Dateiliste |
| Aktivierungslink meldet „Der Token ist ungültig" | Der Token wurde bereits eingelöst oder der Link ist unvollständig | Der Gast meldet sich mit E-Mail-Adresse und dem bereits gesetzten Passwort an |
| Gastkonten sind in der Benutzerverwaltung nicht auffindbar | Es wurde nicht nach der virtuellen Gruppe gefiltert | Nach der Gruppe aus `group` filtern (Vorgabe `guest_app`) |

## Herkunft

Die ursprüngliche Implementierung stammt von der ownCloud GmbH und ihren
Beitragenden. Dieser Fork wird von der BW-Tech GmbH für owncloud.online
gepflegt und auf PHP 8.4 gehalten.

- Quelltext: <https://github.com/BWTECH-github/guests>
- Dokumentation: <https://docs.owncloud.online>
- Produktseite: <https://owncloud.online>

Lizenz: GPL-2.0, unverändert gegenüber der ursprünglichen Fassung.
