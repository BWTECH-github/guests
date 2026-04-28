<?php

declare(strict_types=1);

namespace OCP\Share;

use OCP\IUser;

interface IShare {
	public const TYPE_USER = 0;
	public const TYPE_GROUP = 1;
	public const TYPE_LINK = 3;
	public const TYPE_EMAIL = 4;
	public const TYPE_REMOTE = 6;
	
	public function getId(): string;
	public function getFullId(): string;
	public function getNodeId(): int;
	public function getNodeType(): string;
	public function getShareType(): int;
	public function getSharedWith(): ?string;
	public function getSharedBy(): string;
	public function getShareOwner(): string;
	public function getPermissions(): int;
	public function getToken(): ?string;
	public function getExpirationDate(): ?\DateTimeInterface;
	public function getTarget(): string;
	public function getNote(): string;
	
	public function setId(string $id): IShare;
	public function setNodeId(int $fileId): IShare;
	public function setNodeType(string $type): IShare;
	public function setShareType(int $shareType): IShare;
	public function setSharedWith(?string $sharedWith): IShare;
	public function setSharedBy(string $sharedBy): IShare;
	public function setShareOwner(string $shareOwner): IShare;
	public function setPermissions(int $permissions): IShare;
	public function setToken(?string $token): IShare;
	public function setExpirationDate(?\DateTimeInterface $expireDate): IShare;
	public function setTarget(string $target): IShare;
	public function setNote(string $note): IShare;
}
