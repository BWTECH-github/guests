<?php

declare(strict_types=1);

namespace OCP;

class Defaults {
	public function getName(): string {
		return 'ownCloud';
	}
	
	public function getSlogan(): string {
		return '';
	}
	
	public function getBaseUrl(): string {
		return 'https://owncloud.com';
	}
	
	public function getTextColorPrimary(): string {
		return '#000000';
	}
	
	public function getMailHeaderColor(): string {
		return '#1d2d44';
	}
}
