<?php

namespace App\Filters;

class UserFilter {
    private ?string $username = null;
    private ?string $email = null;
    private ?string $name = null;
    private ?bool $activated = null;
    private ?string $createdAtFrom = null;
    private ?string $createdAtTo = null;
    private ?string $lastLoginFrom = null;
    private ?string $lastLoginTo = null;
    private ?string $orderBy = null;
    private string $orderDirection = 'ASC';
    private ?int $limit = null;
    private ?int $offset = null;

    // Setters with fluent interface
    public function setUsername(?string $username): self {
        $this->username = $username;
        return $this;
    }

    public function setEmail(?string $email): self {
        $this->email = $email;
        return $this;
    }

    public function setName(?string $name): self {
        $this->name = $name;
        return $this;
    }

    public function setActivated(?bool $activated): self {
        $this->activated = $activated;
        return $this;
    }

    public function setCreatedAtFrom(?string $createdAtFrom): self {
        $this->createdAtFrom = $createdAtFrom;
        return $this;
    }

    public function setCreatedAtTo(?string $createdAtTo): self {
        $this->createdAtTo = $createdAtTo;
        return $this;
    }

    public function setLastLoginFrom(?string $lastLoginFrom): self {
        $this->lastLoginFrom = $lastLoginFrom;
        return $this;
    }

    public function setLastLoginTo(?string $lastLoginTo): self {
        $this->lastLoginTo = $lastLoginTo;
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
    public function getUsername(): ?string {
        return $this->username;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getActivated(): ?bool {
        return $this->activated;
    }

    public function getCreatedAtFrom(): ?string {
        return $this->createdAtFrom;
    }

    public function getCreatedAtTo(): ?string {
        return $this->createdAtTo;
    }

    public function getLastLoginFrom(): ?string {
        return $this->lastLoginFrom;
    }

    public function getLastLoginTo(): ?string {
        return $this->lastLoginTo;
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

        if (isset($data['username'])) {
            $filter->setUsername($data['username']);
        }

        if (isset($data['email'])) {
            $filter->setEmail($data['email']);
        }

        if (isset($data['name'])) {
            $filter->setName($data['name']);
        }

        if (isset($data['activated'])) {
            $filter->setActivated((bool)$data['activated']);
        }

        if (isset($data['created_at_from'])) {
            $filter->setCreatedAtFrom($data['created_at_from']);
        }

        if (isset($data['created_at_to'])) {
            $filter->setCreatedAtTo($data['created_at_to']);
        }

        if (isset($data['last_login_from'])) {
            $filter->setLastLoginFrom($data['last_login_from']);
        }

        if (isset($data['last_login_to'])) {
            $filter->setLastLoginTo($data['last_login_to']);
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