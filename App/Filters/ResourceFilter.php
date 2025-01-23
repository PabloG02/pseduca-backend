<?php

namespace App\Filters;

class ResourceFilter {
    private ?string $name = null;
    private ?string $acronym = null;
    private ?int $yearFrom = null;
    private ?int $yearTo = null;
    private ?array $formats = null;
    private ?array $areas = null;
    private ?array $types = null;
    private ?array $applications = null;
    private ?int $minAgeMonths = null;  // Total age in months for comparison
    private ?int $maxAgeMonths = null;  // Total age in months for comparison
    private ?int $minCompletionTime = null;
    private ?int $maxCompletionTime = null;
    private ?string $orderBy = null;
    private string $orderDirection = 'ASC';
    private ?int $limit = null;
    private ?int $offset = null;

    // Name filter
    public function setName(?string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    // Acronym filter
    public function setAcronym(?string $acronym): self {
        $this->acronym = $acronym;
        return $this;
    }

    public function getAcronym(): ?string {
        return $this->acronym;
    }

    // Year range filters
    public function setYearFrom(?int $yearFrom): self {
        $this->yearFrom = $yearFrom;
        return $this;
    }

    public function getYearFrom(): ?int {
        return $this->yearFrom;
    }

    public function setYearTo(?int $yearTo): self {
        $this->yearTo = $yearTo;
        return $this;
    }

    public function getYearTo(): ?int {
        return $this->yearTo;
    }

    // Format filters
    public function setFormats(?array $formats): self {
        $this->formats = $formats;
        return $this;
    }

    public function getFormats(): ?array {
        return $this->formats;
    }

    // Area filters
    public function setAreas(?array $areas): self {
        $this->areas = $areas;
        return $this;
    }

    public function getAreas(): ?array {
        return $this->areas;
    }

    // Type filters
    public function setTypes(?array $types): self {
        $this->types = $types;
        return $this;
    }

    public function getTypes(): ?array {
        return $this->types;
    }

    // Application filters
    public function setApplications(?array $applications): self {
        $this->applications = $applications;
        return $this;
    }

    public function getApplications(): ?array {
        return $this->applications;
    }

    // Age range filters (converts years and months to total months for comparison)
    public function setMinAge(?int $years, ?int $months): self {
        if ($years === null && $months === null) {
            $this->minAgeMonths = null;
        } else {
            $this->minAgeMonths = ($years ?? 0) * 12 + ($months ?? 0);
        }
        return $this;
    }

    public function getMinAgeMonths(): ?int {
        return $this->minAgeMonths;
    }

    public function setMaxAge(?int $years, ?int $months): self {
        if ($years === null && $months === null) {
            $this->maxAgeMonths = null;
        } else {
            $this->maxAgeMonths = ($years ?? 0) * 12 + ($months ?? 0);
        }
        return $this;
    }

    public function getMaxAgeMonths(): ?int {
        return $this->maxAgeMonths;
    }

    // Completion time range filters
    public function setMinCompletionTime(?int $minutes): self {
        $this->minCompletionTime = $minutes;
        return $this;
    }

    public function getMinCompletionTime(): ?int {
        return $this->minCompletionTime;
    }

    public function setMaxCompletionTime(?int $minutes): self {
        $this->maxCompletionTime = $minutes;
        return $this;
    }

    public function getMaxCompletionTime(): ?int {
        return $this->maxCompletionTime;
    }

    // Ordering
    public function setOrderBy(?string $orderBy): self {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function getOrderBy(): ?string {
        return $this->orderBy;
    }

    public function setOrderDirection(string $orderDirection): self {
        $this->orderDirection = strtoupper($orderDirection) === 'DESC' ? 'DESC' : 'ASC';
        return $this;
    }

    public function getOrderDirection(): string {
        return $this->orderDirection;
    }

    // Pagination
    public function setLimit(?int $limit): self {
        $this->limit = $limit;
        return $this;
    }

    public function getLimit(): ?int {
        return $this->limit;
    }

    public function setOffset(?int $offset): self {
        $this->offset = $offset;
        return $this;
    }

    public function getOffset(): ?int {
        return $this->offset;
    }

    // Factory method to create from array
    public static function fromArray(array $data): self {
        $filter = new self();

        if (isset($data['name'])) {
            $filter->setName($data['name']);
        }

        if (isset($data['acronym'])) {
            $filter->setAcronym($data['acronym']);
        }

        if (isset($data['year_from'])) {
            $filter->setYearFrom((int)$data['year_from']);
        }

        if (isset($data['year_to'])) {
            $filter->setYearTo((int)$data['year_to']);
        }

        if (isset($data['formats']) && is_array($data['formats'])) {
            $filter->setFormats($data['formats']);
        }

        if (isset($data['areas']) && is_array($data['areas'])) {
            $filter->setAreas($data['areas']);
        }

        if (isset($data['types']) && is_array($data['types'])) {
            $filter->setTypes($data['types']);
        }

        if (isset($data['applications']) && is_array($data['applications'])) {
            $filter->setApplications($data['applications']);
        }

        if (isset($data['min_age_years']) || isset($data['min_age_months'])) {
            $filter->setMinAge(
                isset($data['min_age_years']) ? (int)$data['min_age_years'] : null,
                isset($data['min_age_months']) ? (int)$data['min_age_months'] : null
            );
        }

        if (isset($data['max_age_years']) || isset($data['max_age_months'])) {
            $filter->setMaxAge(
                isset($data['max_age_years']) ? (int)$data['max_age_years'] : null,
                isset($data['max_age_months']) ? (int)$data['max_age_months'] : null
            );
        }

        if (isset($data['min_completion_time'])) {
            $filter->setMinCompletionTime((int)$data['min_completion_time']);
        }

        if (isset($data['max_completion_time'])) {
            $filter->setMaxCompletionTime((int)$data['max_completion_time']);
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
