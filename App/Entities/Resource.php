<?php

namespace App\Entities;

use DateTimeImmutable;
use JsonSerializable;
use ValueError;

class Resource
{
    public int $id;
    public string $name {
        get => $this->name;
        set {
            if (strlen($value) > 180) {
                throw new ValueError('Name cannot be more than 180 characters long.');
            }
            $this->name = $value;
        }
    }
    public string $acronym {
        get => $this->acronym;
        set {
            if (strlen($value) > 10) {
                throw new ValueError('Acronym cannot be more than 10 characters long.');
            }
            $this->acronym = $value;
        }
    }
    public int $year;
    public ?string $description;
    public ?string $notes;
    public ?string $imageUri;
    public ?int $minAgeYears;
    public ?int $minAgeMonths;
    public ?int $maxAgeYears;
    public ?int $maxAgeMonths;
    public ?int $completionTime;
    public array $authors = [];
    public array $categories = [];
    public array $formats = [];
    public array $areas = [];
    public array $types = [];
    public array $applications = [];

    public function __construct(
        string $name,
        string $acronym,
        int $year,
        ?string $description = null,
        ?string $notes = null,
        ?string $imageUri = null,
        ?int $minAgeYears = null,
        ?int $minAgeMonths = null,
        ?int $maxAgeYears = null,
        ?int $maxAgeMonths = null,
        ?int $completionTime = null,
        array $authors = [],
        array $categories = [],
        array $formats = [],
        array $areas = [],
        array $types = [],
        array $applications = [],
        ?int $id = null
    ) {
        if ($id !== null) {
            $this->id = $id;
        }
        $this->name = $name;
        $this->acronym = $acronym;
        $this->year = $year;
        $this->description = $description;
        $this->notes = $notes;
        $this->imageUri = $imageUri;
        $this->minAgeYears = $minAgeYears;
        $this->minAgeMonths = $minAgeMonths;
        $this->maxAgeYears = $maxAgeYears;
        $this->maxAgeMonths = $maxAgeMonths;
        $this->completionTime = $completionTime;
        $this->authors = $authors;
        $this->categories = $categories;
        $this->formats = $formats;
        $this->areas = $areas;
        $this->types = $types;
        $this->applications = $applications;
    }
}
