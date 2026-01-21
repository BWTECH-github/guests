<?php

declare(strict_types=1);

namespace OCP\Security;

interface ISecureRandom {
    public const CHAR_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const CHAR_LOWER = 'abcdefghijklmnopqrstuvwxyz';
    public const CHAR_DIGITS = '0123456789';
    public const CHAR_SYMBOLS = '!&quot;#$%&\\\'()*+,-./:;<=>?@[\\]^_`{|}~';
    
    public function generate(int $length, string $characters = ''): string;
}