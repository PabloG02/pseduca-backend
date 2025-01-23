<?php

namespace App\Repositories;

use App\Entities\Resource;
use Core\Inject;
use PDO;
use ValueError;

class ResourceRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(Resource $resource): int
    {
        // Start a transaction to handle multiple table insertions
        $this->db->beginTransaction();

        try {
            // Insert main resource details
            $stmt = $this->db->prepare(
                "INSERT INTO Resource (name, acronym, year, description, notes, image_uri, " .
                "min_age_years, min_age_months, max_age_years, max_age_months, completion_time) " .
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $resource->name,
                $resource->acronym,
                $resource->year,
                $resource->description,
                $resource->notes,
                $resource->imageUri,
                $resource->minAgeYears,
                $resource->minAgeMonths,
                $resource->maxAgeYears,
                $resource->maxAgeMonths,
                $resource->completionTime
            ]);

            // Get the last inserted ID
            $resourceId = (int)$this->db->lastInsertId();

            // Insert authors
            $this->insertRelationships(
                $resourceId,
                $resource->authors,
                'Author',
                'ResourceAuthor',
                'resource_id',
                'author_id'
            );

            // Insert categories
            $this->insertRelationships(
                $resourceId,
                $resource->categories,
                'Category',
                'ResourceCategory',
                'resource_id',
                'category_id'
            );

            // Insert formats
            $this->insertRelationships(
                $resourceId,
                $resource->formats,
                'Format',
                'ResourceFormat',
                'resource_id',
                'format_id'
            );

            // Insert areas
            $this->insertRelationships(
                $resourceId,
                $resource->areas,
                'Area',
                'ResourceArea',
                'resource_id',
                'area_id'
            );

            // Insert types
            $this->insertRelationships(
                $resourceId,
                $resource->types,
                'ResType',
                'ResourceType',
                'resource_id',
                'type_id'
            );

            // Insert applications
            $this->insertRelationships(
                $resourceId,
                $resource->applications,
                'ResApplication',
                'ResourceApplication',
                'resource_id',
                'application_id'
            );

            // Commit the transaction
            $this->db->commit();

            return $resourceId;
        } catch (\Exception $e) {
            // Rollback the transaction on error
            $this->db->rollBack();
            throw $e;
        }
    }

    private function insertRelationships(
        int $resourceId,
        array $items,
        string $masterTable,
        string $relationTable,
        string $resourceColumn,
        string $itemColumn
    ): void {
        // Skip if no items
        if (empty($items)) {
            return;
        }

        // Prepare insert statement for relationship table
        $stmt = $this->db->prepare(
            "INSERT INTO $relationTable ($resourceColumn, $itemColumn) VALUES (?, ?)"
        );

        // Insert each relationship
        foreach ($items as $item) {
            // If it's an ID, use directly
            if (is_numeric($item)) {
                $stmt->execute([$resourceId, $item]);
                continue;
            }

            // If it's a string, first insert into master table
            $insertMasterStmt = $this->db->prepare(
                "INSERT IGNORE INTO $masterTable (name) VALUES (?)"
            );
            $insertMasterStmt->execute([$item]);

            // Get the ID of the inserted/existing item
            $selectIdStmt = $this->db->prepare(
                "SELECT id FROM $masterTable WHERE name = ?"
            );
            $selectIdStmt->execute([$item]);
            $itemId = $selectIdStmt->fetchColumn();

            // Insert relationship
            $stmt->execute([$resourceId, $itemId]);
        }
    }

    public function update(Resource $resource): void
    {
        // Start a transaction to handle multiple table updates
        $this->db->beginTransaction();

        try {
            // Update main resource details
            $stmt = $this->db->prepare(
                "UPDATE Resource SET " .
                "name = ?, acronym = ?, year = ?, description = ?, notes = ?, image_uri = ?, " .
                "min_age_years = ?, min_age_months = ?, max_age_years = ?, max_age_months = ?, " .
                "completion_time = ? WHERE id = ?"
            );
            $stmt->execute([
                $resource->name,
                $resource->acronym,
                $resource->year,
                $resource->description,
                $resource->notes,
                $resource->imageUri,
                $resource->minAgeYears,
                $resource->minAgeMonths,
                $resource->maxAgeYears,
                $resource->maxAgeMonths,
                $resource->completionTime,
                $resource->id
            ]);

            // Delete existing relationships
            $deleteRelations = [
                'ResourceAuthor', 'ResourceCategory', 'ResourceFormat',
                'ResourceArea', 'ResourceType', 'ResourceApplication'
            ];
            foreach ($deleteRelations as $table) {
                $deleteStmt = $this->db->prepare(
                    "DELETE FROM $table WHERE resource_id = ?"
                );
                $deleteStmt->execute([$resource->id]);
            }

            // Re-insert relationships
            $this->insertRelationships(
                $resource->id,
                $resource->authors,
                'Author',
                'ResourceAuthor',
                'resource_id',
                'author_id'
            );

            $this->insertRelationships(
                $resource->id,
                $resource->categories,
                'Category',
                'ResourceCategory',
                'resource_id',
                'category_id'
            );

            $this->insertRelationships(
                $resource->id,
                $resource->formats,
                'Format',
                'ResourceFormat',
                'resource_id',
                'format_id'
            );

            $this->insertRelationships(
                $resource->id,
                $resource->areas,
                'Area',
                'ResourceArea',
                'resource_id',
                'area_id'
            );

            $this->insertRelationships(
                $resource->id,
                $resource->types,
                'ResType',
                'ResourceType',
                'resource_id',
                'type_id'
            );

            $this->insertRelationships(
                $resource->id,
                $resource->applications,
                'ResApplication',
                'ResourceApplication',
                'resource_id',
                'application_id'
            );

            // Commit the transaction
            $this->db->commit();
        } catch (\Exception $e) {
            // Rollback the transaction on error
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $resourceId): void
    {
        $stmt = $this->db->prepare("DELETE FROM Resource WHERE id = ?");
        $stmt->execute([$resourceId]);
    }

    public function find(int $resourceId): ?Resource
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, " .
            "GROUP_CONCAT(DISTINCT a.name) AS authors, " .
            "GROUP_CONCAT(DISTINCT c.name) AS categories, " .
            "GROUP_CONCAT(DISTINCT f.name) AS formats, " .
            "GROUP_CONCAT(DISTINCT ar.name) AS areas, " .
            "GROUP_CONCAT(DISTINCT t.name) AS types, " .
            "GROUP_CONCAT(DISTINCT app.name) AS applications " .
            "FROM Resource r " .
            "LEFT JOIN ResourceAuthor ra ON r.id = ra.resource_id " .
            "LEFT JOIN Author a ON ra.author_id = a.id " .
            "LEFT JOIN ResourceCategory rc ON r.id = rc.resource_id " .
            "LEFT JOIN Category c ON rc.category_id = c.id " .
            "LEFT JOIN ResourceFormat rf ON r.id = rf.resource_id " .
            "LEFT JOIN Format f ON rf.format_id = f.id " .
            "LEFT JOIN ResourceArea rarea ON r.id = rarea.resource_id " .
            "LEFT JOIN Area ar ON rarea.area_id = ar.id " .
            "LEFT JOIN ResourceType rt ON r.id = rt.resource_id " .
            "LEFT JOIN ResType t ON rt.type_id = t.id " .
            "LEFT JOIN ResourceApplication rapp ON r.id = rapp.resource_id " .
            "LEFT JOIN ResApplication app ON rapp.application_id = app.id " .
            "WHERE r.id = ? " .
            "GROUP BY r.id"
        );
        $stmt->execute([$resourceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Resource(
            $row['name'],
            $row['acronym'],
            $row['year'],
            $row['description'],
            $row['notes'],
            $row['image_uri'],
            $row['min_age_years'],
            $row['min_age_months'],
            $row['max_age_years'],
            $row['max_age_months'],
            $row['completion_time'],
            $row['authors'] ? explode(',', $row['authors']) : [],
            $row['categories'] ? explode(',', $row['categories']) : [],
            $row['formats'] ? explode(',', $row['formats']) : [],
            $row['areas'] ? explode(',', $row['areas']) : [],
            $row['types'] ? explode(',', $row['types']) : [],
            $row['applications'] ? explode(',', $row['applications']) : [],
            $row['id']
        );
    }

    public function findAll(?array $filters = null): array
    {
        // Prepare base query with all joins
        $baseQuery =
            "SELECT r.*, " .
            "GROUP_CONCAT(DISTINCT a.name) AS authors, " .
            "GROUP_CONCAT(DISTINCT c.name) AS categories, " .
            "GROUP_CONCAT(DISTINCT f.name) AS formats, " .
            "GROUP_CONCAT(DISTINCT ar.name) AS areas, " .
            "GROUP_CONCAT(DISTINCT t.name) AS types, " .
            "GROUP_CONCAT(DISTINCT app.name) AS applications " .
            "FROM Resource r " .
            "LEFT JOIN ResourceAuthor ra ON r.id = ra.resource_id " .
            "LEFT JOIN Author a ON ra.author_id = a.id " .
            "LEFT JOIN ResourceCategory rc ON r.id = rc.resource_id " .
            "LEFT JOIN Category c ON rc.category_id = c.id " .
            "LEFT JOIN ResourceFormat rf ON r.id = rf.resource_id " .
            "LEFT JOIN Format f ON rf.format_id = f.id " .
            "LEFT JOIN ResourceArea rarea ON r.id = rarea.resource_id " .
            "LEFT JOIN Area ar ON rarea.area_id = ar.id " .
            "LEFT JOIN ResourceType rt ON r.id = rt.resource_id " .
            "LEFT JOIN ResType t ON rt.type_id = t.id " .
            "LEFT JOIN ResourceApplication rapp ON r.id = rapp.resource_id " .
            "LEFT JOIN ResApplication app ON rapp.application_id = app.id ";

        $conditions = [];
        $params = [];

        // Build conditions based on filters
        if ($filters) {
            // Name filter
            if (isset($filters['name'])) {
                $conditions[] = "r.name LIKE :name";
                $params[':name'] = '%' . $filters['name'] . '%';
            }

            // Acronym filter
            if (isset($filters['acronym'])) {
                $conditions[] = "r.acronym LIKE :acronym";
                $params[':acronym'] = '%' . $filters['acronym'] . '%';
            }

            // Year filter
            if (isset($filters['year'])) {
                $conditions[] = "r.year = :year";
                $params[':year'] = $filters['year'];
            }

            // Age range filters
            if (isset($filters['min_age_years'])) {
                $conditions[] = "r.min_age_years >= :min_age_years";
                $params[':min_age_years'] = $filters['min_age_years'];
            }

            if (isset($filters['max_age_years'])) {
                $conditions[] = "r.max_age_years <= :max_age_years";
                $params[':max_age_years'] = $filters['max_age_years'];
            }

            // Completion time filter
            if (isset($filters['completion_time'])) {
                $conditions[] = "r.completion_time <= :completion_time";
                $params[':completion_time'] = $filters['completion_time'];
            }

            // Author filter
            if (isset($filters['author'])) {
                $conditions[] = "a.name LIKE :author";
                $params[':author'] = '%' . $filters['author'] . '%';
            }

            // Category filter
            if (isset($filters['category'])) {
                $conditions[] = "c.name LIKE :category";
                $params[':category'] = '%' . $filters['category'] . '%';
            }

            // Format filter
            if (isset($filters['format'])) {
                $conditions[] = "f.name LIKE :format";
                $params[':format'] = '%' . $filters['format'] . '%';
            }

            // Area filter
            if (isset($filters['area'])) {
                $conditions[] = "ar.name LIKE :area";
                $params[':area'] = '%' . $filters['area'] . '%';
            }

            // Type filter
            if (isset($filters['type'])) {
                $conditions[] = "t.name LIKE :type";
                $params[':type'] = '%' . $filters['type'] . '%';
            }

            // Application filter
            if (isset($filters['application'])) {
                $conditions[] = "app.name LIKE :application";
                $params[':application'] = '%' . $filters['application'] . '%';
            }
        }

        // Add WHERE clause if conditions exist
        $whereClause = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

        // Count query
        $countQuery = "SELECT COUNT(DISTINCT r.id) FROM Resource r " .
            implode(" ", array_slice(explode(" ", $baseQuery), 3)) .
            $whereClause;

        // Prepare and execute count query
        $countStmt = $this->db->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalCount = $countStmt->fetchColumn();

        // Add GROUP BY and potential pagination/sorting
        $query = $baseQuery .
            $whereClause .
            " GROUP BY r.id";

        // Add sorting if specified
        if (isset($filters['order_by'])) {
            $allowedColumns = [
                'id', 'name', 'acronym', 'year',
                'min_age_years', 'completion_time'
            ];
            $orderBy = strtolower(trim($filters['order_by']));
            $orderDirection = isset($filters['order_direction']) &&
            strtoupper($filters['order_direction']) === 'DESC' ? 'DESC' : 'ASC';

            $orderBy = in_array($orderBy, $allowedColumns) ? "r.$orderBy" : "r.id";
            $query .= " ORDER BY $orderBy $orderDirection";
        }

        // Add pagination
        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 10;
        $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        // Prepare and execute the query
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        // Fetch and map results
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resources = [];

        foreach ($rows as $row) {
            $resources[] = new Resource(
                $row['name'],
                $row['acronym'],
                $row['year'],
                $row['description'],
                $row['notes'],
                $row['image_uri'],
                $row['min_age_years'],
                $row['min_age_months'],
                $row['max_age_years'],
                $row['max_age_months'],
                $row['completion_time'],
                $row['authors'] ? explode(',', $row['authors']) : [],
                $row['categories'] ? explode(',', $row['categories']) : [],
                $row['formats'] ? explode(',', $row['formats']) : [],
                $row['areas'] ? explode(',', $row['areas']) : [],
                $row['types'] ? explode(',', $row['types']) : [],
                $row['applications'] ? explode(',', $row['applications']) : [],
                $row['id']
            );
        }

        return [
            'total_count' => $totalCount,
            'data' => $resources
        ];
    }

    // Additional helper methods for managing related entities
    public function addAuthor(int $resourceId, string $authorName): int
    {
        // Insert or get existing author
        $stmt = $this->db->prepare(
            "INSERT INTO Author (name) VALUES (?) " .
            "ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)"
        );
        $stmt->execute([$authorName]);
        $authorId = (int)$this->db->lastInsertId();

        // Add relationship if not exists
        $relationStmt = $this->db->prepare(
            "INSERT IGNORE INTO ResourceAuthor (resource_id, author_id) " .
            "VALUES (?, ?)"
        );
        $relationStmt->execute([$resourceId, $authorId]);

        return $authorId;
    }

    public function removeAuthor(int $resourceId, string $authorName): void
    {
        $stmt = $this->db->prepare(
            "DELETE ra FROM ResourceAuthor ra " .
            "JOIN Author a ON ra.author_id = a.id " .
            "WHERE ra.resource_id = ? AND a.name = ?"
        );
        $stmt->execute([$resourceId, $authorName]);
    }
}