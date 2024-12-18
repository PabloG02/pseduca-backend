<?php

namespace App\Controllers;

use App\Entities\AcademicProgram;
use App\Filters\AcademicProgramFilter;
use App\Services\AcademicProgramService;
use Core\Inject;
use PDOException;
use RuntimeException;

class AcademicProgramController extends BaseController
{
    #[Inject]
    private AcademicProgramService $academicProgramService;

    public function __construct(AcademicProgramService $academicProgramService)
    {
        $this->academicProgramService = $academicProgramService;
    }

    public function create(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        $name = filter_var($_POST['name'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $qualificationLevel = filter_var($_POST['qualification_level'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $description = filter_var($_POST['description'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $imageFile = $_FILES['image_uri'] ?? null;
        $imageAlt = filter_var($_POST['image_alt'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $availableSlots = filter_var($_POST['available_slots'], FILTER_VALIDATE_INT);
        $teachingType = filter_var($_POST['teaching_type'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $offeringFrequency = filter_var($_POST['offering_frequency'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $durationEcts = filter_var($_POST['duration_ects'], FILTER_VALIDATE_INT);
        $location = filter_var($_POST['location'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $url = filter_var($_POST['url'] ?? null, FILTER_VALIDATE_URL);

        // Check for required fields
        if (!isset($name) || !isset($qualificationLevel) ||
            $availableSlots === false || !isset($teachingType) ||
            !isset($offeringFrequency) || $durationEcts === false ||
            !isset($location)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing required fields.']);
            return;
        }

        // Validate image file if provided
        $imageUri = null;
        if ($imageFile !== null) {
            $imageValidation = $this->validateImageFile($imageFile);
            if ($imageValidation !== null) {
                http_response_code(400);
                echo json_encode(['error' => $imageValidation]);
                return;
            }
            $imageUri = $this->saveImageFile($imageFile);
        }

        try {
            $program = new AcademicProgram(
                0, // ID will be auto-generated
                $name,
                $qualificationLevel,
                $description,
                $imageUri,
                $imageAlt,
                $availableSlots,
                $teachingType,
                $offeringFrequency,
                $durationEcts,
                $location,
                $url
            );
            $id = $this->academicProgramService->create($program);

            http_response_code(201);
            echo json_encode(['message' => 'Academic program created successfully.', 'id' => $id]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);

            // Delete the uploaded image if the database operation failed
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

        // Check for valid ID
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid academic program ID.']);
            return;
        }

        try {
            $program = $this->academicProgramService->get($id);

            if (!$program) {
                http_response_code(404);
                echo json_encode(['error' => 'Academic program not found.']);
                return;
            }

            // Optional fields to update
            $name = filter_var($_POST['name'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $qualificationLevel = filter_var($_POST['qualification_level'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $description = filter_var($_POST['description'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $imageFile = $_FILES['image_uri'] ?? null;
            $imageAlt = filter_var($_POST['image_alt'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $availableSlots = filter_var($_POST['available_slots'] ?? null, FILTER_VALIDATE_INT);
            $teachingType = filter_var($_POST['teaching_type'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $offeringFrequency = filter_var($_POST['offering_frequency'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $durationEcts = filter_var($_POST['duration_ects'] ?? null, FILTER_VALIDATE_INT);
            $location = filter_var($_POST['location'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $url = filter_var($_POST['url'] ?? null, FILTER_VALIDATE_URL);

            // Update fields if provided
            if ($name !== null) {
                $program->name = $name;
            }
            if ($qualificationLevel !== null) {
                $program->qualificationLevel = $qualificationLevel;
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

                // Delete old image if exists
                if ($program->imageUri) {
                    $this->deleteImageFile($program->imageUri);
                }
                $program->imageUri = $this->saveImageFile($imageFile);
            }
            if ($imageAlt !== null) {
                $program->imageAlt = $imageAlt;
            }
            if ($availableSlots !== false) {
                $program->availableSlots = $availableSlots;
            }
            if ($teachingType !== null) {
                $program->teachingType = $teachingType;
            }
            if ($offeringFrequency !== null) {
                $program->offeringFrequency = $offeringFrequency;
            }
            if ($durationEcts !== false) {
                $program->durationEcts = $durationEcts;
            }
            if ($location !== null) {
                $program->location = $location;
            }
            if ($url !== false) {
                $program->url = $url;
            }

            $this->academicProgramService->update($program);

            http_response_code(200);
            echo json_encode(['message' => 'Academic program updated successfully.']);
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
            echo json_encode(['error' => 'Invalid academic program ID.']);
            return;
        }

        try {
            $program = $this->academicProgramService->get($id);

            if (!$program) {
                http_response_code(404);
                echo json_encode(['error' => 'Academic program not found.']);
                return;
            }

            $this->academicProgramService->delete($id);

            // Delete associated image if exists
            if ($program->imageUri) {
                $this->deleteImageFile($program->imageUri);
            }

            http_response_code(200);
            echo json_encode(['message' => 'Academic program deleted successfully.']);
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
            echo json_encode(['error' => 'Invalid academic program ID.']);
            return;
        }

        try {
            $program = $this->academicProgramService->get($id);

            if (!$program) {
                http_response_code(404);
                echo json_encode(['error' => 'Academic program not found.']);
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
            $result = $this->academicProgramService->list($filter);

            http_response_code(200);
            echo json_encode($result);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    protected function createFilterFromRequest(): AcademicProgramFilter
    {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new AcademicProgramFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return AcademicProgramFilter::fromArray($data);
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

        $destinationDir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'uploads', 'images', 'academicprogs']);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $destination = $destinationDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($imageFile['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to save the uploaded image file.');
        }

        return '/uploads/images/academicprogs/' . $filename;
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
