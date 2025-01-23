<?php

namespace App\Repositories;

use App\Entities\Program;
use App\Filters\ProgramFilter;
use Core\Inject;
use PDO;

class ProgramRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(Program $program): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Program (name, description, image_uri, image_alt, url) " .
            "VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $program->name,
            $program->description,
            $program->imageUri,
            $program->imageAlt,
            $program->url
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(Program $program): void
    {
        $stmt = $this->db->prepare(
            "UPDATE Program SET " .
            "name = ?, description = ?, image_uri = ?, image_alt = ?, url = ? " .
            "WHERE id = ?"
        );
        $stmt->execute([
            $program->name,
            $program->description,
            $program->imageUri,
            $program->imageAlt,
            $program->url,
            $program->id
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM Program WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id): ?Program
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Program WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Program(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['image_uri'],
            $row['image_alt'],
            $row['url']
        );
    }

    public function findAll(?ProgramFilter $filter = null): array {
        $countSql = "SELECT COUNT(*) FROM Program";
        $params = [];
        $conditions = [];

        if ($filter) {
            // Name contains filter
            if ($filter->getName()) {
                $conditions[] = "name LIKE :name";
                $params[':name'] = '%' . $filter->getName() . '%';
            }

            // Add conditions to the count query
            if (!empty($conditions)) {
                $countSql .= " WHERE " . implode(" AND ", $conditions);
            }
        }

        // Execute the count query to get the total number of matching entries
        $stmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        // Build the main query to fetch data
        $sql = "SELECT * FROM Program";

        // Add conditions to the main query
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Add sorting
        if ($filter && $filter->getOrderBy()) {
            $sql .= " ORDER BY " . $this->sanitizeOrderBy($filter->getOrderBy()) .
                " " . $filter->getOrderDirection();
        }

        // Add pagination
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

        // Execute the query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        // Map results to Program objects
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $programs = [];
        foreach ($rows as $row) {
            $programs[] = new Program(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['image_uri'],
                $row['image_alt'],
                $row['url']
            );
        }

        return [
            'total_count' => $totalCount,
            'data' => $programs
        ];
    }

    private function sanitizeOrderBy(string $orderBy): string {
        $allowedColumns = ['id', 'name'];
        $orderBy = strtolower(trim($orderBy));
        return in_array($orderBy, $allowedColumns) ? $orderBy : 'id';
    }
}
