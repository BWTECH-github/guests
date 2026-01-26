<?php

declare(strict_types=1);

namespace OC\User;

use OCP\IUser;

class User implements IUser {
    private string $uid;
    private string $displayName;
    private ?string $email;
    private bool $enabled = true;
    
    public function __construct(string $uid, $backend = null) {
        $this->uid = $uid;
        $this->displayName = $uid;
        $this->email = null;
    }
    
    public function getUID(): string {
        return $this->uid;
    }
    
    public function getDisplayName(): string {
        return $this->displayName;
    }
    
    public function getEMailAddress(): ?string {
        return $this->email;
    }
    
    public function setEMailAddress(?string $mailAddress): void {
        $this->email = $mailAddress;
    }
    
    public function getBackendClassName(): string {
        return 'Database';
    }
    
    public function isEnabled(): bool {
        return $this->enabled;
    }
    
    public function setEnabled(bool $enabled = true): void {
        $this->enabled = $enabled;
    }
    
    public function delete(): bool {
        return true;
    }
}