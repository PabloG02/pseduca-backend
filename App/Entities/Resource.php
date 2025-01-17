<?php

namespace App\Entities;

use ValueError;

class Resource
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
            $this->name = $value;
        }
    }
    public string $author {
        get => $this->author;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Author must be at least 2 characters long.');
            }
            if (strlen($value) > 255) {
                throw new ValueError('Author cannot be more than 255 characters long.');
            }
            $this->author = $value;
        }
    }
    public string $description;
    public ?string $imageUri;

    public function __construct(
        int $id,
        string $name,
        string $author,
        string $description,
        ?string $imageUri = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->author = $author;
        $this->description = $description;
        $this->imageUri = $imageUri;
    }
}
