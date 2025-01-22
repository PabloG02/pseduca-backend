<?php

namespace App\Controllers;

use App\Entities\TeamMember;
use App\Filters\TeamMemberFilter;
use App\Services\TeamMemberService;
use Core\Inject;
use PDOException;
use RuntimeException;

class TeamMemberController extends BaseController
{
    #[Inject]
    private TeamMemberService $teamMemberService;

    public function __construct(TeamMemberService $teamMemberService)
    {
        $this->teamMemberService = $teamMemberService;
    }

    public function create(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        $name = filter_var($_POST['name'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $imageFile = $_FILES['image_uri'] ?? null;
        $biography = filter_var($_POST['biography'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $researcherId = filter_var($_POST['researcher_id'], FILTER_VALIDATE_INT);

        // Check for required fields
        if (!isset($name) || $email === false || !isset($imageFile) || !isset($biography) || $researcherId === false) {
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
            $teamMember = new TeamMember(
                0, // ID will be auto-generated
                $name,
                $email,
                $imageUri,
                $biography,
                $researcherId
            );
            $id = $this->teamMemberService->create($teamMember);

            http_response_code(201);
            echo json_encode(['message' => 'Team member created successfully.', 'id' => $id]);
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
        $email = filter_var($_POST['email'] ?? null, FILTER_VALIDATE_EMAIL);
        $imageFile = $_FILES['image_uri'] ?? null;
        $biography = filter_var($_POST['biography'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $researcherId = filter_var($_POST['researcher_id'] ?? null, FILTER_VALIDATE_INT);

        // Check for required fields
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid team member ID.']);
            return;
        }

        try {
            $teamMember = $this->teamMemberService->get($id);

            if (!$teamMember) {
                http_response_code(404);
                echo json_encode(['error' => 'Team member not found.']);
                return;
            }

            // Update fields if provided
            if ($name !== null) {
                $teamMember->name = $name;
            }
            if ($email !== null) {
                $teamMember->email = $email;
            }
            if ($imageFile !== null) {
                $imageValidation = $this->validateImageFile($imageFile);
                if ($imageValidation !== null) {
                    http_response_code(400);
                    echo json_encode(['error' => $imageValidation]);
                    return;
                }

                $this->deleteImageFile($teamMember->imageUri);
                $teamMember->imageUri = $this->saveImageFile($imageFile);
            }
            if ($biography !== null) {
                $teamMember->biography = $biography;
            }
            if ($researcherId !== false) {
                $teamMember->researcherId = $researcherId;
            }

            $this->teamMemberService->update($teamMember);

            http_response_code(200);
            echo json_encode(['message' => 'Team member updated successfully.']);
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
            echo json_encode(['error' => 'Invalid team member ID.']);
            return;
        }

        try {
            $teamMember = $this->teamMemberService->get($id);

            if (!$teamMember) {
                http_response_code(404);
                echo json_encode(['error' => 'Team member not found.']);
                return;
            }

            $this->teamMemberService->delete($id);
            $this->deleteImageFile($teamMember->imageUri);

            http_response_code(200);
            echo json_encode(['message' => 'Team member deleted successfully.']);
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
            echo json_encode(['error' => 'Invalid team member ID.']);
            return;
        }

        try {
            $teamMember = $this->teamMemberService->get($id);

            if (!$teamMember) {
                http_response_code(404);
                echo json_encode(['error' => 'Team member not found.']);
                return;
            }

            http_response_code(200);
            echo json_encode($teamMember);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $result = $this->teamMemberService->list($filter);

            http_response_code(200);
            echo json_encode($result);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    protected function createFilterFromRequest(): TeamMemberFilter
    {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new TeamMemberFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return TeamMemberFilter::fromArray($data);
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
        $destinationDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'images';
        if (!is_dir($destinationDir)) {
            // Create the directory if it doesn't exist
            mkdir($destinationDir, 0755, true);
        }

        $destination = $destinationDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($imageFile['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to save the uploaded image file.');
        }

        return '/uploads/images/' . $filename;
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