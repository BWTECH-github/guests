<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2026, BW-Tech GmbH
 * @license GPL-2.0
 *
 * Die Liste haelt Gaeste aus fremden Anwendungen heraus. Sie taugt dafuer nur,
 * wenn die Zuordnung von Pfad zu Anwendung keine Luecke laesst - und die
 * Zuordnung ist reine Zeichenkettenarbeit, also wird sie hier vollstaendig
 * durchgespielt.
 */

namespace OCA\Guests\Tests\Unit;

use OCA\Guests\AppWhitelist;
use OCP\IConfig;
use Test\TestCase;

class AppWhitelistTest extends TestCase {
	/** @var mixed */
	private $restoreServer;

	protected function setUp(): void {
		parent::setUp();
		$this->restoreServer = \OC::$server;
	}

	protected function tearDown(): void {
		\OC::$server = $this->restoreServer;
		parent::tearDown();
	}

	/**
	 * Pfade, die eine Anwendung benennen. Steht sie nicht auf der Liste, wird
	 * abgewiesen - unabhaengig davon, ueber welchen Einstiegspunkt sie
	 * angesprochen wird.
	 */
	public static function namedAppProvider(): array {
		return [
			// Der gewoehnliche Weg.
			['/apps/market/', 'market'],
			['/apps/market', 'market'],
			['/apps/files/ajax/list.php', 'files'],
			['/apps/systemtags/api/v1/tags', 'systemtags'],

			// Ueber index.php. Frueher fiel das in einen Sammelzweig, der
			// pauschal 'core' lieferte, womit jede Anwendung offen stand.
			['/index.php/apps/market/', 'market'],
			['/index.php/apps/files/', 'files'],

			// OCS. Das war die groesste Luecke: der ganze Baum galt als
			// 'core', also war die Schnittstelle jeder installierten
			// Anwendung fuer Gaeste erreichbar.
			['/ocs/v1.php/apps/market/api/v1/search', 'market'],
			['/ocs/v2.php/apps/systemtags/api/v1/tags', 'systemtags'],
			['/ocs/v2.php/apps/notifications/api/v1/notifications', 'notifications'],
			['/ocs/v1.php/apps/files_sharing/api/v1/shares', 'files_sharing'],
			['/index.php/ocs/v2.php/apps/market/api', 'market'],

			// Mehrfache Schraegstriche duerfen nicht an der Zuordnung
			// vorbeilaufen.
			['//apps//market//', 'market'],
			['/ocs//v2.php//apps//market', 'market'],
		];
	}

	/**
	 * @dataProvider namedAppProvider
	 */
	public function testPathNamesTheApp(string $url, string $expected): void {
		$this->assertSame($expected, AppWhitelist::getRequestedApp($url));
	}

	/**
	 * Pfade ohne Anwendungsnamen. Fuer sie gilt die Rechtepruefung des Kerns,
	 * die Liste laesst sie als 'core' durch - genau wie bisher, damit Gaeste
	 * sich weiterhin anmelden, syncen und ihre Dateien sehen koennen.
	 */
	public static function coreProvider(): array {
		return [
			['/', 'core'],
			['', 'core'],
			['/index.php', 'core'],
			['/login', 'core'],
			['/logout', 'core'],
			['/status.php', 'core'],
			['/s/abcdef123456', 'core'],
			['/f/42', 'core'],
			['/public.php', 'core'],
			['/.well-known/caldav', 'core'],
			['/robots.txt', 'core'],
			// OCS-Kernrouten benennen keine Anwendung.
			['/ocs/v1.php/cloud/capabilities', 'core'],
			['/ocs/v2.php/cloud/user', 'core'],
			['/ocs/v1.php/config', 'core'],
			['/ocs-provider/', 'core'],
			// '/apps' ohne Namen dahinter.
			['/apps', 'core'],
			['/apps/', 'core'],
			['/index.php/apps/', 'core'],
			['/core/img/logo.svg', 'core'],
		];
	}

	/**
	 * @dataProvider coreProvider
	 */
	public function testPathNamesNoApp(string $url, string $expected): void {
		$this->assertSame($expected, AppWhitelist::getRequestedApp($url));
	}

	public static function fixedPrefixProvider(): array {
		return [
			['/settings/admin', 'settings'],
			['/settings', 'settings'],
			['/index.php/settings/personal', 'settings'],
			['/avatar/alice/64', 'avatar'],
			['/index.php/avatar/alice/64', 'avatar'],
			['/heartbeat', 'heartbeat'],
			['/index.php/heartbeat', 'heartbeat'],
			['/remote.php/dav/files/alice/', 'dav'],
			['/remote.php/webdav/', 'dav'],
			['/dav/files/alice/', 'dav'],
			['/webdav/', 'dav'],
			// Oeffentlicher Link ueber WebDAV: der Einstiegspunkt wird
			// abgetragen, es bleibt der DAV-Baum.
			['/public.php/webdav', 'dav'],
		];
	}

	/**
	 * @dataProvider fixedPrefixProvider
	 */
	public function testFixedPrefixes(string $url, string $expected): void {
		$this->assertSame($expected, AppWhitelist::getRequestedApp($url));
	}

	/**
	 * Ein Anwendungsname, von dem nach dem Saeubern nichts uebrig bleibt, ist
	 * kein Kernpfad, sondern ein Versuch, an der Liste vorbeizukommen. Frueher
	 * ergab er den leeren String - und der stand wegen des fuehrenden Kommas
	 * in CORE_WHITELIST selbst auf der Liste.
	 */
	public static function traversalProvider(): array {
		return [
			['/apps/../market/'],
			['/apps/..'],
			['/apps/...."],'],
			['/index.php/apps/../'],
			['/ocs/v2.php/apps/../'],
		];
	}

	/**
	 * @dataProvider traversalProvider
	 */
	public function testTraversalIsRejected(string $url): void {
		$app = AppWhitelist::getRequestedApp($url);
		$this->assertNotSame('core', $app, 'a stripped app name must not pass as a core path');
		$this->assertTrue(
			$app === false || !\in_array($app, ['', 'core', 'files'], true),
			'a stripped app name must not land on a whitelisted value, got ' . \var_export($app, true)
		);
	}

	/**
	 * Der leere String darf nicht auf der Liste stehen. Bis hierher stand er
	 * es: CORE_WHITELIST begann mit einem Komma.
	 */
	public function testWhitelistHasNoEmptyEntry(): void {
		\OC::$server = $this->serverWithWhitelist(AppWhitelist::DEFAULT_WHITELIST);

		$whitelist = AppWhitelist::getWhitelist();

		$this->assertNotContains('', $whitelist);
		$this->assertContains('core', $whitelist);
		$this->assertContains('files', $whitelist);
		$this->assertContains('files_sharing', $whitelist);
	}

	/**
	 * Eine vom Administrator gespeicherte Liste mit leeren Feldern darf den
	 * leeren Eintrag nicht zurueckbringen.
	 */
	public function testAdminSuppliedEmptyEntriesAreDropped(): void {
		\OC::$server = $this->serverWithWhitelist(',, ,activity,,');

		$whitelist = AppWhitelist::getWhitelist();

		$this->assertNotContains('', $whitelist);
		$this->assertNotContains(' ', $whitelist);
		$this->assertContains('activity', $whitelist);
	}

	/**
	 * Die eigentliche Aussage: was heute verboten ist, bleibt verboten, und
	 * was ein Gast zum Arbeiten braucht, bleibt erreichbar.
	 */
	public function testForbiddenAppsStayForbiddenOnEveryEntryPoint(): void {
		\OC::$server = $this->serverWithWhitelist(AppWhitelist::DEFAULT_WHITELIST);
		$whitelist = AppWhitelist::getWhitelist();

		$forbidden = [
			'/apps/market/',
			'/index.php/apps/market/',
			'/ocs/v1.php/apps/market/api/v1/search',
			'/ocs/v2.php/apps/systemtags/api/v1/tags',
			'/index.php/ocs/v2.php/apps/market/api',
		];
		foreach ($forbidden as $url) {
			$app = AppWhitelist::getRequestedApp($url);
			$this->assertFalse(
				$app !== false && \in_array($app, $whitelist, true),
				$url . ' must not be reachable for guests (resolved to ' . \var_export($app, true) . ')'
			);
		}

		$allowed = [
			'/', '/login', '/logout', '/index.php', '/status.php',
			'/apps/files/', '/index.php/apps/files/',
			'/remote.php/dav/files/alice/', '/remote.php/webdav/',
			'/settings/personal', '/avatar/alice/64', '/core/img/logo.svg',
			'/ocs/v1.php/cloud/capabilities',
			'/ocs/v1.php/apps/files_sharing/api/v1/shares',
			'/ocs/v2.php/apps/notifications/api/v1/notifications',
			'/s/token', '/f/42',
		];
		foreach ($allowed as $url) {
			$app = AppWhitelist::getRequestedApp($url);
			$this->assertTrue(
				$app !== false && \in_array($app, $whitelist, true),
				$url . ' must stay reachable for guests (resolved to ' . \var_export($app, true) . ')'
			);
		}
	}

	private function serverWithWhitelist(string $configured): object {
		$config = $this->createMock(IConfig::class);
		$config->method('getAppValue')
			->willReturnCallback(static function (string $app, string $key, string $default = '') use ($configured) {
				if ($app === 'guests' && $key === 'whitelist') {
					return $configured;
				}
				return $default;
			});

		return new class ($config) {
			/** @var IConfig */
			private $config;
			public function __construct(IConfig $config) {
				$this->config = $config;
			}
			public function getConfig(): IConfig {
				return $this->config;
			}
		};
	}
}
