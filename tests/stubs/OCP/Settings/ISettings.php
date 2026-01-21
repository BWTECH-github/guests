<?php

declare(strict_types=1);

namespace OCP\Settings;

interface ISettings {
    public function getForm();
    public function getSection(): string;
    public function getPriority(): int;
}