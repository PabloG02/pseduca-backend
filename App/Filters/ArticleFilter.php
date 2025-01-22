<?php

namespace App\Filters;

use DateTimeImmutable;

class ArticleFilter
{
    private ?string $title = null;
    private ?string $author = null;
    private ?DateTimeImmutable $createdAtFrom = null;
    private ?DateTimeImmutable $createdAtTo = null;
    private ?string $orderBy = null;
    private string $orderDirection = 'DESC';  // Default to newest first
    private ?int $limit = null;
    private ?int $offset = null;

    // Setters with fluent interface
    public function setTitle(?string $title): self {
        $this->title = $title;
        return $this;
    }

    public function setAuthor(?string $author): self {
        $this->author = $author;
        return $this;
    }

    public function setCreatedAtFrom(?DateTimeImmutable $createdAtFrom): self {
        $this->createdAtFrom = $createdAtFrom;
        return $this;
    }

    public function setCreatedAtTo(?DateTimeImmutable $createdAtTo): self {
        $this->createdAtTo = $createdAtTo;
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
    public function getTitle(): ?string {
        return $this->title;
    }

    public function getAuthor(): ?string {
        return $this->author;
    }

    public function getCreatedAtFrom(): ?DateTimeImmutable {
        return $this->createdAtFrom;
    }

    public function getCreatedAtTo(): ?DateTimeImmutable {
        return $this->createdAtTo;
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

        if (isset($data['title'])) {
            $filter->setTitle($data['title']);
        }

        if (isset($data['author'])) {
            $filter->setAuthor($data['author']);
        }

        if (isset($data['created_at_from'])) {
            $filter->setCreatedAtFrom(new DateTimeImmutable($data['created_at_from']));
        }

        if (isset($data['created_at_to'])) {
            $filter->setCreatedAtTo(new DateTimeImmutable($data['created_at_to']));
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