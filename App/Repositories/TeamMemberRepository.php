<?php

namespace App\Repositories;

use App\Entities\TeamMember;
use App\Filters\TeamMemberFilter;
use Core\Inject;
use PDO;

class TeamMemberRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(TeamMember $teamMember): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO TeamMember (name, email, image_uri, biography, researcher_id) " .
            "VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $teamMember->name,
            $teamMember->email,
            $teamMember->imageUri,
            $teamMember->biography,
            $teamMember->researcherId
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(TeamMember $teamMember): void
    {
        $stmt = $this->db->prepare(
            "UPDATE TeamMember SET " .
            "name = ?, email = ?, image_uri = ?, biography = ?, researcher_id = ? " .
            "WHERE id = ?"
        );
        $stmt->execute([
            $teamMember->name,
            $teamMember->email,
            $teamMember->imageUri,
            $teamMember->biography,
            $teamMember->researcherId,
            $teamMember->id
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM TeamMember WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id): ?TeamMember
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM TeamMember WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new TeamMember(
            $row['id'],
            $row['name'],
            $row['email'],
            $row['image_uri'],
            $row['biography'],
            $row['researcher_id']
        );
    }

    public function findAll(?TeamMemberFilter $filter = null): array {
        $countSql = "SELECT COUNT(*) FROM TeamMember";
        $params = [];
        $conditions = [];

        if ($filter) {
            // Name contains filter
            if ($filter->getName()) {
                $conditions[] = "name LIKE :name";
                $params[':name'] = '%' . $filter->getName() . '%';
            }

            // Email contains filter
            if ($filter->getEmail()) {
                $conditions[] = "email LIKE :email";
                $params[':email'] = '%' . $filter->getEmail() . '%';
            }

            // Researcher ID filter
            if ($filter->getResearcherId()) {
                $conditions[] = "researcher_id = :researcher_id";
                $params[':researcher_id'] = $filter->getResearcherId();
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
        $sql = "SELECT * FROM TeamMember";

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

        // Map results to TeamMember objects
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $teamMembers = [];
        foreach ($rows as $row) {
            $teamMembers[] = new TeamMember(
                $row['id'],
                $row['name'],
                $row['email'],
                $row['image_uri'],
                $row['biography'],
                $row['researcher_id']
            );
        }

        return [
            'total_count' => $totalCount,
            'data' => $teamMembers
        ];
    }

    private function sanitizeOrderBy(string $orderBy): string {
        $allowedColumns = ['id', 'name', 'email', 'researcher_id'];
        $orderBy = strtolower(trim($orderBy));
        return in_array($orderBy, $allowedColumns) ? $orderBy : 'id';
    }
}