<?php

namespace App\Controllers;

use App\Entities\Resource;
use App\Filters\ResourceFilter;
use App\Services\ResourceService;
use Core\Inject;
use DateTimeImmutable;
use Exception;
use ValueError;

class ResourceController extends BaseController
{
    #[Inject]
    private ResourceService $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    public function create(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        try {
            // Filter and validate form data
            $name = filter_var($_POST['name'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $acronym = filter_var($_POST['acronym'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $year = filter_var($_POST['year'], FILTER_VALIDATE_INT);
            $description = filter_var($_POST['description'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $notes = filter_var($_POST['notes'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $imageUri = filter_var($_POST['image_uri'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $minAgeYears = filter_var($_POST['min_age_years'] ?? null, FILTER_VALIDATE_INT);
            $minAgeMonths = filter_var($_POST['min_age_months'] ?? null, FILTER_VALIDATE_INT);
            $maxAgeYears = filter_var($_POST['max_age_years'] ?? null, FILTER_VALIDATE_INT);
            $maxAgeMonths = filter_var($_POST['max_age_months'] ?? null, FILTER_VALIDATE_INT);
            $completionTime = filter_var($_POST['completion_time'] ?? null, FILTER_VALIDATE_INT);

            // Check required fields
            if (!isset($name) || !isset($acronym) || !isset($year)) {
                http_response_code(400);
                echo json_encode(['error' => 'Name, acronym, and year are required.']);
                return;
            }

            // Create resource object
            $resource = new Resource(
                $name,
                $acronym,
                $year,
                $description,
                $notes,
                $imageUri,
                $minAgeYears,
                $minAgeMonths,
                $maxAgeYears,
                $maxAgeMonths,
                $completionTime
            );

            // Handle formats (assuming comma-separated string)
            if (isset($_POST['formats'])) {
                $formats = array_filter(array_map('trim', explode(',', $_POST['formats'])));
                foreach ($formats as $format) {
                    $resource->formats[] = $format;
                }
            }

            // Handle areas
            if (isset($_POST['areas'])) {
                $areas = array_filter(array_map('trim', explode(',', $_POST['areas'])));
                foreach ($areas as $area) {
                    $resource->areas[] = $area;
                }
            }

            // Handle types
            if (isset($_POST['types'])) {
                $types = array_filter(array_map('trim', explode(',', $_POST['types'])));
                foreach ($types as $type) {
                    $resource->types[] = $type;
                }
            }

            // Handle applications
            if (isset($_POST['applications'])) {
                $applications = array_filter(array_map('trim', explode(',', $_POST['applications'])));
                foreach ($applications as $application) {
                    $resource->applications[] = $application;
                }
            }

            $this->resourceService->create($resource);

            http_response_code(201);
            echo json_encode(['message' => 'Resource created successfully.']);
        } catch (ValueError $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function update(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        try {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid resource ID is required.']);
                return;
            }

            // Get existing resource
            $resource = $this->resourceService->get($id);
            if (!$resource) {
                http_response_code(404);
                echo json_encode(['error' => 'Resource not found.']);
                return;
            }

            // Filter and validate form data
            $name = filter_var($_POST['name'] ?? $resource->name, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $acronym = filter_var($_POST['acronym'] ?? $resource->acronym, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $year = filter_var($_POST['year'] ?? $resource->year, FILTER_VALIDATE_INT);
            $description = filter_var($_POST['description'] ?? $resource->description, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $notes = filter_var($_POST['notes'] ?? $resource->notes, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $imageUri = filter_var($_POST['image_uri'] ?? $resource->imageUri, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $minAgeYears = filter_var($_POST['min_age_years'] ?? $resource->minAgeYears, FILTER_VALIDATE_INT);
            $minAgeMonths = filter_var($_POST['min_age_months'] ?? $resource->minAgeMonths, FILTER_VALIDATE_INT);
            $maxAgeYears = filter_var($_POST['max_age_years'] ?? $resource->maxAgeYears, FILTER_VALIDATE_INT);
            $maxAgeMonths = filter_var($_POST['max_age_months'] ?? $resource->maxAgeMonths, FILTER_VALIDATE_INT);
            $completionTime = filter_var($_POST['completion_time'] ?? $resource->completionTime, FILTER_VALIDATE_INT);

            // Create updated resource object
            $updatedResource = new Resource(
                $name,
                $acronym,
                $year,
                $description,
                $notes,
                $imageUri,
                $minAgeYears,
                $minAgeMonths,
                $maxAgeYears,
                $maxAgeMonths,
                $completionTime,
                $id
            );

            // Handle formats
            if (isset($_POST['formats'])) {
                $resource->formats = array_filter(array_map('trim', explode(',', $_POST['formats'])));
            }

            // Handle areas
            if (isset($_POST['areas'])) {
                $resource->areas = array_filter(array_map('trim', explode(',', $_POST['areas'])));
            }

            // Handle types
            if (isset($_POST['types'])) {
                $resource->types = array_filter(array_map('trim', explode(',', $_POST['types'])));
            }

            // Handle applications
            if (isset($_POST['applications'])) {
                $resource->applications = array_filter(array_map('trim', explode(',', $_POST['applications'])));
            }

            $this->resourceService->update($updatedResource);

            http_response_code(200);
            echo json_encode(['message' => 'Resource updated successfully.']);
        } catch (ValueError $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function delete(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        try {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid resource ID is required.']);
                return;
            }

            // Check if resource exists
            $resource = $this->resourceService->get($id);
            if (!$resource) {
                http_response_code(404);
                echo json_encode(['error' => 'Resource not found.']);
                return;
            }

            $this->resourceService->delete($id);
            http_response_code(200);
            echo json_encode(['message' => 'Resource deleted successfully.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function get(): void
    {
        try {
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid resource ID is required.']);
                return;
            }

            $resource = $this->resourceService->get($id);

            if (!$resource) {
                http_response_code(404);
                echo json_encode(['error' => 'Resource not found.']);
                return;
            }

            http_response_code(200);
            echo json_encode($resource);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $resources = $this->resourceService->list($filter);
            http_response_code(200);
            echo json_encode($resources);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    protected function createFilterFromRequest(): ResourceFilter
    {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new ResourceFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return ResourceFilter::fromArray($data);
    }
}
