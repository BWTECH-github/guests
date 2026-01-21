<?php

declare(strict_types=1);

/**
 * ownCloud
 *
 * @author Viktar Dubiniuk <dubiniuk@owncloud.com>
 * @copyright (C) 2019 ownCloud GmbH
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

namespace OCA\Guests\Tests\Unit;

use OCA\Guests\Hooks;
use OCA\Guests\Mail;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Share\IShare;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

/**
 * Class HooksTest
 *
 * @package OCA\Guests\Tests\Unit
 */
class HooksTest extends TestCase {
	public const GUEST_UID = 'me@example.org';

	private ILogger&MockObject $logger;

	private IUserSession&MockObject $userSession;

	private Mail&MockObject $mail;

	private IConfig&MockObject $config;

	private Hooks $hooks;

	public function setUp(): void {
		$this->logger = $this->createMock(ILogger::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->mail = $this->createMock(Mail::class);
		$this->config = $this->createMock(IConfig::class);
		$this->hooks = new Hooks(
			$this->logger,
			$this->userSession,
			$this->mail,
			$this->config
		);
	}

	public function testUnsupportedShareType(): void {
		$shareMock = $this->createMock(IShare::class);
		$shareMock->method('getNodeType')->willReturn('key');

		$this->logger->expects($this->once())->method('debug')->willReturnCallback(
			function (string $message, array $params) use ($shareMock): void {
				$itemType = $shareMock->getNodeType();
				$this->assertEquals("ignoring share for itemType '$itemType'", $message);
			}
		);

		$this->hooks->handlePostShare($shareMock);
	}

	public function testNonGuestUser(): void {
		$shareMock = $this->createMock(IShare::class);
		$shareMock->method('getNodeType')->willReturn('file');
		$shareMock->method('getSharedWith')->willReturn(self::GUEST_UID);

		$this->config->expects($this->once())->method('getUserValue')
			->with(self::GUEST_UID, 'owncloud', 'isGuest', false)
			->willReturn(false);

		$this->logger->expects($this->once())->method('debug')->willReturnCallback(
			function (string $message, array $params) use ($shareMock): void {
				$shareWith = $shareMock->getSharedWith();
				$this->assertEquals("ignoring user '$shareWith', not a guest", $message);
			}
		);

		$this->hooks->handlePostShare($shareMock);
	}

	public function testPostShareHookWithNoUser(): void {
		$shareMock = $this->createMock(IShare::class);
		$shareMock->method('getNodeType')->willReturn('file');
		$shareMock->method('getSharedWith')->willReturn(self::GUEST_UID);

		$this->config->expects($this->once())->method('getUserValue')
			->with(self::GUEST_UID, 'owncloud', 'isGuest', false)
			->willReturn(true);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('post_share hook triggered without user in session');

		$this->hooks->handlePostShare($shareMock);
	}

	public function testPostShareHookForRegisteredGuest(): void {
		$shareMock = $this->createMock(IShare::class);
		$shareMock->method('getNodeType')->willReturn('file');
		$shareMock->method('getSharedWith')->willReturn(self::GUEST_UID);

		// Replace withConsecutive with callback-based approach for PHPUnit 10+ compatibility
		$callCount = 0;
		$this->config->method('getUserValue')
			->willReturnCallback(function (
				string $userId,
				string $app,
				string $key,
				mixed $default = null
			) use (&$callCount): mixed {
				$callCount++;
				if ($callCount === 1) {
					$this->assertEquals(self::GUEST_UID, $userId);
					$this->assertEquals('owncloud', $app);
					$this->assertEquals('isGuest', $key);
					return true;
				}
				if ($callCount === 2) {
					$this->assertEquals(self::GUEST_UID, $userId);
					$this->assertEquals('guests', $app);
					$this->assertEquals('registerToken', $key);
					return null;
				}
				return $default;
			});

		$userMock = $this->createMock(IUser::class);
		$this->userSession->method('getUser')->willReturn($userMock);

		$this->mail->expects($this->never())->method('sendGuestInviteMail');

		$this->hooks->handlePostShare($shareMock);
	}

	public function testPostShareHookForNewGuest(): void {
		$shareMock = $this->createMock(IShare::class);
		$shareMock->method('getNodeType')->willReturn('file');
		$shareMock->method('getSharedWith')->willReturn(self::GUEST_UID);

		// Replace withConsecutive with callback-based approach for PHPUnit 10+ compatibility
		$callCount = 0;
		$this->config->method('getUserValue')
			->willReturnCallback(function (
				string $userId,
				string $app,
				string $key,
				mixed $default = null
			) use (&$callCount): mixed {
				$callCount++;
				if ($callCount === 1) {
					$this->assertEquals(self::GUEST_UID, $userId);
					$this->assertEquals('owncloud', $app);
					$this->assertEquals('isGuest', $key);
					return true;
				}
				if ($callCount === 2) {
					$this->assertEquals(self::GUEST_UID, $userId);
					$this->assertEquals('guests', $app);
					$this->assertEquals('registerToken', $key);
					return 'token';
				}
				return $default;
			});

		$userMock = $this->createMock(IUser::class);
		$userMock->method('getUID')->willReturn(self::GUEST_UID);
		$this->userSession->method('getUser')->willReturn($userMock);

		$this->mail->expects($this->once())
			->method('sendGuestInviteMail')
			->with($shareMock, self::GUEST_UID, 'token');

		$this->hooks->handlePostShare($shareMock);
	}
}