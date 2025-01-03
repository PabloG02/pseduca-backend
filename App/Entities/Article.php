<?php

namespace App\Entities;

use DateTimeImmutable;
use JsonSerializable;
use ValueError;

class Article implements JsonSerializable
{
    public int $id;
    public string $title {
        get => $this->title;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Article title must be at least 2 characters long.');
            }
            if (strlen($value) > 255) {
                throw new ValueError('Article title cannot be more than 255 characters long.');
            }
            $this->title = $value;
        }
    }
    public string $subtitle;
    public string $body;
    public ?string $imageUri;
    public ?string $imageAlt;
    public DateTimeImmutable $createdAt;
    public string $author {
        get => $this->author;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Author name must be at least 2 characters long.');
            }
            if (strlen($value) > 255) {
                throw new ValueError('Author name cannot be more than 255 characters long.');
            }
            $this->author = $value;
        }
    }

    public function __construct(
        int $id,
        string $title,
        string $subtitle,
        string $body,
        string $author,
        DateTimeImmutable $createdAt,
        ?string $imageUri = null,
        ?string $imageAlt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->body = $body;
        $this->author = $author;
        $this->createdAt = $createdAt;

        // Validate image and alt text consistency
        if (($imageUri === null && $imageAlt !== null) || ($imageUri !== null && $imageAlt === null)) {
            throw new ValueError('Image URI and alt text must either both be set or both be null.');
        }

        $this->imageUri = $imageUri;
        $this->imageAlt = $imageAlt;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'body' => $this->body,
            'imageUri' => $this->imageUri,
            'imageAlt' => $this->imageAlt,
            'author' => $this->author,
            'createdAt' => $this->createdAt->format(DateTimeImmutable::ATOM)
        ];
    }
}
