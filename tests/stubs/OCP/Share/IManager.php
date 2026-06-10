<?php

declare(strict_types=1);

/**
 * Modified by BW-Tech GmbH
 */

namespace OCP\Share;

interface IManager {
	public function deleteShare(IShare $share);
}
