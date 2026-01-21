<?php

declare(strict_types=1);

namespace OCP;

interface IRequest {
    public function getParam(string $key, $default = null);
    public function getParams(): array;
    public function getMethod(): string;
    public function getHeader(string $name): string;
    public function getRemoteAddress(): string;
    public function getServerHost(): string;
}