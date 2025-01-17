<?php

namespace App\Controllers;

use App\Entities\Resource;
use App\Filters\ResourceFilter;
use App\Services\ResourceService;
use Core\Inject;
use PDOException;
use RuntimeException;

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

        $name = filter_var($_POST['name'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $author = filter_var($_POST['author'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $description = filter_var($_POST['description'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $imageFile = $_FILES['image_uri'] ?? null;

        // Check for required fields
        if (!isset($name) || !isset($author) || !isset($description) || !isset($imageFile)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing required fields.']);
            return;
        }

        // Validate image file
        $imageValidation = $this->validateImageFile($imageFile);
        if ($imageValidation !== null) {
            http_response_code(400);
            echo json_encode(['error' => $imageValidation]);
            return;
        }

        // Save image file
        $imageUri = $this->saveImageFile($imageFile);

        try {
            $resource = new Resource(
                0, // ID will be auto-generated
                $name,
                $author,
                $description,
                $imageUri
            );
            $id = $this->resourceService->create($resource);

            http_response_code(201);
            echo json_encode(['message' => 'Resource created successfully.', 'id' => $id]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);

            $this->deleteImageFile($imageUri);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $result = $this->resourceService->list($filter);

            http_response_code(200);
            echo json_encode($result);
        } catch (PDOException $e) {
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

    protected function validateImageFile(array $imageFile): ?string
    {
        if ($imageFile['error'] !== UPLOAD_ERR_OK) {
            return 'Error uploading image file.';
        }

        $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            return 'Invalid image file format. Only JPEG and PNG are allowed.';
        }

        return null;
    }

    protected function saveImageFile(array $imageFile): string
    {
        $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;

        $destinationDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'resources';
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $destination = $destinationDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($imageFile['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to save the uploaded image file.');
        }

        return '/uploads/resources/' . $filename;
    }

    protected function deleteImageFile(string $imageUri): void
    {
        $imagePath = explode('/', $imageUri);
        $imagePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $imagePath);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}

