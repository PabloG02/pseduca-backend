<?php

namespace App\Entities;

use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use ValueError;

class User implements JsonSerializable
{
    public string $username {
        get => $this->username;
        set {
            if (strlen($value) < 3) {
                throw new ValueError('Username must be at least 3 characters long.');
            }
            if (strlen($value) > 20) {
                throw new ValueError('Username cannot be more than 20 characters long.');
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                throw new ValueError('Username must be alphanumeric and can include underscores.');
            }
            $this->username = $value;
        }
    }
    public string $password;
    public string $email {
        get => $this->email;
        set {
            if (strlen($value) > 255) {
                throw new ValueError('Email cannot be more than 255 characters long.');
            }
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new ValueError('Invalid email format.');
            }
            $this->email = $value;
        }
    }
    public string $name {
        get => $this->name;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Name must be at least 2 characters long.');
            }
            if (strlen($value) > 50) {
                throw new ValueError('Name cannot be more than 50 characters long.');
            }
            if (!preg_match("/^[a-zA-Z\s'-]+$/", $value)) {
                throw new ValueError('Name can only contain letters, spaces, apostrophes, and hyphens.');
            }
            $this->name = $value;
        }
    }
    public bool $activated;
    public ?DateTimeImmutable $createdAt;
    public ?DateTimeImmutable $lastLogin;
    public array $roles;

    public function __construct(
        string $username,
        string $password,
        string $email,
        string $name,
        bool $activated = false,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $lastLogin = null,
        array $roles = [],
        bool $isPasswordHashed = false
    ) {
        $this->username = $username;
        $this->password = $isPasswordHashed ? $password : $this->hashPassword($password);
        $this->email = $email;
        $this->name = $name;
        $this->activated = $activated;
        $this->createdAt = $createdAt;
        $this->lastLogin = $lastLogin;
        $this->roles = $roles;
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function jsonSerialize(): array
    {
        return [
            'username' => $this->username,
            // Password is not serialized because it should never be sent to the client
            'email' => $this->email,
            'name' => $this->name,
            'activated' => $this->activated,
            'createdAt' => $this->createdAt?->format(DateTimeInterface::ATOM),
            'lastLogin' => $this->lastLogin?->format(DateTimeInterface::ATOM),
            'roles' => $this->roles
        ];
    }
}
