<?php

declare(strict_types=1);

namespace OCP;

interface IUserManager {
	public function get(string $uid): ?IUser;
	public function userExists(string $uid): bool;
	public function createUser(string $uid, string $password): ?IUser;
	public function checkPassword(string $loginName, string $password);
	public function search(string $pattern, ?int $limit = null, ?int $offset = null): array;
	public function countUsers(): array;
}
