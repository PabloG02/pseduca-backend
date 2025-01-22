<?php

namespace App\Entities;

use ValueError;

class TeamMember
{
    public int $id;
    public string $name {
        get => $this->name;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Name must be at least 2 characters long.');
            }
            if (strlen($value) > 255) {
                throw new ValueError('Name cannot be more than 255 characters long.');
            }
            if (!preg_match("/^[a-zA-Z\s'-]+$/", $value)) {
                throw new ValueError('Name can only contain letters, spaces, apostrophes, and hyphens.');
            }
            $this->name = $value;
        }
    }
    public string $email {
        get => $this->email;
        set {
            if (strlen($value) > 100) {
                throw new ValueError('Email cannot be more than 100 characters long.');
            }
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new ValueError('Invalid email format.');
            }
            $this->email = $value;
        }
    }
    public ?string $imageUri;
    public ?string $biography;
    public int $researcherId;

    public function __construct(
        int $id,
        string $name,
        string $email,
        ?string $imageUri = null,
        ?string $biography = null,
        int $researcherId = 0 // TODO: Do not set default value for researcherId
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->imageUri = $imageUri;
        $this->biography = $biography;
        $this->researcherId = $researcherId;
    }
}
