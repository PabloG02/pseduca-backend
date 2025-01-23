<?php

namespace App\Controllers;

use App\Entities\Program;
use App\Filters\ProgramFilter;
use App\Services\ProgramService;
use Core\Inject;
use PDOException;
use RuntimeException;

class ProgramController extends BaseController
{
    #[Inject]
    private ProgramService $programService;

    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
    }

    public function create(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        $name = filter_var($_POST['name'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $description = filter_var($_POST['description'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $imageFile = $_FILES['image_uri'] ?? null;
        $imageAlt = filter_var($_POST['image_alt'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $url = filter_var($_POST['url'] ?? null, FILTER_VALIDATE_URL);

        // Check for required fields
        if (!isset($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing required fields.']);
            return;
        }

        // Validate image file if provided
        $imageUri = null;
        if (isset($imageFile)) {
            $imageValidation = $this->validateImageFile($imageFile);
            if ($imageValidation !== null) {
                http_response_code(400);
                echo json_encode(['error' => $imageValidation]);
                return;
            }

            $imageUri = $this->saveImageFile($imageFile);
        }

        try {
            $program = new Program(
                0, // ID will be auto-generated
                $name,
                $description,
                $imageUri,
                $imageAlt,
                $url
            );
            $id = $this->programService->create($program);

            http_response_code(201);
            echo json_encode(['message' => 'Program created successfully.', 'id' => $id]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);

            // Clean up uploaded image in case of database error
            if ($imageUri) {
                $this->deleteImageFile($imageUri);
            }
        }
    }

    public function update(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $name = filter_var($_POST['name'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $description = filter_var($_POST['description'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $imageFile = $_FILES['image_uri'] ?? null;
        $imageAlt = filter_var($_POST['image_alt'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $url = filter_var($_POST['url'] ?? null, FILTER_VALIDATE_URL);

        // Check for required fields
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid program ID.']);
            return;
        }

        try {
            $program = $this->programService->get($id);

            if (!$program) {
                http_response_code(404);
                echo json_encode(['error' => 'Program not found.']);
                return;
            }

            // Update fields if provided
            if ($name !== null) {
                $program->name = $name;
            }
            if ($description !== null) {
                $program->description = $description;
            }
            if ($imageFile !== null) {
                $imageValidation = $this->validateImageFile($imageFile);
                if ($imageValidation !== null) {
                    http_response_code(400);
                    echo json_encode(['error' => $imageValidation]);
                    return;
                }

                $this->deleteImageFile($program->imageUri);
                $program->imageUri = $this->saveImageFile($imageFile);
            }
            if ($imageAlt !== null) {
                $program->imageAlt = $imageAlt;
            }
            if ($url !== false) {
                $program->url = $url;
            }

            $this->programService->update($program);

            http_response_code(200);
            echo json_encode(['message' => 'Program updated successfully.']);
        } catch (PDOException $e) {
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

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid program ID.']);
            return;
        }

        try {
            $program = $this->programService->get($id);

            if (!$program) {
                http_response_code(404);
                echo json_encode(['error' => 'Program not found.']);
                return;
            }

            $this->programService->delete($id);
            $this->deleteImageFile($program->imageUri);

            http_response_code(200);
            echo json_encode(['message' => 'Program deleted successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function get(): void
    {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid program ID.']);
            return;
        }

        try {
            $program = $this->programService->get($id);

            if (!$program) {
                http_response_code(404);
                echo json_encode(['error' => 'Program not found.']);
                return;
            }

            http_response_code(200);
            echo json_encode($program);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $result = $this->programService->list($filter);

            http_response_code(200);
            echo json_encode($result);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    protected function createFilterFromRequest(): ProgramFilter
    {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new ProgramFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return ProgramFilter::fromArray($data);
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

        // Use DIRECTORY_SEPARATOR to ensure cross-platform compatibility
        $destinationDir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'uploads', 'images', 'programs']);
        if (!is_dir($destinationDir)) {
            // Create the directory if it doesn't exist
            mkdir($destinationDir, 0755, true);
        }

        $destination = $destinationDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($imageFile['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to save the uploaded image file.');
        }

        return '/uploads/images/programs/' . $filename;
    }

    protected function deleteImageFile(string $imageUri): void
    {
        if (!$imageUri) return;

        $imagePath = explode('/', $imageUri);
        $imagePath = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', ...$imagePath]);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
