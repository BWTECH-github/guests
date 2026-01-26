<?php

declare(strict_types=1);

namespace OCP\Mail;

interface IMessage {
    public function setSubject(string $subject);
    public function setFrom(array $addresses);
    public function setTo(array $recipients);
    public function setPlainBody(string $body);
    public function setHtmlBody(string $body);
}