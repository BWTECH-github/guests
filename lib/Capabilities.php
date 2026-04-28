<?php

declare(strict_types=1);

/**
 * @author Jan Ackermann <jackermann@owncloud.com>
 *
 * @copyright Copyright (c) 2021, ownCloud GmbH
 * Modified by BW-Tech GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Guests;

use OCP\Capabilities\ICapability;

/**
 * Class Capabilities
 *
 * @package OCA\Guests
 */
class Capabilities implements ICapability {
	/**
	 * @return array<string, array<string, bool>>
	 */
	public function getCapabilities(): array {
		return [
			'guests_v1' =>
				['enabled' => true],
		];
	}
}
