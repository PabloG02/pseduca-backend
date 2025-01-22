<?php

namespace App\Repositories;

use App\Entities\TestingProgram;
use App\Filters\TestingProgramFilter;
use Core\Inject;
use DateTimeImmutable;
use PDO;

class TestingProgramRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(TestingProgram $program): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO TestingPrograms (name, description, start_date, end_date, image_uri, url) " .
            "VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $program->name,
            $program->description,
            $program->startDate->format('Y-m-d'),
            $program->endDate->format('Y-m-d'),
            $program->imageUri,
            $program->url
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(TestingProgram $program): void
    {
        $stmt = $this->db->prepare(
            "UPDATE TestingPrograms SET " .
            "name = ?, description = ?, start_date = ?, end_date = ?, image_uri = ?, url = ? " .
            "WHERE id = ?"
        );
        $stmt->execute([
            $program->name,
            $program->description,
            $program->startDate->format('Y-m-d'),
            $program->endDate->format('Y-m-d'),
            $program->imageUri,
            $program->url,
            $program->id
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM TestingPrograms WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id): ?TestingProgram
    {
        $stmt = $this->db->prepare("SELECT * FROM TestingPrograms WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new TestingProgram(
            $row['id'],
            $row['name'],
            new DateTimeImmutable($row['start_date']),
            new DateTimeImmutable($row['end_date']),
            $row['description'],
            $row['image_uri'],
            $row['url']
        );
    }

    public function findAll(?TestingProgramFilter $filter = null): array {
        $countSql = "SELECT COUNT(*) FROM TestingPrograms";
        $params = [];
        $conditions = [];

        if ($filter) {
            if ($filter->getName()) {
                $conditions[] = "name LIKE :name";
                $params[':name'] = '%' . $filter->getName() . '%';
            }

            if ($filter->getStartDateFrom()) {
                $conditions[] = "start_date >= :start_date_from";
                $params[':start_date_from'] = $filter->getStartDateFrom()->format('Y-m-d');
            }

            if ($filter->getStartDateTo()) {
                $conditions[] = "start_date <= :start_date_to";
                $params[':start_date_to'] = $filter->getStartDateTo()->format('Y-m-d');
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

        $sql = "SELECT * FROM TestingPrograms";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if ($filter && $filter->getOrderBy()) {
            $sql .= " ORDER BY " . $this->sanitizeOrderBy($filter->getOrderBy()) .
                " " . $filter->getOrderDirection();
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
        $programs = [];
        foreach ($rows as $row) {
            $programs[] = new TestingProgram(
                $row['id'],
                $row['name'],
                new DateTimeImmutable($row['start_date']),
                new DateTimeImmutable($row['end_date']),
                $row['description'],
                $row['image_uri'],
                $row['url']
            );
        }

        return [
            'total_count' => $totalCount,
            'data' => $programs
        ];
    }

    private function sanitizeOrderBy(string $orderBy): string {
        $allowedColumns = ['id', 'name', 'start_date', 'end_date'];
        $orderBy = strtolower(trim($orderBy));
        return in_array($orderBy, $allowedColumns) ? $orderBy : 'id';
    }
}
