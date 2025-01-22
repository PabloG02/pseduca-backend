<?php

namespace App\Repositories;

use App\Entities\WebpageText;
use App\Filters\WebpageTextFilter;
use Core\Inject;
use PDO;

class WebpageTextRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function update(WebpageText $webpageText): void
    {
        $stmt = $this->db->prepare("UPDATE WebpageText SET text = ? WHERE text_key = ?");
        $stmt->execute([$webpageText->text, $webpageText->textKey]);
    }

    public function find(string $textKey): ?WebpageText
    {
        $stmt = $this->db->prepare("SELECT text_key, text FROM WebpageText WHERE text_key = ?");
        $stmt->execute([$textKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new WebpageText($row['text_key'], $row['text']);
    }

    public function findAll(?WebpageTextFilter $filter = null): array {
        // Initial SQL query for counting the total matching entries
        $countSql = "SELECT COUNT(*) FROM WebpageText";
        $params = [];
        $conditions = [];

        if ($filter) {
            // Add conditions to the count query
            if ($filter->getTextKey()) {
                $conditions[] = "text_key LIKE :text_key";
                $params[':text_key'] = '%' . $filter->getTextKey() . '%';
            }

            if ($filter->getTextContent()) {
                $conditions[] = "text LIKE :text_content";
                $params[':text_content'] = '%' . $filter->getTextContent() . '%';
            }

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

        // Fetch the count result
        $totalCount = $stmt->fetchColumn();

        // Now build the SQL query to fetch the actual paginated data
        $sql = "SELECT text_key, text FROM WebpageText";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if ($filter && $filter->getOrderBy()) {
            $sql .= " ORDER BY " . $this->sanitizeOrderBy($filter->getOrderBy()) .
                " " . $filter->getOrderDirection();
        }

        // Apply pagination if necessary
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

        // Execute the query to fetch the paginated data
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        // Fetch the result rows and map them to WebpageText objects
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $webpageTexts = [];
        foreach ($rows as $row) {
            $webpageTexts[] = new WebpageText($row['text_key'], $row['text']);
        }

        // Return both the total count and the paginated data
        return [
            'total_count' => $totalCount,
            'data' => $webpageTexts
        ];
    }

    private function sanitizeOrderBy(string $orderBy): string {
        $allowedColumns = ['text_key', 'text'];
        $orderBy = strtolower(trim($orderBy));
        return in_array($orderBy, $allowedColumns) ? $orderBy : 'text_key';
    }
}

