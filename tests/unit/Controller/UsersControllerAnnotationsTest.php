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

use OCA\Guests\Controller\UsersController;
use Test\TestCase;

/**
 * Das AppFramework des Kerns steuert Zugriffs- und CSRF-Prüfung über
 * Annotationen im DocComment der Controller-Methode. Dieser Test hält den
 * Vertrag fest, ohne den Controller zu bauen: Gastkonten anlegen darf jede
 * angemeldete Person (NoAdminRequired), aber nur mit gültigem Anfrage-Token
 * (keine CSRF-Ausnahme).
 */
class UsersControllerAnnotationsTest extends TestCase {
	private function getCreateDocComment(): string {
		$method = new \ReflectionMethod(UsersController::class, 'create');
		$docComment = $method->getDocComment();
		$this->assertIsString($docComment, 'create() braucht einen DocComment mit den Annotationen');
		return $docComment;
	}

	public function testCreateIsAllowedForNonAdmins(): void {
		$this->assertMatchesRegularExpression(
			'/@NoAdminRequired\b/',
			$this->getCreateDocComment()
		);
	}

	public function testCreateKeepsCsrfProtection(): void {
		// Der Kern liest jedes @Großwort im DocComment als Annotation - die
		// Ausnahme darf also auch nicht als Nebenbemerkung dort stehen.
		$this->assertDoesNotMatchRegularExpression(
			'/@NoCSRFRequired\b/',
			$this->getCreateDocComment()
		);
	}
}
