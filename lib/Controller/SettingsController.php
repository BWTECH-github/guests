<?php

declare(strict_types=1);

/**
 * @author Ilja Neumann <ineumann@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
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

namespace OCA\Guests\Controller;

use OCA\Guests\AppWhitelist;
use OCA\Guests\GroupBackend;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;

/**
 * Class SettingsController is used to handle configuration changes on the
 * settings page
 *
 * @package OCA\Guests\Controller
 */
class SettingsController extends Controller {
	private ?string $userId;

	private IConfig $config;

	private IL10N $l10n;

	public function __construct(
		string $AppName,
		IRequest $request,
		?string $UserId,
		IConfig $config,
		IL10N $l10n
	) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->config = $config;
		$this->l10n = $l10n;
	}

	/**
	 * AJAX handler for getting the config
	 *
	 * @return DataResponse with the current config
	 */
	public function getConfig(): DataResponse {
		$useWhitelist = $this->config->getAppValue('guests', 'usewhitelist', 'true');
		if ($useWhitelist === 'true' || $useWhitelist === true) {
			$useWhitelist = true;
		} else {
			$useWhitelist = false;
		}
		$whitelistValue = $this->config->getAppValue('guests', 'whitelist', AppWhitelist::DEFAULT_WHITELIST);
		$whitelist = \explode(',', $whitelistValue);
		return new DataResponse([
			'group' => $this->config->getAppValue('guests', 'group', GroupBackend::DEFAULT_NAME),
			'useWhitelist' => $useWhitelist,
			'shareBlockDomains' => \OC::$server->getConfig()->getAppValue('guests', 'blockdomains', ''),
			'whitelist' => $whitelist,
		]);
	}

	/**
	 * AJAX handler for setting the config
	 *
	 * @param string $group
	 * @param string $useWhitelist
	 * @param array<string> $whitelist
	 * @param string $shareBlockDomains
	 * @return DataResponse
	 */
	public function setConfig(string $group, string $useWhitelist, array $whitelist, string $shareBlockDomains): DataResponse {
		if (empty($group)) {
			return new DataResponse([
				'status' => 'error',
				'data' => [
					'message' => $this->l10n->t('Group name must not be empty.')
				],
			]);
		}

		$newWhitelist = [];
		foreach ($whitelist as $app) {
			$newWhitelist[] = \trim($app);
		}
		$newWhitelistStr = \implode(',', $newWhitelist);
		$this->config->setAppValue('guests', 'group', $group);
		$this->config->setAppValue('guests', 'usewhitelist', $useWhitelist);
		$this->config->setAppValue('guests', 'whitelist', $newWhitelistStr);
		$this->config->setAppValue('guests', 'blockdomains', $shareBlockDomains);

		return new DataResponse([
			'status' => 'success',
			'data' => [
				'message' => $this->l10n->t('Saved')
			],
		]);
	}

	/**
	 * AJAX handler for getting whitelisted apps
	 *
	 * @return array<string, mixed> whitelisted apps
	 */
	public function getWhitelist(): array {
		return [
			'isGuest' => false,
			'enabled' => $this->config->getAppValue('guests', 'usewhitelist', 'true') === 'true',
			'apps' => AppWhitelist::getWhitelist()
		];
	}

	/**
	 * AJAX handler for resetting the whitelisted apps
	 *
	 * @return DataResponse with the reset whitelist
	 */
	public function resetWhitelist(): DataResponse {
		$this->config->setAppValue('guests', 'whitelist', AppWhitelist::DEFAULT_WHITELIST);
		return new DataResponse([
			'whitelist' => \explode(',', AppWhitelist::DEFAULT_WHITELIST),
		]);
	}
}