<?php

namespace App\Controllers;

use App\Entities\TestingProgram;
use App\Filters\TestingProgramFilter;
use App\Services\TestingProgramService;
use Core\Inject;
use PDOException;
use RuntimeException;

class TestingProgramController extends BaseController
{
    #[Inject]
    private TestingProgramService $testingProgramService;

    public function __construct(TestingProgramService $testingProgramService)
    {
        $this->testingProgramService = $testingProgramService;
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
        $url = filter_var($_POST['url'] ?? null, FILTER_VALIDATE_URL);

        if (!isset($name) || !isset($imageFile)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing required fields.']);
            return;
        }

        $imageValidation = $this->validateImageFile($imageFile);
        if ($imageValidation !== null) {
            http_response_code(400);
            echo json_encode(['error' => $imageValidation]);
            return;
        }

        $imageUri = $this->saveImageFile($imageFile);

        try {
            $testingProgram = new TestingProgram(
                0,
                $name,
                $description,
                $imageUri,
                $url
            );
            $id = $this->testingProgramService->create($testingProgram);

            http_response_code(201);
            echo json_encode(['message' => 'Testing program created successfully.', 'id' => $id]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            $this->deleteImageFile($imageUri);
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
        $url = filter_var($_POST['url'] ?? null, FILTER_VALIDATE_URL);

        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid testing program ID.']);
            return;
        }

        try {
            $testingProgram = $this->testingProgramService->get($id);

            if (!$testingProgram) {
                http_response_code(404);
                echo json_encode(['error' => 'Testing program not found.']);
                return;
            }

            if ($name !== null) {
                $testingProgram->name = $name;
            }
            if ($description !== null) {
                $testingProgram->description = $description;
            }
            if ($imageFile !== null) {
                $imageValidation = $this->validateImageFile($imageFile);
                if ($imageValidation !== null) {
                    http_response_code(400);
                    echo json_encode(['error' => $imageValidation]);
                    return;
                }

                $this->deleteImageFile($testingProgram->imageUri);
                $testingProgram->imageUri = $this->saveImageFile($imageFile);
            }
            if ($url !== false) {
                $testingProgram->url = $url;
            }

            $this->testingProgramService->update($testingProgram);

            http_response_code(200);
            echo json_encode(['message' => 'Testing program updated successfully.']);
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
            echo json_encode(['error' => 'Invalid testing program ID.']);
            return;
        }

        try {
            $testingProgram = $this->testingProgramService->get($id);

            if (!$testingProgram) {
                http_response_code(404);
                echo json_encode(['error' => 'Testing program not found.']);
                return;
            }

            $this->testingProgramService->delete($id);
            $this->deleteImageFile($testingProgram->imageUri);

            http_response_code(200);
            echo json_encode(['message' => 'Testing program deleted successfully.']);
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
            echo json_encode(['error' => 'Invalid testing program ID.']);
            return;
        }

        try {
            $testingProgram = $this->testingProgramService->get($id);

            if (!$testingProgram) {
                http_response_code(404);
                echo json_encode(['error' => 'Testing program not found.']);
                return;
            }

            http_response_code(200);
            echo json_encode($testingProgram);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $result = $this->testingProgramService->list($filter);

            http_response_code(200);
            echo json_encode($result);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    protected function createFilterFromRequest(): TestingProgramFilter
    {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new TestingProgramFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return TestingProgramFilter::fromArray($data);
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

        $destinationDir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'uploads', 'images', 'testing_programs']);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $destination = $destinationDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($imageFile['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to save the uploaded image file.');
        }

        return '/uploads/images/testing_programs/' . $filename;
    }

    protected function deleteImageFile(string $imageUri): void
    {
        $imagePath = explode('/', $imageUri);
        $imagePath = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', ...$imagePath]);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
