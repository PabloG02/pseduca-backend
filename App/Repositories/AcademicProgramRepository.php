<?php

namespace App\Repositories;

use App\Entities\AcademicProgram;
use App\Filters\AcademicProgramFilter;
use Core\Inject;
use PDO;

class AcademicProgramRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(AcademicProgram $program): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO AcademicPrograms " .
            "(name, qualification_level, description, image_uri, image_alt, " .
            "available_slots, teaching_type, offering_frequency, duration_ects, location, url) " .
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $program->name,
            $program->qualificationLevel,
            $program->description,
            $program->imageUri,
            $program->imageAlt,
            $program->availableSlots,
            $program->teachingType,
            $program->offeringFrequency,
            $program->durationEcts,
            $program->location,
            $program->url
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(AcademicProgram $program): void
    {
        $stmt = $this->db->prepare(
            "UPDATE AcademicPrograms SET " .
            "name = ?, qualification_level = ?, description = ?, image_uri = ?, image_alt = ?, " .
            "available_slots = ?, teaching_type = ?, offering_frequency = ?, " .
            "duration_ects = ?, location = ?, url = ? " .
            "WHERE id = ?"
        );
        $stmt->execute([
            $program->name,
            $program->qualificationLevel,
            $program->description,
            $program->imageUri,
            $program->imageAlt,
            $program->availableSlots,
            $program->teachingType,
            $program->offeringFrequency,
            $program->durationEcts,
            $program->location,
            $program->url,
            $program->id
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM AcademicPrograms WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id): ?AcademicProgram
    {
        $stmt = $this->db->prepare("SELECT * FROM AcademicPrograms WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new AcademicProgram(
            $row['id'],
            $row['name'],
            $row['qualification_level'],
            $row['description'],
            $row['image_uri'],
            $row['image_alt'],
            $row['available_slots'],
            $row['teaching_type'],
            $row['offering_frequency'],
            $row['duration_ects'],
            $row['location'],
            $row['url']
        );
    }

    public function findAll(?AcademicProgramFilter $filter = null): array {
        $countSql = "SELECT COUNT(*) FROM AcademicPrograms";
        $params = [];
        $conditions = [];

        if ($filter) {
            // Name contains filter
            if ($filter->getName()) {
                $conditions[] = "name LIKE :name";
                $params[':name'] = '%' . $filter->getName() . '%';
            }

            // Qualification level filter
            if ($filter->getQualificationLevel()) {
                $conditions[] = "qualification_level = :qualification_level";
                $params[':qualification_level'] = $filter->getQualificationLevel();
            }

            // Teaching type filter
            if ($filter->getTeachingType()) {
                $conditions[] = "teaching_type = :teaching_type";
                $params[':teaching_type'] = $filter->getTeachingType();
            }

            // Offering frequency filter
            if ($filter->getOfferingFrequency()) {
                $conditions[] = "offering_frequency = :offering_frequency";
                $params[':offering_frequency'] = $filter->getOfferingFrequency();
            }

            // Location filter
            if ($filter->getLocation()) {
                $conditions[] = "location LIKE :location";
                $params[':location'] = '%' . $filter->getLocation() . '%';
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
        $sql = "SELECT * FROM AcademicPrograms";

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

        // Map results to AcademicProgram objects
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $programs = [];
        foreach ($rows as $row) {
            $programs[] = new AcademicProgram(
                $row['id'],
                $row['name'],
                $row['qualification_level'],
                $row['description'],
                $row['image_uri'],
                $row['image_alt'],
                $row['available_slots'],
                $row['teaching_type'],
                $row['offering_frequency'],
                $row['duration_ects'],
                $row['location'],
                $row['url']
            );
        }

        return [
            'total_count' => $totalCount,
            'data' => $programs
        ];
    }

    private function sanitizeOrderBy(string $orderBy): string {
        $allowedColumns = [
            'id', 'name', 'qualification_level', 'teaching_type',
            'offering_frequency', 'duration_ects', 'location'
        ];
        $orderBy = strtolower(trim($orderBy));
        return in_array($orderBy, $allowedColumns) ? $orderBy : 'id';
    }
}
