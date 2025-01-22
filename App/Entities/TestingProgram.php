<?php

namespace App\Entities;

use ValueError;

class TestingProgram
{
    public int $id;
    public string $name {
        get => $this->name;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Testing program name must be at least 2 characters long.');
            }
            if (strlen($value) > 255) {
                throw new ValueError('Testing program name cannot be more than 255 characters long.');
            }
            $this->name = $value;
        }
    }
    public string $description;
    public ?string $imageUri;
    public ?string $url {
        get => $this->url;
        set {
            if ($value === null) {
                $this->url = null;
                return;
            }

            if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                throw new ValueError('Invalid URL format.');
            }

            if (strlen($value) > 255) {
                throw new ValueError('URL cannot be more than 255 characters long.');
            }

            $this->url = $value;
        }
    }

    public function __construct(
        int $id,
        string $name,
        string $description,
        ?string $imageUri = null,
        ?string $url = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->imageUri = $imageUri;
        $this->url = $url;
    }
}
