<?php

declare(strict_types=1);

namespace OCP\Mail;

interface IMailer {
	public function createMessage();
	public function send($message): array;
	public function validateMailAddress(string $email): bool;
}
