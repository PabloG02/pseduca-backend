<?php

namespace App\Repositories;

use App\Entities\Course;
use App\Filters\CourseFilter;
use Core\Inject;
use DateTimeImmutable;
use PDO;

class CourseRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(Course $course): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Courses (name, description, start_date, end_date, image_uri, url) " .
            "VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $course->name,
            $course->description,
            $course->startDate->format('Y-m-d'),
            $course->endDate->format('Y-m-d'),
            $course->imageUri,
            $course->url
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(Course $course): void
    {
        $stmt = $this->db->prepare(
            "UPDATE Courses SET " .
            "name = ?, description = ?, start_date = ?, end_date = ?, image_uri = ?, url = ? " .
            "WHERE id = ?"
        );
        $stmt->execute([
            $course->name,
            $course->description,
            $course->startDate->format('Y-m-d'),
            $course->endDate->format('Y-m-d'),
            $course->imageUri,
            $course->url,
            $course->id
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM Courses WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id): ?Course
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Courses WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Course(
            $row['id'],
            $row['name'],
            new \DateTimeImmutable($row['start_date']),
            new \DateTimeImmutable($row['end_date']),
            $row['description'],
            $row['image_uri'],
            $row['url']
        );
    }

    public function findAll(?CourseFilter $filter = null): array {
        $countSql = "SELECT COUNT(*) FROM Courses";
        $params = [];
        $conditions = [];

        if ($filter) {
            // Name contains filter
            if ($filter->getName()) {
                $conditions[] = "name LIKE :name";
                $params[':name'] = '%' . $filter->getName() . '%';
            }

            // Start date range filter
            if ($filter->getStartDateFrom()) {
                $conditions[] = "start_date >= :start_date_from";
                $params[':start_date_from'] = $filter->getStartDateFrom()->format('Y-m-d');
            }

            if ($filter->getStartDateTo()) {
                $conditions[] = "start_date <= :start_date_to";
                $params[':start_date_to'] = $filter->getStartDateTo()->format('Y-m-d');
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
        $sql = "SELECT * FROM Courses";

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

        // Map results to Course objects
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $courses = [];
        foreach ($rows as $row) {
            $courses[] = new Course(
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
            'data' => $courses
        ];
    }

    private function sanitizeOrderBy(string $orderBy): string {
        $allowedColumns = ['id', 'name', 'start_date', 'end_date'];
        $orderBy = strtolower(trim($orderBy));
        return in_array($orderBy, $allowedColumns) ? $orderBy : 'id';
    }
}
