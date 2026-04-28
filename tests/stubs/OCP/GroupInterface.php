<?php

declare(strict_types=1);

namespace OCP;

interface GroupInterface {
	public const CREATE_GROUP = 1;
	public const DELETE_GROUP = 2;
	public const ADD_TO_GROUP = 4;
	public const REMOVE_FROM_GROUP = 8;
	public const COUNT_USERS = 16;
	public const GROUP_DETAILS = 32;
	public const IS_ADMIN = 64;
	
	public function getGroups(string $search = '', int $limit = -1, int $offset = 0): array;
	public function groupExists(string $gid): bool;
	public function usersInGroup(string $gid, string $search = '', int $limit = -1, int $offset = 0): array;
	public function inGroup(string $uid, string $gid): bool;
	public function getUserGroups(string $uid): array;
}
