<?php

namespace App\Repositories;

use App\Entities\User;
use App\Filters\UserFilter;
use Core\Inject;
use DateTimeImmutable;
use PDO;

class UserRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(User $user): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO User (username, password, email, name, activated) " .
            "VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $user->username,
            $user->password,
            $user->email,
            $user->name,
            $user->activated ? 1 : 0
            // The created_at field is set by the database
            // The last_login field is initially null
        ]);
    }

    public function update(User $user): void
    {
        $stmt = $this->db->prepare(
            "UPDATE User SET " .
            "password = ?, email = ?, name = ?, activated = ?, last_login = ? " .
            "WHERE username = ?"
        );
        $stmt->execute([
            $user->password,
            $user->email,
            $user->name,
            $user->activated ? 1 : 0,
            $user->lastLogin?->format('Y-m-d H:i:s'),
            $user->username
        ]);
    }

    public function delete(string $username): void
    {
        $stmt = $this->db->prepare("DELETE FROM User WHERE username = ?");
        $stmt->execute([$username]);
    }

    public function find(string $username): ?User
    {
        $stmt = $this->db->prepare(
            "SELECT username, password, email, name, activated, created_at, last_login, GROUP_CONCAT(UserRole.role_id) AS roles " .
            "FROM User LEFT JOIN UserRole ON User.username = UserRole.user_id " .
            "WHERE username = ? ".
            "GROUP BY username"
        );
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new User(
            $row['username'],
            $row['password'],
            $row['email'],
            $row['name'],
            (bool)$row['activated'],
            new DateTimeImmutable($row['created_at']),
            $row['last_login'] ? new DateTimeImmutable($row['last_login']) : null,
            $row['roles'] ? array_filter(explode(',', $row['roles'])) : [],
            true
        );

    }

    public function findAll(?UserFilter $filter = null): array {
        $countSql = "SELECT COUNT(*) FROM User";
        $params = [];
        $conditions = [];

        if ($filter) {
            // Username contains filter
            if ($filter->getUsername()) {
                $conditions[] = "username LIKE :username";
                $params[':username'] = '%' . $filter->getUsername() . '%';
            }

            // Email contains filter
            if ($filter->getEmail()) {
                $conditions[] = "email LIKE :email";
                $params[':email'] = '%' . $filter->getEmail() . '%';
            }

            // Name contains filter
            if ($filter->getName()) {
                $conditions[] = "name LIKE :name";
                $params[':name'] = '%' . $filter->getName() . '%';
            }

            // Activation filter
            if ($filter->getActivated() !== null) {
                $conditions[] = "activated = :activated";
                $params[':activated'] = $filter->getActivated() ? 1 : 0;
            }

            // Created At range filter
            if ($filter->getCreatedAtFrom()) {
                $conditions[] = "created_at >= :created_at_from";
                $params[':created_at_from'] = $filter->getCreatedAtFrom();
            }

            if ($filter->getCreatedAtTo()) {
                $conditions[] = "created_at <= :created_at_to";
                $params[':created_at_to'] = $filter->getCreatedAtTo();
            }

            // Last Login range filter
            if ($filter->getLastLoginFrom()) {
                $conditions[] = "last_login >= :last_login_from";
                $params[':last_login_from'] = $filter->getLastLoginFrom();
            }

            if ($filter->getLastLoginTo()) {
                $conditions[] = "last_login <= :last_login_to";
                $params[':last_login_to'] = $filter->getLastLoginTo();
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
        $sql = "SELECT username, password, email, name, activated, created_at, last_login, GROUP_CONCAT(UserRole.role_id) AS roles " .
            "FROM User LEFT JOIN UserRole ON User.username = UserRole.user_id";

        // Add conditions to the main query
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Add GROUP BY clause
        $sql .= " GROUP BY User.username";

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

        // Map results to User objects
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $users[] = new User(
                $row['username'],
                $row['password'],
                $row['email'],
                $row['name'],
                (bool)$row['activated'],
                new DateTimeImmutable($row['created_at']),
                $row['last_login'] ? new DateTimeImmutable($row['last_login']) : null,
                $row['roles'] ? array_filter(explode(',', $row['roles'])) : [],
                true
            );
        }

        return [
            'total_count' => $totalCount,
            'data' => $users
        ];
    }

    private function sanitizeOrderBy(string $orderBy): string {
        $allowedColumns = [
            'username', 'email', 'name', 'activated',
            'created_at', 'last_login'
        ];
        $orderBy = strtolower(trim($orderBy));
        return in_array($orderBy, $allowedColumns) ? $orderBy : 'username';
    }
}
