<?php

declare(strict_types=1);

namespace OCP;

interface IUser {
	public function getUID(): string;
	public function getDisplayName(): string;
	public function getEMailAddress(): ?string;
	public function setEMailAddress(?string $mailAddress): void;
	public function getBackendClassName(): string;
	public function isEnabled(): bool;
	public function setEnabled(bool $enabled = true): void;
	public function delete(): bool;
}
