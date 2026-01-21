<?php

declare(strict_types=1);

/**
 * @author Ilja Neumann <ineumann@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Thomas Heinisch <t.heinisch@bw-tech.de>
 * @author Vincent Petry <pvince81@owncloud.com>
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

use OCP\Defaults;
use OCP\IL10N;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Mail\IMailer;
use OCP\Share;
use OCP\Template;
use OCP\Util;

class Mail {
	private ILogger $logger;

	private IUserSession $userSession;

	private IMailer $mailer;

	private Defaults $defaults;

	private IL10N $l10n;

	private ?IConfig $config;

	private IURLGenerator $urlGenerator;

	private IUserManager $userManager;

	public function __construct(
		ILogger $logger,
		IUserSession $userSession,
		IMailer $mailer,
		Defaults $defaults,
		IL10N $l10n,
		?IConfig $config,
		IUserManager $userManager,
		IURLGenerator $urlGenerator
	) {
		$this->logger = $logger;
		$this->userSession = $userSession;
		$this->mailer = $mailer;
		$this->defaults = $defaults;
		$this->l10n = $l10n;
		$this->config = $config;
		$this->userManager = $userManager;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * Sends out a reset password mail if the user is a guest and does not have
	 * a password set, yet.
	 *
	 * @param Share\IShare $share
	 * @param string $uid
	 * @param string $token
	 *
	 * @throws \Exception
	 */
	public function sendGuestInviteMail(Share\IShare $share, string $uid, string $token): void {
		$shareWith = $share->getSharedWith();
		$shareWithUser = $this->userManager->get($shareWith);
		if ($shareWithUser === null) {
			throw new \Exception("User '$shareWith' not found");
		}
		$shareWithEmail = $shareWithUser->getEMailAddress();

		$uidUser = $this->userManager->get($uid);
		$replyTo = $uidUser?->getEMailAddress();

		$currentUser = $this->userSession->getUser();
		if ($currentUser === null) {
			throw new \Exception('No user in session');
		}
		$senderDisplayName = $currentUser->getDisplayName();

		$registerLink = $this->urlGenerator->linkToRouteAbsolute(
			'guests.register.showPasswordForm',
			['email' => $shareWithEmail, 'token' => $token]
		);

		$this->logger->debug("sending invite to $shareWith: $registerLink", ['app' => 'guests']);

		$filename = \trim($share->getTarget(), '/');
		$defaultLanguage = $this->getDefaultLanguage();
		$l10n = $defaultLanguage ?? $this->l10n;
		$subject = (string)$l10n->t('%s shared »%s« with you', [$senderDisplayName, $filename]);
		$expiration = $share->getExpirationDate();
		$expirationTimestamp = null;
		if ($expiration instanceof \DateTime) {
			try {
				$expirationTimestamp = $expiration->getTimestamp();
			} catch (\Exception $e) {
				$this->logger->error("Couldn't read date: " . $e->getMessage(), ['app' => 'sharing']);
			}
		}

		$link = $this->urlGenerator->linkToRouteAbsolute(
			'files.viewcontroller.showFile',
			['fileId' => $share->getNode()->getId()]
		);

		[$htmlBody, $textBody] = $this->createMailBody(
			$filename,
			$link,
			$registerLink,
			$this->defaults->getName(),
			$senderDisplayName,
			$expirationTimestamp,
			$shareWithEmail ?? '',
			$defaultLanguage
		);

		try {
			$message = $this->mailer->createMessage();
			$message->setTo([$shareWithEmail => $shareWith]);
			$message->setSubject($subject);
			$message->setHtmlBody($htmlBody);
			$message->setPlainBody($textBody);
			$message->setFrom([
				Util::getDefaultEmailAddress('sharing-noreply') =>
					(string)$this->l10n->t('%s via %s', [
						$senderDisplayName,
						$this->defaults->getName()
					]),
			]);

			if ($replyTo !== null) {
				$message->setReplyTo([$replyTo]);
			}

			$this->mailer->send($message);
		} catch (\Exception $e) {
			$this->logger->error("Failed to send reset email: " . $e->getMessage(), ['app' => 'guests']);
			throw new \Exception($this->l10n->t(
				'Couldn\'t send reset email. Please contact your administrator.'
			));
		}
	}

	/**
	 * @throws \Exception
	 */
	public function sendGuestPlainInviteMail(string $shareWith, string $uid, string $token): void {
		$shareWithUser = $this->userManager->get($shareWith);
		if ($shareWithUser === null) {
			throw new \Exception("User '$shareWith' not found");
		}
		$shareWithEmail = $shareWithUser->getEMailAddress();

		$uidUser = $this->userManager->get($uid);
		$replyTo = $uidUser?->getEMailAddress();

		$currentUser = $this->userSession->getUser();
		if ($currentUser === null) {
			throw new \Exception('No user in session');
		}
		$senderDisplayName = $currentUser->getDisplayName();

		$registerLink = $this->urlGenerator->linkToRouteAbsolute(
			'guests.register.showPasswordForm',
			['email' => $shareWithEmail, 'token' => $token]
		);

		$this->logger->debug("sending invite to $shareWith: $registerLink", ['app' => 'guests']);

		$defaultLanguage = $this->getDefaultLanguage();
		$l10n = $defaultLanguage ?? $this->l10n;
		$subject = (string)$l10n->t('%s invited you', [$senderDisplayName]);

		[$htmlBody, $textBody] = $this->createMailBody(
			null,
			null,
			$registerLink,
			$this->defaults->getName(),
			$senderDisplayName,
			null,
			$shareWithEmail ?? '',
			$defaultLanguage
		);

		try {
			$message = $this->mailer->createMessage();
			$message->setTo([$shareWithEmail => $shareWith]);
			$message->setSubject($subject);
			$message->setHtmlBody($htmlBody);
			$message->setPlainBody($textBody);
			$message->setFrom([
				Util::getDefaultEmailAddress('sharing-noreply') =>
					(string)$this->l10n->t('%s via %s', [
						$senderDisplayName,
						$this->defaults->getName()
					]),
			]);

			if ($replyTo !== null) {
				$message->setReplyTo([$replyTo]);
			}

			$this->mailer->send($message);
		} catch (\Exception $e) {
			$this->logger->error("Failed to send reset email: " . $e->getMessage(), ['app' => 'guests']);
			throw new \Exception($this->l10n->t(
				'Couldn\'t send reset email. Please contact your administrator.'
			));
		}
	}

	/**
	 * create mail body for plain text and html mail
	 *
	 * @param string|null $filename the shared file
	 * @param string|null $link link to the shared file
	 * @param string $passwordLink link to set password
	 * @param string $cloudName name of the cloud instance
	 * @param string $displayName sender display name
	 * @param int|null $expiration expiration date (timestamp)
	 * @param string $guestEmail guest email address
	 * @param IL10N|null $overrideL10n language to be used
	 * @return array{0: string, 1: string} an array of the html mail body and the plain text mail body
	 */
	private function createMailBody(
		?string $filename,
		?string $link,
		string $passwordLink,
		string $cloudName,
		string $displayName,
		?int $expiration,
		string $guestEmail,
		?IL10N $overrideL10n = null
	): array {
		$formattedDate = $expiration !== null ? $this->l10n->l('date', $expiration) : null;
		$l10n = $overrideL10n ?? $this->l10n;

		$html = new Template('guests', 'mail/invite', '', false, $l10n->getLanguageCode());
		$html->assign('link', $link);
		$html->assign('password_link', $passwordLink);
		$html->assign('cloud_name', $cloudName);
		$html->assign('user_displayname', $displayName);
		$html->assign('filename', $filename);
		$html->assign('expiration', $formattedDate);
		$html->assign('guestEmail', $guestEmail);
		$htmlMail = $html->fetchPage();

		$plainText = new Template('guests', 'mail/altinvite', '', false, $l10n->getLanguageCode());
		$plainText->assign('link', $link);
		$plainText->assign('password_link', $passwordLink);
		$plainText->assign('cloud_name', $cloudName);
		$plainText->assign('user_displayname', $displayName);
		$plainText->assign('filename', $filename);
		$plainText->assign('expiration', $formattedDate);
		$plainText->assign('guestEmail', $guestEmail);
		$plainTextMail = $plainText->fetchPage();

		return [$htmlMail, $plainTextMail];
	}

	/**
	 * get default_language if defined in config.php
	 * @return IL10N|null
	 */
	private function getDefaultLanguage(): ?IL10N {
		if ($this->config === null) {
			return null;
		}

		$defaultLang = $this->config->getSystemValue('default_language', false);
		if ($defaultLang !== false) {
			return \OC::$server->getL10N('lib', $defaultLang);
		}
		return null;
	}
}