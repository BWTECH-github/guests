<?php

declare(strict_types=1);

/**
 * @author Ilja Neumann <ineumann@owncloud.com>
 * @author Thomas Heinisch <t.heinisch@bw-tech.de>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
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
 * @package OCA\Guests
 */
class AppWhitelist {
	public const CORE_WHITELIST = ',core,files,dav,federatedfilesharing,guests,encryption,files_primary_s3,files_antivirus,files_external,files_external_dropbox,files_external_ftp,files_ldap_home,files_onedrive,sharepoint,files_external_s3,windows_network_drive,admin_audit,firewall,ransomware_protection';
	public const DEFAULT_WHITELIST = 'settings,avatar,files_trashbin,files_versions,files_sharing,files_texteditor,activity,firstrunwizard,gallery,notifications,password_policy,oauth2,files_pdfviewer,files_mediaviewer,richdocuments,onlyoffice,wopi,oco_selfservice,twofactor_totp,impersonate';

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

			if (!\in_array($app, $whitelist, true)) {
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

		return \explode(',', $whitelist);
	}

	/**
	 * Core has \OC::$REQUESTEDAPP but it isn't set until the routes are matched
	 * taken from \OC\Route\Router::match()
	 */
	private static function getRequestedApp(string $url): string|false {
		if (str_starts_with($url, '/apps/')) {
			// empty string / 'apps' / $app / rest of the route
			$parts = \explode('/', $url, 4);
			return \OC_App::cleanAppId($parts[2] ?? '');
		} elseif (str_starts_with($url, '/core/')) {
			return 'core';
		} elseif (str_starts_with($url, '/settings/')) {
			return 'settings';
		} elseif (str_starts_with($url, '/avatar/')) {
			return 'avatar';
		} elseif (str_starts_with($url, '/heartbeat')) {
			return 'heartbeat';
		} elseif (str_starts_with($url, '/remote.php/dav')) {
			return 'dav';
		} elseif (str_starts_with($url, '/dav/comments')) {
			return 'comments';
		} elseif (str_starts_with($url, '/index.php/apps/')) {
			// Handle /index.php/apps/{appname} - essential for directory listing
			$parts = \explode('/', $url, 5);
			return \OC_App::cleanAppId($parts[3] ?? '');
		} elseif (str_starts_with($url, '/index.php/')) {
			// Handle other /index.php endpoints (AJAX, API, etc.)
			return 'core';
		} elseif (str_starts_with($url, '/ocs/')) {
			// Handle OCS API endpoints
			return 'core';
		} elseif (str_starts_with($url, '/')) {
			// Root URL and other paths - default to files for guest users
			return 'files';
		}
		return false;
	}
}