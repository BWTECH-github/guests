# Guests (BW-Tech fork for owncloud.online)

PHP 8.4 fork of [owncloud/guests](https://github.com/BWTECH-github/guests) maintained by
**BW-Tech GmbH** for [owncloud.online](https://github.com/BWTECH-github/owncloud.online).

Share with external users by entering an email address in the sharing dialog.
Recipients receive an activation link, set their own password, and from then on can
log in with their email as the username. Guest accounts have no storage of their own
and can only access content that has been shared with them.

## Features

- Email-based guest invitation via the standard owncloud.online share dialog
- Self-service activation flow (recipient picks the password)
- Virtual `guest_app` group for filtering / permission scoping in user management
- Configurable per-app whitelist that limits which apps guests are allowed to use
- WebDAV (`/remote.php/dav`) and `/remote.php/webdav` access for guest users
- Optional domain blocklist for invitation email addresses
- Full audit-log compatibility (works with `admin_audit`)

## Requirements

| Component   | Version             |
|-------------|---------------------|
| PHP         | **8.4** or newer    |
| owncloud.online    | **10.15** – **11.x** (tested against `BWTECH-github/owncloud.online`) |
| Composer    | 2.x                 |
| Database    | sqlite, mysql, mariadb, postgres (whatever core supports) |

## Installation

```bash
cd /var/www/owncloud/apps
git clone https://github.com/BWTECH-github/owncloud.online.git -b main owncloud.online   # if not already present
git clone https://github.com/BWTECH-github/guests.git guests
cd guests
composer install --no-dev --no-interaction --prefer-dist
chown -R www-data:www-data .
cd /var/www/owncloud
sudo -u www-data php occ app:enable guests
```

App-id stays `guests` so any existing data, shares, and configuration carry over from
upstream installations without migration.

## Configuration

All settings live under the `guests` app namespace. Set via OCC or in `config/config.php`.

### `config.php` example

```php
<?php
$CONFIG = [
    // ... your usual core config ...

    // Optional: change the virtual group name guests are added to
    'apps' => [
        'guests' => [
            // 'true' (default) restricts guests to the whitelist below
            'usewhitelist'  => 'true',
            // Comma-separated app-ids that guests are allowed to use
            'whitelist'     => 'avatar,settings,files,files_external,files_trashbin,files_versions,files_sharing,activity,firstrunwizard,gallery,notifications,password_policy,oauth2,impersonate',
            // Virtual group name for filtering guests in user management
            'group'         => 'guest_app',
            // Comma-separated email-domain blocklist (e.g. 'mailinator.com,guerrillamail.com')
            'blockdomains'  => '',
        ],
    ],
];
```

### Setting values via OCC

```bash
sudo -u www-data php occ config:app:set guests usewhitelist --value=true
sudo -u www-data php occ config:app:set guests blockdomains --value='mailinator.com,guerrillamail.com'
sudo -u www-data php occ config:app:get guests whitelist
```

## OCC Commands

This app does **not** register custom OCC subcommands. All configuration is done via
`occ config:app:{get,set,delete} guests <key>` and the **Settings → Sharing** admin
page.

| OCC operation | Effect |
|---|---|
| `occ app:enable guests` | Activate the app |
| `occ app:disable guests` | Deactivate the app (existing guest users remain) |
| `occ config:app:get guests <key>` | Read a guests setting |
| `occ config:app:set guests <key> --value=<v>` | Write a guests setting |
| `occ user:list --group guest_app` | List all guest users (default group name) |

## Daily Usage

1. As any user with share permissions, open a file/folder share dialog.
2. Type a recipient **email address** that does not match an existing local user.
3. The app creates a disabled guest account, sends an activation email with a link.
4. The recipient opens the link, sets a password, and is forwarded to the share.
5. From then on the guest logs in with `<email>` and the chosen password.
6. The same email can receive additional shares from any user — the existing guest
   account is reused (no duplicate user is created).

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| Sharing returns "Error whilst sharing" | SMTP not configured | `occ config:system:set mail_smtpmode --value=smtp` and friends |
| Guest can log in but sees blank file list / 403 | Whitelist too strict | add the missing app-id to `guests/whitelist`, e.g. `dav`, `files_sharing` |
| WebDAV/sync clients return 403 | `dav` not in whitelist | add `dav` to whitelist (the app already permits `/remote.php/dav` URL prefix internally) |
| "User already exists" when re-sharing to an existing guest | Expected — this fork suppresses the error and reuses the account | no action needed; verify share appears in recipient's *Shared with you* |
| Activation link 404 | App not enabled or URL rewrite broken | `occ app:list \| grep guests`, check `.htaccess` / nginx rewrites |
| `composer install` fails with "platform requirement php >= 8.4" | PHP CLI is older | run with PHP 8.4 (`/usr/bin/php8.4 $(which composer) install`) |
| Guest user appears in normal user lists | Group filter not applied | filter user management by group `guest_app` (or your custom `guests/group` value) |

## Development

```bash
composer install                      # full deps incl. dev
composer bin owncloud-codestyle install
vendor-bin/owncloud-codestyle/vendor/bin/php-cs-fixer fix --dry-run --diff
```

CI runs the same lint + a full integration boot against
`BWTECH-github/owncloud.online@main` on every push and PR — see
[`.github/workflows/main.yml`](.github/workflows/main.yml).

## Attribution

- Original implementation: **ownCloud GmbH** and contributors —
  see [upstream history](https://github.com/BWTECH-github/guests/graphs/contributors)
- PHP 8.4 migration, WebDAV/whitelist fixes, owncloud.online integration:
  **BW-Tech GmbH** ([bw.tech](https://bw.tech))

## License

GPL-2.0 (unchanged from upstream).
