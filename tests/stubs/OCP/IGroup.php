<?php

declare(strict_types=1);

namespace OCP;

interface IGroup {
    public function getGID(): string;
    public function getDisplayName(): string;
    public function getUsers(): array;
    public function inGroup(IUser $user): bool;
    public function addUser(IUser $user): void;
    public function removeUser(IUser $user): void;
    public function count(string $search = ''): int;
    public function searchUsers(string $search, ?int $limit = null, ?int $offset = null): array;
    public function delete(): bool;
}