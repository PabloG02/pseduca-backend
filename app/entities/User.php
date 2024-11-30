<?php

namespace App\Entities;

use DateTimeImmutable;
use ValueError;

class User
{
    public string $username;
    public string $password;
    public string $email;
    public string $name;
    public bool $activated;
    public ?DateTimeImmutable $createdAt;
    public ?DateTimeImmutable $lastLogin;

    public function __construct(
        string $username,
        string $password,
        string $email,
        string $name,
        bool $activated = false,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $lastLogin = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->name = $name;
        $this->activated = $activated;
        $this->createdAt = $createdAt;
        $this->lastLogin = $lastLogin;
    }
}
