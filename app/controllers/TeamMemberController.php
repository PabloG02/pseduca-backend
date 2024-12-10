<?php

namespace App\Controllers;

use App\Entities\TeamMember;
use App\Filters\TeamMemberFilter;
use App\Services\TeamMemberService;
use Core\Inject;
use PDOException;

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
        // TODO: receive a file and save it to the server
        $imageUri = filter_var($_POST['image_uri'] ?? null, FILTER_VALIDATE_URL, FILTER_FLAG_EMPTY_STRING_NULL);
        $biography = filter_var($_POST['biography'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $researcherId = filter_var($_POST['researcher_id'], FILTER_VALIDATE_INT);

        // Check for required fields
        if (!isset($name) || $email === false || $researcherId === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing required fields.']);
            return;
        }

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
        // TODO: receive a file and save it to the server
        $imageUri = filter_var($_POST['image_uri'] ?? null, FILTER_VALIDATE_URL, FILTER_FLAG_EMPTY_STRING_NULL);
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
            if ($imageUri !== false) {
                $teamMember->imageUri = $imageUri;
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
}