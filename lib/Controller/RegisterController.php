<?php

declare(strict_types=1);

/**
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

namespace OCA\Guests\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\Mail\IMailer;
use OCP\Security\ISecureRandom;

class RegisterController extends Controller {
	/**
	 * Gültigkeit des Einladungslinks in Sekunden (14 Tage). Jede neue
	 * Freigabe an den Gast verschickt den Link erneut und startet die Frist
	 * neu (Hooks::postShareHook).
	 */
	public const REGISTER_TOKEN_TTL = 14 * 24 * 60 * 60;

	// Note: $request is inherited from parent Controller without type declaration
	// due to PHP 8.4 stricter inheritance rules

	private IUserManager $userManager;

	private IL10N $l10n;

	private IConfig $config;

	private IGroupManager $groupManager;

	private ISecureRandom $secureRandom;

	private IMailer $mailer;

	private IURLGenerator $urlGenerator;

	/**
	 * RegisterController constructor.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IUserManager $userManager
	 * @param IGroupManager $groupManager
	 * @param IL10N $l10n
	 * @param IConfig $config
	 * @param ISecureRandom $secureRandom
	 * @param IMailer $mailer
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct(
		string $appName,
		IRequest $request,
		IUserManager $userManager,
		IGroupManager $groupManager,
		IL10N $l10n,
		IConfig $config,
		ISecureRandom $secureRandom,
		IMailer $mailer,
		IURLGenerator $urlGenerator
	) {
		parent::__construct($appName, $request);

		$this->request = $request;
		$this->userManager = $userManager;
		$this->l10n = $l10n;
		$this->config = $config;
		$this->groupManager = $groupManager;
		$this->secureRandom = $secureRandom;
		$this->mailer = $mailer;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * Show the password form
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @param string $email
	 * @param string $token
	 *
	 * @return TemplateResponse
	 */
	public function showPasswordForm(string $email, string $token): TemplateResponse {
		$errorMessages = [];
		$userId = \strtolower($email);

		if (empty($email) || !$this->mailer->validateMailAddress($email)) {
			$errorMessages['email'] = (string)$this->l10n->t(
				'Invalid mail address'
			);
		}

		// Eine Meldung für alle Fälle: Ob es das Gastkonto gibt, bleibt für
		// Aufrufer ohne gültigen Link unsichtbar.
		if (!$this->isRegisterTokenValid($userId, $token)) {
			$errorMessages['token'] = (string)$this->l10n->t(
				'The token is invalid'
			);
		}

		$parameters = [];
		$parameters['email'] = $email;
		$parameters['messages'] = $errorMessages;
		$parameters['token'] = $token;
		$parameters['postAction'] =
			$this->urlGenerator->linkToRouteAbsolute('guests.register.register');

		return new TemplateResponse(
			$this->appName,
			'form.password',
			$parameters,
			'guest'
		);
	}

	/**
	 * Perform the registration
	 *
	 * @PublicPage
	 * @NoAdminRequired
	 * @UseSession
	 *
	 * @return TemplateResponse|RedirectResponse
	 */
	public function register(): TemplateResponse|RedirectResponse {
		$email = \trim($_POST['email'] ?? '');
		$token = \trim($_POST['token'] ?? '');
		$password = \trim($_POST['password'] ?? '');
		$userId = \strtolower($email);
		$parameters = [];

		if (empty($email) || !$this->mailer->validateMailAddress($email)) {
			$parameters['messages']['email'] = (string)$this->l10n->t(
				'Invalid mail address'
			);
		}

		if (empty($password)) {
			$parameters['messages']['password'] = (string)$this->l10n->t(
				'Password cannot be empty'
			);
		}

		// only show token error when there are no others
		if (
			empty($parameters['messages']) &&
			!$this->isRegisterTokenValid($userId, $token)
		) {
			$parameters['token'] = $token;
			$parameters['email'] = $email;
			$parameters['messages']['token'] = (string)$this->l10n->t(
				'The token is invalid'
			);
		}

		if (!empty($parameters['messages'])) {
			// Die Vorlage braucht das Formularziel auch im Fehlerfall; ohne
			// diese Zuweisung schrieb jeder POST mit ungültigem Token
			// „Undefined array key postAction“ ins Protokoll.
			$parameters['postAction'] =
				$this->urlGenerator->linkToRouteAbsolute('guests.register.register');
			return new TemplateResponse(
				$this->appName,
				'form.password',
				$parameters,
				'guest'
			);
		}

		try {
			$user = $this->userManager->get($userId);
			if ($user !== null) {
				$user->setPassword($password);
			}
		} catch (\Exception $e) {
			$parameters['email'] = $email;
			$parameters['messages']['password'] = $e->getMessage();
			$parameters['token'] = $token;
			$parameters['postAction'] =
				$this->urlGenerator->linkToRouteAbsolute('guests.register.register');
			return new TemplateResponse(
				$this->appName,
				'form.password',
				$parameters,
				'guest'
			);
		}

		$this->config->deleteUserValue($userId, 'guests', 'registerToken');
		$this->config->deleteUserValue($userId, 'guests', 'registerTokenTime');

		// redirect to login
		return new RedirectResponse($this->urlGenerator->linkToRouteAbsolute('core.login.showLoginForm'));
	}

	/**
	 * Prüft das Einladungs-Token zeitkonstant und mit Ablauf. Ausgangspunkt der
	 * Frist ist der letzte Versand (registerTokenTime); ältere Einladungen ohne
	 * diesen Wert zählen ab dem Anlegen des Gastkontos (created). Nur
	 * Gastkonten haben einen Einladungslink.
	 */
	private function isRegisterTokenValid(string $userId, string $token): bool {
		if (!(bool)$this->config->getUserValue($userId, 'owncloud', 'isGuest', '')) {
			return false;
		}
		$storedToken = (string)$this->config->getUserValue(
			$userId,
			'guests',
			'registerToken',
			''
		);
		if ($storedToken === '' || $token === '' || !\hash_equals($storedToken, $token)) {
			return false;
		}

		$issuedAt = (int)$this->config->getUserValue($userId, 'guests', 'registerTokenTime', '');
		if ($issuedAt <= 0) {
			$issuedAt = (int)$this->config->getUserValue($userId, 'guests', 'created', '');
		}

		return $issuedAt > 0 && \time() - $issuedAt <= self::REGISTER_TOKEN_TTL;
	}
}
