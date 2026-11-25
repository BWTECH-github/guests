<?php

declare(strict_types=1);

/**
 * owncloud.online
 *
 * @copyright (C) 2026 BW-Tech GmbH
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

namespace OCA\Guests\Tests\Unit\Controller;

use OCA\Guests\Controller\RegisterController;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Mail\IMailer;
use OCP\Security\ISecureRandom;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

/**
 * Einladungslink der Gäste: Das Token gilt nur genau gleich und nur
 * REGISTER_TOKEN_TTL Sekunden ab dem letzten Versand.
 */
class RegisterControllerTest extends TestCase {
	private const GUEST = 'gast@example.org';
	private const TOKEN = 'Abc123Def456Ghi789Jkl';

	private IConfig&MockObject $config;
	private IUserManager&MockObject $userManager;
	private RegisterController $controller;

	/** @var array<string, string> Benutzerwerte des Gasts (app/key => Wert) */
	private array $values = [];

	protected function setUp(): void {
		parent::setUp();
		$this->values = [
			'owncloud/isGuest' => '1',
			'guests/registerToken' => self::TOKEN,
			'guests/created' => (string)\time(),
		];
		$this->config = $this->createMock(IConfig::class);
		$this->config->method('getUserValue')->willReturnCallback(
			fn (string $uid, string $app, string $key, string $default = ''): string =>
				$uid === self::GUEST ? ($this->values["$app/$key"] ?? $default) : $default
		);
		$this->userManager = $this->createMock(IUserManager::class);

		$l10n = $this->createMock(IL10N::class);
		$l10n->method('t')->willReturnArgument(0);
		$mailer = $this->createMock(IMailer::class);
		$mailer->method('validateMailAddress')->willReturn(true);
		$urlGenerator = $this->createMock(IURLGenerator::class);
		$urlGenerator->method('linkToRouteAbsolute')->willReturnArgument(0);

		$this->controller = new RegisterController(
			'guests',
			$this->createMock(IRequest::class),
			$this->userManager,
			$this->createMock(IGroupManager::class),
			$l10n,
			$this->config,
			$this->createMock(ISecureRandom::class),
			$mailer,
			$urlGenerator
		);
	}

	protected function tearDown(): void {
		$_POST = [];
		parent::tearDown();
	}

	private function formErrors(string $token): array {
		$response = $this->controller->showPasswordForm(self::GUEST, $token);
		$this->assertInstanceOf(TemplateResponse::class, $response);
		return $response->getParams()['messages'];
	}

	private function register(string $token): TemplateResponse|RedirectResponse {
		$_POST = ['email' => self::GUEST, 'token' => $token, 'password' => 'Ein-sicheres-Kennwort-1'];
		return $this->controller->register();
	}

	public function testFreshTokenIsAccepted(): void {
		$this->assertSame([], $this->formErrors(self::TOKEN));

		$user = $this->createMock(IUser::class);
		$user->expects($this->once())->method('setPassword')->with('Ein-sicheres-Kennwort-1');
		$this->userManager->method('get')->with(self::GUEST)->willReturn($user);
		$this->config->expects($this->exactly(2))->method('deleteUserValue');

		$this->assertInstanceOf(RedirectResponse::class, $this->register(self::TOKEN));
	}

	public function testWrongTokenIsRejected(): void {
		$this->assertArrayHasKey('token', $this->formErrors('Abc123Def456Ghi789Jkx'));
		$this->userManager->expects($this->never())->method('get');
		$this->assertInstanceOf(TemplateResponse::class, $this->register('Abc123Def456Ghi789Jkx'));
	}

	public function testEmptyTokenIsRejected(): void {
		$this->values['guests/registerToken'] = '';
		$this->assertArrayHasKey('token', $this->formErrors(''));
		$this->assertInstanceOf(TemplateResponse::class, $this->register(''));
	}

	public function testExpiredTokenIsRejected(): void {
		$this->values['guests/created'] = (string)(\time() - RegisterController::REGISTER_TOKEN_TTL - 60);
		$this->assertArrayHasKey('token', $this->formErrors(self::TOKEN));

		$this->userManager->expects($this->never())->method('get');
		$response = $this->register(self::TOKEN);
		$this->assertInstanceOf(TemplateResponse::class, $response);
		$this->assertArrayHasKey('token', $response->getParams()['messages']);
	}

	public function testResentInvitationRestartsValidity(): void {
		$this->values['guests/created'] = (string)(\time() - RegisterController::REGISTER_TOKEN_TTL - 60);
		$this->values['guests/registerTokenTime'] = (string)(\time() - 3600);
		$this->assertSame([], $this->formErrors(self::TOKEN));
	}

	public function testTokenWithoutTimestampIsRejected(): void {
		unset($this->values['guests/created']);
		$this->assertArrayHasKey('token', $this->formErrors(self::TOKEN));
	}

	/**
	 * Unbekannte Adresse und bestehender Gast mit falschem Token liefern
	 * dieselbe Antwort; sonst ließe sich aufzählen, wer Gast ist.
	 */
	public function testUnknownAddressLooksLikeWrongToken(): void {
		$known = $this->formErrors('falsch');
		$response = $this->controller->showPasswordForm('nichtda@example.org', 'falsch');
		$this->assertSame($known, $response->getParams()['messages']);
		$this->assertSame(['token'], array_keys($known));
	}

	public function testNonGuestWithTokenIsRejected(): void {
		$this->values['owncloud/isGuest'] = '0';
		$this->assertArrayHasKey('token', $this->formErrors(self::TOKEN));
		$this->userManager->expects($this->never())->method('get');
		$this->assertInstanceOf(TemplateResponse::class, $this->register(self::TOKEN));
	}
}
