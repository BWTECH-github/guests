<?php

declare(strict_types=1);

namespace OCP;

interface IL10N {
	public function t(string $text, $parameters = []): string;
	public function n(string $text_singular, string $text_plural, int $count, array $parameters = []): string;
	public function getLanguageCode(): string;
}
