<?php

declare(strict_types=1);

namespace OCP;

interface IGroupManager {
    public function get(string $gid): ?IGroup;
    public function groupExists(string $gid): bool;
    public function createGroup(string $gid): ?IGroup;
    public function isAdmin(string $uid): bool;
    public function isInGroup(string $uid, string $gid): bool;
    public function getUserGroups(IUser $user): array;
    public function getUserGroupIds(IUser $user): array;
}