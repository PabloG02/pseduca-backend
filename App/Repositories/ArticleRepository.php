<?php

namespace App\Repositories;

use App\Entities\Article;
use App\Filters\ArticleFilter;
use Core\Inject;
use DateTimeImmutable;
use PDO;

class ArticleRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(Article $article): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Article (title, subtitle, body, image_uri, image_alt, author) " .
            "VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $article->title,
            $article->subtitle,
            $article->body,
            $article->imageUri,
            $article->imageAlt,
            $article->author
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(Article $article): void
    {
        $stmt = $this->db->prepare(
            "UPDATE Article SET " .
            "title = ?, subtitle = ?, body = ?, image_uri = ?, image_alt = ?, author = ? " .
            "WHERE id = ?"
        );
        $stmt->execute([
            $article->title,
            $article->subtitle,
            $article->body,
            $article->imageUri,
            $article->imageAlt,
            $article->author,
            $article->id
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM Article WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id): ?Article
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Article WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Article(
            $row['id'],
            $row['title'],
            $row['subtitle'],
            $row['body'],
            $row['author'],
            new DateTimeImmutable($row['created_at']),
            $row['image_uri'],
            $row['image_alt']
        );
    }

    public function findAll(?ArticleFilter $filter = null): array {
        $countSql = "SELECT COUNT(*) FROM Article";
        $params = [];
        $conditions = [];

        if ($filter) {
            // Title contains filter
            if ($filter->getTitle()) {
                $conditions[] = "title LIKE :title";
                $params[':title'] = '%' . $filter->getTitle() . '%';
            }

            // Author filter
            if ($filter->getAuthor()) {
                $conditions[] = "author LIKE :author";
                $params[':author'] = '%' . $filter->getAuthor() . '%';
            }

            // Created date range filter
            if ($filter->getCreatedAtFrom()) {
                $conditions[] = "created_at >= :created_at_from";
                $params[':created_at_from'] = $filter->getCreatedAtFrom()->format('Y-m-d H:i:s');
            }

            if ($filter->getCreatedAtTo()) {
                $conditions[] = "created_at <= :created_at_to";
                $params[':created_at_to'] = $filter->getCreatedAtTo()->format('Y-m-d H:i:s');
            }

            if (!empty($conditions)) {
                $countSql .= " WHERE " . implode(" AND ", $conditions);
            }
        }

        $stmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        $sql = "SELECT * FROM Article";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if ($filter && $filter->getOrderBy()) {
            $sql .= " ORDER BY " . $this->sanitizeOrderBy($filter->getOrderBy()) .
                " " . $filter->getOrderDirection();
        } else {
            // Default order by created_at DESC if no order specified
            $sql .= " ORDER BY created_at DESC";
        }

        if ($filter) {
            if ($filter->getLimit()) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = $filter->getLimit();

                if ($filter->getOffset()) {
                    $sql .= " OFFSET :offset";
                    $params[':offset'] = $filter->getOffset();
                }
            }
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $articles = [];
        foreach ($rows as $row) {
            $articles[] = new Article(
                $row['id'],
                $row['title'],
                $row['subtitle'],
                $row['body'],
                $row['author'],
                new DateTimeImmutable($row['created_at']),
                $row['image_uri'],
                $row['image_alt']
            );
        }

        return [
            'total_count' => $totalCount,
            'data' => $articles
        ];
    }

    private function sanitizeOrderBy(string $orderBy): string {
        $allowedColumns = ['id', 'title', 'created_at', 'author'];
        $orderBy = strtolower(trim($orderBy));
        return in_array($orderBy, $allowedColumns) ? $orderBy : 'created_at';
    }
}