<?php

declare(strict_types=1);

namespace OCP\Group;

use OCP\IUser;
use OCP\IGroup;

interface ISubAdmin {
    public function isSubAdminOfGroup(IUser $user, IGroup $group): bool;
    public function isSubAdmin(IUser $user): bool;
    public function getSubAdminsGroups(IUser $user): array;
}