<?php

namespace App\Entities;

use ValueError;

class AcademicProgram
{
    public int $id;
    public string $name {
        get => $this->name;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Program name must be at least 2 characters long.');
            }
            if (strlen($value) > 255) {
                throw new ValueError('Program name cannot be more than 255 characters long.');
            }
            $this->name = $value;
        }
    }
    public string $qualificationLevel {
        get => $this->qualificationLevel;
        set {
            $allowedLevels = ['Master', 'Doctorate'];
            if (!in_array($value, $allowedLevels)) {
                throw new ValueError('Invalid qualification level. Must be Master or Doctorate.');
            }
            $this->qualificationLevel = $value;
        }
    }
    public string $description;
    public ?string $imageUri;
    public ?string $imageAlt;
    public int $availableSlots {
        get => $this->availableSlots;
        set {
            if ($value < 0) {
                throw new ValueError('Available slots cannot be negative.');
            }
            $this->availableSlots = $value;
        }
    }
    public string $teachingType {
        get => $this->teachingType;
        set {
            $allowedTypes = ['Online', 'Onsite'];
            if (!in_array($value, $allowedTypes)) {
                throw new ValueError('Invalid teaching type. Must be Online or Onsite.');
            }
            $this->teachingType = $value;
        }
    }
    public string $offeringFrequency {
        get => $this->offeringFrequency;
        set {
            $allowedFrequencies = ['Annual', 'Biannual', 'Quarterly'];
            if (!in_array($value, $allowedFrequencies)) {
                throw new ValueError('Invalid offering frequency. Must be Annual, Biannual, or Quarterly.');
            }
            $this->offeringFrequency = $value;
        }
    }
    public int $durationEcts {
        get => $this->durationEcts;
        set {
            if ($value <= 0) {
                throw new ValueError('Duration ECTS must be a positive number.');
            }
            $this->durationEcts = $value;
        }
    }
    public string $location {
        get => $this->location;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Location must be at least 2 characters long.');
            }
            $this->location = $value;
        }
    }
    public ?string $url;

    public function __construct(
        int $id,
        string $name,
        string $qualificationLevel,
        string $description,
        ?string $imageUri,
        ?string $imageAlt,
        int $availableSlots,
        string $teachingType,
        string $offeringFrequency,
        int $durationEcts,
        string $location,
        ?string $url
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->qualificationLevel = $qualificationLevel;
        $this->description = $description;
        $this->imageUri = $imageUri;
        $this->imageAlt = $imageAlt;
        $this->availableSlots = $availableSlots;
        $this->teachingType = $teachingType;
        $this->offeringFrequency = $offeringFrequency;
        $this->durationEcts = $durationEcts;
        $this->location = $location;
        $this->url = $url;
    }
}
