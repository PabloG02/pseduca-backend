<?php

namespace App\Controllers;

use App\Entities\Course;
use App\Filters\CourseFilter;
use App\Services\CourseService;
use Core\Inject;
use DateTimeImmutable;
use PDOException;
use RuntimeException;

class CourseController extends BaseController
{
    #[Inject]
    private CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
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
        $startDate = filter_var($_POST['start_date'], FILTER_DEFAULT);
        $endDate = filter_var($_POST['end_date'], FILTER_DEFAULT);
        $imageFile = $_FILES['image_uri'] ?? null;
        $url = filter_var($_POST['url'] ?? null, FILTER_VALIDATE_URL);

        // Check for required fields
        if (!isset($name) || !$startDate || !$endDate || !isset($imageFile)) {
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
            $course = new Course(
                0, // ID will be auto-generated
                $name,
                new DateTimeImmutable($startDate),
                new DateTimeImmutable($endDate),
                $description,
                $imageUri,
                $url
            );
            $id = $this->courseService->create($course);

            http_response_code(201);
            echo json_encode(['message' => 'Course created successfully.', 'id' => $id]);
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
        $startDate = filter_var($_POST['start_date'] ?? null, FILTER_DEFAULT);
        $endDate = filter_var($_POST['end_date'] ?? null, FILTER_DEFAULT);
        $imageFile = $_FILES['image_uri'] ?? null;
        $url = filter_var($_POST['url'] ?? null, FILTER_VALIDATE_URL);

        // Check for required fields
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid course ID.']);
            return;
        }

        try {
            $course = $this->courseService->get($id);

            if (!$course) {
                http_response_code(404);
                echo json_encode(['error' => 'Course not found.']);
                return;
            }

            // Update fields if provided
            if ($name !== null) {
                $course->name = $name;
            }
            if ($description !== null) {
                $course->description = $description;
            }
            if ($startDate !== null) {
                $course->startDate = new DateTimeImmutable($startDate);
            }
            if ($endDate !== null) {
                $course->endDate = new DateTimeImmutable($endDate);
            }
            if ($imageFile !== null) {
                $imageValidation = $this->validateImageFile($imageFile);
                if ($imageValidation !== null) {
                    http_response_code(400);
                    echo json_encode(['error' => $imageValidation]);
                    return;
                }

                $this->deleteImageFile($course->imageUri);
                $course->imageUri = $this->saveImageFile($imageFile);
            }
            if ($url !== false) {
                $course->url = $url;
            }

            $this->courseService->update($course);

            http_response_code(200);
            echo json_encode(['message' => 'Course updated successfully.']);
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
            echo json_encode(['error' => 'Invalid course ID.']);
            return;
        }

        try {
            $course = $this->courseService->get($id);

            if (!$course) {
                http_response_code(404);
                echo json_encode(['error' => 'Course not found.']);
                return;
            }

            $this->courseService->delete($id);
            $this->deleteImageFile($course->imageUri);

            http_response_code(200);
            echo json_encode(['message' => 'Course deleted successfully.']);
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
            echo json_encode(['error' => 'Invalid course ID.']);
            return;
        }

        try {
            $course = $this->courseService->get($id);

            if (!$course) {
                http_response_code(404);
                echo json_encode(['error' => 'Course not found.']);
                return;
            }

            http_response_code(200);
            echo json_encode($course);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $result = $this->courseService->list($filter);

            http_response_code(200);
            echo json_encode($result);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    protected function createFilterFromRequest(): CourseFilter
    {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new CourseFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return CourseFilter::fromArray($data);
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
        $destinationDir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'uploads', 'images', 'courses']);
        if (!is_dir($destinationDir)) {
            // Create the directory if it doesn't exist
            mkdir($destinationDir, 0755, true);
        }

        $destination = $destinationDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($imageFile['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to save the uploaded image file.');
        }

        return '/uploads/images/courses/' . $filename;
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