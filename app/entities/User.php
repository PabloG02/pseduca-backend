<?php

namespace App\Entities;

use DateTimeImmutable;
use ValueError;

class User
{
    public string $username {
        set {
            if (strlen($value) === 0) {
                throw new ValueError("username-empty");
            }
            $this->username = $value;
        }
    }
    public string $password {
        set {
            $this->password = $value;
        }
    }
    public string $email {
        set {
            $this->email = $value;
        }
    }
    public string $name {
        set {
            $this->name = $value;
        }
    }
    public ?DateTimeImmutable $createdAt {
        set {
            $this->createdAt = $value;
        }
    }
    public ?DateTimeImmutable $lastLogin {
        set {
            $this->lastLogin = $value;
        }
    }

    public function __construct(
        string $username,
        string $password,
        string $email,
        string $name,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $lastLogin = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->name = $name;
        $this->createdAt = $createdAt;
        $this->lastLogin = $lastLogin;
    }
}
