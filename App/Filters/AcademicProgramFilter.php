<?php

namespace App\Filters;

class AcademicProgramFilter {
    private ?string $name = null;
    private ?string $qualificationLevel = null;
    private ?string $teachingType = null;
    private ?string $offeringFrequency = null;
    private ?string $location = null;
    private ?string $orderBy = null;
    private string $orderDirection = 'ASC';
    private ?int $limit = null;
    private ?int $offset = null;

    // Setters with fluent interface
    public function setName(?string $name): self {
        $this->name = $name;
        return $this;
    }

    public function setQualificationLevel(?string $qualificationLevel): self {
        $this->qualificationLevel = $qualificationLevel;
        return $this;
    }

    public function setTeachingType(?string $teachingType): self {
        $this->teachingType = $teachingType;
        return $this;
    }

    public function setOfferingFrequency(?string $offeringFrequency): self {
        $this->offeringFrequency = $offeringFrequency;
        return $this;
    }

    public function setLocation(?string $location): self {
        $this->location = $location;
        return $this;
    }

    public function setOrderBy(?string $orderBy): self {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function setOrderDirection(string $orderDirection): self {
        $this->orderDirection = strtoupper($orderDirection) === 'DESC' ? 'DESC' : 'ASC';
        return $this;
    }

    public function setLimit(?int $limit): self {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset(?int $offset): self {
        $this->offset = $offset;
        return $this;
    }

    // Getters
    public function getName(): ?string {
        return $this->name;
    }

    public function getQualificationLevel(): ?string {
        return $this->qualificationLevel;
    }

    public function getTeachingType(): ?string {
        return $this->teachingType;
    }

    public function getOfferingFrequency(): ?string {
        return $this->offeringFrequency;
    }

    public function getLocation(): ?string {
        return $this->location;
    }

    public function getOrderBy(): ?string {
        return $this->orderBy;
    }

    public function getOrderDirection(): string {
        return $this->orderDirection;
    }

    public function getLimit(): ?int {
        return $this->limit;
    }

    public function getOffset(): ?int {
        return $this->offset;
    }

    public static function fromArray(array $data): self {
        $filter = new self();

        if (isset($data['name'])) {
            $filter->setName($data['name']);
        }

        if (isset($data['qualification_level'])) {
            $filter->setQualificationLevel($data['qualification_level']);
        }

        if (isset($data['teaching_type'])) {
            $filter->setTeachingType($data['teaching_type']);
        }

        if (isset($data['offering_frequency'])) {
            $filter->setOfferingFrequency($data['offering_frequency']);
        }

        if (isset($data['location'])) {
            $filter->setLocation($data['location']);
        }

        if (isset($data['order_by'])) {
            $filter->setOrderBy($data['order_by']);
        }

        if (isset($data['order_direction'])) {
            $filter->setOrderDirection($data['order_direction']);
        }

        if (isset($data['limit'])) {
            $filter->setLimit((int)$data['limit']);
        }

        if (isset($data['offset'])) {
            $filter->setOffset((int)$data['offset']);
        }

        return $filter;
    }
}
