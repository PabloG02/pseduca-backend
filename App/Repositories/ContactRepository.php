<?php

namespace App\Repositories;

use App\Entities\Contact;
use App\Filters\ContactFilter;
use Core\Inject;
use PDO;

class ContactRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(Contact $contact): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Contact " .
            "(address, email, phone, google_maps_embed_url) " .
            "VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $contact->address,
            $contact->email,
            $contact->phone,
            $contact->googleMapsEmbedUrl
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(Contact $contact): void
    {
        $stmt = $this->db->prepare(
            "UPDATE Contact SET " .
            "address = ?, email = ?, phone = ?, google_maps_embed_url = ? " .
            "WHERE id = ?"
        );
        $stmt->execute([
            $contact->address,
            $contact->email,
            $contact->phone,
            $contact->googleMapsEmbedUrl,
            $contact->id
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM Contact WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id): ?Contact
    {
        $stmt = $this->db->prepare("SELECT * FROM Contact WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Contact(
            $row['id'],
            $row['address'],
            $row['email'],
            $row['phone'],
            $row['google_maps_embed_url']
        );
    }

    public function findAll(?ContactFilter $filter = null): array
    {
        $countSql = "SELECT COUNT(*) FROM Contact";
        $params = [];
        $conditions = [];

        if ($filter) {
            if ($filter->getEmail()) {
                $conditions[] = "email LIKE :email";
                $params[':email'] = '%' . $filter->getEmail() . '%';
            }

            if ($filter->getPhone()) {
                $conditions[] = "phone LIKE :phone";
                $params[':phone'] = '%' . $filter->getPhone() . '%';
            }

            if ($filter->getAddress()) {
                $conditions[] = "address LIKE :address";
                $params[':address'] = '%' . $filter->getAddress() . '%';
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

        $sql = "SELECT * FROM Contact";

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
        $contacts = [];
        foreach ($rows as $row) {
            $contacts[] = new Contact(
                $row['id'],
                $row['address'],
                $row['email'],
                $row['phone'],
                $row['google_maps_embed_url']
            );
        }

        return [
            'total_count' => $totalCount,
            'data' => $contacts
        ];
    }

    private function sanitizeOrderBy(string $orderBy): string {
        $allowedColumns = ['id', 'email', 'phone', 'address'];
        $orderBy = strtolower(trim($orderBy));
        return in_array($orderBy, $allowedColumns) ? $orderBy : 'id';
    }
}
