<?php

namespace App\Filters;

class TeamMemberFilter {
    private ?string $name = null;
    private ?string $email = null;
    private ?int $researcherId = null;
    private ?string $orderBy = null;
    private string $orderDirection = 'ASC';
    private ?int $limit = null;
    private ?int $offset = null;

    // Setters with fluent interface
    public function setName(?string $name): self {
        $this->name = $name;
        return $this;
    }

    public function setEmail(?string $email): self {
        $this->email = $email;
        return $this;
    }

    public function setResearcherId(?int $researcherId): self {
        $this->researcherId = $researcherId;
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

    public function getEmail(): ?string {
        return $this->email;
    }

    public function getResearcherId(): ?int {
        return $this->researcherId;
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

        if (isset($data['email'])) {
            $filter->setEmail($data['email']);
        }

        if (isset($data['researcher_id'])) {
            $filter->setResearcherId((int)$data['researcher_id']);
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