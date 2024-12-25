<?php

namespace App\Filters;

class ContactFilter {
    private ?string $email = null;
    private ?string $phone = null;
    private ?string $address = null;
    private ?string $orderBy = null;
    private string $orderDirection = 'ASC';
    private ?int $limit = null;
    private ?int $offset = null;

    // Setters with fluent interface
    public function setEmail(?string $email): self {
        $this->email = $email;
        return $this;
    }

    public function setPhone(?string $phone): self {
        $this->phone = $phone;
        return $this;
    }

    public function setAddress(?string $address): self {
        $this->address = $address;
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
    public function getEmail(): ?string {
        return $this->email;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function getAddress(): ?string {
        return $this->address;
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

        if (isset($data['email'])) {
            $filter->setEmail($data['email']);
        }

        if (isset($data['phone'])) {
            $filter->setPhone($data['phone']);
        }

        if (isset($data['address'])) {
            $filter->setAddress($data['address']);
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
