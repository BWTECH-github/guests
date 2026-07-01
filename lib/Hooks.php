<?php

declare(strict_types=1);

/**
 * @author Ilja Neumann <ineumann@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
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

use OCP\IConfig;
use OCP\ILogger;
use OCP\IUserSession;
use OCP\Share\IManager;
use OCP\Share\IShare;

class Hooks {
	private ILogger $logger;

	private IUserSession $userSession;

	private Mail $mail;

	private IConfig $config;

	private IManager $shareManager;

	/**
	 * Hooks constructor.
	 *
	 * @param ILogger $logger
	 * @param IUserSession $userSession
	 * @param Mail $mail
	 * @param IConfig $config
	 * @param IManager $shareManager
	 */
	public function __construct(
		ILogger $logger,
		IUserSession $userSession,
		Mail $mail,
		IConfig $config,
		IManager $shareManager
	) {
		$this->logger = $logger;
		$this->userSession = $userSession;
		$this->mail = $mail;
		$this->config = $config;
		$this->shareManager = $shareManager;
	}

	public function handlePostShare(IShare $share): void {
		$itemType = $share->getNodeType();
		if ($itemType !== 'file'
			&& $itemType !== 'folder'
		) {
			$this->logger->debug(
				"ignoring share for itemType '$itemType'",
				['app' => 'guests']
			);
			return;
		}

		$shareWith = $share->getSharedWith();
		$isGuest = $this->config->getUserValue(
			$shareWith,
			'owncloud',
			'isGuest',
			''
		);

		// Real guests are flagged with isGuest === '1'; anything else (including an
		// unset preference that reads back as null) means the recipient is a regular
		// user, so skip the guest invitation flow to avoid passing a null token to
		// sendGuestInviteMail().
		if ($isGuest !== '1') {
			$this->logger->debug(
				"ignoring user '$shareWith', not a guest",
				['app' => 'guests']
			);

			return;
		}

		$user = $this->userSession->getUser();
		if ($user === null) {
			throw new \Exception(
				'post_share hook triggered without user in session'
			);
		}

		$this->logger->debug(
			"checking if '$shareWith' has a password",
			['app' => 'guests']
		);

		$registerToken = $this->config->getUserValue(
			$shareWith,
			'guests',
			'registerToken',
			''
		);

		try {
			if ($registerToken !== '') {
				$uid = $user->getUID();
				// send invitation
				$this->mail->sendGuestInviteMail(
					$share,
					$uid,
					$registerToken
				);
			}
		} catch (\Exception $ex) {
			$this->logger->error(
				"Guest invitation for '$shareWith' failed after creating the share: "
				. $ex->getMessage(),
				['app' => 'guests']
			);

			try {
				// The share is already persisted before this post-create hook runs.
				$this->shareManager->deleteShare($share);
			} catch (\Exception $rollbackException) {
				$this->logger->critical(
					"Could not roll back failed guest share for '$shareWith': "
					. $rollbackException->getMessage(),
					['app' => 'guests']
				);
			}

			throw $ex;
		}
	}

	/**
	 * Function used to extend global JS config emitted with
	 * OC_Hook::emit('\OCP\Config', 'js', ['array' => &$array]) and available
	 * in JS as oc_appconfig.guests
	 *
	 * @param array<string, mixed> $array holding $array['array'] key with a reference value to config
	 */
	public static function extendJsConfig(array $array): void {
		$blockDomains = \OC::$server->getConfig()->getAppValue('guests', 'blockdomains', '');

		$array['array']['oc_appconfig']['guests'] = [
			'blockdomains' => \explode(',', $blockDomains),
		];
	}
}
