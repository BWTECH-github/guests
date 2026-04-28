<?php

declare(strict_types=1);

namespace OC;

class Hooks {
	public static function emit(string $signalClass, string $signalName, array $params = []): bool {
		return true;
	}
}
