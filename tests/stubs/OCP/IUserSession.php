<?php

declare(strict_types=1);

namespace OCP;

interface IUserSession {
    public function getUser(): ?IUser;
    public function isLoggedIn(): bool;
    public function login(string $uid, string $password): bool;
    public function logout(): void;
}