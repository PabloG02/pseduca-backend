<?php

namespace App\Entities;

use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use ValueError;

class User implements JsonSerializable
{
    public string $username;
    public string $password;
    public string $email;
    public string $name;
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
