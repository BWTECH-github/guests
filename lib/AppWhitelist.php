<?php

declare(strict_types=1);

/**
 * @author Ilja Neumann <ineumann@owncloud.com>
 * @author Thomas Heinisch <t.heinisch@bw-tech.de>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
 * Modified by BW-Tech GmbH
 * @license GPL-2.0
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Guests;

use OCP\Template;

/**
 * Only allow whitelisted apps to be accessed by guests
 *
 * Die Liste haelt Gaeste aus fremden Anwendungen heraus. Sie ersetzt nicht das
 * Rechtesystem: Pfade, die gar keine Anwendung benennen (Einstiegspunkte,
 * statische Dateien, die OCS-Kernrouten), bleiben dem Kern ueberlassen und
 * werden hier als 'core' gefuehrt. Sobald ein Pfad eine Anwendung benennt,
 * muss sie in der Liste stehen - sonst wird abgewiesen.
 *
 * @package OCA\Guests
 */
class AppWhitelist {
	// dashboard: Startseite des Redesign-Kerns und Ziel nach der Anmeldung
	// (OC_Util::getDefaultPageUrl). Fehlt sie, landet ein Gast nach dem Login
	// auf der Sperrseite statt bei seinen Dateien.
	public const CORE_WHITELIST = 'core,files,dashboard,dav,federatedfilesharing,guests,encryption,files_primary_s3,files_antivirus,files_external,files_external_dropbox,files_external_ftp,files_ldap_home,files_onedrive,sharepoint,files_external_s3,windows_network_drive,admin_audit,firewall,ransomware_protection';
	public const DEFAULT_WHITELIST = 'settings,avatar,files_trashbin,files_versions,files_sharing,files_texteditor,activity,firstrunwizard,gallery,notifications,password_policy,oauth2,files_pdfviewer,files_mediaviewer,richdocuments,onlyoffice,wopi,oco_selfservice,twofactor_totp,impersonate';

	/**
	 * Pfade, die keine Anwendung benennen. Sie gelten als 'core' und damit als
	 * zulaessig - fuer sie greift die Rechtepruefung des Kerns.
	 */
	private const NO_APP = 'core';

	/**
	 * @param array<string, mixed> $params
	 */
	public static function preSetup(array $params): void {
		$uid = $params['user'] ?? '';

		if (empty($uid)) {
			return;
		}

		$config = \OC::$server->getConfig();
		$isGuest = $config->getUserValue($uid, 'owncloud', 'isGuest', false);
		$whitelistEnabled = $config->getAppValue('guests', 'usewhitelist', 'true') === 'true';

		if ($isGuest && $whitelistEnabled) {
			$path = \OC::$server->getRequest()->getPathInfo();
			$app = self::getRequestedApp($path);
			$whitelist = self::getWhitelist();

			if ($app === false || !\in_array($app, $whitelist, true)) {
				\header('HTTP/1.0 403 Forbidden');
				$l = \OC::$server->getL10NFactory()->get('guests');
				Template::printErrorPage($l->t(
					'Access to this resource is forbidden for guests.'
				));
				exit;
			}
		}
	}

	/**
	 * @return array<string>
	 */
	public static function getWhitelist(): array {
		$whitelist = self::CORE_WHITELIST;
		$whitelist .= ',' . \OC::$server->getConfig()->getAppValue(
			'guests',
			'whitelist',
			self::DEFAULT_WHITELIST
		);

		// Leere Eintraege entfernen. Ohne das steht '' in der Liste, und weil
		// ein Pfad ohne Anwendungsnamen frueher ebenfalls '' ergab, haette
		// jeder solche Pfad die Pruefung bestanden.
		$apps = [];
		foreach (\explode(',', $whitelist) as $app) {
			$app = \trim($app);
			if ($app !== '') {
				$apps[] = $app;
			}
		}

		return \array_values(\array_unique($apps));
	}

	/**
	 * Welche Anwendung spricht dieser Pfad an?
	 *
	 * Core has \OC::$REQUESTEDAPP but it isn't set until the routes are matched
	 * taken from \OC\Route\Router::match()
	 *
	 * Rueckgabe:
	 *   - der Anwendungsname, wenn der Pfad eine Anwendung benennt
	 *   - self::NO_APP ('core') fuer Pfade ohne Anwendungsnamen
	 *   - false, wenn der Pfad eine Anwendung benennt, der Name aber nach dem
	 *     Saeubern leer ist - das ist ein Manipulationsversuch und wird
	 *     abgewiesen
	 *
	 * Sichtbar fuer die Tests; die Zuordnung ist reine Zeichenkettenarbeit und
	 * laesst sich nur so vollstaendig pruefen.
	 */
	public static function getRequestedApp(string $url): string|false {
		$url = self::normalize($url);

		// Einstiegspunkte abtragen: '/index.php/apps/x' und '/apps/x' sind
		// derselbe Aufruf. Frueher fiel die erste Form in einen Sammelzweig,
		// der pauschal 'core' lieferte - damit war jede Anwendung erreichbar.
		foreach (['/index.php', '/public.php'] as $entryPoint) {
			if ($url === $entryPoint) {
				return self::NO_APP;
			}
			if (\str_starts_with($url, $entryPoint . '/')) {
				$url = \substr($url, \strlen($entryPoint));
				break;
			}
		}

		// OCS: '/ocs/v1.php/apps/<app>/...' und '/ocs/v2.php/apps/<app>/...'
		// benennen eine Anwendung. Genau hier lag die groesste Luecke - der
		// ganze OCS-Baum wurde als 'core' gefuehrt, wodurch Gaeste die
		// Schnittstellen jeder installierten Anwendung erreichten.
		if (\preg_match('#^/ocs/v[12]\.php(/.*)?$#', $url, $matches) === 1) {
			return self::appFromAppsPath($matches[1] ?? '');
		}
		if (\str_starts_with($url, '/ocs/') || $url === '/ocs') {
			return self::NO_APP;
		}

		if (\str_starts_with($url, '/apps/')) {
			return self::appFromAppsPath($url);
		}
		if ($url === '/apps') {
			return self::NO_APP;
		}

		if (\str_starts_with($url, '/core/') || $url === '/core') {
			return 'core';
		}
		if (\str_starts_with($url, '/settings/') || $url === '/settings') {
			return 'settings';
		}
		if (\str_starts_with($url, '/avatar/') || $url === '/avatar') {
			return 'avatar';
		}
		if (\str_starts_with($url, '/heartbeat')) {
			return 'heartbeat';
		}
		// Kommentare laufen über den DAV-Baum, gehören aber der App comments.
		// Die steht auf keiner Liste; vor der verschärften Zuordnung bekamen
		// Gäste hier 403. Ohne diesen Zweig fiele der Pfad unter 'dav' und
		// wäre erlaubt (Gegen-Review 23.09.2026). Genaue Segmentprüfung, damit
		// '/dav/commentsX' nicht mitgemeint ist.
		foreach (['/dav/comments', '/remote.php/dav/comments'] as $kommentare) {
			if ($url === $kommentare || \str_starts_with($url, $kommentare . '/')) {
				return 'comments';
			}
		}
		// Der DAV-Baum ist ein einziger Endpunkt; wer dort was sehen darf,
		// entscheidet die Rechtepruefung des Kerns an der Datei, nicht diese
		// Liste. Beide Schreibweisen fuehren deshalb zur selben Antwort.
		if (\str_starts_with($url, '/remote.php/dav') || \str_starts_with($url, '/dav/')
			|| $url === '/dav' || \str_starts_with($url, '/remote.php/webdav')
			|| \str_starts_with($url, '/webdav')) {
			return 'dav';
		}

		// Alles Weitere benennt keine Anwendung: die Wurzel, '/login',
		// '/logout', '/status.php', '/s/<token>', statische Dateien. Dafuer
		// gilt die Rechtepruefung des Kerns.
		return self::NO_APP;
	}

	/**
	 * Den Anwendungsnamen aus einem Pfad der Form '/apps/<app>/...' ziehen.
	 * Benennt der Pfad keine Anwendung, ist es ein Kernpfad; bleibt nach dem
	 * Saeubern nichts uebrig, wird abgewiesen.
	 */
	private static function appFromAppsPath(string $path): string|false {
		if (!\str_starts_with($path, '/apps/')) {
			return self::NO_APP;
		}

		// leerer String / 'apps' / $app / Rest der Route
		$parts = \explode('/', $path, 4);
		$raw = $parts[2] ?? '';
		if ($raw === '') {
			return self::NO_APP;
		}

		$app = self::cleanAppId($raw);

		// '..' oder '/' im Namen: nach dem Saeubern bleibt nichts uebrig.
		// Das ist kein Kernpfad, sondern ein Versuch, an der Liste
		// vorbeizukommen.
		return $app === '' ? false : $app;
	}

	private static function normalize(string $url): string {
		// Mehrfache Schraegstriche zusammenziehen, damit '//apps//market' nicht
		// an der Zuordnung vorbeilaeuft, und einen fuehrenden Schraegstrich
		// sicherstellen.
		$url = \preg_replace('#/{2,}#', '/', $url) ?? $url;
		if ($url === '' || $url[0] !== '/') {
			$url = '/' . $url;
		}

		return $url;
	}

	private static function cleanAppId(string $app): string {
		return \str_replace(["\0", '/', '\\', '..'], '', $app);
	}
}
