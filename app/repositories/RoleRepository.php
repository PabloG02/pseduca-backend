<?php

namespace App\Repositories;

use App\Entities\Role;
use Core\Inject;
use PDO;

class RoleRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array {
        $countSql = "SELECT COUNT(*) FROM Role";

        // Execute the count query to get the total number of matching entries
        $stmt = $this->db->prepare($countSql);
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        // Build the main query to fetch data
        $sql = "SELECT name FROM Role";

        // Execute the query
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        // Map results to User objects
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $roles = [];
        foreach ($rows as $row) {
            $roles[] = new Role($row['name']);
        }

        return [
            'total_count' => $totalCount,
            'data' => $roles
        ];
    }
}