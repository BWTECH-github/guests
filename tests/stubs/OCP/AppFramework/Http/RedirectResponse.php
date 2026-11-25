<?php

declare(strict_types=1);

namespace OCP\AppFramework\Http;

class RedirectResponse extends Response {
	private string $redirectURL;

	public function __construct(string $redirectURL) {
		$this->redirectURL = $redirectURL;
		$this->status = 303;
	}

	public function getRedirectURL(): string {
		return $this->redirectURL;
	}
}
