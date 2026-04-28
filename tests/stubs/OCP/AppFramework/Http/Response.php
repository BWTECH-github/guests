<?php

declare(strict_types=1);

namespace OCP\AppFramework\Http;

class Response {
	protected int $status = 200;
	protected array $headers = [];
	
	public function setStatus(int $status): Response {
		$this->status = $status;
		return $this;
	}
	
	public function getStatus(): int {
		return $this->status;
	}
	
	public function addHeader(string $name, string $value): Response {
		$this->headers[$name] = $value;
		return $this;
	}
}
