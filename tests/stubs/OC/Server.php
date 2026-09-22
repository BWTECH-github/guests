<?php

declare(strict_types=1);

/**
 * Minimal stand-in for the core service container, enough for the parts of the
 * guests app that reach for \OC::$server directly.
 *
 * Modified by BW-Tech GmbH
 */

class OC {
	/** @var object|null */
	public static $server = null;
}
