<?php

declare(strict_types=1);

namespace OCP\Group\Backend;

use OCP\GroupInterface;

abstract class ABackend implements GroupInterface {
	public function implementsActions(int $actions): bool {
		return (bool)($this->getSupportedActions() & $actions);
	}
	
	protected function getSupportedActions(): int {
		return 0;
	}
}
