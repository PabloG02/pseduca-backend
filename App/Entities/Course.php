<?php

namespace App\Entities;

use DateTimeImmutable;
use ValueError;

class Course
{
    public int $id;
    public string $name {
        get => $this->name;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Course name must be at least 2 characters long.');
            }
            if (strlen($value) > 255) {
                throw new ValueError('Course name cannot be more than 255 characters long.');
            }
            $this->name = $value;
        }
    }
    public string $description;
    public DateTimeImmutable $startDate;
    public DateTimeImmutable $endDate;
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
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        string $description,
        ?string $imageUri = null,
        ?string $url = null
    ) {
        $this->id = $id;
        $this->name = $name;

        // Validate date range if both dates are provided
        if ($startDate > $endDate) {
            throw new ValueError('Start date must be before or equal to end date.');
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->description = $description;
        $this->imageUri = $imageUri;
        $this->url = $url;
    }
}
